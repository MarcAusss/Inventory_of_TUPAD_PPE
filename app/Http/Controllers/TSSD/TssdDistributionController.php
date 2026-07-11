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
    public function show(int $id): View
    {
        $purchaseOrder = PurchaseOrder::query()
            ->with([
                'supplier',
                'items.item',
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
         * Legacy allocation records.
         */
        $legacyDistributionRecords = TSSDDistribution::query()
            ->with([
                'province',
                'item',
            ])
            ->where('purchase_order_id', $id)
            ->get();

        $legacyDistributions = $legacyDistributionRecords
            ->groupBy('province_id');

        /*
         * New normalized allocation records.
         */
        $normalizedProvinceDistributions = $purchaseOrder
            ->distributionBatches
            ->flatMap(
                fn ($batch) => $batch->provinceDistributions
            )
            ->groupBy('province_id');

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

        $distributed = $this->emptyPpeSummary();

        foreach ($legacyDistributionRecords as $record) {
            $key = $this->mapKey(
                $record->item?->item_name,
                $record->item?->label
            );

            if ($key === null) {
                continue;
            }

            $distributed[$key] += (int) $record->quantity;
        }

        foreach ($purchaseOrder->distributionBatches as $batch) {
            if ($batch->status === 'Cancelled') {
                continue;
            }

            foreach ($batch->provinceDistributions as $provinceDistribution) {
                foreach ($provinceDistribution->items as $item) {
                    $key = $this->mapKey(
                        $item->item?->item_name,
                        $item->item?->label
                    );

                    if ($key === null) {
                        continue;
                    }

                    $distributed[$key] += (int) $item->quantity;
                }
            }
        }

        $remaining = [];

        foreach ($purchased as $key => $quantity) {
            $remaining[$key] = max(
                0,
                $quantity - ($distributed[$key] ?? 0)
            );
        }

        return view('tssd.distribution.show', [
            'purchaseOrder' => $purchaseOrder,
            'provinces' => $provinces,

            /*
             * Keep the old variable available for the existing Blade file.
             */
            'distributions' => $legacyDistributions,

            'legacyDistributions' => $legacyDistributions,
            'normalizedProvinceDistributions' => $normalizedProvinceDistributions,

            'purchased' => $purchased,
            'distributed' => $distributed,
            'remaining' => $remaining,
        ]);
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
        return match (true) {
            $name === 'Long Sleeve'
                && $label === 'Medium' => 'lsm',

            $name === 'Long Sleeve'
                && $label === 'Large' => 'lsl',

            $name === 'Rubber Boots'
                && $label === 'US9' => 'us9',

            $name === 'Rubber Boots'
                && $label === 'US10' => 'us10',

            $name === 'Bucket Hat' => 'bucket',

            $name === 'Hand Gloves' => 'gloves',

            $name === 'Mask' => 'mask',

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
}
