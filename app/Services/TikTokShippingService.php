<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use App\Services\TikTokSignatureService;

class TikTokShippingService
{
    /**
     * Lấy danh sách đơn vị vận chuyển từ TikTok API
     */
    public static function getShippingProviders($shop, $deliveryOptionId)
    {
        try {
            $integration = $shop->integration;

            if (!$integration || !$integration->isActive()) {
                return [
                    'success' => false,
                    'error' => 'Integration không hoạt động hoặc token đã hết hạn'
                ];
            }

            // Lấy thông tin app credentials từ integration (có xử lý market-specific)
            // Tự động lấy credentials theo market của shop (US hoặc UK)
            $market = $integration->market ?? 'US';
            $appKey = $integration->getAppKey();
            $appSecret = $integration->getAppSecret();
            $shopCipher = $shop->getShopCipher();

            Log::info('Using TikTok credentials for shipping providers', [
                'shop_id' => $shop->id,
                'shop_name' => $shop->shop_name,
                'market' => $market,
                'app_key' => substr($appKey, 0, 10) . '...' // Chỉ log một phần để bảo mật
            ]);

            if (!$appKey || !$appSecret) {
                Log::warning('TikTok app credentials missing for shipping providers', [
                    'shop_id' => $shop->id,
                    'integration_id' => $integration->id,
                    'market' => $market,
                    'config_keys_checked' => [
                        'market_specific' => "tiktok-shop.markets.{$market}",
                        'general' => 'tiktok-shop.app_key'
                    ]
                ]);

                return [
                    'success' => false,
                    'error' => "Thiếu TikTok app credentials cho thị trường {$market}. Vui lòng cấu hình trong System Settings."
                ];
            }

            // Tạo timestamp
            $timestamp = time();

            // Chuẩn bị query parameters
            $queryParams = [
                'app_key' => $appKey,
                'timestamp' => $timestamp,
                'shop_cipher' => $shopCipher
            ];

            // Tạo signature
            $signature = TikTokSignatureService::generateShippingProvidersSignature(
                $appKey,
                $appSecret,
                $timestamp,
                $shopCipher,
                $deliveryOptionId
            );

            $queryParams['sign'] = $signature;

            // Build URL
            $url = "https://open-api.tiktokglobalshop.com/logistics/202309/delivery_options/{$deliveryOptionId}/shipping_providers?" . http_build_query($queryParams);

            $headers = [
                'Content-Type: application/json',
                'x-tts-access-token: ' . $integration->access_token
            ];

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_HTTPGET, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            Log::info('TikTok Shipping Providers API Response', [
                'shop_id' => $shop->id,
                'delivery_option_id' => $deliveryOptionId,
                'url' => $url,
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
                        'error' => $data['message'] ?? 'API error',
                        'code' => $data['code'] ?? 'unknown'
                    ];
                }
            }

            return [
                'success' => false,
                'error' => 'HTTP ' . $httpCode . ': ' . $response
            ];
        } catch (\Exception $e) {
            Log::error('TikTok Shipping Providers API Exception', [
                'shop_id' => $shop->id,
                'delivery_option_id' => $deliveryOptionId,
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
     * Mark package as shipped
     */
    public static function markPackageAsShipped($shop, $orderId, $trackingNumber, $shippingProviderId, $orderLineItemIds = [])
    {
        try {
            $integration = $shop->integration;

            if (!$integration || !$integration->isActive()) {
                return [
                    'success' => false,
                    'error' => 'Integration không hoạt động hoặc token đã hết hạn'
                ];
            }

            // Lấy thông tin app credentials từ integration (có xử lý market-specific)
            // Tự động lấy credentials theo market của shop (US hoặc UK)
            $market = $integration->market ?? 'US';
            $appKey = $integration->getAppKey();
            $appSecret = $integration->getAppSecret();
            $shopCipher = $shop->getShopCipher();

            Log::info('Using TikTok credentials for mark as shipped', [
                'shop_id' => $shop->id,
                'shop_name' => $shop->shop_name,
                'market' => $market,
                'app_key' => substr($appKey, 0, 10) . '...' // Chỉ log một phần để bảo mật
            ]);

            if (!$appKey || !$appSecret) {
                Log::warning('TikTok app credentials missing for mark as shipped', [
                    'shop_id' => $shop->id,
                    'integration_id' => $integration->id,
                    'market' => $market,
                    'config_keys_checked' => [
                        'market_specific' => "tiktok-shop.markets.{$market}",
                        'general' => 'tiktok-shop.app_key'
                    ]
                ]);

                return [
                    'success' => false,
                    'error' => "Thiếu TikTok app credentials cho thị trường {$market}. Vui lòng cấu hình trong System Settings."
                ];
            }

            // Tạo timestamp
            $timestamp = time();

            // Chuẩn bị body parameters
            $bodyParams = [
                'tracking_number' => $trackingNumber,
                'shipping_provider_id' => $shippingProviderId
            ];

            if (!empty($orderLineItemIds)) {
                $bodyParams['order_line_item_ids'] = $orderLineItemIds;
            }

            // Tạo signature
            $signature = TikTokSignatureService::generateMarkShippedSignature(
                $appKey,
                $appSecret,
                $timestamp,
                $bodyParams,
                $shopCipher,
                $orderId
            );

            // Chuẩn bị query parameters
            $queryParams = [
                'app_key' => $appKey,
                'timestamp' => $timestamp,
                'shop_cipher' => $shopCipher,
                'sign' => $signature
            ];

            // Build URL
            $url = "https://open-api.tiktokglobalshop.com/fulfillment/202309/orders/{$orderId}/packages?" . http_build_query($queryParams);

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

            Log::info('TikTok Mark Package As Shipped API Response', [
                'shop_id' => $shop->id,
                'tiktok_order_id' => $orderId,
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
                    // Xử lý các error codes cụ thể từ TikTok API
                    $errorCode = $data['code'] ?? 'unknown';
                    $errorMessage = $data['message'] ?? 'API error';

                    // Dịch các error messages phổ biến
                    $translatedMessage = match ($errorCode) {
                        21011020 => 'Không thể đánh dấu gói hàng đã gửi. Đơn hàng có thể đã được xử lý, không ở trạng thái cho phép (không phải AWAITING_SHIPMENT), hoặc đã có tracking number rồi.',
                        default => $errorMessage
                    };

                    Log::warning('TikTok API returned error for mark as shipped', [
                        'shop_id' => $shop->id,
                        'tiktok_order_id' => $orderId,
                        'error_code' => $errorCode,
                        'error_message' => $errorMessage,
                        'translated_message' => $translatedMessage
                    ]);

                    return [
                        'success' => false,
                        'error' => $translatedMessage,
                        'code' => $errorCode,
                        'original_message' => $errorMessage
                    ];
                }
            }

            return [
                'success' => false,
                'error' => 'HTTP ' . $httpCode . ': ' . $response
            ];
        } catch (\Exception $e) {
            Log::error('TikTok Mark Package As Shipped API Exception', [
                'shop_id' => $shop->id,
                'order_id' => $orderId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
}
