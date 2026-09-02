<?php

namespace Modules\Agent\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\ChatSession;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AgentWorkspaceController extends Controller
{
    /**
     * Render layar utama workspace chat split-view
     */
    public function index(Request $request): View
    {
        $userId = $request->user()->id;

        $sessions = ChatSession::where('user_id', $userId)
            ->orderByDesc('is_pinned')
            ->orderByDesc('updated_at')
            ->get(['id', 'title', 'is_pinned', 'created_at']);

        $activeSessionId = $request->query('session_id', optional($sessions->first())->id);
        $activeSession = null;

        if ($activeSessionId) {
            $activeSession = ChatSession::with(['messages.stepLogs'])
                ->where('id', $activeSessionId)
                ->where('user_id', $userId)
                ->first();
        }

        return view('agent::workspace', compact('sessions', 'activeSession'));
    }

    /**
     * Buat sesi riset baru
     */
    public function storeSession(Request $request): JsonResponse
    {
        $request->validate([
            'title' => 'nullable|string|max:100',
        ]);

        $session = ChatSession::create([
            'user_id' => $request->user()->id,
            'title' => $request->title ?? 'Riset Baru ' . now()->format('d/m/Y H:i'),
            'is_pinned' => false,
        ]);

        return response()->json(['success' => true, 'session' => $session], 201);
    }

    /**
     * Muat riwayat pesan untuk sesi tertentu (AJAX Switcher)
     */
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

    /**
     * Pin / Unpin sesi
     */
    public function togglePin(Request $request, string $sessionId): JsonResponse
    {
        $session = ChatSession::where('id', $sessionId)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        $session->update(['is_pinned' => !$session->is_pinned]);

        return response()->json(['success' => true, 'is_pinned' => $session->is_pinned]);
    }
}