<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ProductTemplateOptionValue extends Model
{
    use HasFactory;

    protected $table = 'prod_option_values';

    protected $fillable = [
        'prod_template_option_id',
        'value',
        'label',
        'sort_order',
    ];

    public function option(): BelongsTo
    {
        return $this->belongsTo(ProductTemplateOption::class, 'prod_template_option_id');
    }

    public function variants(): BelongsToMany
    {
        return $this->belongsToMany(
            ProductTemplateVariant::class,
            'prod_variant_options',
            'prod_option_value_id',
            'prod_template_variant_id'
        );
    }
}
