<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
    public function __invoke()
    {
        return view('dashboards.accounting');
    }
}