<?php

use App\Http\Controllers\TSSD\CallOffController;
use App\Http\Controllers\TSSD\DashboardController;
use App\Http\Controllers\TSSD\ReceivingInboxController;
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

        /*
        |--------------------------------------------------------------------------
        | Provincial Receiving Inbox
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/receiving-inbox',
            [
                ReceivingInboxController::class,
                'index',
            ]
        )->name('receiving-inbox.index');

        Route::get(
            '/receiving-inbox/{workflowNotification}',
            [
                ReceivingInboxController::class,
                'show',
            ]
        )
            ->whereNumber('workflowNotification')
            ->name('receiving-inbox.show');

        Route::patch(
            '/receiving-inbox/{workflowNotification}/read',
            [
                ReceivingInboxController::class,
                'markAsRead',
            ]
        )
            ->whereNumber('workflowNotification')
            ->name('receiving-inbox.read');

        Route::patch(
            '/receiving-inbox/{workflowNotification}/resolve',
            [
                ReceivingInboxController::class,
                'resolve',
            ]
        )
            ->whereNumber('workflowNotification')
            ->name('receiving-inbox.resolve');
    });
