<?php

use Illuminate\Support\Facades\Route;
use Modules\Admin\Http\Controllers\MarketInsightCmsController;
use Modules\Admin\Http\Controllers\PromptStarterController;
use Modules\Admin\Http\Controllers\RoleController;
use Modules\Admin\Http\Controllers\SystemCacheController;
use Modules\Admin\Http\Controllers\UserManagementController;

Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    Route::resource('users', UserManagementController::class)->only([
        'index',
        'store',
        'update'
    ]);
    Route::patch('users/{id}/suspend', [UserManagementController::class, 'toggleSuspend'])
        ->name('users.suspend');
    Route::resource('insights', MarketInsightCmsController::class)->only([
        'index',
        'store',
        'update',
        'destroy'
    ]);
    Route::resource('prompts', PromptStarterController::class)->only([
        'index',
        'store',
        'update',
        'destroy'
    ]);
    Route::get('/caches', [SystemCacheController::class, 'index'])->name('caches.index');
    Route::delete('/caches/{id}/flush', [SystemCacheController::class, 'flushKey'])->name('caches.flush-key');
    Route::resource('roles', RoleController::class)->except(['show']);
});
