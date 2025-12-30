<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Team extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'slug',
        'description',
        'status',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($team) {
            if (empty($team->slug)) {
                $team->slug = Str::slug($team->name);
            }
        });

        static::updating(function ($team) {
            if ($team->isDirty('name') && empty($team->slug)) {
                $team->slug = Str::slug($team->name);
            }
        });
    }

    /**
     * Get the users for the team.
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }

    /**
     * Get the team admin users.
     */
    public function admins()
    {
        return $this->users()->whereHas('roles', function ($query) {
            $query->where('name', 'team-admin');
        });
    }

    /**
     * TikTok markets assigned to this team.
     */
    public function tiktokMarkets()
    {
        return $this->hasMany(TeamTikTokMarket::class);
    }

    /**
     * Get primary / first market.
     */
    public function getPrimaryMarket(): ?string
    {
        $market = $this->tiktokMarkets()->first();
        return $market?->market;
    }

    /**
     * Get the subscriptions for the team.
     */
    public function subscriptions(): HasMany
    {
        return $this->hasMany(TeamSubscription::class);
    }

    /**
     * Get active subscriptions for the team.
     */
    public function activeSubscriptions(): HasMany
    {
        return $this->subscriptions()->where('status', 'active');
    }

    /**
     * Get current active subscription for the team.
     */
    public function currentSubscription()
    {
        return $this->activeSubscriptions()
            ->where('end_date', '>=', now())
            ->orderBy('end_date', 'desc')
            ->first();
    }

    /**
     * Check if team has active subscription.
     */
    public function hasActiveSubscription(): bool
    {
        return $this->currentSubscription() !== null;
    }

    /**
     * Check if team is active
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Get the TikTok Shop integration for the team.
     */
    public function tiktokShopIntegration()
    {
        return $this->hasOne(TikTokShopIntegration::class);
    }

    /**
     * Get the TikTok shops for the team.
     */
    public function tiktokShops()
    {
        return $this->hasMany(TikTokShop::class);
    }

    /**
     * Get active TikTok shops for the team.
     */
    public function activeTikTokShops()
    {
        return $this->tiktokShops()->where('status', 'active');
    }

    /**
     * Check if team has TikTok Shop integration.
     */
    public function hasTikTokShopIntegration(): bool
    {
        return $this->tiktokShopIntegration()->exists();
    }

    /**
     * Check if team has TikTok shops.
     */
    public function hasTikTokShops(): bool
    {
        return $this->tiktokShops()->exists();
    }

    /**
     * Check if team has active TikTok Shop integration.
     */
    public function hasActiveTikTokShopIntegration(): bool
    {
        $integration = $this->tiktokShopIntegration;
        return $integration && $integration->isActive();
    }
}
