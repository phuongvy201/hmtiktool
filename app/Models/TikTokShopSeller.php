<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TikTokShopSeller extends Model
{
    use HasFactory;
    protected $table = 'tiktok_shop_sellers';
    protected $fillable = [
        'tiktok_shop_id',
        'user_id',
        'role',
        'permissions',
        'is_active',
    ];

    protected $casts = [
        'permissions' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Get the shop that owns the seller.
     */
    public function shop(): BelongsTo
    {
        return $this->belongsTo(TikTokShop::class, 'tiktok_shop_id');
    }

    /**
     * Get the user that owns the seller.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if seller has specific permission
     */
    public function hasPermission(string $permission): bool
    {
        if (!$this->is_active) {
            return false;
        }

        // Owner has all permissions
        if ($this->role === 'owner') {
            return true;
        }

        // Check specific permissions
        return in_array($permission, $this->permissions ?? []);
    }

    /**
     * Get role text
     */
    public function getRoleTextAttribute(): string
    {
        return match ($this->role) {
            'owner' => 'Chủ sở hữu',
            'manager' => 'Quản lý',
            'viewer' => 'Xem',
            default => 'Không xác định',
        };
    }

    /**
     * Get role badge class
     */
    public function getRoleBadgeClassAttribute(): string
    {
        return match ($this->role) {
            'owner' => 'bg-purple-500/20 text-purple-400 border-purple-500/50',
            'manager' => 'bg-blue-500/20 text-blue-400 border-blue-500/50',
            'viewer' => 'bg-gray-500/20 text-gray-400 border-gray-500/50',
            default => 'bg-gray-500/20 text-gray-400 border-gray-500/50',
        };
    }
}
