<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\TikTokAnalyticsCacheService;
use App\Models\TikTokShop;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class RefreshAnalyticsCache extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'analytics:refresh-cache {--shop-id= : Specific shop ID to refresh} {--all : Refresh all shops}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Refresh TikTok Product API cache only (orders data from database)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔄 Starting TikTok Product API cache refresh...');
        $this->info('📊 Orders data will be fetched from database (no cache needed)');

        if ($this->option('all')) {
            $this->refreshAllShops();
        } elseif ($this->option('shop-id')) {
            $this->refreshSpecificShop($this->option('shop-id'));
        } else {
            $this->refreshActiveShops();
        }

        $this->info('✅ TikTok Product API cache refresh completed!');
    }

    /**
     * Refresh Product API cache for all shops
     */
    private function refreshAllShops()
    {
        $this->info('🔄 Refreshing Product API cache for all shops...');

        TikTokAnalyticsCacheService::clearAllAnalyticsCache();

        $shops = TikTokShop::with(['integration'])->get();
        $this->info("Found {$shops->count()} shops to refresh Product API cache");

        $bar = $this->output->createProgressBar($shops->count());
        $bar->start();

        foreach ($shops as $shop) {
            try {
                $this->refreshShopProductCache($shop);
                $bar->advance();
            } catch (\Exception $e) {
                $this->error("Error refreshing shop {$shop->id}: " . $e->getMessage());
                Log::error('Error refreshing shop Product API cache', [
                    'shop_id' => $shop->id,
                    'error' => $e->getMessage()
                ]);
            }
        }

        $bar->finish();
        $this->newLine();
    }

    /**
     * Refresh Product API cache for specific shop
     */
    private function refreshSpecificShop($shopId)
    {
        $this->info("🔄 Refreshing Product API cache for shop ID: {$shopId}");

        $shop = TikTokShop::with(['integration'])->find($shopId);

        if (!$shop) {
            $this->error("Shop with ID {$shopId} not found!");
            return;
        }

        TikTokAnalyticsCacheService::clearShopCache($shopId);
        $this->refreshShopProductCache($shop);

        $this->info("✅ Product API cache refreshed for shop: {$shop->shop_name}");
    }

    /**
     * Refresh cache for active shops only
     */
    private function refreshActiveShops()
    {
        $this->info('🔄 Refreshing cache for active shops...');

        $shops = TikTokShop::whereHas('integration', function ($query) {
            $query->where('is_active', true);
        })->with(['integration', 'orders'])->get();

        $this->info("Found {$shops->count()} active shops to refresh");

        if ($shops->isEmpty()) {
            $this->warn('No active shops found!');
            return;
        }

        $bar = $this->output->createProgressBar($shops->count());
        $bar->start();

        foreach ($shops as $shop) {
            try {
                TikTokAnalyticsCacheService::clearShopCache($shop->id);
                $this->refreshShopCache($shop);
                $bar->advance();
            } catch (\Exception $e) {
                $this->error("Error refreshing shop {$shop->id}: " . $e->getMessage());
                Log::error('Error refreshing shop cache', [
                    'shop_id' => $shop->id,
                    'error' => $e->getMessage()
                ]);
            }
        }

        $bar->finish();
        $this->newLine();
    }

    /**
     * Refresh Product API cache for a specific shop
     */
    private function refreshShopProductCache($shop)
    {
        // Pre-warm the Product API cache only
        $controller = new \App\Http\Controllers\TikTokShopAnalyticsController();

        // Use reflection to access private methods
        $reflection = new \ReflectionClass($controller);

        // Get active listings (this will cache the TikTok API call)
        $getActiveListings = $reflection->getMethod('getActiveListings');
        $getActiveListings->setAccessible(true);
        $getActiveListings->invoke($controller, $shop);

        $this->info("  ✅ Product API cache warmed for shop: {$shop->shop_name}");
    }
}
