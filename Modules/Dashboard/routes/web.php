<?php

use Illuminate\Support\Facades\Route;
use Modules\Dashboard\Http\Controllers\DashboardOverviewController;
use Modules\Dashboard\Http\Controllers\MarketInsightController;
use Modules\Dashboard\Http\Controllers\WatchlistController;
use Modules\Dashboard\Http\Controllers\WatchlistPageController;

Route::get('/insights', [MarketInsightController::class, 'index'])->name('insights.index');

Route::middleware(['auth', 'can:market-insights.view'])->group(function () {
    Route::get('/insights/{slug}', [MarketInsightController::class, 'show'])->name('insights.show');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/watchlist', [WatchlistPageController::class, 'index'])->name('watchlist.index');

    Route::post('/watchlist/lists', [WatchlistPageController::class, 'store'])->name('watchlist.lists.store');
    Route::put('/watchlist/lists/{id}', [WatchlistPageController::class, 'update'])->name('watchlist.lists.update');
    Route::delete('/watchlist/lists/{id}', [WatchlistPageController::class, 'destroy'])->name('watchlist.lists.destroy');

    Route::post('/watchlist/tickers', [WatchlistController::class, 'store'])->name('watchlist.store');
    Route::delete('/watchlist/tickers/{ticker}', [WatchlistController::class, 'destroy'])->name('watchlist.destroy');
});

Route::middleware(['auth'])->prefix('dashboard')->name('dashboard.')->group(function () {
    Route::get('/', [DashboardOverviewController::class, 'index'])->name('index');
    Route::get('/telemetry/stats', [DashboardOverviewController::class, 'telemetryData'])->name('telemetry.stats');

    Route::post('/watchlist', [WatchlistController::class, 'store'])->name('watchlist.store');
    Route::delete('/watchlist/{ticker}', [WatchlistController::class, 'destroy'])->name('watchlist.destroy');
});
