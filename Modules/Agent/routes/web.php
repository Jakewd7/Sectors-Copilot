<?php

use Illuminate\Support\Facades\Route;
use Modules\Agent\Http\Controllers\AgentController;
use Modules\Agent\Http\Controllers\AgentWorkspaceController;

Route::middleware(['auth'])->prefix('agent')->group(function () {
    Route::get('/workspace', [AgentWorkspaceController::class, 'index'])->name('agent.workspace');
    Route::post('/sessions', [AgentWorkspaceController::class, 'storeSession'])->name('agent.sessions.store');
    Route::get('/sessions/{session}', [AgentWorkspaceController::class, 'loadSession'])->name('agent.sessions.show');
    Route::patch('/sessions/{session}/pin', [AgentWorkspaceController::class, 'togglePin'])->name('agent.sessions.pin');
});
