<?php

namespace App\Services;

use App\Models\TikTokShop;
use App\Models\TikTokOrder;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

class TikTokShopPerformanceService
{
    private const API_BASE_URL = 'https://open-api.tiktokglobalshop.com';
    private const ENDPOINT = '/analytics/202405/shop/performance';

    /**
     * Lấy dữ liệu performance theo công thức GMV nội bộ
     * GMV = (Total - Tax + TikTok Discount - TikTok Shipping Discount) * 0.94 trong khoảng ngày
     * Hold = (Total - Tax + TikTok Discount - TikTok Shipping Discount) * 0.96 toàn thời gian
     */
    public function getShopPerformance(TikTokShop $shop, array $filters = []): array
    {
        try {
            $startDate = Carbon::parse($filters['start_date'] ?? Carbon::now()->subDays(7))->startOfDay();
            $endDate = Carbon::parse($filters['end_date'] ?? Carbon::now())->endOfDay();

            // Đơn trong khoảng ngày
            $ordersQuery = TikTokOrder::where('tiktok_shop_id', $shop->id)
                ->whereBetween('create_time', [$startDate, $endDate])
                ->whereNotIn('order_status', ['CANCELLED', 'UNPAID']);
            $ordersInRange = $ordersQuery->get();

            // Đơn toàn thời gian cho Hold
            $ordersAll = TikTokOrder::where('tiktok_shop_id', $shop->id)
                ->whereNotIn('order_status', ['CANCELLED', 'UNPAID'])
                ->get();

            $currentIntervals = $this->buildIntervals($ordersInRange, 0.94);
            $summary = $this->calculateSummaryFromOrders($ordersInRange, 0.94);

            // Hold tổng toàn thời gian với hệ số 0.96
            $summary['hold_gmv'] = $this->calculateGmvFromOrders($ordersAll, 0.96);

            return [
                'success' => true,
                'data' => [
                    'current_period' => $currentIntervals,
                    'comparison_period' => [],
                    'latest_available_date' => $endDate->toDateString(),
                    'summary' => $summary,
                ],
                'message' => 'Calculated GMV locally',
            ];
        } catch (\Exception $e) {
            Log::error('Exception in TikTokShopPerformanceService::getShopPerformance (local)', [
                'shop_id' => $shop->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'data' => null,
                'message' => 'Exception: ' . $e->getMessage(),
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Tạo dữ liệu mẫu cho GMV Dashboard
     */
    private function generateMockPerformanceData(TikTokShop $shop, array $filters): array
    {
        $startDate = $filters['start_date'] ?? date('Y-m-d', strtotime('-7 days'));
        $endDate = $filters['end_date'] ?? date('Y-m-d');
        $currency = $filters['currency'] ?? 'USD';

        // Tạo dữ liệu cho 7 ngày
        $intervals = [];
        $comparisonIntervals = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-{$i} days"));
            $nextDate = date('Y-m-d', strtotime("-{$i} days +1 day"));

            // Tạo dữ liệu ngẫu nhiên nhưng realistic
            $gmv = rand(100, 5000);
            $orders = rand(5, 50);
            $units = rand(10, 100);
            $buyers = rand(3, 30);
            $impressions = rand(1000, 10000);
            $pageViews = rand(100, 1000);
            $refunds = rand(0, $gmv * 0.1);

            $interval = [
                'start_date' => $date,
                'end_date' => $nextDate,
                'gmv' => [
                    'amount' => $gmv,
                    'currency' => $currency
                ],
                'gmv_breakdowns' => [
                    [
                        'amount' => number_format($gmv * 0.7, 2),
                        'currency' => $currency,
                        'type' => 'LIVE'
                    ],
                    [
                        'amount' => number_format($gmv * 0.3, 2),
                        'currency' => $currency,
                        'type' => 'VIDEO'
                    ]
                ],
                'sku_orders' => $orders,
                'orders' => $orders,
                'avg_order_value' => [
                    'amount' => $gmv / max($orders, 1),
                    'currency' => $currency
                ],
                'units_sold' => $units,
                'buyers' => $buyers,
                'buyer_breakdowns' => [
                    [
                        'amount' => intval($buyers * 0.6),
                        'type' => 'LIVE'
                    ],
                    [
                        'amount' => intval($buyers * 0.4),
                        'type' => 'VIDEO'
                    ]
                ],
                'product_impressions' => $impressions,
                'product_impression_breakdowns' => [
                    [
                        'amount' => intval($impressions * 0.8),
                        'type' => 'LIVE'
                    ],
                    [
                        'amount' => intval($impressions * 0.2),
                        'type' => 'VIDEO'
                    ]
                ],
                'product_page_views' => $pageViews,
                'product_page_view_breakdowns' => [
                    [
                        'amount' => intval($pageViews * 0.7),
                        'type' => 'LIVE'
                    ],
                    [
                        'amount' => intval($pageViews * 0.3),
                        'type' => 'VIDEO'
                    ]
                ],
                'avg_product_page_visitors' => $pageViews / max($buyers, 1),
                'avg_product_page_visitor_breakdowns' => [
                    [
                        'amount' => number_format(($pageViews * 0.7) / max($buyers, 1), 1),
                        'type' => 'LIVE'
                    ],
                    [
                        'amount' => number_format(($pageViews * 0.3) / max($buyers, 1), 1),
                        'type' => 'VIDEO'
                    ]
                ],
                'refunds' => [
                    'amount' => $refunds,
                    'currency' => $currency
                ],
                'cancellations_and_returns' => rand(0, 5)
            ];

            $intervals[] = $interval;

            // Tạo dữ liệu comparison (tuần trước)
            $comparisonInterval = $interval;
            $comparisonInterval['gmv']['amount'] = $gmv * rand(80, 120) / 100;
            $comparisonInterval['orders'] = intval($orders * rand(80, 120) / 100);
            $comparisonInterval['sku_orders'] = $comparisonInterval['orders'];
            $comparisonInterval['units_sold'] = intval($units * rand(80, 120) / 100);
            $comparisonInterval['buyers'] = intval($buyers * rand(80, 120) / 100);
            $comparisonInterval['product_impressions'] = intval($impressions * rand(80, 120) / 100);
            $comparisonInterval['product_page_views'] = intval($pageViews * rand(80, 120) / 100);
            $comparisonInterval['refunds']['amount'] = $refunds * rand(80, 120) / 100;

            $comparisonIntervals[] = $comparisonInterval;
        }

        return [
            'performance' => [
                'intervals' => $intervals,
                'comparison_intervals' => $comparisonIntervals
            ],
            'latest_available_date' => $endDate
        ];
    }

    /**
     * Xây dựng parameters cho API call
     */
    private function buildPerformanceParams(TikTokShop $shop, array $filters): array
    {
        $startDate = $filters['start_date'] ?? date('Y-m-d', strtotime('-7 days'));
        $endDate = $filters['end_date'] ?? date('Y-m-d');
        $granularity = $filters['granularity'] ?? '1D'; // 1D for daily breakdown, ALL for summary
        $withComparison = $filters['with_comparison'] ?? true;
        $currency = $filters['currency'] ?? 'USD';

        // Lấy app_key từ integration theo market (giống các service khác)
        $integration = $shop->integration;
        $appKey = $integration ? $integration->getAppKey() : config('tiktok-shop.app_key');

        // Fallback nếu vẫn rỗng
        if (empty($appKey)) {
            $appKey = config('tiktok-shop.app_key') ?? env('TIKTOK_SHOP_APP_KEY');
        }

        if (empty($appKey)) {
            Log::warning('TikTok app_key is empty in TikTokShopPerformanceService', [
                'shop_id' => $shop->id,
                'integration_id' => $integration->id ?? null,
                'market' => $integration->market ?? null
            ]);
        }

        return [
            'app_key' => $appKey,
            'timestamp' => time(),
            'shop_cipher' => $shop->cipher,
            'granularity' => $granularity,
            'start_date_ge' => $startDate,
            'end_date_lt' => $endDate,
            'with_comparison' => $withComparison ? 'true' : 'false',
            'currency' => $currency
        ];
    }

    /**
     * Tạo signature cho API call
     */
    private function generateSignature(array $params, TikTokShop $shop): string
    {
        $integration = $shop->integration;
        $appSecret = $integration ? $integration->getAppSecret() : config('tiktok-shop.app_secret');

        // Fallback nếu vẫn rỗng
        if (empty($appSecret)) {
            $appSecret = config('tiktok-shop.app_secret') ?? env('TIKTOK_SHOP_APP_SECRET');
        }

        if (empty($appSecret)) {
            Log::warning('TikTok app_secret is empty in TikTokShopPerformanceService', [
                'shop_id' => $shop->id,
                'integration_id' => $integration->id ?? null,
                'market' => $integration->market ?? null
            ]);
        }

        // Lọc bỏ các tham số không cần thiết cho signature (theo TikTokSignatureService)
        $filteredParams = array_filter($params, function ($key) {
            return !in_array($key, ['sign', 'access_token']);
        }, ARRAY_FILTER_USE_KEY);

        // Sắp xếp parameters theo key
        ksort($filteredParams);

        // Tạo param string theo TikTok API spec: {key}{value} (không có dấu =)
        $paramString = '';
        foreach ($filteredParams as $key => $value) {
            $paramString .= $key . $value;
        }

        // Tạo input: apiPath + paramString
        $input = self::ENDPOINT . $paramString;

        // Tạo string để sign: app_secret + input + app_secret
        $stringToSign = $appSecret . $input . $appSecret;

        // Tạo signature với HMAC-SHA256 (theo TikTokSignatureService)
        $signature = hash_hmac('sha256', $stringToSign, $appSecret, true);
        $hexSignature = bin2hex($signature);

        Log::info('TikTok Performance Signature Generation', [
            'api_path' => self::ENDPOINT,
            'query_params' => $params,
            'filtered_params' => $filteredParams,
            'param_string' => $paramString,
            'input' => $input,
            'string_to_sign' => $stringToSign,
            'signature_hex' => $hexSignature,
            'app_key' => $params['app_key'] ?? '',
            'app_secret_length' => strlen($appSecret),
            'content_type' => null,
            'body_params' => []
        ]);

        return $hexSignature;
    }

    /**
     * Format dữ liệu performance từ API response
     */
    private function formatPerformanceData(array $data): array
    {
        $performance = $data['performance'] ?? [];
        $intervals = $performance['intervals'] ?? [];
        $comparisonIntervals = $performance['comparison_intervals'] ?? [];

        return [
            'current_period' => $this->formatIntervals($intervals),
            'comparison_period' => $this->formatIntervals($comparisonIntervals),
            'latest_available_date' => $data['latest_available_date'] ?? null,
            'summary' => $this->calculateSummary($intervals)
        ];
    }

    /**
     * Tính GMV cho danh sách đơn theo hệ số
     */
    private function calculateGmvFromOrders($orders, float $multiplier): float
    {
        $total = 0;
        foreach ($orders as $order) {
            $total += $this->calculateOrderGmv($order, $multiplier);
        }
        return $total;
    }

    /**
     * Xây dựng intervals theo ngày cho biểu đồ
     */
    private function buildIntervals($orders, float $multiplier): array
    {
        $grouped = $orders->groupBy(function ($order) {
            return $order->create_time ? $order->create_time->format('Y-m-d') : Carbon::parse($order->created_at)->format('Y-m-d');
        });

        $intervals = [];
        foreach ($grouped as $date => $dayOrders) {
            $gmv = $this->calculateGmvFromOrders($dayOrders, $multiplier);
            $ordersCount = $dayOrders->count();
            $units = $this->sumUnits($dayOrders);
            $buyers = $dayOrders->pluck('buyer_user_id')->filter()->unique()->count();
            $avgOrderValue = $ordersCount > 0 ? $gmv / $ordersCount : 0;

            $intervals[] = [
                'start_date' => $date,
                'end_date' => Carbon::parse($date)->addDay()->format('Y-m-d'),
                'gmv' => [
                    'amount' => round($gmv, 2),
                    'currency' => $dayOrders->first()->currency ?? 'USD'
                ],
                'orders' => $ordersCount,
                'sku_orders' => $ordersCount,
                'units_sold' => $units,
                'buyers' => $buyers,
                'avg_order_value' => [
                    'amount' => round($avgOrderValue, 2),
                    'currency' => $dayOrders->first()->currency ?? 'USD'
                ],
                'product_impressions' => 0,
                'product_page_views' => 0,
                'avg_product_page_visitors' => 0,
                'refunds' => [
                    'amount' => 0,
                    'currency' => $dayOrders->first()->currency ?? 'USD'
                ],
                'cancellations_and_returns' => 0,
                'gmv_breakdowns' => [],
                'buyer_breakdowns' => [],
                'product_impression_breakdowns' => [],
                'product_page_view_breakdowns' => [],
                'avg_product_page_visitor_breakdowns' => []
            ];
        }

        usort($intervals, fn($a, $b) => strcmp($a['start_date'], $b['start_date']));
        return $intervals;
    }

    /**
     * Tính summary từ danh sách đơn
     */
    private function calculateSummaryFromOrders($orders, float $multiplier): array
    {
        $totalGmv = $this->calculateGmvFromOrders($orders, $multiplier);
        $totalOrders = $orders->count();
        $totalUnits = $this->sumUnits($orders);
        $totalBuyers = $orders->pluck('buyer_user_id')->filter()->unique()->count();
        $avgOrderValue = $totalOrders > 0 ? $totalGmv / $totalOrders : 0;

        return [
            'total_gmv' => round($totalGmv, 2),
            'total_orders' => $totalOrders,
            'total_units' => $totalUnits,
            'total_buyers' => $totalBuyers,
            'total_refunds' => 0,
            'total_impressions' => 0,
            'total_page_views' => 0,
            'avg_order_value' => round($avgOrderValue, 2),
            'conversion_rate' => 0,
            'refund_rate' => 0
        ];
    }

    /**
     * Tính GMV của một đơn
     * GMV = (Total - Tax + TikTok Discount - TikTok Shipping Discount) * multiplier
     */
    private function calculateOrderGmv(TikTokOrder $order, float $multiplier): float
    {
        $data = $order->order_data ?? [];
        $total = $this->getNested($data, 'payment.total_amount', $order->total_amount ?? $order->order_amount ?? 0);
        $tax = $this->getNested($data, 'payment.tax_fee', 0);
        $tiktokDiscount = $this->getNested($data, 'payment.platform_discount', 0);
        $shippingDiscountTiktok = $this->getNested($data, 'payment.shipping_discount_platform', 0);

        $gmvBase = floatval($total) - floatval($tax) + floatval($tiktokDiscount) - floatval($shippingDiscountTiktok);
        $gmvBase = max($gmvBase, 0);

        return round($gmvBase * $multiplier, 2);
    }

    /**
     * Đếm units sold từ line_items trong order_data
     */
    private function sumUnits($orders): int
    {
        $total = 0;
        foreach ($orders as $order) {
            $items = $order->order_data['line_items'] ?? [];
            foreach ($items as $item) {
                $total += intval($item['quantity'] ?? 0);
            }
        }
        return $total;
    }

    /**
     * Helper: lấy giá trị lồng nhau
     */
    private function getNested(array $data, string $path, $default = null)
    {
        $segments = explode('.', $path);
        $value = $data;
        foreach ($segments as $segment) {
            if (is_array($value) && array_key_exists($segment, $value)) {
                $value = $value[$segment];
            } else {
                return $default;
            }
        }
        return $value;
    }

    /**
     * Format intervals data
     */
    private function formatIntervals(array $intervals): array
    {
        return array_map(function ($interval) {
            return [
                'start_date' => $interval['start_date'] ?? null,
                'end_date' => $interval['end_date'] ?? null,
                'gmv' => [
                    'amount' => floatval($interval['gmv']['amount'] ?? 0),
                    'currency' => $interval['gmv']['currency'] ?? 'USD'
                ],
                'orders' => intval($interval['orders'] ?? 0),
                'sku_orders' => intval($interval['sku_orders'] ?? 0),
                'units_sold' => intval($interval['units_sold'] ?? 0),
                'buyers' => intval($interval['buyers'] ?? 0),
                'avg_order_value' => [
                    'amount' => floatval($interval['avg_order_value']['amount'] ?? 0),
                    'currency' => $interval['avg_order_value']['currency'] ?? 'USD'
                ],
                'product_impressions' => intval($interval['product_impressions'] ?? 0),
                'product_page_views' => intval($interval['product_page_views'] ?? 0),
                'avg_product_page_visitors' => floatval($interval['avg_product_page_visitors'] ?? 0),
                'refunds' => [
                    'amount' => floatval($interval['refunds']['amount'] ?? 0),
                    'currency' => $interval['refunds']['currency'] ?? 'USD'
                ],
                'cancellations_and_returns' => intval($interval['cancellations_and_returns'] ?? 0),
                'gmv_breakdowns' => $interval['gmv_breakdowns'] ?? [],
                'buyer_breakdowns' => $interval['buyer_breakdowns'] ?? [],
                'product_impression_breakdowns' => $interval['product_impression_breakdowns'] ?? [],
                'product_page_view_breakdowns' => $interval['product_page_view_breakdowns'] ?? [],
                'avg_product_page_visitor_breakdowns' => $interval['avg_product_page_visitor_breakdowns'] ?? []
            ];
        }, $intervals);
    }

    /**
     * Tính toán summary metrics
     */
    private function calculateSummary(array $intervals): array
    {
        $totalGmv = 0;
        $totalOrders = 0;
        $totalUnits = 0;
        $totalBuyers = 0;
        $totalRefunds = 0;
        $totalImpressions = 0;
        $totalPageViews = 0;

        foreach ($intervals as $interval) {
            $totalGmv += floatval($interval['gmv']['amount'] ?? 0);
            $totalOrders += intval($interval['orders'] ?? 0);
            $totalUnits += intval($interval['units_sold'] ?? 0);
            $totalBuyers += intval($interval['buyers'] ?? 0);
            $totalRefunds += floatval($interval['refunds']['amount'] ?? 0);
            $totalImpressions += intval($interval['product_impressions'] ?? 0);
            $totalPageViews += intval($interval['product_page_views'] ?? 0);
        }

        $avgOrderValue = $totalOrders > 0 ? $totalGmv / $totalOrders : 0;
        $conversionRate = $totalImpressions > 0 ? ($totalPageViews / $totalImpressions) * 100 : 0;
        $refundRate = $totalGmv > 0 ? ($totalRefunds / $totalGmv) * 100 : 0;

        return [
            'total_gmv' => $totalGmv,
            'total_orders' => $totalOrders,
            'total_units' => $totalUnits,
            'total_buyers' => $totalBuyers,
            'total_refunds' => $totalRefunds,
            'total_impressions' => $totalImpressions,
            'total_page_views' => $totalPageViews,
            'avg_order_value' => $avgOrderValue,
            'conversion_rate' => $conversionRate,
            'refund_rate' => $refundRate
        ];
    }
}
