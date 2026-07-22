<?php

namespace App\Http\Controllers\TSSD;

use App\Http\Controllers\Controller;
use App\Http\Requests\TSSD\StoreTssdDistributionRequest;
use App\Models\Province;
use App\Models\ProvinceDistributionItem;
use App\Models\PurchaseOrder;
use App\Models\TSSDDistribution;
use App\Services\DistributionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class TssdDistributionController extends Controller
{
    /**
     * Display the available Purchase Orders and their distribution status.
     */
    public function index(): View
    {
        $purchaseOrders = PurchaseOrder::query()
            ->with([
                'supplier',
                'distributionBatches.provinceDistributions',
            ])
            ->latest('po_date')
            ->paginate(10);

        return view(
            'tssd.distribution.index',
            compact('purchaseOrders')
        );
    }

    /**
     * Display the form for creating a distribution batch.
     */
     public function create(Request $request): View
    {
        $purchaseOrders = PurchaseOrder::query()
            ->with([
                'supplier',
                'items.item',
            ])
            ->whereIn('status', [
                'Pending Distribution', 
                'Distributed',
            ])
            ->latest('po_date')
            ->get();

        $provinces = Province::query()
            ->orderBy('name')
            ->get();

        $purchaseOrderId = $request->integer('purchase_order_id');

        /*
         * This legacy collection is temporarily retained because the current
         * Blade page still displays records from tssd_distributions.
         *
         * It will be removed after the create page is migrated fully to the
         * normalized batch and province-distribution structure.
         */
        $provinceDistributions = collect();

        if ($purchaseOrderId) {
            $provinceDistributions = TSSDDistribution::query()
                ->with([
                    'province',
                    'item',
                ])
                ->where(
                    'purchase_order_id',
                    $purchaseOrderId
                )
                ->get()
                ->groupBy('province_id');
        }

        return view('tssd.distribution.create', [
            'purchaseOrders' => $purchaseOrders,
            'provinces' => $provinces,
            'provinceDistributions' => $provinceDistributions,
            'purchaseOrderId' => $purchaseOrderId,
        ]);
    }

    /**
     * Store a new normalized TSSD distribution batch.
     */
    public function store(
        StoreTssdDistributionRequest $request,
        DistributionService $distributionService
    ): JsonResponse {
        $batch = $distributionService->createBatch(
            $request->validated()
        );

        return response()->json([
            'success' => true,
            'message' => 'Distribution batch saved successfully.',
            'batch_id' => $batch->id,
            'redirect_url' => route(
                'tssd.distributions.show',
                $batch->purchase_order_id
            ),
        ]);
    }

    /**
     * Display the distribution summary for a Purchase Order.
     *
     * This method currently combines legacy and normalized allocation data
     * while the old distribution table is being retired safely.
     */
    /**
     * Display the distribution summary for one Purchase Order.
     */
    public function show(int $id): View
    {
        $purchaseOrder = PurchaseOrder::query()
            ->with([
                'supplier',
                'items.item',

                'distributionBatches' => function ($query): void {
                    $query
                        ->where(
                            'status',
                            '!=',
                            'Cancelled'
                        )
                        ->orderBy('distribution_date')
                        ->orderBy('id');
                },

                'distributionBatches.creator',
                'distributionBatches.callOff',

                'distributionBatches.provinceDistributions.province',

                'distributionBatches.provinceDistributions.items.item',
            ])
            ->findOrFail($id);

        $provinces = Province::query()
            ->orderBy('name')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Normalized provincial distributions
        |--------------------------------------------------------------------------
        |
        | Flatten all active distribution batches into one collection of
        | ProvinceDistribution models. These models contain the `items`
        | relationship expected by the Blade.
        |
        */

        $normalizedProvinceDistributions =
            $purchaseOrder
                ->distributionBatches
                ->flatMap(
                    fn($batch) => $batch->provinceDistributions
                )
                ->values();

        /*
        |--------------------------------------------------------------------------
        | Consolidate multiple batches for the same province
        |--------------------------------------------------------------------------
        |
        | One Purchase Order may have several distribution batches for the
        | same province. The table should show the combined assigned PPE.
        |
        */

        $provinceDistributionSummaries =
            $normalizedProvinceDistributions
                ->groupBy('province_id')
                ->map(
                    function ($provinceRows) {
                        $firstDistribution =
                            $provinceRows->first();

                        /*
                         * Combine ProvinceDistributionItem quantities by item.
                         */
                        $combinedItems =
                            $provinceRows
                                ->flatMap(
                                    fn($distribution) => $distribution->items
                                )
                                ->groupBy('item_id')
                                ->map(
                                    function ($itemRows) {
                                        $firstItemRow =
                                            $itemRows->first();

                                        /*
                                         * Clone the first row so the Blade can
                                         * still access ->item and ->quantity.
                                         */
                                        $summaryItem =
                                            clone $firstItemRow;

                                        $summaryItem->quantity =
                                            (int) $itemRows->sum(
                                                'quantity'
                                            );

                                        return $summaryItem;
                                    }
                                )
                                ->values();

                        /*
                         * Clone one ProvinceDistribution model to preserve
                         * province_id, province, and the `items` relationship.
                         */
                        $summaryDistribution =
                            clone $firstDistribution;

                        $summaryDistribution->setRelation(
                            'items',
                            $combinedItems
                        );

                        return $summaryDistribution;
                    }
                )
                ->values();

        /*
        |--------------------------------------------------------------------------
        | Purchased PPE
        |--------------------------------------------------------------------------
        */

        $purchased = $this->emptyPpeSummary();

        foreach (
            $purchaseOrder->items as $purchaseOrderItem
        ) {
            $key = $this->mapKey(
                $purchaseOrderItem->item?->item_name,
                $purchaseOrderItem->item?->label
            );

            if ($key === null) {
                continue;
            }

            $purchased[$key] +=
                (int) $purchaseOrderItem->quantity;
        }

        /*
        |--------------------------------------------------------------------------
        | Distributed PPE
        |--------------------------------------------------------------------------
        */

        $distributed = $this->emptyPpeSummary();

        foreach (
            $normalizedProvinceDistributions as $provinceDistribution
        ) {
            foreach (
                $provinceDistribution->items as $item
            ) {
                $key = $this->mapKey(
                    $item->item?->item_name,
                    $item->item?->label
                );

                if ($key === null) {
                    continue;
                }

                $distributed[$key] +=
                    (int) $item->quantity;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Remaining PO stock
        |--------------------------------------------------------------------------
        */

        $remaining = [];

        foreach ($purchased as $key => $quantity) {
            $remaining[$key] = max(
                0,
                (int) $quantity
                - (int) (
                    $distributed[$key]
                    ?? 0
                )
            );
        }

        return view(
            'tssd.distribution.show',
            [
                'purchaseOrder' => $purchaseOrder,

                'provinces' => $provinces,

                /*
                 * This is now the normalized, consolidated collection
                 * expected by the current Blade.
                 */
                'distributions' => $provinceDistributionSummaries,

                'normalizedProvinceDistributions' => $normalizedProvinceDistributions,

                'purchased' => $purchased,

                'distributed' => $distributed,

                'remaining' => $remaining,
            ]
        );
    }

    /**
     * Return the server-calculated remaining PPE quantities for a Purchase
     * Order.
     */
    public function getRemaining(int $poId): JsonResponse
    {
        $purchaseOrder = PurchaseOrder::query()
            ->with([
                'items.item',
            ])
            ->findOrFail($poId);

        $purchased = $this->emptyPpeSummary();

        foreach ($purchaseOrder->items as $purchaseOrderItem) {
            $key = $this->mapKey(
                $purchaseOrderItem->item?->item_name,
                $purchaseOrderItem->item?->label
            );

            if ($key === null) {
                continue;
            }

            $purchased[$key] +=
                (int) $purchaseOrderItem->quantity;
        }

        $legacyRows = DB::table('tssd_distributions')
            ->join(
                'items',
                'items.id',
                '=',
                'tssd_distributions.item_id'
            )
            ->where(
                'tssd_distributions.purchase_order_id',
                $poId
            )
            ->select(
                'items.item_name',
                'items.label',
                DB::raw(
                    'SUM(tssd_distributions.quantity) as total_quantity'
                )
            )
            ->groupBy(
                'items.item_name',
                'items.label'
            )
            ->get();

        $used = $this->emptyPpeSummary();

        foreach ($legacyRows as $row) {
            $key = $this->mapKey(
                $row->item_name,
                $row->label
            );

            if ($key === null) {
                continue;
            }

            $used[$key] += (int) $row->total_quantity;
        }

        $normalizedRows = ProvinceDistributionItem::query()
            ->whereHas(
                'provinceDistribution.distributionBatch',
                function ($query) use ($poId): void {
                    $query
                        ->where('purchase_order_id', $poId)
                        ->where('status', '!=', 'Cancelled');
                }
            )
            ->with('item')
            ->selectRaw(
                'item_id, SUM(quantity) as total_quantity'
            )
            ->groupBy('item_id')
            ->get();

        foreach ($normalizedRows as $row) {
            $key = $this->mapKey(
                $row->item?->item_name,
                $row->item?->label
            );

            if ($key === null) {
                continue;
            }

            $used[$key] += (int) $row->total_quantity;
        }

        $remaining = [];

        foreach ($purchased as $key => $quantity) {
            $remaining[$key] = max(
                0,
                $quantity - ($used[$key] ?? 0)
            );
        }

        return response()->json([
            'remaining' => $remaining,
        ]);
    }

    /**
     * Convert an Item name and label into the PPE summary key used by the
     * existing interface.
     */
    private function mapKey(
        ?string $name,
        ?string $label
    ): ?string {
        $normalizedName = strtolower(
            trim((string) $name)
        );

        $normalizedLabel = strtolower(
            trim((string) $label)
        );

        return match (true) {
            in_array(
                $normalizedName,
                [
                    'long sleeve',
                    'long sleeves',
                    'longsleeve',
                    'longsleeves',
                ],
                true
            )
            && in_array(
                $normalizedLabel,
                [
                    'm',
                    'medium',
                ],
                true
            ) => 'lsm',

            in_array(
                $normalizedName,
                [
                    'long sleeve',
                    'long sleeves',
                    'longsleeve',
                    'longsleeves',
                ],
                true
            )
            && in_array(
                $normalizedLabel,
                [
                    'l',
                    'large',
                ],
                true
            ) => 'lsl',

            in_array(
                $normalizedName,
                [
                    'rubber boot',
                    'rubber boots',
                ],
                true
            )
            && in_array(
                $normalizedLabel,
                [
                    'us9',
                    'us 9',
                    '9',
                ],
                true
            ) => 'us9',

            in_array(
                $normalizedName,
                [
                    'rubber boot',
                    'rubber boots',
                ],
                true
            )
            && in_array(
                $normalizedLabel,
                [
                    'us10',
                    'us 10',
                    '10',
                ],
                true
            ) => 'us10',

            in_array(
                $normalizedName,
                [
                    'bucket hat',
                    'bucket hats',
                ],
                true
            ) => 'bucket',

            in_array(
                $normalizedName,
                [
                    'hand glove',
                    'hand gloves',
                    'glove',
                    'gloves',
                ],
                true
            ) => 'gloves',

            in_array(
                $normalizedName,
                [
                    'mask',
                    'masks',
                ],
                true
            ) => 'mask',

            default => null,
        };
    }

    /**
     * Return an empty summary for the seven supported PPE variants.
     *
     * @return array<string, int>
     */
    private function emptyPpeSummary(): array
    {
        return [
            'lsm' => 0,
            'lsl' => 0,
            'bucket' => 0,
            'us9' => 0,
            'us10' => 0,
            'gloves' => 0,
            'mask' => 0,
        ];
    }
    /**
     * Print the distribution summary of one Purchase Order.
     */
    public function print(int $id): View
    {
        $purchaseOrder = PurchaseOrder::query()
            ->with([
                'supplier',
                'items.item',

                'distributionBatches' => function ($query): void {
                    $query
                        ->where('status', '!=', 'Cancelled')
                        ->orderBy('distribution_date')
                        ->orderBy('id');
                },

                'distributionBatches.creator',
                'distributionBatches.callOff.approvedBy',
                'distributionBatches.provinceDistributions.province',
                'distributionBatches.provinceDistributions.items.item',
            ])
            ->findOrFail($id);

        $distributions = $purchaseOrder
            ->distributionBatches
            ->flatMap(
                fn($batch) => $batch->provinceDistributions
            )
            ->groupBy('province_id')
            ->map(function ($provinceRows) {
                $firstDistribution = $provinceRows->first();

                $combinedItems = $provinceRows
                    ->flatMap(
                        fn($distribution) => $distribution->items
                    )
                    ->groupBy('item_id')
                    ->map(function ($itemRows) {
                        $summaryItem = clone $itemRows->first();

                        $summaryItem->quantity = (int) $itemRows->sum(
                            'quantity'
                        );

                        return $summaryItem;
                    })
                    ->values();

                $summaryDistribution = clone $firstDistribution;

                $summaryDistribution->setRelation(
                    'items',
                    $combinedItems
                );

                return $summaryDistribution;
            })
            ->values();

        return view(
            'tssd.distribution.print',
            compact(
                'purchaseOrder',
                'distributions'
            )
        );
    }

}
