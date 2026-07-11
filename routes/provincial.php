<?php

use App\Http\Controllers\ProvincialOffice\DashboardController;
use App\Http\Controllers\ProvincialOffice\ProvincialOfficeController;
use App\Http\Controllers\ProvincialOffice\ReceivingController;
use Illuminate\Support\Facades\Route;

Route::middleware([
    'auth',
    'verified',
    'role:Provincial Office',
])
    ->prefix('provincial')
    ->name('provincial.')
    ->group(function (): void {
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
        | New Call-Off Based Receiving
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/receiving',
            [
                ReceivingController::class,
                'index',
            ]
        )->name('receiving.index');

        Route::get(
            '/receiving/history',
            [
                ReceivingController::class,
                'history',
            ]
        )->name('receiving.history');

        Route::get(
            '/receiving/{provinceDistribution}',
            [
                ReceivingController::class,
                'show',
            ]
        )
            ->whereNumber('provinceDistribution')
            ->name('receiving.show');

        Route::get(
            '/receiving/{provinceDistribution}/create',
            [
                ReceivingController::class,
                'create',
            ]
        )
            ->whereNumber('provinceDistribution')
            ->name('receiving.create');

        Route::post(
            '/receiving/{provinceDistribution}',
            [
                ReceivingController::class,
                'store',
            ]
        )
            ->whereNumber('provinceDistribution')
            ->name('receiving.store');

        /*
        |--------------------------------------------------------------------------
        | Legacy Provincial Routes
        |--------------------------------------------------------------------------
        |
        | Temporarily retained until the new receiving and inventory screens
        | are fully verified.
        |
        */

        Route::get(
            '/deliveries',
            [
                ProvincialOfficeController::class,
                'index',
            ]
        )->name('deliveries.index');

        Route::get(
            '/deliveries/{purchaseOrder}',
            [
                ProvincialOfficeController::class,
                'show',
            ]
        )
            ->whereNumber('purchaseOrder')
            ->name('deliveries.show');

        Route::get(
            '/deliveries/{purchaseOrder}/receive',
            [
                ProvincialOfficeController::class,
                'receive',
            ]
        )
            ->whereNumber('purchaseOrder')
            ->name('deliveries.receive');

        Route::post(
            '/deliveries/{purchaseOrder}/receive',
            [
                ProvincialOfficeController::class,
                'storeReceipt',
            ]
        )
            ->whereNumber('purchaseOrder')
            ->name('deliveries.receipt.store');

        Route::get(
            '/inventory',
            [
                ProvincialOfficeController::class,
                'inventory',
            ]
        )->name('inventory.index');

        Route::get(
            '/inventory/{receipt}',
            [
                ProvincialOfficeController::class,
                'inventoryShow',
            ]
        )
            ->whereNumber('receipt')
            ->name('inventory.show');

        Route::get(
            '/inventory/{receipt}/designate',
            [
                ProvincialOfficeController::class,
                'designate',
            ]
        )
            ->whereNumber('receipt')
            ->name('inventory.designate');

        Route::post(
            '/inventory/{receipt}/designate',
            [
                ProvincialOfficeController::class,
                'storeDesignation',
            ]
        )
            ->whereNumber('receipt')
            ->name('inventory.designate.store');

        Route::get(
            '/designations',
            [
                ProvincialOfficeController::class,
                'designationIndex',
            ]
        )->name('designations.index');
    });
