<?php

namespace App\Http\Controllers;

use App\Models\ProductTemplate;
use App\Models\ProductTemplateOption;
use App\Models\ProductTemplateOptionValue;
use App\Models\ProductTemplateVariant;
use App\Models\TikTokShopIntegration;
use App\Policies\ProductTemplatePolicy;
use App\Services\TikTokShopService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ProductTemplateController extends Controller
{
    protected $tikTokShopService;



    public function __construct(TikTokShopService $tikTokShopService)
    {
        $this->tikTokShopService = $tikTokShopService;
        // Disabled authorizeResource to avoid permission issues
        // $this->authorizeResource(ProductTemplate::class, 'product_template');
    }

    public function index()
    {
        $user = Auth::user();

        // Sử dụng Policy để lấy templates mà user có quyền xem
        $policy = new ProductTemplatePolicy();
        $templatesQuery = $policy->getViewableTemplates($user);

        $templates = $templatesQuery
            ->with(['options.values', 'variants', 'user'])
            ->orderBy('created_at', 'desc')
            ->paginate(12);

        return view('product-templates.index', compact('templates'));
    }

    public function create()
    {
        $categories = $this->getCategories();

        // Nếu có old category, lấy tên category để hiển thị
        $oldCategoryName = '';
        if (old('category')) {
            $oldCategoryName = $categories[old('category')] ?? '';
        }

        return view('product-templates.create', compact('categories', 'oldCategoryName'));
    }

    public function store(Request $request)
    {
        Log::info('=== START PRODUCT TEMPLATE STORE ===');
        Log::info('Request data (without files):', $request->except(['image_files']));
        Log::info('Has image_files?', ['has_files' => $request->hasFile('image_files')]);

        if ($request->hasFile('image_files')) {
            $files = $request->file('image_files');
            Log::info('Image files info:', [
                'count' => count($files),
                'files' => array_map(function ($file) {
                    return [
                        'name' => $file->getClientOriginalName(),
                        'size' => $file->getSize(),
                        'mime' => $file->getMimeType(),
                        'valid' => $file->isValid()
                    ];
                }, $files)
            ]);
        } else {
            Log::info('No image files found in request');
        }


        Log::info('About to validate request data:', [
            'name' => $request->input('name'),
            'base_price' => $request->input('base_price'),
            'has_variants' => $request->has('variants'),
            'variants_count' => $request->has('variants') ? count($request->input('variants', [])) : 0
        ]);

        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'category' => 'nullable|string|max:255',
                'base_price' => 'required|numeric|min:0',
                'list_price' => 'nullable|numeric|min:0',
                'weight' => 'nullable|numeric|min:0',
                'height' => 'nullable|numeric|min:0',
                'width' => 'nullable|numeric|min:0',
                'length' => 'nullable|numeric|min:0',
                'images' => 'nullable|array',
                'images.*' => 'nullable|string|url',
                'image_files' => 'nullable|array',
                'image_files.*' => 'nullable|file|image|mimes:jpeg,png,jpg,gif,webp|max:10240', // 10MB max
                'size_chart_files' => 'nullable|array',
                'size_chart_files.*' => 'nullable|file|image|mimes:jpeg,png,jpg,gif,webp|max:10240',
                'bulk_images_files' => 'nullable|array',
                'bulk_images_files.*' => 'nullable|file|image|mimes:jpeg,png,jpg,gif,webp|max:10240',
                'product_video_files' => 'nullable|array',
                'product_video_files.*' => 'nullable|file|mimes:mp4,avi,mov,wmv,flv,webm|max:102400', // 100MB max for video
                'size_chart' => 'nullable|string',
                'product_video' => 'nullable|string',
                'options' => 'nullable|array',
                'options.*.name' => 'nullable|string',
                'options.*.values_string' => 'nullable|string',
                'variants' => 'nullable|array',
                'variants.*.price' => 'required_with:variants|numeric|min:0',
                'variants.*.list_price' => 'nullable|numeric|min:0',
                'variants.*.quantity' => 'required_with:variants|integer|min:0',
                'variants.*.image' => 'nullable|string',
                'variants.*.image_file' => 'nullable|file|image|mimes:jpeg,png,jpg,gif,webp|max:10240',
                'variants.*.combination' => 'nullable|string',
                'variants.*.combination_string' => 'nullable|string',
                'attributes' => 'nullable|array',
                'attributes.*' => 'nullable',

            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation failed:', [
                'errors' => $e->errors(),
                'message' => $e->getMessage()
            ]);

            // Handle AJAX requests
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $e->errors()
                ], 422);
            }

            return back()->withErrors($e->errors())->withInput();
        }

        DB::beginTransaction();
        try {
            Log::info('Processing images from request');

            // Process images from both S3 URLs and uploaded files
            $images = [];

            // Handle images array (contains S3 URLs from JavaScript upload)
            if ($request->has('images') && is_array($request->images)) {
                Log::info('Processing images array:', $request->images);
                foreach ($request->images as $imageUrl) {
                    if (!empty($imageUrl) && filter_var($imageUrl, FILTER_VALIDATE_URL)) {
                        $images[] = $imageUrl;
                    }
                }
            }

            // Handle file uploads from form
            if ($request->hasFile('image_files')) {
                Log::info('Processing file uploads from form');
                $uploadedFiles = $request->file('image_files');
                Log::info('Files to upload:', ['count' => count($uploadedFiles)]);

                foreach ($uploadedFiles as $index => $file) {
                    Log::info("Processing file {$index}:", [
                        'name' => $file->getClientOriginalName(),
                        'size' => $file->getSize(),
                        'valid' => $file->isValid(),
                        'temp_path' => $file->getPathname()
                    ]);

                    if ($file->isValid()) {
                        try {
                            // Upload to S3
                            $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
                            $path = 'product-images/' . $filename;

                            Log::info("Uploading to S3:", [
                                'path' => $path,
                                'filename' => $filename,
                                'file_size' => $file->getSize(),
                                'bucket' => config('filesystems.disks.s3.bucket'),
                                'region' => config('filesystems.disks.s3.region')
                            ]);

                            // Test S3 connection first
                            try {
                                $testResult = Storage::disk('s3')->exists('test-connection');
                                Log::info("S3 connection test:", ['can_connect' => true, 'test_result' => $testResult]);
                            } catch (\Exception $testE) {
                                Log::error("S3 connection test failed:", [
                                    'error' => $testE->getMessage(),
                                    'code' => $testE->getCode()
                                ]);
                            }

                            $uploaded = Storage::disk('s3')->put($path, file_get_contents($file));

                            Log::info("S3 upload result:", ['uploaded' => $uploaded]);

                            if ($uploaded) {
                                // Generate S3 URL manually
                                $bucket = config('filesystems.disks.s3.bucket');
                                $region = config('filesystems.disks.s3.region');
                                $url = "https://{$bucket}.s3.{$region}.amazonaws.com/{$path}";
                                $images[] = $url;
                                Log::info('Successfully uploaded file to S3:', [
                                    'url' => $url,
                                    'bucket' => $bucket,
                                    'region' => $region,
                                    'path' => $path
                                ]);
                            } else {
                                Log::error('S3 upload returned false');
                            }
                        } catch (\Exception $e) {
                            Log::error('Error uploading file:', [
                                'error' => $e->getMessage(),
                                'file' => $e->getFile(),
                                'line' => $e->getLine(),
                                'trace' => $e->getTraceAsString()
                            ]);
                        }
                    } else {
                        Log::error("File {$index} is not valid:", [
                            'name' => $file->getClientOriginalName(),
                            'error' => $file->getErrorMessage()
                        ]);
                    }
                }
            } else {
                Log::info('No image_files found in request - checking all request files');
                Log::info('All files in request:', array_keys($request->allFiles()));
            }

            Log::info('Final images array:', $images);

            // Handle size chart file upload
            $sizeChartUrl = null;
            if ($request->hasFile('size_chart_files')) {
                Log::info('Processing size chart file upload');
                $sizeChartFiles = $request->file('size_chart_files');

                foreach ($sizeChartFiles as $file) {
                    if ($file->isValid()) {
                        try {
                            $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
                            $path = 'size-charts/' . $filename;

                            $uploaded = Storage::disk('s3')->put($path, file_get_contents($file));

                            if ($uploaded) {
                                $bucket = config('filesystems.disks.s3.bucket');
                                $region = config('filesystems.disks.s3.region');
                                $sizeChartUrl = "https://{$bucket}.s3.{$region}.amazonaws.com/{$path}";
                                Log::info('Successfully uploaded size chart to S3:', ['url' => $sizeChartUrl]);
                                break; // Only take the first file for size chart
                            }
                        } catch (\Exception $e) {
                            Log::error('Error uploading size chart file:', ['error' => $e->getMessage()]);
                        }
                    }
                }
            }

            // Handle bulk images file upload (for future use)
            $bulkImagesUrls = [];
            if ($request->hasFile('bulk_images_files')) {
                Log::info('Processing bulk images file upload');
                $bulkImageFiles = $request->file('bulk_images_files');

                foreach ($bulkImageFiles as $file) {
                    if ($file->isValid()) {
                        try {
                            $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
                            $path = 'bulk-images/' . $filename;

                            $uploaded = Storage::disk('s3')->put($path, file_get_contents($file));

                            if ($uploaded) {
                                $bucket = config('filesystems.disks.s3.bucket');
                                $region = config('filesystems.disks.s3.region');
                                $url = "https://{$bucket}.s3.{$region}.amazonaws.com/{$path}";
                                $bulkImagesUrls[] = $url;
                                Log::info('Successfully uploaded bulk image to S3:', ['url' => $url]);
                            }
                        } catch (\Exception $e) {
                            Log::error('Error uploading bulk image file:', ['error' => $e->getMessage()]);
                        }
                    }
                }
            }

            // Handle product video file upload
            $productVideoUrl = null;
            if ($request->hasFile('product_video_files')) {
                Log::info('Processing product video file upload');
                $videoFiles = $request->file('product_video_files');

                foreach ($videoFiles as $file) {
                    if ($file->isValid()) {
                        try {
                            // Upload video to S3
                            $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
                            $path = 'product-videos/' . $filename;

                            Log::info("Uploading video to S3:", [
                                'path' => $path,
                                'filename' => $filename,
                                'file_size' => $file->getSize(),
                                'mime_type' => $file->getMimeType()
                            ]);

                            $uploaded = Storage::disk('s3')->put($path, file_get_contents($file));

                            if ($uploaded) {
                                // Generate S3 URL manually
                                $bucket = config('filesystems.disks.s3.bucket');
                                $region = config('filesystems.disks.s3.region');
                                $productVideoUrl = "https://{$bucket}.s3.{$region}.amazonaws.com/{$path}";
                                Log::info('Successfully uploaded video to S3:', ['url' => $productVideoUrl]);
                                break; // Only take the first video
                            } else {
                                Log::error('S3 video upload returned false');
                            }
                        } catch (\Exception $e) {
                            Log::error('Error uploading video file:', ['error' => $e->getMessage()]);
                        }
                    }
                }
            }

            Log::info('Creating ProductTemplate with data:', [
                'user_id' => Auth::user()->id,
                'team_id' => Auth::user()->team->id,
                'name' => $request->name,
                'category' => $request->category,
                'base_price' => $request->base_price,
                'images_count' => count($images),
                'size_chart_url' => $sizeChartUrl,
                'product_video_url' => $productVideoUrl,
                'bulk_images_count' => count($bulkImagesUrls)
            ]);

            $template = ProductTemplate::create([
                'user_id' => Auth::user()->id,
                'team_id' => Auth::user()->team->id,
                'name' => $request->name,
                'description' => $request->description,
                'category_id' => $request->category, // Sửa từ 'category' thành 'category_id'
                'status' => 'published',
                'base_price' => $request->base_price,
                'list_price' => $request->list_price,
                'weight' => $request->weight,
                'height' => $request->height,
                'width' => $request->width,
                'length' => $request->length,
                'images' => $images,
                'size_chart' => $sizeChartUrl ?? $request->size_chart,
                'product_video' => $productVideoUrl ?? $request->product_video,
            ]);

            Log::info('ProductTemplate created successfully with ID:', ['id' => $template->id]);

            // Create options and values
            if ($request->options) {
                Log::info('Processing options:', $request->options);

                foreach ($request->options as $index => $optionData) {
                    Log::info("Creating option {$index}:", $optionData);

                    $option = $template->options()->create([
                        'name' => $optionData['name'],
                        'type' => 'custom', // Default type
                        'is_required' => false, // Default to not required
                        'sort_order' => $index,
                    ]);

                    // Parse values from comma-separated string
                    $values = array_map('trim', explode(',', $optionData['values_string']));
                    Log::info("Values for option {$optionData['name']}:", $values);

                    foreach ($values as $valueIndex => $value) {
                        if (!empty($value)) {
                            $option->values()->create([
                                'value' => $value,
                                'label' => $value, // Use value as label
                                'sort_order' => $valueIndex,
                            ]);
                        }
                    }
                }
            }

            // Process variants - either from request or generate automatically
            if ($request->variants && !empty($request->variants)) {
                Log::info('Processing variants from request:', ['count' => count($request->variants)]);

                foreach ($request->variants as $index => $variantData) {
                    Log::info("Processing variant {$index}:", $variantData);

                    // Handle variant image file upload
                    $variantImageUrl = $variantData['image'] ?? null;

                    if ($request->hasFile("variants.{$index}.image_file")) {
                        Log::info("Processing variant {$index} image file upload");
                        $variantImageFile = $request->file("variants.{$index}.image_file");

                        if ($variantImageFile && $variantImageFile->isValid()) {
                            try {
                                $filename = Str::uuid() . '.' . $variantImageFile->getClientOriginalExtension();
                                $path = 'variant-images/' . $filename;

                                $uploaded = Storage::disk('s3')->put($path, file_get_contents($variantImageFile));

                                if ($uploaded) {
                                    $bucket = config('filesystems.disks.s3.bucket');
                                    $region = config('filesystems.disks.s3.region');
                                    $variantImageUrl = "https://{$bucket}.s3.{$region}.amazonaws.com/{$path}";
                                    Log::info('Successfully uploaded variant image to S3:', ['url' => $variantImageUrl]);
                                }
                            } catch (\Exception $e) {
                                Log::error('Error uploading variant image file:', ['error' => $e->getMessage()]);
                            }
                        }
                    }

                    // Create variant
                    $variant = $template->variants()->create([
                        'sku' => $this->generateSkuFromVariantData($template, $variantData),
                        'price' => $variantData['price'],
                        'list_price' => $variantData['list_price'] ?? $variantData['price'],
                        'stock_quantity' => $variantData['quantity'],
                        'variant_data' => [
                            'image' => $variantImageUrl,
                            'combination_string' => $variantData['combination_string'] ?? null,
                        ],
                    ]);

                    // Process combination if provided
                    if (isset($variantData['combination']) && !empty($variantData['combination'])) {
                        Log::info("Processing combination data for variant {$index}:", ['combination' => $variantData['combination']]);

                        // Decode combination if it's URL encoded
                        $combinationData = $variantData['combination'];
                        if (is_string($combinationData)) {
                            $combinationData = urldecode($combinationData);
                            $combinationData = json_decode($combinationData, true);
                        }

                        Log::info("Decoded combination data:", $combinationData);
                    }

                    if (isset($variantData['combination_string']) && !empty($variantData['combination_string'])) {
                        Log::info("Processing combination string for variant {$index}:", ['combination_string' => $variantData['combination_string']]);

                        // Parse combination string to find option values
                        $combinationParts = explode(' / ', $variantData['combination_string']);
                        $pivotData = [];

                        foreach ($combinationParts as $part) {
                            $optionValuePair = explode(': ', trim($part));
                            if (count($optionValuePair) == 2) {
                                $optionName = trim($optionValuePair[0]);
                                $optionValue = trim($optionValuePair[1]);

                                // Find the option value record
                                $optionValueRecord = ProductTemplateOptionValue::whereHas('option', function ($query) use ($optionName, $template) {
                                    $query->where('name', $optionName)
                                        ->where('product_template_id', $template->id);
                                })->where('value', $optionValue)->first();

                                if ($optionValueRecord) {
                                    $pivotData[] = [
                                        'prod_template_variant_id' => $variant->id,
                                        'prod_template_option_id' => $optionValueRecord->prod_template_option_id,
                                        'prod_option_value_id' => $optionValueRecord->id,
                                    ];
                                }
                            }
                        }

                        if (!empty($pivotData)) {
                            DB::table('prod_variant_options')->insert($pivotData);
                            Log::info("Created pivot records for variant {$index}:", $pivotData);
                        }
                    }
                }
            } else {
                // Generate variants automatically if no variants provided
                Log::info('Generating variants automatically for template');
                $this->generateVariants($template);
            }

            // Save category attributes if provided
            if ($request->has('attributes')) {
                $attributesData = $request->input('attributes');
                Log::info('Raw attributes data:', ['attributes' => $attributesData]);

                if (is_array($attributesData)) {
                    Log::info('Processing category attributes:', $attributesData);

                    $attributes = array_filter($attributesData, function ($value) {
                        return !empty($value);
                    });

                    Log::info('Filtered attributes:', $attributes);

                    if (!empty($attributes)) {
                        Log::info('Saving category attributes:', $attributes);
                        Log::info('Template ID:', ['id' => $template->id]);
                        Log::info('Category ID:', ['category_id' => $request->category]);

                        try {
                            \App\Models\ProdTemplateCategoryAttribute::saveTemplateAttributes(
                                $template->id,
                                $request->category, // Giữ nguyên vì đây là category ID
                                $attributes
                            );
                            Log::info('Category attributes saved successfully');
                        } catch (\Exception $e) {
                            Log::error('Error saving category attributes:', ['error' => $e->getMessage()]);
                            throw $e;
                        }
                    } else {
                        Log::info('No valid attributes to save after filtering');
                    }
                } else {
                    Log::info('Attributes is not an array, type:', ['type' => gettype($request->attributes)]);
                }
            } else {
                Log::info('No attributes provided in request');
            }

            DB::commit();


            Log::info('=== END PRODUCT TEMPLATE STORE - SUCCESS ===');

            // Handle AJAX requests
            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Template sản phẩm đã được tạo thành công.',
                    'redirect' => route('product-templates.index')
                ]);
            }

            return redirect()->route('product-templates.index')
                ->with('success', 'Template sản phẩm đã được tạo thành công.');
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('=== PRODUCT TEMPLATE STORE ERROR ===');
            Log::error('Error message: ' . $e->getMessage());
            Log::error('Error file: ' . $e->getFile() . ':' . $e->getLine());
            Log::error('Error trace: ' . $e->getTraceAsString());

            // Handle AJAX requests
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Có lỗi xảy ra khi tạo template: ' . $e->getMessage()
                ], 500);
            }

            return back()->withInput()->with('error', 'Có lỗi xảy ra khi tạo template: ' . $e->getMessage());
        }
    }



    public function show(ProductTemplate $productTemplate)
    {
        $user = Auth::user();

        // Kiểm tra quyền truy cập
        if (!$user->hasRole('team-admin') && $productTemplate->user_id !== $user->id) {
            abort(403, 'Bạn không có quyền truy cập template này.');
        }

        $productTemplate->load(['options.values', 'variants.optionValues.option', 'categoryAttributes']);

        // Get category name
        $categories = $this->getCategories();
        $productTemplate->category_name = $categories[$productTemplate->category_id] ?? $productTemplate->category_id;

        return view('product-templates.show', compact('productTemplate'));
    }

    public function edit(ProductTemplate $productTemplate)
    {
        $user = Auth::user();

        // Kiểm tra quyền truy cập
        if (!$user->hasRole('team-admin') && $productTemplate->user_id !== $user->id) {
            abort(403, 'Bạn không có quyền chỉnh sửa template này.');
        }

        $productTemplate->load(['options.values', 'variants.optionValues.option', 'categoryAttributes']);
        $categories = $this->getCategories();

        // Get old category name for display
        $oldCategoryName = '';
        if ($productTemplate->category) {
            $oldCategoryName = $categories[$productTemplate->category] ?? '';
        }

        return view('product-templates.edit', compact('productTemplate', 'categories', 'oldCategoryName'));
    }

    public function update(Request $request, ProductTemplate $productTemplate)
    {
        Log::info('=== START PRODUCT TEMPLATE UPDATE ===');
        Log::info('Request data (without files):', $request->except(['image_files', 'size_chart_files', 'product_video_files', 'bulk_images_files']));

        $user = Auth::user();

        // Kiểm tra quyền truy cập
        if (!$user->hasRole('team-admin') && $productTemplate->user_id !== $user->id) {
            abort(403, 'Bạn không có quyền cập nhật template này.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'required|string|max:255',
            'base_price' => 'required|numeric|min:0',
            'list_price' => 'nullable|numeric|min:0',
            'weight' => 'nullable|numeric|min:0',
            'height' => 'nullable|numeric|min:0',
            'width' => 'nullable|numeric|min:0',
            'length' => 'nullable|numeric|min:0',
            'images' => 'nullable|array',
            'images.*' => 'nullable|string|url',
            'image_files' => 'nullable|array',
            'image_files.*' => 'nullable|file|image|mimes:jpeg,png,jpg,gif,webp|max:10240', // 10MB max
            'size_chart_files' => 'nullable|array',
            'size_chart_files.*' => 'nullable|file|image|mimes:jpeg,png,jpg,gif,webp|max:10240',
            'product_video_files' => 'nullable|array',
            'product_video_files.*' => 'nullable|file|mimes:mp4,avi,mov,wmv,flv,webm|max:102400', // 100MB max for video
            'bulk_images_files' => 'nullable|array',
            'bulk_images_files.*' => 'nullable|file|image|mimes:jpeg,png,jpg,gif,webp|max:10240',
            'bulk_image_file' => 'nullable|file|image|mimes:jpeg,png,jpg,gif,webp|max:10240',
            'bulk_image_variants' => 'nullable|string',
            'variants' => 'nullable|array',
            'variants.*.id' => 'nullable|exists:prod_template_variants,id',
            'variants.*.image_file' => 'nullable|file|image|mimes:jpeg,png,jpg,gif,webp|max:10240',
            'attributes' => 'nullable|array',
            'attributes.*' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            Log::info('Processing images from request');

            // Process images from both S3 URLs and uploaded files
            $images = [];

            // Handle images array (contains S3 URLs from JavaScript upload)
            if ($request->has('images') && is_array($request->images)) {
                Log::info('Processing images array:', $request->images);
                foreach ($request->images as $imageUrl) {
                    if (!empty($imageUrl) && filter_var($imageUrl, FILTER_VALIDATE_URL)) {
                        $images[] = $imageUrl;
                    }
                }
            }

            // Handle file uploads from form
            if ($request->hasFile('image_files')) {
                Log::info('Processing file uploads from form');
                $uploadedFiles = $request->file('image_files');
                Log::info('Files to upload:', ['count' => count($uploadedFiles)]);

                foreach ($uploadedFiles as $index => $file) {
                    Log::info("Processing file {$index}:", [
                        'name' => $file->getClientOriginalName(),
                        'size' => $file->getSize(),
                        'valid' => $file->isValid(),
                        'temp_path' => $file->getPathname()
                    ]);

                    if ($file->isValid()) {
                        try {
                            // Upload to S3
                            $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
                            $path = 'product-images/' . $filename;

                            Log::info("Uploading to S3:", [
                                'path' => $path,
                                'filename' => $filename,
                                'file_size' => $file->getSize(),
                                'bucket' => config('filesystems.disks.s3.bucket'),
                                'region' => config('filesystems.disks.s3.region')
                            ]);

                            $uploaded = Storage::disk('s3')->put($path, file_get_contents($file));

                            Log::info("S3 upload result:", ['uploaded' => $uploaded]);

                            if ($uploaded) {
                                // Generate S3 URL manually
                                $bucket = config('filesystems.disks.s3.bucket');
                                $region = config('filesystems.disks.s3.region');
                                $url = "https://{$bucket}.s3.{$region}.amazonaws.com/{$path}";
                                $images[] = $url;
                                Log::info('Successfully uploaded file to S3:', [
                                    'url' => $url,
                                    'bucket' => $bucket,
                                    'region' => $region,
                                    'path' => $path
                                ]);
                            } else {
                                Log::error('S3 upload returned false');
                            }
                        } catch (\Exception $e) {
                            Log::error('Error uploading file:', [
                                'error' => $e->getMessage(),
                                'file' => $e->getFile(),
                                'line' => $e->getLine(),
                                'trace' => $e->getTraceAsString()
                            ]);
                        }
                    } else {
                        Log::error("File {$index} is not valid:", [
                            'name' => $file->getClientOriginalName(),
                            'error' => $file->getErrorMessage()
                        ]);
                    }
                }
            }

            Log::info('Final images array:', $images);

            // Handle size chart file upload
            $sizeChartUrl = $productTemplate->size_chart; // Keep existing if no new upload
            if ($request->hasFile('size_chart_files')) {
                Log::info('Processing size chart file upload');
                $sizeChartFiles = $request->file('size_chart_files');

                foreach ($sizeChartFiles as $file) {
                    if ($file->isValid()) {
                        try {
                            $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
                            $path = 'size-charts/' . $filename;

                            $uploaded = Storage::disk('s3')->put($path, file_get_contents($file));

                            if ($uploaded) {
                                $bucket = config('filesystems.disks.s3.bucket');
                                $region = config('filesystems.disks.s3.region');
                                $sizeChartUrl = "https://{$bucket}.s3.{$region}.amazonaws.com/{$path}";
                                Log::info('Successfully uploaded size chart to S3:', ['url' => $sizeChartUrl]);
                                break; // Only take the first file for size chart
                            }
                        } catch (\Exception $e) {
                            Log::error('Error uploading size chart file:', ['error' => $e->getMessage()]);
                        }
                    }
                }
            }

            // Handle product video file upload
            $productVideoUrl = $productTemplate->product_video; // Keep existing if no new upload
            if ($request->hasFile('product_video_files')) {
                Log::info('Processing product video file upload');
                $videoFiles = $request->file('product_video_files');

                foreach ($videoFiles as $file) {
                    if ($file->isValid()) {
                        try {
                            // Upload video to S3
                            $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
                            $path = 'product-videos/' . $filename;

                            Log::info("Uploading video to S3:", [
                                'path' => $path,
                                'filename' => $filename,
                                'file_size' => $file->getSize(),
                                'mime_type' => $file->getMimeType()
                            ]);

                            $uploaded = Storage::disk('s3')->put($path, file_get_contents($file));

                            if ($uploaded) {
                                // Generate S3 URL manually
                                $bucket = config('filesystems.disks.s3.bucket');
                                $region = config('filesystems.disks.s3.region');
                                $productVideoUrl = "https://{$bucket}.s3.{$region}.amazonaws.com/{$path}";
                                Log::info('Successfully uploaded video to S3:', ['url' => $productVideoUrl]);
                                break; // Only take the first video
                            } else {
                                Log::error('S3 video upload returned false');
                            }
                        } catch (\Exception $e) {
                            Log::error('Error uploading video file:', ['error' => $e->getMessage()]);
                        }
                    }
                }
            }

            // Handle bulk image file
            $bulkImageUrl = null;
            if ($request->hasFile('bulk_image_file')) {
                Log::info('Processing bulk image file upload');
                $bulkImageFile = $request->file('bulk_image_file');

                if ($bulkImageFile && $bulkImageFile->isValid()) {
                    try {
                        $filename = Str::uuid() . '.' . $bulkImageFile->getClientOriginalExtension();
                        $path = 'variant-images/' . $filename;

                        $uploaded = Storage::disk('s3')->put($path, file_get_contents($bulkImageFile));

                        if ($uploaded) {
                            $bucket = config('filesystems.disks.s3.bucket');
                            $region = config('filesystems.disks.s3.region');
                            $bulkImageUrl = "https://{$bucket}.s3.{$region}.amazonaws.com/{$path}";
                            Log::info('Successfully uploaded bulk image to S3:', ['url' => $bulkImageUrl]);
                        }
                    } catch (\Exception $e) {
                        Log::error('Error uploading bulk image file:', ['error' => $e->getMessage()]);
                    }
                }
            }

            // Handle variant image file uploads
            $variantImageUrls = [];
            if ($request->has('variants') && is_array($request->variants)) {
                Log::info('Processing variant image file uploads');

                foreach ($request->variants as $index => $variantData) {
                    if ($request->hasFile("variants.{$index}.image_file")) {
                        Log::info("Processing variant {$index} image file upload");
                        $variantImageFile = $request->file("variants.{$index}.image_file");

                        if ($variantImageFile && $variantImageFile->isValid()) {
                            try {
                                $filename = Str::uuid() . '.' . $variantImageFile->getClientOriginalExtension();
                                $path = 'variant-images/' . $filename;

                                $uploaded = Storage::disk('s3')->put($path, file_get_contents($variantImageFile));

                                if ($uploaded) {
                                    $bucket = config('filesystems.disks.s3.bucket');
                                    $region = config('filesystems.disks.s3.region');
                                    $variantImageUrl = "https://{$bucket}.s3.{$region}.amazonaws.com/{$path}";
                                    Log::info('Successfully uploaded variant image to S3:', ['url' => $variantImageUrl]);

                                    // Store the image URL for later use instead of modifying request directly
                                    $variantImageUrls[$index] = $variantImageUrl;
                                }
                            } catch (\Exception $e) {
                                Log::error('Error uploading variant image file:', ['error' => $e->getMessage()]);
                            }
                        }
                    }
                }
            }

            Log::info('Updating ProductTemplate with data:', [
                'id' => $productTemplate->id,
                'name' => $request->name,
                'category' => $request->category,
                'base_price' => $request->base_price,
                'images_count' => count($images),
                'size_chart_url' => $sizeChartUrl,
                'product_video_url' => $productVideoUrl
            ]);

            // Update the product template
            $productTemplate->update([
                'name' => $request->name,
                'description' => $request->description,
                'category_id' => $request->category,
                'base_price' => $request->base_price,
                'list_price' => $request->list_price,
                'weight' => $request->weight,
                'height' => $request->height,
                'width' => $request->width,
                'length' => $request->length,
                'images' => $images,
                'size_chart' => $sizeChartUrl,
                'product_video' => $productVideoUrl,
            ]);

            // Update category attributes if provided
            if ($request->has('attributes') && is_array($request->attributes)) {
                $attributes = array_filter($request->attributes, function ($value) {
                    return !empty($value);
                });

                if (!empty($attributes)) {
                    \App\Models\ProdTemplateCategoryAttribute::saveTemplateAttributes(
                        $productTemplate->id,
                        $request->category,
                        $attributes
                    );
                }
            }

            // Update variants if provided
            if ($request->has('variants') && is_array($request->variants)) {
                Log::info('Updating variants:', ['count' => count($request->variants)]);
                Log::info('Variant image URLs:', $variantImageUrls);

                foreach ($request->variants as $index => $variantData) {
                    Log::info("Processing variant {$index}:", $variantData);

                    // Get variant by index from the product template
                    $variant = $productTemplate->variants()->skip($index)->first();
                    Log::info("Found variant for index {$index}:", ['variant_id' => $variant ? $variant->id : 'null']);

                    if ($variant) {
                        $updateData = [];

                        // Update price if provided
                        if (isset($variantData['price'])) {
                            $updateData['price'] = $variantData['price'];
                        }

                        // Update list_price if provided
                        if (isset($variantData['list_price'])) {
                            $updateData['list_price'] = $variantData['list_price'];
                        }

                        // Update stock_quantity if provided
                        if (isset($variantData['quantity'])) {
                            $updateData['stock_quantity'] = $variantData['quantity'];
                        }

                        // Update variant image if provided
                        if (isset($variantData['image'])) {
                            $updateData['variant_data'] = array_merge(
                                $variant->variant_data ?? [],
                                ['image' => $variantData['image']]
                            );
                        }

                        // Update variant image if uploaded via file
                        if (isset($variantImageUrls[$index])) {
                            $updateData['variant_data'] = array_merge(
                                $variant->variant_data ?? [],
                                ['image' => $variantImageUrls[$index]]
                            );
                            Log::info("Applied uploaded image to variant {$variant->id}:", ['url' => $variantImageUrls[$index]]);
                        }

                        if (!empty($updateData)) {
                            $variant->update($updateData);
                            Log::info("Updated variant {$variant->id} with data:", $updateData);
                        }
                    } else {
                        Log::warning("Variant at index {$index} not found");
                    }
                }
            }

            // Apply bulk image to selected variants if bulk image exists
            if ($bulkImageUrl) {
                Log::info('Processing bulk image application:', ['url' => $bulkImageUrl]);

                // Get selected variant indices for bulk image
                $bulkImageVariantIndices = [];
                if ($request->has('bulk_image_variants')) {
                    try {
                        $bulkImageVariantIndices = json_decode($request->bulk_image_variants, true);
                        Log::info('Bulk image variant indices:', $bulkImageVariantIndices);
                    } catch (\Exception $e) {
                        Log::error('Error parsing bulk image variants:', ['error' => $e->getMessage()]);
                    }
                }

                if (!empty($bulkImageVariantIndices)) {
                    // Apply bulk image to selected variants
                    foreach ($bulkImageVariantIndices as $index) {
                        $variant = $productTemplate->variants()->skip($index)->first();
                        if ($variant) {
                            $variantData = $variant->variant_data ?? [];
                            $variantData['image'] = $bulkImageUrl;
                            $variant->update(['variant_data' => $variantData]);
                            Log::info("Applied bulk image to selected variant {$variant->id} at index {$index}:", ['url' => $bulkImageUrl]);
                        } else {
                            Log::warning("Variant at index {$index} not found for bulk image application");
                        }
                    }
                } else {
                    Log::info('No specific variants selected for bulk image, skipping bulk image application');
                }
            }

            DB::commit();
            Log::info('ProductTemplate updated successfully');

            return redirect()->route('product-templates.index')
                ->with('success', 'Template sản phẩm đã được cập nhật thành công.');
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error updating ProductTemplate:', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()
                ->withInput()
                ->withErrors(['error' => 'Có lỗi xảy ra khi cập nhật template: ' . $e->getMessage()]);
        }
    }

    public function destroy(ProductTemplate $productTemplate)
    {
        $user = Auth::user();

        // Kiểm tra quyền truy cập
        if (!$user->hasRole('team-admin') && $productTemplate->user_id !== $user->id) {
            abort(403, 'Bạn không có quyền xóa template này.');
        }

        $productTemplate->delete();
        return redirect()->route('product-templates.index')
            ->with('success', 'Template sản phẩm đã được xóa thành công.');
    }

    public function updateVariants(Request $request, ProductTemplate $productTemplate)
    {
        $request->validate([
            'variants' => 'required|array',
            'variants.*.id' => 'required|exists:prod_template_variants,id',
            'variants.*.price' => 'nullable|numeric|min:0',
            'variants.*.list_price' => 'nullable|numeric|min:0',
            'variants.*.stock_quantity' => 'nullable|integer|min:0',
            'variants.*.images' => 'nullable|array',
        ]);

        foreach ($request->variants as $variantData) {
            $variant = $productTemplate->variants()->find($variantData['id']);
            if ($variant) {
                $variant->update($variantData);
            }
        }

        return response()->json(['success' => true]);
    }

    public function setBulkPrice(Request $request, ProductTemplate $productTemplate)
    {
        $request->validate([
            'variant_ids' => 'required|array',
            'variant_ids.*' => 'exists:prod_template_variants,id',
            'price' => 'required|numeric|min:0',
            'list_price' => 'nullable|numeric|min:0',
        ]);

        $productTemplate->variants()
            ->whereIn('id', $request->variant_ids)
            ->update([
                'price' => $request->price,
                'list_price' => $request->list_price ?? $request->price,
            ]);

        return response()->json(['success' => true]);
    }

    public function deleteVariant(Request $request, ProductTemplate $productTemplate)
    {
        $request->validate([
            'variant_id' => 'required|exists:prod_template_variants,id'
        ]);

        $variant = $productTemplate->variants()->find($request->variant_id);
        if ($variant) {
            // Xóa pivot records trước
            DB::table('prod_variant_options')->where('prod_template_variant_id', $variant->id)->delete();
            // Xóa variant
            $variant->delete();
            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false, 'message' => 'Variant not found']);
    }

    private function generateVariants(ProductTemplate $template)
    {
        $options = $template->options()->with('values')->get();
        if ($options->isEmpty()) {
            return;
        }

        $optionValues = [];
        foreach ($options as $option) {
            $optionValues[] = $option->values->pluck('id')->toArray();
        }

        $combinations = $this->generateCombinations($optionValues);

        foreach ($combinations as $combination) {
            $variant = $template->variants()->create([
                'sku' => $this->generateSku($template, $combination),
                'price' => $template->base_price,
                'list_price' => $template->list_price,
                'stock_quantity' => 0,
                'variant_data' => [],
            ]);

            // Manually create pivot records with all required columns
            $pivotData = [];
            foreach ($combination as $optionValueId) {
                $optionValue = ProductTemplateOptionValue::find($optionValueId);
                if ($optionValue) {
                    $pivotData[] = [
                        'prod_template_variant_id' => $variant->id,
                        'prod_template_option_id' => $optionValue->prod_template_option_id,
                        'prod_option_value_id' => $optionValueId,
                    ];
                }
            }

            if (!empty($pivotData)) {
                \DB::table('prod_variant_options')->insert($pivotData);
            }
        }
    }

    private function generateCombinations(array $arrays): array
    {
        if (empty($arrays)) {
            return [];
        }

        $result = [[]];
        foreach ($arrays as $array) {
            $temp = [];
            foreach ($result as $combination) {
                foreach ($array as $item) {
                    $temp[] = array_merge($combination, [$item]);
                }
            }
            $result = $temp;
        }

        return $result;
    }

    private function generateSku(ProductTemplate $template, array $optionValueIds): string
    {
        $sku = strtoupper(substr(preg_replace('/[^a-zA-Z0-9]/', '', $template->name), 0, 3));

        $optionValues = ProductTemplateOptionValue::whereIn('id', $optionValueIds)->get();
        foreach ($optionValues as $optionValue) {
            $sku .= '-' . strtoupper(substr(preg_replace('/[^a-zA-Z0-9]/', '', $optionValue->value), 0, 2));
        }

        return $sku;
    }

    private function generateSkuFromVariantData(ProductTemplate $template, array $variantData): string
    {
        $sku = strtoupper(substr(preg_replace('/[^a-zA-Z0-9]/', '', $template->name), 0, 3));

        // Add variant index or combination info to make SKU unique
        if (isset($variantData['combination_string']) && !empty($variantData['combination_string'])) {
            $combinationParts = explode(' / ', $variantData['combination_string']);
            foreach ($combinationParts as $part) {
                $value = trim(explode(':', $part)[1] ?? $part);
                $sku .= '-' . strtoupper(substr(preg_replace('/[^a-zA-Z0-9]/', '', $value), 0, 2));
            }
        } else {
            $sku .= '-VAR' . time(); // Fallback unique identifier
        }

        return $sku;
    }

    private function getCategories(): array
    {
        try {
            $user = Auth::user();
            $teamId = $user->team->id;

            Log::info('Getting categories for team', ['team_id' => $teamId]);

            // Try to get TikTok Shop integration for the team
            $integration = TikTokShopIntegration::where('team_id', $teamId)->first();

            if ($integration) {
                Log::info('Found TikTok Shop integration', [
                    'team_id' => $teamId,
                    'has_access_token' => !empty($integration->access_token),
                    'token_expires_at' => $integration->access_token_expires_at
                ]);

                if ($integration->access_token) {
                    Log::info('Attempting to get categories from TikTok Shop API');

                    // Use TikTok Shop API to get categories with hierarchy
                    $categories = $this->tikTokShopService->getCachedCategoriesWithHierarchy();

                    Log::info('Categories retrieved', [
                        'source' => 'TikTok Shop API with hierarchy',
                        'count' => count($categories),
                        'sample_categories' => array_slice($categories, 0, 5, true)
                    ]);

                    return $categories;
                } else {
                    Log::warning('TikTok Shop integration found but no access token available');
                }
            } else {
                Log::info('No TikTok Shop integration found for team', ['team_id' => $teamId]);
            }

            // If no integration or no access token, return default categories
            Log::info('Using default categories');
            $defaultCategories = $this->tikTokShopService->getCachedCategoriesWithHierarchy();

            Log::info('Default categories loaded', [
                'source' => 'Default with hierarchy',
                'count' => count($defaultCategories),
                'sample_categories' => array_slice($defaultCategories, 0, 5, true)
            ]);

            return $defaultCategories;
        } catch (\Exception $e) {
            // Log error and return default categories
            Log::error('Error getting categories: ' . $e->getMessage(), [
                'team_id' => $user->team->id ?? 'unknown',
                'exception' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            $defaultCategories = $this->tikTokShopService->getCachedCategoriesWithHierarchy();
            Log::info('Using default categories due to error');
            return $defaultCategories;
        }
    }

    /**
     * Duplicate a product template with all related data
     */
    public function duplicate(ProductTemplate $productTemplate)
    {
        try {
            Log::info('Starting template duplication', [
                'original_id' => $productTemplate->id,
                'original_name' => $productTemplate->name,
                'user_id' => Auth::user()->id
            ]);

            DB::beginTransaction();

            // 1. Tạo bản sao của template chính
            $newTemplate = $productTemplate->replicate();
            $newTemplate->name = $productTemplate->name . ' (Copy)';
            $newTemplate->user_id = Auth::user()->id;
            $newTemplate->team_id = Auth::user()->team->id;
            $newTemplate->created_at = now();
            $newTemplate->updated_at = now();
            $newTemplate->save();

            Log::info('New template created', ['new_id' => $newTemplate->id]);

            // 2. Sao chép tất cả options và values với mapping
            $optionMapping = []; // Map old option ID -> new option ID
            $valueMapping = [];  // Map old value ID -> new value ID
            foreach ($productTemplate->options as $option) {
                $newOption = $option->replicate();
                $newOption->product_template_id = $newTemplate->id;
                $newOption->save();

                $optionMapping[$option->id] = $newOption->id;
                Log::info('Copied option', [
                    'old_id' => $option->id,
                    'new_id' => $newOption->id,
                    'name' => $option->name
                ]);

                // Sao chép tất cả option values
                foreach ($option->values as $value) {
                    $newValue = $value->replicate();
                    $newValue->prod_template_option_id = $newOption->id;
                    $newValue->save();

                    $valueMapping[$value->id] = $newValue->id;
                    Log::info('Copied option value', [
                        'old_id' => $value->id,
                        'new_id' => $newValue->id,
                        'value' => $value->value
                    ]);
                }
            }

            // 3. Sao chép tất cả variants và relationships
            foreach ($productTemplate->variants as $variant) {
                $newVariant = $variant->replicate();
                $newVariant->product_template_id = $newTemplate->id;
                $newVariant->save();

                Log::info('Copied variant', [
                    'old_id' => $variant->id,
                    'new_id' => $newVariant->id,
                    'sku' => $variant->sku,
                    'price' => $variant->price
                ]);

                // Sao chép relationships variant -> option values sử dụng mapping
                foreach ($variant->optionValues as $optionValue) {
                    if (isset($valueMapping[$optionValue->id])) {
                        // Tìm option ID tương ứng với option value
                        $newOptionValue = ProductTemplateOptionValue::find($valueMapping[$optionValue->id]);
                        if ($newOptionValue) {
                            DB::table('prod_variant_options')->insert([
                                'prod_template_variant_id' => $newVariant->id,
                                'prod_template_option_id' => $newOptionValue->prod_template_option_id,
                                'prod_option_value_id' => $valueMapping[$optionValue->id],
                            ]);
                            Log::info('Attached option value to variant', [
                                'variant_id' => $newVariant->id,
                                'option_value_id' => $valueMapping[$optionValue->id],
                                'option_id' => $newOptionValue->prod_template_option_id
                            ]);
                        }
                    }
                }
            }

            // 4. Sao chép category attributes
            foreach ($productTemplate->categoryAttributes as $attribute) {
                $newAttribute = $attribute->replicate();
                $newAttribute->product_template_id = $newTemplate->id;
                $newAttribute->save();

                Log::info('Copied category attribute', [
                    'old_id' => $attribute->id,
                    'new_id' => $newAttribute->id,
                    'attribute_name' => $attribute->attribute_name
                ]);
            }

            DB::commit();

            // 5. Verify the duplication
            $newTemplate->load(['options.values', 'variants.optionValues', 'categoryAttributes']);

            Log::info('Template duplicated successfully', [
                'original_id' => $productTemplate->id,
                'new_id' => $newTemplate->id,
                'user_id' => Auth::user()->id
            ]);

            return redirect()->route('product-templates.index')
                ->with('success', 'Template đã được copy thành công! ID mới: #' . $newTemplate->id .
                    ' (Options: ' . $newTemplate->options->count() .
                    ', Variants: ' . $newTemplate->variants->count() . ')');
        } catch (\Exception $e) {
            DB::rollback();

            Log::error('Error duplicating template:', [
                'template_id' => $productTemplate->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => Auth::user()->id
            ]);

            return redirect()->route('product-templates.index')
                ->with('error', 'Có lỗi xảy ra khi copy template: ' . $e->getMessage());
        }
    }

    /**
     * Get existing category attributes for a template
     */
    public function getExistingAttributes(ProductTemplate $productTemplate)
    {
        try {
            $attributes = \App\Models\ProdTemplateCategoryAttribute::where('product_template_id', $productTemplate->id)
                ->get()
                ->keyBy('attribute_id')
                ->map(function ($attr) {
                    return [
                        'attribute_id' => $attr->attribute_id,
                        'attribute_name' => $attr->attribute_name,
                        'value' => $attr->value,
                        'value_id' => $attr->value_id,
                        'value_name' => $attr->value_name,
                        'is_required' => $attr->is_required,
                    ];
                });

            return response()->json([
                'success' => true,
                'attributes' => $attributes
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting existing attributes: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error getting existing attributes'
            ], 500);
        }
    }
}
