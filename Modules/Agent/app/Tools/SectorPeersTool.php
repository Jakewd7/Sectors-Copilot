<?php

namespace Modules\Agent\Tools;

use Illuminate\Support\Str;
use Modules\Agent\Tools\Contracts\AgentToolInterface;
use Modules\SectorsData\Services\CachedSectorsService;
use Throwable;

class SectorPeersTool implements AgentToolInterface
{
    public function __construct(
        protected CachedSectorsService $sectorsService
    ) {
    }

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

        try {
            $result = $this->sectorsService->getSubsectorPeers($subSector);

            return [
                'endpoint' => "/subsectors/{$subSector}/peers",
                'data' => $result['data'] ?? [],
            ];
        } catch (Throwable $e) {
            return [
                'endpoint' => "/subsectors/{$subSector}/peers",
                'data' => ['error' => 'Gagal memuat benchmark ' . $subSector . ': ' . $e->getMessage()],
            ];
        }
    }
}