<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProdTemplateCategoryAttribute extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_template_id',
        'category_id',
        'attribute_id',
        'attribute_name',
        'attribute_type',
        'is_required',
        'value',
        'value_id',
        'value_name',
        'attribute_data',
    ];

    protected $casts = [
        'is_required' => 'boolean',
        'attribute_data' => 'array',
        'value' => 'array',
        'value_id' => 'array',
        'value_name' => 'array',
    ];

    /**
     * Relationship với ProductTemplate
     */
    public function productTemplate(): BelongsTo
    {
        return $this->belongsTo(ProductTemplate::class);
    }

    /**
     * Relationship với TikTokCategoryAttribute
     */
    public function categoryAttribute(): BelongsTo
    {
        return $this->belongsTo(TikTokCategoryAttribute::class, 'attribute_id', 'attribute_id')
            ->where('category_id', $this->category_id);
    }

    /**
     * Scope để lọc theo template
     */
    public function scopeOfTemplate($query, $templateId)
    {
        return $query->where('product_template_id', $templateId);
    }

    /**
     * Scope để lọc theo category
     */
    public function scopeOfCategory($query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    /**
     * Scope để lọc theo loại attribute
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('attribute_type', $type);
    }

    /**
     * Scope để lọc attributes bắt buộc
     */
    public function scopeRequired($query)
    {
        return $query->where('is_required', true);
    }

    /**
     * Scope để lọc attributes tùy chọn
     */
    public function scopeOptional($query)
    {
        return $query->where('is_required', false);
    }

    /**
     * Lưu hoặc cập nhật category attributes cho một template
     */
    public static function saveTemplateAttributes(int $templateId, string $categoryId, array $attributes): void
    {
        foreach ($attributes as $attributeId => $value) {
            // Tìm attribute trong TikTokCategoryAttribute để lấy thông tin
            $categoryAttribute = TikTokCategoryAttribute::where('category_id', $categoryId)
                ->where('attribute_id', $attributeId)
                ->first();

            if (!$categoryAttribute) {
                continue;
            }

            // Xử lý value có thể là string hoặc array
            $values = is_array($value) ? $value : [$value];
            $valueIds = [];
            $valueNames = [];

            if ($categoryAttribute->values && is_array($categoryAttribute->values)) {
                foreach ($values as $val) {
                    foreach ($categoryAttribute->values as $attrVal) {
                        if (isset($attrVal['id']) && $attrVal['id'] == $val) {
                            $valueIds[] = $attrVal['id'];
                            $valueNames[] = $attrVal['name'] ?? null;
                            break;
                        }
                    }
                }
            }

            // Lưu hoặc cập nhật
            static::updateOrCreate(
                [
                    'product_template_id' => $templateId,
                    'category_id' => $categoryId,
                    'attribute_id' => $attributeId,
                ],
                [
                    'attribute_name' => $categoryAttribute->name,
                    'attribute_type' => $categoryAttribute->type,
                    'is_required' => $categoryAttribute->is_required,
                    'value' => is_array($value) ? json_encode($value) : $value,
                    'value_id' => !empty($valueIds) ? json_encode($valueIds) : null,
                    'value_name' => !empty($valueNames) ? json_encode($valueNames) : null,
                    'attribute_data' => $categoryAttribute->toArray(),
                ]
            );
        }
    }

    /**
     * Lấy tất cả attributes của một template theo nhóm
     */
    public static function getTemplateAttributesGrouped(int $templateId): array
    {
        $attributes = static::where('product_template_id', $templateId)->get();

        return [
            'required' => $attributes->where('is_required', true),
            'optional' => $attributes->where('is_required', false),
            'product_properties' => $attributes->where('attribute_type', 'PRODUCT_PROPERTY'),
            'sales_properties' => $attributes->where('attribute_type', 'SALES_PROPERTY'),
        ];
    }
}
