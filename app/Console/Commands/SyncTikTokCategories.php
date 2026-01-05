<?php

namespace App\Console\Commands;

use App\Models\TikTokShopCategory;
use App\Models\TikTokShopIntegration;
use App\Services\TikTokShopService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Artisan;

class SyncTikTokCategories extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tiktok:sync-categories 
                            {--force : Force sync even if not needed}
                            {--hours=24 : Hours threshold for sync check}
                            {--market= : Market filter (e.g. US, UK)}
                            {--category-version= : Category version override (v1, v2)}';

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
        $marketFilter = strtoupper((string) $this->option('market'));
        $categoryVersionFilter = $this->option('category-version') ? strtolower($this->option('category-version')) : null;

        $this->info('Starting TikTok Shop categories sync for UK (v1) and US (v2)...');

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
            $integrationCategoryVersion = strtolower($integration->getCategoryVersion());
            $targetCategoryVersion = $categoryVersionFilter ?: $integrationCategoryVersion;

            if ($categoryVersionFilter && $integrationCategoryVersion !== $categoryVersionFilter) {
                $this->warn("⚠️  Skipped {$market} because integration version ({$integrationCategoryVersion}) does not match override ({$categoryVersionFilter})");
                $totalSkipped++;
                continue;
            }

            $this->info("Processing {$market} market ({$targetCategoryVersion})...");

            try {
                $result = $this->syncSystemCategories($integration, $force, $hours, $targetCategoryVersion);

                if ($result) {
                    $this->info("✅ Successfully synced categories for {$market} ({$targetCategoryVersion})");
                    $totalSynced++;
                } else {
                    $this->warn("⚠️  Skipped sync for {$market} ({$targetCategoryVersion}) - not needed. Use --force to override.");
                    $totalSkipped++;
                }
            } catch (\Exception $e) {
                $totalErrors++;
                $this->error("❌ Error syncing categories for {$market} ({$targetCategoryVersion}): " . $e->getMessage());
                Log::error('TikTok categories sync error', [
                    'market' => $market,
                    'category_version' => $targetCategoryVersion,
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
     * Sync categories cho một market cụ thể
     */
    private function syncSystemCategories(TikTokShopIntegration $integration, bool $force, int $hours, ?string $categoryVersionOverride = null): bool
    {
        $market = strtoupper($integration->market ?? 'US');
        $categoryVersion = $categoryVersionOverride ?: $integration->getCategoryVersion();

        if (!$force && !$this->needsMarketSync($market, $categoryVersion, $hours)) {
            $this->warn("Categories for {$market} ({$categoryVersion}) were synced recently. Use --force to override.");
            return false;
        }

        $service = new TikTokShopService();
        $result = $service->getCategories($integration);

        if (!$result['success']) {
            throw new \Exception("Failed to get categories from TikTok Shop API: " . ($result['error'] ?? 'Unknown error'));
        }

        $categories = $result['data'];
        $rawCategories = $result['raw_data'] ?? [];

        $this->info("Retrieved " . count($categories) . " categories from TikTok Shop API for {$market} ({$categoryVersion})");

        $timestamp = now();
        $timestampIso = $timestamp->toISOString();
        $records = [];

        if (!empty($rawCategories)) {

            foreach ($rawCategories as $index => $category) {
                $categoryId = (string) ($category['id'] ?? '');
                $categoryName = trim($category['local_name'] ?? $category['name'] ?? '');
                $parentId = $category['parent_id'] ?? null;
                if ($parentId === '') {
                    $parentId = null;
                }
                $level = isset($category['level']) ? (int) $category['level'] : 1;
                $isLeaf = array_key_exists('is_leaf', $category) ? (bool) $category['is_leaf'] : true;

                if ($categoryId === '' || $categoryName === '') {
                    continue;
                }

                $records[] = [
                    'market' => $market,
                    'category_version' => $categoryVersion,
                    'category_id' => $categoryId,
                    'category_name' => $categoryName,
                    'parent_category_id' => $parentId,
                    'level' => $level,
                    'is_leaf' => $isLeaf,
                    'is_active' => true,
                    'category_data' => json_encode([
                        'original_data' => $category,
                        'parsed_at' => $timestampIso
                    ]),
                    'last_synced_at' => $timestamp,
                    'created_at' => $timestamp,
                    'updated_at' => $timestamp,
                ];
            }
        } else {
            foreach ($categories as $categoryId => $categoryName) {
                $categoryData = $this->parseCategoryData($categoryId, $categoryName);

                $records[] = [
                    'market' => $market,
                    'category_version' => $categoryVersion,
                    'category_id' => $categoryData['category_id'],
                    'category_name' => $categoryData['category_name'],
                    'parent_category_id' => $categoryData['parent_category_id'],
                    'level' => $categoryData['level'],
                    'is_leaf' => $categoryData['is_leaf'],
                    'is_active' => true,
                    'category_data' => json_encode($categoryData['metadata']),
                    'last_synced_at' => $timestamp,
                    'created_at' => $timestamp,
                    'updated_at' => $timestamp,
                ];
            }
        }

        if (empty($records)) {
            $this->warn("No categories returned from API for {$market} ({$categoryVersion}). Skipping persistence.");
            return false;
        }

        $incomingIds = array_column($records, 'category_id');

        $inactiveCount = 0;
        if (empty($incomingIds)) {
            $inactiveCount = TikTokShopCategory::where('market', $market)
                ->where('category_version', $categoryVersion)
                ->update([
                    'is_active' => false,
                    'last_synced_at' => $timestamp,
                    'updated_at' => $timestamp,
                ]);
        } else {
            $inactiveCount = TikTokShopCategory::where('market', $market)
                ->where('category_version', $categoryVersion)
                ->whereNotIn('category_id', $incomingIds)
                ->update([
                    'is_active' => false,
                    'last_synced_at' => $timestamp,
                    'updated_at' => $timestamp,
                ]);
        }

        if ($inactiveCount > 0) {
            $this->info("Marked {$inactiveCount} stale categories as inactive for {$market} ({$categoryVersion})");
        }

        // Chia nhỏ thành batches để tránh lỗi max_allowed_packet
        $batchSize = 500;
        $batches = array_chunk($records, $batchSize);
        $totalBatches = count($batches);
        
        $this->info("Saving " . count($records) . " categories in {$totalBatches} batches...");
        
        foreach ($batches as $index => $batch) {
            TikTokShopCategory::upsert(
                $batch,
                ['category_id', 'market'],
                [
                    'category_name',
                    'parent_category_id',
                    'level',
                    'is_leaf',
                    'is_active',
                    'category_version',
                    'category_data',
                    'last_synced_at',
                    'updated_at',
                ]
            );
            
            $batchNum = $index + 1;
            $this->line("  Batch {$batchNum}/{$totalBatches}: " . count($batch) . " categories saved");
        }

        $savedCount = count($records);
        $this->info("✅ Total saved: {$savedCount} categories to database");

        $this->triggerAttributeSync($market, $categoryVersion, $force, $hours);

        return true;
    }

    private function triggerAttributeSync(string $market, string $categoryVersion, bool $force, int $hours): void
    {
        $locale = $market === 'UK' ? 'en-GB' : 'en-US';

        $options = [
            '--hours' => (string) $hours,
            '--locale' => $locale,
            '--market' => $market,
            '--category-version' => $categoryVersion,
        ];

        if ($force) {
            $options['--force'] = true;
        }

        try {
            $this->info("Triggering attribute sync for {$market} ({$categoryVersion})...");
            Artisan::call('tiktok:sync-category-attributes', $options);

            $output = trim(Artisan::output());
            if (!empty($output)) {
                $this->line($output);
            }
        } catch (\Exception $e) {
            $this->error("❌ Failed to trigger attribute sync for {$market} ({$categoryVersion}): " . $e->getMessage());

            Log::error('TikTok categories attribute sync trigger error', [
                'market' => $market,
                'category_version' => $categoryVersion,
                'error' => $e->getMessage(),
            ]);
        }
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

        return [
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
    }

    /**
     * Kiểm tra xem có cần sync categories cho market cụ thể không
     */
    private function needsMarketSync(string $market, string $categoryVersion, int $hours): bool
    {
        $lastSync = TikTokShopCategory::where('market', strtoupper($market))
            ->where('category_version', strtolower($categoryVersion))
            ->orderBy('last_synced_at', 'desc')
            ->value('last_synced_at');

        if (!$lastSync) {
            return true;
        }

        return $lastSync->diffInHours(now()) >= $hours;
    }
}
