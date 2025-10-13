<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use App\Models\TikTokShop;
use App\Models\TikTokOrder;
use App\Models\TikTokProductUploadHistory;
use App\Services\TikTokSignatureService;
use App\Services\TikTokAnalyticsCacheService;
use Carbon\Carbon;

class TikTokShopAnalyticsController extends Controller
{
    /**
     * Hiển thị trang Shop Analytics với cache và pagination
     */
    public function index(Request $request)
    {
        try {
            Log::info('Analytics index method called');

            $user = Auth::user();

            // Lấy danh sách shop mà user có quyền truy cập
            $shops = $this->getAccessibleShops($user);

            Log::info('Accessible shops found', [
                'count' => $shops->count(),
                'shop_ids' => $shops->pluck('id')->toArray()
            ]);

            // Lấy analytics data trực tiếp từ database (không cache)
            $analytics = $this->getShopAnalytics($shops);

            // Lấy daily orders trực tiếp từ database (không cache)
            $dailyOrders = $this->getDailyOrders($shops);

            // Pagination cho analytics data
            $perPage = $request->get('per_page', 10);
            $currentPage = $request->get('page', 1);

            $total = count($analytics);
            $offset = ($currentPage - 1) * $perPage;
            $paginatedAnalytics = array_slice($analytics, $offset, $perPage);

            // Tạo pagination data
            $pagination = [
                'current_page' => $currentPage,
                'per_page' => $perPage,
                'total' => $total,
                'last_page' => ceil($total / $perPage),
                'from' => $offset + 1,
                'to' => min($offset + $perPage, $total),
                'has_more_pages' => $currentPage < ceil($total / $perPage)
            ];

            return view('tiktok.shop-analytics', compact('analytics', 'dailyOrders', 'pagination'));
        } catch (\Exception $e) {
            Log::error('Error in Analytics index', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return view('tiktok.shop-analytics', [
                'analytics' => [],
                'dailyOrders' => [],
                'pagination' => [
                    'current_page' => 1,
                    'per_page' => 10,
                    'total' => 0,
                    'last_page' => 1,
                    'from' => 0,
                    'to' => 0,
                    'has_more_pages' => false
                ]
            ]);
        }
    }

    /**
     * Lấy danh sách shop mà user có quyền truy cập
     */
    private function getAccessibleShops($user)
    {
        if ($user->hasRole('team-admin')) {
            // Team admin xem tất cả shop trong team
            return TikTokShop::whereHas('team', function ($query) use ($user) {
                $query->where('id', $user->team_id);
            })->with(['orders'])->get();
        } elseif ($user->hasRole('seller')) {
            // Seller chỉ xem shop được assign
            return TikTokShop::whereHas('teamMembers', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })->with(['orders'])->get();
        }

        return collect();
    }

    /**
     * Lấy dữ liệu analytics cho các shop
     */
    private function getShopAnalytics($shops)
    {
        $analytics = [];

        foreach ($shops as $shop) {
            $orders = $shop->orders;

            Log::info('Processing shop for analytics', [
                'shop_id' => $shop->id,
                'shop_name' => $shop->shop_name
            ]);

            // Tính toán các metrics với cache
            $activeListings = TikTokAnalyticsCacheService::getActiveListings($shop, function ($shop) {
                return $this->getActiveListings($shop);
            });

            $todayListings = $this->getTodayListings($shop);
            $yesterdayListings = $this->getYesterdayListings($shop);
            $twoDaysAgoListings = $this->get2DaysAgoListings($shop);
            $threeDaysAgoListings = $this->get3DaysAgoListings($shop);
            $allTimeOrders = $orders->count();

            // Daily orders
            $dailyOrdersData = $this->getDailyOrdersForShop($orders);

            $analytics[] = [
                'shop' => $shop,
                'active_listings' => $activeListings,
                'today_listings' => $todayListings,
                'yesterday_listings' => $yesterdayListings,
                'two_days_ago_listings' => $twoDaysAgoListings,
                'three_days_ago_listings' => $threeDaysAgoListings,
                'all_time_orders' => $allTimeOrders,
                'daily_orders' => $dailyOrdersData
            ];
        }

        return $analytics;
    }

    /**
     * Lấy dữ liệu đơn hàng theo ngày cho tất cả shop
     */
    private function getDailyOrders($shops)
    {
        $dailyOrders = [];

        for ($i = 0; $i <= 6; $i++) { // Từ hôm nay đến 6 ngày trước
            $date = Carbon::now()->subDays($i);

            // Tên ngày theo thứ tự mới (i=0 là Today, i=6 là 6 Days Ago)
            $dayName = $i === 0 ? 'Today' : ($i === 1 ? 'Yesterday' : ($i === 2 ? '2 Days Ago' : ($i === 3 ? '3 Days Ago' : ($i === 4 ? '4 Days Ago' : ($i === 5 ? '5 Days Ago' : '6 Days Ago')))));

            $totalOrders = 0;
            $totalItems = 0;

            foreach ($shops as $shop) {
                $orders = $shop->orders->where('create_time', '>=', $date->startOfDay())
                    ->where('create_time', '<=', $date->endOfDay())
                    ->where('order_status', '!=', 'CANCELLED');

                $totalOrders += $orders->count();

                // Đếm items từ order_data['line_items']
                foreach ($orders as $order) {
                    if (isset($order->order_data['line_items']) && is_array($order->order_data['line_items'])) {
                        $totalItems += count($order->order_data['line_items']);
                    } else {
                        $totalItems += 1; // Mặc định 1 nếu không có line_items
                    }
                }
            }

            $dailyOrders[] = [
                'day' => $dayName,
                'orders' => $totalOrders,
                'items' => $totalItems,
                'date' => $date
            ];
        }

        return $dailyOrders; // Đã sắp xếp đúng thứ tự từ xa đến gần
    }

    /**
     * Lấy dữ liệu đơn hàng theo ngày cho một shop cụ thể
     */
    private function getDailyOrdersForShop($orders)
    {
        $dailyOrders = [];

        for ($i = 0; $i <= 6; $i++) { // Từ hôm nay đến 6 ngày trước
            $date = Carbon::now()->subDays($i);

            $dayOrders = $orders->where('create_time', '>=', $date->startOfDay())
                ->where('create_time', '<=', $date->endOfDay())
                ->where('order_status', '!=', 'CANCELLED');

            // Đếm tổng số items trong các đơn hàng của ngày đó
            $totalItems = 0;
            foreach ($dayOrders as $order) {
                // Đếm số items từ order_data['line_items']
                if (isset($order->order_data['line_items']) && is_array($order->order_data['line_items'])) {
                    $totalItems += count($order->order_data['line_items']);
                } else {
                    $totalItems += 1; // Mặc định 1 nếu không có line_items
                }
            }

            // Tên ngày theo thứ tự mới (i=0 là Today, i=6 là 6 Days Ago)
            $dayName = $i === 0 ? 'Today' : ($i === 1 ? 'Yesterday' : ($i === 2 ? '2 Days Ago' : ($i === 3 ? '3 Days Ago' : ($i === 4 ? '4 Days Ago' : ($i === 5 ? '5 Days Ago' : '6 Days Ago')))));

            $dailyOrders[] = [
                'date' => $date,
                'day' => $dayName,
                'orders' => $dayOrders->count(),
                'items' => $totalItems
            ];
        }

        return $dailyOrders;
    }

    /**
     * Lấy số lượng listing đang active từ TikTok API
     */
    private function getActiveListings($shop)
    {
        try {
            Log::info('Getting active listings for shop', [
                'shop_id' => $shop->id,
                'shop_name' => $shop->shop_name
            ]);

            $integration = $shop->integration;

            if (!$integration || !$integration->isActive()) {
                Log::warning('Integration not active for shop', [
                    'shop_id' => $shop->id,
                    'has_integration' => $integration ? 'yes' : 'no',
                    'is_active' => $integration ? $integration->isActive() : 'N/A'
                ]);
                return 0;
            }

            // Gọi TikTok Product API để lấy danh sách sản phẩm active
            $response = $this->callTikTokProductAPI($shop, [
                'page_size' => 100, // Lấy nhiều sản phẩm để đếm chính xác
                'page_number' => 1,
                'product_status' => 'ONLINE' // Chỉ lấy sản phẩm đang active
            ]);

            if ($response['success'] && isset($response['data']['products'])) {
                // Đếm số sản phẩm có status = 'ACTIVATE' (tương đương ONLINE)
                $activeCount = 0;
                $statusCounts = [];

                foreach ($response['data']['products'] as $product) {
                    $status = $product['status'] ?? 'UNKNOWN';
                    $statusCounts[$status] = ($statusCounts[$status] ?? 0) + 1;

                    // Chỉ đếm sản phẩm có status = 'ACTIVATE' là active
                    if ($status === 'ACTIVATE') {
                        $activeCount++;
                    }
                }

                Log::info('Active listings counted from products array', [
                    'shop_id' => $shop->id,
                    'active_count' => $activeCount,
                    'total_products' => count($response['data']['products']),
                    'status_breakdown' => $statusCounts
                ]);

                return $activeCount;
            } elseif ($response['success'] && isset($response['data']['total'])) {
                // Fallback nếu có total field
                Log::info('Active listings found via total field', [
                    'shop_id' => $shop->id,
                    'total' => $response['data']['total']
                ]);
                return $response['data']['total'];
            }

            Log::warning('No active listings found in API response', [
                'shop_id' => $shop->id,
                'response' => $response
            ]);
            return 0;
        } catch (\Exception $e) {
            Log::error('Error getting active listings', [
                'shop_id' => $shop->id,
                'error' => $e->getMessage()
            ]);

            return 0;
        }
    }

    /**
     * Lấy số lượng listing được thêm hôm nay từ database
     */
    private function getTodayListings($shop)
    {
        try {
            $today = Carbon::today();

            $count = TikTokProductUploadHistory::where('tiktok_shop_id', $shop->id)
                ->where('status', 'success')
                ->whereDate('uploaded_at', $today)
                ->count();

            Log::info('Today listings from database', [
                'shop_id' => $shop->id,
                'shop_name' => $shop->shop_name,
                'count' => $count,
                'date' => $today->format('Y-m-d')
            ]);

            return $count;
        } catch (\Exception $e) {
            Log::error('Error getting today listings from database', [
                'shop_id' => $shop->id,
                'error' => $e->getMessage()
            ]);

            return 0;
        }
    }

    /**
     * Lấy tất cả listings counts trong một query tối ưu (không cache)
     */
    private function getAllListingsCounts($shop)
    {
        try {
            $today = Carbon::today();
            $yesterday = Carbon::yesterday();
            $twoDaysAgo = Carbon::now()->subDays(2);
            $threeDaysAgo = Carbon::now()->subDays(3);

            // Một query duy nhất để lấy tất cả counts
            $counts = TikTokProductUploadHistory::where('tiktok_shop_id', $shop->id)
                ->where('status', 'success')
                ->whereIn('uploaded_at', [
                    $today->format('Y-m-d'),
                    $yesterday->format('Y-m-d'),
                    $twoDaysAgo->format('Y-m-d'),
                    $threeDaysAgo->format('Y-m-d')
                ])
                ->selectRaw('
                    DATE(uploaded_at) as upload_date,
                    COUNT(*) as count
                ')
                ->groupBy('upload_date')
                ->pluck('count', 'upload_date')
                ->toArray();

            $result = [
                'today' => $counts[$today->format('Y-m-d')] ?? 0,
                'yesterday' => $counts[$yesterday->format('Y-m-d')] ?? 0,
                'two_days_ago' => $counts[$twoDaysAgo->format('Y-m-d')] ?? 0,
                'three_days_ago' => $counts[$threeDaysAgo->format('Y-m-d')] ?? 0,
            ];

            Log::info('All listings counts from database (optimized)', [
                'shop_id' => $shop->id,
                'shop_name' => $shop->shop_name,
                'counts' => $result
            ]);

            return $result;
        } catch (\Exception $e) {
            Log::error('Error getting all listings counts from database', [
                'shop_id' => $shop->id,
                'error' => $e->getMessage()
            ]);

            return [
                'today' => 0,
                'yesterday' => 0,
                'two_days_ago' => 0,
                'three_days_ago' => 0,
            ];
        }
    }

    /**
     * Lấy số lượng listing được thêm hôm qua từ database
     */
    private function getYesterdayListings($shop)
    {
        $counts = $this->getAllListingsCounts($shop);
        return $counts['yesterday'];
    }

    /**
     * Lấy số lượng listing được thêm 2 ngày trước từ database
     */
    private function get2DaysAgoListings($shop)
    {
        $counts = $this->getAllListingsCounts($shop);
        return $counts['two_days_ago'];
    }

    /**
     * Lấy số lượng listing được thêm 3 ngày trước từ database
     */
    private function get3DaysAgoListings($shop)
    {
        $counts = $this->getAllListingsCounts($shop);
        return $counts['three_days_ago'];
    }

    /**
     * Gọi TikTok Product API với signature authentication
     */
    private function callTikTokProductAPI($shop, $filters = [])
    {
        try {
            $integration = $shop->integration;

            if (!$integration || !$integration->isActive()) {
                return [
                    'success' => false,
                    'error' => 'Integration không hoạt động hoặc token đã hết hạn'
                ];
            }

            // Lấy thông tin app credentials
            $appKey = config('tiktok-shop.app_key');
            $appSecret = config('tiktok-shop.app_secret');
            $shopCipher = $shop->getShopCipher();

            if (!$appKey || !$appSecret) {
                return [
                    'success' => false,
                    'error' => 'Thiếu TikTok app credentials'
                ];
            }

            // Tạo timestamp
            $timestamp = time(); // TikTok yêu cầu timestamp seconds

            // Chuẩn bị body parameters
            $bodyParams = array_merge([
                'page_size' => 20,
                'page_number' => 1
            ], $filters);

            // Tạo signature
            $signature = TikTokSignatureService::generateProductSearchSignature(
                $appKey,
                $appSecret,
                $timestamp,
                $bodyParams,
                $shopCipher,
                false // return_draft_version = false để lấy latest product info
            );

            // Chuẩn bị query parameters (phải match với signature generation)
            $queryParams = [
                'app_key' => $appKey,
                'timestamp' => $timestamp,
                'return_draft_version' => 'false',
                'page_size' => (string)$bodyParams['page_size'],
                'shop_cipher' => $shopCipher,
                'sign' => $signature
            ];

            // Build URL
            $url = 'https://open-api.tiktokglobalshop.com/product/202309/products/search?' . http_build_query($queryParams);

            $headers = [
                'Content-Type: application/json',
                'x-tts-access-token: ' . $integration->access_token
            ];

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($bodyParams));
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            Log::info('TikTok Product API Response', [
                'shop_id' => $shop->id,
                'url' => $url,
                'body_params' => $bodyParams,
                'query_params' => $queryParams,
                'http_code' => $httpCode,
                'response' => $response
            ]);

            if ($httpCode === 200) {
                $data = json_decode($response, true);

                if (isset($data['code']) && $data['code'] === 0) {
                    return [
                        'success' => true,
                        'data' => $data['data'] ?? []
                    ];
                } else {
                    return [
                        'success' => false,
                        'error' => $data['message'] ?? 'API error'
                    ];
                }
            }

            return [
                'success' => false,
                'error' => 'HTTP ' . $httpCode . ': ' . $response
            ];
        } catch (\Exception $e) {
            Log::error('TikTok Product API Exception', [
                'shop_id' => $shop->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * API endpoint để lấy dữ liệu analytics
     */
    public function getAnalyticsData(Request $request)
    {
        $user = Auth::user();
        $shops = $this->getAccessibleShops($user);
        $analytics = $this->getShopAnalytics($shops);
        $dailyOrders = $this->getDailyOrders($shops);

        return response()->json([
            'success' => true,
            'data' => [
                'analytics' => $analytics,
                'daily_orders' => $dailyOrders
            ]
        ]);
    }

    /**
     * Test endpoint để kiểm tra việc lấy sản phẩm active
     */
    public function testProductAPI(Request $request)
    {
        try {
            $user = Auth::user();
            $shops = $this->getAccessibleShops($user);

            if ($shops->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Không có shop nào để test'
                ]);
            }

            $shop = $shops->first();
            Log::info('Testing Product API', [
                'shop_id' => $shop->id,
                'shop_name' => $shop->shop_name,
                'shop_cipher' => $shop->shop_cipher
            ]);

            // Test lấy active listings
            $activeListings = $this->getActiveListings($shop);
            $todayListings = $this->getTodayListings($shop);
            $yesterdayListings = $this->getYesterdayListings($shop);

            // Test trực tiếp Product API
            $productResponse = $this->callTikTokProductAPI($shop, [
                'page_size' => 5,
                'page_number' => 1,
                'product_status' => 'ONLINE'
            ]);

            return response()->json([
                'success' => true,
                'data' => [
                    'shop' => [
                        'id' => $shop->id,
                        'name' => $shop->shop_name,
                        'cipher' => $shop->shop_cipher
                    ],
                    'active_listings' => $activeListings,
                    'today_listings' => $todayListings,
                    'yesterday_listings' => $yesterdayListings,
                    'product_api_response' => $productResponse
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Test Product API Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }
}
