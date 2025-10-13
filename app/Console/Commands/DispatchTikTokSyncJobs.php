<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\TikTokShop;
use App\Jobs\SyncTikTokShopOrders;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class DispatchTikTokSyncJobs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tiktok:dispatch-sync-jobs 
                            {--shop= : ID của shop cụ thể}
                            {--status= : Trạng thái đơn hàng cần sync}
                            {--hours=24 : Số giờ gần đây để sync}
                            {--priority : Đưa jobs vào queue ưu tiên cao}
                            {--batch-size=5 : Số shop xử lý đồng thời}
                            {--delay=0 : Delay giữa các jobs (giây)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Dispatch jobs để sync TikTok orders cho nhiều shops song song';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('=== DISPATCHING TIKTOK SYNC JOBS ===');

        $shopId = $this->option('shop');
        $status = $this->option('status');
        $hours = (int) $this->option('hours');
        $isPriority = $this->option('priority');
        $batchSize = (int) $this->option('batch-size');
        $delay = (int) $this->option('delay');

        try {
            // Lấy danh sách shops
            $shops = $this->getShopsToSync($shopId);

            if ($shops->isEmpty()) {
                $this->warn('Không tìm thấy shop nào để sync');
                return Command::SUCCESS;
            }

            $this->info("Tìm thấy {$shops->count()} shop(s) để dispatch jobs");

            // Tạo filters
            $filters = $this->buildFilters($status, $hours);

            // Dispatch jobs theo batch
            $dispatchedJobs = [];
            $shopChunks = $shops->chunk($batchSize);

            foreach ($shopChunks as $chunkIndex => $shopChunk) {
                $this->info("Dispatching batch " . ($chunkIndex + 1) . " với {$shopChunk->count()} shops");

                foreach ($shopChunk as $shop) {
                    $job = new SyncTikTokShopOrders($shop, $filters, $isPriority);

                    if ($delay > 0) {
                        $job->delay(now()->addSeconds($delay * count($dispatchedJobs)));
                    }

                    dispatch($job);
                    $dispatchedJobs[] = $shop->id;

                    $this->info("✓ Dispatched job cho shop: {$shop->shop_name} (ID: {$shop->id})");
                }

                // Nghỉ giữa các batch để tránh overload
                if ($chunkIndex < $shopChunks->count() - 1) {
                    $this->info("Nghỉ 2 giây trước batch tiếp theo...");
                    sleep(2);
                }
            }

            $this->info("\n=== KẾT QUẢ DISPATCH ===");
            $this->info("Tổng jobs dispatched: " . count($dispatchedJobs));
            $this->info("Queue: " . ($isPriority ? 'high' : 'default'));
            $this->info("Batch size: {$batchSize}");
            $this->info("Delay: {$delay}s");

            Log::info('TikTok Sync Jobs Dispatched', [
                'total_jobs' => count($dispatchedJobs),
                'shop_ids' => $dispatchedJobs,
                'filters' => $filters,
                'is_priority' => $isPriority,
                'batch_size' => $batchSize,
                'delay' => $delay
            ]);

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error("Lỗi trong quá trình dispatch: {$e->getMessage()}");
            Log::error('TikTok Sync Jobs Dispatch Failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return Command::FAILURE;
        } finally {
            $this->info('=== KẾT THÚC DISPATCH JOBS ===');
        }
    }

    /**
     * Lấy danh sách shops cần sync
     */
    private function getShopsToSync(?string $shopId)
    {
        $query = TikTokShop::with(['integration']);

        if ($shopId) {
            $query->where('id', $shopId);
        }

        $shops = $query->get();

        // Lọc bỏ các shop không có integration hoặc integration không hoạt động
        $shops = $shops->filter(function ($shop) {
            if (!$shop->integration) {
                $this->warn("Shop {$shop->shop_name} không có integration");
                return false;
            }

            if (!$shop->integration->isActive()) {
                $this->warn("Shop {$shop->shop_name} integration không hoạt động");
                return false;
            }

            return true;
        });

        return $shops;
    }

    /**
     * Tạo filters cho việc sync
     */
    private function buildFilters(?string $status, int $hours): array
    {
        $filters = [];

        // Filter theo trạng thái
        if ($status) {
            $filters['order_status'] = $status;
        }

        // Filter theo thời gian (mặc định 24h gần đây)
        $startTime = Carbon::now()->subHours($hours)->timestamp;
        $filters['create_time_ge'] = $startTime;

        $this->info("Filters: " . json_encode($filters));

        return $filters;
    }
}
