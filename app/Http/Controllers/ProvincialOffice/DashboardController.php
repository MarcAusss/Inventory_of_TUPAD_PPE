<?php

namespace App\Http\Controllers\ProvincialOffice;

use App\Http\Controllers\Controller;
use App\Services\DashboardService;

class DashboardController extends Controller
{
    public function __invoke(DashboardService $dashboardService)
    {
        $dashboard = $dashboardService->getDashboardData();

        return view('dashboards.provincial-office', [
            'statistics' => $dashboard['statistics'],
            'charts' => $dashboard['charts'],
            'recentActivities' => $dashboard['recentActivities'],
        ]);
    }
}
