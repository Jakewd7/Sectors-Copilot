<?php

use Illuminate\Support\Facades\Route;
use Modules\Admin\Http\Controllers\AdminPageController;

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
    Route::get('/users', [AdminPageController::class, 'users'])
        ->middleware('can:admin.users.view')->name('users.index');

    Route::get('/market-insights', [AdminPageController::class, 'marketInsights'])
        ->middleware('can:admin.insights.manage')->name('insights.index');

    Route::get('/prompt-starters', [AdminPageController::class, 'promptStarters'])
        ->middleware('can:admin.prompts.manage')->name('prompts.index');

    Route::get('/caches', [AdminPageController::class, 'caches'])
        ->middleware('can:admin.system.cache-manage')->name('caches.index');

    // Roles & Access — super-admin only (admin.roles.* is not granted to `admin`).
    Route::middleware('can:admin.roles.view')->group(function () {
        Route::get('/roles', [AdminPageController::class, 'roles'])->name('roles.index');
        Route::get('/roles/{id}/edit', [AdminPageController::class, 'roleEdit'])->name('roles.edit');
        Route::get('/roles/create', [AdminPageController::class, 'roleCreate'])->name('roles.create');
    });
});
