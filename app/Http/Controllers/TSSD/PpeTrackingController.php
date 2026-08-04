<?php

namespace App\Http\Controllers\TSSD;

use App\Http\Controllers\Controller;
use App\Models\DeliveryReceiptItem;
use App\Models\Item;
use App\Models\Province;
use App\Models\ProvincialInventory;
use App\Models\ProvinceDistribution;
use App\Models\ProvinceDistributionItem;
use App\Models\PurchaseOrder;
use App\Models\SupplyDesignation;
use App\Models\SupplyDesignationItem;
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
            ->orderByRaw($this->provinceOrderSql('id'))
            ->get();

        $visibleProvinces = Province::query()
            ->when($provinceId, fn (Builder $query) => $query->whereKey($provinceId))
            ->when(
                $search !== '',
                fn (Builder $query) => $query->where('name', 'like', "%{$search}%")
            )
            ->orderByRaw($this->provinceOrderSql('id'))
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

        $itemTotals = ProvincialInventory::query()
            ->whereIn('province_id', $filteredProvinceIds)
            ->selectRaw('item_id, SUM(quantity) AS total_quantity')
            ->groupBy('item_id')
            ->pluck('total_quantity', 'item_id')
            ->map(fn ($quantity): int => max(0, (int) $quantity));

        $totalAvailable = (int) $itemTotals->sum();

        return view('tssd.tracking.provincial-stock', [
            'search' => $search,
            'provinceId' => $provinceId,
            'provinces' => $provinces,
            'visibleProvinces' => $visibleProvinces,
            'items' => $items,
            'totalAvailable' => $totalAvailable,
            'itemTotals' => $itemTotals,
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
        $provinces = Province::query()->orderByRaw($this->provinceOrderSql('id'))->get();
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


    public function summary(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));
        $provinceId = $request->integer('province_id') ?: null;
        $status = trim((string) $request->query('status', ''));
        $items = $this->activeItems();

        $baseQuery = ProvinceDistribution::query()
            ->with([
                'province',
                'items.item',
                'distributionBatch.callOff.approvedBy',
                'distributionBatch.purchaseOrder.supplier',
                'deliveryReceipts.items.item',
                'deliveryReceipts.receivedByUser',
                'deliveryReceipts.documents',
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
                        ->orWhereHas('distributionBatch.purchaseOrder', function (Builder $q) use ($search): void {
                            $q->where('po_number', 'like', "%{$search}%")
                                ->orWhere('nefa_number', 'like', "%{$search}%");
                        })
                        ->orWhereHas('distributionBatch.purchaseOrder.supplier', fn (Builder $q) => $q->where('supplier_name', 'like', "%{$search}%"))
                        ->orWhereHas('deliveryReceipts', fn (Builder $q) => $q->where('dr_number', 'like', "%{$search}%"));
                });
            });

        $filteredAllocationIds = (clone $baseQuery)->pluck('province_distributions.id');

        $totalAllocated = (int) ProvinceDistributionItem::query()
            ->whereIn('province_distribution_id', $filteredAllocationIds)
            ->sum('quantity');

        $totalReceived = (int) DeliveryReceiptItem::query()
            ->whereHas('deliveryReceipt', function (Builder $query) use ($filteredAllocationIds): void {
                $query
                    ->whereIn('province_distribution_id', $filteredAllocationIds)
                    ->where('status', 'Received');
            })
            ->sum('received_quantity');

        $totalProjectIssued = (int) SupplyDesignationItem::query()
            ->whereHas('supplyDesignation', function (Builder $query) use ($filteredAllocationIds): void {
                $query
                    ->whereIn('province_distribution_id', $filteredAllocationIds)
                    ->where('status', '!=', 'Cancelled');
            })
            ->sum('quantity');

        $purchaseOrderIdOrder = \App\Models\TssdDistributionBatch::query()
            ->select('purchase_order_id')
            ->whereColumn('tssd_distribution_batches.id', 'province_distributions.tssd_distribution_batch_id')
            ->limit(1);

        $allocations = (clone $baseQuery)
            ->orderByDesc($purchaseOrderIdOrder)
            ->orderByDesc('tssd_distribution_batch_id')
            ->orderByRaw($this->provinceOrderSql('province_id'))
            ->orderBy('id')
            ->paginate(10)
            ->withQueryString();

        $receiptModalData = [];

        $allocations->through(function (ProvinceDistribution $allocation) use ($items, &$receiptModalData): ProvinceDistribution {
            $allocatedByItem = $allocation->items
                ->groupBy('item_id')
                ->map(fn (Collection $rows): int => (int) $rows->sum('quantity'));

            $validReceipts = $allocation->deliveryReceipts
                ->filter(fn ($receipt): bool => strcasecmp(trim((string) $receipt->status), 'Received') === 0)
                ->sortBy(fn ($receipt): string => sprintf(
                    '%s-%010d',
                    $receipt->delivery_date?->format('Y-m-d') ?? '9999-12-31',
                    (int) $receipt->id
                ))
                ->values();

            $receivedByItem = $validReceipts
                ->flatMap(fn ($receipt) => $receipt->items)
                ->groupBy('item_id')
                ->map(fn (Collection $rows): int => (int) $rows->sum('received_quantity'));

            $activeDesignations = $allocation->supplyDesignations
                ->filter(fn ($designation): bool => strcasecmp(trim((string) $designation->status), 'Cancelled') !== 0)
                ->values();

            $projectByItem = $activeDesignations
                ->flatMap(fn ($designation) => $designation->items)
                ->groupBy('item_id')
                ->map(fn (Collection $rows): int => (int) $rows->sum('quantity'));

            $directProjectUse = [];
            $unlinkedProjectUse = [];

            foreach ($activeDesignations as $designation) {
                foreach ($designation->items as $designationItem) {
                    $itemId = (int) $designationItem->item_id;
                    $quantity = max(0, (int) $designationItem->quantity);

                    if ($designation->delivery_receipt_id) {
                        $receiptId = (int) $designation->delivery_receipt_id;
                        $directProjectUse[$receiptId][$itemId] = ($directProjectUse[$receiptId][$itemId] ?? 0) + $quantity;
                    } else {
                        $unlinkedProjectUse[$itemId] = ($unlinkedProjectUse[$itemId] ?? 0) + $quantity;
                    }
                }
            }

            $unlinkedRemaining = $unlinkedProjectUse;
            $receiptSummaries = [];

            foreach ($validReceipts as $receipt) {
                $receiptItems = [];
                $receiptReceivedByItem = [];
                $receiptRemainingByItem = [];
                $receiptReceivedTotal = 0;
                $receiptProjectUsedTotal = 0;
                $receiptRemainingTotal = 0;

                foreach ($items as $item) {
                    $itemId = (int) $item->id;
                    $receivedQuantity = (int) $receipt->items
                        ->where('item_id', $itemId)
                        ->sum('received_quantity');
                    $receiptReceivedByItem[$itemId] = max(0, $receivedQuantity);

                    if ($receivedQuantity <= 0) {
                        $receiptRemainingByItem[$itemId] = 0;
                        continue;
                    }

                    $directUsed = min(
                        $receivedQuantity,
                        max(0, (int) ($directProjectUse[(int) $receipt->id][$itemId] ?? 0))
                    );
                    $remainingCapacity = max(0, $receivedQuantity - $directUsed);
                    $fifoUsed = min(
                        $remainingCapacity,
                        max(0, (int) ($unlinkedRemaining[$itemId] ?? 0))
                    );
                    $projectUsed = $directUsed + $fifoUsed;
                    $remainingQuantity = max(0, $receivedQuantity - $projectUsed);
                    $unlinkedRemaining[$itemId] = max(
                        0,
                        (int) ($unlinkedRemaining[$itemId] ?? 0) - $fifoUsed
                    );

                    $receiptReceivedTotal += $receivedQuantity;
                    $receiptProjectUsedTotal += $projectUsed;
                    $receiptRemainingTotal += $remainingQuantity;
                    $receiptRemainingByItem[$itemId] = $remainingQuantity;

                    $receiptItems[] = [
                        'name' => Item::canonicalItemName((string) $item->item_name),
                        'label' => $item->label,
                        'received' => $receivedQuantity,
                        'project_used' => $projectUsed,
                        'remaining' => $remainingQuantity,
                    ];
                }

                $receiptStatus = $receiptRemainingTotal <= 0 && $receiptReceivedTotal > 0
                    ? 'Fully Allocated to Projects'
                    : 'Stock Remaining';

                $summary = [
                    'id' => (int) $receipt->id,
                    'dr_number' => $receipt->dr_number,
                    'delivery_date' => $receipt->delivery_date?->format('M d, Y') ?? '—',
                    'status' => $receiptStatus,
                    'received_total' => $receiptReceivedTotal,
                    'project_used_total' => $receiptProjectUsedTotal,
                    'remaining_total' => $receiptRemainingTotal,
                    'received_by_item' => $receiptReceivedByItem,
                    'remaining_by_item' => $receiptRemainingByItem,
                ];

                $receiptSummaries[] = $summary;

                $receiptDocuments = $receipt->documents
                    ->map(fn ($document): array => [
                        'name' => $document->original_name ?: 'Delivery Receipt document',
                        'url' => route('documents.receipt-documents', $document),
                    ])
                    ->values()
                    ->all();

                if ($receipt->document && empty($receiptDocuments)) {
                    $receiptDocuments[] = [
                        'name' => 'Delivery Receipt PDF',
                        'url' => route('documents.receipt-legacy', $receipt),
                    ];
                }

                $receiptModalData[(int) $receipt->id] = [
                    ...$summary,
                    'call_off_number' => $allocation->distributionBatch?->callOff?->call_off_number ?? '—',
                    'po_number' => $allocation->distributionBatch?->purchaseOrder?->po_number ?? '—',
                    'province' => $allocation->province?->name ?? '—',
                    'receiver' => $receipt->physical_receiver_name
                        ?: $receipt->received_by
                        ?: $receipt->receivedByUser?->name
                        ?: '—',
                    'submitted_at' => $receipt->submitted_at?->format('M d, Y h:i A') ?? '—',
                    'remarks' => $receipt->remarks ?: 'No remarks.',
                    'documents' => $receiptDocuments,
                    'items' => $receiptItems,
                ];
            }

            $quantities = [];
            $allocatedTotal = 0;
            $receivedTotal = 0;
            $projectTotal = 0;
            $callOffRemainingTotal = 0;
            $availableNowTotal = 0;
            $toReceiveTotal = 0;

            foreach ($items as $item) {
                $itemId = (int) $item->id;
                $allocated = max(0, (int) $allocatedByItem->get($itemId, 0));
                $received = max(0, (int) $receivedByItem->get($itemId, 0));
                $project = max(0, (int) $projectByItem->get($itemId, 0));
                $callOffRemaining = max(0, $allocated - $project);
                $availableNow = max(0, min($allocated, $received) - $project);
                $toReceive = max(0, $allocated - $received);

                $quantities[$itemId] = [
                    'allocated' => $allocated,
                    'received' => $received,
                    'project' => $project,
                    'remaining' => $callOffRemaining,
                    'available_now' => $availableNow,
                    'to_receive' => $toReceive,
                ];

                $allocatedTotal += $allocated;
                $receivedTotal += $received;
                $projectTotal += $project;
                $callOffRemainingTotal += $callOffRemaining;
                $availableNowTotal += $availableNow;
                $toReceiveTotal += $toReceive;
            }

            $receivingStatus = match (true) {
                $allocatedTotal > 0 && $toReceiveTotal === 0 => 'Received',
                $receivedTotal > 0 => 'Partially Received',
                default => 'Pending Delivery',
            };

            $allocation->setAttribute('summary_quantities', $quantities);
            $allocation->setAttribute('summary_receipts', $receiptSummaries);
            $allocation->setAttribute('summary_allocated_total', $allocatedTotal);
            $allocation->setAttribute('summary_received_total', $receivedTotal);
            $allocation->setAttribute('summary_project_total', $projectTotal);
            $allocation->setAttribute('summary_remaining_total', $callOffRemainingTotal);
            $allocation->setAttribute('summary_available_now_total', $availableNowTotal);
            $allocation->setAttribute('summary_to_receive_total', $toReceiveTotal);
            $allocation->setAttribute('summary_receiving_status', $receivingStatus);

            return $allocation;
        });

        $provinces = Province::query()->orderByRaw($this->provinceOrderSql('id'))->get();
        $statuses = ProvinceDistribution::query()
            ->whereHas('distributionBatch.callOff')
            ->whereNotNull('status')
            ->where('status', '!=', '')
            ->distinct()
            ->orderBy('status')
            ->pluck('status');

        return view('tssd.tracking.summary', [
            'search' => $search,
            'provinceId' => $provinceId,
            'status' => $status,
            'provinces' => $provinces,
            'statuses' => $statuses,
            'items' => $items,
            'allocations' => $allocations,
            'receiptModalData' => $receiptModalData,
            'allocationCount' => $filteredAllocationIds->count(),
            'totalAllocated' => $totalAllocated,
            'totalReceived' => $totalReceived,
            'totalProjectIssued' => $totalProjectIssued,
            'totalCallOffRemaining' => max(0, $totalAllocated - $totalProjectIssued),
            'totalAvailableNow' => max(0, $totalReceived - $totalProjectIssued),
        ]);
    }

    /**
     * Operational province order used by TSSD tracking tables.
     */
    private function provinceOrderSql(string $column): string
    {
        $names = [
            'Albay',
            'Camarines Norte',
            'Camarines Sur',
            'Catanduanes',
            'Masbate',
            'Sorsogon',
        ];

        $ids = Province::query()
            ->whereIn('name', $names)
            ->pluck('id', 'name');

        $parts = [];
        foreach ($names as $position => $name) {
            $id = (int) ($ids[$name] ?? 0);
            if ($id > 0) {
                $parts[] = 'WHEN '.$id.' THEN '.($position + 1);
            }
        }

        if ($parts === []) {
            return '999';
        }

        return 'CASE '.$column.' '.implode(' ', $parts).' ELSE 999 END';
    }

    private function activeItems(): Collection
    {
        return Item::query()
            ->where('is_active', true)
            ->orderForDisplay()
            ->get();
    }
}
