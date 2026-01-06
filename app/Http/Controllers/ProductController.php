<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductTemplate;
use App\Models\Team;
use App\Models\User;
use App\Models\TikTokProductUploadHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use App\Services\TikTokImageUploadService;
    use App\Services\TikTokShopProductService;

class ProductController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Hiển thị danh sách sản phẩm theo team
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $team = $user->currentTeam;

        if (!$team) {
            return redirect()->route('teams.index')->with('error', 'You need to select a team to manage products.');
        }

        // Kiểm tra quyền xem sản phẩm
        if (!$user->hasPermissionTo('view-products')) {
            abort(403, 'You do not have permission to view products in this team.');
        }

        $query = Product::with(['productTemplate', 'user', 'images'])
            ->byTeam($team->id);

        // Only team-admin and system-admin can view all products in the team
        // Other roles (seller) can only view products of themselves
        if (!$user->hasRole('team-admin') && !$user->hasRole('system-admin')) {
            $query->byUser($user->id);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by template
        if ($request->filled('template_id')) {
            $query->byTemplate($request->template_id);
        }

        // Filter by SKU
        if ($request->filled('sku')) {
            $query->where('sku', 'like', "%{$request->sku}%");
        }

        // Filter by creator (only admin)
        if (($user->hasRole('team-admin') || $user->hasRole('system-admin')) && $request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Filter by active/inactive status
        if ($request->filled('is_active')) {
            $query->where('is_active', $request->is_active == '1');
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        $products = $query->orderBy('created_at', 'desc')->paginate(15);

        // Only team-admin can get templates of all users in the team
        if ($user->hasRole('team-admin') || $user->hasRole('system-admin')) {
            $templates = ProductTemplate::byTeam($team->id)->active()->get();
        } else {
            // Other roles can only get templates of themselves
            $templates = ProductTemplate::byTeam($team->id)->byUser($user->id)->active()->get();
        }

            // Get list of users in the team (for creator filter - only admin)
        $teamUsers = collect();
        if ($user->hasRole('team-admin') || $user->hasRole('system-admin')) {
            $teamUsers = User::where('team_id', $team->id)
                ->where('is_system_user', false)
                ->select('id', 'name', 'email')
                ->orderBy('name')
                ->get();
        }

        // Get TikTok shops by user's permission
        $tiktokShops = $this->getUserAccessibleTikTokShops($user, $team);

        // Get upload history for products (with integration to get market)
        $productIds = $products->pluck('id');
        $uploadHistories = TikTokProductUploadHistory::with([
                'tiktokShop.integration',
                'product.primaryImage',
                'product.images',
            ])
            ->whereIn('product_id', $productIds)
            ->orderBy('created_at', 'desc')
            ->get()
            ->groupBy('product_id');

        return view('products.index', compact('products', 'templates', 'team', 'tiktokShops', 'uploadHistories', 'teamUsers'));
    }

    /**
     * Display form to create new product
     */
    public function create()
    {
        $user = Auth::user();
        $team = $user->currentTeam;

        if (!$team) {
            return redirect()->route('teams.index')->with('error', 'You need to select a team to create a product.');
        }

        // Check permission to create product
        if (!$user->hasPermissionTo('create-products')) {
            abort(403, 'You do not have permission to create products in this team.');
        }

        // Only team-admin can get templates of all users in the team
        if ($user->hasRole('team-admin')) {
            $templates = ProductTemplate::byTeam($team->id)->active()->get();
        } else {
            // Other roles can only get templates of themselves
            $templates = ProductTemplate::byTeam($team->id)->byUser($user->id)->active()->get();
        }

        return view('products.create', compact('templates', 'team'));
    }

    /**
     * Save new product
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        $team = $user->currentTeam;

        if (!$team) {
            return redirect()->route('teams.index')->with('error', 'You need to select a team to create a product.');
        }

        // Check permission to create product
        if (!$user->hasPermissionTo('create-products')) {
            abort(403, 'You do not have permission to create products in this team.');
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'sku' => 'required|string|max:100',
            'price' => 'required|numeric|min:0',
            'product_template_id' => [
                'required',
                Rule::exists('product_templates', 'id')->where('team_id', $team->id)
            ],
            'product_images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'size_chart_file' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:10240',
            'product_video_file' => 'nullable|file|mimes:mp4,avi,mov,wmv,flv,webm|max:102400',
            'status' => 'required|in:active,inactive',
        ]);

        $data = $request->only([
            'title',
            'description',
            'sku',
            'price',
            'product_template_id',
            'status'
        ]);

        $data['user_id'] = $user->id;
        $data['team_id'] = $team->id;
        $data['is_active'] = true;

        $product = Product::create($data);

        // Process upload images to S3
        if ($request->hasFile('product_images')) {
            foreach ($request->file('product_images') as $index => $image) {
                if ($image->isValid()) {
                    try {
                        // Upload to S3
                        $filename = Str::uuid() . '.' . $image->getClientOriginalExtension();
                        $path = 'product-images/' . $filename;

                        $uploaded = Storage::disk('s3')->put($path, file_get_contents($image));

                        if ($uploaded) {
                            // Generate S3 URL manually
                            $bucket = config('filesystems.disks.s3.bucket');
                            $region = config('filesystems.disks.s3.region');
                            $url = "https://{$bucket}.s3.{$region}.amazonaws.com/{$path}";

                            $product->images()->create([
                                'file_name' => $image->getClientOriginalName(),
                                    'file_path' => $url, // Save S3 URL instead of local path
                                'type' => 'image',
                                'source' => 'product',
                                'sort_order' => $index,
                                'is_primary' => $index === 0, // The first image is the main image
                            ]);
                        }
                    } catch (\Exception $e) {
                        Log::error('Error uploading product image to S3:', [
                            'error' => $e->getMessage(),
                            'file' => $image->getClientOriginalName()
                        ]);
                    }
                }
            }
        }

        // Upload size chart (optional)
        if ($request->hasFile('size_chart_file')) {
            $sizeChart = $request->file('size_chart_file');
            if ($sizeChart->isValid()) {
                try {
                    $filename = Str::uuid() . '.' . $sizeChart->getClientOriginalExtension();
                    $path = 'size-charts/' . $filename;
                    $uploaded = Storage::disk('s3')->put($path, file_get_contents($sizeChart));
                    if ($uploaded) {
                        $bucket = config('filesystems.disks.s3.bucket');
                        $region = config('filesystems.disks.s3.region');
                        $url = "https://{$bucket}.s3.{$region}.amazonaws.com/{$path}";
                        $product->size_chart = $url;
                        $product->save();
                    }
                } catch (\Exception $e) {
                    Log::error('Error uploading size chart:', [
                        'error' => $e->getMessage(),
                        'file' => $sizeChart->getClientOriginalName()
                    ]);
                }
            }
        }

        // Upload product video (optional)
        if ($request->hasFile('product_video_file')) {
            $video = $request->file('product_video_file');
            if ($video->isValid()) {
                try {
                    $filename = Str::uuid() . '.' . $video->getClientOriginalExtension();
                    $path = 'product-videos/' . $filename;
                    $uploaded = Storage::disk('s3')->put($path, file_get_contents($video));
                    if ($uploaded) {
                        $bucket = config('filesystems.disks.s3.bucket');
                        $region = config('filesystems.disks.s3.region');
                        $url = "https://{$bucket}.s3.{$region}.amazonaws.com/{$path}";
                        $product->product_video = $url;
                        $product->save();
                    }
                } catch (\Exception $e) {
                    Log::error('Error uploading product video:', [
                        'error' => $e->getMessage(),
                        'file' => $video->getClientOriginalName()
                    ]);
                }
            }
        }

        return redirect()->route('products.index')
            ->with('success', 'Product created successfully.');
    }

    /**
     * Display product details
     */
    public function show(Product $product)
    {
        $user = Auth::user();
        $team = $user->currentTeam;

        if (!$team || $product->team_id !== $team->id) {
            abort(404);
        }

        // Check permission to view product
        if (!$user->hasPermissionTo('view-products')) {
            abort(403, 'You do not have permission to view products in this team.');
        }

        $product->load(['productTemplate', 'user']);

        return view('products.show', compact('product', 'team'));
    }

    /**
     * Display form to edit product
     */
    public function edit(Product $product)
    {
        $user = Auth::user();
        $team = $user->currentTeam;

        if (!$team || $product->team_id !== $team->id) {
            abort(404);
        }

        // Kiểm tra quyền chỉnh sửa sản phẩm
        if (!$user->hasPermissionTo('update-products')) {
            abort(403, 'You do not have permission to edit products in this team.');
        }

        // Only team-admin can get templates of all users in the team
        if ($user->hasRole('team-admin')) {
            $templates = ProductTemplate::byTeam($team->id)->active()->get();
        } else {
            // Other roles can only get templates of themselves
            $templates = ProductTemplate::byTeam($team->id)->byUser($user->id)->active()->get();
        }

        return view('products.edit', compact('product', 'templates', 'team'));
    }

    /**
     * Update product
     */
    public function update(Request $request, Product $product)
    {
        $user = Auth::user();
        $team = $user->currentTeam;

        if (!$team || $product->team_id !== $team->id) {
            abort(404);
        }

        // Check permission to edit product
        if (!$user->hasPermissionTo('update-products')) {
            abort(403, 'You do not have permission to edit products in this team.');
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'sku' => 'required|string|max:100',
            'price' => 'required|numeric|min:0',
            'product_template_id' => [
                'required',
                Rule::exists('product_templates', 'id')->where('team_id', $team->id)
            ],
            'product_images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'status' => 'required|in:active,inactive',
        ]);

        $data = $request->only([
            'title',
            'description',
            'sku',
            'price',
            'product_template_id',
            'status'
        ]);

        $product->update($data);

        // Process upload new images to S3
        if ($request->hasFile('product_images')) {
            // Delete old images if any
            foreach ($product->images as $image) {
                // Delete file from S3 if needed
                if ($image->file_path && str_contains($image->file_path, 'amazonaws.com')) {
                    try {
                        $path = str_replace('https://' . config('filesystems.disks.s3.bucket') . '.s3.' . config('filesystems.disks.s3.region') . '.amazonaws.com/', '', $image->file_path);
                        Storage::disk('s3')->delete($path);
                    } catch (\Exception $e) {
                        Log::error('Error deleting old image from S3:', ['error' => $e->getMessage()]);
                    }
                }
            }
            $product->images()->delete();

            foreach ($request->file('product_images') as $index => $image) {
                if ($image->isValid()) {
                    try {
                        // Upload to S3
                        $filename = Str::uuid() . '.' . $image->getClientOriginalExtension();
                        $path = 'product-images/' . $filename;

                        $uploaded = Storage::disk('s3')->put($path, file_get_contents($image));

                        if ($uploaded) {
                            // Generate S3 URL manually
                            $bucket = config('filesystems.disks.s3.bucket');
                            $region = config('filesystems.disks.s3.region');
                            $url = "https://{$bucket}.s3.{$region}.amazonaws.com/{$path}";

                            $product->images()->create([
                                'file_name' => $image->getClientOriginalName(),
                                'file_path' => $url, // Save S3 URL instead of local path
                                'type' => 'image',
                                'source' => 'product',
                                'sort_order' => $index,
                                'is_primary' => $index === 0, // The first image is the main image
                            ]);
                        }
                    } catch (\Exception $e) {
                        Log::error('Error uploading product image to S3:', [
                            'error' => $e->getMessage(),
                            'file' => $image->getClientOriginalName()
                        ]);
                    }
                }
            }
        }

        return redirect()->route('products.index')
            ->with('success', 'Product updated successfully.');
    }

    /**
        * Delete product
     */
    public function destroy(Product $product)
    {
        $user = Auth::user();
        $team = $user->currentTeam;

        if (!$team || $product->team_id !== $team->id) {
            abort(404);
        }

        // Check permission to delete product
        if (!$user->hasPermissionTo('delete-products')) {
            abort(403, 'You do not have permission to delete products in this team.');
        }

        // Delete product images from S3
        foreach ($product->images as $image) {
            if ($image->file_path && str_contains($image->file_path, 'amazonaws.com')) {
                try {
                    $path = str_replace('https://' . config('filesystems.disks.s3.bucket') . '.s3.' . config('filesystems.disks.s3.region') . '.amazonaws.com/', '', $image->file_path);
                    Storage::disk('s3')->delete($path);
                } catch (\Exception $e) {
                    Log::error('Error deleting image from S3:', ['error' => $e->getMessage()]);
                }
            }
        }
        $product->images()->delete();

        $product->delete();

        return redirect()->route('products.index')
            ->with('success', 'Product deleted successfully.');
    }

    /**
     * Toggle active/inactive status
     */
    public function toggleStatus(Product $product)
    {
        $user = Auth::user();
        $team = $user->currentTeam;

        if (!$team || $product->team_id !== $team->id) {
            abort(404);
        }

        // Check permission to edit product
        if (!$user->hasPermissionTo('update-products')) {
            abort(403, 'You do not have permission to edit products in this team.');
        }

        $product->update(['is_active' => !$product->is_active]);

        $status = $product->is_active ? 'activated' : 'deactivated';

        return redirect()->route('products.index')
            ->with('success', "Product has been {$status} successfully.");
    }

    /**
     * API to get products by template
     */
    public function getByTemplate(Request $request)
    {
        $user = Auth::user();
        $team = $user->currentTeam;

        if (!$team) {
            return response()->json(['error' => 'Team does not exist'], 404);
        }

        $templateId = $request->get('template_id');

        $products = Product::with(['productTemplate'])
            ->byTeam($team->id)
            ->byTemplate($templateId)
            ->active()
            ->get();

        return response()->json($products);
    }

    /**
        * Upload product images to TikTok Shop
     */
    public function uploadImagesToTikTok(Product $product)
    {
        $user = Auth::user();
        $team = $user->currentTeam;

        if (!$team || $product->team_id !== $team->id) {
            abort(404);
        }

        // Check permission to edit product
        if (!$user->hasPermissionTo('update-products')) {
            abort(403, 'You do not have permission to edit products in this team.');
        }

        try {
            // Get TikTok Shop integration of the team
            $integration = $team->tiktokShopIntegration;
            if (!$integration) {
                return response()->json([
                    'success' => false,
                    'message' => 'Team has not connected to TikTok Shop'
                ], 400);
            }

            if (!$integration->access_token) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid access token TikTok Shop'
                ], 400);
            }

            // Initialize upload service
            $uploadService = new TikTokImageUploadService($integration);

            // Upload product images
            $result = $uploadService->uploadProductImages($product);

            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'message' => "Successfully uploaded {$result['uploaded_count']} images to TikTok Shop",
                    'data' => $result
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => $result['message']
                ], 500);
            }
        } catch (\Exception $e) {
            Log::error('Error uploading images to TikTok', [
                'product_id' => $product->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error uploading images: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Upload bulk products to TikTok Shop
     */
    public function bulkUploadToTikTok(Request $request)
    {
        $user = Auth::user();
        $team = $user->currentTeam;

        if (!$team) {
            return response()->json([
                'success' => false,
                'message' => 'Team does not exist'
            ], 404);
        }

            // Check permission to edit products
        if (!$user->hasPermissionTo('update-products')) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to upload products'
            ], 403);
        }

        $request->validate([
            'product_ids' => 'required|array|min:1',
            'product_ids.*' => 'exists:products,id',
            'tiktok_shop_ids' => 'required|array|min:1',
            'tiktok_shop_ids.*' => 'exists:tiktok_shops,id'
        ]);

        $productIds = $request->input('product_ids');
        $tiktokShopIds = $request->input('tiktok_shop_ids');

        // Get TikTok shops and check access permission
        $tiktokShops = \App\Models\TikTokShop::whereIn('id', $tiktokShopIds)
            ->where('team_id', $team->id)
            ->get();

        if ($tiktokShops->count() !== count($tiktokShopIds)) {
            return response()->json([
                'success' => false,
                'message' => 'Some TikTok Shops do not exist or not belong to this team'
            ], 404);
        }

        // Check access permission for each shop
        foreach ($tiktokShops as $shop) {
            if (!$shop->canUserAccess($user)) {
                return response()->json([
                    'success' => false,
                    'message' => "You do not have permission to access shop: {$shop->shop_name}"
                ], 403);
            }
        }

        // Get products and check permission
        $products = Product::whereIn('id', $productIds)
            ->where('team_id', $team->id)
            ->get();

        if ($products->count() !== count($productIds)) {
            return response()->json([
                'success' => false,
                'message' => 'Some products do not exist or not belong to this team'
            ], 400);
        }

        // Get TikTok Shop integration
        $integration = $team->tiktokShopIntegration;
        if (!$integration || !$integration->isActive()) {
            return response()->json([
                'success' => false,
                'message' => 'Team has not connected to TikTok Shop or connection is not active'
            ], 400);
        }

        $totalSuccessCount = 0;
        $totalFailureCount = 0;
        $errors = [];
        $shopResults = [];

        try {
            // Initialize upload service
            $uploadService = new TikTokImageUploadService($integration);

            foreach ($tiktokShops as $shop) {
                $shopSuccessCount = 0;
                $shopFailureCount = 0;
                $shopErrors = [];

                foreach ($products as $product) {
                    try {
                        // Upload product images for each product
                        $result = $uploadService->uploadProductImages($product);

                        if ($result['success']) {
                            $shopSuccessCount++;
                            $totalSuccessCount++;
                            Log::info('Bulk upload success', [
                                'product_id' => $product->id,
                                'product_title' => $product->title,
                                'tiktok_shop_id' => $shop->id,
                                'shop_name' => $shop->shop_name,
                                'uploaded_count' => $result['uploaded_count']
                            ]);
                        } else {
                            $shopFailureCount++;
                            $totalFailureCount++;
                                $shopErrors[] = "Product '{$product->title}': {$result['message']}";
                            Log::warning('Bulk upload failed', [
                                'product_id' => $product->id,
                                'product_title' => $product->title,
                                'tiktok_shop_id' => $shop->id,
                                'shop_name' => $shop->shop_name,
                                'error' => $result['message']
                            ]);
                        }
                    } catch (\Exception $e) {
                        $shopFailureCount++;
                        $totalFailureCount++;
                        $shopErrors[] = "Product '{$product->title}': {$e->getMessage()}";
                        Log::error('Bulk upload error', [
                            'product_id' => $product->id,
                            'product_title' => $product->title,
                            'tiktok_shop_id' => $shop->id,
                            'shop_name' => $shop->shop_name,
                            'error' => $e->getMessage()
                        ]);
                    }
                }

                $shopResults[] = [
                    'shop_name' => $shop->shop_name,
                    'success_count' => $shopSuccessCount,
                    'failure_count' => $shopFailureCount,
                    'errors' => $shopErrors
                ];

                $errors = array_merge($errors, $shopErrors);
            }

            $message = "Upload completed: {$totalSuccessCount} successful, {$totalFailureCount} failed";
            if (!empty($errors)) {
                $message .= "\n\nDetailed errors:\n" . implode("\n", array_slice($errors, 0, 5));
                if (count($errors) > 5) {
                    $message .= "\n... and " . (count($errors) - 5) . " other errors";
                }
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'success_count' => $totalSuccessCount,
                'failure_count' => $totalFailureCount,
                'shop_results' => $shopResults,
                'errors' => $errors
            ]);
        } catch (\Exception $e) {
            Log::error('Bulk upload general error', [
                'user_id' => $user->id,
                'team_id' => $team->id,
                'tiktok_shop_ids' => $tiktokShopIds,
                'product_count' => count($productIds),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error uploading bulk products: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get list of TikTok shops that user has access permission
     */
    private function getUserAccessibleTikTokShops($user, $team)
    {
        $shops = $team->activeTikTokShops;

        // System admin can access all shops
        if ($user->hasRole('system-admin')) {
            return $shops;
        }

        // Team admin can access all shops in the team
        if ($user->hasRole('team-admin')) {
            return $shops;
        }

        // Seller can only access shops that they are assigned to
        $accessibleShops = collect();
        foreach ($shops as $shop) {
            if ($shop->canUserAccess($user)) {
                $accessibleShops->push($shop);
            }
        }

        return $accessibleShops;
    }

    /**
     * Upload product to TikTok Shop (without images)
     */
    public function uploadProductToTikTok(Request $request)
    {
        $user = Auth::user();
        $team = $user->currentTeam;

        if (!$team) {
            return response()->json([
                'success' => false,
                'message' => 'Team does not exist'
            ], 404);
        }

        // Check permission to edit product
        if (!$user->hasPermissionTo('update-products')) {
            return response()->json([
                'success' => false,
                    'message' => 'You do not have permission to upload products'
            ], 403);
        }

        $request->validate([
            'product_ids' => 'required|array|min:1',
            'product_ids.*' => 'exists:products,id',
            'tiktok_shop_ids' => 'required|array|min:1',
            'tiktok_shop_ids.*' => 'exists:tiktok_shops,id'
        ]);

        $productIds = $request->input('product_ids');
        $tiktokShopIds = $request->input('tiktok_shop_ids');

        // Get TikTok shops and check access permission
        $tiktokShops = \App\Models\TikTokShop::whereIn('id', $tiktokShopIds)
            ->where('team_id', $team->id)
            ->get();

        if ($tiktokShops->count() !== count($tiktokShopIds)) {
            return response()->json([
                'success' => false,
                'message' => 'Some TikTok Shops do not exist or not belong to this team'
            ], 404);
        }

        // Check access permission for each shop
        foreach ($tiktokShops as $shop) {
            if (!$shop->canUserAccess($user)) {
                return response()->json([
                    'success' => false,
                    'message' => "You do not have permission to access shop: {$shop->shop_name}"
                ], 403);
            }
        }

        // Get products and check permission
        $products = Product::whereIn('id', $productIds)
            ->where('team_id', $team->id)
            ->get();

        if ($products->count() !== count($productIds)) {
            return response()->json([
                'success' => false,
                'message' => 'Some products do not exist or not belong to this team'
            ], 400);
        }

        try {
            // Initialize upload service
            $productService = new TikTokShopProductService();

            // Call bulk upload method
            $results = $productService->bulkUploadProducts($productIds, $tiktokShopIds, $user->id);

            $message = "Upload completed: {$results['success_count']} successful, {$results['failure_count']} failed";

            return response()->json([
                'success' => true,
                'message' => $message,
                'success_count' => $results['success_count'],
                'failure_count' => $results['failure_count'],
                'details' => $results['details']
            ]);
        } catch (\Exception $e) {
            Log::error('Product upload to TikTok error', [
                'user_id' => $user->id,
                'team_id' => $team->id,
                'tiktok_shop_ids' => $tiktokShopIds,
                'product_count' => count($productIds),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error uploading products: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Retry upload product from history
     */
    public function retryUpload(Request $request, $historyId)
    {
        $user = Auth::user();
        $team = $user->currentTeam;

        if (!$team) {
            return response()->json([
                'success' => false,
                    'message' => 'You need to select a team to perform this action.'
            ], 400);
        }

        // Check permission to upload products
        if (!$user->hasPermissionTo('upload-products')) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to upload products.'
            ], 403);
        }

        try {
            $uploadHistory = TikTokProductUploadHistory::with(['product', 'tiktokShop'])
                ->findOrFail($historyId);

            // Check access permission for product
            if ($uploadHistory->product->team_id !== $team->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to access this product.'
                ], 403);
            }

            // Retry upload
            $service = new TikTokShopProductService();
            $result = $service->uploadProduct($uploadHistory->product, $uploadHistory->tiktokShop, $user->id);

            return response()->json([
                'success' => $result['success'],
                'message' => $result['message'],
                'upload_history_id' => $result['upload_history_id'] ?? null
            ]);
        } catch (\Exception $e) {
            Log::error('Retry upload error', [
                'user_id' => $user->id,
                'team_id' => $team->id,
                'history_id' => $historyId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                    'message' => 'Error retrying upload: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export products to CSV
     */
    public function export(Request $request)
    {
        $user = Auth::user();
        $team = $user->currentTeam;

        if (!$team) {
            return redirect()->route('teams.index')->with('error', 'You need to select a team to export products.');
        }

        // Check permission to view products
        if (!$user->hasPermissionTo('view-products')) {
            abort(403, 'You do not have permission to export products in this team.');
        }

        $query = Product::with(['productTemplate', 'user'])
            ->byTeam($team->id);

        // Only team-admin and system-admin can view all products in the team
        if (!$user->hasRole('team-admin') && !$user->hasRole('system-admin')) {
            $query->byUser($user->id);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by template
        if ($request->filled('template_id')) {
            $query->byTemplate($request->template_id);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $products = $query->orderBy('created_at', 'desc')->get();

        // Create CSV file
        $filename = 'products_' . date('Y-m-d_His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        // Add BOM to Excel to display Vietnamese correctly
        $callback = function () use ($products) {
            $file = fopen('php://output', 'w');

                    // Add BOM for UTF-8
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

            // Header row - Match with import template
            fputcsv($file, [
                'Product Name',
                'Description',
                'SKU',
                'Price',
                'Template ID',
                'Status',
                'Active'
            ]);

            // Data rows - Match with import format
            foreach ($products as $product) {
                fputcsv($file, [
                    $product->title,
                    $product->description ?? '',
                    $product->sku,
                    number_format($product->price, 2, '.', ''), // Format number with 2 decimal places
                    $product->product_template_id ?? '',
                    $product->status, // active or inactive
                    $product->is_active ? 'Yes' : 'No', // Yes or No
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Import products from CSV file
     */
    public function import(Request $request)
    {
        $user = Auth::user();
        $team = $user->currentTeam;

        if (!$team) {
            return redirect()->route('products.index')->with('error', 'You need to select a team to import products.');
        }

        // Check permission to create products
        if (!$user->hasPermissionTo('create-products')) {
            abort(403, 'You do not have permission to import products in this team.');
        }

        $request->validate([
            'file' => 'required|file|mimes:csv,txt|max:10240', // Maximum 10MB
        ]);

        $file = $request->file('file');
        $path = $file->getRealPath();

        $successCount = 0;
        $failureCount = 0;
        $errors = [];

                // Read CSV file
        if (($handle = fopen($path, 'r')) !== false) {
            // Skip BOM if exists
            $bom = fread($handle, 3);
            if ($bom !== chr(0xEF) . chr(0xBB) . chr(0xBF)) {
                rewind($handle);
            }

            // Read header row
            $headers = fgetcsv($handle);
            if (!$headers) {
                return redirect()->route('products.index')
                    ->with('error', 'Invalid or empty CSV file.');
            }

            // Map header to avoid depending on column order
            $headerMap = [];
            foreach ($headers as $index => $header) {
                $headerMap[trim($header)] = $index;
            }

            $rowNumber = 1; // Start counting from 1 because header row has been read

            // Read each data row
            while (($row = fgetcsv($handle)) !== false) {
                $rowNumber++;

                // Skip empty row
                if (empty(array_filter($row))) {
                    continue;
                }

                try {
                    // Get data from columns
                    $title = isset($headerMap['Product Name']) ? trim($row[$headerMap['Product Name']]) : '';
                    $sku = isset($headerMap['SKU']) ? trim($row[$headerMap['SKU']]) : '';
                    $priceRaw = isset($headerMap['Price']) ? trim($row[$headerMap['Price']]) : '';
                    $description = isset($headerMap['Description']) ? trim($row[$headerMap['Description']]) : '';
                    $templateId = isset($headerMap['Template ID']) ? trim($row[$headerMap['Template ID']]) : '';
                    $status = isset($headerMap['Status']) ? trim($row[$headerMap['Status']]) : 'active';
                    $isActive = isset($headerMap['Active']) ? trim($row[$headerMap['Active']]) : 'Yes';

                    // Validate required data
                    if (empty($title)) {
                        $errors[] = "Row {$rowNumber}: Missing product name";
                        $failureCount++;
                        continue;
                    }

                    if (empty($sku)) {
                        $errors[] = "Row {$rowNumber}: Missing SKU";
                        $failureCount++;
                        continue;
                    }

                    // Handle price - support both comma and dot
                    $price = str_replace(',', '.', $priceRaw); // Replace comma with dot
                    $price = preg_replace('/[^0-9.]/', '', $price); // Remove non-numeric and dot characters

                    if (!is_numeric($price) || $price < 0) {
                        $errors[] = "Row {$rowNumber}: Invalid price ({$priceRaw})";
                        $failureCount++;
                        continue;
                    }

                    $price = (float) $price; // Convert to float

                    // Check if SKU is duplicated in the team
                    $existingProduct = Product::where('team_id', $team->id)
                        ->where('sku', $sku)
                        ->first();

                    if ($existingProduct) {
                        $errors[] = "Row {$rowNumber}: SKU '{$sku}' already exists";
                        $failureCount++;
                        continue;
                    }

                    // Validate template
                    if (!empty($templateId)) {
                        $template = ProductTemplate::where('id', $templateId)
                            ->where('team_id', $team->id)
                            ->first();

                        if (!$template) {
                                    $errors[] = "Row {$rowNumber}: Template ID '{$templateId}' does not exist in the team";
                            $failureCount++;
                            continue;
                        }

                                // Only team-admin can use template of other users
                        if (!$user->hasRole('team-admin') && !$user->hasRole('system-admin')) {
                            if ($template->user_id !== $user->id) {
                                $errors[] = "Row {$rowNumber}: You do not have permission to use template ID '{$templateId}'";
                                $failureCount++;
                                continue;
                            }
                        }
                    } else {
                        $templateId = null;
                    }

                    // Convert status to lowercase
                    $status = in_array(strtolower($status), ['active', 'inactive']) ? 'active' : 'inactive';
                    $isActive = in_array(strtolower($isActive), ['yes', 'true', '1']) ? true : false;

                    // Create product
                    $product = Product::create([
                        'user_id' => $user->id,
                        'team_id' => $team->id,
                        'product_template_id' => $templateId,
                        'title' => $title,
                        'description' => $description,
                        'sku' => $sku,
                        'price' => $price,
                        'status' => $status,
                        'is_active' => $isActive,
                    ]);

                    $successCount++;
                } catch (\Exception $e) {
                    $errors[] = "Row {$rowNumber}: " . $e->getMessage();
                    $failureCount++;
                    Log::error('Error importing product', [
                        'row' => $rowNumber,
                        'error' => $e->getMessage(),
                        'data' => $row
                    ]);
                }
            }

            fclose($handle);
        }

        $message = "Import completed: {$successCount} successful, {$failureCount} failed";
        if (!empty($errors)) {
            $message .= "\n\nDetailed errors:\n" . implode("\n", array_slice($errors, 0, 10));
            if (count($errors) > 10) {
                $message .= "\n... and " . (count($errors) - 10) . " other errors";
            }
        }

        return redirect()->route('products.index')
            ->with($successCount > 0 ? 'success' : 'error', $message);
    }

    /**
     * Download template CSV to import
     */
    public function downloadTemplate()
    {
        $filename = 'products_template.csv';
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () {
            $file = fopen('php://output', 'w');

            // Add BOM for UTF-8
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

            // Header row
            fputcsv($file, [
                'Product Name',
                'Description',
                'SKU',
                'Price',
                'Template ID',
                'Status',
                'Active'
            ]);

                // Add a sample row
            fputcsv($file, [
                        'Product Template',
                'This is the description of the product template',
                'TEMPLATE001',
                '100.00',
                '',
                'active',
                'Yes'
            ]);

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
