<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeamTikTokMarket extends Model
{
    use HasFactory;

    protected $table = 'team_tiktok_markets';

    protected $fillable = [
        'team_id',
        'market',
    ];

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }
}

