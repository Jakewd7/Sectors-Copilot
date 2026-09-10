<?php

namespace Modules\Agent\Tools;

use Modules\Agent\Tools\Contracts\AgentToolInterface;
use Modules\SectorsData\Services\CachedSectorsService;
use Throwable;

class CompanyFinancialsTool implements AgentToolInterface
{
    public function __construct(
        protected CachedSectorsService $sectorsService
    ) {
    }

    public function getName(): string
    {
        return 'get_company_financials';
    }

    public function getDescription(): string
    {
        return 'Mengambil laporan keuangan historis kuartalan emiten (laba rugi, neraca, arus kas).';
    }

    public function getSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'symbol' => [
                    'type' => 'string',
                    'description' => 'Ticker saham IDX 4 huruf (contoh: BMRI)',
                ],
                'period' => [
                    'type' => 'string',
                    'description' => 'Pilihan horizon laporan: "annual" atau "quarterly"',
                    'enum' => ['annual', 'quarterly'],
                ],
            ],
            'required' => ['symbol'],
        ];
    }

    public function execute(array $parameters): array
    {
        $symbol = strtoupper($parameters['symbol'] ?? '');
        $period = $parameters['period'] ?? 'quarterly';
        $nQuarters = ($period === 'annual') ? 12 : 4;

        try {
            $result = $this->sectorsService->getQuarterlyFinancials($symbol, $nQuarters);

            return [
                'endpoint' => "/companies/{$symbol}/financials?n_quarters={$nQuarters}",
                'data' => $result['data'] ?? [],
            ];
        } catch (Throwable $e) {
            return [
                'endpoint' => "/companies/{$symbol}/financials?n_quarters={$nQuarters}",
                'data' => ['error' => 'Gagal memuat keuangan ' . $symbol . ': ' . $e->getMessage()],
            ];
        }
    }
}