<?php

use App\Http\Controllers\Company\ClientController;
use App\Http\Controllers\Company\TeamUserController;
use App\Http\Controllers\Saas\CompanyController;
use App\Http\Controllers\Saas\PlatformController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {

    // ---- Global SaaS administration -------------------------------------
    Route::middleware('role:saas_admin')
        ->prefix('saas')
        ->name('saas.')
        ->group(function () {
            Route::resource('companies', CompanyController::class)->except(['show']);
            Route::resource('platforms', PlatformController::class)->except(['show']);
        });

    // ---- Tenant administration (company_admin) --------------------------
    Route::middleware('role:company_admin')->group(function () {
        Route::resource('clients', ClientController::class)->except(['show']);
        Route::resource('team', TeamUserController::class)
            ->parameters(['team' => 'user'])
            ->except(['show']);
    });
});
