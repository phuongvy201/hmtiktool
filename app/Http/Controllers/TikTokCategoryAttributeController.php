<?php

namespace App\Http\Controllers;

use App\Models\TikTokCategoryAttribute;
use App\Models\TikTokShopCategory;
use App\Models\TikTokShopIntegration;
use App\Services\TikTokShopService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TikTokCategoryAttributeController extends Controller
{
    /**
     * Hiển thị danh sách attributes của một category
     */
    public function index(Request $request)
    {
        $categoryId = $request->get('category_id');

        if (!$categoryId) {
            return view('tik-tok-category-attributes.index', [
                'categories' => TikTokShopCategory::where('is_leaf', true)->get(),
                'attributes' => collect(),
                'selectedCategory' => null
            ]);
        }

        $category = TikTokShopCategory::where('category_id', $categoryId)->first();
        if (!$category) {
            return redirect()->back()->with('error', 'Category không tồn tại');
        }

        $attributes = TikTokCategoryAttribute::where('category_id', $categoryId)
            ->orderBy('is_required', 'desc')
            ->orderBy('name')
            ->get();

        return view('tik-tok-category-attributes.index', [
            'categories' => TikTokShopCategory::where('is_leaf', true)->get(),
            'attributes' => $attributes,
            'selectedCategory' => $category
        ]);
    }

    /**
     * Sync attributes cho một category
     */
    public function sync(Request $request)
    {
        $request->validate([
            'category_id' => 'required|string',
            'force' => 'boolean'
        ]);

        $categoryId = $request->input('category_id');
        $force = $request->boolean('force', false);

        try {
            // Kiểm tra category
            $category = TikTokShopCategory::where('category_id', $categoryId)->first();
            if (!$category) {
                return response()->json([
                    'success' => false,
                    'message' => 'Category không tồn tại'
                ]);
            }

            if (!$category->is_leaf) {
                return response()->json([
                    'success' => false,
                    'message' => 'Chỉ có thể sync attributes cho leaf categories'
                ]);
            }

            // Kiểm tra integration
            $integration = TikTokShopIntegration::first();
            if (!$integration) {
                return response()->json([
                    'success' => false,
                    'message' => 'Chưa có TikTok Shop integration'
                ]);
            }

            // Gọi API để lấy attributes
            $service = new TikTokShopService();
            $result = $service->getCategoryAttributes($integration, $categoryId);

            if (!$result['success']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Lỗi khi lấy attributes: ' . ($result['error'] ?? 'Unknown error')
                ]);
            }

            $attributes = $result['data'];

            // Xóa attributes cũ nếu force sync
            if ($force) {
                TikTokCategoryAttribute::clearCategoryAttributes($categoryId);
            }

            // Lưu attributes mới
            $savedCount = 0;
            foreach ($attributes as $attribute) {
                TikTokCategoryAttribute::createOrUpdateFromApiData($categoryId, $attribute);
                $savedCount++;
            }

            Log::info('Category attributes synced manually', [
                'category_id' => $categoryId,
                'category_name' => $category->category_name,
                'attributes_count' => $savedCount,
                'force' => $force
            ]);

            return response()->json([
                'success' => true,
                'message' => "Đã sync thành công {$savedCount} attributes cho category {$category->category_name}",
                'data' => [
                    'category_id' => $categoryId,
                    'category_name' => $category->category_name,
                    'attributes_count' => $savedCount
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error syncing category attributes', [
                'category_id' => $categoryId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Lỗi hệ thống: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Hiển thị chi tiết một attribute
     */
    public function show($id)
    {
        $attribute = TikTokCategoryAttribute::findOrFail($id);

        return view('tik-tok-category-attributes.show', [
            'attribute' => $attribute
        ]);
    }

    /**
     * API endpoint để lấy attributes của một category
     */
    public function getAttributes(Request $request)
    {
        $request->validate([
            'category_id' => 'required|string'
        ]);

        $categoryId = $request->input('category_id');

        // Lấy tất cả attributes để tính stats
        $allAttributes = TikTokCategoryAttribute::where('category_id', $categoryId)
            ->orderBy('is_required', 'desc')
            ->orderBy('name')
            ->get();

        // Chỉ lấy attributes có type là PRODUCT_PROPERTY để hiển thị
        $productPropertyAttributes = TikTokCategoryAttribute::where('category_id', $categoryId)
            ->productProperties()
            ->orderBy('is_required', 'desc')
            ->orderBy('name')
            ->get();

        $groupedAttributes = [
            'required' => $productPropertyAttributes->where('is_required', true),
            'optional' => $productPropertyAttributes->where('is_required', false),
            'product_properties' => $productPropertyAttributes,
            'sales_properties' => $allAttributes->where('type', 'SALES_PROPERTY'),
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'attributes' => $productPropertyAttributes,
                'grouped' => $groupedAttributes,
                'stats' => [
                    'total' => $allAttributes->count(),
                    'required' => $productPropertyAttributes->where('is_required', true)->count(),
                    'optional' => $productPropertyAttributes->where('is_required', false)->count(),
                    'product_properties' => $productPropertyAttributes->count(),
                    'sales_properties' => $allAttributes->where('type', 'SALES_PROPERTY')->count(),
                ]
            ]
        ]);
    }

    /**
     * API endpoint để lấy values của một attribute
     */
    public function getAttributeValues(Request $request)
    {
        $request->validate([
            'attribute_id' => 'required|integer'
        ]);

        $attribute = TikTokCategoryAttribute::findOrFail($request->input('attribute_id'));

        return response()->json([
            'success' => true,
            'data' => [
                'attribute' => $attribute,
                'values' => $attribute->values ?? [],
                'values_list' => $attribute->values_list
            ]
        ]);
    }

    /**
     * Kiểm tra trạng thái sync của một category
     */
    public function checkSyncStatus(Request $request)
    {
        $request->validate([
            'category_id' => 'required|string'
        ]);

        $categoryId = $request->input('category_id');

        $lastSync = TikTokCategoryAttribute::where('category_id', $categoryId)
            ->max('last_synced_at');

        $attributesCount = TikTokCategoryAttribute::where('category_id', $categoryId)->count();

        $needsSync = TikTokCategoryAttribute::needsSync($categoryId);

        return response()->json([
            'success' => true,
            'data' => [
                'category_id' => $categoryId,
                'last_synced_at' => $lastSync,
                'attributes_count' => $attributesCount,
                'needs_sync' => $needsSync,
                'can_sync' => TikTokShopCategory::where('category_id', $categoryId)->where('is_leaf', true)->exists()
            ]
        ]);
    }
}
