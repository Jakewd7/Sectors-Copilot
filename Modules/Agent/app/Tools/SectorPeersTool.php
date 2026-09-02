<?php

namespace Modules\Agent\Tools;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Modules\Agent\Tools\Contracts\AgentToolInterface;

class SectorPeersTool implements AgentToolInterface
{
    public function getName(): string
    {
        return 'get_sector_peers';
    }

    public function getDescription(): string
    {
        return 'Mengambil benchmark industri subsektor, median PER, dan daftar emiten kompetitor sejenis.';
    }

    public function getSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'subSector' => [
                    'type' => 'string',
                    'description' => 'Slug subsektor format kebab-case (contoh: banks, food-beverage, telecommunication)',
                ],
            ],
            'required' => ['subSector'],
        ];
    }

    public function execute(array $parameters): array
    {
        $subSector = Str::slug($parameters['subSector'] ?? 'banks');
        $endpoint = config('agent.internal_api_base_url') . "/subsectors/{$subSector}/peers";

        $response = Http::timeout(10)->get($endpoint);

        return [
            'endpoint' => "/subsectors/{$subSector}/peers",
            'data' => $response->successful() ? $response->json('data') : ['error' => 'Gagal memuat benchmark ' . $subSector],
        ];
    }
}