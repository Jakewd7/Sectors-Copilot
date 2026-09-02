<?php

namespace Modules\Agent\Actions;

use App\Models\AgentStepLog;
use App\Models\ChatMessage;
use App\Models\ChatSession;
use Illuminate\Support\Str;
use Modules\Agent\Services\GeminiClientService;
use Modules\Agent\Tools\CompanyFinancialsTool;
use Modules\Agent\Tools\CompanyOverviewTool;
use Modules\Agent\Tools\SectorPeersTool;
use Modules\Agent\Tools\StockScreenerTool;

class OrchestrateResearchAction
{
    public function __construct(
        protected GeminiClientService $geminiService,
        protected InjectComplianceDisclaimerAction $disclaimerAction,
        CompanyOverviewTool $overviewTool,
        CompanyFinancialsTool $financialsTool,
        SectorPeersTool $peersTool,
        StockScreenerTool $screenerTool
    ) {
        $this->geminiService->registerTool($overviewTool);
        $this->geminiService->registerTool($financialsTool);
        $this->geminiService->registerTool($peersTool);
        $this->geminiService->registerTool($screenerTool);
    }

    public function execute(ChatSession $session, string $prompt, callable $sseCallback): ChatMessage
    {
        // 1. Simpan pesan pengguna
        $session->messages()->create([
            'role' => 'user',
            'content' => $prompt,
        ]);

        // 2. Buat placeholder record pesan assistant
        /** @var ChatMessage $assistantMessage */
        $assistantMessage = $session->messages()->create([
            'role' => 'assistant',
            'content' => '',
            'structured_payload' => null,
        ]);

        // Kirim event Planning ke Inspector UI
        $this->logStep($assistantMessage->id, 'planning', null, null, 'pending', ['intent' => $prompt]);
        $sseCallback('step_progress', [
            'step' => 'planning',
            'status' => 'success',
            'message' => 'Mengidentifikasi intensi riset dan merencanakan analisis data emiten...',
        ]);

        // Bangun riwayat chat sebelumnya untuk Context Memory
        $history = $session->messages()
            ->where('id', '!=', $assistantMessage->id)
            ->latest('created_at')
            ->take(6)
            ->get()
            ->reverse()
            ->map(fn($msg) => [
                'role' => $msg->role === 'assistant' ? 'model' : 'user',
                'parts' => [['text' => $msg->content ?? '']],
            ])
            ->toArray();

        $tools = $this->geminiService->getRegisteredTools();
        $toolDeclarations = $this->geminiService->getToolDeclarations();

        $chat = $this->geminiService->getClient()
            ->generativeModel(model: $this->geminiService->getModel())
            ->withSystemInstruction(
                "Anda adalah AI Research Copilot pasar modal Indonesia (IDX) yang presisi, objektif, dan faktual. " .
                "Gunakan tools yang tersedia untuk mengambil data bursa terkini sebelum menjawab pertanyaan emiten. " .
                "Sajikan narasi fundamental yang tajam dan gunakan data aktual dari tools."
            )
            ->startChat(history: $history);

        // Turn pertama LLM
        $startTime = microtime(true);
        $response = $chat->sendMessage($prompt, ['tools' => [['function_declarations' => $toolDeclarations]]]);

        $collectedPayloads = [];
        $maxLoops = 4;
        $currentLoop = 0;

        // Multi-Step Execution Loop
        while ($response->functionCalls() && $currentLoop < $maxLoops) {
            $currentLoop++;
            $functionCall = $response->functionCalls()[0];
            $toolName = $functionCall->name;
            $toolArgs = (array) $functionCall->args;

            if (isset($tools[$toolName])) {
                $sseCallback('step_progress', [
                    'step' => 'tool_execution',
                    'tool' => $toolName,
                    'status' => 'running',
                    'message' => "Memanggil Sectors API via tool [{$toolName}]...",
                ]);

                $toolResult = $tools[$toolName]->execute($toolArgs);
                $endpoint = $toolResult['endpoint'] ?? null;
                $data = $toolResult['data'] ?? [];

                $duration = (int) round((microtime(true) - $startTime) * 1000);
                $this->logStep($assistantMessage->id, 'tool_execution', $toolName, $endpoint, 'success', $data, null, $duration);
                $collectedPayloads[$toolName] = $data;

                $sseCallback('step_progress', [
                    'step' => 'tool_execution',
                    'tool' => $toolName,
                    'status' => 'success',
                    'endpoint' => $endpoint,
                    'message' => "Data berhasil diperoleh dari {$endpoint}.",
                ]);

                // Kirim kembali hasil tool ke Gemini untuk sintesis
                $response = $chat->sendMessage([
                    'functionResponse' => [
                        'name' => $toolName,
                        'response' => ['result' => $data],
                    ],
                ]);
            } else {
                break;
            }
        }

        // Tahap Sintesis Akhir
        $sseCallback('step_progress', [
            'step' => 'synthesis',
            'status' => 'running',
            'message' => 'Melakukan sintesis narasi dan menyusun matriks analisis...',
        ]);

        $finalText = $response->text();
        $finalContentWithDisclaimer = $this->disclaimerAction->execute($finalText);

        // 3. Format payload terstruktur untuk SSE dan PostgreSQL JSONB
        $structuredPayload = !empty($collectedPayloads) ? [
            'widget_type' => $this->detectWidgetType($collectedPayloads),
            'metrics' => $this->formatMetrics($collectedPayloads),
            'raw' => $collectedPayloads,
        ] : null;

        // Perbarui record chat assistant
        $assistantMessage->update([
            'content' => $finalContentWithDisclaimer,
            'structured_payload' => $structuredPayload,
        ]);

        $this->logStep($assistantMessage->id, 'synthesis', null, null, 'success', ['summary_length' => strlen($finalContentWithDisclaimer)]);

        // Kirim event selesai & payload terstruktur ke browser
        if ($structuredPayload !== null) {
            $sseCallback('structured_payload', $structuredPayload);
        }

        $sseCallback('done', [
            'message_id' => $assistantMessage->id,
            'content' => $finalContentWithDisclaimer,
        ]);

        return $assistantMessage;
    }

    /**
     * Mendeteksi jenis widget UI berdasarkan payload tool yang dieksekusi.
     */
    protected function detectWidgetType(array $collectedPayloads): string
    {
        if (isset($collectedPayloads['stock_screener'])) {
            return 'screener_table';
        }

        if (isset($collectedPayloads['sector_peers'])) {
            return 'peers_comparison';
        }

        if (isset($collectedPayloads['company_financials'])) {
            return 'financial_matrix';
        }

        if (isset($collectedPayloads['company_overview'])) {
            return 'company_overview';
        }

        return 'generic';
    }

    /**
     * Ekstraksi metrik-metrik kunci dari respon API agar siap dirender oleh kartu metrik frontend.
     */
    protected function formatMetrics(array $collectedPayloads): array
    {
        $metrics = [];

        // Ambil metrik dari Company Financials jika ada
        if (isset($collectedPayloads['company_financials'])) {
            $fin = $collectedPayloads['company_financials'];
            $metrics['pe_ratio'] = $fin['pe_ratio'] ?? $fin['pe'] ?? null;
            $metrics['pbv_ratio'] = $fin['pbv_ratio'] ?? $fin['pbv'] ?? null;
            $metrics['roe'] = $fin['roe'] ?? null;
            $metrics['roa'] = $fin['roa'] ?? null;
            $metrics['net_profit_margin'] = $fin['net_profit_margin'] ?? $fin['npm'] ?? null;
            $metrics['debt_to_equity'] = $fin['debt_to_equity'] ?? $fin['der'] ?? null;
        }

        // Ambil metrik dari Company Overview jika ada
        if (isset($collectedPayloads['company_overview'])) {
            $overview = $collectedPayloads['company_overview'];
            $metrics['symbol'] = $overview['symbol'] ?? $overview['ticker'] ?? null;
            $metrics['company_name'] = $overview['name'] ?? $overview['company_name'] ?? null;
            $metrics['market_cap'] = $overview['market_cap'] ?? null;
            $metrics['sector'] = $overview['sector'] ?? null;
            $metrics['sub_sector'] = $overview['sub_sector'] ?? null;
            $metrics['last_price'] = $overview['price'] ?? $overview['last_price'] ?? null;
        }

        // Filter null values agar payload tetap ringkas
        return array_filter($metrics, fn($value) => !is_null($value));
    }

    protected function logStep(
        string $messageId,
        string $stepType,
        ?string $toolName = null,
        ?string $endpoint = null,
        string $status = 'pending',
        ?array $payload = null,
        ?string $errorMessage = null,
        ?int $durationMs = null
    ): void {
        AgentStepLog::create([
            'id' => Str::uuid(),
            'chat_message_id' => $messageId,
            'step_type' => $stepType,
            'tool_name' => $toolName,
            'endpoint' => $endpoint,
            'status' => $status,
            'payload_data' => $payload,
            'error_message' => $errorMessage,
            'duration_ms' => $durationMs,
        ]);
    }
}