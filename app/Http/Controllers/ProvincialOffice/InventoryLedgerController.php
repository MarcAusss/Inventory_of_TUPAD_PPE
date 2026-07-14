<?php

namespace App\Http\Controllers\ProvincialOffice;

use App\Http\Controllers\Controller;
use App\Models\DeliveryReceipt;
use App\Services\InventoryMovementReportService;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class InventoryLedgerController extends Controller
{
    /**
     * Display the Delivery Receipt-based inventory ledger.
     */
    public function index(
        Request $request,
        InventoryMovementReportService $reportService
    ): View {
        $provinceId = $this->provinceId();

        $year = $this->resolveYear(
            $request
        );

        $deliveryReceiptId = max(
            0,
            (int) $request->query(
                'delivery_receipt_id',
                0
            )
        );

        $deliveryReceipts = $this->deliveryReceiptOptions(
            provinceId: $provinceId,
            year: $year
        );

        $selectedDeliveryReceipt = null;

        $reportRows = collect();

        if ($deliveryReceiptId > 0) {
            $selectedDeliveryReceipt =
                $this->findSelectedReceipt(
                    provinceId: $provinceId,
                    deliveryReceiptId: $deliveryReceiptId,
                    year: $year
                );

            $reportRows = $reportService
                ->buildForDeliveryReceipt(
                    provinceId: $provinceId,
                    deliveryReceiptId: $deliveryReceiptId
                );
        }

        $summary = $this->buildSummary(
            $reportRows
        );

        $availableYears = $this->availableYears(
            $provinceId,
            $year
        );

        $rows = $this->paginate(
            items: $reportRows,
            perPage: 10,
            request: $request,
            pageName: 'page'
        );

        return view(
            'provincial.inventory-ledger.index',
            compact(
                'rows',
                'summary',
                'year',
                'availableYears',
                'deliveryReceiptId',
                'deliveryReceipts',
                'selectedDeliveryReceipt'
            )
        );
    }

    /**
     * Print every project-distribution row belonging to one selected
     * Delivery Receipt.
     *
     * This deliberately does not paginate the report.
     */
    public function print(
        Request $request,
        InventoryMovementReportService $reportService
    ): View {
        $provinceId = $this->provinceId();

        $deliveryReceiptId = max(
            0,
            (int) $request->query(
                'delivery_receipt_id',
                0
            )
        );

        abort_if(
            $deliveryReceiptId <= 0,
            422,
            'Select a Delivery Receipt before printing the inventory ledger.'
        );

        $year = $this->resolveYear(
            $request
        );

        $selectedDeliveryReceipt =
            $this->findSelectedReceipt(
                provinceId: $provinceId,
                deliveryReceiptId: $deliveryReceiptId,
                year: $year
            );

        /*
         * Use the same report builder as the screen so the printed
         * values cannot differ from the UI.
         */
        $rows = $reportService
            ->buildForDeliveryReceipt(
                provinceId: $provinceId,
                deliveryReceiptId: $deliveryReceiptId
            );

        $summary = $this->buildSummary(
            $rows
        );

        $user = Auth::user();

        $provinceName =
            $user?->province?->name
            ?? $selectedDeliveryReceipt
                ->province?->name
            ?? 'Provincial Office';

        $preparedBy =
            $user?->name
            ?? '';

        /*
         * Leave Reviewed by blank for now. You can later retrieve this
         * from a database setting or an authorized signatory record.
         */
        $reviewedBy = '';

        $printedAt = now();

        return view(
            'provincial.inventory-ledger.print',
            compact(
                'rows',
                'summary',
                'year',
                'selectedDeliveryReceipt',
                'provinceName',
                'preparedBy',
                'reviewedBy',
                'printedAt'
            )
        );
    }

    /**
     * Retrieve one selected receipt belonging to the authenticated
     * Provincial Office.
     */
    private function findSelectedReceipt(
        int $provinceId,
        int $deliveryReceiptId,
        int $year
    ): DeliveryReceipt {
        return DeliveryReceipt::query()
            ->with([
                'province',
                'items.item',
                'receivedByUser',

                'provinceDistribution.items.item',

                'provinceDistribution.distributionBatch.callOff',

                'provinceDistribution.distributionBatch.purchaseOrder.supplier',
            ])
            ->where(
                'province_id',
                $provinceId
            )
            ->where(
                'status',
                'Received'
            )
            ->whereNotNull(
                'province_distribution_id'
            )
            ->whereYear(
                'delivery_date',
                $year
            )
            ->whereHas(
                'provinceDistribution.distributionBatch.callOff',
                fn ($query) => $query->whereIn(
                    'status',
                    [
                        'Approved',
                        'Completed',
                    ]
                )
            )
            ->whereKey(
                $deliveryReceiptId
            )
            ->firstOrFail();
    }

    /**
     * Delivery Receipt dropdown options.
     *
     * @return Collection<int, DeliveryReceipt>
     */
    private function deliveryReceiptOptions(
        int $provinceId,
        int $year
    ): Collection {
        return DeliveryReceipt::query()
            ->with([
                'provinceDistribution.distributionBatch.callOff',

                'provinceDistribution.distributionBatch.purchaseOrder.supplier',
            ])
            ->where(
                'province_id',
                $provinceId
            )
            ->where(
                'status',
                'Received'
            )
            ->whereNotNull(
                'province_distribution_id'
            )
            ->whereYear(
                'delivery_date',
                $year
            )
            ->whereHas(
                'provinceDistribution.distributionBatch.callOff',
                fn ($query) => $query->whereIn(
                    'status',
                    [
                        'Approved',
                        'Completed',
                    ]
                )
            )
            ->orderByDesc(
                'delivery_date'
            )
            ->orderByDesc('id')
            ->get();
    }

    /**
     * Get report years from received Delivery Receipts.
     *
     * @return Collection<int, int>
     */
    private function availableYears(
        int $provinceId,
        int $selectedYear
    ): Collection {
        $years = DeliveryReceipt::query()
            ->where(
                'province_id',
                $provinceId
            )
            ->where(
                'status',
                'Received'
            )
            ->whereNotNull(
                'delivery_date'
            )
            ->selectRaw(
                'YEAR(delivery_date) AS report_year'
            )
            ->distinct()
            ->orderByDesc(
                'report_year'
            )
            ->pluck(
                'report_year'
            )
            ->map(
                fn ($year): int => (int) $year
            )
            ->values();

        if (! $years->contains($selectedYear)) {
            $years->prepend(
                $selectedYear
            );
        }

        return $years;
    }

    /**
     * Resolve the report year.
     */
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
     * Build summary totals for one selected Delivery Receipt.
     *
     * @param  Collection<int, array<string, mixed>>  $rows
     * @return array<string, int>
     */
    private function buildSummary(
        Collection $rows
    ): array {
        if ($rows->isEmpty()) {
            return [
                'row_count' => 0,
                'call_off_count' => 0,
                'project_count' => 0,
                'beginning_total' => 0,
                'actual_total' => 0,
                'ending_total' => 0,
            ];
        }

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

            /*
             * The first project row begins with the quantity physically
             * received in the selected Delivery Receipt.
             */
            'beginning_total' => (int) (
                $rows->first()[
                    'beginning_total'
                ]
                ?? 0
            ),

            /*
             * Sum of all project distributions from this exact receipt.
             */
            'actual_total' => (int) $rows->sum(
                'actual_total'
            ),

            /*
             * The final row contains the current remaining DR balance.
             */
            'ending_total' => (int) (
                $rows->last()[
                    'ending_total'
                ]
                ?? 0
            ),
        ];
    }

    /**
     * Get authenticated Provincial Office province ID.
     */
    private function provinceId(): int
    {
        $provinceId = Auth::user()
            ?->province_id;

        abort_unless(
            $provinceId,
            403,
            'This Provincial Office account has no assigned province.'
        );

        return (int) $provinceId;
    }

    /**
     * Paginate an in-memory report collection.
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
                'path' => $request->url(),

                'query' => $request->query(),

                'pageName' => $pageName,
            ]
        );
    }
}
