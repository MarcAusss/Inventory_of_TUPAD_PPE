<?php

use App\Http\Controllers\Auth\RedirectDashboardController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProvincialOffice\InventoryLedgerController;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return redirect()->route('login');
});

/*
|--------------------------------------------------------------------------
| Authenticated Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'verified'])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Redirect Dashboard
    |--------------------------------------------------------------------------
    */

    Route::get('/dashboard', RedirectDashboardController::class)
        ->name('dashboard');

    /*
    |--------------------------------------------------------------------------
    | Profile
    |--------------------------------------------------------------------------
    */

    Route::get('/profile', [ProfileController::class, 'edit'])
        ->name('profile.edit');

    Route::patch('/profile', [ProfileController::class, 'update'])
        ->name('profile.update');

    Route::delete('/profile', [ProfileController::class, 'destroy'])
        ->name('profile.destroy');

    Route::get(
        '/inventory-ledger',
        [
            InventoryLedgerController::class,
            'index',
        ]
    )->name('inventory-ledger.index');

    Route::get(
        '/inventory-ledger/print',
        [
            InventoryLedgerController::class,
            'print',
        ]
    )->name('inventory-ledger.print');
});



require __DIR__ . '/auth.php';
require __DIR__ . '/supply.php';
require __DIR__ . '/tssd.php';
require __DIR__ . '/provincial.php';
require __DIR__ . '/accounting.php';