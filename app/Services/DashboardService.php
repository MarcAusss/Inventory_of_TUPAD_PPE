<?php

namespace App\Services;

use App\Models\CallOff;
use App\Models\DeliveryReceipt;
use App\Models\Item;
use App\Models\Province;
use App\Models\ProvinceDistribution;
use App\Models\ProvinceDistributionItem;
use App\Models\ProvincialInventory;
use App\Models\PurchaseOrder;
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
                    fn (Province $province): string => match ($province->name) {
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
                function (
                    Province $province
                ) use (
                    $rows,
                    $column
                ): int {
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
                    fn (string $status): int => (int) (
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
                function (
                    Province $province
                ): array {
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
                                fn (string $status): int => (int) (
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
                'suppliers' => Supplier::query()->count(),

                'items' => Item::query()->count(),

                'purchase_orders' => PurchaseOrder::query()->count(),

                'pending_calloffs' => CallOff::query()
                    ->where(
                        'status',
                        'Pending'
                    )
                    ->count(),
            ],

            'charts' => [],

            'recentActivities' => [],
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

        return [
            'statistics' => [
                'available_items' => $provinceId
                        ? (int) ProvincialInventory::query()
                            ->where(
                                'province_id',
                                $provinceId
                            )
                            ->sum('quantity')
                        : 0,

                'receipts' => $provinceId
                        ? DeliveryReceipt::query()
                            ->where(
                                'province_id',
                                $provinceId
                            )
                            ->count()
                        : 0,

                'designations' => $provinceId
                        ? SupplyDesignation::query()
                            ->where(
                                'province_id',
                                $provinceId
                            )
                            ->count()
                        : 0,
            ],

            'charts' => [],

            'recentActivities' => [],
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
                'purchase_orders' => PurchaseOrder::query()->count(),

                'po_value' => (float) PurchaseOrder::query()
                    ->sum('total_amount'),

                'distributions' => TssdDistributionBatch::query()
                    ->count(),

                'receipts' => DeliveryReceipt::query()
                    ->count(),

                'project_designations' => SupplyDesignation::query()
                    ->count(),
            ],

            'charts' => [],

            'recentActivities' => [],
        ];
    }
}
