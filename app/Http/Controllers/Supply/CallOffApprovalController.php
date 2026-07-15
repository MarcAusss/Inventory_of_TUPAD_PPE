<?php

namespace App\Http\Controllers\Supply;

use App\Http\Controllers\Controller;
use App\Http\Requests\Supply\ReviewCallOffRequest;
use App\Models\CallOff;
use App\Models\TssdDistributionBatch;
use App\Services\CallOffService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CallOffApprovalController extends Controller
{
    public function index(): View
    {
        $pendingBatches = TssdDistributionBatch::query()
            ->with([
                'purchaseOrder.supplier',
                'creator',
                'provinceDistributions.province',
            ])
            ->where('status', 'Submitted')
            ->doesntHave('callOff')
            ->latest('distribution_date')
            ->latest('id')
            ->get();

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
            compact('pendingBatches', 'callOffs')
        );
    }

    public function show(
        TssdDistributionBatch $distributionBatch
    ): View {
        abort_unless(
            $distributionBatch->status === 'Submitted'
            && ! $distributionBatch->callOff()->exists(),
            404
        );

        $distributionBatch->load([
            'purchaseOrder.supplier',
            'creator',
            'provinceDistributions.province',
            'provinceDistributions.items.item',
        ]);

        return view(
            'supply.call-offs.show',
            compact('distributionBatch')
        );
    }

    public function review(
        ReviewCallOffRequest $request,
        TssdDistributionBatch $distributionBatch,
        CallOffService $callOffService
    ): RedirectResponse {
        $callOff = $callOffService->assignAndApprove(
            $distributionBatch,
            $request->validated(),
            $request->file('approval_document')
        );

        return redirect()
            ->route('supply.call-offs.index')
            ->with(
                'success',
                "Call-Off {$callOff->call_off_number} was assigned and approved successfully."
            );
    }
}
