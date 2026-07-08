<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProvincialOffice\DashboardController;
use App\Http\Controllers\ProvincialOffice\ProvincialOfficeController;

Route::middleware([
    'auth',
    'verified',
    'role:Provincial Office',
])
    ->prefix('provincial')
    ->name('provincial.')
    ->group(function () {

        /*
        |--------------------------------------------------------------------------
        | Dashboard
        |--------------------------------------------------------------------------
        */

        Route::get('/', DashboardController::class)
            ->name('dashboard');



        /*
        |--------------------------------------------------------------------------
        | Deliveries
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/deliveries',
            [ProvincialOfficeController::class, 'index']
        )->name('deliveries.index');

        Route::get(
            '/deliveries/{purchaseOrder}',
            [ProvincialOfficeController::class, 'show']
        )->name('deliveries.show');

        Route::get(
            '/deliveries/{purchaseOrder}/receive',
            [ProvincialOfficeController::class, 'receive']
        )->name('deliveries.receive');

        Route::post(
            '/deliveries/{purchaseOrder}/receive',
            [ProvincialOfficeController::class, 'storeReceipt']
        )->name('deliveries.receipt.store');



        /*
        |--------------------------------------------------------------------------
        | Delivery Receipt
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/inventory',
            [ProvincialOfficeController::class, 'inventory']
        )->name('inventory.index');

        Route::get(
            '/inventory/{receipt}',
            [ProvincialOfficeController::class, 'inventoryShow']
        )->name('inventory.show');



        /*
        |--------------------------------------------------------------------------
        | Supply Designation
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/inventory/{receipt}/designate',
            [ProvincialOfficeController::class, 'designate']
        )->name('inventory.designate');

        Route::post(
            '/inventory/{receipt}/designate',
            [ProvincialOfficeController::class, 'storeDesignation']
        )->name('inventory.designate.store');

        Route::get(
            '/designations',
            [ProvincialOfficeController::class, 'designationIndex']
        )->name('designations.index');

    });