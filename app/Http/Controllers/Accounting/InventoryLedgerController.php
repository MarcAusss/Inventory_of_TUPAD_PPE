<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\InventoryMovement;
use App\Models\Item;
use App\Models\Province;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class InventoryLedgerController extends Controller
{
    /**
     * Display the read-only inventory ledger for Accounting Unit.
     */
    public function index(Request $request): View
    {
        $currentYear = now()->year;

        $year = $this->resolveYear(
            $request,
            $currentYear
        );

        $provinceId = $this->resolveProvinceId(
            $request
        );

        $search = trim(
            (string) $request->query('search')
        );

        $provinces = Province::query()
            ->orderBy('name')
            ->get();

        $selectedProvince = $provinceId
            ? $provinces->firstWhere('id', $provinceId)
            : null;

        $movements = InventoryMovement::query()
            ->with([
                'province',
                'item',
                'creator',
                'deliveryReceipt.provinceDistribution.distributionBatch.callOff',
                'deliveryReceipt.provinceDistribution.distributionBatch.purchaseOrder.supplier',
                'supplyDesignation',
            ])
            ->when(
                $provinceId,
                fn (Builder $query): Builder => $query->where(
                    'province_id',
                    $provinceId
                )
            )
            ->whereYear(
                'movement_date',
                $year
            )
            ->when(
                $search,
                fn (Builder $query): Builder => $this->applySearch(
                    $query,
                    $search
                )
            )
            ->latest('movement_date')
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        $summary = $this->buildSummary(
            $provinceId,
            $year
        );

        $totals = [
            'beginning_inventory' => collect($summary)->sum(
                'beginning_inventory'
            ),

            'received_inventory' => collect($summary)->sum(
                'received_inventory'
            ),

            'issued_inventory' => collect($summary)->sum(
                'issued_inventory'
            ),

            'actual_inventory' => collect($summary)->sum(
                'actual_inventory'
            ),

            'ending_inventory' => collect($summary)->sum(
                'ending_inventory'
            ),
        ];

        $availableYears = $this->availableYears(
            $provinceId,
            $currentYear
        );

        return view(
            'accounting.inventory-ledger.index',
            compact(
                'movements',
                'summary',
                'totals',
                'provinces',
                'selectedProvince',
                'provinceId',
                'year',
                'currentYear',
                'availableYears',
                'search'
            )
        );
    }

    /**
     * Resolve and validate the selected year.
     */
    private function resolveYear(
        Request $request,
        int $currentYear
    ): int {
        $year = (int) $request->query(
            'year',
            $currentYear
        );

        if ($year < 2000 || $year > 2100) {
            return $currentYear;
        }

        return $year;
    }

    /**
     * Resolve and validate the selected province.
     */
    private function resolveProvinceId(
        Request $request
    ): ?int {
        $provinceId = $request->query(
            'province_id'
        );

        if (
            $provinceId === null
            || $provinceId === ''
        ) {
            return null;
        }

        $provinceId = (int) $provinceId;

        abort_unless(
            Province::query()
                ->whereKey($provinceId)
                ->exists(),
            404,
            'The selected province does not exist.'
        );

        return $provinceId;
    }

    /**
     * Apply movement search filters.
     */
    private function applySearch(
        Builder $query,
        string $search
    ): Builder {
        return $query->where(
            function (Builder $query) use ($search): void {
                $query
                    ->where(
                        'reference_number',
                        'like',
                        "%{$search}%"
                    )
                    ->orWhere(
                        'description',
                        'like',
                        "%{$search}%"
                    )
                    ->orWhere(
                        'remarks',
                        'like',
                        "%{$search}%"
                    )
                    ->orWhereHas(
                        'item',
                        function (
                            Builder $itemQuery
                        ) use ($search): void {
                            $itemQuery
                                ->where(
                                    'item_name',
                                    'like',
                                    "%{$search}%"
                                )
                                ->orWhere(
                                    'label',
                                    'like',
                                    "%{$search}%"
                                )
                                ->orWhere(
                                    'unit_of_measurement',
                                    'like',
                                    "%{$search}%"
                                );
                        }
                    )
                    ->orWhereHas(
                        'province',
                        fn (
                            Builder $provinceQuery
                        ) => $provinceQuery->where(
                            'name',
                            'like',
                            "%{$search}%"
                        )
                    )
                    ->orWhereHas(
                        'deliveryReceipt',
                        fn (
                            Builder $receiptQuery
                        ) => $receiptQuery->where(
                            'dr_number',
                            'like',
                            "%{$search}%"
                        )
                    )
                    ->orWhereHas(
                        'supplyDesignation',
                        function (
                            Builder $designationQuery
                        ) use ($search): void {
                            $designationQuery
                                ->where(
                                    'project_code',
                                    'like',
                                    "%{$search}%"
                                )
                                ->orWhere(
                                    'project_title',
                                    'like',
                                    "%{$search}%"
                                );
                        }
                    );
            }
        );
    }

    /**
     * Build the inventory summary.
     *
     * When no province is selected, totals are combined across all provinces.
     *
     * @return array<int, array<string, mixed>>
     */
    private function buildSummary(
        ?int $provinceId,
        int $year
    ): array {
        $items = Item::query()
            ->where('is_active', true)
            ->orderBy('id')
            ->get();

        return $items
            ->map(
                function (Item $item) use (
                    $provinceId,
                    $year
                ): array {
                    $beginningInventory =
                        $this->balanceBeforeYear(
                            $provinceId,
                            $item->id,
                            $year
                        );

                    $receivedInventory =
                        $this->sumMovements(
                            $provinceId,
                            $item->id,
                            $year,
                            [
                                'IN',
                                'ADJUSTMENT_IN',
                            ]
                        );

                    $issuedInventory =
                        $this->sumMovements(
                            $provinceId,
                            $item->id,
                            $year,
                            [
                                'OUT',
                                'ADJUSTMENT_OUT',
                            ]
                        );

                    $actualInventory =
                        $beginningInventory
                        + $receivedInventory
                        - $issuedInventory;

                    return [
                        'item' => $item,

                        'beginning_inventory' => $beginningInventory,

                        'received_inventory' => $receivedInventory,

                        'issued_inventory' => $issuedInventory,

                        'actual_inventory' => $actualInventory,

                        'ending_inventory' => $actualInventory,
                    ];
                }
            )
            ->all();
    }

    /**
     * Calculate inventory balance before January 1 of the selected year.
     */
    private function balanceBeforeYear(
        ?int $provinceId,
        int $itemId,
        int $year
    ): int {
        $stockIn = InventoryMovement::query()
            ->when(
                $provinceId,
                fn (Builder $query): Builder => $query->where(
                    'province_id',
                    $provinceId
                )
            )
            ->where(
                'item_id',
                $itemId
            )
            ->whereDate(
                'movement_date',
                '<',
                "{$year}-01-01"
            )
            ->whereIn(
                'movement_type',
                [
                    'IN',
                    'ADJUSTMENT_IN',
                ]
            )
            ->sum('quantity');

        $stockOut = InventoryMovement::query()
            ->when(
                $provinceId,
                fn (Builder $query): Builder => $query->where(
                    'province_id',
                    $provinceId
                )
            )
            ->where(
                'item_id',
                $itemId
            )
            ->whereDate(
                'movement_date',
                '<',
                "{$year}-01-01"
            )
            ->whereIn(
                'movement_type',
                [
                    'OUT',
                    'ADJUSTMENT_OUT',
                ]
            )
            ->sum('quantity');

        return (int) $stockIn
            - (int) $stockOut;
    }

    /**
     * Sum movements for one item and year.
     *
     * @param  array<int, string>  $movementTypes
     */
    private function sumMovements(
        ?int $provinceId,
        int $itemId,
        int $year,
        array $movementTypes
    ): int {
        return (int) InventoryMovement::query()
            ->when(
                $provinceId,
                fn (Builder $query): Builder => $query->where(
                    'province_id',
                    $provinceId
                )
            )
            ->where(
                'item_id',
                $itemId
            )
            ->whereYear(
                'movement_date',
                $year
            )
            ->whereIn(
                'movement_type',
                $movementTypes
            )
            ->sum('quantity');
    }

    /**
     * Return years containing inventory data.
     *
     * @return Collection<int, int>
     */
    private function availableYears(
        ?int $provinceId,
        int $currentYear
    ): Collection {
        $years = InventoryMovement::query()
            ->when(
                $provinceId,
                fn (Builder $query): Builder => $query->where(
                    'province_id',
                    $provinceId
                )
            )
            ->selectRaw(
                'YEAR(movement_date) as movement_year'
            )
            ->distinct()
            ->orderByDesc('movement_year')
            ->pluck('movement_year')
            ->map(
                fn ($value): int => (int) $value
            );

        if (! $years->contains($currentYear)) {
            $years->prepend($currentYear);
        }

        return $years->unique()->values();
    }
}
