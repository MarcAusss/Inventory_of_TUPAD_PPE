<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\DeliveryReceipt;
use App\Models\Province;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DeliveryReceiptSummaryController extends Controller
{
    public function __invoke(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));
        $provinceId = $request->integer('province_id') ?: null;
        $status = trim((string) $request->query('status', ''));

        $baseQuery = DeliveryReceipt::query()
            ->with([
                'province',
                'receivedByUser',
                'documents',
                'items.item',
                'provinceDistribution.province',
                'provinceDistribution.items.item',
                'provinceDistribution.distributionBatch.callOff',
                'provinceDistribution.distributionBatch.purchaseOrder.supplier',
            ])
            ->when(
                $provinceId,
                function (Builder $query) use ($provinceId): void {
                    $query->where(
                        function (Builder $subQuery) use ($provinceId): void {
                            $subQuery
                                ->where('province_id', $provinceId)
                                ->orWhereHas(
                                    'provinceDistribution',
                                    function (Builder $distributionQuery) use ($provinceId): void {
                                        $distributionQuery->where(
                                            'province_id',
                                            $provinceId
                                        );
                                    }
                                );
                        }
                    );
                }
            )
            ->when(
                $status !== '',
                function (Builder $query) use ($status): void {
                    $query->where('status', $status);
                }
            )
            ->when(
                $search !== '',
                function (Builder $query) use ($search): void {
                    $like = '%' . $search . '%';

                    $query->where(
                        function (Builder $subQuery) use ($like): void {
                            $subQuery
                                ->where('dr_number', 'like', $like)
                                ->orWhere('received_by', 'like', $like)
                                ->orWhere(
                                    'physical_receiver_name',
                                    'like',
                                    $like
                                )
                                ->orWhere('remarks', 'like', $like)
                                ->orWhereHas(
                                    'province',
                                    function (Builder $provinceQuery) use ($like): void {
                                        $provinceQuery->where(
                                            'name',
                                            'like',
                                            $like
                                        );
                                    }
                                )
                                ->orWhereHas(
                                    'provinceDistribution.province',
                                    function (Builder $provinceQuery) use ($like): void {
                                        $provinceQuery->where(
                                            'name',
                                            'like',
                                            $like
                                        );
                                    }
                                )
                                ->orWhereHas(
                                    'provinceDistribution.distributionBatch.callOff',
                                    function (Builder $callOffQuery) use ($like): void {
                                        $callOffQuery->where(
                                            'call_off_number',
                                            'like',
                                            $like
                                        );
                                    }
                                )
                                ->orWhereHas(
                                    'provinceDistribution.distributionBatch.purchaseOrder',
                                    function (Builder $purchaseOrderQuery) use ($like): void {
                                        $purchaseOrderQuery
                                            ->where('po_number', 'like', $like)
                                            ->orWhere(
                                                'nefa_number',
                                                'like',
                                                $like
                                            );
                                    }
                                )
                                ->orWhereHas(
                                    'provinceDistribution.distributionBatch.purchaseOrder.supplier',
                                    function (Builder $supplierQuery) use ($like): void {
                                        $supplierQuery->where(
                                            'supplier_name',
                                            'like',
                                            $like
                                        );
                                    }
                                );
                        }
                    );
                }
            );

        $summaryQuery = clone $baseQuery;

        $allReceipts = $summaryQuery->get();

        $totalReceipts = $allReceipts->count();

        $receivedCount = $allReceipts
            ->where('status', 'Received')
            ->count();

        $pendingCount = $allReceipts
            ->where('status', 'Pending')
            ->count();

        $totalReceivedPpe = (int) $allReceipts->sum(
            fn (DeliveryReceipt $receipt): int =>
                (int) $receipt->items->sum('received_quantity')
        );

        $receipts = (clone $baseQuery)
            ->orderByDesc('delivery_date')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        $provinces = Province::query()
            ->orderBy('name')
            ->get();

        return view(
            'accounting.delivery-receipts.index',
            compact(
                'search',
                'provinceId',
                'status',
                'provinces',
                'receipts',
                'totalReceipts',
                'receivedCount',
                'pendingCount',
                'totalReceivedPpe'
            )
        );
    }
}
