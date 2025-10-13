<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Scheduled tasks
Schedule::command('tiktok:refresh-tokens')
    ->daily()
    ->at('02:00')
    ->name('refresh-tiktok-tokens')
    ->description('Refresh TikTok Shop access tokens daily at 2 AM')
    ->withoutOverlapping()
    ->runInBackground();

// Backup scheduled task (if needed)
Schedule::command('backup:database')
    ->daily()
    ->at('01:00')
    ->name('backup-database')
    ->description('Backup database daily at 1 AM')
    ->withoutOverlapping();

// TikTok Finance sync scheduled task
Schedule::command('tiktok:sync-finance --days=7')
    ->daily()
    ->at('03:00')
    ->name('sync-tiktok-finance')
    ->description('Sync TikTok finance/payment data daily at 3 AM')
    ->withoutOverlapping()
    ->runInBackground();
