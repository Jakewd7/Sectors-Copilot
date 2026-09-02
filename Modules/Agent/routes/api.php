<?php

use Illuminate\Support\Facades\Route;
use Modules\Agent\Http\Controllers\AgentChatController;
use Modules\Agent\Http\Controllers\AgentController;
use Modules\Agent\Http\Controllers\ReportExportController;

Route::middleware(['auth:sanctum'])->prefix('v1/agent')->group(function () {
    Route::post('/chat/stream', [AgentChatController::class, 'stream']);
    Route::get('/sessions/{session}/export/md', [ReportExportController::class, 'exportMarkdown']);
    Route::get('/sessions/{session}/export/pdf', [ReportExportController::class, 'exportPdf']);
});