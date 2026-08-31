<?php

namespace Modules\SectorsData\Services;

use App\Models\ApiCache;
use Carbon\Carbon;

class CachedSectorsService
{
    protected const PROVIDER = 'sectors_app_v2';
    protected SectorsApiClient $apiClient;

    public function __construct(SectorsApiClient $apiClient)
    {
        $this->apiClient = $apiClient;
    }

    protected function remember(string $endpoint, array $params, int $ttlHours, callable $apiCallback): array
    {
        ksort($params);
        $cacheKey = hash('sha256', self::PROVIDER . ':' . $endpoint . ':' . json_encode($params));

        $cached = ApiCache::where('cache_key', $cacheKey)->valid()->first();

        if ($cached) {
            $cached->increment('hit_count');
            return [
                'data' => $cached->response_payload,
                'is_cached' => true,
                'cached_at' => $cached->created_at,
            ];
        }

        $payload = $apiCallback();

        ApiCache::updateOrCreate(
            ['cache_key' => $cacheKey],
            [
                'provider' => self::PROVIDER,
                'endpoint' => $endpoint,
                'request_params' => $params,
                'response_payload' => $payload,
                'hit_count' => 0,
                'expires_at' => Carbon::now()->addHours($ttlHours),
            ]
        );

        return [
            'data' => $payload,
            'is_cached' => false,
            'cached_at' => now(),
        ];
    }

    /**
     * Cache 24 Jam: Snapshot Fundamental & Valuasi Emiten
     */
    public function getCompanyOverview(string $symbol): array
    {
        $symbol = strtoupper(trim($symbol));
        $sections = ['overview', 'valuation', 'financials', 'dividend'];
        $endpoint = "/company/report/{$symbol}/";

        return $this->remember($endpoint, ['symbol' => $symbol, 'sections' => $sections], 24, function () use ($symbol, $sections) {
            return $this->apiClient->getCompanyReport($symbol, $sections);
        });
    }

    /**
     * Cache 72 Jam: Historical Quarterly Financials
     */
    public function getQuarterlyFinancials(string $symbol, int $nQuarters = 4): array
    {
        $symbol = strtoupper(trim($symbol));
        $endpoint = "/financials/quarterly/{$symbol}/";

        return $this->remember($endpoint, ['symbol' => $symbol, 'n_quarters' => $nQuarters], 72, function () use ($symbol, $nQuarters) {
            return $this->apiClient->getQuarterlyFinancials($symbol, $nQuarters);
        });
    }

    /**
     * Cache 24 Jam: Subsector Benchmark & Peer Medians
     */
    public function getSubsectorPeers(string $subSector): array
    {
        $subSector = strtolower(trim(str_replace(' ', '-', $subSector)));
        $sections = ['statistics', 'valuation', 'companies'];
        $endpoint = "/subsector/report/{$subSector}/";

        return $this->remember($endpoint, ['sub_sector' => $subSector, 'sections' => $sections], 24, function () use ($subSector, $sections) {
            return $this->apiClient->getSubsectorReport($subSector, $sections);
        });
    }

    /**
     * Cache 6 Jam: Stock Screener Terstruktur
     */
    public function screenStocks(array $filters, string $orderBy = '-market_cap', int $limit = 20): array
    {
        $conditions = [];

        if (!empty($filters['sector'])) {
            $conditions[] = "sector = '" . addslashes($filters['sector']) . "'";
        }
        if (!empty($filters['sub_sector'])) {
            $conditions[] = "sub_sector = '" . addslashes($filters['sub_sector']) . "'";
        }
        if (isset($filters['min_roe'])) {
            $conditions[] = "roe_ttm >= {$filters['min_roe']}";
        }
        if (isset($filters['max_per'])) {
            $conditions[] = "pe_ttm <= {$filters['max_per']} and pe_ttm > 0";
        }
        if (isset($filters['min_dividend_yield'])) {
            $conditions[] = "yield_ttm >= {$filters['min_dividend_yield']}";
        }

        $params = [
            'where' => implode(' and ', $conditions),
            'order_by' => $orderBy,
            'limit' => min($limit, 100),
            'include_query_values' => true,
        ];

        return $this->remember('/companies/', $params, 6, function () use ($params) {
            return $this->apiClient->screenCompanies($params);
        });
    }
}