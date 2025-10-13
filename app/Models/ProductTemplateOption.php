<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductTemplateOption extends Model
{
    use HasFactory;

    protected $table = 'prod_template_options';

    protected $fillable = [
        'product_template_id',
        'name',
        'type',
        'is_required',
        'sort_order',
    ];

    public function productTemplate(): BelongsTo
    {
        return $this->belongsTo(ProductTemplate::class);
    }

    public function values(): HasMany
    {
        return $this->hasMany(ProductTemplateOptionValue::class, 'prod_template_option_id');
    }
}
