<?php

namespace Modules\Dashboard\Services;

use App\Models\MarketInsight;
use App\Models\TelemetryLog;
use App\Models\Watchlist;
use Illuminate\Support\Facades\DB;
use Modules\SectorsData\Services\CachedSectorsService;

class DashboardAnalyticsService
{
    public function __construct(
        protected CachedSectorsService $sectorsService
    ) {
    }

    public function getCreditShieldTelemetry(): array
    {
        $stats = TelemetryLog::select(
            DB::raw("COUNT(*) FILTER (WHERE event_type = 'api_cache_hit') as hits"),
            DB::raw("COUNT(*) FILTER (WHERE event_type = 'api_cache_miss') as misses")
        )->first();

        $totalHits = (int) ($stats->hits ?? 0);
        $totalMisses = (int) ($stats->misses ?? 0);
        $totalRequests = $totalHits + $totalMisses;
        $quotaLimit = 1000;

        return [
            'quota_limit' => $quotaLimit,
            'credits_used' => $totalMisses,
            'credits_remaining' => max(0, $quotaLimit - $totalMisses),
            'hit_rate_percentage' => $totalRequests > 0
                ? round(($totalHits / $totalRequests) * 100, 1)
                : 0.0,
            'total_requests' => $totalRequests,
        ];
    }

    public function getUserWatchlistPayload(?string $userId): array
    {
        if (!$userId) {
            return ['id' => null, 'name' => 'Watchlist', 'items' => []];
        }

        $watchlist = Watchlist::with('items')->where('user_id', $userId)->first();

        if (!$watchlist || $watchlist->items->isEmpty()) {
            return ['id' => $watchlist?->id, 'name' => $watchlist?->name ?? 'Watchlist', 'items' => []];
        }

        $enrichedItems = $watchlist->items->map(function ($item) {
            $response = $this->sectorsService->getCompanyOverview($item->stock_ticker);
            $detail = $response['data'] ?? [];

            return [
                'id' => $item->id,
                'ticker' => $item->stock_ticker,
                'note' => $item->note,
                'company_name' => $detail['company_name'] ?? $item->stock_ticker,
                'sector' => $detail['overview']['sector'] ?? 'N/A',
                'sub_sector' => $detail['overview']['sub_sector'] ?? 'N/A',
                'close_price' => $detail['overview']['last_close_price'] ?? 0,
                'forward_pe' => $detail['valuation']['forward_pe'] ?? null,
                'added_at' => $item->added_at,
            ];
        })->toArray();

        return [
            'id' => $watchlist->id,
            'name' => $watchlist->name,
            'items' => $enrichedItems,
        ];
    }

    public function getSectorPerformance(): array
    {
        $trackedSectors = ['banks', 'food-beverage', 'telecommunication', 'energy', 'basic-materials'];
        $sectorCards = [];

        foreach ($trackedSectors as $sector) {
            try {
                $response = $this->sectorsService->getSubsectorPeers($sector);
                $data = $response['data'] ?? [];

                if (!empty($data)) {
                    $sectorCards[] = [
                        'slug' => $sector,
                        'name' => ucwords(str_replace('-', ' ', $sector)),
                        'benchmark' => $data['statistics'] ?? [],
                        'peers_count' => count($data['companies'] ?? []),
                    ];
                }
            } catch (\Throwable $e) {
                continue;
            }
        }

        return $sectorCards;
    }

    public function getCuratedInsights(int $limit = 4): array
    {
        return MarketInsight::published()
            ->latest('published_at')
            ->limit($limit)
            ->get(['id', 'title', 'slug', 'category', 'published_at'])
            ->toArray();
    }
}