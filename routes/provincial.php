<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\ProvincialOffice\DashboardController;
use App\Http\Controllers\ProvincialOffice\InventoryController;
use App\Http\Controllers\ProvincialOffice\InventoryLedgerController;
use App\Http\Controllers\ProvincialOffice\ReceivingController;
use App\Http\Controllers\ProvincialOffice\SupplyDesignationController;
use App\Http\Controllers\ProvincialOffice\CallOffInventoryController;

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
        | Receiving
        |--------------------------------------------------------------------------
        */

        Route::get('/receiving', [ReceivingController::class, 'index'])
            ->name('receiving.index');

        Route::get('/receiving/history', [ReceivingController::class, 'history'])
            ->name('receiving.history');

        Route::get('/receiving/{provinceDistribution}', [ReceivingController::class, 'show'])
            ->name('receiving.show');

        Route::get('/receiving/{provinceDistribution}/create', [ReceivingController::class, 'create'])
            ->name('receiving.create');

        Route::post('/receiving/{provinceDistribution}', [ReceivingController::class, 'store'])
            ->name('receiving.store');

        /*
        |--------------------------------------------------------------------------
        | Current Inventory
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/current-inventory',
            [
                InventoryController::class,
                'index',
            ]
        )->name('current-inventory.index');

        /*
        |--------------------------------------------------------------------------
        | Inventory Ledger
        |--------------------------------------------------------------------------
        */

        Route::get('/inventory-ledger', [InventoryLedgerController::class, 'index'])
            ->name('inventory-ledger.index');

        /*
        |--------------------------------------------------------------------------
        | Project Designations
        |--------------------------------------------------------------------------
        */

        Route::get('/project-designations', [SupplyDesignationController::class, 'index'])
            ->name('project-designations.index');

        Route::get('/project-designations/create', [SupplyDesignationController::class, 'create'])
            ->name('project-designations.create');

        Route::post('/project-designations', [SupplyDesignationController::class, 'store'])
            ->name('project-designations.store');

        Route::get('/project-designations/{supplyDesignation}', [SupplyDesignationController::class, 'show'])
            ->name('project-designations.show');

        Route::get(
            '/call-off-inventory',
            [
                CallOffInventoryController::class,
                'index',
            ]
        )->name('call-off-inventory.index');

        Route::get(
            '/inventory-ledger/print',
            [
                InventoryLedgerController::class,
                'print',
            ]
        )->name('inventory-ledger.print');

        Route::get(
            '/project-designations/print',
            [
                SupplyDesignationController::class,
                'printAll',
            ]
        )->name(
                'project-designations.print-all'
            );
        Route::get(
            '/project-designations/{supplyDesignation}/print',
            [
                SupplyDesignationController::class,
                'printOne',
            ]
        )->name(
                'project-designations.print-one'
            );
    });