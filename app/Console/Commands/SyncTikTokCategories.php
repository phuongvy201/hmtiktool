<?php

namespace App\Console\Commands;

use App\Models\Team;
use App\Models\TikTokShopCategory;
use App\Models\TikTokShopIntegration;
use App\Services\TikTokShopService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SyncTikTokCategories extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tiktok:sync-categories 
                            {--force : Force sync even if not needed}
                            {--hours=24 : Hours threshold for sync check}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync TikTok Shop categories from API to database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $force = $this->option('force');
        $hours = (int) $this->option('hours');

        $this->info('Starting TikTok Shop categories sync for system...');

        // Lấy integration đầu tiên để làm mẫu (categories chung cho toàn hệ thống)
        $integration = TikTokShopIntegration::first();

        if (!$integration) {
            $this->warn('No TikTok Shop integration found. Please set up integration first.');
            return 0;
        }

        try {
            $result = $this->syncSystemCategories($integration, $force, $hours);

            if ($result) {
                $this->info("✅ Successfully synced categories for system");
                return 0;
            } else {
                $this->warn("⚠️  Skipped sync (not needed). Use --force to override.");
                return 0;
            }
        } catch (\Exception $e) {
            $this->error("❌ Error syncing categories: " . $e->getMessage());
            Log::error('TikTok categories sync error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return 1;
        }
    }

    /**
     * Sync categories cho toàn hệ thống
     */
    private function syncSystemCategories(TikTokShopIntegration $integration, bool $force, int $hours): bool
    {
        // Kiểm tra xem có cần sync không
        if (!$force && !TikTokShopCategory::needsSystemSync($hours)) {
            $this->warn("Categories were synced recently. Use --force to override.");
            return false;
        }

        // Gọi API để lấy categories
        $service = new TikTokShopService();
        $result = $service->getCategories($integration);

        if (!$result['success']) {
            throw new \Exception("Failed to get categories from TikTok Shop API: " . ($result['error'] ?? 'Unknown error'));
        }

        $categories = $result['data'];
        $rawCategories = $result['raw_data'] ?? [];

        $this->info("Retrieved " . count($categories) . " categories from TikTok Shop API");

        // Log chi tiết categories nhận được
        Log::info('SyncTikTokCategories: Categories retrieved from API', [
            'formatted_count' => count($categories),
            'raw_count' => count($rawCategories),
            'formatted_sample' => array_slice($categories, 0, 5, true),
            'raw_sample' => array_slice($rawCategories, 0, 3)
        ]);

        // Xóa tất cả categories cũ (categories chung cho toàn hệ thống)
        $deletedCount = TikTokShopCategory::count();
        TikTokShopCategory::truncate();
        $this->info("Cleared {$deletedCount} old categories");

        Log::info('SyncTikTokCategories: Cleared old categories', [
            'deleted_count' => $deletedCount
        ]);

        // Lưu categories mới
        $savedCount = 0;
        $now = now();

        // Kiểm tra xem có raw_data từ API không (cấu trúc chi tiết hơn)
        if (!empty($rawCategories)) {
            // Sử dụng raw data nếu có để có thông tin chi tiết hơn
            Log::info('SyncTikTokCategories: Using raw data from API', [
                'raw_categories_count' => count($rawCategories),
                'processing_method' => 'raw_data'
            ]);

            foreach ($rawCategories as $index => $category) {
                $categoryId = $category['id'] ?? '';
                // TikTok Shop API sử dụng 'local_name' thay vì 'name'
                $categoryName = $category['local_name'] ?? $category['name'] ?? '';
                $parentId = $category['parent_id'] ?? null;
                $level = $category['level'] ?? 1;
                $isLeaf = $category['is_leaf'] ?? true;

                if (empty($categoryId) || empty($categoryName)) {
                    Log::warning('SyncTikTokCategories: Skipping invalid category', [
                        'index' => $index,
                        'category_data' => $category
                    ]);
                    continue;
                }

                // Log mỗi 100 categories để tránh spam log
                if ($savedCount % 100 === 0) {
                    Log::info('SyncTikTokCategories: Processing progress', [
                        'processed_count' => $savedCount,
                        'current_category' => [
                            'id' => $categoryId,
                            'name' => $categoryName,
                            'parent_id' => $parentId,
                            'level' => $level,
                            'is_leaf' => $isLeaf
                        ]
                    ]);
                }

                TikTokShopCategory::create([
                    'category_id' => $categoryId,
                    'category_name' => $categoryName,
                    'parent_category_id' => $parentId,
                    'level' => $level,
                    'is_leaf' => $isLeaf,
                    'category_data' => [
                        'original_data' => $category,
                        'parsed_at' => now()->toISOString()
                    ],
                    'last_synced_at' => $now,
                ]);

                $savedCount++;
            }
        } else {
            // Fallback: sử dụng format đơn giản
            Log::info('SyncTikTokCategories: Using formatted data (fallback)', [
                'formatted_categories_count' => count($categories),
                'processing_method' => 'formatted_data'
            ]);

            foreach ($categories as $categoryId => $categoryName) {
                // Parse category structure từ TikTok API response
                $categoryData = $this->parseCategoryData($categoryId, $categoryName);

                // Log mỗi 100 categories để tránh spam log
                if ($savedCount % 100 === 0) {
                    Log::info('SyncTikTokCategories: Processing progress (formatted)', [
                        'processed_count' => $savedCount,
                        'current_category' => [
                            'id' => $categoryId,
                            'name' => $categoryName,
                            'parsed_data' => $categoryData
                        ]
                    ]);
                }

                TikTokShopCategory::create([
                    'category_id' => $categoryData['category_id'],
                    'category_name' => $categoryData['category_name'],
                    'parent_category_id' => $categoryData['parent_category_id'],
                    'level' => $categoryData['level'],
                    'is_leaf' => $categoryData['is_leaf'],
                    'category_data' => $categoryData['metadata'],
                    'last_synced_at' => $now,
                ]);

                $savedCount++;
            }
        }

        $this->info("Saved {$savedCount} categories to database");

        // Log thành công với thống kê chi tiết
        $leafCount = TikTokShopCategory::where('is_leaf', true)->count();
        $rootCount = TikTokShopCategory::where('level', 1)->count();
        $maxLevel = TikTokShopCategory::max('level');

        Log::info('TikTok categories synced successfully for system', [
            'total_categories' => $savedCount,
            'leaf_categories' => $leafCount,
            'root_categories' => $rootCount,
            'max_level' => $maxLevel,
            'synced_at' => $now->toISOString(),
            'processing_time' => now()->diffInSeconds($now) . ' seconds'
        ]);

        // Log sample categories để kiểm tra
        $sampleCategories = TikTokShopCategory::take(10)->get(['category_id', 'category_name', 'level', 'is_leaf']);
        Log::info('Sample categories saved to database', [
            'sample_categories' => $sampleCategories->toArray()
        ]);

        return true;
    }

    /**
     * Parse category data từ TikTok API response
     * TikTok API có thể trả về categories với cấu trúc phức tạp
     */
    private function parseCategoryData(string $categoryId, string $categoryName): array
    {
        // Mặc định là leaf category (có thể tạo sản phẩm)
        $isLeaf = true;
        $level = 1;
        $parentCategoryId = null;

        // Nếu category ID có format phân cấp (ví dụ: "100001.100002")
        if (str_contains($categoryId, '.')) {
            $parts = explode('.', $categoryId);
            $level = count($parts);
            $parentCategoryId = implode('.', array_slice($parts, 0, -1));
        }

        // Một số category có thể không phải leaf (ví dụ: category cha)
        // Có thể cần logic phức tạp hơn dựa trên response thực tế từ TikTok API
        if (
            str_contains(strtolower($categoryName), 'all') ||
            str_contains(strtolower($categoryName), 'tất cả') ||
            str_contains(strtolower($categoryName), 'other') ||
            str_contains(strtolower($categoryName), 'khác')
        ) {
            $isLeaf = false;
        }

        $parsedData = [
            'category_id' => $categoryId,
            'category_name' => $categoryName,
            'parent_category_id' => $parentCategoryId,
            'level' => $level,
            'is_leaf' => $isLeaf,
            'metadata' => [
                'original_id' => $categoryId,
                'original_name' => $categoryName,
                'parsed_at' => now()->toISOString()
            ]
        ];

        // Log parsing details cho categories đặc biệt
        if ($level > 1 || !$isLeaf || $parentCategoryId) {
            Log::info('SyncTikTokCategories: Parsed category with special structure', [
                'original_id' => $categoryId,
                'original_name' => $categoryName,
                'parsed_data' => $parsedData,
                'parsing_logic' => [
                    'has_hierarchy' => str_contains($categoryId, '.'),
                    'is_non_leaf' => !$isLeaf,
                    'level_determined' => $level > 1
                ]
            ]);
        }

        return $parsedData;
    }
}
