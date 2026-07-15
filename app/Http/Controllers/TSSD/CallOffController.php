<?php

namespace App\Http\Controllers\TSSD;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCallOffRequest;
use App\Models\CallOff;
use App\Models\TssdDistributionBatch;
use App\Services\CallOffService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CallOffController extends Controller
{
    /**
     * Display all Call-Off records.
     */
    public function index(): View
    {
        $this->authorize('viewAny', CallOff::class);

        $callOffs = CallOff::query()
            ->with([
                'distributionBatch.purchaseOrder.supplier',
                'distributionBatch.provinceDistributions.province',
                'assignedBy',
                'approvedBy',
            ])
            ->latest('assigned_at')
            ->paginate(10);

        return view(
            'tssd.call-offs.index',
            compact('callOffs')
        );
    }

    /**
     * Show the Call-Off assignment form.
     */
    public function create(): View
    {
        $this->authorize('create', CallOff::class);

        $distributionBatches = TssdDistributionBatch::query()
            ->with([
                'purchaseOrder.supplier',
                'creator',
                'provinceDistributions.province',
                'provinceDistributions.items.item',
            ])
            ->whereIn('status', [
                'Draft',
                'Submitted',
            ])
            ->doesntHave('callOff')
            ->latest('distribution_date')
            ->get();

        return view(
            'tssd.call-offs.create',
            compact('distributionBatches')
        );
    }

    /**
     * Store a newly assigned Call-Off.
     */
    public function store(
        StoreCallOffRequest $request,
        CallOffService $callOffService
    ): RedirectResponse {
        $callOff = $callOffService->create(
            $request->validated()
        );

        return redirect()
            ->route(
                'tssd.call-offs.show',
                $callOff
            )
            ->with(
                'success',
                'Call-Off Number assigned successfully and submitted for Supply Unit approval.'
            );
    }

    /**
     * Display one Call-Off.
     */
    public function show(CallOff $callOff): View
    {
        $this->authorize('view', $callOff);

        $this->loadCallOffReportData($callOff);

        return view(
            'tssd.call-offs.show',
            compact('callOff')
        );
    }

    /**
     * Print the Province Distribution Summary.
     */
    public function print(CallOff $callOff): View
    {
        $this->authorize('view', $callOff);

        $this->loadCallOffReportData($callOff);

        return view(
            'tssd.call-offs.print',
            compact('callOff')
        );
    }

    /**
     * Cancel a pending Call-Off.
     */
    public function destroy(
        CallOff $callOff
    ): RedirectResponse {
        $this->authorize('delete', $callOff);

        $callOff->update([
            'status' => 'Cancelled',
        ]);

        if ($callOff->distributionBatch) {
            $callOff->distributionBatch->update([
                'status' => 'Submitted',
            ]);
        }

        return redirect()
            ->route('tssd.call-offs.index')
            ->with(
                'success',
                'The pending Call-Off has been cancelled.'
            );
    }

    /**
     * Load the relationships required by the
     * Call-Off details and print report.
     */
    private function loadCallOffReportData(
        CallOff $callOff
    ): void {
        $callOff->load([
            'distributionBatch.purchaseOrder.supplier',
            'distributionBatch.creator',

            'distributionBatch.provinceDistributions.province',

            'distributionBatch.provinceDistributions.items.item',

            'assignedBy',
            'approvedBy',
        ]);
    }
}