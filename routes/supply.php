<?php

use App\Http\Controllers\Supply\DashboardController;
use App\Http\Controllers\Supply\SupplierController;
use Illuminate\Support\Facades\Route;

Route::middleware([
    'auth',
    'verified',
    'role:Supply Unit',
])
->prefix('supply')
->name('supply.')
->group(function () {

    Route::get('/dashboard', DashboardController::class)
        ->name('dashboard');

    /*
    |--------------------------------------------------------------------------
    | Phase 2
    |--------------------------------------------------------------------------
    */

    Route::resource('suppliers', SupplierController::class);
    // Route::resource('items', ItemController::class);

    /*
    |--------------------------------------------------------------------------
    | Phase 3
    |--------------------------------------------------------------------------
    */

    // Route::resource('purchase-orders', PurchaseOrderController::class);

});