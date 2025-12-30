<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserTikTokMarket extends Model
{
    use HasFactory;

    protected $table = 'user_tiktok_markets';

    protected $fillable = [
        'user_id',
        'market',
    ];

    /**
     * Get the user that owns this market assignment
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
