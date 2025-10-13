<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TikTokShop extends Model
{
    use HasFactory;
    protected $table = 'tiktok_shops';

    protected $fillable = [
        'team_id',
        'tiktok_shop_integration_id',
        'shop_id',
        'shop_name',
        'seller_name',
        'seller_region',
        'open_id',
        'cipher',
        'status',
        'shop_data',
    ];

    protected $casts = [
        'shop_data' => 'array',
    ];

    /**
     * Get the team that owns the shop.
     */
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    /**
     * Get the integration that owns the shop.
     */
    public function integration(): BelongsTo
    {
        return $this->belongsTo(TikTokShopIntegration::class, 'tiktok_shop_integration_id');
    }

    /**
     * Get the sellers for this shop.
     */
    public function sellers(): HasMany
    {
        return $this->hasMany(TikTokShopSeller::class, 'tiktok_shop_id');
    }

    /**
     * Get active sellers for this shop.
     */
    public function activeSellers(): HasMany
    {
        return $this->sellers()->where('is_active', true);
    }

    /**
     * Get team members who can access this shop
     */
    public function teamMembers(): HasMany
    {
        return $this->sellers()->where('is_active', true);
    }

    /**
     * Get orders for this shop
     */
    public function orders(): HasMany
    {
        return $this->hasMany(TikTokOrder::class, 'tiktok_shop_id');
    }

    /**
     * Get payments for this shop
     */
    public function payments(): HasMany
    {
        return $this->hasMany(TikTokPayment::class, 'tiktok_shop_id');
    }

    /**
     * Check if shop is active
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Get status badge class
     */
    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'active' => 'bg-green-500/20 text-green-400 border-green-500/50',
            'inactive' => 'bg-gray-500/20 text-gray-400 border-gray-500/50',
            'suspended' => 'bg-red-500/20 text-red-400 border-red-500/50',
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
            'inactive' => 'Không hoạt động',
            'suspended' => 'Tạm ngưng',
            default => 'Không xác định',
        };
    }

    /**
     * Check if user can access this shop
     */
    public function canUserAccess(User $user): bool
    {
        // System admin can access all shops
        if ($user->hasRole('system-admin')) {
            return true;
        }

        // Team admin can access shops in their team
        if ($user->hasRole('team-admin') && $user->team_id === $this->team_id) {
            return true;
        }

        // Check if user is assigned to this shop
        return $this->sellers()
            ->where('user_id', $user->id)
            ->where('is_active', true)
            ->exists();
    }

    /**
     * Get user's role in this shop
     */
    public function getUserRole(User $user): ?string
    {
        $seller = $this->sellers()
            ->where('user_id', $user->id)
            ->where('is_active', true)
            ->first();

        return $seller?->role;
    }

    /**
     * Get shop cipher for API calls
     */
    public function getShopCipher(): ?string
    {
        // Ưu tiên lấy từ trường cipher
        if (!empty($this->cipher)) {
            return $this->cipher;
        }

        // Fallback: lấy từ shop_data
        if (!empty($this->shop_data)) {
            return $this->shop_data['cipher'] ?? $this->shop_data['shop_cipher'] ?? null;
        }

        // Cuối cùng fallback về shop_id
        return $this->shop_id;
    }

    /**
     * Check if shop has valid cipher
     */
    public function hasValidCipher(): bool
    {
        return !empty($this->getShopCipher());
    }
}
