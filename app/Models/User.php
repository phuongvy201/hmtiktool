<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'team_id',
        'is_system_user',
        'email_verified_at',
        'email_verification_token',
        'email_verification_expires_at',
        'avatar',
        'last_login_at',
        'login_count',
        'two_factor_enabled',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_system_user' => 'boolean',
            'last_login_at' => 'datetime',
            'two_factor_enabled' => 'boolean',
        ];
    }

    /**
     * Get the team that owns the user.
     */
    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    /**
     * Get TikTok markets assigned to this user
     */
    public function tiktokMarkets()
    {
        return $this->hasMany(UserTikTokMarket::class);
    }

    /**
     * Get primary TikTok market for this user (first market assigned)
     */
    public function getPrimaryTikTokMarket(): ?string
    {
        $market = $this->tiktokMarkets()->first();
        return $market ? $market->market : null;
    }

    /**
     * Check if user has access to a specific market
     */
    public function hasTikTokMarket(string $market): bool
    {
        return $this->tiktokMarkets()->where('market', $market)->exists();
    }

    /**
     * Get all markets assigned to this user as array
     */
    public function getTikTokMarkets(): array
    {
        return $this->tiktokMarkets()->pluck('market')->toArray();
    }

    /**
     * Check if user is a system level user
     */
    public function isSystemUser(): bool
    {
        return $this->is_system_user;
    }

    /**
     * Check if user is a tenant/team level user
     */
    public function isTenantUser(): bool
    {
        return !$this->is_system_user;
    }

    /**
     * Get user's role level (system or tenant)
     */
    public function getRoleLevel(): string
    {
        return $this->is_system_user ? 'system' : 'tenant';
    }

    /**
     * Get the subscriptions for the user.
     */
    public function subscriptions(): HasMany
    {
        return $this->hasMany(UserSubscription::class);
    }

    /**
     * Get the active subscription for the user.
     */
    public function activeSubscription()
    {
        return $this->subscriptions()
            ->where('status', 'active')
            ->where('end_date', '>', now())
            ->latest()
            ->first();
    }

    /**
     * Check if user has active subscription.
     */
    public function hasActiveSubscription(): bool
    {
        return $this->activeSubscription() !== null;
    }

    /**
     * Get current service package.
     */
    public function currentServicePackage()
    {
        $subscription = $this->activeSubscription();
        return $subscription ? $subscription->servicePackage : null;
    }

    /**
     * Get user's avatar URL.
     */
    public function getAvatarUrlAttribute()
    {
        // Luôn dùng avatar mặc định
        $name = urlencode($this->name);
        return "https://ui-avatars.com/api/?name={$name}&color=7C3AED&background=1F2937&size=128&bold=true";
    }

    /**
     * Get user's display name (with fallback).
     */
    public function getDisplayNameAttribute()
    {
        return $this->name ?: 'Unknown User';
    }

    /**
     * Check if user has avatar.
     */
    public function hasAvatar(): bool
    {
        return !empty($this->avatar);
    }

    /**
     * Update last login information.
     */
    public function updateLastLogin()
    {
        $this->update([
            'last_login_at' => now(),
            'login_count' => $this->login_count + 1,
        ]);
    }

    /**
     * Get user's primary role name.
     */
    public function getPrimaryRoleNameAttribute()
    {
        return $this->roles->first()?->name ?? 'No Role';
    }

    /**
     * Get user's team name.
     */
    public function getTeamNameAttribute()
    {
        return $this->team?->name ?? 'No Team';
    }

    /**
     * Get current team for the user
     */
    public function getCurrentTeamAttribute()
    {
        return $this->team;
    }
}
