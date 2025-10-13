<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductTemplate;
use App\Models\Team;
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
            return redirect()->route('teams.index')->with('error', 'Bạn cần chọn một team để quản lý sản phẩm.');
        }

        // Kiểm tra quyền xem sản phẩm
        if (!$user->hasPermissionTo('view-products')) {
            abort(403, 'Bạn không có quyền xem sản phẩm trong team này.');
        }

        $query = Product::with(['productTemplate', 'user', 'images'])
            ->byTeam($team->id);

        // Lọc theo trạng thái
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Lọc theo template
        if ($request->filled('template_id')) {
            $query->byTemplate($request->template_id);
        }

        // Tìm kiếm
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $products = $query->orderBy('created_at', 'desc')->paginate(15);

        // Chỉ team-admin mới được lấy template của toàn bộ user trong team
        if ($user->hasRole('team-admin')) {
            $templates = ProductTemplate::byTeam($team->id)->active()->get();
        } else {
            // Các role khác chỉ được lấy template của mình
            $templates = ProductTemplate::byTeam($team->id)->byUser($user->id)->active()->get();
        }

        // Lấy TikTok shops theo quyền user
        $tiktokShops = $this->getUserAccessibleTikTokShops($user, $team);

        // Lấy lịch sử upload cho các sản phẩm
        $productIds = $products->pluck('id');
        $uploadHistories = TikTokProductUploadHistory::with(['tiktokShop'])
            ->whereIn('product_id', $productIds)
            ->orderBy('created_at', 'desc')
            ->get()
            ->groupBy('product_id');

        return view('products.index', compact('products', 'templates', 'team', 'tiktokShops', 'uploadHistories'));
    }

    /**
     * Hiển thị form tạo sản phẩm mới
     */
    public function create()
    {
        $user = Auth::user();
        $team = $user->currentTeam;

        if (!$team) {
            return redirect()->route('teams.index')->with('error', 'Bạn cần chọn một team để tạo sản phẩm.');
        }

        // Kiểm tra quyền tạo sản phẩm
        if (!$user->hasPermissionTo('create-products')) {
            abort(403, 'Bạn không có quyền tạo sản phẩm trong team này.');
        }

        // Chỉ team-admin mới được lấy template của toàn bộ user trong team
        if ($user->hasRole('team-admin')) {
            $templates = ProductTemplate::byTeam($team->id)->active()->get();
        } else {
            // Các role khác chỉ được lấy template của mình
            $templates = ProductTemplate::byTeam($team->id)->byUser($user->id)->active()->get();
        }

        return view('products.create', compact('templates', 'team'));
    }

    /**
     * Lưu sản phẩm mới
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        $team = $user->currentTeam;

        if (!$team) {
            return redirect()->route('teams.index')->with('error', 'Bạn cần chọn một team để tạo sản phẩm.');
        }

        // Kiểm tra quyền tạo sản phẩm
        if (!$user->hasPermissionTo('create-products')) {
            abort(403, 'Bạn không có quyền tạo sản phẩm trong team này.');
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'sku' => 'required|string|max:100|unique:products,sku',
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

        $data['user_id'] = $user->id;
        $data['team_id'] = $team->id;
        $data['is_active'] = true;

        $product = Product::create($data);

        // Xử lý upload ảnh lên S3
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
                                'file_path' => $url, // Lưu S3 URL thay vì local path
                                'type' => 'image',
                                'source' => 'product',
                                'sort_order' => $index,
                                'is_primary' => $index === 0, // Ảnh đầu tiên là ảnh chính
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
            ->with('success', 'Sản phẩm đã được tạo thành công.');
    }

    /**
     * Hiển thị chi tiết sản phẩm
     */
    public function show(Product $product)
    {
        $user = Auth::user();
        $team = $user->currentTeam;

        if (!$team || $product->team_id !== $team->id) {
            abort(404);
        }

        // Kiểm tra quyền xem sản phẩm
        if (!$user->hasPermissionTo('view-products')) {
            abort(403, 'Bạn không có quyền xem sản phẩm trong team này.');
        }

        $product->load(['productTemplate', 'user']);

        return view('products.show', compact('product', 'team'));
    }

    /**
     * Hiển thị form chỉnh sửa sản phẩm
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
            abort(403, 'Bạn không có quyền chỉnh sửa sản phẩm trong team này.');
        }

        // Chỉ team-admin mới được lấy template của toàn bộ user trong team
        if ($user->hasRole('team-admin')) {
            $templates = ProductTemplate::byTeam($team->id)->active()->get();
        } else {
            // Các role khác chỉ được lấy template của mình
            $templates = ProductTemplate::byTeam($team->id)->byUser($user->id)->active()->get();
        }

        return view('products.edit', compact('product', 'templates', 'team'));
    }

    /**
     * Cập nhật sản phẩm
     */
    public function update(Request $request, Product $product)
    {
        $user = Auth::user();
        $team = $user->currentTeam;

        if (!$team || $product->team_id !== $team->id) {
            abort(404);
        }

        // Kiểm tra quyền chỉnh sửa sản phẩm
        if (!$user->hasPermissionTo('update-products')) {
            abort(403, 'Bạn không có quyền chỉnh sửa sản phẩm trong team này.');
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'sku' => 'required|string|max:100|unique:products,sku,' . $product->id,
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

        // Xử lý upload ảnh mới lên S3
        if ($request->hasFile('product_images')) {
            // Xóa ảnh cũ nếu có
            foreach ($product->images as $image) {
                // Xóa file từ S3 nếu cần
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
                                'file_path' => $url, // Lưu S3 URL thay vì local path
                                'type' => 'image',
                                'source' => 'product',
                                'sort_order' => $index,
                                'is_primary' => $index === 0, // Ảnh đầu tiên là ảnh chính
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
            ->with('success', 'Sản phẩm đã được cập nhật thành công.');
    }

    /**
     * Xóa sản phẩm
     */
    public function destroy(Product $product)
    {
        $user = Auth::user();
        $team = $user->currentTeam;

        if (!$team || $product->team_id !== $team->id) {
            abort(404);
        }

        // Kiểm tra quyền xóa sản phẩm
        if (!$user->hasPermissionTo('delete-products')) {
            abort(403, 'Bạn không có quyền xóa sản phẩm trong team này.');
        }

        // Xóa ảnh sản phẩm từ S3
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
            ->with('success', 'Sản phẩm đã được xóa thành công.');
    }

    /**
     * Thay đổi trạng thái active/inactive
     */
    public function toggleStatus(Product $product)
    {
        $user = Auth::user();
        $team = $user->currentTeam;

        if (!$team || $product->team_id !== $team->id) {
            abort(404);
        }

        // Kiểm tra quyền chỉnh sửa sản phẩm
        if (!$user->hasPermissionTo('update-products')) {
            abort(403, 'Bạn không có quyền chỉnh sửa sản phẩm trong team này.');
        }

        $product->update(['is_active' => !$product->is_active]);

        $status = $product->is_active ? 'kích hoạt' : 'vô hiệu hóa';

        return redirect()->route('products.index')
            ->with('success', "Sản phẩm đã được {$status} thành công.");
    }

    /**
     * API để lấy danh sách sản phẩm theo template
     */
    public function getByTemplate(Request $request)
    {
        $user = Auth::user();
        $team = $user->currentTeam;

        if (!$team) {
            return response()->json(['error' => 'Team không tồn tại'], 404);
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
     * Upload hình ảnh sản phẩm lên TikTok Shop
     */
    public function uploadImagesToTikTok(Product $product)
    {
        $user = Auth::user();
        $team = $user->currentTeam;

        if (!$team || $product->team_id !== $team->id) {
            abort(404);
        }

        // Kiểm tra quyền chỉnh sửa sản phẩm
        if (!$user->hasPermissionTo('update-products')) {
            abort(403, 'Bạn không có quyền chỉnh sửa sản phẩm trong team này.');
        }

        try {
            // Lấy TikTok Shop integration của team
            $integration = $team->tiktokShopIntegration;
            if (!$integration) {
                return response()->json([
                    'success' => false,
                    'message' => 'Team chưa kết nối TikTok Shop'
                ], 400);
            }

            if (!$integration->access_token) {
                return response()->json([
                    'success' => false,
                    'message' => 'Access token TikTok Shop không hợp lệ'
                ], 400);
            }

            // Khởi tạo service upload
            $uploadService = new TikTokImageUploadService($integration);

            // Upload hình ảnh
            $result = $uploadService->uploadProductImages($product);

            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'message' => "Đã upload {$result['uploaded_count']} hình ảnh lên TikTok Shop thành công",
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
                'message' => 'Có lỗi xảy ra khi upload hình ảnh: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Upload hàng loạt sản phẩm lên TikTok Shop
     */
    public function bulkUploadToTikTok(Request $request)
    {
        $user = Auth::user();
        $team = $user->currentTeam;

        if (!$team) {
            return response()->json([
                'success' => false,
                'message' => 'Team không tồn tại'
            ], 404);
        }

        // Kiểm tra quyền chỉnh sửa sản phẩm
        if (!$user->hasPermissionTo('update-products')) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn không có quyền upload sản phẩm'
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

        // Lấy TikTok shops và kiểm tra quyền truy cập
        $tiktokShops = \App\Models\TikTokShop::whereIn('id', $tiktokShopIds)
            ->where('team_id', $team->id)
            ->get();

        if ($tiktokShops->count() !== count($tiktokShopIds)) {
            return response()->json([
                'success' => false,
                'message' => 'Một số TikTok Shop không tồn tại hoặc không thuộc team này'
            ], 404);
        }

        // Kiểm tra quyền truy cập từng shop
        foreach ($tiktokShops as $shop) {
            if (!$shop->canUserAccess($user)) {
                return response()->json([
                    'success' => false,
                    'message' => "Bạn không có quyền truy cập shop: {$shop->shop_name}"
                ], 403);
            }
        }

        // Lấy sản phẩm và kiểm tra quyền
        $products = Product::whereIn('id', $productIds)
            ->where('team_id', $team->id)
            ->get();

        if ($products->count() !== count($productIds)) {
            return response()->json([
                'success' => false,
                'message' => 'Một số sản phẩm không tồn tại hoặc không thuộc team này'
            ], 400);
        }

        // Lấy TikTok Shop integration
        $integration = $team->tiktokShopIntegration;
        if (!$integration || !$integration->isActive()) {
            return response()->json([
                'success' => false,
                'message' => 'Team chưa kết nối TikTok Shop hoặc kết nối không hoạt động'
            ], 400);
        }

        $totalSuccessCount = 0;
        $totalFailureCount = 0;
        $errors = [];
        $shopResults = [];

        try {
            // Khởi tạo service upload
            $uploadService = new TikTokImageUploadService($integration);

            foreach ($tiktokShops as $shop) {
                $shopSuccessCount = 0;
                $shopFailureCount = 0;
                $shopErrors = [];

                foreach ($products as $product) {
                    try {
                        // Upload hình ảnh cho từng sản phẩm
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
                            $shopErrors[] = "Sản phẩm '{$product->title}': {$result['message']}";
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
                        $shopErrors[] = "Sản phẩm '{$product->title}': {$e->getMessage()}";
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

            $message = "Upload hoàn tất: {$totalSuccessCount} thành công, {$totalFailureCount} thất bại";
            if (!empty($errors)) {
                $message .= "\n\nLỗi chi tiết:\n" . implode("\n", array_slice($errors, 0, 5));
                if (count($errors) > 5) {
                    $message .= "\n... và " . (count($errors) - 5) . " lỗi khác";
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
                'message' => 'Có lỗi xảy ra khi upload hàng loạt: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Lấy danh sách TikTok shops mà user có quyền truy cập
     */
    private function getUserAccessibleTikTokShops($user, $team)
    {
        $shops = $team->activeTikTokShops;

        // System admin có thể truy cập tất cả shops
        if ($user->hasRole('system-admin')) {
            return $shops;
        }

        // Team admin có thể truy cập tất cả shops trong team
        if ($user->hasRole('team-admin')) {
            return $shops;
        }

        // Seller chỉ có thể truy cập shops mà họ được assign
        $accessibleShops = collect();
        foreach ($shops as $shop) {
            if ($shop->canUserAccess($user)) {
                $accessibleShops->push($shop);
            }
        }

        return $accessibleShops;
    }

    /**
     * Upload sản phẩm lên TikTok Shop (không chỉ hình ảnh)
     */
    public function uploadProductToTikTok(Request $request)
    {
        $user = Auth::user();
        $team = $user->currentTeam;

        if (!$team) {
            return response()->json([
                'success' => false,
                'message' => 'Team không tồn tại'
            ], 404);
        }

        // Kiểm tra quyền chỉnh sửa sản phẩm
        if (!$user->hasPermissionTo('update-products')) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn không có quyền upload sản phẩm'
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

        // Lấy TikTok shops và kiểm tra quyền truy cập
        $tiktokShops = \App\Models\TikTokShop::whereIn('id', $tiktokShopIds)
            ->where('team_id', $team->id)
            ->get();

        if ($tiktokShops->count() !== count($tiktokShopIds)) {
            return response()->json([
                'success' => false,
                'message' => 'Một số TikTok Shop không tồn tại hoặc không thuộc team này'
            ], 404);
        }

        // Kiểm tra quyền truy cập từng shop
        foreach ($tiktokShops as $shop) {
            if (!$shop->canUserAccess($user)) {
                return response()->json([
                    'success' => false,
                    'message' => "Bạn không có quyền truy cập shop: {$shop->shop_name}"
                ], 403);
            }
        }

        // Lấy sản phẩm và kiểm tra quyền
        $products = Product::whereIn('id', $productIds)
            ->where('team_id', $team->id)
            ->get();

        if ($products->count() !== count($productIds)) {
            return response()->json([
                'success' => false,
                'message' => 'Một số sản phẩm không tồn tại hoặc không thuộc team này'
            ], 400);
        }

        try {
            // Khởi tạo service upload sản phẩm
            $productService = new TikTokShopProductService();

            // Gọi method bulk upload
            $results = $productService->bulkUploadProducts($productIds, $tiktokShopIds, $user->id);

            $message = "Upload sản phẩm hoàn tất: {$results['success_count']} thành công, {$results['failure_count']} thất bại";

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
                'message' => 'Có lỗi xảy ra khi upload sản phẩm: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Retry upload sản phẩm từ lịch sử
     */
    public function retryUpload(Request $request, $historyId)
    {
        $user = Auth::user();
        $team = $user->currentTeam;

        if (!$team) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn cần chọn một team để thực hiện thao tác này.'
            ], 400);
        }

        // Kiểm tra quyền upload sản phẩm
        if (!$user->hasPermissionTo('upload-products')) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn không có quyền upload sản phẩm.'
            ], 403);
        }

        try {
            $uploadHistory = TikTokProductUploadHistory::with(['product', 'tiktokShop'])
                ->findOrFail($historyId);

            // Kiểm tra quyền truy cập sản phẩm
            if ($uploadHistory->product->team_id !== $team->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bạn không có quyền truy cập sản phẩm này.'
                ], 403);
            }

            // Thực hiện upload lại
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
                'message' => 'Có lỗi xảy ra khi retry upload: ' . $e->getMessage()
            ], 500);
        }
    }
}
