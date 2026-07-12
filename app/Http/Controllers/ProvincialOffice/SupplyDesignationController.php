<?php

namespace App\Http\Controllers\ProvincialOffice;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProvincialOffice\StoreSupplyDesignationRequest;
use App\Models\ProvinceDistribution;
use App\Models\SupplyDesignation;
use App\Services\CallOffInventoryService;
use App\Services\SupplyDesignationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class SupplyDesignationController extends Controller
{
    public function index(
        Request $request
    ): View {
        $provinceId = Auth::user()?->province_id;

        abort_unless(
            $provinceId,
            403,
            'This Provincial Office account has no assigned province.'
        );

        $search = trim(
            (string) $request->query('search')
        );

        $designations = SupplyDesignation::query()
            ->with([
                'province',
                'creator',
                'items.item',
                'provinceDistribution.distributionBatch.callOff',
                'provinceDistribution.distributionBatch.purchaseOrder.supplier',
            ])
            ->forProvince(
                (int) $provinceId
            )
            ->search($search)
            ->latest('designation_date')
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        return view(
            'provincial.project-designations.index',
            compact(
                'designations',
                'search'
            )
        );
    }

    public function create(
        Request $request,
        CallOffInventoryService $callOffInventoryService
    ): View {
        $provinceId = Auth::user()?->province_id;

        abort_unless(
            $provinceId,
            403,
            'This Provincial Office account has no assigned province.'
        );

        $allocations = $callOffInventoryService
            ->availableAllocations();

        $selectedAllocation = null;

        $balances = [];

        $selectedAllocationId = (int) $request->query(
            'province_distribution_id',
            old('province_distribution_id', 0)
        );

        if ($selectedAllocationId > 0) {
            $selectedAllocation = $allocations
                ->first(
                    fn (
                        ProvinceDistribution $allocation
                    ): bool => (int) $allocation->id
                        === $selectedAllocationId
                );

            abort_unless(
                $selectedAllocation,
                403,
                'The selected Call-Off allocation is not available for project designation.'
            );

            $balances = $callOffInventoryService
                ->balances(
                    $selectedAllocation
                );
        }

        return view(
            'provincial.project-designations.create',
            compact(
                'allocations',
                'selectedAllocation',
                'selectedAllocationId',
                'balances'
            )
        );
    }

    public function store(
        StoreSupplyDesignationRequest $request,
        SupplyDesignationService $service
    ): RedirectResponse {
        $designation = $service->create(
            $request->validated(),
            $request->file('are_document')
        );

        return redirect()
            ->route(
                'provincial.project-designations.show',
                $designation
            )
            ->with(
                'success',
                'Project PPE Designation saved and the selected Call-Off inventory was updated successfully.'
            );
    }

    public function show(
        SupplyDesignation $supplyDesignation
    ): View {
        $provinceId = Auth::user()?->province_id;

        abort_unless(
            $provinceId
            && (int) $supplyDesignation->province_id
                === (int) $provinceId,
            403,
            'You cannot access another province’s project designation.'
        );

        $supplyDesignation->load([
            'province',
            'creator',
            'items.item',
            'provinceDistribution.province',
            'provinceDistribution.distributionBatch.callOff',
            'provinceDistribution.distributionBatch.purchaseOrder.supplier',
            'provinceDistribution.deliveryReceipts.items.item',
        ]);

        return view(
            'provincial.project-designations.show',
            compact('supplyDesignation')
        );
    }
}
