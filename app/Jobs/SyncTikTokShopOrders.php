<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\TikTokShop;
use App\Services\TikTokOrderService;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class SyncTikTokShopOrders implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 300; // 5 minutes timeout
    public $tries = 3;
    public $maxExceptions = 2;

    /**
     * Create a new job instance.
     */
    public function __construct(
        private TikTokShop $shop,
        private array $filters = [],
        private bool $isPriority = false
    ) {
        // Set queue priority
        $this->onQueue($isPriority ? 'high' : 'default');
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $startTime = microtime(true);

        Log::info('Starting TikTok shop orders sync job', [
            'shop_id' => $this->shop->id,
            'shop_name' => $this->shop->shop_name,
            'filters' => $this->filters,
            'queue' => $this->queue,
            'job_id' => $this->job?->getJobId()
        ]);

        try {
            // Kiểm tra integration
            if (!$this->shop->integration) {
                throw new \Exception('Shop không có integration');
            }

            if (!$this->shop->integration->isActive()) {
                throw new \Exception('Shop integration không hoạt động');
            }

            // Kiểm tra rate limit (nếu có)
            $this->checkRateLimit();

            // Sync orders
            $orderService = new TikTokOrderService();
            $result = $orderService->syncAllOrders($this->shop, $this->filters);

            $executionTime = round((microtime(true) - $startTime) * 1000, 2);

            if ($result['success']) {
                Log::info('TikTok shop orders sync job completed successfully', [
                    'shop_id' => $this->shop->id,
                    'shop_name' => $this->shop->shop_name,
                    'total_orders' => $result['total_orders'] ?? 0,
                    'execution_time_ms' => $executionTime,
                    'filters' => $this->filters
                ]);
            } else {
                throw new \Exception($result['message'] ?? 'Sync failed');
            }
        } catch (\Exception $e) {
            $executionTime = round((microtime(true) - $startTime) * 1000, 2);

            Log::error('TikTok shop orders sync job failed', [
                'shop_id' => $this->shop->id,
                'shop_name' => $this->shop->shop_name,
                'error' => $e->getMessage(),
                'execution_time_ms' => $executionTime,
                'attempt' => $this->attempts(),
                'max_tries' => $this->tries,
                'filters' => $this->filters
            ]);

            // Re-throw để trigger retry mechanism
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('TikTok shop orders sync job failed permanently', [
            'shop_id' => $this->shop->id,
            'shop_name' => $this->shop->shop_name,
            'error' => $exception->getMessage(),
            'attempts' => $this->attempts(),
            'max_tries' => $this->tries,
            'filters' => $this->filters
        ]);

        // Có thể thêm logic gửi email thông báo lỗi ở đây
        // Mail::to(config('mail.admin_email'))->send(new SyncJobFailedMail($this->shop, $exception));
    }

    /**
     * Kiểm tra rate limit để tránh spam API
     */
    private function checkRateLimit(): void
    {
        // Kiểm tra xem shop này có đang được sync gần đây không
        $lastSync = \Cache::get("tiktok_sync_last_run_{$this->shop->id}");

        if ($lastSync) {
            $timeSinceLastSync = Carbon::now()->diffInSeconds(Carbon::parse($lastSync));

            // Nếu sync trong vòng 30 giây gần đây, nghỉ 30 giây
            if ($timeSinceLastSync < 30) {
                Log::info('Rate limiting shop sync', [
                    'shop_id' => $this->shop->id,
                    'seconds_since_last_sync' => $timeSinceLastSync
                ]);
                sleep(30 - $timeSinceLastSync);
            }
        }

        // Ghi nhận lần sync này
        \Cache::put("tiktok_sync_last_run_{$this->shop->id}", Carbon::now(), 300); // 5 phút
    }

    /**
     * Get the tags that should be assigned to the job.
     */
    public function tags(): array
    {
        return [
            'tiktok:sync-orders',
            "shop:{$this->shop->id}",
            "shop-name:{$this->shop->shop_name}"
        ];
    }
}
