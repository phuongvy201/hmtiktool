<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PublishHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'user_id',
        'team_id',
        'tiktok_product_id',
        'tiktok_shop_id',
        'status',
        'action',
        'request_data',
        'response_data',
        'error_message',
        'published_at',
        'failed_at',
        'retry_count',
        'next_retry_at',
    ];

    protected $casts = [
        'request_data' => 'array',
        'response_data' => 'array',
        'published_at' => 'datetime',
        'failed_at' => 'datetime',
        'next_retry_at' => 'datetime',
    ];

    /**
     * Get the product that owns the publish history.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the user that owns the publish history.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the team that owns the publish history.
     */
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    /**
     * Scope to get pending publish histories.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope to get processing publish histories.
     */
    public function scopeProcessing($query)
    {
        return $query->where('status', 'processing');
    }

    /**
     * Scope to get successful publish histories.
     */
    public function scopeSuccessful($query)
    {
        return $query->where('status', 'success');
    }

    /**
     * Scope to get failed publish histories.
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * Scope to get publish histories ready for retry.
     */
    public function scopeReadyForRetry($query)
    {
        return $query->where('status', 'failed')
            ->where('retry_count', '<', 3)
            ->where(function ($q) {
                $q->whereNull('next_retry_at')
                    ->orWhere('next_retry_at', '<=', now());
            });
    }

    /**
     * Mark as processing.
     */
    public function markAsProcessing()
    {
        $this->update(['status' => 'processing']);
    }

    /**
     * Mark as successful.
     */
    public function markAsSuccessful($tiktokProductId = null, $responseData = null)
    {
        $this->update([
            'status' => 'success',
            'published_at' => now(),
            'tiktok_product_id' => $tiktokProductId,
            'response_data' => $responseData,
        ]);
    }

    /**
     * Mark as failed.
     */
    public function markAsFailed($errorMessage = null, $responseData = null)
    {
        $this->update([
            'status' => 'failed',
            'failed_at' => now(),
            'error_message' => $errorMessage,
            'response_data' => $responseData,
            'retry_count' => $this->retry_count + 1,
            'next_retry_at' => now()->addMinutes(pow(2, $this->retry_count)), // Exponential backoff
        ]);
    }

    /**
     * Check if can be retried.
     */
    public function canBeRetried(): bool
    {
        return $this->status === 'failed' && $this->retry_count < 3;
    }

    /**
     * Get status display name.
     */
    public function getStatusDisplayAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'Chờ xử lý',
            'processing' => 'Đang xử lý',
            'success' => 'Thành công',
            'failed' => 'Thất bại',
            default => 'Không xác định',
        };
    }

    /**
     * Get action display name.
     */
    public function getActionDisplayAttribute(): string
    {
        return match ($this->action) {
            'create' => 'Tạo mới',
            'update' => 'Cập nhật',
            'delete' => 'Xóa',
            default => 'Không xác định',
        };
    }
}
