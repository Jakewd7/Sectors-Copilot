<?php

namespace Modules\Agent\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\ChatSession;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ReportExportController extends Controller
{
    public function exportMarkdown(Request $request, string $sessionId): Response
    {
        $session = ChatSession::with('messages')
            ->where('id', $sessionId)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        $markdown = "# Laporan Riset Saham: {$session->title}\n\n";
        $markdown .= '*Tanggal Ekspor: '.now()->format('d M Y H:i')."*\n\n---\n\n";

        foreach ($session->messages as $msg) {
            $roleLabel = $msg->role === 'user' ? '### 👤 Pertanyaan Riset' : '### 🤖 Analisis Copilot';
            $markdown .= "{$roleLabel}\n\n".$msg->content."\n\n";
        }

        return response($markdown, 200, [
            'Content-Type' => 'text/markdown',
            'Content-Disposition' => "attachment; filename=\"riset-{$session->id}.md\"",
        ]);
    }

    public function exportPdf(Request $request, string $sessionId): Response
    {
        $session = ChatSession::with('messages')
            ->where('id', $sessionId)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        $pdf = Pdf::loadView('agent::exports.report-pdf', [
            'session' => $session,
            'messages' => $session->messages,
            'exportedAt' => now(),
        ])->setOption('isFontSubsettingEnabled', true);

        return $pdf->download("laporan-riset-{$session->id}.pdf");
    }
}
