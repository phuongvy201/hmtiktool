<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class TikTokShopIntegration extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'tiktok_shop_integrations';

    protected $fillable = [
        'team_id',
        'name',
        'description',
        'access_token',
        'refresh_token',
        'access_token_expires_at',
        'refresh_token_expires_at',
        'status',
        'error_message',
        'additional_data',
    ];

    protected $casts = [
        'access_token_expires_at' => 'integer',
        'refresh_token_expires_at' => 'integer',
        'additional_data' => 'array',
    ];

    protected $hidden = [
        'access_token',
        'refresh_token',
    ];

    /**
     * Get the team that owns the integration.
     */
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    /**
     * Get the shops for this integration.
     */
    public function shops(): HasMany
    {
        return $this->hasMany(TikTokShop::class, 'tiktok_shop_integration_id');
    }

    /**
     * Get active shops for this integration.
     */
    public function activeShops(): HasMany
    {
        return $this->hasMany(TikTokShop::class, 'tiktok_shop_integration_id')
            ->where('status', 'active');
    }

    /**
     * Check if access token is expired
     */
    public function isAccessTokenExpired(): bool
    {
        if (!$this->access_token_expires_at) {
            return true;
        }

        // Consider token expired if it expires within 5 minutes
        return $this->access_token_expires_at < (time() + 300);
    }

    /**
     * Check if access token will expire soon (within specified minutes)
     */
    public function isAccessTokenExpiringSoon(int $minutes = 30): bool
    {
        if (!$this->access_token_expires_at) {
            return true;
        }

        return $this->access_token_expires_at < (time() + ($minutes * 60));
    }

    /**
     * Check if refresh token is expired
     */
    public function isRefreshTokenExpired(): bool
    {
        if (!$this->refresh_token_expires_at) {
            return true;
        }
        return $this->refresh_token_expires_at < time();
    }

    /**
     * Check if integration is active
     */
    public function isActive(): bool
    {
        return $this->status === 'active' && !$this->isAccessTokenExpired();
    }

    /**
     * Check if integration needs token refresh
     */
    public function needsTokenRefresh(): bool
    {
        if (!$this->status === 'active' || !$this->access_token || !$this->refresh_token) {
            return false;
        }

        if ($this->isRefreshTokenExpired()) {
            return false;
        }

        // Refresh if expires within 24 hours (86400 seconds)
        if (!$this->access_token_expires_at) {
            return true;
        }

        return $this->access_token_expires_at < (time() + 86400);
    }

    /**
     * Check if integration can be refreshed
     */
    public function canRefreshToken(): bool
    {
        return in_array($this->status, ['active', 'error']) &&
            $this->access_token &&
            $this->refresh_token &&
            !$this->isRefreshTokenExpired();
    }

    /**
     * Get remaining days for access token
     */
    public function getAccessTokenRemainingDaysAttribute(): int
    {
        if (!$this->access_token_expires_at) {
            return 0;
        }

        $remainingSeconds = $this->access_token_expires_at - time();
        return max(0, (int) ($remainingSeconds / 86400));
    }

    /**
     * Get formatted access token expires at
     */
    public function getFormattedAccessTokenExpiresAtAttribute(): string
    {
        if (!$this->access_token_expires_at) {
            return 'Không xác định';
        }

        return date('d/m/Y H:i:s', $this->access_token_expires_at);
    }

    /**
     * Get formatted refresh token expires at
     */
    public function getFormattedRefreshTokenExpiresAtAttribute(): string
    {
        if (!$this->refresh_token_expires_at) {
            return 'Không xác định';
        }

        return date('d/m/Y H:i:s', $this->refresh_token_expires_at);
    }

    /**
     * Get token status information
     */
    public function getTokenStatusAttribute(): array
    {
        return [
            'access_token' => [
                'exists' => !empty($this->access_token),
                'expires_at' => $this->access_token_expires_at,
                'expires_at_formatted' => $this->formatted_access_token_expires_at,
                'is_expired' => $this->isAccessTokenExpired(),
                'is_expiring_soon' => $this->isAccessTokenExpiringSoon(),
                'remaining_days' => $this->access_token_remaining_days,
                'remaining_seconds' => $this->access_token_expires_at ? max(0, $this->access_token_expires_at - time()) : 0,
            ],
            'refresh_token' => [
                'exists' => !empty($this->refresh_token),
                'expires_at' => $this->refresh_token_expires_at,
                'expires_at_formatted' => $this->formatted_refresh_token_expires_at,
                'is_expired' => $this->isRefreshTokenExpired(),
                'remaining_seconds' => $this->refresh_token_expires_at ? max(0, $this->refresh_token_expires_at - time()) : 0,
            ],
            'integration' => [
                'status' => $this->status,
                'is_active' => $this->isActive(),
                'needs_refresh' => $this->needsTokenRefresh(),
                'can_refresh' => $this->canRefreshToken(),
                'error_message' => $this->error_message,
            ]
        ];
    }

    /**
     * Get status badge class
     */
    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'active' => 'bg-green-500/20 text-green-400 border-green-500/50',
            'pending' => 'bg-yellow-500/20 text-yellow-400 border-yellow-500/50',
            'error' => 'bg-red-500/20 text-red-400 border-red-500/50',
            'disconnected' => 'bg-gray-500/20 text-gray-400 border-gray-500/50',
            default => 'bg-gray-500/20 text-gray-400 border-gray-500/50',
        };
    }

    /**
     * Get status text
     */
    public function getStatusTextAttribute(): string
    {
        return match ($this->status) {
            'active' => 'Hoạt động',
            'pending' => 'Chờ kết nối',
            'error' => 'Lỗi',
            'disconnected' => 'Đã ngắt kết nối',
            default => 'Không xác định',
        };
    }

    /**
     * Update tokens from API response
     */
    public function updateTokens(array $data): void
    {
        Log::info('=== START UPDATE TOKENS ===');
        Log::info('Input data keys:', array_keys($data));

        // Xử lý access_token_expire_in (Unix timestamp từ TikTok)
        $accessTokenExpiresAt = null;
        if (isset($data['access_token_expire_in'])) {
            $expireTimestamp = (int) $data['access_token_expire_in'];
            Log::info('Processing access_token_expire_in from TikTok:', ['original_value' => $expireTimestamp]);

            // TikTok trả về Unix timestamp (seconds), không cần chia cho 1000
            // Validate timestamp hợp lệ (không quá xa trong tương lai)
            $maxFutureTimestamp = time() + (86400 * 365 * 10); // 10 năm từ bây giờ
            if ($expireTimestamp > $maxFutureTimestamp) {
                Log::warning('Access token expiry timestamp seems too far in future, capping to 10 years');
                $expireTimestamp = $maxFutureTimestamp;
            }

            $accessTokenExpiresAt = $expireTimestamp;
            Log::info('Using TikTok access_token_expires_at:', [
                'current_time' => time(),
                'tiktok_timestamp' => $expireTimestamp,
                'expires_at_formatted' => date('Y-m-d H:i:s', $expireTimestamp),
                'hours_until_expiry' => ($expireTimestamp - time()) / 3600
            ]);
        }

        // Xử lý refresh_token_expire_in (Unix timestamp từ TikTok)
        $refreshTokenExpiresAt = null;
        if (isset($data['refresh_token_expire_in'])) {
            $expireTimestamp = (int) $data['refresh_token_expire_in'];
            Log::info('Processing refresh_token_expire_in from TikTok:', ['original_value' => $expireTimestamp]);

            // TikTok trả về Unix timestamp (seconds), không cần chia cho 1000
            // Validate timestamp hợp lệ (không quá xa trong tương lai)
            $maxFutureTimestamp = time() + (86400 * 365 * 10); // 10 năm từ bây giờ
            if ($expireTimestamp > $maxFutureTimestamp) {
                Log::warning('Refresh token expiry timestamp seems too far in  future, capping to 10 years');
                $expireTimestamp = $maxFutureTimestamp;
            }

            $refreshTokenExpiresAt = $expireTimestamp;
            Log::info('Using TikTok refresh_token_expires_at:', [
                'current_time' => time(),
                'tiktok_timestamp' => $expireTimestamp,
                'expires_at_formatted' => date('Y-m-d H:i:s', $expireTimestamp),
                'hours_until_expiry' => ($expireTimestamp - time()) / 3600
            ]);
        }

        // Xử lý additional_data để lưu s_token và các thông tin khác
        $additionalData = $this->additional_data ?? [];

        // Lưu s_token nếu có
        if (isset($data['s_token'])) {
            $additionalData['s_token'] = $data['s_token'];
            Log::info('Saved s_token to additional_data', [
                's_token_length' => strlen($data['s_token']),
                'user_type' => $data['user_type'] ?? 'unknown'
            ]);
        }

        // Lưu user_type nếu có
        if (isset($data['user_type'])) {
            $additionalData['user_type'] = $data['user_type'];
        }

        // Lưu các thông tin khác từ response
        $additionalData['last_token_update'] = time();
        $additionalData['token_response_keys'] = array_keys($data);

        $updateData = [
            'access_token' => $data['access_token'] ?? $this->access_token,
            'refresh_token' => $data['refresh_token'] ?? $this->refresh_token,
            'access_token_expires_at' => $accessTokenExpiresAt ?? $this->access_token_expires_at,
            'refresh_token_expires_at' => $refreshTokenExpiresAt ?? $this->refresh_token_expires_at,
            'additional_data' => $additionalData,
            'status' => 'active',
            'error_message' => null,
        ];

        Log::info('Updating integration with data:', [
            'has_access_token' => !empty($updateData['access_token']),
            'has_refresh_token' => !empty($updateData['refresh_token']),
            'access_token_expires_at' => $updateData['access_token_expires_at'],
            'refresh_token_expires_at' => $updateData['refresh_token_expires_at'],
            'status' => $updateData['status']
        ]);

        $this->update($updateData);

        Log::info('=== END UPDATE TOKENS ===');
    }

    /**
     * Mark integration as error
     */
    public function markAsError(string $errorMessage): void
    {
        $this->update([
            'status' => 'error',
            'error_message' => $errorMessage,
        ]);
    }

    /**
     * Get authorization URL for OAuth flow
     */
    public function getAuthorizationUrl(): string
    {
        $params = [
            'app_key' => $this->getAppKey(),
            'state' => $this->team_id,
            'redirect_uri' => route('team.tiktok-shop.callback'),
            'scope' => 'seller.authorization.info,seller.shop.info,seller.product.basic,seller.order.info,seller.fulfillment.basic,seller.logistics,seller.delivery.status.write,seller.finance.info,seller.product.delete,seller.product.write,seller.product.optimize',
        ];

        return 'https://auth.tiktok-shops.com/oauth/authorize?' . http_build_query($params);
    }

    /**
     * Get App Key from system configuration
     */
    public function getAppKey(): string
    {
        // Lấy từ system configuration hoặc environment
        return config('tiktok-shop.app_key') ?? env('TIKTOK_SHOP_APP_KEY');
    }

    /**
     * Get App Secret from system configuration
     */
    public function getAppSecret(): string
    {
        // Lấy từ system configuration hoặc environment
        return config('tiktok-shop.app_secret') ?? env('TIKTOK_SHOP_APP_SECRET');
    }

    /**
     * Refresh access token using refresh token
     */
    public function refreshAccessToken(): array
    {
        Log::info('=== START REFRESH TOKEN ===', [
            'integration_id' => $this->id,
            'team_id' => $this->team_id
        ]);

        try {
            // Kiểm tra refresh token có hợp lệ không
            if (!$this->refresh_token) {
                throw new \Exception('Refresh token không tồn tại');
            }

            if ($this->isRefreshTokenExpired()) {
                throw new \Exception('Refresh token đã hết hạn');
            }

            // Chuẩn bị dữ liệu request
            $requestData = [
                'app_key' => $this->getAppKey(),
                'app_secret' => $this->getAppSecret(),
                'refresh_token' => $this->refresh_token,
                'grant_type' => 'refresh_token'
            ];

            Log::info('Refresh token request data', [
                'app_key' => $this->getAppKey(),
                'has_refresh_token' => !empty($this->refresh_token),
                'grant_type' => 'refresh_token'
            ]);

            // Gọi API refresh token
            $response = $this->callRefreshTokenAPI($requestData);

            if ($response['success']) {
                // Cập nhật tokens mới
                $this->updateTokens($response['data']);

                Log::info('Token refresh successful', [
                    'integration_id' => $this->id,
                    'team_id' => $this->team_id,
                    'new_access_token_expires_at' => $this->access_token_expires_at,
                    'new_refresh_token_expires_at' => $this->refresh_token_expires_at
                ]);

                return [
                    'success' => true,
                    'message' => 'Token refresh thành công',
                    'data' => [
                        'access_token_expires_at' => $this->access_token_expires_at,
                        'refresh_token_expires_at' => $this->refresh_token_expires_at,
                        'formatted_access_expires' => $this->formatted_access_token_expires_at,
                        'formatted_refresh_expires' => $this->formatted_refresh_token_expires_at
                    ]
                ];
            } else {
                throw new \Exception($response['message'] ?? 'Lỗi không xác định khi refresh token');
            }
        } catch (\Exception $e) {
            Log::error('Token refresh failed', [
                'integration_id' => $this->id,
                'team_id' => $this->team_id,
                'error' => $e->getMessage()
            ]);

            // Đánh dấu integration có lỗi
            $this->markAsError('Token refresh failed: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        } finally {
            Log::info('=== END REFRESH TOKEN ===');
        }
    }

    /**
     * Call TikTok Shop refresh token API
     */
    private function callRefreshTokenAPI(array $data): array
    {
        $url = 'https://auth.tiktok-shops.com/api/v2/token/refresh';

        // Sử dụng form-encoded data
        $formData = http_build_query($data);

        // Sử dụng GET request với query parameters
        $urlWithParams = $url . '?' . $formData;

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $urlWithParams,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPGET => true,
            CURLOPT_HTTPHEADER => [
                'Accept: application/json'
            ],
            CURLOPT_TIMEOUT => 30,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            throw new \Exception("cURL Error: {$error}");
        }

        if ($httpCode !== 200) {
            throw new \Exception("HTTP Error: {$httpCode}");
        }

        $responseData = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception('Invalid JSON response: ' . json_last_error_msg());
        }

        Log::info('TikTok Refresh Token API Request', [
            'url' => $url,
            'request_data' => $data,
            'form_data' => $formData
        ]);

        Log::info('TikTok API response', [
            'http_code' => $httpCode,
            'response_keys' => array_keys($responseData ?? []),
            'code' => $responseData['code'] ?? null,
            'message' => $responseData['message'] ?? null,
            'full_response' => $responseData
        ]);

        if (isset($responseData['code']) && $responseData['code'] === 0) {
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
     * Get time until access token expires (in hours)
     */
    public function getHoursUntilExpiry(): float
    {
        if (!$this->access_token_expires_at) {
            return 0;
        }

        $secondsUntilExpiry = $this->access_token_expires_at - time();
        return max(0, $secondsUntilExpiry / 3600);
    }

    /**
     * Upload sản phẩm lên TikTok Shop
     */
    public function uploadProduct(\App\Models\Product $product, \App\Models\TikTokShop $shop): array
    {
        $productService = new \App\Services\TikTokShopProductService();
        return $productService->uploadProduct($product, $shop);
    }
}
