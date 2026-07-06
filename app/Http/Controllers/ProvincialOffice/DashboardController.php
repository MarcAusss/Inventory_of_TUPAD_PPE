<?php

namespace App\Http\Controllers\ProvincialOffice;

use App\Http\Controllers\Controller;
use App\Models\TSSDDistribution;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function __invoke()
    {
        $provinceId = Auth::user()->province_id;

        $deliveries = TSSDDistribution::with([
            'purchaseOrder',
            'purchaseOrder.supplier',
        ])
        ->where('province_id', $provinceId)
        ->select('purchase_order_id')
        ->distinct()
        ->get();

        return view(
            'dashboards.provincial-office',
            compact('deliveries')
        );
    }
}