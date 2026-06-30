<?php

namespace App\Http\Controllers\ProvincialOffice;

use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
    public function __invoke()
    {
        return view('dashboards.provincial-office');
    }
}