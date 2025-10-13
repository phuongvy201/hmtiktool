<?php

namespace App\Console\Commands;

use App\Models\TikTokCategoryAttribute;
use App\Models\TikTokShopCategory;
use App\Models\TikTokShopIntegration;
use App\Services\TikTokShopService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SyncTikTokCategoryAttributes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tiktok:sync-category-attributes 
                            {category_id? : Specific category ID to sync}
                            {--force : Force sync even if not needed}
                            {--hours=24 : Hours threshold for sync check}
                            {--locale=en-US : Locale for attributes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync TikTok Shop category attributes from API to database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $categoryId = $this->argument('category_id');
        $force = $this->option('force');
        $hours = (int) $this->option('hours');
        $locale = $this->option('locale');

        $this->info('Starting TikTok Shop category attributes sync...');

        // Lấy integration đầu tiên
        $integration = TikTokShopIntegration::first();

        if (!$integration) {
            $this->warn('No TikTok Shop integration found. Please set up integration first.');
            return 0;
        }

        try {
            if ($categoryId) {
                // Sync specific category
                $result = $this->syncCategoryAttributes($integration, $categoryId, $force, $hours, $locale);

                if ($result) {
                    $this->info("✅ Successfully synced attributes for category: {$categoryId}");
                    return 0;
                } else {
                    $this->warn("⚠️  Skipped sync for category {$categoryId} (not needed). Use --force to override.");
                    return 0;
                }
            } else {
                // Sync all leaf categories
                $result = $this->syncAllCategoryAttributes($integration, $force, $hours, $locale);

                if ($result) {
                    $this->info("✅ Successfully synced attributes for all categories");
                    return 0;
                } else {
                    $this->warn("⚠️  No categories needed sync. Use --force to override.");
                    return 0;
                }
            }
        } catch (\Exception $e) {
            $this->error("❌ Error syncing category attributes: " . $e->getMessage());
            Log::error('TikTok category attributes sync error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return 1;
        }
    }

    /**
     * Sync attributes cho một category cụ thể
     */
    private function syncCategoryAttributes(TikTokShopIntegration $integration, string $categoryId, bool $force, int $hours, string $locale): bool
    {
        // Kiểm tra xem có cần sync không
        if (!$force && !TikTokCategoryAttribute::needsSync($categoryId, $hours)) {
            $this->warn("Attributes for category {$categoryId} were synced recently. Use --force to override.");
            return false;
        }

        // Kiểm tra xem category có tồn tại không
        $category = TikTokShopCategory::where('category_id', $categoryId)->first();
        if (!$category) {
            $this->warn("Category {$categoryId} not found in database. Please sync categories first.");
            return false;
        }

        // Kiểm tra xem category có phải là leaf category không
        if (!$category->is_leaf) {
            $this->warn("Category {$categoryId} is not a leaf category. Attributes can only be retrieved for leaf categories.");
            return false;
        }

        $this->info("Syncing attributes for category: {$categoryId} ({$category->category_name})");

        // Gọi API để lấy attributes
        $service = new TikTokShopService();
        $result = $service->getCategoryAttributes($integration, $categoryId, $locale);

        if (!$result['success']) {
            throw new \Exception("Failed to get attributes for category {$categoryId}: " . ($result['error'] ?? 'Unknown error'));
        }

        $attributes = $result['data'];

        $this->info("Retrieved " . count($attributes) . " attributes from TikTok Shop API");

        // Log chi tiết attributes nhận được
        Log::info('SyncTikTokCategoryAttributes: Attributes retrieved from API', [
            'category_id' => $categoryId,
            'category_name' => $category->category_name,
            'attributes_count' => count($attributes),
            'sample_attributes' => array_slice($attributes, 0, 3)
        ]);

        // Xóa attributes cũ của category này
        $deletedCount = TikTokCategoryAttribute::clearCategoryAttributes($categoryId);
        $this->info("Cleared {$deletedCount} old attributes");

        // Lưu attributes mới
        $savedCount = 0;
        foreach ($attributes as $attribute) {
            TikTokCategoryAttribute::createOrUpdateFromApiData($categoryId, $attribute);
            $savedCount++;
        }

        $this->info("Saved {$savedCount} attributes to database");

        // Log thành công với thống kê chi tiết
        $requiredCount = TikTokCategoryAttribute::where('category_id', $categoryId)->where('is_required', true)->count();
        $optionalCount = TikTokCategoryAttribute::where('category_id', $categoryId)->where('is_required', false)->count();
        $productPropsCount = TikTokCategoryAttribute::where('category_id', $categoryId)->where('type', 'PRODUCT_PROPERTY')->count();
        $salesPropsCount = TikTokCategoryAttribute::where('category_id', $categoryId)->where('type', 'SALES_PROPERTY')->count();

        Log::info('TikTok category attributes synced successfully', [
            'category_id' => $categoryId,
            'category_name' => $category->category_name,
            'total_attributes' => $savedCount,
            'required_attributes' => $requiredCount,
            'optional_attributes' => $optionalCount,
            'product_properties' => $productPropsCount,
            'sales_properties' => $salesPropsCount,
            'synced_at' => now()->toISOString(),
            'locale' => $locale
        ]);

        return true;
    }

    /**
     * Sync attributes cho tất cả leaf categories
     */
    private function syncAllCategoryAttributes(TikTokShopIntegration $integration, bool $force, int $hours, string $locale): bool
    {
        // Lấy tất cả leaf categories
        $leafCategories = TikTokShopCategory::where('is_leaf', true)->get();

        if ($leafCategories->isEmpty()) {
            $this->warn('No leaf categories found. Please sync categories first.');
            return false;
        }

        $this->info("Found {$leafCategories->count()} leaf categories to sync");

        $syncedCount = 0;
        $skippedCount = 0;
        $errorCount = 0;

        $progressBar = $this->output->createProgressBar($leafCategories->count());
        $progressBar->start();

        foreach ($leafCategories as $category) {
            try {
                $progressBar->setMessage("Syncing {$category->category_name} ({$category->category_id})");

                $result = $this->syncCategoryAttributes($integration, $category->category_id, $force, $hours, $locale);

                if ($result) {
                    $syncedCount++;
                } else {
                    $skippedCount++;
                }
            } catch (\Exception $e) {
                $errorCount++;
                Log::error('Error syncing category attributes', [
                    'category_id' => $category->category_id,
                    'category_name' => $category->category_name,
                    'error' => $e->getMessage()
                ]);
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine();

        $this->info("Sync completed:");
        $this->info("  ✅ Synced: {$syncedCount} categories");
        $this->info("  ⏭️  Skipped: {$skippedCount} categories");
        $this->info("  ❌ Errors: {$errorCount} categories");

        return $syncedCount > 0;
    }
}
