<?php

use Illuminate\Support\Facades\Route;
use Modules\Admin\Http\Controllers\MarketInsightCmsController;
use Modules\Admin\Http\Controllers\PromptStarterController;
use Modules\Admin\Http\Controllers\RoleController;
use Modules\Admin\Http\Controllers\SystemCacheController;
use Modules\Admin\Http\Controllers\UserManagementController;

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
        Route::get('/prompt-starters', [PromptStarterController::class, 'index'])->name('prompts.index');
        Route::post('/prompt-starters', [PromptStarterController::class, 'store'])->name('prompts.store');
        Route::put('/prompt-starters/{id}', [PromptStarterController::class, 'update'])->name('prompts.update');
        Route::delete('/prompt-starters/{id}', [PromptStarterController::class, 'destroy'])->name('prompts.destroy');
    });

    Route::middleware('can:admin.system.cache-manage')->group(function () {
        Route::get('/caches', [SystemCacheController::class, 'index'])->name('caches.index');
        Route::delete('/caches/{id}/flush', [SystemCacheController::class, 'flushKey'])->name('caches.flush-key');
    });

    Route::middleware('can:admin.roles.view')->group(function () {
        Route::get('/roles', [RoleController::class, 'index'])->name('roles.index');
        Route::get('/roles/create', [RoleController::class, 'create'])->name('roles.create');
        Route::get('/roles/{id}/edit', [RoleController::class, 'edit'])->name('roles.edit');
    });

    Route::middleware('can:admin.roles.manage')->group(function () {
        Route::post('/roles', [RoleController::class, 'store'])->name('roles.store');
        Route::put('/roles/{id}', [RoleController::class, 'update'])->name('roles.update');
        Route::delete('/roles/{id}', [RoleController::class, 'destroy'])->name('roles.destroy');
    });
});
