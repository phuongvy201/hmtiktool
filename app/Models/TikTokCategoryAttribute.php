<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class TikTokCategoryAttribute extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'attribute_id',
        'name',
        'type',
        'is_required',
        'is_multiple_selection',
        'is_customizable',
        'value_data_format',
        'values',
        'requirement_conditions',
        'attribute_data',
        'last_synced_at',
    ];

    protected $casts = [
        'is_required' => 'boolean',
        'is_multiple_selection' => 'boolean',
        'is_customizable' => 'boolean',
        'values' => 'array',
        'requirement_conditions' => 'array',
        'attribute_data' => 'array',
        'last_synced_at' => 'datetime',
    ];

    /**
     * Relationship với TikTokShopCategory
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(TikTokShopCategory::class, 'category_id', 'category_id');
    }

    /**
     * Scope để lọc theo loại attribute
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
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
     * Scope để lọc attributes có thể chọn nhiều
     */
    public function scopeMultipleSelection($query)
    {
        return $query->where('is_multiple_selection', true);
    }

    /**
     * Scope để lọc attributes có thể tùy chỉnh
     */
    public function scopeCustomizable($query)
    {
        return $query->where('is_customizable', true);
    }

    /**
     * Lấy danh sách values dưới dạng array đơn giản
     */
    public function getValuesListAttribute(): array
    {
        if (!$this->values) {
            return [];
        }

        return collect($this->values)->pluck('name', 'id')->toArray();
    }

    /**
     * Kiểm tra xem attribute có cần sync không
     */
    public static function needsSync(string $categoryId, int $hours = 24): bool
    {
        $lastSync = static::where('category_id', $categoryId)
            ->max('last_synced_at');

        if (!$lastSync) {
            return true;
        }

        return Carbon::parse($lastSync)->diffInHours(now()) >= $hours;
    }

    /**
     * Xóa tất cả attributes của một category
     */
    public static function clearCategoryAttributes(string $categoryId): int
    {
        return static::where('category_id', $categoryId)->delete();
    }

    /**
     * Tạo hoặc cập nhật attribute từ API data
     */
    public static function createOrUpdateFromApiData(string $categoryId, array $attributeData): self
    {
        $data = [
            'category_id' => $categoryId,
            'attribute_id' => $attributeData['id'],
            'name' => $attributeData['name'],
            'type' => $attributeData['type'],
            'is_required' => $attributeData['is_requried'] ?? false, // Note: API has typo
            'is_multiple_selection' => $attributeData['is_multiple_selection'] ?? false,
            'is_customizable' => $attributeData['is_customizable'] ?? false,
            'value_data_format' => $attributeData['value_data_format'] ?? null,
            'values' => $attributeData['values'] ?? null,
            'requirement_conditions' => $attributeData['requirement_conditions'] ?? null,
            'attribute_data' => $attributeData,
            'last_synced_at' => now(),
        ];

        return static::updateOrCreate(
            ['category_id' => $categoryId, 'attribute_id' => $attributeData['id']],
            $data
        );
    }

    /**
     * Lấy attributes theo category với phân loại
     */
    public static function getByCategoryWithGrouping(string $categoryId): array
    {
        $attributes = static::where('category_id', $categoryId)
            ->orderBy('is_required', 'desc')
            ->orderBy('name')
            ->get();

        // Chỉ lấy attributes có type là PRODUCT_PROPERTY
        $productPropertyAttributes = $attributes->where('type', 'PRODUCT_PROPERTY');

        return [
            'required' => $productPropertyAttributes->where('is_required', true),
            'optional' => $productPropertyAttributes->where('is_required', false),
            'product_properties' => $productPropertyAttributes,
            'sales_properties' => $attributes->where('type', 'SALES_PROPERTY'),
        ];
    }

    /**
     * Scope để lấy chỉ PRODUCT_PROPERTY attributes
     */
    public function scopeProductProperties($query)
    {
        return $query->where('type', 'PRODUCT_PROPERTY');
    }
}
