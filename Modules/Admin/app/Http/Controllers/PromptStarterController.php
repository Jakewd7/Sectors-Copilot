<?php

namespace Modules\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\AdminAuditLog;
use App\Models\PromptStarter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PromptStarterController extends Controller
{
    public function index(Request $request)
    {
        $promptsData = PromptStarter::orderBy('display_order', 'asc')
            ->latest('created_at')
            ->get();

        $prompts = $promptsData->map(function ($item) {
            return [
                'id' => $item->id,
                'text' => $item->prompt_text,
            ];
        });

        return view('admin::prompt-starters.index', compact('prompts'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'text' => ['required', 'string', 'max:1000'],
        ]);

        $maxOrder = PromptStarter::max('display_order') ?? 0;

        $prompt = PromptStarter::create([
            'user_id' => Auth::id(),
            'prompt_text' => $validated['text'],
            'category' => 'General',
            'is_active' => true,
            'display_order' => $maxOrder + 1,
        ]);

        $this->recordAudit($request, 'CREATE_PROMPT', 'prompt_starters', (string) $prompt->id, null, [
            'prompt_text' => $prompt->prompt_text,
        ]);

        return redirect()->route('admin.prompts.index')
            ->with('success', 'Prompt starter created successfully.');
    }

    public function update(Request $request, $id)
    {
        $prompt = PromptStarter::findOrFail($id);

        $validated = $request->validate([
            'text' => ['required', 'string', 'max:1000'],
        ]);

        $before = [
            'prompt_text' => $prompt->prompt_text,
        ];

        $prompt->update([
            'prompt_text' => $validated['text'],
        ]);

        $this->recordAudit($request, 'UPDATE_PROMPT', 'prompt_starters', (string) $prompt->id, $before, [
            'prompt_text' => $prompt->prompt_text,
        ]);

        return redirect()->route('admin.prompts.index')
            ->with('success', 'Prompt starter updated successfully.');
    }

    public function destroy(Request $request, $id)
    {
        $prompt = PromptStarter::findOrFail($id);

        $before = [
            'prompt_text' => $prompt->prompt_text,
        ];

        $prompt->delete();

        $this->recordAudit($request, 'DELETE_PROMPT', 'prompt_starters', (string) $id, $before, null);

        return redirect()->route('admin.prompts.index')
            ->with('success', 'Prompt starter deleted successfully.');
    }

    protected function recordAudit(Request $request, $action, $targetTable, $targetId, $before = null, $after = null)
    {
        AdminAuditLog::create([
            'admin_id' => Auth::id(),
            'action' => strtoupper($action),
            'target_table' => $targetTable,
            'target_id' => $targetId,
            'before_payload' => $before,
            'after_payload' => $after,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);
    }
}