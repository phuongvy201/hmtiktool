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
                            {--locale=en-US : Locale for attributes}
                            {--market= : Market filter (e.g. US, UK)}
                            {--category-version= : Category version to override (v1, v2)}';

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

        $marketFilter = strtoupper((string) $this->option('market'));
        $categoryVersionFilter = $this->option('category-version') ? strtolower($this->option('category-version')) : null;

        $this->info('Starting TikTok Shop category attributes sync for UK (v1) and US (v2)...');

        if ($marketFilter) {
            $this->line("  • Market filter: {$marketFilter}");
        }

        if ($categoryVersionFilter) {
            $this->line("  • Category version override: {$categoryVersionFilter}");
        }

        $markets = $marketFilter ? [$marketFilter] : ['UK', 'US'];

        // Lấy integrations theo market filter
        $integrations = TikTokShopIntegration::query()
            ->whereIn('additional_data->market', $markets)
            ->get();

        if ($integrations->isEmpty()) {
            $this->warn('No UK or US TikTok Shop integration found. Please set up integrations first.');
            return 0;
        }

        $totalSynced = 0;
        $totalSkipped = 0;
        $totalErrors = 0;

        foreach ($integrations as $integration) {
            $market = $integration->market;
            $categoryVersion = $integration->getCategoryVersion();

            $this->info("Processing {$market} market ({$categoryVersion})...");

            if ($categoryVersionFilter && strtolower($categoryVersion) !== $categoryVersionFilter) {
                $this->warn("⚠️  Skipped {$market} because category version does not match filter ({$categoryVersionFilter})");
                $totalSkipped++;
                continue;
            }

            try {
                if ($categoryId) {
                    // Sync specific category
                    $result = $this->syncCategoryAttributes($integration, $categoryId, $force, $hours, $locale, $categoryVersionFilter);

                    if ($result) {
                        $this->info("✅ Successfully synced attributes for category {$categoryId} in {$market} ({$categoryVersion})");
                        $totalSynced++;
                    } else {
                        $this->warn("⚠️  Skipped sync for category {$categoryId} in {$market} ({$categoryVersion}) - not needed. Use --force to override.");
                        $totalSkipped++;
                    }
                } else {
                    // Sync all leaf categories
                    $result = $this->syncAllCategoryAttributes($integration, $force, $hours, $locale, $categoryVersionFilter);

                    if ($result) {
                        $this->info("✅ Successfully synced attributes for all categories in {$market} ({$categoryVersion})");
                        $totalSynced++;
                    } else {
                        $this->warn("⚠️  No categories needed sync in {$market} ({$categoryVersion}). Use --force to override.");
                        $totalSkipped++;
                    }
                }
            } catch (\Exception $e) {
                $totalErrors++;
                $this->error("❌ Error syncing category attributes for {$market} ({$categoryVersion}): " . $e->getMessage());
                Log::error('TikTok category attributes sync error', [
                    'market' => $market,
                    'category_version' => $categoryVersion,
                    'integration_id' => $integration->id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }
        }

        $this->newLine();
        $this->info("Sync Summary:");
        $this->info("  ✅ Successfully synced: {$totalSynced} markets");
        $this->info("  ⏭️  Skipped: {$totalSkipped} markets");
        $this->info("  ❌ Errors: {$totalErrors} markets");

        return $totalErrors > 0 ? 1 : 0;
    }

    /**
     * Sync attributes cho một category cụ thể
     */
    private function syncCategoryAttributes(TikTokShopIntegration $integration, string $categoryId, bool $force, int $hours, string $locale, ?string $categoryVersionOverride = null): bool
    {
        $market = $integration->market ?? 'US';
        $categoryVersion = strtolower($categoryVersionOverride ?: $integration->getCategoryVersion());

        // Kiểm tra xem có cần sync không (với category_version)
        if (!$force && !TikTokCategoryAttribute::needsSync($categoryId, $hours, $categoryVersion, $market)) {
            $this->warn("Attributes for category {$categoryId} (version {$categoryVersion}) were synced recently. Use --force to override.");
            return false;
        }

        // Kiểm tra xem category có tồn tại không (theo market và version)
        $category = TikTokShopCategory::where('category_id', $categoryId)
            ->where('market', $market)
            ->where('category_version', $categoryVersion)
            ->first();
        if (!$category) {
            $this->warn("Category {$categoryId} not found in database for {$market} ({$categoryVersion}). Please sync categories first.");
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

        // Xóa attributes cũ của category này (theo category_version)
        $deletedCount = TikTokCategoryAttribute::clearCategoryAttributes($categoryId, $categoryVersion, $market);
        $this->info("Cleared {$deletedCount} old attributes for {$market} ({$categoryVersion})");

        // Lưu attributes mới (với category_version)
        $savedCount = 0;
        foreach ($attributes as $attribute) {
            TikTokCategoryAttribute::createOrUpdateFromApiData($categoryId, $attribute, $market, $categoryVersion);
            $savedCount++;
        }

        $this->info("Saved {$savedCount} attributes to database");

        return true;
    }

    /**
     * Sync attributes cho tất cả leaf categories
     */
    private function syncAllCategoryAttributes(TikTokShopIntegration $integration, bool $force, int $hours, string $locale, ?string $categoryVersionOverride = null): bool
    {
        $market = $integration->market ?? 'US';
        $categoryVersion = strtolower($categoryVersionOverride ?: $integration->getCategoryVersion());

        // Lấy tất cả leaf categories của market và version này
        $leafCategories = TikTokShopCategory::where('market', $market)
            ->where('category_version', $categoryVersion)
            ->where('is_leaf', true)
            ->where('is_active', true)
            ->get();

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

                $result = $this->syncCategoryAttributes($integration, $category->category_id, $force, $hours, $locale, $categoryVersionOverride);

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
