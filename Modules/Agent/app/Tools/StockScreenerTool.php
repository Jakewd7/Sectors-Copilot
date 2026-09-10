<?php

namespace Modules\Agent\Tools;

use Modules\Agent\Tools\Contracts\AgentToolInterface;
use Modules\SectorsData\Services\CachedSectorsService;
use Throwable;

class StockScreenerTool implements AgentToolInterface
{
    public function __construct(
        protected CachedSectorsService $sectorsService
    ) {
    }

    public function getName(): string
    {
        return 'screen_stocks';
    }

    public function getDescription(): string
    {
        return 'Menyaring saham berdasarkan kriteria fundamental (ROE, PER, Dividend Yield, Subsektor).';
    }

    public function getSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'sub_sector' => ['type' => 'string', 'description' => 'Nama subsektor (contoh: Banks)'],
                'min_roe' => ['type' => 'number', 'description' => 'Batas minimal ROE desimal (misal 0.15 untuk 15%)'],
                'max_per' => ['type' => 'number', 'description' => 'Batas maksimal PER'],
                'min_dividend_yield' => ['type' => 'number', 'description' => 'Batas minimal yield dividen (misal 0.04 untuk 4%)'],
                'limit' => ['type' => 'integer', 'description' => 'Jumlah hasil maksimal (default: 5)'],
            ],
        ];
    }

    public function execute(array $parameters): array
    {
        $filters = array_filter($parameters);
        $orderBy = $filters['order_by'] ?? '-market_cap';
        $limit = (int) ($filters['limit'] ?? 5);

        try {
            $result = $this->sectorsService->screenStocks($filters, $orderBy, $limit);

            return [
                'endpoint' => '/screener',
                'data' => $result['data'] ?? [],
            ];
        } catch (Throwable $e) {
            return [
                'endpoint' => '/screener',
                'data' => ['error' => 'Gagal menyaring saham: ' . $e->getMessage()],
            ];
        }
    }
}