<?php

use App\Http\Controllers\Accounting\DashboardController;
use App\Http\Controllers\Accounting\InventoryLedgerController;
use Illuminate\Support\Facades\Route;

Route::middleware([
    'auth',
    'verified',
    'role:Accounting Unit',
])
    ->prefix('accounting')
    ->name('accounting.')
    ->group(function (): void {
        /*
        |--------------------------------------------------------------------------
        | Dashboard
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/dashboard',
            DashboardController::class
        )->name('dashboard');

        /*
        |--------------------------------------------------------------------------
        | Read-Only Inventory Ledger
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/inventory-ledger',
            [
                InventoryLedgerController::class,
                'index',
            ]
        )->name('inventory-ledger.index');
    });
 