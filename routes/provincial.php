<?php

use App\Http\Controllers\ProvincialOffice\DashboardController;
use Illuminate\Support\Facades\Route;

Route::middleware([
    'auth',
    'verified',
    'role:Provincial Office',
])
->prefix('provincial')
->name('provincial.')
->group(function () {

    Route::get('/dashboard', DashboardController::class)
        ->name('dashboard');

});