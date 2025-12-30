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
        'market',
        'category_version',
        'category_id',
        'category_name',
        'parent_category_id',
        'level',
        'is_leaf',
        'is_active',
        'category_data',
        'last_synced_at',
    ];

    protected $casts = [
        'category_data' => 'array',
        'is_leaf' => 'boolean',
        'is_active' => 'boolean',
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
     * Scope để lọc theo market
     */
    public function scopeForMarket($query, string $market)
    {
        return $query->where('market', strtoupper($market));
    }

    /**
     * Scope để lọc theo category version
     */
    public function scopeForVersion($query, string $version)
    {
        return $query->where('category_version', strtolower($version));
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
    public static function getCategoriesArray(?string $market = null, ?string $categoryVersion = null): array
    {
        $query = static::leafCategories()->where('is_active', true);

        if ($market) {
            $query->forMarket($market);
        }

        if ($categoryVersion) {
            $query->forVersion($categoryVersion);
        }

        return $query->orderBy('category_name')
            ->pluck('category_name', 'category_id')
            ->toArray();
    }

    /**
     * Lấy categories với hierarchy cho select dropdown
     * Có cache để tránh query nhiều lần
     */
    public static function getCategoriesWithHierarchy(?string $market = null, ?string $categoryVersion = null): array
    {
        $cacheKey = sprintf(
            'tiktok_categories_hierarchy_%s_%s',
            $market ? strtolower($market) : 'all',
            $categoryVersion ? strtolower($categoryVersion) : 'all'
        );

        return \Illuminate\Support\Facades\Cache::remember($cacheKey, 3600, function () use ($market, $categoryVersion) {
            $allCategoriesQuery = static::query();

            if ($market) {
                $allCategoriesQuery->forMarket($market);
            }

            if ($categoryVersion) {
                $allCategoriesQuery->forVersion($categoryVersion);
            }

            $allCategoriesQuery->where('is_active', true);

            // Lấy tất cả categories (bao gồm cả parent) một lần
            $allCategories = $allCategoriesQuery
                ->orderBy('category_name')
                ->get()
                ->keyBy('category_id');

            // Lấy chỉ leaf categories
            $leafCategories = $allCategories->where('is_leaf', true);

            $result = [];
            foreach ($leafCategories as $category) {
                $hierarchy = static::buildHierarchyFromCache($category, $allCategories);
                $result[$category->category_id] = $hierarchy;
            }

            return $result;
        });
    }

    /**
     * Build hierarchy từ cache (không query database)
     */
    private static function buildHierarchyFromCache($category, $allCategories): string
    {
        $hierarchy = [$category->category_name];
        $currentCategory = $category;

        while ($currentCategory->parent_category_id && $currentCategory->parent_category_id !== '0') {
            $parent = $allCategories->get($currentCategory->parent_category_id);
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
    public static function needsSystemSync($hours = 24, ?string $market = null, ?string $categoryVersion = null): bool
    {
        $query = static::query();

        if ($market) {
            $query->forMarket($market);
        }

        if ($categoryVersion) {
            $query->forVersion($categoryVersion);
        }

        $lastSync = $query
            ->orderBy('last_synced_at', 'desc')
            ->value('last_synced_at');

        if (!$lastSync) {
            return true;
        }

        return $lastSync->diffInHours(now()) >= $hours;
    }
}
