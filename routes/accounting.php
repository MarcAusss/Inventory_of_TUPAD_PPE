<?php

use App\Http\Controllers\Accounting\DashboardController;
use App\Http\Controllers\Accounting\DeliveryReceiptSummaryController;
use App\Http\Controllers\Accounting\DistributionSummaryController;
use App\Http\Controllers\Accounting\InventoryLedgerController;
use App\Http\Controllers\Accounting\ProjectDesignationSummaryController;
use App\Http\Controllers\Accounting\ProvincialInventorySummaryController;
use App\Http\Controllers\Accounting\SupplyInventorySummaryController;
use Illuminate\Support\Facades\Route;

Route::middleware([
    'auth',
    'verified',
    'role:Accounting Unit',
])
    ->prefix('accounting')
    ->name('accounting.')
    ->group(function (): void {
        Route::get('/dashboard', DashboardController::class)
            ->name('dashboard');

        Route::get('/supply-inventory', SupplyInventorySummaryController::class)
            ->name('supply-inventory.index');

        Route::get('/provincial-inventory', ProvincialInventorySummaryController::class)
            ->name('provincial-inventory.index');

        Route::get('/distributions', DistributionSummaryController::class)
            ->name('distributions.index');

        Route::get('/delivery-receipts', DeliveryReceiptSummaryController::class)
            ->name('delivery-receipts.index');

        Route::get('/project-designations', ProjectDesignationSummaryController::class)
            ->name('project-designations.index');

        Route::get('/inventory-ledger', [InventoryLedgerController::class, 'index'])
            ->name('inventory-ledger.index');
    });
