<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TikTokFile extends Model
{
    use HasFactory;

    protected $fillable = [
        'tiktok_shop_integration_id',
        'product_id',
        'product_template_id',
        'file_name',
        'file_path',
        'file_type',
        'source',
        'use_case',
        'file_size',
        'tiktok_uri',
        'tiktok_url',
        'tiktok_resource_id',
        'is_uploaded_to_tiktok',
        'tiktok_uploaded_at',
        'upload_response',
        'error_message',
    ];

    protected $casts = [
        'is_uploaded_to_tiktok' => 'boolean',
        'tiktok_uploaded_at' => 'datetime',
        'file_size' => 'integer',
    ];

    /**
     * Get the TikTok Shop integration that owns this file
     */
    public function tiktokShopIntegration()
    {
        return $this->belongsTo(TikTokShopIntegration::class);
    }

    /**
     * Get the product that owns this file
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the product template that owns this file
     */
    public function productTemplate()
    {
        return $this->belongsTo(ProductTemplate::class);
    }

    /**
     * Scope for files uploaded to TikTok
     */
    public function scopeUploadedToTiktok($query)
    {
        return $query->where('is_uploaded_to_tiktok', true);
    }

    /**
     * Scope for files by type
     */
    public function scopeByType($query, $type)
    {
        return $query->where('file_type', $type);
    }

    /**
     * Scope for files by source
     */
    public function scopeBySource($query, $source)
    {
        return $query->where('source', $source);
    }

    /**
     * Scope for files by use case
     */
    public function scopeByUseCase($query, $useCase)
    {
        return $query->where('use_case', $useCase);
    }

    /**
     * Get file size in human readable format
     */
    public function getFileSizeHumanAttribute()
    {
        if (!$this->file_size) {
            return 'Unknown';
        }

        $units = ['B', 'KB', 'MB', 'GB'];
        $size = $this->file_size;
        $unit = 0;

        while ($size >= 1024 && $unit < count($units) - 1) {
            $size /= 1024;
            $unit++;
        }

        return round($size, 2) . ' ' . $units[$unit];
    }

    /**
     * Mark file as uploaded to TikTok
     */
    public function markAsUploadedToTiktok($tiktokUri = null, $tiktokUrl = null, $tiktokResourceId = null, $response = null)
    {
        $this->update([
            'is_uploaded_to_tiktok' => true,
            'tiktok_uploaded_at' => now(),
            'tiktok_uri' => $tiktokUri,
            'tiktok_url' => $tiktokUrl,
            'tiktok_resource_id' => $tiktokResourceId,
            'upload_response' => $response ? json_encode($response) : null,
            'error_message' => null,
        ]);
    }

    /**
     * Mark file upload as failed
     */
    public function markAsUploadFailed($errorMessage, $response = null)
    {
        $this->update([
            'is_uploaded_to_tiktok' => false,
            'error_message' => $errorMessage,
            'upload_response' => $response ? json_encode($response) : null,
        ]);
    }
}
