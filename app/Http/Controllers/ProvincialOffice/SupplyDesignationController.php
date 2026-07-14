<?php

namespace App\Http\Controllers\ProvincialOffice;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProvincialOffice\StoreSupplyDesignationRequest;
use App\Models\DeliveryReceipt;
use App\Models\InventoryMovement;
use App\Models\SupplyDesignation;
use App\Services\DeliveryReceiptInventoryService;
use App\Services\SupplyDesignationService;
use Illuminate\Database\Eloquent\Builder;
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
            (string) $request->query(
                'search',
                ''
            )
        );

        $designations = $this
            ->projectDesignationQuery(
                provinceId: (int) $provinceId,
                search: $search
            )
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
        DeliveryReceiptInventoryService $inventoryService
    ): View {
        $provinceId = Auth::user()?->province_id;

        abort_unless(
            $provinceId,
            403,
            'This Provincial Office account has no assigned province.'
        );

        /*
        |--------------------------------------------------------------------------
        | Separate Delivery Receipt options
        |--------------------------------------------------------------------------
        */

        $deliveryReceipts =
            $inventoryService->availableReceipts();

        $selectedDeliveryReceiptId = (int) $request
            ->query(
                'delivery_receipt_id',
                0
            );

        $selectedDeliveryReceipt = null;

        $balances = [];

        if ($selectedDeliveryReceiptId > 0) {
            $selectedDeliveryReceipt =
                DeliveryReceipt::query()
                    ->with([
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
                    ->whereKey(
                        $selectedDeliveryReceiptId
                    )
                    ->firstOrFail();

            $balances = $inventoryService
                ->balances(
                    $selectedDeliveryReceipt
                );

            $selectedDeliveryReceipt->setAttribute(
                'project_balances',
                $balances
            );

            $selectedDeliveryReceipt->setAttribute(
                'available_for_projects_total',
                (int) collect($balances)
                    ->sum('available_for_projects')
            );
        }

        return view(
            'provincial.project-designations.create',
            compact(
                'deliveryReceipts',
                'selectedDeliveryReceipt',
                'selectedDeliveryReceiptId',
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
                'Project PPE Designation saved and the selected Delivery Receipt inventory was deducted successfully.'
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
            'You cannot access another province\'s project designation.'
        );

        $supplyDesignation->load([
            'province',
            'creator',
            'items.item',
            'deliveryReceipt.items.item',
            'provinceDistribution.items.item',
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
                'beginning' => $movement
                    && $movement->call_off_balance_before !== null
                    ? (int) $movement
                        ->call_off_balance_before
                    : null,

                'actual' => (int) (
                    $movement?->quantity
                    ?? $designationItem->quantity
                ),

                'ending' => $movement
                    && $movement->call_off_balance_after !== null
                    ? (int) $movement
                        ->call_off_balance_after
                    : null,

                'pooled_beginning' => $movement
                    && $movement->balance_before !== null
                    ? (int) $movement->balance_before
                    : null,

                'pooled_ending' => $movement
                    && $movement->balance_after !== null
                    ? (int) $movement->balance_after
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

    public function printAll(
        Request $request
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

        /*
         * Print all records matching the current search.
         *
         * No pagination is applied.
         */
        $designations = $this
            ->projectDesignationQuery(
                provinceId: (int) $provinceId,
                search: $search
            )
            ->orderBy('designation_date')
            ->orderBy('id')
            ->get();

        $provinceName =
            Auth::user()?->province?->name
            ?? 'Provincial Office';

        $preparedBy =
            Auth::user()?->name
            ?? '';

        $reviewedBy = '';

        $printedAt = now();

        $reportTitle = $search !== ''
            ? 'Filtered Project PPE Distribution Report'
            : 'All Project PPE Distribution Report';

        return view(
            'provincial.project-designations.print',
            compact(
                'designations',
                'search',
                'provinceName',
                'preparedBy',
                'reviewedBy',
                'printedAt',
                'reportTitle'
            )
        );
    }

    public function printOne(
        SupplyDesignation $supplyDesignation
    ): View {
        $provinceId = Auth::user()?->province_id;

        abort_unless(
            $provinceId
            && (int) $supplyDesignation->province_id
            === (int) $provinceId,
            403,
            'You cannot print another province\'s project distribution.'
        );

        $supplyDesignation->load([
            'province',
            'creator',
            'items.item',
            'deliveryReceipt',
            'provinceDistribution.distributionBatch.callOff',
            'provinceDistribution.distributionBatch.purchaseOrder.supplier',
        ]);

        $designations = collect([
            $supplyDesignation,
        ]);

        $search =
            $supplyDesignation->project_code;

        $provinceName =
            Auth::user()?->province?->name
            ?? $supplyDesignation->province?->name
            ?? 'Provincial Office';

        $preparedBy =
            Auth::user()?->name
            ?? '';

        $reviewedBy = '';

        $printedAt = now();

        $reportTitle =
            'Project PPE Distribution - '
            . $supplyDesignation->project_code;

        return view(
            'provincial.project-designations.print',
            compact(
                'designations',
                'search',
                'provinceName',
                'preparedBy',
                'reviewedBy',
                'printedAt',
                'reportTitle'
            )
        );
    }

    private function projectDesignationQuery(
        int $provinceId,
        string $search
    ): Builder {
        return SupplyDesignation::query()
            ->with([
                'province',
                'creator',
                'items.item',
                'deliveryReceipt',
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
                'Completed'
            )
            ->when(
                $search !== '',
                function (Builder $query) use ($search): void {
                    $query->where(
                        function (Builder $searchQuery) use ($search): void {
                            $searchQuery
                                ->where(
                                    'project_code',
                                    'like',
                                    "%{$search}%"
                                )
                                ->orWhere(
                                    'project_title',
                                    'like',
                                    "%{$search}%"
                                )
                                ->orWhere(
                                    'location',
                                    'like',
                                    "%{$search}%"
                                )
                                ->orWhereHas(
                                    'deliveryReceipt',
                                    fn(
                                    Builder $receiptQuery
                                ) => $receiptQuery->where(
                                        'dr_number',
                                        'like',
                                        "%{$search}%"
                                    )
                                )
                                ->orWhereHas(
                                    'provinceDistribution'
                                    . '.distributionBatch'
                                    . '.callOff',
                                    fn(
                                    Builder $callOffQuery
                                ) => $callOffQuery->where(
                                        'call_off_number',
                                        'like',
                                        "%{$search}%"
                                    )
                                )
                                ->orWhereHas(
                                    'provinceDistribution'
                                    . '.distributionBatch'
                                    . '.purchaseOrder'
                                    . '.supplier',
                                    fn(
                                    Builder $supplierQuery
                                ) => $supplierQuery->where(
                                        'supplier_name',
                                        'like',
                                        "%{$search}%"
                                    )
                                );
                        }
                    );
                }
            );
    }
}
