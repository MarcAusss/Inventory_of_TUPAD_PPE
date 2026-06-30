<?php

use App\Http\Controllers\TSSD\DashboardController;
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

});