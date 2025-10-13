<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TikTokProductUploadHistory extends Model
{
    use HasFactory;

    protected $table = 'tiktok_product_upload_history';

    protected $fillable = [
        'user_id',
        'user_name',
        'product_id',
        'product_name',
        'tiktok_shop_id',
        'shop_name',
        'shop_cipher',
        'status',
        'error_message',
        'response_data',
        'tiktok_product_id',
        'tiktok_skus',
        'idempotency_key',
        'uploaded_at',
        'request_data',
    ];

    protected $casts = [
        'response_data' => 'array',
        'tiktok_skus' => 'array',
        'request_data' => 'array',
        'uploaded_at' => 'datetime',
    ];

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function tiktokShop(): BelongsTo
    {
        return $this->belongsTo(TikTokShop::class, 'tiktok_shop_id');
    }

    // Scopes
    public function scopeSuccessful($query)
    {
        return $query->where('status', 'success');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByProduct($query, $productId)
    {
        return $query->where('product_id', $productId);
    }

    public function scopeByShop($query, $shopId)
    {
        return $query->where('tiktok_shop_id', $shopId);
    }

    public function scopeRecent($query, $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    // Helper methods
    public function isSuccessful(): bool
    {
        return $this->status === 'success';
    }

    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'success' => '<span class="badge bg-success">Thành công</span>',
            'failed' => '<span class="badge bg-danger">Thất bại</span>',
            'pending' => '<span class="badge bg-warning">Đang xử lý</span>',
            default => '<span class="badge bg-secondary">Không xác định</span>'
        };
    }

    public function getDurationAttribute(): ?string
    {
        if (!$this->uploaded_at) {
            return null;
        }

        $duration = $this->uploaded_at->diffInSeconds($this->created_at);

        if ($duration < 60) {
            return $duration . ' giây';
        } elseif ($duration < 3600) {
            return round($duration / 60) . ' phút';
        } else {
            return round($duration / 3600, 1) . ' giờ';
        }
    }
}
