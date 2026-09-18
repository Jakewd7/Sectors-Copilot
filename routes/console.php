<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Modules\SectorsData\Console\ClearExpiredSectorsCacheCommand;
use Modules\SectorsData\Console\WarmSectorsCacheCommand;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('sectors:clear-expired-cache', function () {
    $this->call(ClearExpiredSectorsCacheCommand::class);
})->purpose('Menghapus cache Sectors API yang kedaluwarsa');

Artisan::command('sectors:warm-cache', function () {
    $this->call(WarmSectorsCacheCommand::class);
})->purpose('Pre-fetch master data Sectors API');