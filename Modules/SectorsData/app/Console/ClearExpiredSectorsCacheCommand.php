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
        $this->info('Membersihkan cache Sectors API yang kedaluwarsa...');

        $deleted = ApiCache::where('provider', 'sectors_app')
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', now())
            ->delete();

        $this->info("Berhasil membersihkan {$deleted} record cache kedaluwarsa.");

        return Command::SUCCESS;
    }
}
