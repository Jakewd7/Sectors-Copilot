<?php

namespace Modules\Agent\Tools;

use Illuminate\Support\Facades\Http;
use Modules\Agent\Tools\Contracts\AgentToolInterface;

class CompanyOverviewTool implements AgentToolInterface
{
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
        $endpoint = config('agent.internal_api_base_url') . "/companies/{$symbol}/overview";

        $response = Http::timeout(10)->get($endpoint);

        return [
            'endpoint' => "/companies/{$symbol}/overview",
            'data' => $response->successful() ? $response->json('data') : ['error' => 'Gagal mengambil data emiten ' . $symbol],
        ];
    }
}