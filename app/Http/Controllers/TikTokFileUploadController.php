<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\TikTokShopIntegration;
use App\Models\TikTokFile;
use App\Services\TikTokFileUploadService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class TikTokFileUploadController extends Controller
{
    protected $fileUploadService;

    public function __construct()
    {
        $this->fileUploadService = null;
    }

    /**
     * Upload a single file to TikTok
     */
    public function uploadFile(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'file' => 'required|file|max:102400', // 100MB max
                'use_case' => 'string|in:PRODUCT_VIDEO,CERTIFICATION,REPORT,OTHER',
                'product_id' => 'nullable|exists:products,id',
                'product_template_id' => 'nullable|exists:product_templates,id',
                'source' => 'string|in:manual,template,product',
                'custom_name' => 'nullable|string|max:255',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Get TikTok integration
            $integration = TikTokShopIntegration::where('status', 'active')->first();
            if (!$integration) {
                return response()->json([
                    'success' => false,
                    'message' => 'No active TikTok Shop integration found'
                ], 400);
            }

            // Initialize service
            $this->fileUploadService = new TikTokFileUploadService($integration);

            // Store file temporarily
            $file = $request->file('file');
            $tempPath = $file->store('temp/tiktok-uploads', 'local');
            $fullTempPath = storage_path('app/' . $tempPath);

            try {
                // Upload to TikTok
                $options = [
                    'product_id' => $request->input('product_id'),
                    'product_template_id' => $request->input('product_template_id'),
                    'source' => $request->input('source', 'manual'),
                ];

                $result = $this->fileUploadService->uploadFile(
                    $fullTempPath,
                    $request->input('custom_name'),
                    $request->input('use_case', 'PRODUCT_VIDEO'),
                    $options
                );

                // Clean up temp file
                Storage::disk('local')->delete($tempPath);

                if ($result['success']) {
                    return response()->json([
                        'success' => true,
                        'message' => 'File uploaded successfully',
                        'data' => $result,
                        'tiktok_file_id' => $result['tiktok_file_id'] ?? null
                    ]);
                } else {
                    return response()->json([
                        'success' => false,
                        'message' => 'Failed to upload file to TikTok',
                        'error' => $result['message']
                    ], 500);
                }
            } catch (\Exception $e) {
                // Clean up temp file on error
                Storage::disk('local')->delete($tempPath);
                throw $e;
            }
        } catch (\Exception $e) {
            Log::error('Error in file upload controller', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Internal server error',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Upload multiple files to TikTok
     */
    public function uploadMultipleFiles(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'files.*' => 'required|file|max:102400', // 100MB max per file
                'use_case' => 'string|in:PRODUCT_VIDEO,CERTIFICATION,REPORT,OTHER',
                'product_id' => 'nullable|exists:products,id',
                'product_template_id' => 'nullable|exists:product_templates,id',
                'source' => 'string|in:manual,template,product',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Get TikTok integration
            $integration = TikTokShopIntegration::where('status', 'active')->first();
            if (!$integration) {
                return response()->json([
                    'success' => false,
                    'message' => 'No active TikTok Shop integration found'
                ], 400);
            }

            // Initialize service
            $this->fileUploadService = new TikTokFileUploadService($integration);

            $files = [];
            $tempPaths = [];

            try {
                // Process each file
                foreach ($request->file('files') as $index => $file) {
                    $tempPath = $file->store('temp/tiktok-uploads', 'local');
                    $tempPaths[] = $tempPath;
                    $fullTempPath = storage_path('app/' . $tempPath);

                    $files[] = [
                        'path' => $fullTempPath,
                        'name' => $file->getClientOriginalName(),
                        'options' => [
                            'product_id' => $request->input('product_id'),
                            'product_template_id' => $request->input('product_template_id'),
                            'source' => $request->input('source', 'manual'),
                        ]
                    ];
                }

                // Upload all files
                $options = [
                    'product_id' => $request->input('product_id'),
                    'product_template_id' => $request->input('product_template_id'),
                    'source' => $request->input('source', 'manual'),
                ];

                $result = $this->fileUploadService->uploadMultipleFiles(
                    $files,
                    $request->input('use_case', 'PRODUCT_VIDEO'),
                    $options
                );

                // Clean up temp files
                foreach ($tempPaths as $tempPath) {
                    Storage::disk('local')->delete($tempPath);
                }

                return response()->json([
                    'success' => true,
                    'message' => 'Files processed',
                    'data' => $result
                ]);
            } catch (\Exception $e) {
                // Clean up temp files on error
                foreach ($tempPaths as $tempPath) {
                    Storage::disk('local')->delete($tempPath);
                }
                throw $e;
            }
        } catch (\Exception $e) {
            Log::error('Error in multiple files upload controller', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Internal server error',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get files for a product
     */
    public function getProductFiles(Request $request, $productId)
    {
        try {
            $validator = Validator::make(['product_id' => $productId], [
                'product_id' => 'required|exists:products,id'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid product ID'
                ], 422);
            }

            $fileType = $request->input('file_type');
            $files = TikTokFile::where('product_id', $productId)
                ->when($fileType, function ($query, $fileType) {
                    return $query->where('file_type', $fileType);
                })
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $files
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting product files', [
                'product_id' => $productId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Internal server error'
            ], 500);
        }
    }

    /**
     * Get files for a template
     */
    public function getTemplateFiles(Request $request, $templateId)
    {
        try {
            $validator = Validator::make(['template_id' => $templateId], [
                'template_id' => 'required|exists:product_templates,id'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid template ID'
                ], 422);
            }

            $fileType = $request->input('file_type');
            $files = TikTokFile::where('product_template_id', $templateId)
                ->when($fileType, function ($query, $fileType) {
                    return $query->where('file_type', $fileType);
                })
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $files
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting template files', [
                'template_id' => $templateId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Internal server error'
            ], 500);
        }
    }

    /**
     * Get upload statistics
     */
    public function getUploadStats()
    {
        try {
            $stats = [
                'total_files' => TikTokFile::count(),
                'uploaded_files' => TikTokFile::where('is_uploaded_to_tiktok', true)->count(),
                'failed_files' => TikTokFile::whereNotNull('error_message')->count(),
                'files_by_type' => TikTokFile::selectRaw('file_type, COUNT(*) as count')
                    ->groupBy('file_type')
                    ->get()
                    ->pluck('count', 'file_type'),
                'files_by_source' => TikTokFile::selectRaw('source, COUNT(*) as count')
                    ->groupBy('source')
                    ->get()
                    ->pluck('count', 'source'),
            ];

            $stats['success_rate'] = $stats['total_files'] > 0
                ? round(($stats['uploaded_files'] / $stats['total_files']) * 100, 2)
                : 0;

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting upload stats', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Internal server error'
            ], 500);
        }
    }

    /**
     * Delete a TikTok file record
     */
    public function deleteFile($fileId)
    {
        try {
            $file = TikTokFile::find($fileId);
            if (!$file) {
                return response()->json([
                    'success' => false,
                    'message' => 'File not found'
                ], 404);
            }

            $file->delete();

            return response()->json([
                'success' => true,
                'message' => 'File record deleted successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Error deleting TikTok file', [
                'file_id' => $fileId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Internal server error'
            ], 500);
        }
    }
}
