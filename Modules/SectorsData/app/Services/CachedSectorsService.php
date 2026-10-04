<?php

namespace Modules\SectorsData\Services;

use App\Models\ApiCache;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Throwable;

class CachedSectorsService
{
    protected const PROVIDER = 'sectors_app_v2';

    protected SectorsApiClient $apiClient;

    public function __construct(SectorsApiClient $apiClient)
    {
        $this->apiClient = $apiClient;
    }

    protected function remember(string $endpoint, array $params, int $ttlHours, callable $apiCallback)
    {
        $normalizedParams = $this->normalizeParams($params);
        $cacheKey = hash('sha256', self::PROVIDER . ':' . $endpoint . ':' . json_encode($normalizedParams));

        Log::info("[CachedSectorsService] Processing cache for endpoint: {$endpoint}", [
            'cache_key' => $cacheKey,
            'params' => $normalizedParams
        ]);

        $cached = ApiCache::where('cache_key', $cacheKey)->first();

        // 1. Jika Cache ADA dan BELUM EXPIRED -> Return Cached Data
        if ($cached && ($cached->expires_at === null || !$cached->expires_at->isPast())) {
            $cached->increment('hit_count');

            Log::info("[CachedSectorsService] Cache HIT for endpoint: {$endpoint}", [
                'cache_key' => $cacheKey,
                'hit_count' => $cached->hit_count
            ]);

            return [
                'data' => $cached->response_payload,
                'is_cached' => true,
                'is_stale' => false,
                'cached_at' => $cached->updated_at,
            ];
        }

        // 2. Jika Cache TIDAK ADA / STALE -> Fetch API Baru & Simpan ke DB
        Log::info("[CachedSectorsService] Cache MISS/EXPIRED. Fetching from API: {$endpoint}");

        try {
            $apiResponse = $apiCallback();

            if (!empty($apiResponse)) {
                $expiresAt = Carbon::now()->addHours($ttlHours);

                $cacheRecord = ApiCache::updateOrCreate(
                    ['cache_key' => $cacheKey],
                    [
                        'provider' => self::PROVIDER,
                        'endpoint' => $endpoint,
                        'request_params' => $normalizedParams,
                        'response_payload' => $apiResponse,
                        'expires_at' => $expiresAt,
                    ]
                );

                Log::info("[CachedSectorsService] Successfully saved to database api_caches!", [
                    'cache_key' => $cacheKey,
                    'endpoint' => $endpoint,
                    'expires_at' => $expiresAt->toDateTimeString()
                ]);

                return [
                    'data' => $cacheRecord->response_payload,
                    'is_cached' => false,
                    'is_stale' => false,
                    'cached_at' => $cacheRecord->updated_at,
                ];
            } else {
                Log::warning("[CachedSectorsService] API returned empty payload for endpoint: {$endpoint}");
            }
        } catch (Throwable $e) {
            Log::error("[CachedSectorsService] Exception during API call or saving cache: {$e->getMessage()}", [
                'endpoint' => $endpoint,
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }

        return [
            'data' => [],
            'is_cached' => false,
            'cached_at' => null,
        ];
    }

    private function normalizeParams(array $params): array
    {
        ksort($params);

        foreach ($params as $key => $value) {
            if (is_array($value)) {
                $params[$key] = $this->normalizeParams($value);
            }
        }

        return $params;
    }

    public function getCompanyOverview(string $symbol)
    {
        $symbol = strtoupper(trim($symbol));
        $sections = ['overview', 'valuation', 'financials', 'dividend'];
        $endpoint = "/company/report/{$symbol}/";

        return $this->remember($endpoint, ['symbol' => $symbol, 'sections' => $sections], 24, function () use ($symbol, $sections) {
            return $this->apiClient->getCompanyReport($symbol, $sections);
        });
    }

    public function getQuarterlyFinancials(string $symbol, int $nQuarters = 4)
    {
        $symbol = strtoupper(trim($symbol));
        $endpoint = "/financials/quarterly/{$symbol}/";

        return $this->remember($endpoint, ['symbol' => $symbol, 'n_quarters' => $nQuarters], 72, function () use ($symbol, $nQuarters) {
            return $this->apiClient->getQuarterlyFinancials($symbol, $nQuarters);
        });
    }

    public function getSubsectorPeers(string $subSector)
    {
        $subSector = strtolower(trim(str_replace(' ', '-', $subSector)));
        $sections = ['statistics', 'valuation', 'companies'];
        $endpoint = "/subsector/report/{$subSector}/";

        return $this->remember($endpoint, ['sub_sector' => $subSector, 'sections' => $sections], 24, function () use ($subSector, $sections) {
            return $this->apiClient->getSubsectorReport($subSector, $sections);
        });
    }

    public function screenStocks(array $filters, string $orderBy = '-market_cap', int $limit = 20)
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

    public function getTopCompanyMovers(array $params = [])
    {
        $endpoint = '/companies/top-changes/';

        return $this->remember($endpoint, $params, 4, function () use ($params) {
            return $this->apiClient->getTopCompanyMovers($params);
        });
    }

    public function getMostTradedStocks(array $params = [])
    {
        $endpoint = '/most-traded/';

        return $this->remember($endpoint, $params, 6, function () use ($params) {
            return $this->apiClient->getMostTradedStocks($params);
        });
    }

    public function getIdxMarketSummary(array $params = [])
    {
        $endpoint = '/idx-total/';

        return $this->remember($endpoint, $params, 12, function () use ($params) {
            return $this->apiClient->getIdxMarketSummary($params);
        });
    }
}