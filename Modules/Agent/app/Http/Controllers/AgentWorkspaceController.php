<?php

namespace Modules\Agent\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\ChatSession;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AgentWorkspaceController extends Controller
{
    public function index(Request $request): View
    {
        $userId = $request->user()->id;
        $initialPrompt = $request->query('prompt');
        $ticker = $request->query('ticker');
        $context = $request->query('context');
        $tickers = $request->query('tickers');

        if (! $initialPrompt && $ticker) {
            $initialPrompt = "Analisis prospek saham {$ticker} terkini secara teknikal dan fundamental.";
        } elseif (! $initialPrompt && $context === 'watchlist' && $tickers) {
            $initialPrompt = "Berikan perbandingan dan rekomendasi rotasi portofolio untuk saham berikut: {$tickers}.";
        }

        $sessions = ChatSession::where('user_id', $userId)
            ->orderByDesc('is_pinned')
            ->orderByDesc('updated_at')
            ->get(['id', 'title', 'is_pinned', 'updated_at']);

        $activeSessionId = $request->query('session_id');

        if ($initialPrompt && ! $activeSessionId) {
            $sessionTitle = $ticker
                ? "Riset {$ticker} - ".now()->format('d M H:i')
                : 'Analisis Pasar - '.now()->format('d M H:i');

            $activeSession = ChatSession::create([
                'user_id' => $userId,
                'title' => $sessionTitle,
                'is_pinned' => false,
            ]);

            $activeSessionId = $activeSession->id;
            $sessions->prepend($activeSession);
        } else {
            $activeSessionId = $activeSessionId ?: optional($sessions->first())->id;
            $activeSession = null;

            if ($activeSessionId) {
                $activeSession = ChatSession::with(['messages.stepLogs'])
                    ->where('id', $activeSessionId)
                    ->where('user_id', $userId)
                    ->first();
            }
        }

        return view('agent::workspace', compact('sessions', 'activeSession', 'initialPrompt', 'ticker'));
    }

    public function storeSession(Request $request): JsonResponse
    {
        $request->validate([
            'title' => 'nullable|string|max:100',
        ]);

        $session = ChatSession::create([
            'user_id' => $request->user()->id,
            'title' => $request->title ?? 'Riset Baru '.now()->format('d/m/Y H:i'),
            'is_pinned' => false,
        ]);

        return response()->json(['success' => true, 'session' => $session], 201);
    }

    public function loadSession(Request $request, string $sessionId): JsonResponse
    {
        $session = ChatSession::with(['messages.stepLogs'])
            ->where('id', $sessionId)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'session' => $session,
        ]);
    }

    public function togglePin(Request $request, string $sessionId): JsonResponse
    {
        $session = ChatSession::where('id', $sessionId)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        $session->update(['is_pinned' => ! $session->is_pinned]);

        return response()->json([
            'success' => true,
            'is_pinned' => $session->is_pinned,
            'updated_at' => optional($session->updated_at)->toISOString(),
        ]);
    }

    public function renameSession(Request $request, string $sessionId): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:100',
        ]);

        $session = ChatSession::where('id', $sessionId)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        $session->update(['title' => $validated['title']]);

        return response()->json([
            'success' => true,
            'title' => $session->title,
            'updated_at' => optional($session->updated_at)->toISOString(),
        ]);
    }

    public function destroySession(Request $request, string $sessionId): JsonResponse
    {
        $session = ChatSession::where('id', $sessionId)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        // Messages and their step logs cascade via the schema's foreign keys;
        // delete explicitly so the behaviour is identical if that ever changes.
        $session->messages()->delete();
        $session->delete();

        return response()->json(['success' => true]);
    }
}
