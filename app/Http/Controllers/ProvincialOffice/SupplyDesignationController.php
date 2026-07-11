<?php

namespace App\Http\Controllers\ProvincialOffice;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProvincialOffice\StoreSupplyDesignationRequest;
use App\Models\ProvincialInventory;
use App\Models\SupplyDesignation;
use App\Services\SupplyDesignationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class SupplyDesignationController extends Controller
{
    public function index(Request $request): View
    {
        $provinceId = Auth::user()->province_id;

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
            ])
            ->forProvince($provinceId)
            ->search($search)
            ->latest('designation_date')
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

    public function create(): View
    {
        $provinceId = Auth::user()->province_id;

        abort_unless(
            $provinceId,
            403,
            'This Provincial Office account has no assigned province.'
        );

        $inventories = ProvincialInventory::query()
            ->with('item')
            ->where('province_id', $provinceId)
            ->where('quantity', '>', 0)
            ->orderBy('item_id')
            ->get();

        return view(
            'provincial.project-designations.create',
            compact('inventories')
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
                'Project PPE Designation saved and inventory deducted successfully.'
            );
    }

    public function show(
        SupplyDesignation $supplyDesignation
    ): View {
        $provinceId = Auth::user()->province_id;

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
        ]);

        return view(
            'provincial.project-designations.show',
            compact('supplyDesignation')
        );
    }
}