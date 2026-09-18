<?php

use Illuminate\Support\Facades\Route;
use Modules\Dashboard\Http\Controllers\DashboardOverviewController;
use Modules\Dashboard\Http\Controllers\MarketInsightController;
use Modules\Dashboard\Http\Controllers\WatchlistController;

Route::get('/insights', [MarketInsightController::class, 'index'])->name('insights.index');

Route::middleware(['auth', 'can:market-insights.view'])->group(function () {
    Route::get('/insights/{slug}', [MarketInsightController::class, 'show'])->name('insights.show');
});

Route::middleware(['auth'])->prefix('dashboard')->name('dashboard.')->group(function () {
    Route::get('/', [DashboardOverviewController::class, 'index'])->name('index');
    Route::get('/telemetry/stats', [DashboardOverviewController::class, 'telemetryData'])->name('telemetry.stats');

    Route::post('/watchlist', [WatchlistController::class, 'store'])->name('watchlist.store');
    Route::delete('/watchlist/{ticker}', [WatchlistController::class, 'destroy'])->name('watchlist.destroy');
});
