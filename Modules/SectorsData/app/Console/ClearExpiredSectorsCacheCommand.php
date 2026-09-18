<?php

namespace Modules\SectorsData\Console;

use App\Models\ApiCache;
use Illuminate\Console\Command;

class ClearExpiredSectorsCacheCommand extends Command
{
    protected $signature = 'sectors:clear-expired-cache';
    protected $description = 'Menghapus data cache Sectors API yang sudah melewati masa berlaku (expired)';

    public function handle()
    {
        $this->info('Memeriksa cache Sectors API yang kedaluwarsa...');

        $deleted = ApiCache::where('provider', 'sectors_app_v2')
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', now()->subDays(7))
            ->delete();

        $this->info("Berhasil membersihkan {$deleted} record cache usang (> 7 hari).");

        return Command::SUCCESS;
    }
}
