<?php

use App\Http\Controllers\Supply\CallOffApprovalController;
use App\Http\Controllers\Supply\DashboardController;
use App\Http\Controllers\Supply\ItemController;
use App\Http\Controllers\Supply\PurchaseOrderController;
use App\Http\Controllers\Supply\SupplierController;
use Illuminate\Support\Facades\Route;

Route::middleware([
    'auth',
    'verified',
    'role:Supply Unit',
])
    ->prefix('supply')
    ->name('supply.')
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
        | Reference Data
        |--------------------------------------------------------------------------
        */

        Route::resource(
            'suppliers',
            SupplierController::class
        );

        Route::resource(
            'items',
            ItemController::class
        );

        /*
        |--------------------------------------------------------------------------
        | Purchase Orders
        |--------------------------------------------------------------------------
        */

        Route::resource(
            'purchase-orders',
            PurchaseOrderController::class
        );

        /*
        |--------------------------------------------------------------------------
        | Call-Off Approval
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/call-offs',
            [
                CallOffApprovalController::class,
                'index',
            ]
        )->name('call-offs.index');

        Route::get(
            '/call-offs/{callOff}',
            [
                CallOffApprovalController::class,
                'show',
            ]
        )
            ->whereNumber('callOff')
            ->name('call-offs.show');

        Route::patch(
            '/call-offs/{callOff}/review',
            [
                CallOffApprovalController::class,
                'review',
            ]
        )
            ->whereNumber('callOff')
            ->name('call-offs.review');
    });
