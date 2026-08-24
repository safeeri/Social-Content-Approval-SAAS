<?php

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
});
