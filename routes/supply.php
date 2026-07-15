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
        Route::get(
            '/dashboard',
            DashboardController::class
        )->name('dashboard');

        Route::resource(
            'suppliers',
            SupplierController::class
        );

        Route::resource(
            'items',
            ItemController::class
        );

        Route::resource(
            'purchase-orders',
            PurchaseOrderController::class
        );

        /*
        |--------------------------------------------------------------------------
        | Supply Call-Off Assignment
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
            '/call-offs/batches/{distributionBatch}',
            [
                CallOffApprovalController::class,
                'show',
            ]
        )
            ->whereNumber('distributionBatch')
            ->name('call-offs.show');

        Route::post(
            '/call-offs/batches/{distributionBatch}/assign',
            [
                CallOffApprovalController::class,
                'review',
            ]
        )
            ->whereNumber('distributionBatch')
            ->name('call-offs.review');
    });
