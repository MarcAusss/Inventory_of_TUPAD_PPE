<?php

use App\Http\Controllers\Auth\RedirectDashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Documents\SecureDocumentController;
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

    Route::prefix('secure-documents')->name('documents.')->group(function () {
        Route::get('/purchase-orders/{purchaseOrder}', [SecureDocumentController::class, 'purchaseOrder'])->name('purchase-orders');
        Route::get('/call-offs/{callOff}', [SecureDocumentController::class, 'callOff'])->name('call-offs');
        Route::get('/delivery-receipt-documents/{document}', [SecureDocumentController::class, 'receiptDocument'])->name('receipt-documents');
        Route::get('/delivery-receipts/{deliveryReceipt}', [SecureDocumentController::class, 'receiptLegacy'])->name('receipt-legacy');
        Route::get('/supply-designations/{supplyDesignation}/are', [SecureDocumentController::class, 'are'])->name('are');
    });

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