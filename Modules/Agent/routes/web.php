<?php

use Illuminate\Support\Facades\Route;
use Modules\Agent\Http\Controllers\AgentChatController;
use Modules\Agent\Http\Controllers\AgentWorkspaceController;
use Modules\Agent\Http\Controllers\ReportExportController;

Route::middleware(['auth'])->prefix('agent')->group(function () {
    Route::get('/workspace', [AgentWorkspaceController::class, 'index'])->name('agent.workspace');
    Route::post('/sessions', [AgentWorkspaceController::class, 'storeSession'])->name('agent.sessions.store');
    Route::get('/sessions/{session}', [AgentWorkspaceController::class, 'loadSession'])->name('agent.sessions.show');
    Route::patch('/sessions/{session}/pin', [AgentWorkspaceController::class, 'togglePin'])->name('agent.sessions.pin');
    Route::patch('/sessions/{session}/rename', [AgentWorkspaceController::class, 'renameSession'])->name('agent.sessions.rename');
    Route::delete('/sessions/{session}', [AgentWorkspaceController::class, 'destroySession'])->name('agent.sessions.destroy');
});

Route::middleware(['auth'])->prefix('api/v1/agent')->group(function () {
    Route::match(['get', 'post'], '/chat/stream', [AgentChatController::class, 'stream']);
    Route::get('/sessions/{session}/export/md', [ReportExportController::class, 'exportMarkdown']);
    Route::get('/sessions/{session}/export/pdf', [ReportExportController::class, 'exportPdf']);
});
