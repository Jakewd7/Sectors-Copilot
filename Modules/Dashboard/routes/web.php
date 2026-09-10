<?php

use Illuminate\Support\Facades\Route;
use Modules\Dashboard\Http\Controllers\DashboardOverviewController;
use Modules\Dashboard\Http\Controllers\WatchlistController;
use Modules\Dashboard\Http\Controllers\DashboardController;

Route::middleware(['auth'])->prefix('dashboard')->name('dashboard.')->group(function () {
    // Halaman Utama Dashboard
    Route::get('/', [DashboardOverviewController::class, 'index'])->name('index');
    Route::get('/telemetry/stats', [DashboardOverviewController::class, 'telemetryData'])->name('telemetry.stats');

    // Manajemen Watchlist
    Route::post('/watchlist', [WatchlistController::class, 'store'])->name('watchlist.store');
    Route::delete('/watchlist/{ticker}', [WatchlistController::class, 'destroy'])->name('watchlist.destroy');
});
