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
         * Historical Call-Off movement rows.
         *
         * Completed project rows now use the stored
         * InventoryMovement Call-Off balance snapshots.
         */
        $reportRows = $reportService
            ->buildForProvince(
                (int) $provinceId
            );

        /*
         * Year filter.
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
         * Call-Off filter.
         */
        if ($callOffId > 0) {
            $reportRows = $reportRows
                ->filter(
                    fn (
                        array $row
                    ): bool => (int) (
                        $row[
                            'province_distribution_id'
                        ]
                        ?? 0
                    ) === $callOffId
                )
                ->values();
        }

        /*
         * Search filter.
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
         * Sort report chronologically.
         */
        $reportRows = $reportRows
            ->sortBy(
                function (
                    array $row
                ): string {
                    $movementDate = (
                        $row['movement_date']
                        ?? null
                    )
                        ?->format('Y-m-d')
                        ?? '0000-00-00';

                    $allocationId = str_pad(
                        (string) (
                            $row[
                                'province_distribution_id'
                            ]
                            ?? 0
                        ),
                        20,
                        '0',
                        STR_PAD_LEFT
                    );

                    $designationId = str_pad(
                        (string) (
                            $row[
                                'supply_designation_id'
                            ]
                            ?? 0
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

        $summary = $this->buildSummary(
            $reportRows
        );

        $availableYears = $this->availableYears(
            $reportService,
            (int) $provinceId
        );

        $callOffAllocations =
            $this->callOffAllocations(
                (int) $provinceId
            );

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

    private function resolveYear(
        Request $request
    ): int {
        $currentYear = (int) now()->year;

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
     * Build summary values.
     *
     * IMPORTANT:
     *
     * Beginning total is based on the first historical
     * row of every Call-Off.
     *
     * Ending total is based on the last historical
     * row of every Call-Off.
     *
     * This prevents repeated running balances from
     * being added together.
     *
     * @param Collection<int, array<string, mixed>> $rows
     *
     * @return array<string, int>
     */
    private function buildSummary(
        Collection $rows
    ): array {
        $groupedByCallOff = $rows
            ->groupBy(
                'province_distribution_id'
            );

        return [
            'row_count' =>
                $rows->count(),

            'call_off_count' =>
                $groupedByCallOff->count(),

            'project_count' =>
                $rows
                    ->pluck(
                        'supply_designation_id'
                    )
                    ->filter()
                    ->unique()
                    ->count(),

            'beginning_total' =>
                (int) $groupedByCallOff
                    ->map(
                        fn (
                            Collection $callOffRows
                        ): int => (int) (
                            $callOffRows
                                ->first()[
                                    'beginning_total'
                                ]
                            ?? 0
                        )
                    )
                    ->sum(),

            'actual_total' =>
                (int) $rows->sum(
                    'actual_total'
                ),

            'ending_total' =>
                (int) $groupedByCallOff
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
     * Available report years.
     *
     * @return Collection<int, int>
     */
    private function availableYears(
        InventoryMovementReportService $reportService,
        int $provinceId
    ): Collection {
        $currentYear = (int) now()->year;

        $years = $reportService
            ->buildForProvince(
                $provinceId
            )
            ->map(
                function (
                    array $row
                ): ?int {
                    $movementDate =
                        $row['movement_date']
                        ?? null;

                    if (! $movementDate) {
                        return null;
                    }

                    return (int) $movementDate
                        ->format('Y');
                }
            )
            ->filter()
            ->unique()
            ->sortDesc()
            ->values();

        if (
            ! $years->contains(
                $currentYear
            )
        ) {
            $years->prepend(
                $currentYear
            );
        }

        return $years;
    }

    /**
     * Call-Off filter options.
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
     * Paginate the generated report collection.
     *
     * @template TKey of array-key
     * @template TValue
     *
     * @param Collection<TKey, TValue> $items
     *
     * @return LengthAwarePaginator<TKey, TValue>
     */
    private function paginate(
        Collection $items,
        int $perPage,
        Request $request,
        string $pageName
    ): LengthAwarePaginator {
        $currentPage =
            LengthAwarePaginator::resolveCurrentPage(
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
                'path' =>
                    $request->url(),

                'query' =>
                    $request->query(),

                'pageName' =>
                    $pageName,
            ]
        );
    }
}