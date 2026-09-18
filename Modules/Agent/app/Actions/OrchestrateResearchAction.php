<?php

namespace Modules\Agent\Actions;

use App\Models\AgentStepLog;
use App\Models\ChatMessage;
use App\Models\ChatSession;
use Gemini\Data\Content;
use Gemini\Data\FunctionResponse;
use Gemini\Data\Part;
use Gemini\Data\Tool;
use Gemini\Enums\Role;
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
        $session->messages()->create([
            'role' => 'user',
            'content' => $prompt,
        ]);

        $assistantMessage = $session->messages()->create([
            'role' => 'assistant',
            'content' => '',
            'structured_payload' => null,
        ]);

        $this->logStep($assistantMessage->id, 'planning', null, null, 'pending', ['intent' => $prompt]);
        $sseCallback('step_progress', [
            'step' => 'planning',
            'status' => 'success',
            'message' => 'Mengidentifikasi intensi riset dan merencanakan analisis data emiten...',
        ]);

        $history = $session->messages()
            ->where('id', '!=', $assistantMessage->id)
            ->latest('created_at')
            ->take(6)
            ->get()
            ->reverse()
            ->map(fn ($msg) => Content::parse(
                part: $msg->content ?? '',
                role: $msg->role === 'assistant' ? Role::MODEL : Role::USER
            ))
            ->values()
            ->toArray();

        $tools = $this->geminiService->getRegisteredTools();
        $toolDeclarations = $this->geminiService->getToolDeclarations();

        $systemInstructionText = 'Anda adalah AI Research Copilot pasar modal Indonesia (IDX) yang presisi, objektif, dan faktual. '.
            'Gunakan tools yang tersedia untuk mengambil data bursa terkini sebelum menjawab pertanyaan emiten. '.
            'Sajikan narasi fundamental yang tajam dan gunakan data aktual dari tools.';

        $chat = $this->geminiService->getClient()
            ->generativeModel(model: $this->geminiService->getModel())
            ->withSystemInstruction(Content::parse($systemInstructionText))
            ->withTool(new Tool(functionDeclarations: $toolDeclarations))
            ->startChat(history: $history);

        $startTime = microtime(true);
        $response = $chat->sendMessage($prompt);

        $collectedPayloads = [];
        $maxLoops = 4;
        $currentLoop = 0;

        while (
            isset($response->parts()[0]) &&
            $response->parts()[0]->functionCall !== null &&
            $currentLoop < $maxLoops
        ) {
            $currentLoop++;
            $functionCall = $response->parts()[0]->functionCall;
            $toolName = $functionCall->name;
            $toolArgs = (array) $functionCall->args;

            if (! isset($tools[$toolName])) {
                break;
            }

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
                'payload' => [$toolName => $data],
            ]);

            $functionResponse = new Content(
                parts: [
                    new Part(
                        functionResponse: new FunctionResponse(
                            name: $toolName,
                            response: ['result' => $data]
                        )
                    ),
                ],
                role: Role::USER
            );

            $response = $chat->sendMessage($functionResponse);
        }

        $sseCallback('step_progress', [
            'step' => 'synthesis',
            'status' => 'running',
            'message' => 'Melakukan sintesis narasi dan menyusun matriks analisis...',
        ]);

        $finalText = $response->text();
        $finalContentWithDisclaimer = $this->disclaimerAction->execute($finalText);

        $sseCallback('token', [
            'text' => $finalContentWithDisclaimer,
        ]);

        $structuredPayload = ! empty($collectedPayloads) ? [
            'widget_type' => $this->detectWidgetType($collectedPayloads),
            'metrics' => $this->formatMetrics($collectedPayloads),
            'raw' => $collectedPayloads,
            'get_company_overview' => $collectedPayloads['get_company_overview'] ?? null,
            'get_sector_peers' => $collectedPayloads['get_sector_peers'] ?? null,
            'screen_stocks' => $collectedPayloads['stock_screener'] ?? null,
        ] : null;

        $assistantMessage->update([
            'content' => $finalContentWithDisclaimer,
            'structured_payload' => $structuredPayload,
        ]);

        $this->logStep($assistantMessage->id, 'synthesis', null, null, 'success', ['summary_length' => strlen($finalContentWithDisclaimer)]);

        if ($structuredPayload !== null) {
            $sseCallback('payload', $structuredPayload);
        }

        $sseCallback('step_progress', [
            'step' => 'synthesis',
            'status' => 'success',
            'message' => 'Riset selesai dan matriks valuasi diperbarui.',
        ]);

        $sseCallback('done', [
            'message_id' => $assistantMessage->id,
            'content' => $finalContentWithDisclaimer,
        ]);

        return $assistantMessage;
    }

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

        if (isset($collectedPayloads['get_company_overview']) || isset($collectedPayloads['company_overview'])) {
            return 'company_overview';
        }

        return 'generic';
    }

    protected function formatMetrics(array $collectedPayloads): array
    {
        $metrics = [];

        if (isset($collectedPayloads['company_financials'])) {
            $fin = $collectedPayloads['company_financials'];
            $metrics['pe_ratio'] = $fin['pe_ratio'] ?? $fin['pe'] ?? null;
            $metrics['pbv_ratio'] = $fin['pbv_ratio'] ?? $fin['pbv'] ?? null;
            $metrics['roe'] = $fin['roe'] ?? null;
            $metrics['roa'] = $fin['roa'] ?? null;
            $metrics['net_profit_margin'] = $fin['net_profit_margin'] ?? $fin['npm'] ?? null;
            $metrics['debt_to_equity'] = $fin['debt_to_equity'] ?? $fin['der'] ?? null;
        }

        $overview = $collectedPayloads['get_company_overview'] ?? $collectedPayloads['company_overview'] ?? null;
        if ($overview) {
            $metrics['symbol'] = $overview['symbol'] ?? $overview['ticker'] ?? null;
            $metrics['company_name'] = $overview['name'] ?? $overview['company_name'] ?? null;
            $metrics['market_cap'] = $overview['market_cap'] ?? null;
            $metrics['sector'] = $overview['sector'] ?? null;
            $metrics['sub_sector'] = $overview['sub_sector'] ?? null;
            $metrics['last_price'] = $overview['price'] ?? $overview['last_price'] ?? null;
        }

        return array_filter($metrics, fn ($value) => ! is_null($value));
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
