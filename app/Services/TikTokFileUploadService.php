<?php

namespace App\Services;

use App\Models\TikTokShopIntegration;
use App\Models\TikTokFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Services\TikTokSignatureService;

class TikTokFileUploadService
{
    protected $integration;
    protected $appKey;
    protected $appSecret;
    protected $accessToken;

    public function __construct(TikTokShopIntegration $integration = null)
    {
        if ($integration) {
            $this->integration = $integration;

            // Lấy credentials theo market của integration
            $this->appKey = $integration->getAppKey();
            $this->appSecret = $integration->getAppSecret();

            // Fallback nếu chưa cấu hình market-specific
            if (empty($this->appKey)) {
                $this->appKey = config('tiktok-shop.app_key') ?? env('TIKTOK_SHOP_APP_KEY');
            }

            if (empty($this->appSecret)) {
                $this->appSecret = config('tiktok-shop.app_secret') ?? env('TIKTOK_SHOP_APP_SECRET');
            }

            $this->accessToken = $integration->access_token;

            if (empty($this->appKey) || empty($this->appSecret)) {
                Log::warning('TikTok credentials missing in TikTokFileUploadService', [
                    'integration_id' => $integration->id,
                    'market' => $integration->market,
                    'resolved_app_key' => $this->appKey ? 'available' : 'missing',
                    'resolved_app_secret' => $this->appSecret ? 'available' : 'missing'
                ]);
            }
        }
    }

    /**
     * Upload file (PDF, video) to TikTok Shop
     */
    public function uploadFile($filePath, $fileName = null, $useCase = 'PRODUCT_VIDEO', $options = [])
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
            $fileExtension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

            if (!in_array($fileExtension, $allowedFormats)) {
                throw new \Exception("Unsupported file format: {$fileExtension}. Supported formats: " . implode(', ', $allowedFormats));
            }

            // Validate file size
            $fileSize = $this->getFileSize($filePath);
            $maxSize = $fileExtension === 'pdf' ? 20 * 1024 * 1024 : 100 * 1024 * 1024; // 20MB for PDF, 100MB for video

            if ($fileSize > $maxSize) {
                $maxSizeMB = $fileExtension === 'pdf' ? 20 : 100;
                throw new \Exception("File size exceeds limit: {$maxSizeMB}MB");
            }

            // Use provided filename or generate from path
            $finalFileName = $fileName ?: basename($filePath);

            // Clean filename (remove spaces, special characters, ensure proper extension)
            $finalFileName = $this->cleanFileName($finalFileName, $fileExtension);

            Log::info('Uploading file to TikTok:', [
                'file_path' => $filePath,
                'file_name' => $finalFileName,
                'file_size' => $fileSize,
                'file_extension' => $fileExtension,
                'use_case' => $useCase,
                'options' => $options
            ]);

            // Create TikTokFile record before upload
            $tiktokFile = TikTokFile::create([
                'tiktok_shop_integration_id' => $this->integration->id,
                'product_id' => $options['product_id'] ?? null,
                'product_template_id' => $options['product_template_id'] ?? null,
                'file_name' => $finalFileName,
                'file_path' => $filePath,
                'file_type' => $fileExtension,
                'source' => $options['source'] ?? 'manual',
                'use_case' => $useCase,
                'file_size' => $fileSize,
                'is_uploaded_to_tiktok' => false,
            ]);

            $result = $this->uploadSingleFile($filePath, $finalFileName, $useCase);

            if ($result['success']) {
                // Update TikTokFile record with success info
                $tiktokFile->markAsUploadedToTiktok(
                    $result['data']['uri'] ?? null,
                    $result['data']['url'] ?? null,
                    $result['data']['id'] ?? null,
                    $result
                );

                Log::info('File uploaded to TikTok successfully', [
                    'tiktok_file_id' => $tiktokFile->id,
                    'file_name' => $finalFileName,
                    'tiktok_uri' => $result['data']['uri'] ?? null,
                    'tiktok_url' => $result['data']['url'] ?? null
                ]);

                return [
                    'success' => true,
                    'data' => $result['data'],
                    'file_name' => $finalFileName,
                    'file_size' => $fileSize,
                    'file_extension' => $fileExtension,
                    'tiktok_file_id' => $tiktokFile->id
                ];
            } else {
                // Update TikTokFile record with error info
                $tiktokFile->markAsUploadFailed($result['message'], $result);

                Log::error('Failed to upload file to TikTok', [
                    'tiktok_file_id' => $tiktokFile->id,
                    'file_name' => $finalFileName,
                    'error' => $result['message']
                ]);

                return [
                    'success' => false,
                    'message' => $result['message'],
                    'tiktok_file_id' => $tiktokFile->id
                ];
            }
        } catch (\Exception $e) {
            Log::error('Error uploading file to TikTok', [
                'file_path' => $filePath,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Upload multiple files to TikTok Shop
     */
    public function uploadMultipleFiles($files, $useCase = 'PRODUCT_VIDEO', $options = [])
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
            $failedCount = 0;

            foreach ($files as $fileData) {
                $filePath = $fileData['path'];
                $fileName = $fileData['name'] ?? null;
                $fileOptions = array_merge($options, $fileData['options'] ?? []);

                $result = $this->uploadFile($filePath, $fileName, $useCase, $fileOptions);

                if ($result['success']) {
                    $uploadedCount++;
                    $results[] = [
                        'status' => 'success',
                        'data' => $result
                    ];
                } else {
                    $failedCount++;
                    $results[] = [
                        'status' => 'failed',
                        'data' => $result
                    ];
                }
            }

            return [
                'success' => $uploadedCount > 0,
                'uploaded_count' => $uploadedCount,
                'failed_count' => $failedCount,
                'total_files' => count($files),
                'results' => $results
            ];
        } catch (\Exception $e) {
            Log::error('Error uploading multiple files to TikTok', [
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Upload a single file to TikTok Shop
     */
    protected function uploadSingleFile($filePath, $fileName, $useCase = 'PRODUCT_VIDEO')
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
            Log::info('TikTok File Upload Request Details', [
                'url' => $url,
                'headers' => [
                    'x-tts-access-token' => substr($this->accessToken, 0, 20) . '...',
                    'content-type' => 'multipart/form-data'
                ],
                'query_params' => $queryParams,
                'file_name' => $fileName,
                'file_size' => strlen($fileContent),
                'use_case' => $useCase,
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
            Log::info('TikTok File Upload Response Details', [
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
     * Clean filename according to TikTok requirements
     */
    protected function cleanFileName($fileName, $extension)
    {
        // Remove file extension first
        $nameWithoutExt = pathinfo($fileName, PATHINFO_FILENAME);

        // Remove special characters, spaces, and symbols
        $cleanedName = preg_replace('/[^a-zA-Z0-9._-]/', '_', $nameWithoutExt);

        // Ensure it doesn't start with symbols
        $cleanedName = ltrim($cleanedName, '._-');

        // If empty after cleaning, use default name
        if (empty($cleanedName)) {
            $cleanedName = 'file';
        }

        // Add extension back
        return $cleanedName . '.' . $extension;
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
     * Validate file format and size
     */
    public function validateFile($filePath)
    {
        $allowedFormats = ['pdf', 'mp4', 'mov', 'mkv', 'wmv', 'webm', 'avi', '3gp', 'flv', 'mpeg'];
        $fileExtension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

        if (!in_array($fileExtension, $allowedFormats)) {
            return [
                'valid' => false,
                'message' => "Unsupported file format: {$fileExtension}. Supported formats: " . implode(', ', $allowedFormats)
            ];
        }

        $fileSize = $this->getFileSize($filePath);
        $maxSize = $fileExtension === 'pdf' ? 20 * 1024 * 1024 : 100 * 1024 * 1024;

        if ($fileSize > $maxSize) {
            $maxSizeMB = $fileExtension === 'pdf' ? 20 : 100;
            return [
                'valid' => false,
                'message' => "File size exceeds limit: {$maxSizeMB}MB"
            ];
        }

        return [
            'valid' => true,
            'file_extension' => $fileExtension,
            'file_size' => $fileSize,
            'max_size' => $maxSize
        ];
    }

    /**
     * Get all files for a product
     */
    public function getProductFiles($productId, $fileType = null)
    {
        $query = TikTokFile::where('product_id', $productId);

        if ($fileType) {
            $query->byType($fileType);
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    /**
     * Get all files for a template
     */
    public function getTemplateFiles($templateId, $fileType = null)
    {
        $query = TikTokFile::where('product_template_id', $templateId);

        if ($fileType) {
            $query->byType($fileType);
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    /**
     * Get upload statistics
     */
    public function getUploadStats()
    {
        $totalFiles = TikTokFile::count();
        $uploadedFiles = TikTokFile::uploadedToTiktok()->count();
        $failedFiles = TikTokFile::whereNotNull('error_message')->count();

        $filesByType = TikTokFile::selectRaw('file_type, COUNT(*) as count')
            ->groupBy('file_type')
            ->get()
            ->pluck('count', 'file_type');

        $filesBySource = TikTokFile::selectRaw('source, COUNT(*) as count')
            ->groupBy('source')
            ->get()
            ->pluck('count', 'source');

        return [
            'total_files' => $totalFiles,
            'uploaded_files' => $uploadedFiles,
            'failed_files' => $failedFiles,
            'success_rate' => $totalFiles > 0 ? round(($uploadedFiles / $totalFiles) * 100, 2) : 0,
            'files_by_type' => $filesByType,
            'files_by_source' => $filesBySource,
        ];
    }
}
