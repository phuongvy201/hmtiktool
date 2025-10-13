<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ImageUploadController extends Controller
{
    /**
     * Upload hình ảnh lên AWS S3
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function uploadImage(Request $request)
    {
        try {
            // Validate request
            $validator = Validator::make($request->all(), [
                'file' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:10240', // 10MB max
                'folder' => 'sometimes|string|max:100'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $file = $request->file('file');
            $folder = $request->input('folder', 'uploads');

            // Tạo tên file unique
            $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
            $path = $folder . '/' . $filename;

            // Upload lên S3
            $uploaded = Storage::disk('s3')->put($path, file_get_contents($file), 'public');

            if (!$uploaded) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to upload file to S3'
                ], 500);
            }

            // Lấy URL của file đã upload
            $url = Storage::disk('s3')->url($path);

            return response()->json([
                'success' => true,
                'message' => 'File uploaded successfully',
                'data' => [
                    'url' => $url,
                    'path' => $path,
                    'filename' => $filename,
                    'original_name' => $file->getClientOriginalName(),
                    'size' => $file->getSize(),
                    'mime_type' => $file->getMimeType()
                ]
            ]);
        } catch (\Exception $e) {
            \Log::error('Image upload error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while uploading the file',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Upload nhiều hình ảnh cùng lúc
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function uploadMultipleImages(Request $request)
    {
        try {
            // Validate request
            $validator = Validator::make($request->all(), [
                'files' => 'required|array|max:10',
                'files.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:10240',
                'folder' => 'sometimes|string|max:100'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $files = $request->file('files');
            $folder = $request->input('folder', 'uploads');
            $uploadedFiles = [];
            $failedFiles = [];

            foreach ($files as $file) {
                try {
                    // Tạo tên file unique
                    $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
                    $path = $folder . '/' . $filename;

                    // Upload lên S3
                    $uploaded = Storage::disk('s3')->put($path, file_get_contents($file), 'public');

                    if ($uploaded) {
                        $url = Storage::disk('s3')->url($path);
                        $uploadedFiles[] = [
                            'url' => $url,
                            'path' => $path,
                            'filename' => $filename,
                            'original_name' => $file->getClientOriginalName(),
                            'size' => $file->getSize(),
                            'mime_type' => $file->getMimeType()
                        ];
                    } else {
                        $failedFiles[] = $file->getClientOriginalName();
                    }
                } catch (\Exception $e) {
                    $failedFiles[] = $file->getClientOriginalName();
                    \Log::error('Failed to upload file: ' . $file->getClientOriginalName() . ' - ' . $e->getMessage());
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Upload completed',
                'data' => [
                    'uploaded' => $uploadedFiles,
                    'failed' => $failedFiles,
                    'total_uploaded' => count($uploadedFiles),
                    'total_failed' => count($failedFiles)
                ]
            ]);
        } catch (\Exception $e) {
            \Log::error('Multiple images upload error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while uploading files',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Xóa hình ảnh từ S3
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteImage(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'path' => 'required|string'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $path = $request->input('path');

            // Xóa file từ S3
            $deleted = Storage::disk('s3')->delete($path);

            if ($deleted) {
                return response()->json([
                    'success' => true,
                    'message' => 'File deleted successfully'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to delete file'
                ], 500);
            }
        } catch (\Exception $e) {
            \Log::error('Image delete error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while deleting the file',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }
}
