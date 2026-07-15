<?php

namespace App\Http\Controllers\TSSD;

use App\Http\Controllers\Controller;
use App\Models\CallOff;
use Illuminate\View\View;

class CallOffController extends Controller
{
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

    public function show(CallOff $callOff): View
    {
        $this->authorize('view', $callOff);

        $this->loadCallOffReportData($callOff);

        return view(
            'tssd.call-offs.show',
            compact('callOff')
        );
    }

    public function print(CallOff $callOff): View
    {
        $this->authorize('view', $callOff);

        $this->loadCallOffReportData($callOff);

        return view(
            'tssd.call-offs.print',
            compact('callOff')
        );
    }

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
