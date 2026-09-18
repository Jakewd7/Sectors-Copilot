<?php

namespace Modules\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\AdminAuditLog;
use App\Models\MarketInsight;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class MarketInsightCmsController extends Controller
{
    public function index(Request $request)
    {
        $totalArticles = MarketInsight::count();
        $publishedArticles = MarketInsight::whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->count();
        $draftArticles = MarketInsight::whereNull('published_at')
            ->orWhere('published_at', '>', now())
            ->count();

        $stats = [
            ['label' => 'Total Articles', 'value' => number_format($totalArticles)],
            ['label' => 'Published', 'value' => number_format($publishedArticles)],
            ['label' => 'Drafts', 'value' => number_format($draftArticles)],
        ];

        $insightsData = MarketInsight::latest('created_at')->get();

        $insights = $insightsData->map(function ($item) {
            $isPublished = $item->published_at && $item->published_at <= now();

            return [
                'id' => $item->id,
                'title' => $item->title,
                'category' => $item->category ?? 'General',
                'content' => $item->content,
                'status' => $isPublished ? 'published' : 'draft',
                'date' => $item->published_at
                    ? $item->published_at->format('M d, Y')
                    : ($item->created_at ? $item->created_at->format('M d, Y') : '-'),
            ];
        });

        return view('admin::market-insights.index', compact('stats', 'insights'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string', 'max:100'],
            'content' => ['required', 'string'],
            'action_type' => ['required', 'in:draft,publish'],
        ]);

        $publishedAt = $validated['action_type'] === 'publish' ? now() : null;
        $slug = Str::slug($validated['title']).'-'.Str::lower(Str::random(6));

        $insight = MarketInsight::create([
            'author_id' => Auth::id(),
            'title' => $validated['title'],
            'slug' => $slug,
            'category' => $validated['category'],
            'content' => $validated['content'],
            'published_at' => $publishedAt,
        ]);

        $this->recordAudit($request, 'CREATE_INSIGHT', 'market_insights', (string) $insight->id, null, [
            'title' => $insight->title,
            'category' => $insight->category,
            'is_published' => ! is_null($insight->published_at),
        ]);

        return redirect()->route('admin.insights.index')
            ->with('success', 'Market article created successfully.');
    }

    public function update(Request $request, $id)
    {
        $insight = MarketInsight::findOrFail($id);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string', 'max:100'],
            'content' => ['required', 'string'],
            'action_type' => ['required', 'in:draft,publish'],
        ]);

        $before = [
            'title' => $insight->title,
            'category' => $insight->category,
            'published_at' => $insight->published_at,
        ];

        if ($insight->title !== $validated['title']) {
            $insight->slug = Str::slug($validated['title']).'-'.Str::lower(Str::random(6));
        }

        $insight->title = $validated['title'];
        $insight->category = $validated['category'];
        $insight->content = $validated['content'];
        $insight->published_at = $validated['action_type'] === 'publish' ? now() : null;
        $insight->save();

        $after = [
            'title' => $insight->title,
            'category' => $insight->category,
            'published_at' => $insight->published_at,
        ];

        $this->recordAudit($request, 'UPDATE_INSIGHT', 'market_insights', (string) $insight->id, $before, $after);

        return redirect()->route('admin.insights.index')
            ->with('success', 'Market article updated successfully.');
    }

    public function destroy(Request $request, $id)
    {
        $insight = MarketInsight::findOrFail($id);
        $before = [
            'title' => $insight->title,
            'category' => $insight->category,
        ];

        $insight->delete();

        $this->recordAudit($request, 'DELETE_INSIGHT', 'market_insights', (string) $id, $before, null);

        return redirect()->route('admin.insights.index')
            ->with('success', 'Market article deleted successfully.');
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
