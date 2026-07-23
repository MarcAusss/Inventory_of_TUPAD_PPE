<?php

use App\Http\Controllers\TSSD\CallOffController;
use App\Http\Controllers\TSSD\CallOffLetterController;
use App\Http\Controllers\TSSD\DashboardController;
use App\Http\Controllers\TSSD\InventoryLedgerController;
use App\Http\Controllers\TSSD\PdfTemplateController;
use App\Http\Controllers\TSSD\TssdDistributionController;
use App\Http\Controllers\TSSD\UserManagementController;
use Illuminate\Support\Facades\Route;

Route::middleware([
    'auth',
    'verified',
    'role:TSSD Unit',
])
    ->prefix('tssd')
    ->name('tssd.')
    ->group(function (): void {
        Route::get(
            '/dashboard',
            DashboardController::class
        )->name('dashboard');

        Route::get(
            '/inventory-monitoring',
            [InventoryLedgerController::class, 'index']
        )->name('inventory-monitoring.index');

        /*
        |--------------------------------------------------------------------------
        | TSSD Distributions
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/purchase-orders/{poId}/remaining',
            [TssdDistributionController::class, 'getRemaining']
        )
            ->whereNumber('poId')
            ->name('purchase-orders.remaining');

        Route::get(
            '/distributions/{distribution}/print',
            [TssdDistributionController::class, 'print']
        )
            ->whereNumber('distribution')
            ->name('distributions.print');

        Route::resource(
            'distributions',
            TssdDistributionController::class
        )->only([
            'index',
            'create',
            'store',
            'show',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Call-Offs
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/call-offs',
            [CallOffController::class, 'index']
        )->name('call-offs.index');

        Route::get(
            '/call-offs/{callOff}/print',
            [CallOffController::class, 'print']
        )
            ->whereNumber('callOff')
            ->name('call-offs.print');

        Route::get(
            '/call-offs/{callOff}',
            [CallOffController::class, 'show']
        )
            ->whereNumber('callOff')
            ->name('call-offs.show');

        /*
        |--------------------------------------------------------------------------
        | PDF Templates
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/pdf-templates',
            [PdfTemplateController::class, 'index']
        )->name('pdf-templates.index');

        Route::get(
            '/pdf-templates/create',
            [PdfTemplateController::class, 'create']
        )->name('pdf-templates.create');

        Route::post(
            '/pdf-templates',
            [PdfTemplateController::class, 'store']
        )->name('pdf-templates.store');

        Route::get(
            '/pdf-templates/{pdfTemplate}',
            [PdfTemplateController::class, 'show']
        )
            ->whereNumber('pdfTemplate')
            ->name('pdf-templates.show');

        Route::get(
            '/pdf-templates/{pdfTemplate}/preview',
            [PdfTemplateController::class, 'preview']
        )
            ->whereNumber('pdfTemplate')
            ->name('pdf-templates.preview');

        Route::get(
            '/pdf-templates/{pdfTemplate}/download',
            [PdfTemplateController::class, 'download']
        )
            ->whereNumber('pdfTemplate')
            ->name('pdf-templates.download');

        Route::get(
            '/pdf-templates/{pdfTemplate}/edit',
            [PdfTemplateController::class, 'edit']
        )
            ->whereNumber('pdfTemplate')
            ->name('pdf-templates.edit');

        Route::put(
            '/pdf-templates/{pdfTemplate}',
            [PdfTemplateController::class, 'update']
        )
            ->whereNumber('pdfTemplate')
            ->name('pdf-templates.update');

        Route::patch(
            '/pdf-templates/{pdfTemplate}/activate',
            [PdfTemplateController::class, 'activate']
        )
            ->whereNumber('pdfTemplate')
            ->name('pdf-templates.activate');

        Route::patch(
            '/pdf-templates/{pdfTemplate}/deactivate',
            [PdfTemplateController::class, 'deactivate']
        )
            ->whereNumber('pdfTemplate')
            ->name('pdf-templates.deactivate');

        Route::delete(
            '/pdf-templates/{pdfTemplate}',
            [PdfTemplateController::class, 'destroy']
        )
            ->whereNumber('pdfTemplate')
            ->name('pdf-templates.destroy');

        /*
        |--------------------------------------------------------------------------
        | User Management
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/users',
            [UserManagementController::class, 'index']
        )->name('users.index');

        Route::get(
            '/users/{user}',
            [UserManagementController::class, 'show']
        )
            ->whereNumber('user')
            ->name('users.show');

        Route::get(
            '/users/{user}/edit',
            [UserManagementController::class, 'edit']
        )
            ->whereNumber('user')
            ->name('users.edit');

        Route::put(
            '/users/{user}',
            [UserManagementController::class, 'update']
        )
            ->whereNumber('user')
            ->name('users.update');

        /*
        |--------------------------------------------------------------------------
        | Letter to IMSD
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/call-off-letters',
            [CallOffLetterController::class, 'index']
        )->name('call-off-letters.index');

        Route::get(
            '/call-off-letters/{callOff}/edit',
            [CallOffLetterController::class, 'edit']
        )
            ->whereNumber('callOff')
            ->name('call-off-letters.edit');

        Route::put(
            '/call-off-letters/{callOff}',
            [CallOffLetterController::class, 'update']
        )
            ->whereNumber('callOff')
            ->name('call-off-letters.update');

        Route::get(
            '/call-off-letters/{callOff}/print',
            [CallOffLetterController::class, 'print']
        )
            ->whereNumber('callOff')
            ->name('call-off-letters.print');
    });