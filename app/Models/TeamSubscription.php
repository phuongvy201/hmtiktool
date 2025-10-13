<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeamSubscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'team_id',
        'service_package_id',
        'assigned_by',
        'start_date',
        'end_date',
        'status',
        'paid_amount',
        'payment_method',
        'transaction_id',
        'notes',
        'auto_renew',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'paid_amount' => 'decimal:2',
        'auto_renew' => 'boolean',
    ];

    /**
     * Get the team that owns the subscription.
     */
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    /**
     * Get the service package for this subscription.
     */
    public function servicePackage(): BelongsTo
    {
        return $this->belongsTo(ServicePackage::class);
    }

    /**
     * Get the user who assigned this subscription.
     */
    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    /**
     * Scope a query to only include active subscriptions.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope a query to only include expired subscriptions.
     */
    public function scopeExpired($query)
    {
        return $query->where('status', 'expired');
    }

    /**
     * Check if subscription is active.
     */
    public function isActive(): bool
    {
        return $this->status === 'active' && $this->end_date->isFuture();
    }

    /**
     * Check if subscription is expired.
     */
    public function isExpired(): bool
    {
        return $this->end_date->isPast();
    }

    /**
     * Get remaining days.
     */
    public function getRemainingDaysAttribute(): int
    {
        return max(0, now()->diffInDays($this->end_date, false));
    }

    /**
     * Get formatted paid amount.
     */
    public function getFormattedPaidAmountAttribute(): string
    {
        if (!$this->paid_amount) {
            return 'Miễn phí';
        }
        return number_format($this->paid_amount, 0, ',', '.') . ' VND';
    }

    /**
     * Get status badge class.
     */
    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'active' => 'bg-green-100 text-green-800',
            'expired' => 'bg-red-100 text-red-800',
            'cancelled' => 'bg-gray-100 text-gray-800',
            'pending' => 'bg-yellow-100 text-yellow-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    /**
     * Get status text in Vietnamese.
     */
    public function getStatusTextAttribute(): string
    {
        return match ($this->status) {
            'active' => 'Đang hoạt động',
            'expired' => 'Đã hết hạn',
            'cancelled' => 'Đã hủy',
            'pending' => 'Chờ xử lý',
            default => 'Không xác định',
        };
    }
}
