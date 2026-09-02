<?php

namespace Modules\Agent\Tools;

use Illuminate\Support\Facades\Http;
use Modules\Agent\Tools\Contracts\AgentToolInterface;

class CompanyFinancialsTool implements AgentToolInterface
{
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

        // Mapping otomatis: annual mengambil 12 kuartal (3 tahun), quarterly 4 kuartal
        $nQuarters = ($period === 'annual') ? 12 : 4;
        $endpoint = config('agent.internal_api_base_url') . "/companies/{$symbol}/financials";

        $response = Http::timeout(10)->get($endpoint, ['n_quarters' => $nQuarters]);

        return [
            'endpoint' => "/companies/{$symbol}/financials?n_quarters={$nQuarters}",
            'data' => $response->successful() ? $response->json('data') : ['error' => 'Gagal memuat keuangan ' . $symbol],
        ];
    }
}