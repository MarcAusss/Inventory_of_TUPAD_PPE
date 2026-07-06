<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProvincialOffice\ProvincialOfficeController;

Route::middleware([
    'auth',
    'verified',
    'role:Provincial Office',
])
->prefix('provincial')
->name('provincial.')
->group(function () {

    // Dashboard
    Route::get(
        '/',
        [ProvincialOfficeController::class, 'index']
    )->name('dashboard');

    // Delivery Receipt
    Route::get(
        '/deliveries/{purchaseOrder}',
        [ProvincialOfficeController::class, 'show']
    )->name('show');

});