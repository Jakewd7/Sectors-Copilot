<?php

use Illuminate\Support\Facades\Route;
use Modules\Agent\Http\Controllers\AgentController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('agents', AgentController::class)->names('agent');
});
