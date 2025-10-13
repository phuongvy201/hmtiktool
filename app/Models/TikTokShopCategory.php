<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TikTokShopCategory extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'tiktok_shop_categories';

    protected $fillable = [
        'category_id',
        'category_name',
        'parent_category_id',
        'level',
        'is_leaf',
        'metadata',
        'last_synced_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'is_leaf' => 'boolean',
        'last_synced_at' => 'datetime',
    ];



    /**
     * Relationship với parent category
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(TikTokShopCategory::class, 'parent_category_id', 'category_id');
    }

    /**
     * Relationship với child categories
     */
    public function children(): HasMany
    {
        return $this->hasMany(TikTokShopCategory::class, 'parent_category_id', 'category_id');
    }



    /**
     * Scope để lấy leaf categories (categories có thể tạo sản phẩm)
     */
    public function scopeLeafCategories($query)
    {
        return $query->where('is_leaf', true);
    }

    /**
     * Scope để lấy root categories (level 1)
     */
    public function scopeRootCategories($query)
    {
        return $query->where('level', 1);
    }

    /**
     * Lấy categories dưới dạng array cho select dropdown
     */
    public static function getCategoriesArray(): array
    {
        return static::leafCategories()
            ->orderBy('category_name')
            ->pluck('category_name', 'category_id')
            ->toArray();
    }

    /**
     * Lấy categories với hierarchy cho select dropdown
     */
    public static function getCategoriesWithHierarchy(): array
    {
        $categories = static::leafCategories()
            ->orderBy('category_name')
            ->get();

        $result = [];
        foreach ($categories as $category) {
            $hierarchy = static::getCategoryHierarchy($category->category_id);
            $result[$category->category_id] = $hierarchy;
        }

        return $result;
    }

    /**
     * Tạo hierarchy string cho category
     */
    public static function getCategoryHierarchy($categoryId): string
    {
        $category = static::where('category_id', $categoryId)->first();
        if (!$category) return 'Unknown Category';

        $hierarchy = [$category->category_name];
        $currentCategory = $category;

        while ($currentCategory->parent_category_id && $currentCategory->parent_category_id !== '0') {
            $parent = static::where('category_id', $currentCategory->parent_category_id)->first();
            if ($parent) {
                array_unshift($hierarchy, $parent->category_name);
                $currentCategory = $parent;
            } else {
                break;
            }
        }

        return implode(' -> ', $hierarchy);
    }

    /**
     * Kiểm tra xem categories có cần sync không (dựa trên thời gian)
     */
    public static function needsSystemSync($hours = 24): bool
    {
        $lastSync = static::orderBy('last_synced_at', 'desc')
            ->value('last_synced_at');

        if (!$lastSync) {
            return true;
        }

        return $lastSync->diffInHours(now()) >= $hours;
    }
}
