<?php

namespace App\Http\Controllers\ProvincialOffice;

use App\Http\Controllers\Controller;
use App\Models\DeliveryReceipt;
use App\Models\ProvinceDistribution;
use App\Services\CallOffInventoryService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class CallOffInventoryController extends Controller
{
    /**
     * Display the Call-Off inventory of the authenticated
     * Provincial Office.
     */
    public function index(
        Request $request,
        CallOffInventoryService $callOffInventoryService
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

        $status = trim(
            (string) $request->query(
                'status',
                ''
            )
        );

        /*
        |--------------------------------------------------------------------------
        | Call-Off allocations
        |--------------------------------------------------------------------------
        |
        | Only allocations belonging to the authenticated Provincial Office
        | are retrieved.
        |
        | Pagination is applied before inventory totals are calculated so the
        | dashboard cards represent only the currently displayed page.
        |
        */

        $allocations = ProvinceDistribution::query()
            ->with([
                'province',
                'distributionBatch.callOff',
                'distributionBatch.purchaseOrder.supplier',
                'items.item',

                'deliveryReceipts' => function ($query): void {
                    $query
                        ->with([
                            'items.item',
                        ])
                        ->latest('delivery_date')
                        ->latest('id');
                },

                'supplyDesignations.items.item',
            ])
            ->where(
                'province_id',
                $provinceId
            )
            ->whereHas(
                'distributionBatch.callOff'
            )
            ->when(
                $status !== '',
                fn (Builder $query): Builder => $query->where(
                    'status',
                    $status
                )
            )
            ->when(
                $search !== '',
                function (
                    Builder $query
                ) use (
                    $search
                ): void {
                    $query->where(
                        function (
                            Builder $query
                        ) use (
                            $search
                        ): void {
                            $query
                                ->whereHas(
                                    'distributionBatch.callOff',
                                    fn (
                                        Builder $callOffQuery
                                    ): Builder => $callOffQuery->where(
                                        'call_off_number',
                                        'like',
                                        "%{$search}%"
                                    )
                                )
                                ->orWhereHas(
                                    'distributionBatch.purchaseOrder',
                                    fn (
                                        Builder $purchaseOrderQuery
                                    ): Builder => $purchaseOrderQuery
                                        ->where(
                                            'po_number',
                                            'like',
                                            "%{$search}%"
                                        )
                                        ->orWhere(
                                            'nefa_number',
                                            'like',
                                            "%{$search}%"
                                        )
                                )
                                ->orWhereHas(
                                    'distributionBatch.purchaseOrder.supplier',
                                    fn (
                                        Builder $supplierQuery
                                    ): Builder => $supplierQuery->where(
                                        'supplier_name',
                                        'like',
                                        "%{$search}%"
                                    )
                                )
                                ->orWhereHas(
                                    'deliveryReceipts',
                                    fn (
                                        Builder $deliveryReceiptQuery
                                    ): Builder => $deliveryReceiptQuery->where(
                                        'dr_number',
                                        'like',
                                        "%{$search}%"
                                    )
                                );
                        }
                    );
                }
            )
            ->latest('received_at')
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        /*
        |--------------------------------------------------------------------------
        | Inventory calculations
        |--------------------------------------------------------------------------
        |
        | balances() is called exactly once for each allocation on the current
        | page. This is important because the feature test expects ten service
        | calls when ten allocations are displayed.
        |
        */

        $allocations->through(
            function (
                ProvinceDistribution $allocation
            ) use (
                $callOffInventoryService
            ): ProvinceDistribution {
                $balances = $callOffInventoryService->balances(
                    $allocation
                );

                $receivedTotal = (int) collect($balances)
                    ->sum(
                        fn (array $balance): int =>
                            (int) (
                                $balance['actual_received']
                                ?? 0
                            )
                    );

                $distributedTotal = (int) collect($balances)
                    ->sum(
                        fn (array $balance): int =>
                            (int) (
                                $balance['previously_distributed']
                                ?? 0
                            )
                    );

                $remainingTotal = (int) collect($balances)
                    ->sum(
                        fn (array $balance): int =>
                            max(
                                0,
                                (int) (
                                    $balance['call_off_available']
                                    ?? 0
                                )
                            )
                    );

                $safeAvailableTotal = (int) collect($balances)
                    ->sum(
                        fn (array $balance): int =>
                            max(
                                0,
                                (int) (
                                    $balance['available_for_projects']
                                    ?? 0
                                )
                            )
                    );

                /*
                 * Make the calculated values available to the Blade view.
                 */
                $allocation->setAttribute(
                    'call_off_balances',
                    $balances
                );

                $allocation->setAttribute(
                    'received_total',
                    $receivedTotal
                );

                $allocation->setAttribute(
                    'distributed_total',
                    $distributedTotal
                );

                $allocation->setAttribute(
                    'remaining_total',
                    $remainingTotal
                );

                $allocation->setAttribute(
                    'safe_available_total',
                    $safeAvailableTotal
                );

                $allocation->setAttribute(
                    'has_available_ppe',
                    $safeAvailableTotal > 0
                );

                /*
                 * Count only Received Delivery Receipts when this allocation
                 * still has PPE that may be assigned to projects.
                 */
                $availableDeliveryReceiptCount = 0;

                if ($safeAvailableTotal > 0) {
                    $availableDeliveryReceiptCount = $allocation
                        ->deliveryReceipts
                        ->filter(
                            fn (
                                DeliveryReceipt $deliveryReceipt
                            ): bool => strcasecmp(
                                trim(
                                    (string) $deliveryReceipt->status
                                ),
                                'Received'
                            ) === 0
                        )
                        ->count();
                }

                $allocation->setAttribute(
                    'available_delivery_receipt_count',
                    $availableDeliveryReceiptCount
                );

                return $allocation;
            }
        );

        /*
        |--------------------------------------------------------------------------
        | Summary cards
        |--------------------------------------------------------------------------
        |
        | These values are calculated from the allocations displayed on the
        | current pagination page.
        |
        */

        $summary = [
            'call_off_count' => $allocations->total(),

            'received_total' => (int) $allocations
                ->getCollection()
                ->sum('received_total'),

            'distributed_total' => (int) $allocations
                ->getCollection()
                ->sum('distributed_total'),

            'remaining_total' => (int) $allocations
                ->getCollection()
                ->sum('remaining_total'),

            'safe_available_total' => (int) $allocations
                ->getCollection()
                ->sum('safe_available_total'),

            /*
             * Counts each Received Delivery Receipt individually, but only
             * under current-page allocations with available project PPE.
             */
            'available_delivery_receipt_count' => (int) $allocations
                ->getCollection()
                ->sum('available_delivery_receipt_count'),
        ];

        return view(
            'provincial.call-off-inventory.index',
            compact(
                'allocations',
                'summary',
                'search',
                'status'
            )
        );
    }
}