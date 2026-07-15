<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\ProvincialOffice\CallOffInventoryController;
use App\Http\Controllers\ProvincialOffice\DashboardController;
use App\Http\Controllers\ProvincialOffice\InventoryController;
use App\Http\Controllers\ProvincialOffice\InventoryLedgerController;
use App\Http\Controllers\ProvincialOffice\ReceivingController;
use App\Http\Controllers\ProvincialOffice\SupplyDesignationController;

Route::middleware([
    'auth',
    'verified',
    'role:Provincial Office',
])
    ->prefix('provincial')
    ->name('provincial.')
    ->group(function (): void {

        /*
        |--------------------------------------------------------------------------
        | Dashboard
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/',
            DashboardController::class
        )->name('dashboard');

        /*
        |--------------------------------------------------------------------------
        | Receiving
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/receiving',
            [
                ReceivingController::class,
                'index',
            ]
        )->name('receiving.index');

        Route::get(
            '/receiving/history',
            [
                ReceivingController::class,
                'history',
            ]
        )->name('receiving.history');

        Route::get(
            '/receiving/{provinceDistribution}/create',
            [
                ReceivingController::class,
                'create',
            ]
        )
            ->whereNumber('provinceDistribution')
            ->name('receiving.create');

        Route::post(
            '/receiving/{provinceDistribution}',
            [
                ReceivingController::class,
                'store',
            ]
        )
            ->whereNumber('provinceDistribution')
            ->name('receiving.store');

        Route::get(
            '/receiving/{provinceDistribution}',
            [
                ReceivingController::class,
                'show',
            ]
        )
            ->whereNumber('provinceDistribution')
            ->name('receiving.show');

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
        | Call-Off Inventory
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/call-off-inventory',
            [
                CallOffInventoryController::class,
                'index',
            ]
        )->name('call-off-inventory.index');

        /*
        |--------------------------------------------------------------------------
        | Inventory Ledger
        |--------------------------------------------------------------------------
        */

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

        /*
        |--------------------------------------------------------------------------
        | Project Designations
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/project-designations',
            [
                SupplyDesignationController::class,
                'index',
            ]
        )->name('project-designations.index');

        /*
         * Print all project designations or all current filtered results.
         *
         * This static route must be declared before the dynamic
         * {supplyDesignation} routes.
         */
        Route::get(
            '/project-designations/print',
            [
                SupplyDesignationController::class,
                'printAll',
            ]
        )->name('project-designations.print-all');

        Route::get(
            '/project-designations/create',
            [
                SupplyDesignationController::class,
                'create',
            ]
        )->name('project-designations.create');

        Route::post(
            '/project-designations',
            [
                SupplyDesignationController::class,
                'store',
            ]
        )->name('project-designations.store');

        /*
         * Print one exact project designation.
         */
        Route::get(
            '/project-designations/{supplyDesignation}/print',
            [
                SupplyDesignationController::class,
                'printOne',
            ]
        )
            ->whereNumber('supplyDesignation')
            ->name('project-designations.print-one');

        /*
         * Show one exact project designation.
         *
         * Keep this dynamic route last.
         */
        Route::get(
            '/project-designations/{supplyDesignation}',
            [
                SupplyDesignationController::class,
                'show',
            ]
        )
            ->whereNumber('supplyDesignation')
            ->name('project-designations.show');
    });