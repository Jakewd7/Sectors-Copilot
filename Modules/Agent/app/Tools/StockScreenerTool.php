<?php

namespace Modules\Agent\Tools;

use Illuminate\Support\Facades\Http;
use Modules\Agent\Tools\Contracts\AgentToolInterface;

class StockScreenerTool implements AgentToolInterface
{
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
        $endpoint = config('agent.internal_api_base_url') . "/screener";
        $response = Http::timeout(12)->get($endpoint, array_filter($parameters));

        return [
            'endpoint' => '/screener',
            'data' => $response->successful() ? $response->json('data') : ['error' => 'Tidak ada saham memenuhi kriteria screening'],
        ];
    }
}