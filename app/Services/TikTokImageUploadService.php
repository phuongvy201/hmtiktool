<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductImage;
use App\Models\TikTokShopIntegration;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Services\TikTokSignatureService;

class TikTokImageUploadService
{
    private const API_VERSION = '202309';

    protected $integration;
    protected $appKey;
    protected $appSecret;
    protected $accessToken;

    public function __construct(TikTokShopIntegration $integration = null)
    {
        if ($integration) {
            $this->integration = $integration;

            // Ưu tiên lấy credentials theo market của integration
            $this->appKey = $integration->getAppKey();
            $this->appSecret = $integration->getAppSecret();

            // Fallback cuối cùng nếu vẫn rỗng (tránh request không hợp lệ)
            if (empty($this->appKey)) {
                $this->appKey = config('tiktok-shop.app_key') ?? env('TIKTOK_SHOP_APP_KEY');
            }

            if (empty($this->appSecret)) {
                $this->appSecret = config('tiktok-shop.app_secret') ?? env('TIKTOK_SHOP_APP_SECRET');
            }

            $this->accessToken = $integration->access_token;

            if (empty($this->appKey) || empty($this->appSecret)) {
                Log::warning('TikTok credentials missing in TikTokImageUploadService', [
                    'integration_id' => $integration->id,
                    'market' => $integration->market,
                    'resolved_app_key' => $this->appKey ? 'available' : 'missing',
                    'resolved_app_secret' => $this->appSecret ? 'available' : 'missing'
                ]);
            }
        }
    }

    /**
     * Upload images for a product to TikTok Shop
     */
    public function uploadProductImages(Product $product, $useCase = 'MAIN_IMAGE')
    {
        try {
            if (!$this->integration) {
                throw new \Exception('TikTok Shop integration not found');
            }

            if (!$this->accessToken) {
                throw new \Exception('Access token not available');
            }

            // Lấy tất cả hình ảnh cần upload (product + template)
            $allImages = $this->getAllImagesForUpload($product);

            Log::info('Images to upload:', [
                'product_id' => $product->id,
                'total_images' => count($allImages),
                'images' => $allImages
            ]);

            $uploadedImages = [];
            $uploadedCount = 0;

            foreach ($allImages as $index => $imageData) {
                Log::info('Uploading image:', [
                    'index' => $index,
                    'file_name' => $imageData['file_name'],
                    'file_path' => $imageData['file_path'],
                    'type' => $imageData['type']
                ]);

                $result = $this->uploadSingleImage($imageData['file_path'], $useCase);

                Log::info('Upload result:', [
                    'success' => $result['success'],
                    'message' => $result['message'] ?? 'No message',
                    'response' => $result['response'] ?? 'No response'
                ]);

                if ($result['success']) {
                    $uploadedCount++;

                    // Cập nhật ProductImage với thông tin TikTok
                    if (isset($imageData['product_image_id'])) {
                        // Product image - cập nhật record hiện có
                        $productImage = ProductImage::find($imageData['product_image_id']);
                        if ($productImage) {
                            $productImage->markAsUploadedToTiktok(
                                $result['data']['uri'],
                                $result['data']['url']
                            );
                        }
                    } elseif ($imageData['type'] === 'template') {
                        // Template image - tạo record mới trong product_images
                        ProductImage::create([
                            'product_id' => $product->id,
                            'file_name' => $imageData['file_name'],
                            'file_path' => $imageData['file_path'],
                            'tiktok_uri' => $result['data']['uri'] ?? null,
                            'tiktok_resource_id' => $result['data']['url'] ?? null,
                            'type' => 'image', // Lưu type là 'image' cho tất cả
                            'source' => 'template', // Phân biệt với ảnh sản phẩm
                            'sort_order' => $product->images()->count() + $uploadedCount, // Sắp xếp sau product images
                            'is_primary' => false,
                            'is_uploaded_to_tiktok' => true,
                            'tiktok_uploaded_at' => now()
                        ]);
                    }

                    $uploadedImages[] = [
                        'original_file' => $imageData['file_name'],
                        'tiktok_uri' => $result['data']['uri'],
                        'tiktok_url' => $result['data']['url'],
                        'width' => $result['data']['width'],
                        'height' => $result['data']['height'],
                        'type' => $imageData['type'] ?? 'product'
                    ];

                    Log::info('Image uploaded to TikTok successfully', [
                        'product_id' => $product->id,
                        'file_name' => $imageData['file_name'],
                        'tiktok_uri' => $result['data']['uri']
                    ]);
                } else {
                    Log::error('Failed to upload image to TikTok', [
                        'product_id' => $product->id,
                        'file_name' => $imageData['file_name'],
                        'error' => $result['message']
                    ]);
                }
            }

            return [
                'success' => true,
                'uploaded_count' => count($uploadedImages),
                'images' => $uploadedImages
            ];
        } catch (\Exception $e) {
            Log::error('Error uploading images to TikTok', [
                'product_id' => $product->id,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Upload video for a product to TikTok Shop
     */
    public function uploadProductVideo(Product $product, $videoPath, $fileName = null)
    {
        try {
            if (!$this->integration) {
                throw new \Exception('TikTok Shop integration not found');
            }

            if (!$this->accessToken) {
                throw new \Exception('Access token not available');
            }

            // Validate file format
            $allowedFormats = ['pdf', 'mp4', 'mov', 'mkv', 'wmv', 'webm', 'avi', '3gp', 'flv', 'mpeg'];
            $fileExtension = strtolower(pathinfo($videoPath, PATHINFO_EXTENSION));

            if (!in_array($fileExtension, $allowedFormats)) {
                throw new \Exception("Unsupported file format: {$fileExtension}. Supported formats: " . implode(', ', $allowedFormats));
            }

            // Validate file size
            $fileSize = $this->getFileSize($videoPath);
            $maxSize = $fileExtension === 'pdf' ? 20 * 1024 * 1024 : 100 * 1024 * 1024; // 20MB for PDF, 100MB for video

            if ($fileSize > $maxSize) {
                $maxSizeMB = $fileExtension === 'pdf' ? 20 : 100;
                throw new \Exception("File size exceeds limit: {$maxSizeMB}MB");
            }

            // Use provided filename or generate from path
            $finalFileName = $fileName ?: basename($videoPath);

            // Clean filename (remove spaces, special characters)
            $finalFileName = preg_replace('/[^a-zA-Z0-9._-]/', '_', $finalFileName);

            Log::info('Uploading video to TikTok:', [
                'product_id' => $product->id,
                'file_path' => $videoPath,
                'file_name' => $finalFileName,
                'file_size' => $fileSize,
                'file_extension' => $fileExtension
            ]);

            $result = $this->uploadSingleVideo($videoPath, $finalFileName);

            if ($result['success']) {
                // Save video info to product (you might want to add a video field to products table)
                Log::info('Video uploaded to TikTok successfully', [
                    'product_id' => $product->id,
                    'file_name' => $finalFileName,
                    'tiktok_uri' => $result['data']['uri'] ?? null
                ]);

                return [
                    'success' => true,
                    'data' => $result['data'],
                    'file_name' => $finalFileName
                ];
            } else {
                Log::error('Failed to upload video to TikTok', [
                    'product_id' => $product->id,
                    'file_name' => $finalFileName,
                    'error' => $result['message']
                ]);

                return [
                    'success' => false,
                    'message' => $result['message']
                ];
            }
        } catch (\Exception $e) {
            Log::error('Error uploading video to TikTok', [
                'product_id' => $product->id,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Upload video from template for a product to TikTok Shop
     */
    public function uploadTemplateVideo(Product $product, $fileName = null)
    {
        try {
            if (!$this->integration) {
                throw new \Exception('TikTok Shop integration not found');
            }

            if (!$this->accessToken) {
                throw new \Exception('Access token not available');
            }

            // Check if product has template with video
            if (!$product->productTemplate) {
                throw new \Exception('Product does not have a template');
            }

            $template = $product->productTemplate;
            $videoPath = $template->product_video;

            if (!$videoPath) {
                throw new \Exception('Template does not have a video');
            }

            // Use provided filename or generate from template
            $finalFileName = $fileName ?: basename($videoPath);
            $finalFileName = preg_replace('/[^a-zA-Z0-9._-]/', '_', $finalFileName);

            Log::info('Uploading template video to TikTok:', [
                'product_id' => $product->id,
                'template_id' => $template->id,
                'template_name' => $template->name,
                'video_path' => $videoPath,
                'file_name' => $finalFileName
            ]);

            $result = $this->uploadSingleVideo($videoPath, $finalFileName);

            if ($result['success']) {
                Log::info('Template video uploaded to TikTok successfully', [
                    'product_id' => $product->id,
                    'template_id' => $template->id,
                    'file_name' => $finalFileName,
                    'tiktok_uri' => $result['data']['uri'] ?? null
                ]);

                return [
                    'success' => true,
                    'data' => $result['data'],
                    'file_name' => $finalFileName,
                    'template_id' => $template->id,
                    'template_name' => $template->name
                ];
            } else {
                Log::error('Failed to upload template video to TikTok', [
                    'product_id' => $product->id,
                    'template_id' => $template->id,
                    'file_name' => $finalFileName,
                    'error' => $result['message']
                ]);

                return [
                    'success' => false,
                    'message' => $result['message']
                ];
            }
        } catch (\Exception $e) {
            Log::error('Error uploading template video to TikTok', [
                'product_id' => $product->id,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Upload all videos (product + template) to TikTok Shop
     */
    public function uploadAllVideos(Product $product)
    {
        try {
            if (!$this->integration) {
                throw new \Exception('TikTok Shop integration not found');
            }

            if (!$this->accessToken) {
                throw new \Exception('Access token not available');
            }

            $results = [];
            $uploadedCount = 0;

            // 1. Upload product video if exists
            // (You might need to add a video field to products table)
            // For now, we'll skip this part

            // 2. Upload template video if exists
            if ($product->productTemplate && $product->productTemplate->product_video) {
                $templateResult = $this->uploadTemplateVideo($product);

                if ($templateResult['success']) {
                    $uploadedCount++;
                    $results[] = [
                        'type' => 'template',
                        'result' => $templateResult
                    ];
                } else {
                    $results[] = [
                        'type' => 'template',
                        'result' => $templateResult
                    ];
                }
            }

            return [
                'success' => $uploadedCount > 0,
                'uploaded_count' => $uploadedCount,
                'results' => $results
            ];
        } catch (\Exception $e) {
            Log::error('Error uploading all videos to TikTok', [
                'product_id' => $product->id,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Upload a single image to TikTok Shop
     */
    protected function uploadSingleImage($filePath, $useCase = 'MAIN_IMAGE')
    {
        try {
            $timestamp = time();
            $sign = TikTokSignatureService::generateImageUploadSignature($this->appKey, $this->appSecret, $timestamp);

            $endpoint = '/product/202309/images/upload';
            $url = 'https://open-api.tiktokglobalshop.com' . $endpoint;

            // Đọc file content
            $fileContent = $this->getFileContent($filePath);
            if (!$fileContent) {
                throw new \Exception('Cannot read file content');
            }

            $queryParams = [
                'app_key' => $this->appKey,
                'sign' => $sign,
                'timestamp' => $timestamp
            ];

            $bodyData = [
                'use_case' => $useCase
            ];

            // Log request details
            Log::info('TikTok API Request Details', [
                'url' => $url,
                'headers' => [
                    'x-tts-access-token' => substr($this->accessToken, 0, 20) . '...',
                    'content-type' => 'multipart/form-data'
                ],
                'query_params' => $queryParams,
                'body_data' => $bodyData,
                'app_key_full' => $this->appKey,
                'app_secret_full' => $this->appSecret,
                'access_token_full' => $this->accessToken,
                'file_name' => basename($filePath),
                'file_size' => strlen($fileContent),
                'sign_generation' => [
                    'app_key' => $this->appKey,
                    'app_secret_length' => strlen($this->appSecret),
                    'timestamp' => $timestamp,
                    'sign' => $sign
                ]
            ]);

            $response = Http::withHeaders([
                'x-tts-access-token' => $this->accessToken
            ])->attach('data', $fileContent, basename($filePath))
                ->post($url . '?' . http_build_query($queryParams), $bodyData);

            $result = $response->json();

            // Log response details
            Log::info('TikTok API Response Details', [
                'status_code' => $response->status(),
                'response_body' => $result,
                'response_headers' => $response->headers()
            ]);

            if ($response->successful() && isset($result['code']) && $result['code'] === 0) {
                return [
                    'success' => true,
                    'data' => $result['data']
                ];
            } else {
                return [
                    'success' => false,
                    'message' => $result['message'] ?? 'Upload failed',
                    'response' => $result
                ];
            }
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Get all images for upload (product images + template images)
     */
    protected function getAllImagesForUpload(Product $product)
    {
        $images = [];

        // 1. Lấy hình ảnh sản phẩm
        $productImages = $product->images()->orderBy('sort_order')->get();
        foreach ($productImages as $image) {
            $images[] = [
                'product_image_id' => $image->id,
                'file_name' => $image->file_name,
                'file_path' => $image->file_path,
                'type' => 'product',
                'is_primary' => $image->is_primary
            ];
        }

        // 2. Lấy hình ảnh template (nếu có)
        if ($product->productTemplate && $product->productTemplate->images) {
            $templateImages = $product->productTemplate->images;

            if (is_array($templateImages)) {
                foreach ($templateImages as $index => $templateImage) {
                    if (is_array($templateImage) && isset($templateImage['file_path'])) {
                        // Template image là object có file_path
                        $images[] = [
                            'file_name' => $templateImage['file_name'] ?? 'template_' . ($index + 1),
                            'file_path' => $templateImage['file_path'],
                            'type' => 'template'
                        ];
                    } elseif (is_string($templateImage)) {
                        // Template image là string URL (trong array)
                        $images[] = [
                            'file_name' => 'template_' . ($index + 1) . '.jpg',
                            'file_path' => $templateImage,
                            'type' => 'template'
                        ];
                    }
                }
            } elseif (is_string($templateImages)) {
                // Template image là string URL (single)
                $images[] = [
                    'file_name' => 'template_image.jpg',
                    'file_path' => $templateImages,
                    'type' => 'template'
                ];
            }
        }

        return $images;
    }

    /**
     * Get file content from path (S3, TikTok CDN, or local)
     */
    protected function getFileContent($filePath)
    {
        if (str_contains($filePath, 'amazonaws.com') || str_contains($filePath, 'tiktokcdn-us.com')) {
            // S3 URL hoặc TikTok CDN URL - download content
            try {
                $response = Http::get($filePath);
                if ($response->successful()) {
                    return $response->body();
                }
            } catch (\Exception $e) {
                Log::error('Failed to download file', ['path' => $filePath, 'error' => $e->getMessage()]);
            }
        } else {
            // Local file
            $fullPath = storage_path('app/public/' . $filePath);
            if (file_exists($fullPath)) {
                return file_get_contents($fullPath);
            }
        }

        return null;
    }

    /**
     * Get file size from path
     */
    protected function getFileSize($filePath)
    {
        if (str_contains($filePath, 'amazonaws.com') || str_contains($filePath, 'tiktokcdn-us.com')) {
            // Remote file - get size from headers
            try {
                $response = Http::head($filePath);
                if ($response->successful()) {
                    return (int) $response->header('content-length');
                }
            } catch (\Exception $e) {
                Log::error('Failed to get file size', ['path' => $filePath, 'error' => $e->getMessage()]);
            }
        } else {
            // Local file
            $fullPath = storage_path('app/public/' . $filePath);
            if (file_exists($fullPath)) {
                return filesize($fullPath);
            }
        }

        return 0;
    }

    /**
     * Upload a single video to TikTok Shop
     */
    protected function uploadSingleVideo($filePath, $fileName)
    {
        try {
            $timestamp = time();
            $sign = TikTokSignatureService::generateImageUploadSignature($this->appKey, $this->appSecret, $timestamp);

            $endpoint = '/product/202309/files/upload';
            $url = 'https://open-api.tiktokglobalshop.com' . $endpoint;

            // Đọc file content
            $fileContent = $this->getFileContent($filePath);
            if (!$fileContent) {
                throw new \Exception('Cannot read file content');
            }

            $queryParams = [
                'app_key' => $this->appKey,
                'sign' => $sign,
                'timestamp' => $timestamp
            ];

            // Log request details
            Log::info('TikTok Video Upload Request Details', [
                'url' => $url,
                'headers' => [
                    'x-tts-access-token' => substr($this->accessToken, 0, 20) . '...',
                    'content-type' => 'multipart/form-data'
                ],
                'query_params' => $queryParams,
                'file_name' => $fileName,
                'file_size' => strlen($fileContent),
                'sign_generation' => [
                    'app_key' => $this->appKey,
                    'app_secret_length' => strlen($this->appSecret),
                    'timestamp' => $timestamp,
                    'sign' => $sign
                ]
            ]);

            $response = Http::withHeaders([
                'x-tts-access-token' => $this->accessToken
            ])->attach('data', $fileContent, $fileName)
                ->post($url . '?' . http_build_query($queryParams), [
                    'name' => $fileName
                ]);

            $result = $response->json();

            // Log response details
            Log::info('TikTok Video Upload Response Details', [
                'status_code' => $response->status(),
                'response_body' => $result,
                'response_headers' => $response->headers()
            ]);

            if ($response->successful() && isset($result['code']) && $result['code'] === 0) {
                return [
                    'success' => true,
                    'data' => $result['data']
                ];
            } else {
                return [
                    'success' => false,
                    'message' => $result['message'] ?? 'Upload failed',
                    'response' => $result
                ];
            }
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
}
