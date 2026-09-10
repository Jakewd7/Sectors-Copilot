<?php

namespace Modules\Agent\Tools;

use Modules\Agent\Tools\Contracts\AgentToolInterface;
use Modules\SectorsData\Services\CachedSectorsService;

class CompanyOverviewTool implements AgentToolInterface
{
    public function __construct(
        protected CachedSectorsService $sectorsService
    ) {
    }

    public function getName(): string
    {
        return 'get_company_overview';
    }

    public function getDescription(): string
    {
        return 'Mengambil profil emiten, valuasi (PER, PBV, ROE), market cap, dan dividend yield berdasarkan symbol IDX.';
    }

    public function getSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'symbol' => [
                    'type' => 'string',
                    'description' => 'Ticker saham IDX 4 huruf (contoh: BBCA, BMRI, TLKM)',
                ],
            ],
            'required' => ['symbol'],
        ];
    }

    public function execute(array $parameters): array
    {
        $symbol = strtoupper($parameters['symbol'] ?? '');

        try {
            $result = $this->sectorsService->getCompanyOverview($symbol);

            return [
                'endpoint' => "/companies/{$symbol}/overview",
                'data' => $result['data'] ?? [],
            ];
        } catch (\Throwable $e) {
            return [
                'endpoint' => "/companies/{$symbol}/overview",
                'data' => ['error' => 'Gagal mengambil data emiten ' . $symbol . ': ' . $e->getMessage()],
            ];
        }
    }
}