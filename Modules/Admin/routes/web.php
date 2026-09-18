<?php

use Illuminate\Support\Facades\Route;
use Modules\Admin\Http\Controllers\AdminPageController;
use Modules\Admin\Http\Controllers\MarketInsightCmsController;
use Modules\Admin\Http\Controllers\UserManagementController;

/*
|--------------------------------------------------------------------------
| Admin Panel — page routes
|--------------------------------------------------------------------------
| View-rendering routes only. Every dataset is DUMMY at the controller level
| (marked with TODO comments) — the backend developer will wire real models,
| mutations and policies later.
|
| Every route is guarded by the Spatie permission that owns that page, so the
| sidebar/URL access can never drift from the seeded permission matrix:
|   users            -> admin.users.view
|   market insights  -> admin.insights.manage
|   prompt starters  -> admin.prompts.manage
|   cache            -> admin.system.cache-manage
|   roles & access   -> admin.roles.view  (super-admin only)
|
| Note: the `verified` middleware is intentionally omitted — email
| verification is disabled in config/fortify.php (Features::emailVerification
| commented out), so no verification.notice route exists and any user with a
| NULL email_verified_at could never pass it.
*/

Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    Route::middleware('can:admin.users.view')->group(function () {
        Route::get('/users', [UserManagementController::class, 'index'])->name('users.index');
    });

    Route::middleware('can:admin.users.manage')->group(function () {
        Route::post('/users', [UserManagementController::class, 'store'])->name('users.store');
        Route::put('/users/{id}', [UserManagementController::class, 'update'])->name('users.update');
        Route::patch('/users/{id}/suspend', [UserManagementController::class, 'toggleSuspend'])
            ->name('users.suspend');
    });

    Route::middleware('can:admin.insights.manage')->group(function () {
        Route::get('/market-insights', [MarketInsightCmsController::class, 'index'])->name('insights.index');
        Route::post('/market-insights', [MarketInsightCmsController::class, 'store'])->name('insights.store');
        Route::put('/market-insights/{id}', [MarketInsightCmsController::class, 'update'])->name('insights.update');
        Route::delete('/market-insights/{id}', [MarketInsightCmsController::class, 'destroy'])->name('insights.destroy');
    });

    Route::middleware('can:admin.prompts.manage')->group(function () {
        Route::get('/prompt-starters', [AdminPageController::class, 'promptStarters'])->name('prompts.index');
    });

    Route::middleware('can:admin.system.cache-manage')->group(function () {
        Route::get('/caches', [AdminPageController::class, 'caches'])->name('caches.index');
    });

    Route::middleware('can:admin.roles.view')->group(function () {
        Route::get('/roles', [AdminPageController::class, 'roles'])->name('roles.index');
        Route::get('/roles/{id}/edit', [AdminPageController::class, 'roleEdit'])->name('roles.edit');
        Route::get('/roles/create', [AdminPageController::class, 'roleCreate'])->name('roles.create');
    });
});
