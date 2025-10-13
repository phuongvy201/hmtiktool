<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class TikTokPayment extends Model
{
    use HasFactory;

    protected $table = 'tiktok_payments';

    protected $fillable = [
        'payment_id',
        'tiktok_shop_id',
        'shop_name',
        'shop_profile',
        'create_time',
        'paid_time',
        'status',
        'amount_value',
        'amount_currency',
        'settlement_amount_value',
        'settlement_amount_currency',
        'reserve_amount_value',
        'reserve_amount_currency',
        'payment_amount_before_exchange_value',
        'payment_amount_before_exchange_currency',
        'exchange_rate',
        'bank_account',
        'payment_data',
        'last_synced_at',
    ];

    protected $casts = [
        'create_time' => 'integer',
        'paid_time' => 'integer',
        'amount_value' => 'decimal:2',
        'settlement_amount_value' => 'decimal:2',
        'reserve_amount_value' => 'decimal:2',
        'payment_amount_before_exchange_value' => 'decimal:2',
        'exchange_rate' => 'decimal:6',
        'payment_data' => 'array',
        'last_synced_at' => 'datetime',
    ];

    /**
     * Get the TikTok shop that owns the payment
     */
    public function tiktokShop(): BelongsTo
    {
        return $this->belongsTo(TikTokShop::class);
    }

    /**
     * Get formatted create time
     */
    public function getCreateTimeFormattedAttribute(): string
    {
        return $this->create_time ? Carbon::createFromTimestamp($this->create_time)->format('m/d/Y, g:i A') : 'N/A';
    }

    /**
     * Get formatted paid time
     */
    public function getPaidTimeFormattedAttribute(): string
    {
        return $this->paid_time ? Carbon::createFromTimestamp($this->paid_time)->format('m/d/Y, g:i A') : 'N/A';
    }

    /**
     * Get status badge classes
     */
    public function getStatusClassesAttribute(): string
    {
        return match ($this->status) {
            'PAID' => 'inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-900 text-green-200',
            'PENDING' => 'inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-yellow-900 text-yellow-200',
            'FAILED' => 'inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-red-900 text-red-200',
            default => 'inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-gray-600 text-gray-300'
        };
    }

    /**
     * Get status icon
     */
    public function getStatusIconAttribute(): string
    {
        return match ($this->status) {
            'PAID' => 'fas fa-check-circle',
            'PENDING' => 'fas fa-clock',
            'FAILED' => 'fas fa-times-circle',
            default => 'fas fa-question-circle'
        };
    }

    /**
     * Get formatted amount with currency
     */
    public function getFormattedAmountAttribute(): string
    {
        return number_format($this->amount_value, 2) . ' ' . $this->amount_currency;
    }

    /**
     * Get formatted settlement amount with currency
     */
    public function getFormattedSettlementAmountAttribute(): string
    {
        return number_format($this->settlement_amount_value, 2) . ' ' . $this->settlement_amount_currency;
    }

    /**
     * Get formatted reserve amount with currency
     */
    public function getFormattedReserveAmountAttribute(): string
    {
        return number_format($this->reserve_amount_value, 2) . ' ' . $this->reserve_amount_currency;
    }

    /**
     * Get masked bank account
     */
    public function getMaskedBankAccountAttribute(): string
    {
        if (empty($this->bank_account)) {
            return 'N/A';
        }

        if (strlen($this->bank_account) <= 4) {
            return str_repeat('*', strlen($this->bank_account));
        }

        return str_repeat('*', strlen($this->bank_account) - 4) . substr($this->bank_account, -4);
    }

    /**
     * Scope for filtering by shop
     */
    public function scopeForShop($query, $shopId)
    {
        return $query->where('tiktok_shop_id', $shopId);
    }

    /**
     * Scope for filtering by status
     */
    public function scopeWithStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope for filtering by date range
     */
    public function scopeInDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('create_time', [
            strtotime($startDate),
            strtotime($endDate . ' 23:59:59')
        ]);
    }

    /**
     * Scope for recent payments
     */
    public function scopeRecent($query, $days = 30)
    {
        $timestamp = strtotime("-{$days} days");
        return $query->where('create_time', '>=', $timestamp);
    }
}
