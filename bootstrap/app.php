<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => \App\Http\Middleware\CheckRole::class,
            'permission' => \App\Http\Middleware\CheckPermission::class,
            'prevent.self.deletion' => \App\Http\Middleware\PreventSelfDeletion::class,
            'team.admin' => \App\Http\Middleware\TeamAdminMiddleware::class,
            'product.template.access' => \App\Http\Middleware\CheckProductTemplateAccess::class,
        ]);
    })
    ->withSchedule(function (\Illuminate\Console\Scheduling\Schedule $schedule): void {
        // Refresh TikTok Shop tokens hàng ngày lúc 2:00 AM
        $schedule->command('tiktok:refresh-tokens')
            ->dailyAt('02:00')
            ->withoutOverlapping()
            ->runInBackground()
            ->appendOutputTo(storage_path('logs/tiktok-token-refresh.log'));

        // Sync TikTok Orders - mỗi 30 phút
        $schedule->command('tiktok:sync-orders --hours=24')
            ->everyThirtyMinutes()
            ->withoutOverlapping()
            ->runInBackground()
            ->appendOutputTo(storage_path('logs/tiktok-orders-sync.log'))
            ->emailOutputOnFailure(config('mail.admin_email', 'admin@example.com'));

        // Sync TikTok Orders - đơn hàng mới (1 giờ gần đây) - mỗi 10 phút
        $schedule->command('tiktok:sync-orders --hours=1')
            ->everyTenMinutes()
            ->withoutOverlapping()
            ->runInBackground()
            ->appendOutputTo(storage_path('logs/tiktok-orders-sync-recent.log'));

        // Sync TikTok Orders - đơn hàng đang chờ ship - mỗi 15 phút
        $schedule->command('tiktok:sync-orders --status=AWAITING_SHIPMENT --hours=48')
            ->everyFifteenMinutes()
            ->withoutOverlapping()
            ->runInBackground()
            ->appendOutputTo(storage_path('logs/tiktok-orders-awaiting-shipment.log'));

        // Sync TikTok Orders - đơn hàng đang vận chuyển - mỗi 20 phút
        $schedule->command('tiktok:sync-orders --status=IN_TRANSIT --hours=72')
            ->cron('*/20 * * * *')
            ->withoutOverlapping()
            ->runInBackground()
            ->appendOutputTo(storage_path('logs/tiktok-orders-in-transit.log'));

        // Full sync tất cả đơn hàng - hàng ngày lúc 3:00 AM
        $schedule->command('tiktok:sync-orders --hours=168') // 7 ngày
            ->dailyAt('03:00')
            ->withoutOverlapping()
            ->runInBackground()
            ->appendOutputTo(storage_path('logs/tiktok-orders-full-sync.log'));

        // Monitor sync status - mỗi 2 giờ
        $schedule->command('tiktok:monitor-sync --hours=24 --alert-threshold=2 --send-alerts')
            ->everyTwoHours()
            ->withoutOverlapping()
            ->runInBackground()
            ->appendOutputTo(storage_path('logs/tiktok-sync-monitoring.log'));

        // Dispatch sync jobs cho high priority - mỗi 5 phút
        $schedule->command('tiktok:dispatch-sync-jobs --hours=1 --priority --batch-size=3')
            ->everyFiveMinutes()
            ->withoutOverlapping()
            ->runInBackground()
            ->appendOutputTo(storage_path('logs/tiktok-dispatch-jobs.log'));
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
