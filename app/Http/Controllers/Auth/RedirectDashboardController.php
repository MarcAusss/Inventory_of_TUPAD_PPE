<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;

class RedirectDashboardController extends Controller
{
    public function __invoke(): RedirectResponse
    {
        return match (auth()->user()->role->name) {

            'Supply Unit'
                => redirect()->route('supply.dashboard'),

            'TSSD Unit'
                => redirect()->route('tssd.dashboard'),

            'Provincial Office'
                => redirect()->route('provincial.dashboard'),

            'Accounting Unit'
                => redirect()->route('accounting.dashboard'),

            default
                => abort(403),

        };
    }
}