<?php

namespace App\Http\Controllers\TSSD;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\Province;
use App\Models\ProvinceDistribution;
use App\Services\InventoryMovementReportService;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class InventoryLedgerController extends Controller
{
    /**
     * Display the Accounting Unit read-only inventory report.
     */
    public function index(
        Request $request,
        InventoryMovementReportService $reportService
    ): View {
        $currentYear = (int) now()->year;

        $year = $this->resolveYear(
            $request,
            $currentYear
        );

        $provinceId = $this->resolveProvinceId(
            $request
        );

        $callOffId = max(
            0,
            (int) $request->query(
                'province_distribution_id',
                0
            )
        );

        $search = trim(
            (string) $request->query(
                'search',
                ''
            )
        );

        /*
        |--------------------------------------------------------------------------
        | Provincial Office options
        |--------------------------------------------------------------------------
        */

        $provinces = Province::query()
            ->orderBy('name')
            ->get();

        $selectedProvince = $provinceId
            ? $provinces->firstWhere(
                'id',
                $provinceId
            )
            : null;

        /*
        |--------------------------------------------------------------------------
        | Call-Off allocation options
        |--------------------------------------------------------------------------
        */

        $callOffAllocations = ProvinceDistribution::query()
            ->with([
                'province',
                'distributionBatch.callOff',
                'distributionBatch.purchaseOrder.supplier',
            ])
            ->when(
                $provinceId,
                fn ($query) => $query->where(
                    'province_id',
                    $provinceId
                )
            )
            ->whereHas(
                'distributionBatch.callOff',
                function ($query): void {
                    $query->whereIn(
                        'status',
                        [
                            'Approved',
                            'Completed',
                        ]
                    );
                }
            )
            ->whereYear(
                'scheduled_delivery_date',
                $year
            )
            ->orderByDesc(
                'scheduled_delivery_date'
            )
            ->orderByDesc('id')
            ->get();

        if ($callOffId > 0) {
            abort_unless(
                $callOffAllocations->contains(
                    fn (
                        ProvinceDistribution $allocation
                    ): bool => (int) $allocation->id
                        === $callOffId
                ),
                404,
                'The selected Call-Off allocation is unavailable.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Build ledger rows
        |--------------------------------------------------------------------------
        |
        | The existing report service already generates:
        |
        | - beginning
        | - actual
        | - ending
        | - Call-Off
        | - supplier
        | - project
        | - province
        |
        */

        $reportRows = collect();

        $provinceIds = $provinceId
            ? collect([$provinceId])
            : $provinces->pluck('id');

        foreach ($provinceIds as $reportProvinceId) {
            $provinceRows = $reportService
                ->buildForProvince(
                    (int) $reportProvinceId
                );

            $reportRows = $reportRows->concat(
                $provinceRows
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Apply Accounting filters
        |--------------------------------------------------------------------------
        */

        $reportRows = $reportRows
            ->filter(
                function (array $row) use (
                    $callOffId,
                    $year,
                    $search
                ): bool {
                    if (
                        $callOffId > 0
                        && (int) (
                            $row[
                                'province_distribution_id'
                            ]
                            ?? 0
                        ) !== $callOffId
                    ) {
                        return false;
                    }

                    $rowDate =
                        $row['movement_date']
                        ?? $row['delivery_date']
                        ?? null;

                    if (
                        $rowDate
                        && (int) $rowDate->format('Y')
                            !== $year
                    ) {
                        return false;
                    }

                    if ($search === '') {
                        return true;
                    }

                    $haystack = strtolower(
                        implode(
                            ' ',
                            [
                                $row['province_name'] ?? '',
                                $row['call_off_number'] ?? '',
                                $row['supplier_name'] ?? '',
                                $row['delivery_receipt_number'] ?? '',
                                $row['project_code'] ?? '',
                                $row['project_title'] ?? '',
                                $row['location'] ?? '',
                            ]
                        )
                    );

                    return str_contains(
                        $haystack,
                        strtolower($search)
                    );
                }
            )
            ->sortBy([
                [
                    'province_name',
                    'asc',
                ],
                [
                    'movement_date',
                    'asc',
                ],
                [
                    'supply_designation_id',
                    'asc',
                ],
            ])
            ->values();

        /*
        |--------------------------------------------------------------------------
        | Summary
        |--------------------------------------------------------------------------
        */

        $summary = $this->buildLedgerSummary(
            $reportRows
        );

        /*
        |--------------------------------------------------------------------------
        | Pagination
        |--------------------------------------------------------------------------
        */

        $rows = $this->paginateCollection(
            collection: $reportRows,
            request: $request,
            perPage: 20
        );

        /*
        |--------------------------------------------------------------------------
        | Supply Unit central inventory
        |--------------------------------------------------------------------------
        |
        | This reads the central Supply Unit inventory table, not provincial
        | inventory movements.
        |
        */

        $supplyInventory = DB::table('inventory')
            ->join(
                'items',
                'items.id',
                '=',
                'inventory.item_id'
            )
            ->select([
                'items.id as item_id',
                'items.item_name',
                'items.label',
                'items.unit_of_measurement',
                'inventory.quantity',
            ])
            ->where(
                'items.is_active',
                true
            )
            ->orderBy('items.id')
            ->get();

        $supplyInventoryTotal = (int) $supplyInventory
            ->sum('quantity');

        /*
        |--------------------------------------------------------------------------
        | Current Provincial Office inventories
        |--------------------------------------------------------------------------
        */

        $provincialInventories = DB::table('provincial_inventories')
            ->join('provinces', 'provinces.id', '=', 'provincial_inventories.province_id')
            ->join('items', 'items.id', '=', 'provincial_inventories.item_id')
            ->select([
                'provinces.id as province_id',
                'provinces.name as province_name',
                'items.id as item_id',
                'items.item_name',
                'items.label',
                'items.unit_of_measurement',
                'provincial_inventories.quantity',
            ])
            ->when(
                $provinceId,
                fn ($query) => $query->where('provinces.id', $provinceId)
            )
            ->orderBy('provinces.name')
            ->orderBy('items.id')
            ->get();

        $provincialInventoryTotal = (int) $provincialInventories->sum('quantity');

        $viewerLabel = 'TSSD Unit';
        $viewerDescription = 'Read-only monitoring of Supply inventory, all Provincial Office balances, TSSD allocation and receiving transactions, and project PPE distributions.';

        /*
        |--------------------------------------------------------------------------
        | Available years
        |--------------------------------------------------------------------------
        */

        $availableYears = $this->availableYears(
            $currentYear
        );

        return view(
            'accounting.inventory-ledger.index',
            compact(
                'rows',
                'summary',
                'provinces',
                'selectedProvince',
                'provinceId',
                'callOffId',
                'callOffAllocations',
                'year',
                'currentYear',
                'availableYears',
                'search',
                'supplyInventory',
                'supplyInventoryTotal',
                'provincialInventories',
                'provincialInventoryTotal',
                'viewerLabel',
                'viewerDescription'
            )
        );
    }

    /**
     * Resolve selected year.
     */
    private function resolveYear(
        Request $request,
        int $currentYear
    ): int {
        $year = (int) $request->query(
            'year',
            $currentYear
        );

        if (
            $year < 2000
            || $year > 2100
        ) {
            return $currentYear;
        }

        return $year;
    }

    /**
     * Resolve selected province.
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
     * Build the summary expected by the Accounting Blade.
     *
     * @param Collection<int, array<string, mixed>> $rows
     *
     * @return array<string, int>
     */
    private function buildLedgerSummary(
        Collection $rows
    ): array {
        if ($rows->isEmpty()) {
            return [
                'row_count' => 0,
                'province_count' => 0,
                'call_off_count' => 0,
                'project_count' => 0,
                'beginning_total' => 0,
                'actual_total' => 0,
                'ending_total' => 0,
            ];
        }

        return [
            'row_count' =>
                $rows->count(),

            'province_count' =>
                $rows
                    ->pluck('province_id')
                    ->filter()
                    ->unique()
                    ->count(),

            'call_off_count' =>
                $rows
                    ->pluck(
                        'province_distribution_id'
                    )
                    ->filter()
                    ->unique()
                    ->count(),

            'project_count' =>
                $rows
                    ->pluck(
                        'supply_designation_id'
                    )
                    ->filter()
                    ->unique()
                    ->count(),

            'beginning_total' =>
                (int) $rows->sum(
                    function (array $row): int {
                        return (int) collect(
                            $row['beginning'] ?? []
                        )->sum();
                    }
                ),

            'actual_total' =>
                (int) $rows->sum(
                    function (array $row): int {
                        return (int) collect(
                            $row['actual'] ?? []
                        )->sum();
                    }
                ),

            'ending_total' =>
                (int) $rows->sum(
                    function (array $row): int {
                        return (int) collect(
                            $row['ending'] ?? []
                        )->sum();
                    }
                ),
        ];
    }

    /**
     * Return years containing inventory data.
     *
     * @return Collection<int, int>
     */
    private function availableYears(
        int $currentYear
    ): Collection {
        $years = DB::table(
            'inventory_movements'
        )
            ->selectRaw(
                'YEAR(movement_date) AS movement_year'
            )
            ->whereNotNull(
                'movement_date'
            )
            ->distinct()
            ->orderByDesc(
                'movement_year'
            )
            ->pluck(
                'movement_year'
            )
            ->map(
                fn ($year): int => (int) $year
            );

        if (! $years->contains($currentYear)) {
            $years->prepend(
                $currentYear
            );
        }

        return $years
            ->unique()
            ->values();
    }

    /**
     * Paginate an in-memory collection.
     *
     * @param Collection<int, array<string, mixed>> $collection
     */
    private function paginateCollection(
        Collection $collection,
        Request $request,
        int $perPage
    ): LengthAwarePaginator {
        $page = LengthAwarePaginator::resolveCurrentPage(
            'page'
        );

        $pageItems = $collection
            ->forPage(
                $page,
                $perPage
            )
            ->values();

        return new LengthAwarePaginator(
            $pageItems,
            $collection->count(),
            $perPage,
            $page,
            [
                'path' => $request->url(),
                'query' => $request->query(),
                'pageName' => 'page',
            ]
        );
    }
}