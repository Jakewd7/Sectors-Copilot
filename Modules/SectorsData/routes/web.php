<?php

use Illuminate\Support\Facades\Route;
use Modules\SectorsData\Http\Controllers\SectorsDataController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('sectorsdatas', SectorsDataController::class)->names('sectorsdata');
});
