<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\Province;
use App\Models\SupplyDesignation;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProjectDesignationSummaryController extends Controller
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
        | Base query
        |--------------------------------------------------------------------------
        */

        $baseQuery = SupplyDesignation::query()
            ->with([
                'province',
                'items',
                'provinceDistribution.province',
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
                                    'designation_number',
                                    'like',
                                    '%' . $search . '%'
                                )
                                ->orWhere(
                                    'project_name',
                                    'like',
                                    '%' . $search . '%'
                                )
                                ->orWhere(
                                    'remarks',
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
        | Summary values
        |--------------------------------------------------------------------------
        */

        $allDesignations = (clone $baseQuery)->get();

        $totalDesignations = $allDesignations->count();

        $totalDesignatedPpe = (int) $allDesignations->sum(
            fn (SupplyDesignation $designation): int =>
                (int) $designation->items->sum('quantity')
        );

        $completedCount = $allDesignations
            ->filter(
                fn (SupplyDesignation $designation): bool =>
                    in_array(
                        strtolower((string) $designation->status),
                        [
                            'approved',
                            'completed',
                            'designated',
                        ],
                        true
                    )
            )
            ->count();

        $pendingCount = $allDesignations
            ->filter(
                fn (SupplyDesignation $designation): bool =>
                    in_array(
                        strtolower((string) $designation->status),
                        [
                            'pending',
                            'draft',
                            'for approval',
                            'pending approval',
                        ],
                        true
                    )
            )
            ->count();

        /*
        |--------------------------------------------------------------------------
        | Paginated records
        |--------------------------------------------------------------------------
        */

        $designations = (clone $baseQuery)
            ->orderByDesc('designation_date')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        /*
        |--------------------------------------------------------------------------
        | Filter options
        |--------------------------------------------------------------------------
        */

        $provinces = Province::query()
            ->orderBy('name')
            ->get();

        $statuses = SupplyDesignation::query()
            ->whereNotNull('status')
            ->where('status', '!=', '')
            ->distinct()
            ->orderBy('status')
            ->pluck('status');

        /*
        |--------------------------------------------------------------------------
        | Return view
        |--------------------------------------------------------------------------
        */

        return view(
            'accounting.project-designations.index',
            compact(
                'search',
                'provinceId',
                'status',
                'provinces',
                'statuses',
                'designations',
                'totalDesignations',
                'totalDesignatedPpe',
                'completedCount',
                'pendingCount'
            )
        );
    }
}