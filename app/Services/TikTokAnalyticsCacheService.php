<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class TikTokAnalyticsCacheService
{
    const CACHE_PREFIX = 'tiktok_product_api_';
    const ACTIVE_LISTINGS_CACHE_TTL = 300; // 5 phút - chỉ cache TikTok Product API

    /**
     * Lấy active listings với cache
     */
    public static function getActiveListings($shop, $callback)
    {
        $cacheKey = self::CACHE_PREFIX . 'active_listings_' . $shop->id;

        return Cache::remember($cacheKey, self::ACTIVE_LISTINGS_CACHE_TTL, function () use ($shop, $callback) {
            Log::info('Cache miss - fetching active listings from API', [
                'shop_id' => $shop->id,
                'shop_name' => $shop->shop_name
            ]);

            return $callback($shop);
        });
    }

    /**
     * Lấy daily orders - KHÔNG cache, lấy trực tiếp từ database
     */
    public static function getDailyOrders($shops, $callback)
    {
        Log::info('Fetching daily orders from database (no cache)', [
            'shop_count' => $shops->count(),
            'shop_ids' => $shops->pluck('id')->toArray()
        ]);

        return $callback($shops);
    }

    /**
     * Lấy shop analytics - KHÔNG cache, lấy trực tiếp từ database
     */
    public static function getShopAnalytics($shops, $callback)
    {
        Log::info('Fetching shop analytics from database (no cache)', [
            'shop_count' => $shops->count(),
            'shop_ids' => $shops->pluck('id')->toArray()
        ]);

        return $callback($shops);
    }

    /**
     * Xóa cache cho một shop cụ thể - chỉ xóa Product API cache
     */
    public static function clearShopCache($shopId)
    {
        $cacheKey = self::CACHE_PREFIX . 'active_listings_' . $shopId;
        Cache::forget($cacheKey);

        Log::info('Cleared Product API cache for shop', ['shop_id' => $shopId]);
    }

    /**
     * Xóa tất cả Product API cache
     */
    public static function clearAllAnalyticsCache()
    {
        $keys = Cache::getRedis()->keys(self::CACHE_PREFIX . '*');
        if (!empty($keys)) {
            Cache::getRedis()->del($keys);
        }

        Log::info('Cleared all Product API cache');
    }

    /**
     * Lấy cache info để debug
     */
    public static function getCacheInfo()
    {
        $keys = Cache::getRedis()->keys(self::CACHE_PREFIX . '*');
        $info = [];

        foreach ($keys as $key) {
            $ttl = Cache::getRedis()->ttl($key);
            $info[] = [
                'key' => $key,
                'ttl' => $ttl,
                'expires_at' => $ttl > 0 ? Carbon::now()->addSeconds($ttl)->format('Y-m-d H:i:s') : 'Expired'
            ];
        }

        return $info;
    }
}
