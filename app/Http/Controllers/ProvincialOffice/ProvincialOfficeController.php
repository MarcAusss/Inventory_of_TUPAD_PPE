<?php

namespace App\Http\Controllers\ProvincialOffice;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TSSDDistribution;
use Illuminate\Support\Facades\Auth;


class ProvincialOfficeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $provinceId = Auth::user()->province_id;

        $deliveries = TSSDDistribution::with([
            'purchaseOrder',
            'purchaseOrder.supplier',
        ])
            ->where('province_id', $provinceId)
            ->select('purchase_order_id')
            ->distinct()
            ->get()
            ->map(function ($delivery) use ($provinceId) {

                $delivery->items = TSSDDistribution::where(
                    'purchase_order_id',
                    $delivery->purchase_order_id
                )
                    ->where('province_id', $provinceId)
                    ->get();

                return $delivery;
            });

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
    public function show($purchaseOrderId)
    {
        $provinceId = Auth::user()->province_id;

        $distribution = TSSDDistribution::with([
            'purchaseOrder',
            'purchaseOrder.supplier',
            'province',
            'item',
        ])
            ->where('purchase_order_id', $purchaseOrderId)
            ->where('province_id', $provinceId)
            ->firstOrFail();

        return view(
            'provincial.delivery.show',
            [
                'distribution' => $distribution
            ]
        );
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
