<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'team_id',
        'product_template_id',
        'title',
        'description',
        'sku',
        'price',
        'status',
        'is_active',
    ];

    protected $casts = [
        'price' => 'decimal:2',
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

    public function productTemplate(): BelongsTo
    {
        return $this->belongsTo(ProductTemplate::class);
    }

    /**
     * Tính tổng giá sản phẩm (giá sản phẩm + giá template)
     */
    public function getTotalPriceAttribute()
    {
        $templatePrice = 0;
        if ($this->productTemplate && is_numeric($this->productTemplate->base_price)) {
            $templatePrice = (float) $this->productTemplate->base_price;
        }
        return $this->price + $templatePrice;
    }

    /**
     * Scope để lọc theo team
     */
    public function scopeByTeam($query, $teamId)
    {
        return $query->where('team_id', $teamId);
    }

    /**
     * Scope để lọc theo user
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope để lọc sản phẩm active
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope để lọc theo template
     */
    public function scopeByTemplate($query, $templateId)
    {
        return $query->where('product_template_id', $templateId);
    }

    /**
     * Get the images for the product (ordered).
     */
    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    /**
     * Get the primary image for the product.
     */
    public function primaryImage(): HasOne
    {
        return $this->hasOne(ProductImage::class)->where('is_primary', true);
    }

    /**
     * Get the publish histories for the product.
     */
    public function publishHistories(): HasMany
    {
        return $this->hasMany(PublishHistory::class);
    }

    /**
     * Get the latest successful publish history.
     */
    public function latestSuccessfulPublish()
    {
        return $this->publishHistories()
            ->where('status', 'success')
            ->latest('published_at')
            ->first();
    }

    /**
     * Check if product is published to TikTok.
     */
    public function isPublishedToTiktok(): bool
    {
        return $this->publishHistories()
            ->where('status', 'success')
            ->exists();
    }

    /**
     * Get TikTok product ID.
     */
    public function getTiktokProductIdAttribute()
    {
        $latestPublish = $this->latestSuccessfulPublish();
        return $latestPublish ? $latestPublish->tiktok_product_id : null;
    }

    /**
     * Get all images for TikTok upload (product images + template images)
     */
    public function getAllImagesForTiktok()
    {
        $images = collect();

        // 1. Lấy tất cả ảnh sản phẩm (không chỉ những ảnh đã upload)
        $productImages = $this->images()
            ->orderBy('sort_order')
            ->get();

        foreach ($productImages as $image) {
            $images->push([
                'type' => 'product',
                'file_name' => $image->file_name,
                'file_path' => $image->file_path,
                'tiktok_uri' => $image->tiktok_uri,
                'tiktok_resource_id' => $image->tiktok_resource_id,
                'sort_order' => $image->sort_order,
                'is_primary' => $image->is_primary,
                'is_uploaded_to_tiktok' => $image->is_uploaded_to_tiktok,
            ]);
        }

        // 2. Lấy ảnh template (nếu có)
        if ($this->productTemplate && $this->productTemplate->images) {
            $templateImages = $this->productTemplate->images;

            if (is_array($templateImages)) {
                foreach ($templateImages as $index => $templateImage) {
                    if (is_array($templateImage) && isset($templateImage['file_path'])) {
                        $images->push([
                            'type' => 'template',
                            'file_name' => $templateImage['file_name'] ?? 'template_' . ($index + 1),
                            'file_path' => $templateImage['file_path'],
                            'tiktok_uri' => $templateImage['tiktok_uri'] ?? null,
                            'tiktok_resource_id' => $templateImage['tiktok_resource_id'] ?? null,
                            'sort_order' => $productImages->count() + $index, // Sắp xếp sau ảnh sản phẩm
                            'is_primary' => false, // Template images không phải ảnh chính
                            'is_uploaded_to_tiktok' => false, // Template images chưa upload
                        ]);
                    }
                }
            } elseif (is_string($templateImages)) {
                // Template image là string URL
                $images->push([
                    'type' => 'template',
                    'file_name' => 'template_image.jpg',
                    'file_path' => $templateImages,
                    'tiktok_uri' => null,
                    'tiktok_resource_id' => null,
                    'sort_order' => $productImages->count(), // Sắp xếp sau ảnh sản phẩm
                    'is_primary' => false,
                    'is_uploaded_to_tiktok' => false,
                ]);
            }
        }

        return $images->sortBy('sort_order')->values();
    }

    /**
     * Get primary image for display
     */
    public function getPrimaryImageAttribute()
    {
        // Ưu tiên ảnh chính của sản phẩm
        $primaryProductImage = $this->images()->where('is_primary', true)->first();
        if ($primaryProductImage) {
            return $primaryProductImage;
        }

        // Nếu không có ảnh chính, lấy ảnh đầu tiên của sản phẩm
        $firstProductImage = $this->images()->orderBy('sort_order')->first();
        if ($firstProductImage) {
            return $firstProductImage;
        }

        // Nếu không có ảnh sản phẩm, lấy ảnh đầu tiên của template
        if ($this->productTemplate && $this->productTemplate->images && is_array($this->productTemplate->images)) {
            $templateImages = collect($this->productTemplate->images);
            if ($templateImages->isNotEmpty()) {
                $firstImage = $templateImages->first();
                if (is_array($firstImage)) {
                    $imageUrl = $firstImage['file_path'] ?? '';
                    // Nếu là S3 URL, sử dụng trực tiếp
                    if (str_contains($imageUrl, 'amazonaws.com')) {
                        $url = $imageUrl;
                    } else {
                        $url = asset('storage/' . $imageUrl);
                    }

                    return (object) [
                        'url' => $url,
                        'file_name' => $firstImage['file_name'] ?? '',
                        'type' => 'template'
                    ];
                }
            }
        }

        return null;
    }

    /**
     * Get all images for display (product + template)
     */
    public function getAllImagesAttribute()
    {
        $images = collect();

        // 1. Lấy ảnh sản phẩm
        $productImages = $this->images()->orderBy('sort_order')->get();
        foreach ($productImages as $image) {
            $images->push([
                'id' => $image->id,
                'url' => $image->url,
                'file_name' => $image->file_name,
                'type' => 'product',
                'is_primary' => $image->is_primary,
                'sort_order' => $image->sort_order,
            ]);
        }

        // 2. Lấy ảnh template
        if ($this->productTemplate && $this->productTemplate->images) {
            $templateImages = $this->productTemplate->images;
            
            if (is_array($templateImages)) {
                $templateImages = collect($templateImages);
                foreach ($templateImages as $index => $templateImage) {
                    if (is_array($templateImage)) {
                        $imageUrl = $templateImage['file_path'] ?? '';
                        // Nếu là S3 URL, sử dụng trực tiếp
                        if (str_contains($imageUrl, 'amazonaws.com')) {
                            $url = $imageUrl;
                        } else {
                            $url = asset('storage/' . $imageUrl);
                        }

                        $images->push([
                            'id' => 'template_' . $index,
                            'url' => $url,
                            'file_name' => $templateImage['file_name'] ?? '',
                            'type' => 'template',
                            'is_primary' => false,
                            'sort_order' => $productImages->count() + $index,
                        ]);
                    } elseif (is_string($templateImage)) {
                        // Template image là string URL
                        $images->push([
                            'id' => 'template_' . $index,
                            'url' => $templateImage,
                            'file_name' => 'template_image_' . ($index + 1) . '.jpg',
                            'type' => 'template',
                            'is_primary' => false,
                            'sort_order' => $productImages->count() + $index,
                        ]);
                    }
                }
            } elseif (is_string($templateImages)) {
                // Template image là string URL
                $images->push([
                    'id' => 'template_0',
                    'url' => $templateImages,
                    'file_name' => 'template_image.jpg',
                    'type' => 'template',
                    'is_primary' => false,
                    'sort_order' => $productImages->count(),
                ]);
            }
        }

        return $images->sortBy('sort_order')->values();
    }
}
