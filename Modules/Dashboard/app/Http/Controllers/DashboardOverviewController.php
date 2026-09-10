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

        $topMovers = $this->sectorsService->getTopCompanyMovers([
            'n_stock' => 5,
            'classifications' => 'top_gainers,top_losers',
            'periods' => '1d,7d',
        ]);

        $mostTraded = $this->sectorsService->getMostTradedStocks([
            'n_stock' => 5,
            'adjusted' => 'true',
        ]);

        $marketSummary = $this->sectorsService->getIdxMarketSummary();

        return view('dashboard::index', [
            'topMovers' => $topMovers['data'] ?? [],
            'mostTraded' => $mostTraded['data'] ?? [],
            'summary' => $marketSummary['data'] ?? [],
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