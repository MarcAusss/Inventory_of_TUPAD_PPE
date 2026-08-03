<?php

namespace App\Http\Controllers\TSSD;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\Province;
use App\Models\ProvincialInventory;
use App\Models\ProvinceDistribution;
use App\Models\PurchaseOrder;
use App\Models\SupplyDesignation;
use App\Services\CallOffInventoryService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class PpeTrackingController extends Controller
{
    public function provincialStock(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));
        $provinceId = $request->integer('province_id') ?: null;
        $items = $this->activeItems();

        $provinces = Province::query()
            ->orderBy('name')
            ->get();

        $visibleProvinces = Province::query()
            ->when($provinceId, fn (Builder $query) => $query->whereKey($provinceId))
            ->when(
                $search !== '',
                fn (Builder $query) => $query->where('name', 'like', "%{$search}%")
            )
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        $inventoryByProvince = ProvincialInventory::query()
            ->whereIn('province_id', $visibleProvinces->getCollection()->pluck('id'))
            ->get()
            ->groupBy('province_id');

        $visibleProvinces->through(function (Province $province) use ($inventoryByProvince, $items): Province {
            $inventory = $inventoryByProvince->get($province->id, collect())->keyBy('item_id');
            $quantities = [];

            foreach ($items as $item) {
                $quantities[(int) $item->id] = max(
                    0,
                    (int) ($inventory->get($item->id)?->quantity ?? 0)
                );
            }

            $province->setAttribute('tracking_quantities', $quantities);
            $province->setAttribute('tracking_total', array_sum($quantities));

            return $province;
        });

        $filteredProvinceIds = Province::query()
            ->when($provinceId, fn (Builder $query) => $query->whereKey($provinceId))
            ->when(
                $search !== '',
                fn (Builder $query) => $query->where('name', 'like', "%{$search}%")
            )
            ->pluck('id');

        $totalAvailable = (int) ProvincialInventory::query()
            ->whereIn('province_id', $filteredProvinceIds)
            ->sum('quantity');

        return view('tssd.tracking.provincial-stock', [
            'search' => $search,
            'provinceId' => $provinceId,
            'provinces' => $provinces,
            'visibleProvinces' => $visibleProvinces,
            'items' => $items,
            'totalAvailable' => $totalAvailable,
            'trackedProvinceCount' => $filteredProvinceIds->count(),
        ]);
    }

    public function callOffStock(
        Request $request,
        CallOffInventoryService $inventoryService
    ): View {
        $search = trim((string) $request->query('search', ''));
        $provinceId = $request->integer('province_id') ?: null;
        $status = trim((string) $request->query('status', ''));
        $items = $this->activeItems();
        $itemsById = $items->keyBy('id');

        $baseQuery = ProvinceDistribution::query()
            ->with([
                'province',
                'items.item',
                'distributionBatch.callOff',
                'distributionBatch.purchaseOrder.supplier',
                'deliveryReceipts.items.item',
                'supplyDesignations.items.item',
            ])
            ->whereHas('distributionBatch.callOff')
            ->when($provinceId, fn (Builder $query) => $query->where('province_id', $provinceId))
            ->when($status !== '', fn (Builder $query) => $query->where('status', $status))
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $query) use ($search): void {
                    $query
                        ->whereHas('province', fn (Builder $q) => $q->where('name', 'like', "%{$search}%"))
                        ->orWhereHas('distributionBatch.callOff', fn (Builder $q) => $q->where('call_off_number', 'like', "%{$search}%"))
                        ->orWhereHas('distributionBatch.purchaseOrder', fn (Builder $q) => $q->where('po_number', 'like', "%{$search}%"))
                        ->orWhereHas('distributionBatch.purchaseOrder.supplier', fn (Builder $q) => $q->where('supplier_name', 'like', "%{$search}%"));
                });
            });

        $allocations = (clone $baseQuery)
            ->orderByDesc('scheduled_delivery_date')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        $allocations->through(function (ProvinceDistribution $allocation) use ($inventoryService, $itemsById): ProvinceDistribution {
            $balances = collect($inventoryService->balances($allocation));

            $detailRows = $balances
                ->map(function (array $balance, int|string $itemId) use ($itemsById): array {
                    $item = $itemsById->get((int) $itemId);

                    return [
                        'item_id' => (int) $itemId,
                        'name' => $item
                            ? Item::canonicalItemName((string) $item->item_name)
                            : 'PPE Item',
                        'label' => $item?->label,
                        'allocated' => (int) ($balance['allocated_quantity'] ?? 0),
                        'received' => (int) ($balance['actual_received'] ?? 0),
                        'distributed' => (int) ($balance['previously_distributed'] ?? 0),
                        'remaining' => max(0, (int) ($balance['available_for_projects'] ?? 0)),
                    ];
                })
                ->sortBy(fn (array $row): string => Item::displaySortKey($row['name'], $row['label']))
                ->values();

            $remainingByItem = $detailRows
                ->mapWithKeys(fn (array $row): array => [
                    (int) $row['item_id'] => (int) $row['remaining'],
                ]);

            $quantities = [];
            foreach ($itemsById as $itemId => $item) {
                $quantities[(int) $itemId] = (int) $remainingByItem->get((int) $itemId, 0);
            }

            $allocation->setAttribute('tracking_item_balances', $detailRows);
            $allocation->setAttribute('tracking_quantities', $quantities);
            $allocation->setAttribute('tracking_allocated_total', (int) $detailRows->sum('allocated'));
            $allocation->setAttribute('tracking_received_total', (int) $detailRows->sum('received'));
            $allocation->setAttribute('tracking_distributed_total', (int) $detailRows->sum('distributed'));
            $allocation->setAttribute('tracking_remaining_total', (int) $detailRows->sum('remaining'));

            return $allocation;
        });

        $pageRows = $allocations->getCollection();
        $provinces = Province::query()->orderBy('name')->get();
        $statuses = ProvinceDistribution::query()
            ->whereHas('distributionBatch.callOff')
            ->whereNotNull('status')
            ->where('status', '!=', '')
            ->distinct()
            ->orderBy('status')
            ->pluck('status');

        return view('tssd.tracking.call-off-stock', [
            'search' => $search,
            'provinceId' => $provinceId,
            'status' => $status,
            'provinces' => $provinces,
            'statuses' => $statuses,
            'items' => $items,
            'allocations' => $allocations,
            'allocationCount' => $allocations->total(),
            'pageAllocatedTotal' => (int) $pageRows->sum('tracking_allocated_total'),
            'pageReceivedTotal' => (int) $pageRows->sum('tracking_received_total'),
            'pageRemainingTotal' => (int) $pageRows->sum('tracking_remaining_total'),
        ]);
    }

    public function purchaseOrderStock(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));
        $status = trim((string) $request->query('status', ''));
        $items = $this->activeItems();

        $baseQuery = PurchaseOrder::query()
            ->with([
                'supplier',
                'items.item',
                'tssdDistributions',
                'distributionBatches.provinceDistributions.items',
            ])
            ->when($status !== '', fn (Builder $query) => $query->where('status', $status))
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $query) use ($search): void {
                    $query
                        ->where('po_number', 'like', "%{$search}%")
                        ->orWhere('nefa_number', 'like', "%{$search}%")
                        ->orWhereHas('supplier', fn (Builder $q) => $q->where('supplier_name', 'like', "%{$search}%"));
                });
            });

        $purchaseOrders = (clone $baseQuery)
            ->orderByDesc('po_date')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        $purchaseOrders->through(function (PurchaseOrder $purchaseOrder) use ($items): PurchaseOrder {
            $purchased = $purchaseOrder->items
                ->groupBy('item_id')
                ->map(fn (Collection $rows): int => (int) $rows->sum('quantity'));

            $legacyAllocated = $purchaseOrder->tssdDistributions
                ->groupBy('item_id')
                ->map(fn (Collection $rows): int => (int) $rows->sum('quantity'));

            $normalizedAllocated = $purchaseOrder->distributionBatches
                ->where('status', '!=', 'Cancelled')
                ->flatMap(fn ($batch) => $batch->provinceDistributions)
                ->filter(fn (ProvinceDistribution $distribution): bool => $distribution->status !== 'Cancelled')
                ->flatMap(fn (ProvinceDistribution $distribution) => $distribution->items)
                ->groupBy('item_id')
                ->map(fn (Collection $rows): int => (int) $rows->sum('quantity'));

            $quantities = [];

            foreach ($items as $item) {
                $itemId = (int) $item->id;
                $purchasedQuantity = (int) $purchased->get($itemId, 0);
                $allocatedQuantity = (int) $legacyAllocated->get($itemId, 0)
                    + (int) $normalizedAllocated->get($itemId, 0);

                $quantities[$itemId] = [
                    'purchased' => $purchasedQuantity,
                    'allocated' => $allocatedQuantity,
                    'remaining' => max(0, $purchasedQuantity - $allocatedQuantity),
                ];
            }

            $purchaseOrder->setAttribute('tracking_quantities', $quantities);
            $purchaseOrder->setAttribute('tracking_purchased_total', (int) collect($quantities)->sum('purchased'));
            $purchaseOrder->setAttribute('tracking_allocated_total', (int) collect($quantities)->sum('allocated'));
            $purchaseOrder->setAttribute('tracking_remaining_total', (int) collect($quantities)->sum('remaining'));

            return $purchaseOrder;
        });

        $statuses = PurchaseOrder::query()
            ->whereNotNull('status')
            ->where('status', '!=', '')
            ->distinct()
            ->orderBy('status')
            ->pluck('status');

        $pageRows = $purchaseOrders->getCollection();

        return view('tssd.tracking.purchase-order-stock', [
            'search' => $search,
            'status' => $status,
            'statuses' => $statuses,
            'items' => $items,
            'purchaseOrders' => $purchaseOrders,
            'purchaseOrderCount' => $purchaseOrders->total(),
            'pagePurchasedTotal' => (int) $pageRows->sum('tracking_purchased_total'),
            'pageAllocatedTotal' => (int) $pageRows->sum('tracking_allocated_total'),
            'pageRemainingTotal' => (int) $pageRows->sum('tracking_remaining_total'),
        ]);
    }

    public function projectTransactions(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));
        $provinceId = $request->integer('province_id') ?: null;
        $status = trim((string) $request->query('status', ''));
        $items = $this->activeItems();

        $baseQuery = SupplyDesignation::query()
            ->with([
                'province',
                'items.item',
                'provinceDistribution.province',
                'provinceDistribution.distributionBatch.callOff',
                'provinceDistribution.distributionBatch.purchaseOrder.supplier',
            ])
            ->when($provinceId, function (Builder $query) use ($provinceId): void {
                $query->where(function (Builder $query) use ($provinceId): void {
                    $query
                        ->where('province_id', $provinceId)
                        ->orWhereHas('provinceDistribution', fn (Builder $q) => $q->where('province_id', $provinceId));
                });
            })
            ->when($status !== '', fn (Builder $query) => $query->where('status', $status))
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $query) use ($search): void {
                    $query
                        ->where('designation_number', 'like', "%{$search}%")
                        ->orWhere('project_code', 'like', "%{$search}%")
                        ->orWhere('project_title', 'like', "%{$search}%")
                        ->orWhere('project_name', 'like', "%{$search}%")
                        ->orWhere('location', 'like', "%{$search}%")
                        ->orWhereHas('province', fn (Builder $q) => $q->where('name', 'like', "%{$search}%"))
                        ->orWhereHas('provinceDistribution.distributionBatch.callOff', fn (Builder $q) => $q->where('call_off_number', 'like', "%{$search}%"))
                        ->orWhereHas('provinceDistribution.distributionBatch.purchaseOrder', fn (Builder $q) => $q->where('po_number', 'like', "%{$search}%"));
                });
            });

        $transactions = (clone $baseQuery)
            ->orderByDesc('designation_date')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        $transactions->through(function (SupplyDesignation $designation) use ($items): SupplyDesignation {
            $details = $designation->items
                ->map(function ($designationItem): array {
                    $item = $designationItem->item;

                    return [
                        'name' => $item
                            ? Item::canonicalItemName((string) $item->item_name)
                            : 'PPE Item',
                        'label' => $item?->label,
                        'quantity' => (int) $designationItem->quantity,
                    ];
                })
                ->sortBy(fn (array $row): string => Item::displaySortKey($row['name'], $row['label']))
                ->values();

            $quantityByItem = $designation->items
                ->groupBy('item_id')
                ->map(fn (Collection $rows): int => (int) $rows->sum('quantity'));

            $quantities = [];
            foreach ($items as $item) {
                $quantities[(int) $item->id] = (int) $quantityByItem->get((int) $item->id, 0);
            }

            $designation->setAttribute('tracking_items', $details);
            $designation->setAttribute('tracking_quantities', $quantities);
            $designation->setAttribute('tracking_total', (int) $details->sum('quantity'));

            return $designation;
        });

        $provinces = Province::query()->orderBy('name')->get();
        $statuses = SupplyDesignation::query()
            ->whereNotNull('status')
            ->where('status', '!=', '')
            ->distinct()
            ->orderBy('status')
            ->pluck('status');

        $pageRows = $transactions->getCollection();

        return view('tssd.tracking.project-transactions', [
            'search' => $search,
            'provinceId' => $provinceId,
            'status' => $status,
            'provinces' => $provinces,
            'statuses' => $statuses,
            'items' => $items,
            'transactions' => $transactions,
            'transactionCount' => $transactions->total(),
            'pagePpeTotal' => (int) $pageRows->sum('tracking_total'),
            'pageBeneficiaryTotal' => (int) $pageRows->sum('number_of_beneficiaries'),
        ]);
    }

    private function activeItems(): Collection
    {
        return Item::query()
            ->where('is_active', true)
            ->orderForDisplay()
            ->get();
    }
}
