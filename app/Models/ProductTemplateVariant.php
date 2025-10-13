<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ProductTemplateVariant extends Model
{
    use HasFactory;

    protected $table = 'prod_template_variants';

    protected $fillable = [
        'product_template_id',
        'sku',
        'price',
        'list_price',
        'stock_quantity',
        'variant_data',
    ];

    protected $casts = [
        'variant_data' => 'array',
        'price' => 'decimal:2',
        'list_price' => 'decimal:2',
    ];

    public function productTemplate(): BelongsTo
    {
        return $this->belongsTo(ProductTemplate::class);
    }

    public function optionValues(): BelongsToMany
    {
        return $this->belongsToMany(
            ProductTemplateOptionValue::class,
            'prod_variant_options',
            'prod_template_variant_id',
            'prod_option_value_id'
        );
    }
}
