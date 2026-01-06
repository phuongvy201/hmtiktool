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

// ============================================
// TikTok Orders Sync Scheduled Tasks
// ============================================

// Sync TikTok Orders - đơn hàng trong 24h gần đây - mỗi 30 phút
Schedule::command('tiktok:sync-orders --hours=24')
    ->everyThirtyMinutes()
    ->name('sync-tiktok-orders-24h')
    ->description('Sync TikTok orders trong 24h gần đây - mỗi 30 phút')
    ->withoutOverlapping()
    ->runInBackground()
    ->appendOutputTo(storage_path('logs/tiktok-orders-sync.log'));

// Sync TikTok Orders - đơn hàng mới (1 giờ gần đây) - mỗi 10 phút
Schedule::command('tiktok:sync-orders --hours=1')
    ->everyTenMinutes()
    ->name('sync-tiktok-orders-recent')
    ->description('Sync TikTok orders mới (1h gần đây) - mỗi 10 phút')
    ->withoutOverlapping()
    ->runInBackground()
    ->appendOutputTo(storage_path('logs/tiktok-orders-sync-recent.log'));

// Sync TikTok Orders - đơn hàng đang chờ ship - mỗi 15 phút
Schedule::command('tiktok:sync-orders --status=AWAITING_SHIPMENT --hours=48')
    ->everyFifteenMinutes()
    ->name('sync-tiktok-orders-awaiting-shipment')
    ->description('Sync TikTok orders đang chờ ship - mỗi 15 phút')
    ->withoutOverlapping()
    ->runInBackground()
    ->appendOutputTo(storage_path('logs/tiktok-orders-awaiting-shipment.log'));

// Sync TikTok Orders - đơn hàng đang vận chuyển - mỗi 20 phút
Schedule::command('tiktok:sync-orders --status=IN_TRANSIT --hours=72')
    ->cron('*/20 * * * *')
    ->name('sync-tiktok-orders-in-transit')
    ->description('Sync TikTok orders đang vận chuyển - mỗi 20 phút')
    ->withoutOverlapping()
    ->runInBackground()
    ->appendOutputTo(storage_path('logs/tiktok-orders-in-transit.log'));

// Full sync tất cả đơn hàng - hàng ngày lúc 3:00 AM
Schedule::command('tiktok:sync-orders --hours=168') // 7 ngày
    ->dailyAt('03:00')
    ->name('sync-tiktok-orders-full')
    ->description('Full sync tất cả TikTok orders (7 ngày) - hàng ngày lúc 3:00 AM')
    ->withoutOverlapping()
    ->runInBackground()
    ->appendOutputTo(storage_path('logs/tiktok-orders-full-sync.log'));

// Monitor sync status - mỗi 2 giờ
Schedule::command('tiktok:monitor-sync --hours=24 --alert-threshold=2 --send-alerts')
    ->everyTwoHours()
    ->name('monitor-tiktok-sync-status')
    ->description('Monitor trạng thái sync TikTok orders - mỗi 2 giờ')
    ->withoutOverlapping()
    ->runInBackground()
    ->appendOutputTo(storage_path('logs/tiktok-sync-monitoring.log'));

// Dispatch sync jobs cho high priority - mỗi 5 phút
Schedule::command('tiktok:dispatch-sync-jobs --hours=1 --priority --batch-size=3')
    ->everyFiveMinutes()
    ->name('dispatch-tiktok-sync-jobs')
    ->description('Dispatch sync jobs cho high priority orders - mỗi 5 phút')
    ->withoutOverlapping()
    ->runInBackground()
    ->appendOutputTo(storage_path('logs/tiktok-dispatch-jobs.log'));
