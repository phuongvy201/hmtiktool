<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'team_id',
        'name',
        'description',
        'category_id',
        'status',
        'base_price',
        'list_price',
        'weight',
        'height',
        'width',
        'length',
        'images',
        'size_chart',
        'product_video',
        'general_attributes',
        'is_active',
    ];

    protected $casts = [
        'images' => 'array',
        'general_attributes' => 'array',
        'base_price' => 'decimal:2',
        'list_price' => 'decimal:2',
        'weight' => 'decimal:2',
        'height' => 'decimal:2',
        'width' => 'decimal:2',
        'length' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function options(): HasMany
    {
        return $this->hasMany(ProductTemplateOption::class);
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductTemplateVariant::class);
    }

    public function categoryAttributes(): HasMany
    {
        return $this->hasMany(ProdTemplateCategoryAttribute::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByTeam($query, $teamId)
    {
        return $query->where('team_id', $teamId);
    }

    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }
}
