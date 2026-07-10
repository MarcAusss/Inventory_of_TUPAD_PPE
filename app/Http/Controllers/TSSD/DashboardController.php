<?php

namespace App\Http\Controllers\TSSD;

use App\Http\Controllers\Controller;
use App\Services\DashboardService;

class DashboardController extends Controller
{
    public function __invoke(DashboardService $dashboardService)
    {
        $dashboard = $dashboardService->getDashboardData();

        return view('dashboards.tssd', [
            'statistics' => $dashboard['statistics'],
            'charts' => $dashboard['charts'],
            'recentActivities' => $dashboard['recentActivities'],
        ]);
    }
}