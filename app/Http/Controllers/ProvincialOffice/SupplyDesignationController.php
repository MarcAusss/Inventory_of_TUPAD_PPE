<?php

namespace App\Http\Controllers\ProvincialOffice;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProvincialOffice\StoreSupplyDesignationRequest;
use App\Models\InventoryMovement;
use App\Models\SupplyDesignation;
use App\Services\CallOffInventoryService;
use App\Services\SupplyDesignationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class SupplyDesignationController extends Controller
{
    public function index(Request $request): View
    {
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
            ->forProvince($provinceId)
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

        return view(
            'provincial.project-designations.create',
            compact('allocations')
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
                'Project PPE Designation saved and Call-Off inventory deducted successfully.'
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

            'provinceDistribution.items.item',

            'provinceDistribution.deliveryReceipts.items.item',

            'provinceDistribution.distributionBatch.callOff',

            'provinceDistribution.distributionBatch.purchaseOrder.supplier',
        ]);

        $movements = InventoryMovement::query()
            ->with('item')
            ->where(
                'supply_designation_id',
                $supplyDesignation->id
            )
            ->where(
                'movement_type',
                'OUT'
            )
            ->get()
            ->keyBy('item_id');

        $movementBreakdown = [];

        foreach (
            $supplyDesignation->items as $designationItem
        ) {
            $movement = $movements->get(
                $designationItem->item_id
            );

            $movementBreakdown[
                $designationItem->item_id
            ] = [
                /*
                 * Exact Call-Off balance at the time of the project.
                 */
                'beginning' => $movement
                    ? (
                        $movement->call_off_balance_before
                        !== null
                        ? (int) $movement
                            ->call_off_balance_before
                        : null
                    )
                    : null,

                'actual' => (int) (
                    $movement?->quantity
                    ?? $designationItem->quantity
                ),

                'ending' => $movement
                    ? (
                        $movement->call_off_balance_after
                        !== null
                        ? (int) $movement
                            ->call_off_balance_after
                        : null
                    )
                    : null,

                /*
                 * Optional pooled figures retained for audit.
                 */
                'pooled_beginning' => $movement
                    ? (
                        $movement->balance_before
                        !== null
                        ? (int) $movement->balance_before
                        : null
                    )
                    : null,

                'pooled_ending' => $movement
                    ? (
                        $movement->balance_after
                        !== null
                        ? (int) $movement->balance_after
                        : null
                    )
                    : null,
            ];
        }

        return view(
            'provincial.project-designations.show',
            compact(
                'supplyDesignation',
                'movementBreakdown'
            )
        );
    }
}
