<?php

namespace App\Services;

use App\Models\TikTokShopIntegration;
use App\Services\TikTokSignatureService;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class TikTokShopService
{
    private const API_VERSION = '202309';

    // API Base URLs cho các region khác nhau
    private const REGION_API_URLS = [
        'global' => 'https://open-api.tiktokglobalshop.com',
        'uk' => 'https://open-api.tiktokglobalshop.com',
        'us' => 'https://open-api.tiktokglobalshop.com',
        'sg' => 'https://open-api.tiktokglobalshop.com',
        'my' => 'https://open-api.tiktokglobalshop.com',
        'th' => 'https://open-api.tiktokglobalshop.com',
        'vn' => 'https://open-api.tiktokglobalshop.com', // Vietnam
        'ph' => 'https://open-api.tiktokglobalshop.com',
        'id' => 'https://open-api.tiktokglobalshop.com',
        'cn' => 'https://open-api.tiktokglobalshop.com', // China
    ];

    private const API_BASE_URL = 'https://open-api.tiktokglobalshop.com';

    /**
     * Get API base URL for specific region
     */
    private function getApiBaseUrl(string $region = 'global'): string
    {
        return self::REGION_API_URLS[$region] ?? self::API_BASE_URL;
    }

    /**
     * Detect region from shop data
     */
    private function detectShopRegion($shop): string
    {
        if (!$shop || !$shop->shop_data) {
            return 'global';
        }

        $shopData = $shop->shop_data;

        // Kiểm tra seller_region hoặc region từ shop data
        if (isset($shopData['seller_region'])) {
            $region = strtolower($shopData['seller_region']);
            if (array_key_exists($region, self::REGION_API_URLS)) {
                return $region;
            }
        }

        // Kiểm tra các trường khác có thể chứa thông tin region
        if (isset($shopData['region'])) {
            $region = strtolower($shopData['region']);
            if (array_key_exists($region, self::REGION_API_URLS)) {
                return $region;
            }
        }

        return 'global';
    }

    /**
     * Get access token using authorization code
     */
    public function getAccessToken(TikTokShopIntegration $integration, string $authCode): array
    {
        Log::info('=== START GET ACCESS TOKEN ===');
        Log::info('Integration info:', [
            'integration_id' => $integration->id,
            'app_key' => $integration->getAppKey(),
            'auth_code_length' => strlen($authCode)
        ]);

        try {
            // Sử dụng GET request với query parameters như trong ví dụ
            $url = 'https://auth.tiktok-shops.com/api/v2/token/get';
            $params = [
                'app_key' => $integration->getAppKey(),
                'app_secret' => $integration->getAppSecret(),
                'auth_code' => $authCode,
                'grant_type' => 'authorized_code',
            ];

            Log::info('Making API request:', [
                'url' => $url,
                'params' => array_merge($params, ['app_secret' => '***HIDDEN***', 'auth_code' => '***HIDDEN***'])
            ]);

            $response = Http::get($url, $params);
            $data = $response->json();

            Log::info('=== TIKTOK ACCESS TOKEN API RESPONSE ===', [
                'status_code' => $response->status(),
                'successful' => $response->successful(),
                'response_headers' => $response->headers(),
                'response_body' => $data,
                'response_body_json' => json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                'url_used' => $url,
                'request_params' => array_merge($params, ['app_secret' => '***HIDDEN***', 'auth_code' => '***HIDDEN***'])
            ]);

            if ($response->successful()) {
                // Kiểm tra response theo format thực tế của TikTok Shop API
                if (isset($data['code']) && $data['code'] === 0) {
                    Log::info('Access token obtained successfully');

                    $tokenData = $data['data'] ?? [];

                    // Lưu s_token nếu có và user_type = 0
                    if (isset($tokenData['user_type']) && $tokenData['user_type'] === 0 && isset($tokenData['s_token'])) {
                        Log::info('Found s_token for user_type = 0', [
                            'user_type' => $tokenData['user_type'],
                            's_token_length' => strlen($tokenData['s_token'])
                        ]);
                    }

                    return [
                        'success' => true,
                        'data' => $tokenData
                    ];
                } else {
                    $errorCode = $data['code'] ?? 'UNKNOWN';
                    $errorMessage = $data['message'] ?? 'Không thể lấy access token';

                    Log::error("TikTok Shop Get Access Token Error - Code: {$errorCode}, Message: {$errorMessage}");

                    // Handle specific error codes
                    if ($errorCode === 36004004) {
                        return [
                            'success' => false,
                            'error' => 'Authorization code không hợp lệ hoặc đã hết hạn. Vui lòng lấy code mới từ seller và thử lại.'
                        ];
                    }

                    return [
                        'success' => false,
                        'error' => "Lỗi {$errorCode}: {$errorMessage}"
                    ];
                }
            }

            Log::error('API request failed', ['status' => $response->status(), 'body' => $response->body()]);
            return [
                'success' => false,
                'error' => 'Lỗi kết nối API: ' . $response->status()
            ];
        } catch (Exception $e) {
            Log::error('TikTok Shop API Error - Get Access Token: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            return [
                'success' => false,
                'error' => 'Lỗi hệ thống: ' . $e->getMessage()
            ];
        }

        Log::info('=== END GET ACCESS TOKEN ===');
    }

    /**
     * Generate signature for TikTok Shop API calls
     * Following the official TikTok Shop API signature algorithm
     */
    private function generateSignature(string $appKey, string $appSecret, string $apiPath, array $queryParams = [], array $bodyParams = [], ?string $contentType = null): string
    {
        // Step 1: Loại bỏ sign và access_token, sắp xếp query parameters theo thứ tự bảng chữ cái
        $filteredParams = array_filter($queryParams, function ($key) {
            return !in_array($key, ['sign', 'access_token']);
        }, ARRAY_FILTER_USE_KEY);
        ksort($filteredParams);

        // Step 2: Nối các tham số theo định dạng {key}{value}
        $paramString = '';
        foreach ($filteredParams as $key => $value) {
            $paramString .= $key . $value;
        }

        // Step 3: Nối đường dẫn API vào chuỗi
        $input = $apiPath . $paramString;

        // Step 4: Nếu Content-Type không phải multipart/form-data và body tồn tại, nối body
        if ($contentType !== 'multipart/form-data' && !empty($bodyParams)) {
            $bodyString = json_encode($bodyParams, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            $input .= $bodyString;
        }

        // Step 5: Wrap chuỗi với app_secret (app_secret + input + app_secret)
        $stringToSign = $appSecret . $input . $appSecret;

        // Step 6: Tạo chữ ký HMAC-SHA256 và trả về dưới dạng hex string
        // Sử dụng app_secret làm key cho HMAC
        $signature = hash_hmac('sha256', $stringToSign, $appSecret, true);
        $hexSignature = bin2hex($signature);

        // Debug logging
        Log::info('Signature generation details', [
            'api_path' => $apiPath,
            'query_params' => $queryParams,
            'filtered_params' => $filteredParams,
            'param_string' => $paramString,
            'input' => $input,
            'string_to_sign' => $stringToSign,
            'signature_hex' => $hexSignature,
            'app_key' => $appKey,
            'app_secret_length' => strlen($appSecret)
        ]);

        return $hexSignature;
    }

    /**
     * Get authorized shops using new API with signature
     */
    public function getAuthorizedShops(TikTokShopIntegration $integration): array
    {
        try {
            if (!$integration->access_token) {
                return [
                    'success' => false,
                    'error' => 'Access token không tồn tại'
                ];
            }

            // Check if token is expired
            if ($integration->isAccessTokenExpired()) {
                $refreshResult = $this->refreshAccessToken($integration);
                if (!$refreshResult['success']) {
                    return [
                        'success' => false,
                        'error' => 'Access token đã hết hạn và không thể refresh: ' . $refreshResult['error']
                    ];
                }
                $integration->updateTokens($refreshResult['data']);
            }

            // Lấy timestamp hiện tại (UTC)
            $timestamp = time();
            $appKey = $integration->getAppKey();
            $appSecret = $integration->getAppSecret();
            $accessToken = $integration->access_token;

            // Generate signature với timestamp hiện tại
            $apiPath = '/authorization/' . self::API_VERSION . '/shops';
            $queryParams = [
                'app_key' => $appKey,
                'timestamp' => $timestamp,
            ];
            $contentType = 'application/json';
            $sign = $this->generateSignature($appKey, $appSecret, $apiPath, $queryParams, [], $contentType);

            $url = self::API_BASE_URL . $apiPath;
            $params = [
                'app_key' => $appKey,
                'sign' => $sign,
                'timestamp' => $timestamp,
            ];

            $headers = [
                'Content-Type' => $contentType,
                'x-tts-access-token' => $accessToken,
            ];

            Log::info('TikTok Shop API Request', [
                'url' => $url,
                'params' => $params,
                'headers' => array_merge($headers, ['x-tts-access-token' => '***HIDDEN***']),
                'api_path' => $apiPath,
                'query_params' => $queryParams,
                'signature' => $sign
            ]);

            $response = Http::withHeaders($headers)->get($url, $params);
            $data = $response->json();

            Log::info('TikTok Shop API Response', [
                'status' => $response->status(),
                'data' => $data
            ]);

            if ($response->successful()) {
                if (isset($data['code']) && $data['code'] === 0) {
                    return [
                        'success' => true,
                        'data' => $data['data'] ?? []
                    ];
                } else {
                    $errorCode = $data['code'] ?? 'UNKNOWN';
                    $errorMessage = $data['message'] ?? 'Không thể lấy danh sách shop';

                    Log::error("TikTok Shop Get Authorized Shops Error - Code: {$errorCode}, Message: {$errorMessage}");

                    if ($errorCode === 106001) {
                        return [
                            'success' => false,
                            'error' => 'Lỗi xác thực (106001): Access token không hợp lệ hoặc đã hết hạn. Vui lòng thử kết nối lại.'
                        ];
                    }

                    return [
                        'success' => false,
                        'error' => "Lỗi {$errorCode}: {$errorMessage}"
                    ];
                }
            }

            if ($response->status() === 401) {
                return [
                    'success' => false,
                    'error' => 'Lỗi xác thực (401): Access token không hợp lệ hoặc đã hết hạn. Vui lòng thử kết nối lại.'
                ];
            }

            return [
                'success' => false,
                'error' => 'Lỗi kết nối API: ' . $response->status() . ' - ' . $response->body()
            ];
        } catch (Exception $e) {
            Log::error('TikTok Shop API Error - Get Authorized Shops: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Lỗi hệ thống: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get shop information for specific shop
     */
    public function getShopInfo(TikTokShopIntegration $integration, string $shopId = null): array
    {
        try {
            if (!$integration->access_token) {
                return [
                    'success' => false,
                    'error' => 'Access token không tồn tại'
                ];
            }

            // Check if token is expired
            if ($integration->isAccessTokenExpired()) {
                $refreshResult = $this->refreshAccessToken($integration);
                if (!$refreshResult['success']) {
                    return [
                        'success' => false,
                        'error' => 'Access token đã hết hạn và không thể refresh: ' . $refreshResult['error']
                    ];
                }
                $integration->updateTokens($refreshResult['data']);
            }

            $timestamp = time();
            $appKey = $integration->getAppKey();
            $appSecret = $integration->getAppSecret();
            $accessToken = $integration->access_token;

            // Generate signature
            $apiPath = '/shop/get_authorized_shop';
            $queryParams = [
                'app_key' => $appKey,
                'timestamp' => $timestamp,
            ];

            if ($shopId) {
                $queryParams['shop_id'] = $shopId;
            }

            $sign = $this->generateSignature($appKey, $appSecret, $apiPath, $queryParams);

            $url = self::API_BASE_URL . $apiPath;
            $params = array_merge($queryParams, ['sign' => $sign]);

            $headers = [
                'Content-Type' => 'application/json',
                'x-tts-access-token' => $accessToken,
            ];

            $response = Http::withHeaders($headers)->get($url, $params);
            $data = $response->json();

            if ($response->successful()) {
                if (isset($data['code']) && $data['code'] === 0) {
                    return [
                        'success' => true,
                        'data' => $data['data'] ?? []
                    ];
                } else {
                    $errorCode = $data['code'] ?? 'UNKNOWN';
                    $errorMessage = $data['message'] ?? 'Không thể lấy thông tin shop';

                    return [
                        'success' => false,
                        'error' => "Lỗi {$errorCode}: {$errorMessage}"
                    ];
                }
            }

            return [
                'success' => false,
                'error' => 'Lỗi kết nối API: ' . $response->status()
            ];
        } catch (Exception $e) {
            Log::error('TikTok Shop API Error - Get Shop Info: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Lỗi hệ thống: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get products list for specific shop
     */
    public function getProducts(TikTokShopIntegration $integration, array $params = [], string $shopId = null): array
    {
        $defaultParams = [
            'page_size' => 20,
            'page_number' => 1,
        ];

        if ($shopId) {
            $defaultParams['shop_id'] = $shopId;
        }

        return $this->makeApiCall($integration, 'GET', '/product/get_products', array_merge($defaultParams, $params));
    }

    /**
     * Get orders list for specific shop
     */
    public function getOrders(TikTokShopIntegration $integration, array $params = [], string $shopId = null): array
    {
        $defaultParams = [
            'page_size' => 20,
            'page_number' => 1,
        ];

        if ($shopId) {
            $defaultParams['shop_id'] = $shopId;
        }

        return $this->makeApiCall($integration, 'GET', '/order/get_order_list', array_merge($defaultParams, $params));
    }

    /**
     * Get order details for specific shop
     */
    public function getOrderDetails(TikTokShopIntegration $integration, string $orderId, string $shopId = null): array
    {
        $params = ['order_id' => $orderId];

        if ($shopId) {
            $params['shop_id'] = $shopId;
        }

        return $this->makeApiCall($integration, 'GET', '/order/get_order_detail', $params);
    }

    /**
     * Make API call with authentication (legacy method)
     */
    private function makeApiCall(TikTokShopIntegration $integration, string $method, string $endpoint, array $params = []): array
    {
        try {
            // Check if token needs refresh
            if ($integration->isAccessTokenExpired()) {
                $refreshResult = $this->refreshAccessToken($integration);
                if (!$refreshResult['success']) {
                    $integration->markAsError($refreshResult['error']);
                    return $refreshResult;
                }

                $integration->updateTokens($refreshResult['data']);
            }

            $headers = [
                'Content-Type' => 'application/json',
                'Access-Token' => $integration->access_token,
                'app-key' => $integration->getAppKey(),
            ];

            // Add shop-id header if provided in params
            if (isset($params['shop_id'])) {
                $headers['shop-id'] = $params['shop_id'];
            }

            $url = 'https://auth.tiktok-shops.com/api/v2' . $endpoint;

            if ($method === 'GET') {
                $response = Http::withHeaders($headers)->get($url, $params);
            } else {
                $response = Http::withHeaders($headers)->post($url, $params);
            }

            if ($response->successful()) {
                $data = $response->json();

                if (isset($data['code']) && $data['code'] === 0) {
                    return [
                        'success' => true,
                        'data' => $data['data'] ?? []
                    ];
                } else {
                    // Handle specific error codes
                    if (isset($data['code']) && $data['code'] === 10008) {
                        // Token expired, try to refresh
                        $refreshResult = $this->refreshAccessToken($integration);
                        if ($refreshResult['success']) {
                            $integration->updateTokens($refreshResult['data']);
                            // Retry the original request
                            return $this->makeApiCall($integration, $method, $endpoint, $params);
                        }
                    }

                    return [
                        'success' => false,
                        'error' => $data['message'] ?? 'API call failed',
                        'code' => $data['code'] ?? null
                    ];
                }
            }

            return [
                'success' => false,
                'error' => 'Lỗi kết nối API: ' . $response->status()
            ];
        } catch (Exception $e) {
            Log::error('TikTok Shop API Error - ' . $endpoint . ': ' . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Lỗi hệ thống: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Refresh access token
     */
    public function refreshAccessToken(TikTokShopIntegration $integration): array
    {
        try {
            // Sử dụng GET request với query parameters
            $url = 'https://auth.tiktok-shops.com/api/v2/token/refresh';
            $params = [
                'app_key' => $integration->getAppKey(),
                'app_secret' => $integration->getAppSecret(),
                'refresh_token' => $integration->refresh_token,
                'grant_type' => 'refresh_token',
            ];

            $response = Http::get($url, $params);
            $data = $response->json();

            if ($response->successful()) {
                if (isset($data['code']) && $data['code'] === 0) {
                    $tokenData = $data['data'] ?? [];

                    // Log s_token nếu có trong refresh response
                    if (isset($tokenData['s_token'])) {
                        Log::info('Found s_token in refresh response', [
                            'user_type' => $tokenData['user_type'] ?? 'unknown',
                            's_token_length' => strlen($tokenData['s_token'])
                        ]);
                    }

                    return [
                        'success' => true,
                        'data' => $tokenData
                    ];
                } else {
                    $errorCode = $data['code'] ?? 'UNKNOWN';
                    $errorMessage = $data['message'] ?? 'Không thể refresh token';

                    Log::error("TikTok Shop Refresh Token Error - Code: {$errorCode}, Message: {$errorMessage}");

                    return [
                        'success' => false,
                        'error' => "Lỗi {$errorCode}: {$errorMessage}"
                    ];
                }
            }

            return [
                'success' => false,
                'error' => 'Lỗi kết nối API: ' . $response->status()
            ];
        } catch (Exception $e) {
            Log::error('TikTok Shop API Error - Refresh Token: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Lỗi hệ thống: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Validate app credentials
     */
    public function validateCredentials(string $appKey, string $appSecret): array
    {
        try {
            // Sử dụng GET request với query parameters như các method khác
            $url = 'https://auth.tiktok-shops.com/api/v2/token/get';
            $params = [
                'app_key' => $appKey,
                'app_secret' => $appSecret,
                'auth_code' => 'test_code',
                'grant_type' => 'authorized_code',
            ];

            $response = Http::get($url, $params);
            $data = $response->json();

            // Nếu nhận được lỗi 10004 (invalid auth_code), có nghĩa là credentials hợp lệ
            if (isset($data['code']) && $data['code'] === 10004) {
                return [
                    'success' => true,
                    'message' => 'Thông tin ứng dụng hợp lệ'
                ];
            }

            // Nếu nhận được lỗi 10001 (invalid app_key/app_secret), credentials không hợp lệ
            if (isset($data['code']) && $data['code'] === 10001) {
                return [
                    'success' => false,
                    'error' => 'App Key hoặc App Secret không đúng'
                ];
            }

            // Nếu nhận được lỗi khác, có thể credentials không hợp lệ
            if (isset($data['code']) && $data['code'] !== 0) {
                return [
                    'success' => false,
                    'error' => 'Thông tin ứng dụng không hợp lệ: ' . ($data['message'] ?? 'Lỗi không xác định')
                ];
            }

            // Nếu không có lỗi, credentials có thể hợp lệ
            return [
                'success' => true,
                'message' => 'Thông tin ứng dụng hợp lệ'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'Lỗi kiểm tra thông tin ứng dụng: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get authorization URL for a team
     */
    public function getAuthorizationUrl(int $teamId): string
    {
        $integration = TikTokShopIntegration::where('team_id', $teamId)->first();

        if (!$integration) {
            throw new Exception('Chưa có tích hợp TikTok Shop cho team này');
        }

        return $integration->getAuthorizationUrl();
    }

    /**
     * Get OAuth flow information for debugging
     */
    public function getOAuthFlowInfo(TikTokShopIntegration $integration): array
    {
        return [
            'authorization_url' => $integration->getAuthorizationUrl(),
            'callback_url' => route('team.tiktok-shop.callback'),
            'api_endpoint' => 'https://auth.tiktok-shops.com/api/v2/token/get',
            'required_params' => [
                'app_key' => $integration->getAppKey(),
                'app_secret' => '[HIDDEN]',
                'auth_code' => '[FROM_CALLBACK]',
                'grant_type' => 'authorized_code'
            ],
            'example_request' => "GET https://auth.tiktok-shops.com/api/v2/token/get?app_key={$integration->getAppKey()}&app_secret=[HIDDEN]&auth_code=[AUTH_CODE]&grant_type=authorized_code",
            'flow_steps' => [
                '1. Khách hàng click "Kết nối TikTok Shop"',
                '2. Hệ thống redirect đến: ' . $integration->getAuthorizationUrl(),
                '3. Khách hàng đăng nhập TikTok Shop và đồng ý quyền',
                '4. TikTok redirect về: ' . route('team.tiktok-shop.callback') . '?code=[AUTH_CODE]&state=[STATE]',
                '5. Hệ thống gọi API: https://auth.tiktok-shops.com/api/v2/token/get với auth_code',
                '6. Nhận access_token và refresh_token từ TikTok'
            ]
        ];
    }

    /**
     * Generate signature for testing (public method)
     */
    public function generateSignatureForTest(string $appKey, string $appSecret, string $apiPath, array $queryParams = [], array $bodyParams = [], ?string $contentType = null): string
    {
        return $this->generateSignature($appKey, $appSecret, $apiPath, $queryParams, $bodyParams, $contentType);
    }

    /**
     * Test signature generation with example from documentation
     */
    public function testSignatureGeneration(): array
    {
        // Test với ví dụ từ hướng dẫn
        $appKey = '29a39d';
        $appSecret = 'e59af819cc';
        $apiPath = '/authorization/' . self::API_VERSION . '/shops';
        $queryParams = [
            'app_key' => $appKey,
            'timestamp' => (string)time()
        ];

        $sign = $this->generateSignature($appKey, $appSecret, $apiPath, $queryParams);

        // Chữ ký mong đợi từ ví dụ curl
        $expectedSign = 'bc721f0e0182914e3487b81df204de37a352fc3aa96947efda6dc1e5dd0d5290';

        $isCorrect = ($sign === $expectedSign);

        return [
            'success' => $isCorrect,
            'generated_sign' => $sign,
            'expected_sign' => $expectedSign,
            'is_correct' => $isCorrect,
            'test_params' => [
                'app_key' => $appKey,
                'app_secret' => $appSecret,
                'api_path' => $apiPath,
                'query_params' => $queryParams
            ]
        ];
    }

    /**
     * Simple validation without API call
     */
    public function simpleValidateCredentials(string $appKey, string $appSecret): array
    {
        // Basic validation
        if (empty($appKey) || empty($appSecret)) {
            return [
                'success' => false,
                'error' => 'App Key và App Secret không được để trống'
            ];
        }

        if (strlen($appKey) < 10 || strlen($appSecret) < 10) {
            return [
                'success' => false,
                'error' => 'App Key và App Secret phải có ít nhất 10 ký tự'
            ];
        }

        // Check format (basic)
        if (!preg_match('/^[a-zA-Z0-9_-]+$/', $appKey)) {
            return [
                'success' => false,
                'error' => 'App Key chỉ được chứa chữ cái, số, dấu gạch ngang và dấu gạch dưới'
            ];
        }

        return [
            'success' => true,
            'message' => 'Thông tin ứng dụng có format hợp lệ'
        ];
    }

    /**
     * Test authorization code with specific app credentials
     */
    public function testAuthorizationCode(string $appKey, string $appSecret, string $authCode): array
    {
        try {
            $url = 'https://auth.tiktok-shops.com/api/v2/token/get';
            $params = [
                'app_key' => $appKey,
                'app_secret' => $appSecret,
                'auth_code' => $authCode,
                'grant_type' => 'authorized_code',
            ];

            $response = Http::get($url, $params);
            $data = $response->json();

            if ($response->successful()) {
                if (isset($data['code']) && $data['code'] === 0) {
                    return [
                        'success' => true,
                        'message' => 'Authorization code hợp lệ!',
                        'data' => $data['data'] ?? []
                    ];
                } else {
                    $errorCode = $data['code'] ?? 'UNKNOWN';
                    $errorMessage = $data['message'] ?? 'Không thể lấy access token';

                    return [
                        'success' => false,
                        'error' => "Lỗi {$errorCode}: {$errorMessage}",
                        'code' => $errorCode
                    ];
                }
            }

            return [
                'success' => false,
                'error' => 'Lỗi kết nối API: ' . $response->status()
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'Lỗi hệ thống: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get product categories from TikTok Shop API
     */
    public function getCategories(TikTokShopIntegration $integration): array
    {
        try {
            if (!$integration->access_token) {
                return [
                    'success' => false,
                    'error' => 'Access token không tồn tại'
                ];
            }

            // Check if token is expired
            if ($integration->isAccessTokenExpired()) {
                $refreshResult = $this->refreshAccessToken($integration);
                if (!$refreshResult['success']) {
                    return [
                        'success' => false,
                        'error' => 'Access token đã hết hạn và không thể refresh: ' . $refreshResult['error']
                    ];
                }
                $integration->updateTokens($refreshResult['data']);
            }

            // Sử dụng timestamp hiện tại (UTC) - phải nằm trong vòng 5 phút
            $timestamp = time();
            Log::info('Using current timestamp', [
                'timestamp' => $timestamp,
                'timestamp_human' => date('Y-m-d H:i:s', $timestamp),
                'timestamp_utc' => gmdate('Y-m-d H:i:s', $timestamp)
            ]);
            $appKey = $integration->getAppKey();
            $appSecret = $integration->getAppSecret();
            $accessToken = $integration->access_token;

            // Lấy shop đầu tiên của integration này
            $shop = $integration->shops()->first();
            if (!$shop) {
                return [
                    'success' => false,
                    'error' => 'Không tìm thấy shop nào cho integration này. Vui lòng kết nối shop trước khi sync categories.'
                ];
            }

            // Sử dụng cipher từ shop data hoặc shop_id
            $shopCipher = $shop->shop_data['cipher'] ?? $shop->shop_data['shop_cipher'] ?? $shop->shop_id;

            // Detect region từ shop data
            $region = $this->detectShopRegion($shop);
            Log::info('Detected shop region', ['region' => $region, 'shop_id' => $shop->shop_id]);

            // Generate signature với tất cả parameters cần thiết
            $apiPath = '/product/' . self::API_VERSION . '/categories';
            $queryParams = [
                'app_key' => $appKey,
                'timestamp' => $timestamp,
                'shop_cipher' => $shopCipher,
                'category_version' => 'v1',
                'include_prohibited_categories' => 'false',
                'listing_platform' => 'TIKTOK_SHOP',
                'locale' => 'en-US',
                'keyword' => '',
            ];

            // Log trước khi tạo signature
            Log::info('=== BEFORE SIGNATURE GENERATION ===', [
                'api_path' => $apiPath,
                'query_params' => $queryParams,
                'app_key' => $appKey,
                'app_secret_length' => strlen($appSecret),
                'timestamp' => $timestamp,
                'timestamp_human' => date('Y-m-d H:i:s', $timestamp),
                'timestamp_utc' => gmdate('Y-m-d H:i:s', $timestamp),
                'server_timezone' => date_default_timezone_get(),
                'shop_cipher' => $shopCipher,
                'shop_region' => $region
            ]);

            $sign = $this->generateSignature($appKey, $appSecret, $apiPath, $queryParams);

            // Log sau khi tạo signature
            Log::info('=== AFTER SIGNATURE GENERATION ===', [
                'signature' => $sign,
                'signature_length' => strlen($sign)
            ]);

            // Sử dụng URL cho region được detect
            $url = $this->getApiBaseUrl($region) . $apiPath;
            $params = array_merge($queryParams, ['sign' => $sign]);

            // Thêm s_token nếu có trong access token data
            if (isset($integration->additional_data['s_token'])) {
                $params['s_token'] = $integration->additional_data['s_token'];
                Log::info('Added s_token to request', ['s_token' => substr($integration->additional_data['s_token'], 0, 10) . '...']);
            }

            $headers = [
                'Content-Type' => 'application/json',
                'x-tts-access-token' => $accessToken,
            ];

            // Log chi tiết request cuối cùng
            Log::info('=== FINAL REQUEST DETAILS ===', [
                'url' => $url,
                'params' => $params,
                'headers' => array_merge($headers, ['x-tts-access-token' => '***HIDDEN***']),
                'api_path' => $apiPath,
                'query_params' => $queryParams,
                'signature' => $sign,
                'region' => $region,
                'access_token_length' => strlen($accessToken)
            ]);

            $response = Http::withHeaders($headers)->get($url, $params);
            $data = $response->json();

            Log::info('TikTok Shop API Response - Get Categories', [
                'status' => $response->status(),
                'data' => $data,
                'url_used' => $url
            ]);

            // Nếu gặp lỗi 106008 (traffic go to wrong place), thử các region khác
            if (isset($data['code']) && $data['code'] === 106008) {
                Log::info('Got 106008 error, trying different regions');

                // Thử các region khác nhau
                $regionsToTry = ['global', 'vn', 'cn', 'sg', 'uk'];
                foreach ($regionsToTry as $tryRegion) {
                    if ($tryRegion === $region) continue; // Skip region đã thử

                    Log::info("Trying region: {$tryRegion}");
                    $tryUrl = $this->getApiBaseUrl($tryRegion) . $apiPath;
                    $tryResponse = Http::withHeaders($headers)->get($tryUrl, $params);
                    $tryData = $tryResponse->json();

                    Log::info("TikTok Shop API Response - Get Categories (Region: {$tryRegion})", [
                        'status' => $tryResponse->status(),
                        'data' => $tryData,
                        'url_used' => $tryUrl
                    ]);

                    // Nếu thành công, sử dụng response này
                    if ($tryResponse->successful() && isset($tryData['code']) && $tryData['code'] === 0) {
                        $response = $tryResponse;
                        $data = $tryData;
                        Log::info("Successfully got categories using region: {$tryRegion}");
                        break;
                    }
                }
            }

            if ($response->successful()) {
                if (isset($data['code']) && $data['code'] === 0) {
                    $categories = $data['data']['categories'] ?? [];

                    // Log chi tiết categories nhận được từ TikTok API
                    Log::info('TikTok Shop Categories received from API', [
                        'total_categories' => count($categories),
                        'categories_sample' => array_slice($categories, 0, 10), // Log 10 categories đầu tiên
                        'categories_structure' => [
                            'sample_keys' => !empty($categories) ? array_keys($categories[0]) : [],
                            'sample_values' => !empty($categories) ? array_values($categories[0]) : []
                        ]
                    ]);

                    // Log tất cả categories nếu có ít hơn 50
                    if (count($categories) <= 50) {
                        Log::info('All TikTok categories received', [
                            'categories' => $categories
                        ]);
                    } else {
                        // Log categories theo nhóm nếu có nhiều
                        $chunks = array_chunk($categories, 50);
                        foreach ($chunks as $index => $chunk) {
                            Log::info("TikTok categories chunk " . ($index + 1), [
                                'chunk_number' => $index + 1,
                                'total_chunks' => count($chunks),
                                'categories_in_chunk' => count($chunk),
                                'categories' => $chunk
                            ]);
                        }
                    }

                    // Transform categories to a more usable format
                    $formattedCategories = [];
                    foreach ($categories as $category) {
                        // TikTok Shop API sử dụng 'local_name' thay vì 'name'
                        $categoryName = $category['local_name'] ?? $category['name'] ?? 'Unknown Category';
                        $formattedCategories[$category['id']] = $categoryName;
                    }

                    // Log formatted categories
                    Log::info('TikTok categories formatted for use', [
                        'formatted_count' => count($formattedCategories),
                        'formatted_sample' => array_slice($formattedCategories, 0, 10, true)
                    ]);

                    return [
                        'success' => true,
                        'data' => $formattedCategories,
                        'raw_data' => $categories
                    ];
                } else {
                    $errorCode = $data['code'] ?? 'UNKNOWN';
                    $errorMessage = $data['message'] ?? 'Không thể lấy danh sách categories';

                    Log::error("TikTok Shop Get Categories Error - Code: {$errorCode}, Message: {$errorMessage}");

                    // Handle specific error codes
                    if ($errorCode === 106001) {
                        return [
                            'success' => false,
                            'error' => 'Lỗi xác thực (106001): Access token không hợp lệ hoặc đã hết hạn. Vui lòng thử kết nối lại.'
                        ];
                    }

                    return [
                        'success' => false,
                        'error' => "Lỗi {$errorCode}: {$errorMessage}"
                    ];
                }
            }

            if ($response->status() === 401) {
                return [
                    'success' => false,
                    'error' => 'Lỗi xác thực (401): Access token không hợp lệ hoặc đã hết hạn. Vui lòng thử kết nối lại.'
                ];
            }

            return [
                'success' => false,
                'error' => 'Lỗi kết nối API: ' . $response->status() . ' - ' . $response->body()
            ];
        } catch (Exception $e) {
            Log::error('TikTok Shop API Error - Get Categories: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Lỗi hệ thống: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get category attributes from TikTok Shop API
     */
    public function getCategoryAttributes(TikTokShopIntegration $integration, string $categoryId, string $locale = 'en-US'): array
    {
        Log::info('=== START GET CATEGORY ATTRIBUTES ===');
        Log::info('Request info:', [
            'integration_id' => $integration->id,
            'category_id' => $categoryId,
            'locale' => $locale
        ]);

        try {
            if (!$integration->access_token) {
                return [
                    'success' => false,
                    'error' => 'Access token không tồn tại'
                ];
            }

            // Check if token is expired
            if ($integration->isAccessTokenExpired()) {
                $refreshResult = $this->refreshAccessToken($integration);
                if (!$refreshResult['success']) {
                    return [
                        'success' => false,
                        'error' => 'Access token đã hết hạn và không thể refresh: ' . $refreshResult['error']
                    ];
                }
                $integration->updateTokens($refreshResult['data']);
            }

            // Lấy shop đầu tiên của integration này
            $shop = $integration->shops()->first();
            if (!$shop) {
                return [
                    'success' => false,
                    'error' => 'Không tìm thấy shop nào cho integration này'
                ];
            }

            // Sử dụng cipher từ shop data hoặc shop_id
            $shopCipher = $shop->shop_data['cipher'] ?? $shop->shop_data['shop_cipher'] ?? $shop->shop_id;

            // Detect region từ shop data
            $region = $this->detectShopRegion($shop);
            Log::info('Detected shop region', ['region' => $region, 'shop_id' => $shop->shop_id]);

            // Sử dụng timestamp hiện tại (UTC) - phải nằm trong vòng 5 phút
            $timestamp = time();
            $appKey = $integration->getAppKey();
            $appSecret = $integration->getAppSecret();
            $accessToken = $integration->access_token;

            // Generate signature với tất cả parameters cần thiết
            $apiPath = "/product/" . self::API_VERSION . "/categories/{$categoryId}/attributes";
            $queryParams = [
                'app_key' => $appKey,
                'timestamp' => $timestamp,
                'shop_cipher' => $shopCipher,
                'category_version' => 'v1',
                'locale' => $locale
            ];

            Log::info('=== BEFORE SIGNATURE GENERATION ===', [
                'api_path' => $apiPath,
                'query_params' => $queryParams,
                'app_key' => $appKey,
                'app_secret_length' => strlen($appSecret),
                'timestamp' => $timestamp,
                'timestamp_human' => date('Y-m-d H:i:s', $timestamp),
                'timestamp_utc' => gmdate('Y-m-d H:i:s', $timestamp),
                'server_timezone' => date_default_timezone_get(),
                'shop_cipher' => $shopCipher,
                'shop_region' => $region
            ]);

            $sign = $this->generateSignature($appKey, $appSecret, $apiPath, $queryParams);

            Log::info('=== AFTER SIGNATURE GENERATION ===', [
                'signature' => $sign,
                'signature_length' => strlen($sign)
            ]);

            // Sử dụng URL cho region được detect
            $url = $this->getApiBaseUrl($region) . $apiPath;
            $params = array_merge($queryParams, ['sign' => $sign]);

            $headers = [
                'Content-Type' => 'application/json',
                'x-tts-access-token' => $accessToken,
            ];

            Log::info('=== FINAL REQUEST DETAILS ===', [
                'url' => $url,
                'params' => $params,
                'headers' => array_merge($headers, ['x-tts-access-token' => '***HIDDEN***']),
                'api_path' => $apiPath,
                'query_params' => $queryParams,
                'signature' => $sign,
                'region' => $region,
                'access_token_length' => strlen($accessToken)
            ]);

            $response = Http::withHeaders($headers)->get($url, $params);
            $data = $response->json();

            Log::info('TikTok Shop API Response - Get Category Attributes', [
                'status' => $response->status(),
                'data' => $data,
                'url_used' => $url
            ]);

            if ($response->successful()) {
                if (isset($data['code']) && $data['code'] === 0) {
                    $attributes = $data['data']['attributes'] ?? [];

                    Log::info('Category attributes retrieved successfully', [
                        'category_id' => $categoryId,
                        'total_attributes' => count($attributes),
                        'sample_attributes' => array_slice($attributes, 0, 3)
                    ]);

                    return [
                        'success' => true,
                        'data' => $attributes,
                        'request_id' => $data['request_id'] ?? null
                    ];
                } else {
                    $errorCode = $data['code'] ?? 'UNKNOWN';
                    $errorMessage = $data['message'] ?? 'Không thể lấy attributes của category';

                    Log::error("TikTok Shop Get Category Attributes Error - Code: {$errorCode}, Message: {$errorMessage}");

                    return [
                        'success' => false,
                        'error' => "Lỗi {$errorCode}: {$errorMessage}"
                    ];
                }
            }

            if ($response->status() === 401) {
                return [
                    'success' => false,
                    'error' => 'Lỗi xác thực (401): Access token không hợp lệ hoặc đã hết hạn. Vui lòng thử kết nối lại.'
                ];
            }

            return [
                'success' => false,
                'error' => 'Lỗi kết nối API: ' . $response->status() . ' - ' . $response->body()
            ];
        } catch (Exception $e) {
            Log::error('TikTok Shop API Error - Get Category Attributes: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Lỗi hệ thống: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get categories with fallback to default categories if API fails
     */
    public function getCategoriesWithFallback(TikTokShopIntegration $integration = null): array
    {
        // Default categories as fallback
        $defaultCategories = [
            '100001' => 'Thời trang nam',
            '100002' => 'Thời trang nữ',
            '100003' => 'Thời trang trẻ em',
            '100004' => 'Giày dép',
            '100005' => 'Túi xách',
            '100006' => 'Phụ kiện thời trang',
            '100007' => 'Đồng hồ',
            '100008' => 'Trang sức',
            '100009' => 'Mỹ phẩm',
            '100010' => 'Chăm sóc cá nhân',
            '100011' => 'Điện tử',
            '100012' => 'Điện thoại & Phụ kiện',
            '100013' => 'Máy tính & Laptop',
            '100014' => 'Gaming',
            '100015' => 'Nhà cửa & Đời sống',
            '100016' => 'Đồ gia dụng',
            '100017' => 'Nội thất',
            '100018' => 'Thể thao & Dã ngoại',
            '100019' => 'Sách & Văn phòng phẩm',
            '100020' => 'Đồ chơi & Sở thích',
            '100021' => 'Thực phẩm & Đồ uống',
            '100022' => 'Sức khỏe & Y tế',
            '100023' => 'Ô tô & Xe máy',
            '100024' => 'Mẹ & Bé',
            '100025' => 'Thú cưng',
            '100026' => 'Khác'
        ];

        if (!$integration) {
            Log::info('No TikTok Shop integration provided, using default categories');
            return $defaultCategories;
        }

        // Thử lấy categories từ database cache trước
        $cachedCategories = $this->getCachedCategories();
        if (!empty($cachedCategories)) {
            Log::info('Using cached categories from database', [
                'team_id' => $integration->team_id,
                'count' => count($cachedCategories),
                'source' => 'database_cache'
            ]);
            return $cachedCategories;
        }

        Log::info('No cached categories found, attempting to get from TikTok Shop API', [
            'team_id' => $integration->team_id,
            'has_access_token' => !empty($integration->access_token),
            'token_expires_at' => $integration->access_token_expires_at
        ]);

        $result = $this->getCategories($integration);

        if ($result['success']) {
            Log::info('Successfully retrieved categories from TikTok Shop API', [
                'count' => count($result['data']),
                'sample_categories' => array_slice($result['data'], 0, 5, true)
            ]);
            return $result['data'];
        }

        Log::warning('Failed to get categories from TikTok Shop API, using default categories', [
            'error' => $result['error'] ?? 'Unknown error',
            'team_id' => $integration->team_id
        ]);

        return $defaultCategories;
    }

    /**
     * Lấy categories từ database cache
     */
    public function getCachedCategories(): array
    {
        try {
            $categories = \App\Models\TikTokShopCategory::leafCategories()
                ->orderBy('category_name')
                ->pluck('category_name', 'category_id')
                ->toArray();

            Log::info('Retrieved cached categories from database', [
                'count' => count($categories),
                'source' => 'database_cache'
            ]);

            return $categories;
        } catch (\Exception $e) {
            Log::warning('Failed to get cached categories from database', [
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    /**
     * Lấy categories với hierarchy từ database cache
     */
    public function getCachedCategoriesWithHierarchy(): array
    {
        try {
            $categories = \App\Models\TikTokShopCategory::getCategoriesWithHierarchy();

            Log::info('Retrieved cached categories with hierarchy from database', [
                'count' => count($categories),
                'source' => 'database_cache_hierarchy'
            ]);

            return $categories;
        } catch (\Exception $e) {
            Log::warning('Failed to get cached categories with hierarchy from database', [
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    /**
     * Kiểm tra và sync categories nếu cần
     */
    public function ensureCategoriesSynced(TikTokShopIntegration $integration): bool
    {
        // Kiểm tra xem có cần sync không
        if (!\App\Models\TikTokShopCategory::needsSystemSync()) {
            return true;
        }

        Log::info('Categories need sync, triggering sync process');

        try {
            // Chạy command sync
            Artisan::call('tiktok:sync-categories', [
                '--force' => false
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to sync categories', [
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Test function to get warehouses for all shops in database
     */
    public function testGetWarehousesForAllShops(): array
    {
        Log::info('=== START TEST GET WAREHOUSES FOR ALL SHOPS ===');

        try {
            $shops = \App\Models\TikTokShop::with('integration')->get();

            if ($shops->isEmpty()) {
                return [
                    'success' => false,
                    'error' => 'Không tìm thấy shop nào trong database'
                ];
            }

            $results = [];
            $successCount = 0;
            $errorCount = 0;

            foreach ($shops as $shop) {
                Log::info("Testing shop: {$shop->id} - {$shop->shop_name}");

                $result = $this->testGetWarehousesWithShopCipher($shop->id);

                $results[] = [
                    'shop_id' => $shop->id,
                    'shop_name' => $shop->shop_name,
                    'tiktok_shop_id' => $shop->shop_id,
                    'cipher' => $shop->getShopCipher(),
                    'result' => $result
                ];

                if ($result['success']) {
                    $successCount++;
                } else {
                    $errorCount++;
                }
            }

            Log::info('=== ALL SHOPS TEST SUMMARY ===', [
                'total_shops' => $shops->count(),
                'success_count' => $successCount,
                'error_count' => $errorCount
            ]);

            return [
                'success' => true,
                'data' => $results,
                'summary' => [
                    'total_shops' => $shops->count(),
                    'success_count' => $successCount,
                    'error_count' => $errorCount
                ]
            ];
        } catch (Exception $e) {
            Log::error('Test All Shops Error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            return [
                'success' => false,
                'error' => 'Lỗi test tất cả shops: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Test function to get warehouses using shop cipher from TikTokShop model
     */
    public function testGetWarehousesWithShopCipher(int $shopId = null): array
    {
        Log::info('=== START TEST GET WAREHOUSES WITH SHOP CIPHER ===');

        try {
            // Lấy shop từ database
            $shop = null;
            if ($shopId) {
                $shop = \App\Models\TikTokShop::find($shopId);
            } else {
                $shop = \App\Models\TikTokShop::first();
            }

            if (!$shop) {
                return [
                    'success' => false,
                    'error' => 'Không tìm thấy shop nào trong database'
                ];
            }

            Log::info('Found shop:', [
                'shop_id' => $shop->id,
                'tiktok_shop_id' => $shop->shop_id,
                'shop_name' => $shop->shop_name,
                'cipher' => $shop->cipher,
                'shop_data' => $shop->shop_data,
                'status' => $shop->status
            ]);

            // Lấy integration từ shop
            $integration = $shop->integration;
            if (!$integration) {
                return [
                    'success' => false,
                    'error' => 'Không tìm thấy integration cho shop này'
                ];
            }

            Log::info('Found integration:', [
                'integration_id' => $integration->id,
                'team_id' => $integration->team_id,
                'has_access_token' => !empty($integration->access_token),
                'token_expires_at' => $integration->access_token_expires_at
            ]);

            // Lấy shop cipher từ TikTokShop model
            $shopCipher = $shop->getShopCipher();

            if (!$shopCipher) {
                return [
                    'success' => false,
                    'error' => 'Không tìm thấy shop cipher trong shop data'
                ];
            }

            Log::info('Using shop cipher:', [
                'shop_cipher' => $shopCipher,
                'cipher_source' => $shop->cipher ? 'shop.cipher' : ($shop->shop_data['cipher'] ? 'shop_data.cipher' : ($shop->shop_data['shop_cipher'] ? 'shop_data.shop_cipher' : 'shop_id'))
            ]);

            // Gọi API get warehouses với shop cipher
            $result = $this->getWarehousesWithCipher($integration, $shopCipher);

            Log::info('=== TEST GET WAREHOUSES RESULT ===', [
                'success' => $result['success'],
                'error' => $result['error'] ?? null,
                'warehouses_count' => isset($result['data']) ? count($result['data']) : 0,
                'shop_cipher_used' => $shopCipher
            ]);

            return $result;
        } catch (Exception $e) {
            Log::error('Test Get Warehouses Error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            return [
                'success' => false,
                'error' => 'Lỗi test: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get warehouses from TikTok Shop API using shop cipher
     */
    public function getWarehousesWithCipher(TikTokShopIntegration $integration, string $shopCipher): array
    {
        Log::info('=== START GET WAREHOUSES WITH CIPHER ===');
        Log::info('Request info:', [
            'integration_id' => $integration->id,
            'shop_cipher' => $shopCipher
        ]);

        try {
            if (!$integration->access_token) {
                return [
                    'success' => false,
                    'error' => 'Access token không tồn tại'
                ];
            }

            // Check if token is expired
            if ($integration->isAccessTokenExpired()) {
                $refreshResult = $this->refreshAccessToken($integration);
                if (!$refreshResult['success']) {
                    return [
                        'success' => false,
                        'error' => 'Access token đã hết hạn và không thể refresh: ' . $refreshResult['error']
                    ];
                }
                $integration->updateTokens($refreshResult['data']);
            }

            // Sử dụng timestamp hiện tại (UTC) - phải nằm trong vòng 5 phút
            $timestamp = time();
            $appKey = $integration->getAppKey();
            $appSecret = $integration->getAppSecret();
            $accessToken = $integration->access_token;

            // Sử dụng endpoint đúng theo example
            $endpoint = 'logistics/' . self::API_VERSION . '/warehouses';
            $url = 'https://open-api.tiktokglobalshop.com/' . $endpoint;

            $sign = TikTokSignatureService::generateCustomSignature(
                $appKey,
                $appSecret,
                '/' . $endpoint,
                [
                    'app_key' => $appKey,
                    'timestamp' => $timestamp,
                    'shop_cipher' => $shopCipher,
                ]
            );

            $queryParams = [
                'app_key' => $appKey,
                'sign' => $sign,
                'timestamp' => $timestamp,
                'shop_cipher' => $shopCipher,
            ];

            $headers = [
                'x-tts-access-token' => $accessToken
            ];

            Log::info('=== FINAL REQUEST DETAILS ===', [
                'url' => $url,
                'query_params' => $queryParams,
                'headers' => array_merge($headers, ['x-tts-access-token' => '***HIDDEN***']),
                'endpoint' => '/' . $endpoint,
                'signature' => $sign,
                'access_token_length' => strlen($accessToken),
                'shop_cipher' => $shopCipher
            ]);

            $response = Http::withHeaders($headers)->get($url, $queryParams);
            $data = $response->json();

            Log::info('TikTok Shop API Response - Get Warehouses', [
                'status' => $response->status(),
                'data' => $data,
                'url_used' => $url
            ]);

            if ($response->successful()) {
                // Kiểm tra nếu response không có code (có thể là data null)
                if (!isset($data['code']) || $data['code'] === 0) {
                    $warehouses = $data['data'] ?? [];

                    // Xử lý trường hợp data null hoặc empty
                    if (empty($warehouses)) {
                        Log::warning('Warehouses API returned empty data', [
                            'shop_cipher' => $shopCipher,
                            'response_data' => $data,
                            'response_status' => $response->status()
                        ]);

                        return [
                            'success' => true,
                            'data' => [],
                            'message' => 'Shop chưa có warehouses được setup',
                            'request_id' => $data['request_id'] ?? null
                        ];
                    }

                    // Log chi tiết warehouses nhận được từ TikTok API
                    Log::info('=== WAREHOUSES DATA FROM TIKTOK API ===');
                    Log::info('Warehouses API Response (JSON): ' . json_encode([
                        'shop_cipher' => $shopCipher,
                        'total_warehouses' => count($warehouses),
                        'raw_response_data' => $data['data'] ?? [],
                        'request_id' => $data['request_id'] ?? null
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

                    // Log tất cả warehouses nếu có ít hơn 20
                    if (count($warehouses) <= 20) {
                        Log::info('All warehouses received from TikTok API (JSON): ' . json_encode([
                            'warehouses' => $warehouses
                        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                    } else {
                        // Log warehouses theo nhóm nếu có nhiều
                        $chunks = array_chunk($warehouses, 20);
                        foreach ($chunks as $index => $chunk) {
                            Log::info("Warehouses chunk " . ($index + 1) . " (JSON): " . json_encode([
                                'chunk_number' => $index + 1,
                                'total_chunks' => count($chunks),
                                'warehouses_in_chunk' => count($chunk),
                                'warehouses' => $chunk
                            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                        }
                    }

                    // Log structure của warehouse đầu tiên để kiểm tra format
                    if (!empty($warehouses)) {
                        $firstWarehouse = reset($warehouses); // Lấy phần tử đầu tiên an toàn
                        Log::info('First warehouse structure analysis (JSON): ' . json_encode([
                            'warehouse_keys' => array_keys($firstWarehouse),
                            'warehouse_values' => array_values($firstWarehouse),
                            'warehouse_data_types' => array_map('gettype', $firstWarehouse),
                            'sample_warehouse' => $firstWarehouse
                        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

                        // Kiểm tra các trường quan trọng (theo cấu trúc thực tế từ TikTok API)
                        $importantFields = ['id', 'name', 'type', 'sub_type', 'effect_status', 'is_default'];
                        $availableFields = [];
                        foreach ($importantFields as $field) {
                            if (isset($firstWarehouse[$field])) {
                                $availableFields[$field] = $firstWarehouse[$field];
                            }
                        }

                        Log::info('Important warehouse fields found (JSON): ' . json_encode([
                            'available_fields' => $availableFields,
                            'missing_fields' => array_diff($importantFields, array_keys($firstWarehouse))
                        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                    }

                    // Log summary
                    Log::info('Warehouses summary (JSON): ' . json_encode([
                        'shop_cipher' => $shopCipher,
                        'total_warehouses' => count($warehouses),
                        'warehouse_ids' => array_column($warehouses, 'id'),
                        'warehouse_names' => array_column($warehouses, 'name'),
                        'warehouse_types' => array_column($warehouses, 'type'),
                        'has_default_warehouse' => !empty(array_filter($warehouses, function ($w) {
                            return isset($w['is_default']) && $w['is_default'];
                        }))
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

                    return [
                        'success' => true,
                        'data' => $warehouses,
                        'request_id' => $data['request_id'] ?? null
                    ];
                } else {
                    $errorCode = $data['code'] ?? 'UNKNOWN';
                    $errorMessage = $data['message'] ?? 'Không thể lấy danh sách warehouses';

                    Log::error("TikTok Shop Get Warehouses Error - Code: {$errorCode}, Message: {$errorMessage}");

                    return [
                        'success' => false,
                        'error' => "Lỗi {$errorCode}: {$errorMessage}"
                    ];
                }
            }

            if ($response->status() === 401) {
                return [
                    'success' => false,
                    'error' => 'Lỗi xác thực (401): Access token không hợp lệ hoặc đã hết hạn. Vui lòng thử kết nối lại.'
                ];
            }

            return [
                'success' => false,
                'error' => 'Lỗi kết nối API: ' . $response->status() . ' - ' . $response->body()
            ];
        } catch (Exception $e) {
            Log::error('TikTok Shop API Error - Get Warehouses: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Lỗi hệ thống: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get warehouses from TikTok Shop API using shop cipher from TikTokShop model
     */
    public function getWarehouses(TikTokShopIntegration $integration, int $shopId = null): array
    {
        Log::info('=== START GET WAREHOUSES ===');
        Log::info('Request info:', [
            'integration_id' => $integration->id,
            'shop_id' => $shopId
        ]);

        try {
            if (!$integration->access_token) {
                return [
                    'success' => false,
                    'error' => 'Access token không tồn tại'
                ];
            }

            // Check if token is expired
            if ($integration->isAccessTokenExpired()) {
                $refreshResult = $this->refreshAccessToken($integration);
                if (!$refreshResult['success']) {
                    return [
                        'success' => false,
                        'error' => 'Access token đã hết hạn và không thể refresh: ' . $refreshResult['error']
                    ];
                }
                $integration->updateTokens($refreshResult['data']);
            }

            // Lấy shop đầu tiên của integration này nếu không có shopId
            $shop = null;
            if (!$shopId) {
                $shop = $integration->shops()->first();
                if (!$shop) {
                    return [
                        'success' => false,
                        'error' => 'Không tìm thấy shop nào cho integration này'
                    ];
                }
            } else {
                $shop = $integration->shops()->where('id', $shopId)->first();
                if (!$shop) {
                    return [
                        'success' => false,
                        'error' => 'Không tìm thấy shop với ID: ' . $shopId
                    ];
                }
            }

            // Lấy shop cipher từ TikTokShop model
            $shopCipher = $shop->getShopCipher();
            if (!$shopCipher) {
                return [
                    'success' => false,
                    'error' => 'Không tìm thấy shop cipher cho shop này'
                ];
            }

            Log::info('Using shop cipher for warehouses API:', [
                'shop_id' => $shop->id,
                'shop_name' => $shop->shop_name,
                'shop_cipher' => $shopCipher,
                'cipher_source' => $shop->cipher ? 'shop.cipher' : ($shop->shop_data['cipher'] ? 'shop_data.cipher' : ($shop->shop_data['shop_cipher'] ? 'shop_data.shop_cipher' : 'shop_id'))
            ]);

            // Sử dụng timestamp hiện tại (UTC) - phải nằm trong vòng 5 phút
            $timestamp = time();
            $appKey = $integration->getAppKey();
            $appSecret = $integration->getAppSecret();
            $accessToken = $integration->access_token;

            // Sử dụng endpoint đúng theo example
            $endpoint = 'logistics/' . self::API_VERSION . '/warehouses';
            $url = 'https://open-api.tiktokglobalshop.com/' . $endpoint;

            $sign = TikTokSignatureService::generateCustomSignature(
                $appKey,
                $appSecret,
                '/' . $endpoint,
                [
                    'app_key' => $appKey,
                    'timestamp' => $timestamp,
                    'shop_cipher' => $shopCipher,
                ]
            );

            $queryParams = [
                'app_key' => $appKey,
                'sign' => $sign,
                'timestamp' => $timestamp,
                'shop_cipher' => $shopCipher,
            ];

            $headers = [
                'x-tts-access-token' => $accessToken
            ];

            Log::info('=== FINAL REQUEST DETAILS ===', [
                'url' => $url,
                'query_params' => $queryParams,
                'headers' => array_merge($headers, ['x-tts-access-token' => '***HIDDEN***']),
                'endpoint' => '/' . $endpoint,
                'signature' => $sign,
                'access_token_length' => strlen($accessToken),
                'shop_cipher' => $shopCipher
            ]);

            $response = Http::withHeaders($headers)->get($url, $queryParams);
            $data = $response->json();

            Log::info('TikTok Shop API Response - Get Warehouses', [
                'status' => $response->status(),
                'data' => $data,
                'url_used' => $url
            ]);

            if ($response->successful()) {
                // Kiểm tra nếu response không có code (có thể là data null)
                if (!isset($data['code']) || $data['code'] === 0) {
                    // Lấy warehouses từ data, có thể là data.warehouses hoặc data trực tiếp
                    $warehouses = $data['data']['warehouses'] ?? $data['data'] ?? [];

                    // Xử lý trường hợp data null hoặc empty
                    if (empty($warehouses)) {
                        Log::warning('Warehouses API returned empty data', [
                            'shop_id' => $shop->id,
                            'shop_cipher' => $shopCipher,
                            'response_data' => $data,
                            'response_status' => $response->status()
                        ]);

                        return [
                            'success' => true,
                            'data' => [],
                            'message' => 'Shop chưa có warehouses được setup',
                            'request_id' => $data['request_id'] ?? null
                        ];
                    }

                    // Log chi tiết warehouses nhận được từ TikTok API
                    Log::info('=== WAREHOUSES DATA FROM TIKTOK API ===');
                    Log::info('Warehouses API Response (JSON): ' . json_encode([
                        'shop_id' => $shop->id,
                        'shop_cipher' => $shopCipher,
                        'total_warehouses' => count($warehouses),
                        'raw_response_data' => $data['data'] ?? [],
                        'request_id' => $data['request_id'] ?? null
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

                    // Log tất cả warehouses nếu có ít hơn 20
                    if (count($warehouses) <= 20) {
                        Log::info('All warehouses received from TikTok API (JSON): ' . json_encode([
                            'warehouses' => $warehouses
                        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                    } else {
                        // Log warehouses theo nhóm nếu có nhiều
                        $chunks = array_chunk($warehouses, 20);
                        foreach ($chunks as $index => $chunk) {
                            Log::info("Warehouses chunk " . ($index + 1) . " (JSON): " . json_encode([
                                'chunk_number' => $index + 1,
                                'total_chunks' => count($chunks),
                                'warehouses_in_chunk' => count($chunk),
                                'warehouses' => $chunk
                            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                        }
                    }

                    // Log structure của warehouse đầu tiên để kiểm tra format
                    if (!empty($warehouses)) {
                        $firstWarehouse = reset($warehouses); // Lấy phần tử đầu tiên an toàn
                        Log::info('First warehouse structure analysis (JSON): ' . json_encode([
                            'warehouse_keys' => array_keys($firstWarehouse),
                            'warehouse_values' => array_values($firstWarehouse),
                            'warehouse_data_types' => array_map('gettype', $firstWarehouse),
                            'sample_warehouse' => $firstWarehouse
                        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

                        // Kiểm tra các trường quan trọng (theo cấu trúc thực tế từ TikTok API)
                        $importantFields = ['id', 'name', 'type', 'sub_type', 'effect_status', 'is_default'];
                        $availableFields = [];
                        foreach ($importantFields as $field) {
                            if (isset($firstWarehouse[$field])) {
                                $availableFields[$field] = $firstWarehouse[$field];
                            }
                        }

                        Log::info('Important warehouse fields found (JSON): ' . json_encode([
                            'available_fields' => $availableFields,
                            'missing_fields' => array_diff($importantFields, array_keys($firstWarehouse))
                        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                    }

                    // Log summary
                    Log::info('Warehouses summary (JSON): ' . json_encode([
                        'shop_id' => $shop->id,
                        'shop_cipher' => $shopCipher,
                        'total_warehouses' => count($warehouses),
                        'warehouse_ids' => array_column($warehouses, 'id'),
                        'warehouse_names' => array_column($warehouses, 'name'),
                        'warehouse_types' => array_column($warehouses, 'type'),
                        'has_default_warehouse' => !empty(array_filter($warehouses, function ($w) {
                            return isset($w['is_default']) && $w['is_default'];
                        }))
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

                    return [
                        'success' => true,
                        'data' => $warehouses,
                        'request_id' => $data['request_id'] ?? null
                    ];
                } else {
                    $errorCode = $data['code'] ?? 'UNKNOWN';
                    $errorMessage = $data['message'] ?? 'Không thể lấy danh sách warehouses';

                    Log::error("TikTok Shop Get Warehouses Error - Code: {$errorCode}, Message: {$errorMessage}");

                    return [
                        'success' => false,
                        'error' => "Lỗi {$errorCode}: {$errorMessage}"
                    ];
                }
            }

            if ($response->status() === 401) {
                return [
                    'success' => false,
                    'error' => 'Lỗi xác thực (401): Access token không hợp lệ hoặc đã hết hạn. Vui lòng thử kết nối lại.'
                ];
            }

            return [
                'success' => false,
                'error' => 'Lỗi kết nối API: ' . $response->status() . ' - ' . $response->body()
            ];
        } catch (Exception $e) {
            Log::error('TikTok Shop API Error - Get Warehouses: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Lỗi hệ thống: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Tìm kiếm đơn hàng từ TikTok Shop API
     */
    public function searchOrders(
        TikTokShopIntegration $integration,
        int $shopId,
        array $filters = [],
        int $pageSize = 20,
        string $sortOrder = 'DESC',
        string $sortField = 'create_time',
        ?string $pageToken = null
    ): array {
        Log::info('=== START SEARCH ORDERS FROM TIKTOK ===', [
            'integration_id' => $integration->id,
            'shop_id' => $shopId,
            'filters' => $filters,
            'page_size' => $pageSize
        ]);

        try {
            // Kiểm tra integration có hoạt động không
            if (!$integration->isActive()) {
                throw new Exception('TikTok Shop integration không hoạt động hoặc token đã hết hạn');
            }

            // Kiểm tra access token
            if ($integration->isAccessTokenExpired()) {
                $refreshResult = $integration->refreshAccessToken();
                if (!$refreshResult['success']) {
                    throw new Exception('Không thể refresh token: ' . $refreshResult['message']);
                }
            }

            // Lấy shop cipher
            $shop = \App\Models\TikTokShop::find($shopId);
            if (!$shop) {
                throw new Exception('Shop không tồn tại');
            }

            $shopCipher = $shop->getShopCipher();

            // Tạo timestamp
            $timestamp = time();

            // Chuẩn bị query parameters
            $queryParams = [
                'shop_cipher' => $shopCipher,
                'app_key' => $integration->getAppKey(),
                'timestamp' => $timestamp,
                'page_size' => $pageSize,
                'sort_order' => $sortOrder,
                'sort_field' => $sortField
            ];

            if ($pageToken) {
                $queryParams['page_token'] = $pageToken;
            }

            // Chuẩn bị body parameters
            $bodyParams = $this->prepareOrderSearchFilters($filters);

            // Tạo signature
            $signature = TikTokSignatureService::generateOrderSearchSignature(
                $integration->getAppKey(),
                $integration->getAppSecret(),
                (string) $timestamp,
                $bodyParams,
                $shopCipher
            );

            $queryParams['sign'] = $signature;

            // Gọi API
            $response = $this->callOrderSearchAPI($queryParams, $bodyParams, $integration);

            if ($response['success']) {
                Log::info('Search orders successful', [
                    'shop_id' => $shopId,
                    'orders_count' => count($response['data']['order_list'] ?? []),
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
                'shop_id' => $shopId,
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
    private function prepareOrderSearchFilters(array $filters): array
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

        return $bodyParams;
    }

    /**
     * Gọi API tìm kiếm đơn hàng
     */
    private function callOrderSearchAPI(array $queryParams, array $bodyParams, TikTokShopIntegration $integration): array
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

        $response = Http::withHeaders($headers)
            ->timeout(30)
            ->withBody($jsonBody, 'application/json')
            ->post($url . '?' . http_build_query($queryParams));

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
}
