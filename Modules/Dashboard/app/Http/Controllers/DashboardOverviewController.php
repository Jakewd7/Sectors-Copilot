<?php

namespace Modules\Dashboard\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\Dashboard\Services\DashboardAnalyticsService;
use Modules\SectorsData\Services\CachedSectorsService;

class DashboardOverviewController extends Controller
{
    public function __construct(
        protected DashboardAnalyticsService $analyticsService,
        protected CachedSectorsService $sectorsService
    ) {
    }

    public function index(Request $request): View
    {
        $userId = $request->user()?->id;
        $cachedTickers = ['BBCA', 'BBRI', 'BMRI', 'BBNI', 'TLKM', 'ASII', 'ICBP'];
        $moversData = [];

        foreach ($cachedTickers as $ticker) {
            $overview = $this->sectorsService->getCompanyOverview($ticker);
            $detail = $overview['data'] ?? [];
            if (!empty($detail)) {
                $moversData[] = [
                    'symbol' => $detail['symbol'] ?? $ticker,
                    'company_name' => $detail['company_name'] ?? $ticker,
                    'price' => $detail['overview']['last_close_price'] ?? 0,
                    'change' => $detail['overview']['daily_close_change'] ?? 0,
                    'market_cap' => $detail['overview']['market_cap'] ?? 0,
                ];
            }
        }

        return view('dashboard::index', [
            'topMovers' => $moversData,
            'mostTraded' => array_slice($moversData, 0, 5),
            'summary' => [
                'market_cap' => array_sum(array_column($moversData, 'market_cap')),
                'tracked_stocks' => count($moversData),
            ],
            'sectors' => $this->analyticsService->getSectorPerformance(),
            'watchlist' => $this->analyticsService->getUserWatchlistPayload($userId),
            'telemetry' => $this->analyticsService->getCreditShieldTelemetry(),
            'insights' => $this->analyticsService->getCuratedInsights(),
        ]);
    }

    public function telemetryData(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->analyticsService->getCreditShieldTelemetry(),
        ]);
    }
}