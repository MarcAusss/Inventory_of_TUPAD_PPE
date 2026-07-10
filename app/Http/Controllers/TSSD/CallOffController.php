<?php

namespace App\Http\Controllers\TSSD;

use App\Http\Controllers\Controller;
use App\Models\CallOff;
use App\Models\PurchaseOrder;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class CallOffController extends Controller
{
    /**
     * Display all Call-Offs.
     */
    public function index(): View
    {
        $callOffs = CallOff::with([
            'purchaseOrder.supplier',
            'assignedBy',
            'approvedBy',
        ])
            ->latest()
            ->paginate(10);

        return view('tssd.call-offs.index', compact('callOffs'));
    }

    /**
     * Show form for creating a Call-Off.
     */
    public function create(): View
    {
        $purchaseOrders = PurchaseOrder::with('supplier')
            ->doesntHave('callOff')
            ->latest()
            ->get();

        return view('tssd.call-offs.create', compact('purchaseOrders'));
    }

    /**
     * Store a newly created Call-Off.
     */
    public function store(Request $request): RedirectResponse
    {
        //
        // Business logic will be added in Part 5.
        //

        return redirect()
            ->route('tssd.call-offs.index')
            ->with('success', 'Call-Off created successfully.');
    }

    /**
     * Display a specific Call-Off.
     */
    public function show(CallOff $callOff): View
    {
        $callOff->load([
            'purchaseOrder.supplier',
            'assignedBy',
            'approvedBy',
        ]);

        return view('tssd.call-offs.show', compact('callOff'));
    }

    /**
     * Show edit form.
     */
    public function edit(CallOff $callOff): View
    {
        return view('tssd.call-offs.edit', compact('callOff'));
    }

    /**
     * Update the Call-Off.
     */
    public function update(Request $request, CallOff $callOff): RedirectResponse
    {
        //
        // Will be implemented later.
        //

        return redirect()
            ->route('tssd.call-offs.index')
            ->with('success', 'Call-Off updated successfully.');
    }

    /**
     * Delete Call-Off.
     */
    public function destroy(CallOff $callOff): RedirectResponse
    {
        //
        // Delete will only be allowed while Pending.
        // Business rule will be added later.
        //

        return redirect()
            ->route('tssd.call-offs.index')
            ->with('success', 'Call-Off deleted successfully.');
    }
}