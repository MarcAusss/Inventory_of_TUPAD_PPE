<?php

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
    ->group(function () {

        Route::get('/dashboard', DashboardController::class)
            ->name('dashboard');

        Route::middleware(['auth'])->group(function () {

            Route::resource(
                'distributions',
                TssdDistributionController::class
            );

        });
    });
    Route::get('/tssd/purchase-orders/{id}/remaining', [TssdDistributionController::class, 'getRemaining']);