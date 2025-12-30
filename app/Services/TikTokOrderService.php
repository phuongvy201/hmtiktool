<?php

namespace App\Services;

use App\Models\TikTokOrder;
use App\Models\TikTokShop;
use App\Models\TikTokShopIntegration;
use App\Services\TikTokSignatureService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class TikTokOrderService
{
    private const API_VERSION = '202309';

    /**
     * Tìm kiếm đơn hàng từ TikTok Shop API
     */
    public function searchOrders(
        TikTokShop $shop,
        array $filters = [],
        int $pageSize = 20,
        string $sortOrder = 'DESC',
        string $sortField = 'create_time',
        ?string $pageToken = null
    ): array {
        Log::info('=== START SEARCH ORDERS FROM TIKTOK ===', [
            'shop_id' => $shop->id,
            'shop_cipher' => $shop->getShopCipher(),
            'filters' => $filters,
            'page_size' => $pageSize
        ]);

        try {
            // Kiểm tra integration có hoạt động không
            $integration = $shop->integration;
            if (!$integration) {
                throw new Exception('TikTok Shop không có integration');
            }

            // Tạm thời bỏ qua check isActive() để test
            // if (!$integration->isActive()) {
            //     throw new Exception('TikTok Shop integration không hoạt động hoặc token đã hết hạn');
            // }

            // Kiểm tra access token
            if ($integration->isAccessTokenExpired()) {
                $refreshResult = $integration->refreshAccessToken();
                if (!$refreshResult['success']) {
                    throw new Exception('Không thể refresh token: ' . $refreshResult['message']);
                }
            }

            // Tạo timestamp
            $timestamp = time();

            // Chuẩn bị body parameters
            $bodyParams = $this->prepareSearchFilters($filters);

            // Tạo signature (bao gồm page_size trong signature generation)
            $signature = TikTokSignatureService::generateOrderSearchSignature(
                $integration->getAppKey(),
                $integration->getAppSecret(),
                (string) $timestamp,
                $bodyParams,
                $shop->getShopCipher(),
                $pageSize
            );

            // Chuẩn bị query parameters (chỉ những trường cần thiết)
            $queryParams = [
                'shop_cipher' => $shop->getShopCipher(),
                'app_key' => $integration->getAppKey(),
                'sign' => $signature,
                'timestamp' => $timestamp,
                'page_size' => $pageSize
            ];

            if ($pageToken) {
                $queryParams['page_token'] = $pageToken;
            }

            // Gọi API
            $response = $this->callSearchOrdersAPI($queryParams, $bodyParams, $integration);

            if ($response['success']) {
                // Lưu dữ liệu đơn hàng vào database
                $this->saveOrdersToDatabase($response['data'], $shop);

                Log::info('Search orders successful', [
                    'shop_id' => $shop->id,
                    'orders_count' => count($response['data']['orders'] ?? $response['data']['order_list'] ?? []),
                    'has_more' => $response['data']['more'] ?? false
                ]);

                return [
                    'success' => true,
                    'data' => $response['data'],
                    'message' => 'Tìm kiếm đơn hàng thành công'
                ];
            } else {
                throw new Exception($response['message'] ?? 'Lỗi không xác định khi tìm kiếm đơn hàng');
            }
        } catch (Exception $e) {
            Log::error('Search orders failed', [
                'shop_id' => $shop->id,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        } finally {
            Log::info('=== END SEARCH ORDERS FROM TIKTOK ===');
        }
    }

    /**
     * Chuẩn bị filters cho tìm kiếm đơn hàng
     */
    private function prepareSearchFilters(array $filters): array
    {
        $bodyParams = [];

        // Trạng thái đơn hàng
        if (isset($filters['order_status'])) {
            $bodyParams['order_status'] = $filters['order_status'];
        }

        // Thời gian tạo
        if (isset($filters['create_time_ge'])) {
            $bodyParams['create_time_ge'] = $filters['create_time_ge'];
        }
        if (isset($filters['create_time_lt'])) {
            $bodyParams['create_time_lt'] = $filters['create_time_lt'];
        }

        // Thời gian cập nhật
        if (isset($filters['update_time_ge'])) {
            $bodyParams['update_time_ge'] = $filters['update_time_ge'];
        }
        if (isset($filters['update_time_lt'])) {
            $bodyParams['update_time_lt'] = $filters['update_time_lt'];
        }

        // Phương thức vận chuyển
        if (isset($filters['shipping_type'])) {
            $bodyParams['shipping_type'] = $filters['shipping_type'];
        }

        // ID người mua
        if (isset($filters['buyer_user_id'])) {
            $bodyParams['buyer_user_id'] = $filters['buyer_user_id'];
        }

        // Yêu cầu hủy
        if (isset($filters['is_buyer_request_cancel'])) {
            $bodyParams['is_buyer_request_cancel'] = $filters['is_buyer_request_cancel'];
        }

        // Danh sách kho
        if (isset($filters['warehouse_ids']) && is_array($filters['warehouse_ids'])) {
            $bodyParams['warehouse_ids'] = $filters['warehouse_ids'];
        }

        // Nếu không có body parameters nào, thêm một filter mặc định để signature hoạt động
        // TikTok Order API yêu cầu ít nhất một body parameter để signature generation hoạt động
        if (empty($bodyParams)) {
            // Thử dùng update_time_ge thay vì create_time_ge để lấy đơn hàng đã được cập nhật gần đây
            // Ưu tiên update_time vì nó sẽ lấy được cả đơn hàng cũ nhưng mới được cập nhật
            $ninetyDaysAgo = strtotime('-90 days');

            // Dùng cả create_time_ge và update_time_ge để lấy được nhiều đơn hàng hơn
            $bodyParams['update_time_ge'] = $ninetyDaysAgo;
            // Không dùng create_time_ge để tránh bỏ sót đơn hàng cũ nhưng mới update

            Log::info('No filters provided, adding default time filter for signature generation', [
                'default_filter' => [
                    'update_time_ge' => $ninetyDaysAgo,
                    'update_time_ge_formatted' => date('Y-m-d H:i:s', $ninetyDaysAgo),
                    'current_time' => date('Y-m-d H:i:s'),
                    'days_ago' => 90,
                    'note' => 'Using update_time_ge instead of create_time_ge to catch recently updated orders'
                ],
                'note' => 'TikTok Order API requires at least one body parameter for signature to work. Using update_time_ge (90 days ago) as default filter.'
            ]);
        }

        return $bodyParams;
    }

    /**
     * Gọi API tìm kiếm đơn hàng
     */
    private function callSearchOrdersAPI(array $queryParams, array $bodyParams, TikTokShopIntegration $integration): array
    {
        $url = 'https://open-api.tiktokglobalshop.com/order/' . self::API_VERSION . '/orders/search';

        $headers = [
            'Content-Type' => 'application/json',
            'x-tts-access-token' => $integration->access_token
        ];

        // Log request details
        Log::info('TikTok Order Search API Request Details', [
            'url' => $url,
            'headers' => [
                'x-tts-access-token' => substr($integration->access_token, 0, 20) . '...',
                'content-type' => 'application/json'
            ],
            'query_params' => $queryParams,
            'body_params' => $bodyParams,
            'app_key_full' => $integration->getAppKey(),
            'app_secret_length' => strlen($integration->getAppSecret()),
            'access_token_full' => $integration->access_token,
            'shop_cipher' => $queryParams['shop_cipher'],
            'sign_generation' => [
                'app_key' => $integration->getAppKey(),
                'app_secret_length' => strlen($integration->getAppSecret()),
                'timestamp' => $queryParams['timestamp'],
                'sign' => $queryParams['sign'],
                'method' => 'TikTokSignatureService::generateOrderSearchSignature',
                'api_path' => '/order/' . self::API_VERSION . '/orders/search',
                'content_type' => 'application/json',
                'has_body_params' => !empty($bodyParams),
                'note' => 'shop_cipher INCLUDED in signature generation and query params'
            ]
        ]);

        // Đảm bảo JSON encoding nhất quán
        $jsonBody = json_encode($bodyParams, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        // Sắp xếp query parameters theo thứ tự bảng chữ cái
        ksort($queryParams);
        $queryString = http_build_query($queryParams);

        $response = Http::withHeaders($headers)
            ->timeout(30)
            ->withBody($jsonBody, 'application/json')
            ->post($url . '?' . $queryString);

        $httpCode = $response->status();
        $responseData = $response->json();

        // Log response details
        Log::info('TikTok Order Search API Response Details', [
            'status_code' => $httpCode,
            'response_body' => $responseData,
            'response_headers' => $response->headers()
        ]);

        if ($httpCode === 200 && isset($responseData['code']) && $responseData['code'] === 0) {
            return [
                'success' => true,
                'data' => $responseData['data'] ?? []
            ];
        } else {
            return [
                'success' => false,
                'message' => $responseData['message'] ?? 'API call failed',
                'code' => $responseData['code'] ?? null
            ];
        }
    }

    /**
     * Lưu dữ liệu đơn hàng vào database
     */
    private function saveOrdersToDatabase(array $apiData, TikTokShop $shop): void
    {
        // TikTok API trả về 'orders' chứ không phải 'order_list'
        $orderList = $apiData['orders'] ?? $apiData['order_list'] ?? [];

        if (empty($orderList)) {
            Log::info('No orders found in API response', [
                'shop_id' => $shop->id,
                'available_keys' => array_keys($apiData)
            ]);
            return;
        }

        $savedCount = 0;
        $updatedCount = 0;

        foreach ($orderList as $orderData) {
            try {
                // TikTok API trả về 'id' chứ không phải 'order_id'
                $orderId = $orderData['id'] ?? $orderData['order_id'] ?? null;
                if (!$orderId) {
                    Log::warning('Order data missing order_id', [
                        'shop_id' => $shop->id,
                        'order_data' => $orderData,
                        'available_keys' => array_keys($orderData)
                    ]);
                    continue;
                }

                // Kiểm tra đơn hàng đã tồn tại chưa
                $existingOrder = TikTokOrder::where('order_id', $orderId)
                    ->where('tiktok_shop_id', $shop->id)
                    ->first();

                $orderAttributes = $this->prepareOrderAttributes($orderData, $shop->id);

                if ($existingOrder) {
                    // Cập nhật đơn hàng hiện có
                    $existingOrder->update($orderAttributes);
                    $updatedCount++;

                    Log::info('Updated existing order', [
                        'shop_id' => $shop->id,
                        'order_id' => $orderId
                    ]);
                } else {
                    // Tạo đơn hàng mới
                    TikTokOrder::create($orderAttributes);
                    $savedCount++;

                    Log::info('Created new order', [
                        'shop_id' => $shop->id,
                        'order_id' => $orderId
                    ]);
                }
            } catch (Exception $e) {
                Log::error('Error saving order to database', [
                    'shop_id' => $shop->id,
                    'order_id' => $orderData['order_id'] ?? 'unknown',
                    'error' => $e->getMessage()
                ]);
            }
        }

        Log::info('Orders saved to database', [
            'shop_id' => $shop->id,
            'saved_count' => $savedCount,
            'updated_count' => $updatedCount,
            'total_processed' => count($orderList)
        ]);
    }

    /**
     * Chuẩn bị attributes cho model TikTokOrder
     */
    private function prepareOrderAttributes(array $orderData, int $shopId): array
    {
        return [
            'tiktok_shop_id' => $shopId,
            'order_id' => $orderData['id'] ?? $orderData['order_id'] ?? null,
            'order_number' => $orderData['order_number'] ?? $orderData['order_no'] ?? $orderData['display_order_number'] ?? null,
            'order_status' => $orderData['status'] ?? $orderData['order_status'] ?? null,
            'buyer_user_id' => $orderData['user_id'] ?? $orderData['buyer_user_id'] ?? null,
            'buyer_username' => $orderData['buyer_username'] ?? null,
            'shipping_type' => $orderData['shipping_type'] ?? null,
            'is_buyer_request_cancel' => $orderData['is_buyer_request_cancel'] ?? false,
            'warehouse_id' => $orderData['warehouse_id'] ?? null,
            'warehouse_name' => $orderData['warehouse_name'] ?? null,
            'create_time' => isset($orderData['create_time']) ?
                date('Y-m-d H:i:s', $orderData['create_time']) : null,
            'update_time' => isset($orderData['update_time']) ?
                date('Y-m-d H:i:s', $orderData['update_time']) : null,
            'order_amount' => $orderData['payment']['total_amount'] ?? $orderData['order_amount'] ?? null,
            'currency' => $orderData['payment']['currency'] ?? $orderData['currency'] ?? 'GBP',
            'shipping_fee' => $orderData['payment']['shipping_fee'] ?? $orderData['shipping_fee'] ?? null,
            'total_amount' => $orderData['total_amount'] ?? null,
            'order_data' => $orderData,
            'raw_response' => $orderData,
            'sync_status' => 'synced',
            'last_synced_at' => now()
        ];
    }

    /**
     * Đồng bộ tất cả đơn hàng từ TikTok Shop
     */
    public function syncAllOrders(TikTokShop $shop, array $filters = []): array
    {
        Log::info('Starting full order sync', [
            'shop_id' => $shop->id,
            'filters' => $filters
        ]);

        $totalOrders = 0;
        $pageToken = null;
        $hasMore = true;

        while ($hasMore) {
            Log::info('Fetching page', [
                'shop_id' => $shop->id,
                'page_token' => $pageToken
            ]);

            // Gọi API trực tiếp để tránh lưu trùng lặp
            $result = $this->fetchOrdersPage($shop, $filters, 100, $pageToken);

            if (!$result['success']) {
                return [
                    'success' => false,
                    'message' => $result['message'],
                    'total_orders' => $totalOrders
                ];
            }

            $data = $result['data'];
            $orderList = $data['orders'] ?? $data['order_list'] ?? [];
            $totalOrders += count($orderList);

            // Lưu orders vào database
            if (!empty($orderList)) {
                $this->saveOrdersToDatabase($data, $shop);
                Log::info('Orders saved to database in syncAllOrders', [
                    'shop_id' => $shop->id,
                    'orders_count' => count($orderList)
                ]);
            }

            // Kiểm tra có trang tiếp theo không
            $hasMore = $data['more'] ?? false;
            $pageToken = $data['next_page_token'] ?? null;

            // Nếu có next_page_token thì vẫn còn trang tiếp theo
            if (!$hasMore && $pageToken) {
                $hasMore = true;
                Log::info('Found next_page_token, continuing pagination', [
                    'shop_id' => $shop->id,
                    'next_page_token' => $pageToken
                ]);
            }

            Log::info('Processed page in sync', [
                'shop_id' => $shop->id,
                'orders_in_page' => count($orderList),
                'total_orders' => $totalOrders,
                'has_more' => $hasMore,
                'next_page_token' => $pageToken
            ]);

            // Nghỉ một chút để tránh rate limit
            if ($hasMore) {
                sleep(1);
            }
        }

        Log::info('Full order sync completed', [
            'shop_id' => $shop->id,
            'total_orders' => $totalOrders
        ]);

        return [
            'success' => true,
            'message' => "Đồng bộ thành công {$totalOrders} đơn hàng",
            'total_orders' => $totalOrders
        ];
    }

    /**
     * Lấy một trang đơn hàng từ TikTok API (không lưu database)
     */
    public function fetchOrdersPage(
        TikTokShop $shop,
        array $filters = [],
        int $pageSize = 20,
        ?string $pageToken = null
    ): array {
        Log::info('Fetching orders page', [
            'shop_id' => $shop->id,
            'page_size' => $pageSize,
            'page_token' => $pageToken
        ]);

        try {
            // Kiểm tra integration có hoạt động không
            $integration = $shop->integration;
            if (!$integration) {
                throw new Exception('TikTok Shop không có integration');
            }

            // Kiểm tra access token
            if ($integration->isAccessTokenExpired()) {
                $refreshResult = $integration->refreshAccessToken();
                if (!$refreshResult['success']) {
                    throw new Exception('Không thể refresh token: ' . $refreshResult['message']);
                }
            }

            // Tạo timestamp
            $timestamp = time();

            // Chuẩn bị body parameters
            $bodyParams = $this->prepareSearchFilters($filters);

            // Tạo signature
            $signature = TikTokSignatureService::generateOrderSearchSignature(
                $integration->getAppKey(),
                $integration->getAppSecret(),
                (string) $timestamp,
                $bodyParams,
                $shop->getShopCipher(),
                $pageSize
            );

            // Chuẩn bị query parameters
            $queryParams = [
                'shop_cipher' => $shop->getShopCipher(),
                'app_key' => $integration->getAppKey(),
                'sign' => $signature,
                'timestamp' => $timestamp,
                'page_size' => $pageSize
            ];

            if ($pageToken) {
                $queryParams['page_token'] = $pageToken;
            }

            // Gọi API
            $response = $this->callSearchOrdersAPI($queryParams, $bodyParams, $integration);

            if ($response['success']) {
                Log::info('Fetch orders page successful', [
                    'shop_id' => $shop->id,
                    'orders_count' => count($response['data']['orders'] ?? $response['data']['order_list'] ?? []),
                    'has_more' => $response['data']['more'] ?? false
                ]);

                return [
                    'success' => true,
                    'data' => $response['data']
                ];
            } else {
                throw new Exception($response['message'] ?? 'Lỗi không xác định khi lấy đơn hàng');
            }
        } catch (Exception $e) {
            Log::error('Fetch orders page failed', [
                'shop_id' => $shop->id,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Sync một đơn hàng cụ thể từ TikTok API
     */
    public function syncSingleOrder(TikTokShop $shop, string $orderId): array
    {
        Log::info('Syncing single order', [
            'shop_id' => $shop->id,
            'order_id' => $orderId
        ]);

        try {
            // Gọi API để lấy thông tin đơn hàng cụ thể
            $result = $this->fetchOrdersPage($shop, [
                'order_id' => $orderId
            ], 1);

            if (!$result['success']) {
                return [
                    'success' => false,
                    'message' => $result['message']
                ];
            }

            $data = $result['data'];
            $orderList = $data['orders'] ?? $data['order_list'] ?? [];

            if (empty($orderList)) {
                Log::warning('Order not found in API response', [
                    'shop_id' => $shop->id,
                    'order_id' => $orderId
                ]);
                return [
                    'success' => false,
                    'message' => 'Order not found in TikTok API'
                ];
            }

            // Lưu đơn hàng vào database
            $this->saveOrdersToDatabase($data, $shop);

            Log::info('Single order synced successfully', [
                'shop_id' => $shop->id,
                'order_id' => $orderId
            ]);

            return [
                'success' => true,
                'message' => 'Order synced successfully'
            ];
        } catch (\Exception $e) {
            Log::error('Failed to sync single order', [
                'shop_id' => $shop->id,
                'order_id' => $orderId,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Tìm đơn hàng theo ID sử dụng GET /order/202507/orders với parameter ids
     */
    public function searchOrderById(TikTokShop $shop, string $orderId): array
    {
        Log::info('Searching order by ID using GET /order/202507/orders', [
            'shop_id' => $shop->id,
            'order_id' => $orderId
        ]);

        try {
            // Kiểm tra integration có hoạt động không
            $integration = $shop->integration;
            if (!$integration) {
                throw new Exception('TikTok Shop không có integration');
            }

            // Kiểm tra access token
            if ($integration->isAccessTokenExpired()) {
                $refreshResult = $integration->refreshAccessToken();
                if (!$refreshResult['success']) {
                    throw new Exception('Không thể refresh token: ' . $refreshResult['message']);
                }
            }

            $appKey = $integration->getAppKey();
            $appSecret = $integration->getAppSecret();
            $timestamp = time();
            $shopCipher = $shop->getShopCipher();

            // Kiểm tra shop_cipher
            if (empty($shopCipher)) {
                Log::error('Shop cipher is empty', [
                    'shop_id' => $shop->id,
                    'shop_name' => $shop->shop_name,
                    'cipher' => $shop->cipher,
                    'shop_cipher' => $shop->shop_cipher,
                    'shop_data' => $shop->shop_data
                ]);

                return [
                    'success' => false,
                    'message' => 'Shop cipher is empty',
                    'data' => null
                ];
            }

            // Tạo signature cho API GET /order/202507/orders
            $signature = TikTokSignatureService::generateOrderByIdsSignature(
                $appKey,
                $appSecret,
                $timestamp,
                $shopCipher,
                [$orderId]
            );

            $queryParams = [
                'app_key' => $appKey,
                'timestamp' => $timestamp,
                'shop_cipher' => $shopCipher,
                'sign' => $signature,
                'ids' => $orderId // Single order ID
            ];

            $url = 'https://open-api.tiktokglobalshop.com/order/202507/orders?' . http_build_query($queryParams);

            Log::info('Calling TikTok GET /order/202507/orders API', [
                'shop_id' => $shop->id,
                'order_id' => $orderId,
                'url' => $url,
                'query_params' => $queryParams,
                'access_token' => substr($integration->access_token, 0, 20) . '...'
            ]);

            // Gọi API với access token từ integration
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'x-tts-access-token' => $integration->access_token
            ])->timeout(30)->get($url);

            $httpCode = $response->status();
            $responseData = $response->json();

            Log::info('TikTok GET /order/202507/orders API Response', [
                'shop_id' => $shop->id,
                'order_id' => $orderId,
                'http_code' => $httpCode,
                'response' => $responseData
            ]);

            if ($httpCode === 200 && isset($responseData['code']) && $responseData['code'] === 0) {
                $orders = $responseData['data']['orders'] ?? [];

                if (!empty($orders)) {
                    Log::info('Order found successfully', [
                        'shop_id' => $shop->id,
                        'order_id' => $orderId,
                        'orders_count' => count($orders)
                    ]);

                    return [
                        'success' => true,
                        'data' => [
                            'orders' => $orders
                        ],
                        'message' => 'Order found successfully'
                    ];
                } else {
                    Log::warning('No orders found in response', [
                        'shop_id' => $shop->id,
                        'order_id' => $orderId,
                        'response_data' => $responseData['data'] ?? []
                    ]);

                    return [
                        'success' => false,
                        'message' => 'No orders found in response',
                        'data' => null
                    ];
                }
            } else {
                $errorMessage = $responseData['message'] ?? 'Unknown error';
                $errorCode = $responseData['code'] ?? 'Unknown code';

                Log::error('TikTok GET /order/202507/orders API Error', [
                    'shop_id' => $shop->id,
                    'order_id' => $orderId,
                    'http_code' => $httpCode,
                    'error_code' => $errorCode,
                    'error' => $errorMessage,
                    'response' => $responseData
                ]);

                return [
                    'success' => false,
                    'message' => "API Error ({$errorCode}): {$errorMessage}",
                    'data' => null
                ];
            }
        } catch (\Exception $e) {
            Log::error('Exception in searchOrderById', [
                'shop_id' => $shop->id,
                'order_id' => $orderId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'message' => 'Exception: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Refresh access token cho shop
     */
    private function refreshAccessToken(TikTokShop $shop): array
    {
        try {
            Log::info('Refreshing access token for shop', [
                'shop_id' => $shop->id,
                'shop_name' => $shop->shop_name
            ]);

            $appKey = config('tiktok-shop.app_key');
            $appSecret = config('tiktok-shop.app_secret');
            $timestamp = time();

            $params = [
                'app_key' => $appKey,
                'app_secret' => $appSecret,
                'grant_type' => 'refresh_token',
                'refresh_token' => $shop->refresh_token
            ];

            $response = Http::get(config('tiktok-shop.oauth.refresh_token_url'), $params);

            $httpCode = $response->status();
            $responseData = $response->json();

            Log::info('TikTok Token Refresh API Response', [
                'shop_id' => $shop->id,
                'http_code' => $httpCode,
                'response' => $responseData
            ]);

            if ($httpCode === 200 && isset($responseData['code']) && $responseData['code'] === 0) {
                $data = $responseData['data'];

                // Cập nhật token mới vào database
                $shop->update([
                    'access_token' => $data['access_token'],
                    'refresh_token' => $data['refresh_token'],
                    'expires_in' => $data['expires_in'],
                    'token_updated_at' => now()
                ]);

                Log::info('Access token refreshed successfully', [
                    'shop_id' => $shop->id,
                    'new_access_token' => substr($data['access_token'], 0, 20) . '...',
                    'expires_in' => $data['expires_in']
                ]);

                return [
                    'success' => true,
                    'message' => 'Token refreshed successfully',
                    'data' => $data
                ];
            } else {
                $errorMessage = $responseData['message'] ?? 'Unknown error';
                Log::error('TikTok Token Refresh API Error', [
                    'shop_id' => $shop->id,
                    'http_code' => $httpCode,
                    'error' => $errorMessage,
                    'response' => $responseData
                ]);

                return [
                    'success' => false,
                    'message' => $errorMessage,
                    'data' => null
                ];
            }
        } catch (\Exception $e) {
            Log::error('Exception during token refresh', [
                'shop_id' => $shop->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'message' => 'Exception: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Lấy đơn hàng theo trạng thái
     */
    public function getOrdersByStatus(TikTokShop $shop, string $status, int $limit = 50): array
    {
        return $this->searchOrders($shop, [
            'order_status' => $status
        ], $limit);
    }

    /**
     * Lấy đơn hàng theo khoảng thời gian
     */
    public function getOrdersByTimeRange(
        TikTokShop $shop,
        ?int $startTime = null,
        ?int $endTime = null,
        int $limit = 50
    ): array {
        $filters = [];

        if ($startTime) {
            $filters['create_time_ge'] = $startTime;
        }

        if ($endTime) {
            $filters['create_time_lt'] = $endTime;
        }

        return $this->searchOrders($shop, $filters, $limit);
    }

    /**
     * Lấy đơn hàng từ database (đã lưu)
     */
    public function getStoredOrders(TikTokShop $shop, array $filters = []): array
    {
        $query = TikTokOrder::where('tiktok_shop_id', $shop->id);

        // Áp dụng filters
        if (isset($filters['order_status'])) {
            $query->where('order_status', $filters['order_status']);
        }

        if (isset($filters['create_time_ge'])) {
            $query->where('create_time', '>=', date('Y-m-d H:i:s', $filters['create_time_ge']));
        }

        if (isset($filters['create_time_lt'])) {
            $query->where('create_time', '<=', date('Y-m-d H:i:s', $filters['create_time_lt']));
        }

        if (isset($filters['buyer_user_id'])) {
            $query->where('buyer_user_id', $filters['buyer_user_id']);
        }

        if (isset($filters['warehouse_id'])) {
            $query->where('warehouse_id', $filters['warehouse_id']);
        }

        $orders = $query->orderBy('create_time', 'DESC')
            ->limit($filters['limit'] ?? 50)
            ->get();

        return [
            'success' => true,
            'data' => $orders,
            'count' => $orders->count()
        ];
    }
}
