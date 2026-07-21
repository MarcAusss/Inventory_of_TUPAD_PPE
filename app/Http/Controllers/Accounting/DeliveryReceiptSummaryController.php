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
        /*
        |--------------------------------------------------------------------------
        | Filters
        |--------------------------------------------------------------------------
        */

        $search = trim(
            (string) $request->query('search', '')
        );

        $provinceId = $request->integer('province_id') ?: null;

        $status = trim(
            (string) $request->query('status', '')
        );

        /*
        |--------------------------------------------------------------------------
        | Base Delivery Receipt query
        |--------------------------------------------------------------------------
        */

        $baseQuery = DeliveryReceipt::query()
            ->with([
                'province',
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
                                    fn (Builder $distributionQuery) =>
                                        $distributionQuery->where(
                                            'province_id',
                                            $provinceId
                                        )
                                );
                        }
                    );
                }
            )
            ->when(
                $status !== '',
                fn (Builder $query) =>
                    $query->where('status', $status)
            )
            ->when(
                $search !== '',
                function (Builder $query) use ($search): void {
                    $query->where(
                        function (Builder $subQuery) use ($search): void {
                            $subQuery
                                ->where(
                                    'dr_number',
                                    'like',
                                    '%' . $search . '%'
                                )
                                ->orWhere(
                                    'received_by',
                                    'like',
                                    '%' . $search . '%'
                                )
                                ->orWhere(
                                    'physical_receiver_name',
                                    'like',
                                    '%' . $search . '%'
                                )
                                ->orWhereHas(
                                    'province',
                                    fn (Builder $provinceQuery) =>
                                        $provinceQuery->where(
                                            'name',
                                            'like',
                                            '%' . $search . '%'
                                        )
                                )
                                ->orWhereHas(
                                    'provinceDistribution.province',
                                    fn (Builder $provinceQuery) =>
                                        $provinceQuery->where(
                                            'name',
                                            'like',
                                            '%' . $search . '%'
                                        )
                                )
                                ->orWhereHas(
                                    'provinceDistribution.distributionBatch.callOff',
                                    fn (Builder $callOffQuery) =>
                                        $callOffQuery->where(
                                            'call_off_number',
                                            'like',
                                            '%' . $search . '%'
                                        )
                                )
                                ->orWhereHas(
                                    'provinceDistribution.distributionBatch.purchaseOrder',
                                    fn (Builder $purchaseOrderQuery) =>
                                        $purchaseOrderQuery->where(
                                            'po_number',
                                            'like',
                                            '%' . $search . '%'
                                        )
                                )
                                ->orWhereHas(
                                    'provinceDistribution.distributionBatch.purchaseOrder.supplier',
                                    fn (Builder $supplierQuery) =>
                                        $supplierQuery->where(
                                            'supplier_name',
                                            'like',
                                            '%' . $search . '%'
                                        )
                                );
                        }
                    );
                }
            );

        /*
        |--------------------------------------------------------------------------
        | Summary values from all matching Delivery Receipts
        |--------------------------------------------------------------------------
        */

        $allReceipts = (clone $baseQuery)->get();

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

        /*
        |--------------------------------------------------------------------------
        | Paginated Delivery Receipts
        |--------------------------------------------------------------------------
        */

        $receipts = (clone $baseQuery)
            ->orderByDesc('delivery_date')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        /*
        |--------------------------------------------------------------------------
        | Compatibility aliases
        |--------------------------------------------------------------------------
        |
        | Some versions of the Blade file use:
        |
        | $receipts
        | $distributions
        | $summaries
        |
        | They all point to the same paginator so the page does not crash.
        |
        */

        $distributions = $receipts;

        $summaries = $receipts;

        /*
        |--------------------------------------------------------------------------
        | Province filter options
        |--------------------------------------------------------------------------
        */

        $provinces = Province::query()
            ->orderBy('name')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Return view
        |--------------------------------------------------------------------------
        */

        return view(
            'accounting.delivery-receipts.index',
            compact(
                'search',
                'provinceId',
                'status',
                'provinces',
                'receipts',
                'distributions',
                'summaries',
                'allReceipts',
                'totalReceipts',
                'receivedCount',
                'pendingCount',
                'totalReceivedPpe'
            )
        );
    }
}