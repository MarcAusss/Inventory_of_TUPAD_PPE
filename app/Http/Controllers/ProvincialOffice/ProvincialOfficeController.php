<?php

namespace App\Http\Controllers\ProvincialOffice;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TSSDDistribution;


class ProvincialOfficeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Temporary province ID
        // Later this will come from the logged in user.
        $provinceId = 1;

        $deliveries = TSSDDistribution::with([
            'purchaseOrder.supplier',
            'province',
            'item'
        ])
            ->where('province_id', $provinceId)
            ->orderBy('purchase_order_id')
            ->get()
            ->groupBy('purchase_order_id');

        return view(
            'dashboards.provincial-office',
            compact('deliveries')
        );
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
