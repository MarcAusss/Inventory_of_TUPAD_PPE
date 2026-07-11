<?php

use App\Http\Controllers\TSSD\CallOffController;
use App\Http\Controllers\TSSD\DashboardController;
use App\Http\Controllers\TSSD\TssdDistributionController;
use Illuminate\Support\Facades\Route;

Route::middleware([
    'auth',
    'verified',
    'role:TSSD Unit',
])
    ->prefix('tssd')
    ->name('tssd.')
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
        | TSSD Distributions
        |--------------------------------------------------------------------------
        */

        Route::resource(
            'distributions',
            TssdDistributionController::class
        )->only([
            'index',
            'create',
            'store',
            'show',
        ]);

        Route::get(
            '/purchase-orders/{poId}/remaining',
            [
                TssdDistributionController::class,
                'getRemaining',
            ]
        )
            ->whereNumber('poId')
            ->name('purchase-orders.remaining');

        /*
        |--------------------------------------------------------------------------
        | Call-Off Management
        |--------------------------------------------------------------------------
        */

        Route::resource(
            'call-offs',
            CallOffController::class
        )->only([
            'index',
            'create',
            'store',
            'show',
            'destroy',
        ]);
    });
