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
| Note: the `verified` middleware is intentionally omitted — email
| verification is disabled in config/fortify.php (Features::emailVerification
| commented out), so no verification.notice route exists and any user with a
| NULL email_verified_at could never pass it.
*/

Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/users', [AdminPageController::class, 'users'])->name('users.index');
    Route::get('/market-insights', [AdminPageController::class, 'marketInsights'])->name('insights.index');
    Route::get('/prompt-starters', [AdminPageController::class, 'promptStarters'])->name('prompts.index');
    Route::get('/caches', [AdminPageController::class, 'caches'])->name('caches.index');
    Route::get('/roles', [AdminPageController::class, 'roles'])->name('roles.index');
    Route::get('/roles/{id}/edit', [AdminPageController::class, 'roleEdit'])->name('roles.edit');
    Route::get('/roles/create', [AdminPageController::class, 'roleCreate'])->name('roles.create');
});
