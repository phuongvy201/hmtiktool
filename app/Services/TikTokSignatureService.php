<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class TikTokSignatureService
{
    private const API_VERSION = '202309';
    /**
     * Tạo signature cho TikTok API
     * 
     * @param string $appKey App key của ứng dụng
     * @param string $appSecret App secret của ứng dụng
     * @param string $apiPath Đường dẫn API (ví dụ: /product/202309/images/upload)
     * @param array $queryParams Các tham số query (không bao gồm sign)
     * @param array $bodyParams Các tham số body (nếu có)
     * @param string|null $contentType Content type của request
     * @return string Signature hex string
     */
    public static function generateSignature(
        string $appKey,
        string $appSecret,
        string $apiPath,
        array $queryParams = [],
        array $bodyParams = [],
        ?string $contentType = null
    ): string {
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
        $signature = hash_hmac('sha256', $stringToSign, $appSecret, true);
        $hexSignature = bin2hex($signature);

        // Debug logging
        Log::info('TikTok Signature Generation', [
            'api_path' => $apiPath,
            'query_params' => $queryParams,
            'filtered_params' => $filteredParams,
            'param_string' => $paramString,
            'input' => $input,
            'string_to_sign' => $stringToSign,
            'signature_hex' => $hexSignature,
            'app_key' => $appKey,
            'app_secret_length' => strlen($appSecret),
            'content_type' => $contentType,
            'body_params' => $bodyParams
        ]);

        return $hexSignature;
    }

    /**
     * Tạo signature đơn giản cho image upload
     * 
     * @param string $appKey
     * @param string $appSecret
     * @param string $timestamp
     * @return string
     */
    public static function generateImageUploadSignature(string $appKey, string $appSecret, string $timestamp): string
    {
        $apiPath = '/product/' . self::API_VERSION . '/images/upload';
        $queryParams = [
            'app_key' => $appKey,
            'timestamp' => $timestamp
        ];

        return self::generateSignature($appKey, $appSecret, $apiPath, $queryParams);
    }

    /**
     * Tạo signature cho product upload
     * 
     * @param string $appKey
     * @param string $appSecret
     * @param string $timestamp
     * @param array $bodyParams
     * @param string|null $shopCipher
     * @param string|null $categoryVersion Category version (v1 hoặc v2)
     * @return string
     */
    public static function generateProductUploadSignature(string $appKey, string $appSecret, string $timestamp, array $bodyParams = [], ?string $shopCipher = null, ?string $categoryVersion = null): string
    {
        $apiPath = '/product/' . self::API_VERSION . '/products';
        $queryParams = [
            'app_key' => $appKey,
            'timestamp' => $timestamp
        ];

        // Thêm shop_cipher vào signature generation nếu có
        if ($shopCipher) {
            $queryParams['shop_cipher'] = $shopCipher;
        }

        // Thêm category_version vào signature generation nếu có
        if ($categoryVersion) {
            $queryParams['category_version'] = $categoryVersion;
        }

        return self::generateSignature($appKey, $appSecret, $apiPath, $queryParams, $bodyParams, 'application/json');
    }

    /**
     * Tạo signature cho order search API
     * 
     * @param string $appKey
     * @param string $appSecret
     * @param string $timestamp
     * @param array $bodyParams
     * @param string|null $shopCipher
     * @param int|null $pageSize
     * @return string
     */
    public static function generateOrderSearchSignature(string $appKey, string $appSecret, string $timestamp, array $bodyParams = [], ?string $shopCipher = null, ?int $pageSize = null): string
    {
        $apiPath = '/order/' . self::API_VERSION . '/orders/search';
        $queryParams = [
            'app_key' => $appKey,
            'timestamp' => $timestamp
        ];

        // Thêm shop_cipher vào signature generation nếu có
        if ($shopCipher) {
            $queryParams['shop_cipher'] = $shopCipher;
        }

        // Thêm page_size vào signature generation nếu có
        if ($pageSize !== null) {
            $queryParams['page_size'] = (string)$pageSize;
        }

        return self::generateSignature($appKey, $appSecret, $apiPath, $queryParams, $bodyParams, 'application/json');
    }

    /**
     * Tạo signature cho Product Search API
     * 
     * @param string $appKey
     * @param string $appSecret
     * @param string $timestamp
     * @param array $bodyParams
     * @param string|null $shopCipher
     * @param bool|null $returnDraftVersion
     * @return string
     */
    public static function generateProductSearchSignature(
        string $appKey,
        string $appSecret,
        string $timestamp,
        array $bodyParams = [],
        ?string $shopCipher = null,
        ?bool $returnDraftVersion = null
    ): string {
        $apiPath = '/product/' . self::API_VERSION . '/products/search';
        $queryParams = [
            'app_key' => $appKey,
            'timestamp' => $timestamp
        ];

        // Thêm return_draft_version vào signature generation nếu có
        if ($returnDraftVersion !== null) {
            $queryParams['return_draft_version'] = $returnDraftVersion ? 'true' : 'false';
        }

        // Thêm page_size vào signature generation nếu có trong bodyParams
        if (isset($bodyParams['page_size'])) {
            $queryParams['page_size'] = (string)$bodyParams['page_size'];
        }

        // Thêm shop_cipher vào signature generation nếu có (phải để cuối cùng)
        if ($shopCipher) {
            $queryParams['shop_cipher'] = $shopCipher;
        }

        return self::generateSignature($appKey, $appSecret, $apiPath, $queryParams, $bodyParams, 'application/json');
    }

    /**
     * Tạo signature cho bất kỳ API nào
     * 
     * @param string $appKey
     * @param string $appSecret
     * @param string $apiPath
     * @param array $queryParams
     * @param array $bodyParams
     * @param string|null $contentType
     * @return string
     */
    public static function generateCustomSignature(
        string $appKey,
        string $appSecret,
        string $apiPath,
        array $queryParams = [],
        array $bodyParams = [],
        ?string $contentType = null
    ): string {
        return self::generateSignature($appKey, $appSecret, $apiPath, $queryParams, $bodyParams, $contentType);
    }

    /**
     * Tạo signature cho Shipping Providers API
     */
    public static function generateShippingProvidersSignature(
        string $appKey,
        string $appSecret,
        int $timestamp,
        string $shopCipher,
        string $deliveryOptionId
    ): string {
        // Chuẩn bị query parameters
        $queryParams = [
            'app_key' => $appKey,
            'timestamp' => $timestamp,
            'shop_cipher' => $shopCipher
        ];

        // Tạo signature với delivery_option_id thực tế
        return self::generateSignature(
            $appKey,
            $appSecret,
            "/logistics/202309/delivery_options/{$deliveryOptionId}/shipping_providers",
            $queryParams
        );
    }

    /**
     * Tạo signature cho Mark Package As Shipped API
     */
    public static function generateMarkShippedSignature(
        string $appKey,
        string $appSecret,
        int $timestamp,
        array $bodyParams,
        string $shopCipher,
        string $orderId
    ): string {
        // Chuẩn bị query parameters
        $queryParams = [
            'app_key' => $appKey,
            'timestamp' => $timestamp,
            'shop_cipher' => $shopCipher
        ];

        // Tạo signature với order_id thực tế
        return self::generateSignature(
            $appKey,
            $appSecret,
            "/fulfillment/202309/orders/{$orderId}/packages",
            $queryParams,
            $bodyParams
        );
    }

    /**
     * Tạo signature cho GET /order/202507/orders API với parameter ids
     */
    public static function generateOrderByIdsSignature(
        string $appKey,
        string $appSecret,
        int $timestamp,
        string $shopCipher,
        array $orderIds
    ): string {
        $queryParams = [
            'app_key' => $appKey,
            'timestamp' => $timestamp,
            'shop_cipher' => $shopCipher,
            'ids' => implode(',', $orderIds) // Convert array to comma-separated string
        ];

        return self::generateSignature(
            $appKey,
            $appSecret,
            "/order/202507/orders",
            $queryParams
        );
    }

    /**
     * Tạo signature cho GET /finance/202309/payments API
     */
    public static function generateFinancePaymentsSignature(
        string $appKey,
        string $appSecret,
        int $timestamp,
        string $shopCipher,
        array $queryParams
    ): string {
        // Loại bỏ sign khỏi queryParams nếu có
        unset($queryParams['sign']);

        return self::generateSignature(
            $appKey,
            $appSecret,
            "/finance/202309/payments",
            $queryParams
        );
    }
}
