<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\Province;
use App\Models\ProvinceDistribution;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DistributionSummaryController extends Controller
{
    public function __invoke(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));
        $provinceId = $request->integer('province_id') ?: null;
        $status = trim((string) $request->query('status', ''));

        $baseQuery = ProvinceDistribution::query()
            ->with([
                'province',
                'items',
                'distributionBatch.callOff',
                'distributionBatch.purchaseOrder.supplier',
            ])
            ->when(
                $provinceId,
                fn ($query) => $query->where('province_id', $provinceId)
            )
            ->when(
                $status !== '',
                fn ($query) => $query->where('status', $status)
            )
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($subQuery) use ($search): void {
                    $subQuery
                        ->where('place_of_delivery', 'like', "%{$search}%")
                        ->orWhereHas(
                            'province',
                            fn ($provinceQuery) => $provinceQuery->where(
                                'name',
                                'like',
                                "%{$search}%"
                            )
                        )
                        ->orWhereHas(
                            'distributionBatch.callOff',
                            fn ($callOffQuery) => $callOffQuery->where(
                                'call_off_number',
                                'like',
                                "%{$search}%"
                            )
                        )
                        ->orWhereHas(
                            'distributionBatch.purchaseOrder',
                            fn ($purchaseOrderQuery) => $purchaseOrderQuery->where(
                                'po_number',
                                'like',
                                "%{$search}%"
                            )
                        )
                        ->orWhereHas(
                            'distributionBatch.purchaseOrder.supplier',
                            fn ($supplierQuery) => $supplierQuery->where(
                                'supplier_name',
                                'like',
                                "%{$search}%"
                            )
                        );
                });
            });

        $summaryRows = (clone $baseQuery)->get();

        $distributions = (clone $baseQuery)
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        // Backward-compatible alias for any older Blade code using $summaries.
        $summaries = $distributions;

        $totalDistributions = $summaryRows->count();
        $totalPpe = (int) $summaryRows->sum(
            fn (ProvinceDistribution $distribution) =>
                $distribution->items->sum('quantity')
        );
        $receivedCount = $summaryRows->where('status', 'Received')->count();
        $pendingCount = $summaryRows
            ->whereIn('status', ['Pending', 'Approved', 'For Delivery', 'Partially Received'])
            ->count();

        $provinces = Province::query()
            ->orderBy('name')
            ->get();

        return view('accounting.distributions.index', compact(
            'search',
            'provinceId',
            'status',
            'provinces',
            'distributions',
            'summaries',
            'totalDistributions',
            'totalPpe',
            'receivedCount',
            'pendingCount'
        ));
    }
}
