<?php

namespace App\Services;

use App\Models\CallOff;
use App\Models\DeliveryReceipt;
use App\Models\InventoryMovement;
use App\Models\Item;
use App\Models\Province;
use App\Models\ProvinceDistribution;
use App\Models\ProvinceDistributionItem;
use App\Models\ProvincialInventory;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Supplier;
use App\Models\SupplyDesignation;
use App\Models\TssdDistributionBatch;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class DashboardService extends BaseService
{
    /**
     * Return dashboard information based on the authenticated role.
     *
     * @return array<string, mixed>
     */
    public function getDashboardData(): array
    {
        if ($this->isTssd()) {
            return $this->getTssdDashboardData();
        }

        if ($this->isSupply()) {
            return $this->getSupplyDashboardData();
        }

        if ($this->isProvincial()) {
            return $this->getProvincialDashboardData();
        }

        if ($this->isAccounting()) {
            return $this->getAccountingDashboardData();
        }

        return [
            'statistics' => [],
            'charts' => [],
            'recentActivities' => [],
        ];
    }

    // FOR PROVINCIAL OFFICE DASHBOARD
    /**
     * @return array<string, mixed>
     */
    private function supplyPpePurchaseComposition(): array
    {
        $rows = PurchaseOrderItem::query()
            ->join(
                'items',
                'items.id',
                '=',
                'purchase_order_items.item_id'
            )
            ->selectRaw(
                '
            CASE
                WHEN items.item_name = "Long Sleeve"
                    THEN "Long Sleeves"
                WHEN items.item_name = "Bucket Hat"
                    THEN "Bucket Hat"
                WHEN items.item_name = "Rubber Boots"
                    THEN "Rubber Boots"
                WHEN items.item_name = "Hand Gloves"
                    THEN "Hand Gloves"
                WHEN items.item_name = "Mask"
                    THEN "Mask"
                ELSE items.item_name
            END AS category_name,

            SUM(purchase_order_items.quantity)
                AS total_quantity
            '
            )
            ->groupBy('category_name')
            ->orderBy('category_name')
            ->get();

        return [
            'labels' => $rows
                ->pluck('category_name')
                ->values()
                ->all(),

            'data' => $rows
                ->pluck('total_quantity')
                ->map(
                    fn($quantity): int => (int) $quantity
                )
                ->values()
                ->all(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function supplyPoDistributionStatus(): array
    {
        $statuses = [
            'Pending Distribution',
            'Distributed',
            'Completed',
        ];

        $counts = PurchaseOrder::query()
            ->select(
                'status',
                DB::raw('COUNT(*) as total')
            )
            ->groupBy('status')
            ->pluck(
                'total',
                'status'
            );

        return [
            'labels' => $statuses,

            'data' => collect($statuses)
                ->map(
                    fn(string $status): int => (int) (
                        $counts[$status]
                        ?? 0
                    )
                )
                ->values()
                ->all(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function supplyMonthlyPurchaseOrders(): array
    {
        $startDate = now()
            ->startOfMonth()
            ->subMonths(5);

        $rows = PurchaseOrder::query()
            ->whereDate(
                'po_date',
                '>=',
                $startDate->toDateString()
            )
            ->selectRaw(
                '
            YEAR(po_date) AS po_year,
            MONTH(po_date) AS po_month,
            COUNT(*) AS po_count,
            SUM(total_amount) AS po_value
            '
            )
            ->groupBy(
                'po_year',
                'po_month'
            )
            ->get()
            ->keyBy(
                fn($row): string => sprintf(
                    '%04d-%02d',
                    $row->po_year,
                    $row->po_month
                )
            );

        $labels = [];
        $counts = [];
        $values = [];

        for ($index = 0; $index < 6; $index++) {
            $month = $startDate
                ->copy()
                ->addMonths($index);

            $key = $month->format('Y-m');

            $row = $rows->get($key);

            $labels[] =
                $month->format('M Y');

            $counts[] =
                (int) (
                    $row?->po_count
                    ?? 0
                );

            $values[] =
                (float) (
                    $row?->po_value
                    ?? 0
                );
        }

        return [
            'labels' => $labels,

            'counts' => $counts,

            'values' => $values,
        ];
    }

    /**
     * @return Collection<int, PurchaseOrder>
     */
    private function latestSupplyPurchaseOrders(): Collection
    {
        return PurchaseOrder::query()
            ->with([
                'supplier',
                'creator',
                'items.item',
            ])
            ->latest('po_date')
            ->latest('id')
            ->take(7)
            ->get();
    }

    /**
     * @return Collection<int, CallOff>
     */
    private function pendingSupplyCallOffs(): Collection
    {
        return CallOff::query()
            ->with([
                'purchaseOrder.supplier',
                'distributionBatch.provinceDistributions.province',
                'assignedBy',
            ])
            ->where(
                'status',
                'Pending'
            )
            ->latest('assigned_at')
            ->latest('id')
            ->take(5)
            ->get();
    }

    /**
     * @return Collection<int, Supplier>
     */
    private function supplySupplierSummary(): Collection
    {
        return Supplier::query()
            ->withCount('purchaseOrders')
            ->withSum(
                'purchaseOrders',
                'total_amount'
            )
            ->where(
                'is_active',
                true
            )
            ->orderByDesc(
                'purchase_orders_sum_total_amount'
            )
            ->take(5)
            ->get();
    }

    /**
     * @return Collection<int, object>
     */
    private function supplyPpeStockSummary(): Collection
    {
        $purchased = PurchaseOrderItem::query()
            ->selectRaw(
                'item_id, SUM(quantity) as purchased_quantity'
            )
            ->groupBy('item_id')
            ->pluck(
                'purchased_quantity',
                'item_id'
            );

        $distributed = ProvinceDistributionItem::query()
            ->whereHas(
                'provinceDistribution.distributionBatch',
                function ($query): void {
                    $query->where(
                        'status',
                        '!=',
                        'Cancelled'
                    );
                }
            )
            ->selectRaw(
                'item_id, SUM(quantity) as distributed_quantity'
            )
            ->groupBy('item_id')
            ->pluck(
                'distributed_quantity',
                'item_id'
            );

        return Item::query()
            ->where(
                'is_active',
                true
            )
            ->orderBy('id')
            ->get()
            ->map(
                function (Item $item) use ($purchased, $distributed): object {
                    $purchasedQuantity =
                        (int) (
                            $purchased[$item->id]
                            ?? 0
                        );

                    $distributedQuantity =
                        (int) (
                            $distributed[$item->id]
                            ?? 0
                        );

                    return (object) [
                        'item' => $item,

                        'purchased' => $purchasedQuantity,

                        'distributed' => $distributedQuantity,

                        'remaining' => max(
                            0,
                            $purchasedQuantity
                            - $distributedQuantity
                        ),
                    ];
                }
            );
    }

    // FOR TSSD DASHBOARD
    /**
     * @return array<string, mixed>
     */
    private function provincialInventoryComposition(
        int $provinceId
    ): array {
        $inventories = ProvincialInventory::query()
            ->join(
                'items',
                'items.id',
                '=',
                'provincial_inventories.item_id'
            )
            ->where(
                'provincial_inventories.province_id',
                $provinceId
            )
            ->selectRaw(
                '
            CASE
                WHEN items.item_name = "Long Sleeve"
                    THEN "Long Sleeves"
                WHEN items.item_name = "Bucket Hat"
                    THEN "Bucket Hat"
                WHEN items.item_name = "Rubber Boots"
                    THEN "Rubber Boots"
                WHEN items.item_name = "Hand Gloves"
                    THEN "Hand Gloves"
                WHEN items.item_name = "Mask"
                    THEN "Mask"
                ELSE items.item_name
            END AS category_name,
            SUM(provincial_inventories.quantity) AS total_quantity
            '
            )
            ->groupBy('category_name')
            ->orderBy('category_name')
            ->get();

        return [
            'labels' => $inventories
                ->pluck('category_name')
                ->values()
                ->all(),

            'data' => $inventories
                ->pluck('total_quantity')
                ->map(
                    fn($value): int => (int) $value
                )
                ->values()
                ->all(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function provincialMonthlyMovement(
        int $provinceId
    ): array {
        $startDate = now()
            ->startOfMonth()
            ->subMonths(5);

        $movements = InventoryMovement::query()
            ->forProvince($provinceId)
            ->whereDate(
                'movement_date',
                '>=',
                $startDate->toDateString()
            )
            ->selectRaw(
                '
            YEAR(movement_date) AS movement_year,
            MONTH(movement_date) AS movement_month,

            SUM(
                CASE
                    WHEN movement_type IN (
                        "IN",
                        "ADJUSTMENT_IN"
                    )
                    THEN quantity
                    ELSE 0
                END
            ) AS received_quantity,

            SUM(
                CASE
                    WHEN movement_type IN (
                        "OUT",
                        "ADJUSTMENT_OUT"
                    )
                    THEN quantity
                    ELSE 0
                END
            ) AS issued_quantity
            '
            )
            ->groupBy(
                'movement_year',
                'movement_month'
            )
            ->get()
            ->keyBy(
                fn($row): string => sprintf(
                    '%04d-%02d',
                    $row->movement_year,
                    $row->movement_month
                )
            );

        $labels = [];
        $received = [];
        $issued = [];

        for ($index = 0; $index < 6; $index++) {
            $month = $startDate
                ->copy()
                ->addMonths($index);

            $key = $month->format('Y-m');

            $row = $movements->get($key);

            $labels[] = $month->format('M Y');

            $received[] = (int) (
                $row?->received_quantity
                ?? 0
            );

            $issued[] = (int) (
                $row?->issued_quantity
                ?? 0
            );
        }

        return [
            'labels' => $labels,

            'datasets' => [
                [
                    'label' => 'Received',
                    'data' => $received,
                ],

                [
                    'label' => 'Issued to Projects',
                    'data' => $issued,
                ],
            ],
        ];
    }

    private function provincialInventorySummary(
        int $provinceId
    ): Collection {
        return ProvincialInventory::query()
            ->with('item')
            ->where('province_id', $provinceId)
            ->orderBy('item_id')
            ->get();
    }
    /*
    |--------------------------------------------------------------------------
    | TSSD Dashboard
    |--------------------------------------------------------------------------
    */

    /**
     * @return array<string, mixed>
     */
    private function getTssdDashboardData(): array
    {
        return [
            'statistics' => $this->tssdStatistics(),

            'charts' => [
                'provinceDistribution' => $this->provinceDistributionChart(),

                'callOffStatus' => $this->callOffStatusChart(),
            ],

            'recentActivities' => [
                'receivingProgress' => $this->provinceReceivingProgress(),

                'latestBatches' => $this->latestDistributionBatches(),

                'recentReceipts' => $this->recentProvincialReceipts(),
            ],
        ];
    }

    /**
     * @return array<string, int>
     */
    private function tssdStatistics(): array
    {
        return [
            'purchase_orders' => PurchaseOrder::query()
                ->whereIn(
                    'status',
                    [
                        'Pending Distribution',
                        'Distributed',
                    ]
                )
                ->count(),

            'distribution_batches' => TssdDistributionBatch::query()
                ->where(
                    'status',
                    '!=',
                    'Cancelled'
                )
                ->count(),

            'pending_calloffs' => CallOff::query()
                ->where(
                    'status',
                    'Pending'
                )
                ->count(),

            'approved_calloffs' => CallOff::query()
                ->where(
                    'status',
                    'Approved'
                )
                ->count(),

            'active_provinces' => ProvinceDistribution::query()
                ->whereNotIn(
                    'status',
                    [
                        'Received',
                        'Cancelled',
                    ]
                )
                ->distinct(
                    'province_id'
                )
                ->count(
                    'province_id'
                ),

            'total_allocated_items' => (int) ProvinceDistributionItem::query()
                ->whereHas(
                    'provinceDistribution',
                    function ($query): void {
                        $query->where(
                            'status',
                            '!=',
                            'Cancelled'
                        );
                    }
                )
                ->sum('quantity'),
        ];
    }

    /**
     * Group all distribution items into the five dashboard PPE categories.
     *
     * @return array<string, mixed>
     */
    private function provinceDistributionChart(): array
    {
        $provinces = Province::query()
            ->orderBy('id')
            ->get([
                'id',
                'name',
            ]);

        $rows = ProvinceDistributionItem::query()
            ->join(
                'province_distributions',
                'province_distributions.id',
                '=',
                'province_distribution_items.province_distribution_id'
            )
            ->join(
                'tssd_distribution_batches',
                'tssd_distribution_batches.id',
                '=',
                'province_distributions.tssd_distribution_batch_id'
            )
            ->join(
                'items',
                'items.id',
                '=',
                'province_distribution_items.item_id'
            )
            ->where(
                'province_distributions.status',
                '!=',
                'Cancelled'
            )
            ->where(
                'tssd_distribution_batches.status',
                '!=',
                'Cancelled'
            )
            ->selectRaw(
                '
                province_distributions.province_id,

                SUM(
                    CASE
                        WHEN items.item_name = ?
                        THEN province_distribution_items.quantity
                        ELSE 0
                    END
                ) AS long_sleeves,

                SUM(
                    CASE
                        WHEN items.item_name = ?
                        THEN province_distribution_items.quantity
                        ELSE 0
                    END
                ) AS bucket_hats,

                SUM(
                    CASE
                        WHEN items.item_name = ?
                        THEN province_distribution_items.quantity
                        ELSE 0
                    END
                ) AS rubber_boots,

                SUM(
                    CASE
                        WHEN items.item_name = ?
                        THEN province_distribution_items.quantity
                        ELSE 0
                    END
                ) AS hand_gloves,

                SUM(
                    CASE
                        WHEN items.item_name = ?
                        THEN province_distribution_items.quantity
                        ELSE 0
                    END
                ) AS masks
                ',
                [
                    'Long Sleeve',
                    'Bucket Hat',
                    'Rubber Boots',
                    'Hand Gloves',
                    'Mask',
                ]
            )
            ->groupBy(
                'province_distributions.province_id'
            )
            ->get()
            ->keyBy('province_id');

        return [
            'labels' => $provinces
                ->pluck('name')
                ->values()
                ->all(),

            'shortLabels' => $provinces
                ->map(
                    fn(Province $province): string => match ($province->name) {
                        'Albay' => 'AL',
                        'Camarines Norte' => 'CN',
                        'Camarines Sur' => 'CS',
                        'Catanduanes' => 'CA',
                        'Masbate' => 'MA',
                        'Sorsogon' => 'SO',
                        default => strtoupper(
                            substr(
                                $province->name,
                                0,
                                2
                            )
                        ),
                    }
                )
                ->values()
                ->all(),

            'datasets' => [
                [
                    'label' => 'Long Sleeves',

                    'data' => $this->chartValues(
                        $provinces,
                        $rows,
                        'long_sleeves'
                    ),

                    'unit' => 'pieces',
                ],

                [
                    'label' => 'Bucket Hat',

                    'data' => $this->chartValues(
                        $provinces,
                        $rows,
                        'bucket_hats'
                    ),

                    'unit' => 'pieces',
                ],

                [
                    'label' => 'Rubber Boots',

                    'data' => $this->chartValues(
                        $provinces,
                        $rows,
                        'rubber_boots'
                    ),

                    'unit' => 'pairs',
                ],

                [
                    'label' => 'Hand Gloves',

                    'data' => $this->chartValues(
                        $provinces,
                        $rows,
                        'hand_gloves'
                    ),

                    'unit' => 'pairs',
                ],

                [
                    'label' => 'Mask',

                    'data' => $this->chartValues(
                        $provinces,
                        $rows,
                        'masks'
                    ),

                    'unit' => 'boxes',
                ],
            ],
        ];
    }

    /**
     * @param  Collection<int, Province>  $provinces
     * @param  Collection<int, object>  $rows
     * @return array<int, int>
     */
    private function chartValues(
        Collection $provinces,
        Collection $rows,
        string $column
    ): array {
        return $provinces
            ->map(
                function (Province $province) use ($rows, $column): int {
                    $row = $rows->get(
                        $province->id
                    );

                    return (int) (
                        $row?->{$column}
                        ?? 0
                    );
                }
            )
            ->values()
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    private function callOffStatusChart(): array
    {
        $statuses = [
            'Pending',
            'Approved',
            'Completed',
            'Rejected',
            'Cancelled',
        ];

        $counts = CallOff::query()
            ->select(
                'status',
                DB::raw(
                    'COUNT(*) as total'
                )
            )
            ->groupBy('status')
            ->pluck(
                'total',
                'status'
            );

        return [
            'labels' => $statuses,

            'data' => collect($statuses)
                ->map(
                    fn(string $status): int => (int) (
                        $counts[$status]
                        ?? 0
                    )
                )
                ->all(),
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function provinceReceivingProgress(): array
    {
        $provinces = Province::query()
            ->orderBy('id')
            ->get();

        return $provinces
            ->map(
                function (Province $province): array {
                    $statusCounts =
                        ProvinceDistribution::query()
                            ->where(
                                'province_id',
                                $province->id
                            )
                            ->where(
                                'status',
                                '!=',
                                'Cancelled'
                            )
                            ->select(
                                'status',
                                DB::raw(
                                    'COUNT(*) as total'
                                )
                            )
                            ->groupBy('status')
                            ->pluck(
                                'total',
                                'status'
                            );

                    $received =
                        (int) (
                            $statusCounts[
                                'Received'
                            ] ?? 0
                        );

                    $partial =
                        (int) (
                            $statusCounts[
                                'Partially Received'
                            ] ?? 0
                        );

                    $pending =
                        collect([
                            'Pending',
                            'Approved',
                            'For Delivery',
                        ])
                            ->sum(
                                fn(string $status): int => (int) (
                                    $statusCounts[
                                        $status
                                    ] ?? 0
                                )
                            );

                    $total =
                        $received
                        + $partial
                        + $pending;

                    $completedEquivalent =
                        $received
                        + ($partial * 0.5);

                    $progress = $total > 0
                        ? (int) round(
                            (
                                $completedEquivalent
                                / $total
                            ) * 100
                        )
                        : 0;

                    return [
                        'province' => $province->name,

                        'received' => $received,

                        'partial' => $partial,

                        'pending' => $pending,

                        'progress' => $progress,
                    ];
                }
            )
            ->all();
    }

    /**
     * @return Collection<int, TssdDistributionBatch>
     */
    private function latestDistributionBatches(): Collection
    {
        return TssdDistributionBatch::query()
            ->with([
                'purchaseOrder',
                'provinceDistributions.province',
                'provinceDistributions.items',
                'callOff',
            ])
            ->latest('distribution_date')
            ->latest('id')
            ->take(6)
            ->get();
    }

    /**
     * @return Collection<int, DeliveryReceipt>
     */
    private function recentProvincialReceipts(): Collection
    {
        return DeliveryReceipt::query()
            ->with([
                'province',
                'provinceDistribution.distributionBatch.callOff',
            ])
            ->latest('submitted_at')
            ->latest('id')
            ->take(5)
            ->get();
    }

    /*
    |--------------------------------------------------------------------------
    | Supply Dashboard
    |--------------------------------------------------------------------------
    |
    | These remain separate because the Supply dashboard will use a different
    | bento grid and different charts in the next dashboard revision.
    |
    */

    /**
     * @return array<string, mixed>
     */
    private function getSupplyDashboardData(): array
    {
        return [
            'statistics' => [
                'total_suppliers' => Supplier::query()
                    ->where('is_active', true)
                    ->count(),

                'total_purchase_orders' => PurchaseOrder::query()
                    ->count(),

                'total_po_value' => (float) PurchaseOrder::query()
                    ->sum('total_amount'),

                'pending_distributions' => PurchaseOrder::query()
                    ->where(
                        'status',
                        'Pending Distribution'
                    )
                    ->count(),

                'distributed_purchase_orders' => PurchaseOrder::query()
                    ->where(
                        'status',
                        'Distributed'
                    )
                    ->count(),

                'pending_calloff_approvals' => CallOff::query()
                    ->where(
                        'status',
                        'Pending'
                    )
                    ->count(),

                'approved_calloffs' => CallOff::query()
                    ->where(
                        'status',
                        'Approved'
                    )
                    ->count(),

                'total_purchased_items' => (int) PurchaseOrderItem::query()
                    ->sum('quantity'),
            ],

            'charts' => [
                'ppePurchaseComposition' => $this->supplyPpePurchaseComposition(),

                'poDistributionStatus' => $this->supplyPoDistributionStatus(),

                'monthlyPurchaseOrders' => $this->supplyMonthlyPurchaseOrders(),
            ],

            'recentActivities' => [
                'latestPurchaseOrders' => $this->latestSupplyPurchaseOrders(),

                'pendingCallOffs' => $this->pendingSupplyCallOffs(),

                'supplierSummary' => $this->supplySupplierSummary(),

                'ppeStockSummary' => $this->supplyPpeStockSummary(),
            ],
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Provincial Dashboard
    |--------------------------------------------------------------------------
    */

    /**
     * @return array<string, mixed>
     */
    private function getProvincialDashboardData(): array
    {
        $provinceId = $this->provinceId();

        abort_unless(
            $provinceId,
            403,
            'This Provincial Office account has no assigned province.'
        );

        return [
            'statistics' => [
                'available_items' => (int) ProvincialInventory::query()
                    ->where('province_id', $provinceId)
                    ->sum('quantity'),

                'total_received' => (int) InventoryMovement::query()
                    ->forProvince($provinceId)
                    ->stockIn()
                    ->sum('quantity'),

                'total_issued' => (int) InventoryMovement::query()
                    ->forProvince($provinceId)
                    ->stockOut()
                    ->sum('quantity'),

                'pending_allocations' => ProvinceDistribution::query()
                    ->where('province_id', $provinceId)
                    ->whereIn('status', [
                        'Approved',
                        'For Delivery',
                    ])
                    ->count(),

                'received_deliveries' => ProvinceDistribution::query()
                    ->where('province_id', $provinceId)
                    ->where('status', 'Received')
                    ->count(),

                'partially_received' => ProvinceDistribution::query()
                    ->where('province_id', $provinceId)
                    ->where('status', 'Partially Received')
                    ->count(),

                'project_designations' => SupplyDesignation::query()
                    ->where('province_id', $provinceId)
                    ->count(),
            ],

            'charts' => [
                'inventoryComposition' => $this->provincialInventoryComposition(
                    $provinceId
                ),

                'monthlyMovement' => $this->provincialMonthlyMovement(
                    $provinceId
                ),
            ],

            'recentActivities' => [
                'inventorySummary' => $this->provincialInventorySummary(
                    $provinceId
                ),

                'recentReceipts' => DeliveryReceipt::query()
                    ->with([
                        'provinceDistribution'
                        . '.distributionBatch'
                        . '.callOff',
                        'items.item',
                    ])
                    ->where('province_id', $provinceId)
                    ->latest('delivery_date')
                    ->latest('id')
                    ->take(5)
                    ->get(),

                'recentDesignations' => SupplyDesignation::query()
                    ->with('items.item')
                    ->where('province_id', $provinceId)
                    ->latest('designation_date')
                    ->latest('id')
                    ->take(5)
                    ->get(),
            ],
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Accounting Dashboard
    |--------------------------------------------------------------------------
    */

    /**
     * @return array<string, mixed>
     */
    private function getAccountingDashboardData(): array
    {
        return [
            'statistics' => [
                'purchase_orders' => PurchaseOrder::query()
                    ->count(),

                'po_value' => (float) PurchaseOrder::query()
                    ->sum('total_amount'),

                'total_purchased_items' => (int) PurchaseOrderItem::query()
                    ->sum('quantity'),

                'total_distributed_items' => (int) ProvinceDistributionItem::query()
                    ->sum('quantity'),

                'delivery_receipts' => DeliveryReceipt::query()
                    ->count(),

                'project_designations' => SupplyDesignation::query()
                    ->count(),
            ],

            'charts' => [
                'monthlyFinancialOverview' =>
                    $this->accountingMonthlyFinancialOverview(),

                'purchaseOrderStatus' =>
                    $this->accountingPurchaseOrderStatus(),

                'provinceDistribution' =>
                    $this->accountingProvinceDistribution(),
            ],

            'recentActivities' => [
                'latestPurchaseOrders' =>
                    $this->accountingLatestPurchaseOrders(),

                'supplierSummary' =>
                    $this->accountingSupplierSummary(),
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function accountingMonthlyFinancialOverview(): array
    {
        $startDate = now()
            ->startOfMonth()
            ->subMonths(5);

        $rows = PurchaseOrder::query()
            ->whereDate(
                'po_date',
                '>=',
                $startDate->toDateString()
            )
            ->selectRaw(
                '
            YEAR(po_date) AS po_year,
            MONTH(po_date) AS po_month,
            SUM(total_amount) AS total_value
            '
            )
            ->groupBy(
                'po_year',
                'po_month'
            )
            ->get()
            ->keyBy(
                fn($row): string => sprintf(
                    '%04d-%02d',
                    $row->po_year,
                    $row->po_month
                )
            );

        $labels = [];
        $values = [];

        for ($index = 0; $index < 6; $index++) {
            $month = $startDate
                ->copy()
                ->addMonths($index);

            $key = $month->format('Y-m');

            $labels[] = $month->format('M Y');

            $values[] = (float) (
                $rows->get($key)?->total_value
                ?? 0
            );
        }

        return [
            'labels' => $labels,
            'data' => $values,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function accountingPurchaseOrderStatus(): array
    {
        $statuses = [
            'Pending Distribution',
            'Distributed',
            'Completed',
        ];

        $counts = PurchaseOrder::query()
            ->select(
                'status',
                DB::raw('COUNT(*) AS total')
            )
            ->groupBy('status')
            ->pluck(
                'total',
                'status'
            );

        return [
            'labels' => $statuses,

            'data' => collect($statuses)
                ->map(
                    fn(string $status): int => (int) (
                        $counts[$status]
                        ?? 0
                    )
                )
                ->values()
                ->all(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function accountingProvinceDistribution(): array
    {
        $provinces = Province::query()
            ->orderBy('id')
            ->get();

        $distributed = ProvinceDistributionItem::query()
            ->join(
                'province_distributions',
                'province_distributions.id',
                '=',
                'province_distribution_items.province_distribution_id'
            )
            ->where(
                'province_distributions.status',
                '!=',
                'Cancelled'
            )
            ->selectRaw(
                '
            province_distributions.province_id,
            SUM(province_distribution_items.quantity)
                AS total_quantity
            '
            )
            ->groupBy(
                'province_distributions.province_id'
            )
            ->pluck(
                'total_quantity',
                'province_id'
            );

        return [
            'labels' => $provinces
                ->pluck('name')
                ->values()
                ->all(),

            'data' => $provinces
                ->map(
                    fn(Province $province): int => (int) (
                        $distributed[$province->id]
                        ?? 0
                    )
                )
                ->values()
                ->all(),
        ];
    }

    /**
     * @return Collection<int, PurchaseOrder>
     */
    private function accountingLatestPurchaseOrders(): Collection
    {
        return PurchaseOrder::query()
            ->with([
                'supplier',
                'creator',
            ])
            ->latest('po_date')
            ->latest('id')
            ->take(7)
            ->get();
    }

    /**
     * @return Collection<int, Supplier>
     */
    private function accountingSupplierSummary(): Collection
    {
        return Supplier::query()
            ->withCount('purchaseOrders')
            ->withSum(
                'purchaseOrders',
                'total_amount'
            )
            ->where(
                'is_active',
                true
            )
            ->orderByDesc(
                'purchase_orders_sum_total_amount'
            )
            ->take(5)
            ->get();
    }
}
