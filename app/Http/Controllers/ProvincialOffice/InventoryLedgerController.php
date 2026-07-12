<?php

namespace App\Http\Controllers\ProvincialOffice;

use App\Http\Controllers\Controller;
use App\Models\ProvinceDistribution;
use App\Services\InventoryMovementReportService;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class InventoryLedgerController extends Controller
{
    /**
     * Display Call-Off-based Inventory Movement History.
     *
     * Each project row follows this rule:
     *
     * Beginning Inventory
     * - Actual PPE distributed to the project
     * = Ending Inventory
     *
     * The ending inventory of the current row becomes the beginning
     * inventory of the next project row under the same Call-Off.
     */
    public function index(
        Request $request,
        InventoryMovementReportService $reportService
    ): View {
        $provinceId = Auth::user()?->province_id;

        abort_unless(
            $provinceId,
            403,
            'This Provincial Office account has no assigned province.'
        );

        $search = trim(
            (string) $request->query(
                'search',
                ''
            )
        );

        $year = $this->resolveYear(
            $request
        );

        $callOffId = (int) $request->query(
            'province_distribution_id',
            0
        );

        /*
         * Build the complete Call-Off report for this province.
         */
        $reportRows = $reportService
            ->buildForProvince(
                (int) $provinceId
            );

        /*
         * Restrict records to the selected year.
         *
         * Rows with no project designation use the last delivery date
         * as their movement date.
         */
        $reportRows = $reportRows
            ->filter(
                function (
                    array $row
                ) use (
                    $year
                ): bool {
                    $movementDate =
                        $row['movement_date']
                        ?? null;

                    if (! $movementDate) {
                        return false;
                    }

                    return (int) $movementDate
                        ->format('Y')
                        === $year;
                }
            )
            ->values();

        /*
         * Optional Call-Off filter.
         */
        if ($callOffId > 0) {
            $reportRows = $reportRows
                ->filter(
                    fn (
                        array $row
                    ): bool => (int) $row[
                            'province_distribution_id'
                        ] === $callOffId
                )
                ->values();
        }

        /*
         * Search Call-Off, supplier, DR, project, location, and title.
         */
        if ($search !== '') {
            $normalizedSearch = mb_strtolower(
                $search
            );

            $reportRows = $reportRows
                ->filter(
                    function (
                        array $row
                    ) use (
                        $normalizedSearch
                    ): bool {
                        $values = [
                            $row[
                                'call_off_number'
                            ] ?? '',

                            $row[
                                'supplier_name'
                            ] ?? '',

                            $row[
                                'delivery_receipt_numbers'
                            ] ?? '',

                            $row[
                                'project_code'
                            ] ?? '',

                            $row[
                                'project_title'
                            ] ?? '',

                            $row[
                                'location'
                            ] ?? '',
                        ];

                        foreach ($values as $value) {
                            if (
                                str_contains(
                                    mb_strtolower(
                                        (string) $value
                                    ),
                                    $normalizedSearch
                                )
                            ) {
                                return true;
                            }
                        }

                        return false;
                    }
                )
                ->values();
        }

        /*
         * Sort chronologically, with Call-Off allocation ID and project
         * designation ID as stable tie-breakers.
         */
        $reportRows = $reportRows
            ->sortBy(
                function (
                    array $row
                ): string {
                    $movementDate =
                        $row['movement_date']
                            ?->format(
                                'Y-m-d'
                            )
                        ?? '0000-00-00';

                    $allocationId = str_pad(
                        (string) (
                            $row[
                                'province_distribution_id'
                            ] ?? 0
                        ),
                        20,
                        '0',
                        STR_PAD_LEFT
                    );

                    $designationId = str_pad(
                        (string) (
                            $row[
                                'supply_designation_id'
                            ] ?? 0
                        ),
                        20,
                        '0',
                        STR_PAD_LEFT
                    );

                    return $movementDate
                        .'|'
                        .$allocationId
                        .'|'
                        .$designationId;
                }
            )
            ->values();

        /*
         * Calculate report-level totals.
         */
        $summary = $this->buildSummary(
            $reportRows
        );

        /*
         * Build the available year filter.
         */
        $availableYears = $this->availableYears(
            $reportService,
            (int) $provinceId
        );

        /*
         * Build the Call-Off dropdown.
         */
        $callOffAllocations =
            $this->callOffAllocations(
                (int) $provinceId
            );

        /*
         * Paginate the in-memory report collection.
         */
        $rows = $this->paginate(
            $reportRows,
            10,
            $request,
            'page'
        );

        return view(
            'provincial.inventory-ledger.index',
            compact(
                'rows',
                'summary',
                'year',
                'availableYears',
                'search',
                'callOffId',
                'callOffAllocations'
            )
        );
    }

    /**
     * Resolve and sanitize the selected year.
     */
    private function resolveYear(
        Request $request
    ): int {
        $currentYear =
            (int) now()->year;

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
     * Build report totals from the filtered rows.
     *
     * @param  Collection<int, array<string, mixed>>  $rows
     * @return array<string, int>
     */
    private function buildSummary(
        Collection $rows
    ): array {
        return [
            'row_count' => $rows->count(),

            'call_off_count' => $rows
                ->pluck(
                    'province_distribution_id'
                )
                ->filter()
                ->unique()
                ->count(),

            'project_count' => $rows
                ->pluck(
                    'supply_designation_id'
                )
                ->filter()
                ->unique()
                ->count(),

            'beginning_total' => (int) $rows->sum(
                'beginning_total'
            ),

            'actual_total' => (int) $rows->sum(
                'actual_total'
            ),

            /*
             * The last ending balance per Call-Off is more meaningful than
             * summing the ending balance of every row.
             */
            'ending_total' => (int) $rows
                ->groupBy(
                    'province_distribution_id'
                )
                ->map(
                    fn (
                        Collection $callOffRows
                    ): int => (int) (
                        $callOffRows
                            ->last()[
                                'ending_total'
                            ]
                        ?? 0
                    )
                )
                ->sum(),
        ];
    }

    /**
     * Get all years represented by the report.
     *
     * @return Collection<int, int>
     */
    private function availableYears(
        InventoryMovementReportService $reportService,
        int $provinceId
    ): Collection {
        $currentYear =
            (int) now()->year;

        $years = $reportService
            ->buildForProvince(
                $provinceId
            )
            ->map(
                fn (
                    array $row
                ): ?int => $row[
                        'movement_date'
                    ]
                        ?->format('Y')
                    ? (int) $row[
                        'movement_date'
                    ]->format('Y')
                    : null
            )
            ->filter()
            ->unique()
            ->sortDesc()
            ->values();

        if (! $years->contains($currentYear)) {
            $years->prepend(
                $currentYear
            );
        }

        return $years;
    }

    /**
     * Get Call-Off allocations for the filter dropdown.
     *
     * @return Collection<int, ProvinceDistribution>
     */
    private function callOffAllocations(
        int $provinceId
    ): Collection {
        return ProvinceDistribution::query()
            ->with([
                'distributionBatch.callOff',
                'distributionBatch.purchaseOrder.supplier',
            ])
            ->where(
                'province_id',
                $provinceId
            )
            ->whereHas(
                'distributionBatch.callOff'
            )
            ->latest('id')
            ->get();
    }

    /**
     * Paginate an in-memory collection.
     *
     * @template TKey of array-key
     * @template TValue
     *
     * @param  Collection<TKey, TValue>  $items
     * @return LengthAwarePaginator<TKey, TValue>
     */
    private function paginate(
        Collection $items,
        int $perPage,
        Request $request,
        string $pageName
    ): LengthAwarePaginator {
        $currentPage = LengthAwarePaginator::resolveCurrentPage(
            $pageName
        );

        $currentItems = $items
            ->forPage(
                $currentPage,
                $perPage
            )
            ->values();

        return new LengthAwarePaginator(
            $currentItems,
            $items->count(),
            $perPage,
            $currentPage,
            [
                'path' => $request->url(),

                'query' => $request->query(),

                'pageName' => $pageName,
            ]
        );
    }
}
