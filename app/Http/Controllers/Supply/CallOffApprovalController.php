<?php

namespace App\Http\Controllers\Supply;

use App\Http\Controllers\Controller;
use App\Http\Requests\Supply\ReviewCallOffRequest;
use App\Models\CallOff;
use App\Services\CallOffService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CallOffApprovalController extends Controller
{
    /**
     * Display Call-Offs awaiting or containing Supply decisions.
     */
    public function index(): View
    {
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
            'supply.call-offs.index',
            compact('callOffs')
        );
    }

    /**
     * Show one Call-Off and all provincial allocations.
     */
    public function show(CallOff $callOff): View
    {
        $callOff->load([
            'distributionBatch.purchaseOrder.supplier',
            'distributionBatch.creator',
            'distributionBatch.provinceDistributions.province',
            'distributionBatch.provinceDistributions.items.item',
            'assignedBy',
            'approvedBy',
        ]);

        return view(
            'supply.call-offs.show',
            compact('callOff')
        );
    }

    /**
     * Approve or reject a pending Call-Off.
     */
    public function review(
        ReviewCallOffRequest $request,
        CallOff $callOff,
        CallOffService $callOffService
    ): RedirectResponse {
        $reviewedCallOff = $callOffService->review(
            $callOff,
            $request->validated(),
            $request->file('approval_document')
        );

        $message = $reviewedCallOff->status === 'Approved'
            ? 'Call-Off approved successfully.'
            : 'Call-Off rejected successfully.';

        return redirect()
            ->route(
                'supply.call-offs.show',
                $reviewedCallOff
            )
            ->with('success', $message);
    }
}
