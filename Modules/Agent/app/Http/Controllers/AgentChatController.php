<?php

namespace Modules\Agent\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\ChatSession;
use Illuminate\Http\Request;
use Modules\Agent\Actions\OrchestrateResearchAction;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AgentChatController extends Controller
{
    public function stream(Request $request, OrchestrateResearchAction $orchestrator): StreamedResponse
    {
        $request->validate([
            'chat_session_id' => 'required|uuid|exists:chat_sessions,id',
            'prompt' => 'required|string|max:2000',
        ]);

        $session = ChatSession::where('id', $request->chat_session_id)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        $prompt = $request->prompt;

        return response()->stream(function () use ($orchestrator, $session, $prompt) {
            $sseSender = function (string $event, array $data) {
                echo "event: {$event}\n";
                echo 'data: ' . json_encode($data) . "\n\n";
                if (ob_get_level() > 0) {
                    ob_flush();
                }
                flush();
            };

            try {
                $orchestrator->execute($session, $prompt, $sseSender);
            } catch (\Throwable $e) {
                $sseSender('error', [
                    'message' => 'Gagal memproses riset: ' . $e->getMessage(),
                ]);
            }
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'Connection' => 'keep-alive',
            'X-Accel-Buffering' => 'no',
        ]);
    }
}