<?php

use Illuminate\Support\Facades\Route;
use Modules\SectorsData\Http\Controllers\SectorsDataController;

// Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
//     Route::prefix('sectors')->name('sectors.')->group(function () {
//         Route::get('companies/{symbol}/overview', [SectorsDataController::class, 'companyOverview'])
//             ->name('companies.overview');

//         Route::get('companies/{symbol}/financials', [SectorsDataController::class, 'companyFinancials'])
//             ->name('companies.financials');

//         Route::get('subsectors/{subSector}/peers', [SectorsDataController::class, 'subsectorPeers'])
//             ->name('subsectors.peers');

//         Route::get('screener', [SectorsDataController::class, 'screener'])
//             ->name('screener');
//     });

//     Route::prefix('market')->name('market.')->group(function () {
//         Route::get('top-movers', [SectorsDataController::class, 'topMovers'])->name('top-movers');
//         Route::get('most-traded', [SectorsDataController::class, 'mostTraded'])->name('most-traded');
//         Route::get('summary', [SectorsDataController::class, 'marketSummary'])->name('summary');
//     });
// });
