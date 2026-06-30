<?php

namespace App\Http\Controllers\Supply;

use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
    public function __invoke()
    {
        return view('dashboards.supply');
    }
}