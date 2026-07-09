<?php

use App\Http\Controllers\Accounting\DashboardController;
use Illuminate\Support\Facades\Route;

Route::middleware([
    'auth',
    'verified',
    'role:Accounting Unit',
])
->prefix('accounting')
->name('accounting.')
->group(function () {

    Route::get('/dashboard', DashboardController::class)
        ->name('dashboard');

});