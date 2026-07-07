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

    Route::get('/', DashboardController::class)
        ->name('dashboard');

    Route::get('/deliveries', [ProvincialOfficeController::class, 'index'])
        ->name('deliveries.index');

    Route::get('/deliveries/{distribution}', [ProvincialOfficeController::class, 'show'])
        ->name('deliveries.show');

    Route::get('/deliveries/{distribution}/receive', [ProvincialOfficeController::class, 'receive'])
        ->name('deliveries.receive');

    Route::post('/deliveries/{distribution}/receive', [ProvincialOfficeController::class, 'storeReceipt'])
        ->name('deliveries.receipt.store');

    Route::resource('provincial', ProvincialOfficeController::class);

});