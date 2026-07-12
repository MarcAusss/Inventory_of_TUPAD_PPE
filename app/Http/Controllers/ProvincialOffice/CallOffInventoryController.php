<?php

namespace App\Http\Controllers\ProvincialOffice;

use App\Http\Controllers\Controller;
use App\Models\ProvinceDistribution;
use App\Services\CallOffInventoryService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class CallOffInventoryController extends Controller
{
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

        $allocations = ProvinceDistribution::query()
            ->with([
                'province',
                'distributionBatch.callOff',
                'distributionBatch.purchaseOrder.supplier',
                'items.item',
                'deliveryReceipts.items',
                'supplyDesignations.items',
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
                                    fn (Builder $callOffQuery) => $callOffQuery->where(
                                        'call_off_number',
                                        'like',
                                        "%{$search}%"
                                    )
                                )
                                ->orWhereHas(
                                    'distributionBatch.purchaseOrder',
                                    fn (Builder $purchaseOrderQuery) => $purchaseOrderQuery->where(
                                        'po_number',
                                        'like',
                                        "%{$search}%"
                                    )
                                )
                                ->orWhereHas(
                                    'distributionBatch.purchaseOrder.supplier',
                                    fn (Builder $supplierQuery) => $supplierQuery->where(
                                        'supplier_name',
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

        $allocations->through(
            function (
                ProvinceDistribution $allocation
            ) use (
                $callOffInventoryService
            ): ProvinceDistribution {
                $balances = $callOffInventoryService
                    ->balances(
                        $allocation
                    );

                $allocation->setAttribute(
                    'call_off_balances',
                    $balances
                );

                $allocation->setAttribute(
                    'received_total',
                    (int) collect($balances)
                        ->sum('actual_received')
                );

                $allocation->setAttribute(
                    'distributed_total',
                    (int) collect($balances)
                        ->sum('previously_distributed')
                );

                $allocation->setAttribute(
                    'remaining_total',
                    (int) collect($balances)
                        ->sum('call_off_available')
                );

                $allocation->setAttribute(
                    'safe_available_total',
                    (int) collect($balances)
                        ->sum('available_for_projects')
                );

                return $allocation;
            }
        );

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
