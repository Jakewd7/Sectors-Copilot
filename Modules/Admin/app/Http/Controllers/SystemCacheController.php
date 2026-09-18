<?php

namespace Modules\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\AdminAuditLog;
use App\Models\AgentStepLog;
use App\Models\ApiCache;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SystemCacheController extends Controller
{
    public function index(Request $request)
    {
        $activeModel = config('services.gemini.model') ?? env('GEMINI_MODEL', 'Gemini 1.5 Flash');

        $totalKeys = ApiCache::count();
        $totalHits = ApiCache::sum('hit_count') ?? 0;

        $totalRequests = $totalKeys + $totalHits;
        $hitRate = $totalRequests > 0 ? round(($totalHits / $totalRequests) * 100) : 0;

        $totalCredits = 1000;
        $usedCredits = ApiCache::where('created_at', '>=', now()->startOfMonth())->count();
        $remainingCredits = max(0, $totalCredits - $usedCredits);

        $stats = [
            ['label' => 'Total Cached Items', 'value' => number_format($totalKeys)],
            ['label' => 'Total Cache Hits', 'value' => number_format($totalHits)],
            ['label' => 'Sectors API Credits', 'value' => "{$remainingCredits} / {$totalCredits}"],
            ['label' => 'Cache hit rate', 'value' => "{$hitRate}%"],
        ];

        $todaySteps = AgentStepLog::where('created_at', '>=', Carbon::today())->count();
        $estimatedTokensToday = $todaySteps * 1250;
        $dailyTokenLimit = 500000;
        $percentUsed = min(100, round(($estimatedTokensToday / $dailyTokenLimit) * 100, 1));

        $llmUsage = [
            'daily' => [
                'label' => number_format($estimatedTokensToday / 1000, 1).'k / '.number_format($dailyTokenLimit / 1000, 0).'k tokens',
                'percent' => $percentUsed,
                'percentLabel' => $percentUsed.'% used',
                'meta' => 'Resets daily at 00:00 WIB • '.number_format($todaySteps).' agent executions today',
                'models' => [
                    [
                        'name' => $activeModel,
                        'share' => $percentUsed,
                        'requests' => number_format($todaySteps),
                        'color' => '#3b82f6',
                    ],
                ],
            ],
        ];

        $cacheEntries = ApiCache::latest('updated_at')
            ->paginate(15)
            ->withQueryString();

        $entries = collect($cacheEntries->items())->map(function ($item) {
            return [
                'id' => $item->id,
                'key' => $item->cache_key,
                'provider' => $item->provider,
                'endpoint' => $item->endpoint,
                'expires_at' => $item->expires_at ? $item->expires_at->format('Y-m-d H:i:s') : 'Never',
                'is_expired' => $item->expires_at ? $item->expires_at->isPast() : false,
            ];
        });

        return view('admin::caches.index', compact('stats', 'llmUsage', 'entries', 'cacheEntries'));
    }

    public function flushKey(Request $request, $id)
    {
        $cache = ApiCache::findOrFail($id);

        $cacheInfo = [
            'cache_key' => $cache->cache_key,
            'provider' => $cache->provider,
            'endpoint' => $cache->endpoint,
            'hit_count' => $cache->hit_count,
        ];

        $cache->delete();

        $this->recordAudit($request, 'FLUSH_CACHE_KEY', 'api_caches', (string) $id, $cacheInfo, null);

        return redirect()->route('admin.caches.index')
            ->with('success', "Cache key [{$cacheInfo['cache_key']}] flushed successfully.");
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
