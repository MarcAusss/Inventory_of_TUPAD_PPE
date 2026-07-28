<?php

namespace App\Http\Controllers\TSSD;

use App\Http\Controllers\Controller;
use App\Http\Requests\TSSD\StoreTssdDistributionRequest;
use App\Models\Item;
use App\Models\Province;
use App\Models\ProvinceDistributionItem;
use App\Models\PurchaseOrder;
use App\Models\TSSDDistribution;
use App\Models\TssdDistributionBatch;
use App\Services\DistributionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class TssdDistributionController extends Controller
{
    private const DEFAULT_NEFA_TITLE =
        'Supply and Delivery of Personal Protective Equipment '
        . 'for the implementation of TUPAD Program under '
        . 'Framework Agreement';

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

        $activeItems = Item::query()
            ->where('is_active', true)
            ->orderBy('item_name')
            ->orderBy('label')
            ->get([
                'id',
                'item_name',
                'label',
                'unit_of_measurement',
            ]);

        return view('tssd.distribution.create', [
            'purchaseOrders' => $purchaseOrders,
            'provinces' => $provinces,
            'activeItems' => $activeItems,
            'purchaseOrderId' => $request->integer('purchase_order_id'),
            'defaultNefaTitle' => self::DEFAULT_NEFA_TITLE,
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
            'message' => 'Distribution saved and the Call-Off Number request letter was submitted to the Supply Unit.',
            'batch_id' => $batch->id,
            'redirect_url' => route(
                'tssd.distributions.show',
                $batch->purchase_order_id
            ),
        ]);
    }

    /**
     * Render the Call-Off request letter from the current unsaved form data.
     */
    public function previewCallOffLetter(
        StoreTssdDistributionRequest $request
    ): View {
        $data = $request->validated();

        $purchaseOrder = PurchaseOrder::query()
            ->with('supplier')
            ->findOrFail((int) $data['purchase_order_id']);

        $provinceIds = collect($data['distributions'])
            ->pluck('province_id')
            ->map(fn ($id): int => (int) $id)
            ->unique()
            ->values();

        $provinces = Province::query()
            ->whereIn('id', $provinceIds)
            ->get()
            ->keyBy('id');

        $itemIds = collect($data['distributions'])
            ->flatMap(function (array $distribution): array {
                return collect($distribution['items'] ?? [])
                    ->filter(fn ($quantity): bool => (int) $quantity > 0)
                    ->keys()
                    ->map(fn ($id): int => (int) $id)
                    ->all();
            })
            ->unique()
            ->values();

        $itemColumns = Item::query()
            ->whereIn('id', $itemIds)
            ->orderBy('item_name')
            ->orderBy('label')
            ->get([
                'id',
                'item_name',
                'label',
                'unit_of_measurement',
            ]);

        $rows = collect($data['distributions'])
            ->map(function (array $distribution) use ($provinces): array {
                $province = $provinces->get((int) $distribution['province_id']);

                return [
                    'province' => $province?->name ?? '—',
                    'place_of_delivery' => $province?->deliveryLocation()
                        ?? ($distribution['place_of_delivery'] ?? '—'),
                    'delivery_date' => $distribution['scheduled_delivery_date'] ?? null,
                    'items' => collect($distribution['items'] ?? [])
                        ->mapWithKeys(fn ($quantity, $itemId): array => [
                            (int) $itemId => (int) $quantity,
                        ])
                        ->all(),
                ];
            })
            ->values();

        $totals = $itemColumns
            ->mapWithKeys(fn (Item $item): array => [
                $item->id => (int) $rows->sum(
                    fn (array $row): int => (int) ($row['items'][$item->id] ?? 0)
                ),
            ])
            ->all();

        $batch = new TssdDistributionBatch([
            'distribution_date' => now()->toDateString(),
            'call_off_letter_nefa_title' => $data['nefa_title'],
            'call_off_letter_total_amount' => $data['print_total_amount'],
            'call_off_letter_margin_top' => $data['print_margin_top'],
            'call_off_letter_margin_right' => $data['print_margin_right'],
            'call_off_letter_margin_bottom' => $data['print_margin_bottom'],
            'call_off_letter_margin_left' => $data['print_margin_left'],
        ]);
        $batch->setRelation('purchaseOrder', $purchaseOrder);

        return view('tssd.call-off-letters.print', [
            'callOff' => null,
            'batch' => $batch,
            'purchaseOrder' => $purchaseOrder,
            'rows' => $rows,
            'totals' => $totals,
            'itemColumns' => $itemColumns,
            'nefaTitle' => $data['nefa_title'],
            'callOffLabel' => 'assignment of an official Call-Off Number for the distribution',
            'printDistributionBatch' => 'Draft Preview',
            'printTotalAmount' => $data['print_total_amount'],
            'printMargins' => [
                'top' => (float) $data['print_margin_top'],
                'right' => (float) $data['print_margin_right'],
                'bottom' => (float) $data['print_margin_bottom'],
                'left' => (float) $data['print_margin_left'],
            ],
            'documentDate' => now(),
            'isDraftPreview' => true,
            'backUrl' => null,
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
            ->with('items.item')
            ->findOrFail($poId);

        $activeItems = Item::query()
            ->where('is_active', true)
            ->orderBy('item_name')
            ->orderBy('label')
            ->get();

        $purchased = $activeItems
            ->mapWithKeys(fn (Item $item): array => [$item->id => 0])
            ->all();

        foreach ($purchaseOrder->items as $purchaseOrderItem) {
            $itemId = (int) $purchaseOrderItem->item_id;

            if (!array_key_exists($itemId, $purchased)) {
                continue;
            }

            $purchased[$itemId] += (int) $purchaseOrderItem->quantity;
        }

        $legacyUsed = TSSDDistribution::query()
            ->where('purchase_order_id', $poId)
            ->selectRaw('item_id, SUM(quantity) as total_quantity')
            ->groupBy('item_id')
            ->pluck('total_quantity', 'item_id');

        $normalizedUsed = ProvinceDistributionItem::query()
            ->whereHas(
                'provinceDistribution.distributionBatch',
                function ($query) use ($poId): void {
                    $query->where('purchase_order_id', $poId)
                        ->where('status', '!=', 'Cancelled');
                }
            )
            ->selectRaw('item_id, SUM(quantity) as total_quantity')
            ->groupBy('item_id')
            ->pluck('total_quantity', 'item_id');

        $remaining = [];

        foreach ($purchased as $itemId => $quantity) {
            $remaining[$itemId] = max(
                0,
                (int) $quantity
                    - (int) ($legacyUsed[$itemId] ?? 0)
                    - (int) ($normalizedUsed[$itemId] ?? 0)
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
