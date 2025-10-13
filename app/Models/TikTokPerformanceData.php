<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TikTokPerformanceData extends Model
{
    use HasFactory;

    protected $fillable = [
        'tiktok_shop_id',
        'start_date',
        'end_date',
        'granularity',
        'data',
        'cached_at'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'data' => 'array',
        'cached_at' => 'datetime'
    ];

    /**
     * Relationship với TikTokShop
     */
    public function tiktokShop(): BelongsTo
    {
        return $this->belongsTo(TikTokShop::class);
    }
}
