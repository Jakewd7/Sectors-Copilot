<?php

namespace Modules\SectorsData\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Modules\SectorsData\Services\CachedSectorsService;

class WarmSectorsCacheCommand extends Command
{
    protected $signature = 'sectors:warm-cache';
    protected $description = 'Melakukan pre-fetch data emiten utama ke dalam api_caches agar Agent tidak memakan token Sectors.';

    protected array $popularTickers = [
        'BBCA',
        'BBRI',
        'BMRI',
        'BBNI',
        'ASII',
        'TLKM',
        'ICBP',
        'INDF',
        'UNVR',
        'AMMN',
        'BREN',
        'TPIA',
        'ADRO',
        'PTBA',
        'PGAS',
        'KLBF'
    ];

    protected array $subSectors = [
        'banks',
        'telecommunication',
        'food-beverage',
        'coal-mining',
        'oil-gas',
        'pharmaceuticals',
        'automobiles'
    ];

    public function handle(CachedSectorsService $sectorsService): int
    {
        $this->info('Memulai warm cache Sectors...');
        Log::info('[WarmSectorsCacheCommand] Starting warm cache...');

        foreach ($this->popularTickers as $ticker) {
            $this->line("Fetching overview & financials: {$ticker}");
            try {
                $sectorsService->getCompanyOverview($ticker);
                $sectorsService->getQuarterlyFinancials($ticker, 4);
                $sectorsService->getQuarterlyFinancials($ticker, 12);
                usleep(250000);
            } catch (\Throwable $e) {
                Log::error("[WarmSectorsCacheCommand] Error fetching ticker {$ticker}: {$e->getMessage()}");
                $this->error("Error {$ticker}: {$e->getMessage()}");
            }
        }

        foreach ($this->subSectors as $sub) {
            $this->line("Fetching peers: {$sub}");
            try {
                $sectorsService->getSubsectorPeers($sub);
                usleep(250000);
            } catch (\Throwable $e) {
                Log::error("[WarmSectorsCacheCommand] Error fetching subsector {$sub}: {$e->getMessage()}");
                $this->error("Error {$sub}: {$e->getMessage()}");
            }
        }

        $this->info('Selesai! Seluruh data utama telah tersimpan di api_caches.');
        Log::info('[WarmSectorsCacheCommand] Finished warm cache successfully.');

        return Command::SUCCESS;
    }
}