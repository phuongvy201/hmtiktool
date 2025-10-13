<?php

namespace App\Services;

use App\Models\TikTokShop;
use App\Models\TikTokPayment;
use App\Services\TikTokSignatureService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
// use PhpOffice\PhpSpreadsheet\Spreadsheet;
// use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
// use PhpOffice\PhpSpreadsheet\Style\Alignment;
// use PhpOffice\PhpSpreadsheet\Style\Border;
// use PhpOffice\PhpSpreadsheet\Style\Fill;
use Exception;

class TikTokFinanceService
{
    private const API_VERSION = '202309';

    /**
     * Lấy danh sách payments từ TikTok API
     */
    public function getPayments(TikTokShop $shop, array $filters = []): array
    {
        Log::info('Getting payments from TikTok API', [
            'shop_id' => $shop->id,
            'filters' => $filters
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

            // Chuẩn bị query parameters
            $queryParams = [
                'app_key' => $appKey,
                'timestamp' => $timestamp,
                'shop_cipher' => $shopCipher,
                'sort_field' => 'create_time',
                'sort_order' => $filters['sort_order'] ?? 'DESC',
                'page_size' => $filters['page_size'] ?? 20
            ];

            // Thêm filter thời gian
            if (!empty($filters['date_from'])) {
                $queryParams['create_time_ge'] = strtotime($filters['date_from']);
            }
            if (!empty($filters['date_to'])) {
                $queryParams['create_time_lt'] = strtotime($filters['date_to'] . ' 23:59:59');
            }

            // Tạo signature
            $signature = TikTokSignatureService::generateFinancePaymentsSignature(
                $appKey,
                $appSecret,
                $timestamp,
                $shopCipher,
                $queryParams
            );
            $queryParams['sign'] = $signature;

            $url = 'https://open-api.tiktokglobalshop.com/finance/' . self::API_VERSION . '/payments?' . http_build_query($queryParams);

            Log::info('Calling TikTok Finance API', [
                'shop_id' => $shop->id,
                'url' => $url,
                'query_params' => $queryParams
            ]);

            // Gọi API
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'x-tts-access-token' => $integration->access_token
            ])->timeout(30)->get($url);

            $httpCode = $response->status();
            $responseData = $response->json();

            Log::info('TikTok Finance API Response', [
                'shop_id' => $shop->id,
                'http_code' => $httpCode,
                'response' => $responseData
            ]);

            if ($httpCode === 200 && isset($responseData['code']) && $responseData['code'] === 0) {
                $payments = $responseData['data']['payments'] ?? [];

                // Tính tổng dựa trên cấu trúc API thực tế
                $totalAmount = 0;
                $totalReserve = 0;
                $totalSettle = 0;

                foreach ($payments as $payment) {
                    $totalAmount += floatval($payment['amount']['value'] ?? 0);
                    $totalReserve += floatval($payment['reserve_amount']['value'] ?? 0);
                    $totalSettle += floatval($payment['settlement_amount']['value'] ?? 0);
                }

                return [
                    'success' => true,
                    'data' => [
                        'payments' => $payments,
                        'total_amount' => $totalAmount,
                        'total_reserve' => $totalReserve,
                        'total_settle' => $totalSettle,
                        'has_more' => $responseData['data']['more'] ?? false,
                        'next_page_token' => $responseData['data']['next_page_token'] ?? null
                    ],
                    'message' => 'Lấy danh sách payments thành công'
                ];
            } else {
                $errorMessage = $responseData['message'] ?? 'Unknown error';
                $errorCode = $responseData['code'] ?? 'Unknown code';

                Log::error('TikTok Finance API Error', [
                    'shop_id' => $shop->id,
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
            Log::error('Exception in TikTokFinanceService::getPayments', [
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
     * Lấy payments từ database
     */
    public function getPaymentsFromDatabase(TikTokShop $shop, array $filters = []): array
    {
        try {
            $query = TikTokPayment::forShop($shop->id);

            // Filter by date range
            if (!empty($filters['date_from']) && !empty($filters['date_to'])) {
                $query->inDateRange($filters['date_from'], $filters['date_to']);
            }

            // Filter by status
            if (!empty($filters['status'])) {
                $query->withStatus($filters['status']);
            }

            // Sort order
            $sortOrder = $filters['sort_order'] ?? 'DESC';
            $query->orderBy('create_time', $sortOrder);

            // Pagination
            $pageSize = $filters['page_size'] ?? 20;
            $payments = $query->paginate($pageSize);

            // Calculate totals
            $totalAmount = $payments->sum('amount_value');
            $totalReserve = $payments->sum('reserve_amount_value');
            $totalSettle = $payments->sum('settlement_amount_value');

            return [
                'success' => true,
                'data' => [
                    'payments' => $payments->items(),
                    'total_amount' => $totalAmount,
                    'total_reserve' => $totalReserve,
                    'total_settle' => $totalSettle,
                    'pagination' => [
                        'current_page' => $payments->currentPage(),
                        'last_page' => $payments->lastPage(),
                        'per_page' => $payments->perPage(),
                        'total' => $payments->total(),
                        'has_more' => $payments->hasMorePages()
                    ]
                ],
                'message' => 'Lấy danh sách payments từ database thành công'
            ];
        } catch (\Exception $e) {
            Log::error('Error getting payments from database', [
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
     * Lấy payments từ database cho nhiều shops
     */
    public function getPaymentsFromMultipleShops($shops, array $filters = []): array
    {
        try {
            $shopIds = $shops->pluck('id')->toArray();
            
            if (empty($shopIds)) {
                return [
                    'success' => true,
                    'data' => [
                        'payments' => [],
                        'total_amount' => 0,
                        'total_reserve' => 0,
                        'total_settle' => 0,
                        'pagination' => [
                            'current_page' => 1,
                            'last_page' => 1,
                            'per_page' => 20,
                            'total' => 0,
                            'has_more' => false
                        ]
                    ],
                    'message' => 'Không có shops nào để lấy payments'
                ];
            }

            $query = TikTokPayment::whereIn('tiktok_shop_id', $shopIds);

            // Filter by date range
            if (!empty($filters['date_from']) && !empty($filters['date_to'])) {
                $query->inDateRange($filters['date_from'], $filters['date_to']);
            }

            // Filter by status
            if (!empty($filters['status'])) {
                $query->withStatus($filters['status']);
            }

            // Sort order
            $sortOrder = $filters['sort_order'] ?? 'DESC';
            $query->orderBy('create_time', $sortOrder);

            // Pagination
            $pageSize = $filters['page_size'] ?? 20;
            $payments = $query->paginate($pageSize);

            // Calculate totals
            $totalAmount = $payments->sum('amount_value');
            $totalReserve = $payments->sum('reserve_amount_value');
            $totalSettle = $payments->sum('settlement_amount_value');

            return [
                'success' => true,
                'data' => [
                    'payments' => $payments->items(),
                    'total_amount' => $totalAmount,
                    'total_reserve' => $totalReserve,
                    'total_settle' => $totalSettle,
                    'pagination' => [
                        'current_page' => $payments->currentPage(),
                        'last_page' => $payments->lastPage(),
                        'per_page' => $payments->perPage(),
                        'total' => $payments->total(),
                        'has_more' => $payments->hasMorePages()
                    ]
                ],
                'message' => 'Lấy danh sách payments từ database thành công'
            ];
        } catch (\Exception $e) {
            Log::error('Error getting payments from multiple shops', [
                'shop_ids' => $shopIds ?? [],
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
     * Export payments to Excel (temporary CSV implementation)
     */
    public function exportToExcel(array $payments, TikTokShop $shop, array $filters)
    {
        try {
            // Generate filename
            $filename = 'tiktok_payments_' . $shop->shop_name . '_' . date('Y-m-d_H-i-s') . '.csv';

            // Create CSV content
            $csvContent = "ID,Shop Name,Shop Profile,Date Created,Date Paid,Status,Amount,Reserve,Settle,Bank Account\n";

            foreach ($payments as $payment) {
                $csvContent .= sprintf(
                    "%s,%s,%s,%s,%s,%s,%s,%s,%s,%s\n",
                    $payment['id'] ?? '',
                    $shop->shop_name,
                    $shop->shop_profile ?? '',
                    isset($payment['create_time']) ? date('m/d/Y, g:i A', $payment['create_time']) : '',
                    isset($payment['paid_time']) ? date('m/d/Y, g:i A', $payment['paid_time']) : '',
                    $payment['status'] ?? '',
                    number_format($payment['amount'] ?? 0, 2),
                    number_format($payment['reserve'] ?? 0, 2),
                    number_format($payment['settle'] ?? 0, 2),
                    $this->maskBankAccount($payment['bank_account'] ?? '')
                );
            }

            // Return download response
            return response($csvContent, 200, [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"'
            ]);
        } catch (\Exception $e) {
            Log::error('Error exporting payments to CSV', [
                'shop_id' => $shop->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            throw new Exception('Không thể tạo file CSV: ' . $e->getMessage());
        }
    }

    /**
     * Mask bank account number
     */
    private function maskBankAccount(string $account): string
    {
        if (empty($account)) {
            return '';
        }

        if (strlen($account) <= 4) {
            return str_repeat('*', strlen($account));
        }

        return str_repeat('*', strlen($account) - 4) . substr($account, -4);
    }
}
