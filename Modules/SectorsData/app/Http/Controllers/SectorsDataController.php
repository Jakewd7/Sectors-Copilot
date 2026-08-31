<?php

namespace Modules\SectorsData\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\SectorsData\Services\CachedSectorsService;
use Throwable;

class SectorsDataController extends Controller
{
    protected CachedSectorsService $sectorsService;

    public function __construct(CachedSectorsService $sectorsService)
    {
        $this->sectorsService = $sectorsService;
    }

    /**
     * GET /api/v1/sectors/company/{symbol}/overview
     */
    public function companyOverview(string $symbol): JsonResponse
    {
        try {
            $result = $this->sectorsService->getCompanyOverview($symbol);

            return response()->json([
                'success' => true,
                'meta' => ['is_cached' => $result['is_cached']],
                'data' => $result['data'],
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil overview emiten.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * GET /api/v1/sectors/company/{symbol}/financials
     */
    public function companyFinancials(Request $request, string $symbol): JsonResponse
    {
        $validated = $request->validate([
            'n_quarters' => 'nullable|integer|min:1|max:12',
        ]);

        try {
            $nQuarters = (int) ($validated['n_quarters'] ?? 4);
            $result = $this->sectorsService->getQuarterlyFinancials($symbol, $nQuarters);

            return response()->json([
                'success' => true,
                'meta' => ['is_cached' => $result['is_cached']],
                'data' => $result['data'],
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil laporan kuartalan emiten.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * GET /api/v1/sectors/subsector/{subSector}/peers
     */
    public function subsectorPeers(string $subSector): JsonResponse
    {
        try {
            $result = $this->sectorsService->getSubsectorPeers($subSector);

            return response()->json([
                'success' => true,
                'meta' => ['is_cached' => $result['is_cached']],
                'data' => $result['data'],
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil benchmark subsektor.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * GET /api/v1/sectors/screener
     */
    public function screener(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'sector' => 'nullable|string|max:100',
            'sub_sector' => 'nullable|string|max:100',
            'min_roe' => 'nullable|numeric',
            'max_per' => 'nullable|numeric',
            'min_dividend_yield' => 'nullable|numeric',
            'order_by' => 'nullable|string|max:50',
            'limit' => 'nullable|integer|min:1|max:50',
        ]);

        try {
            $orderBy = $validated['order_by'] ?? '-market_cap';
            $limit = (int) ($validated['limit'] ?? 20);

            $result = $this->sectorsService->screenStocks($validated, $orderBy, $limit);

            return response()->json([
                'success' => true,
                'meta' => ['is_cached' => $result['is_cached']],
                'data' => $result['data'],
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal melakukan screening saham.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}