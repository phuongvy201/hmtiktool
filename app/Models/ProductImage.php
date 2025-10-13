<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'file_name',
        'file_path',
        'tiktok_uri',
        'tiktok_resource_id',
        'type',
        'source',
        'sort_order',
        'is_primary',
        'is_uploaded_to_tiktok',
        'tiktok_uploaded_at',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
        'is_uploaded_to_tiktok' => 'boolean',
        'tiktok_uploaded_at' => 'datetime',
    ];

    /**
     * Get the product that owns the image.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the full URL of the image.
     */
    public function getUrlAttribute()
    {
        if ($this->file_path) {
            // Nếu là S3 URL, trả về trực tiếp
            if (str_contains($this->file_path, 'amazonaws.com')) {
                return $this->file_path;
            }
            // Nếu là local path, trả về asset URL
            return asset('storage/' . $this->file_path);
        }
        return null;
    }

    /**
     * Scope to get primary images.
     */
    public function scopePrimary($query)
    {
        return $query->where('is_primary', true);
    }

    /**
     * Scope to get images uploaded to TikTok.
     */
    public function scopeUploadedToTiktok($query)
    {
        return $query->where('is_uploaded_to_tiktok', true);
    }

    /**
     * Scope to get images by type.
     */
    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Mark image as uploaded to TikTok.
     */
    public function markAsUploadedToTiktok($tiktokUri = null, $tiktokResourceId = null)
    {
        $this->update([
            'is_uploaded_to_tiktok' => true,
            'tiktok_uploaded_at' => now(),
            'tiktok_uri' => $tiktokUri,
            'tiktok_resource_id' => $tiktokResourceId,
        ]);
    }
}
