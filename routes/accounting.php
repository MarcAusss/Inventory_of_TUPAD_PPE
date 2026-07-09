<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Accounting\DashboardController;
use App\Http\Controllers\Accounting\AccountingController;

Route::middleware([
    'auth',
    'verified',
    'role:Accounting Unit',
])
    ->prefix('accounting')
    ->name('accounting.')
    ->group(function () {

        /*
        |--------------------------------------------------------------------------
        | Dashboard
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/',
            DashboardController::class
        )->name('dashboard');



        /*
        |--------------------------------------------------------------------------
        | Purchase Orders
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/purchase-orders',
            [AccountingController::class, 'index']
        )->name('purchase-orders.index');



        Route::get(
            '/purchase-orders/{purchaseOrder}',
            [AccountingController::class, 'show']
        )->name('purchase-orders.show');



        /*
        |--------------------------------------------------------------------------
        | Approval
        |--------------------------------------------------------------------------
        */

        Route::post(
            '/purchase-orders/{purchaseOrder}/approve',
            [AccountingController::class, 'approve']
        )->name('purchase-orders.approve');



        /*
        |--------------------------------------------------------------------------
        | Rejection
        |--------------------------------------------------------------------------
        */

        Route::post(
            '/purchase-orders/{purchaseOrder}/reject',
            [AccountingController::class, 'reject']
        )->name('purchase-orders.reject');

    });