<?php

namespace App\Http\Controllers;

use App\Models\TikTokCategoryAttribute;
use App\Models\TikTokShopCategory;
use App\Models\TikTokShopIntegration;
use App\Models\UserTikTokMarket;
use App\Services\TikTokShopService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
                'categories' => TikTokShopCategory::where('is_leaf', true)->where('is_active', true)->get(),
                'attributes' => collect(),
                'selectedCategory' => null
            ]);
        }

        $category = TikTokShopCategory::where('category_id', $categoryId)->where('is_active', true)->first();
        if (!$category) {
            return redirect()->back()->with('error', 'Category không tồn tại');
        }

        $attributes = TikTokCategoryAttribute::where('category_id', $categoryId)
            ->where('category_version', $category->category_version)
            ->where('market', $category->market)
            ->orderBy('is_required', 'desc')
            ->orderBy('name')
            ->get();

        return view('tik-tok-category-attributes.index', [
            'categories' => TikTokShopCategory::where('is_leaf', true)->where('is_active', true)->get(),
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
            $category = TikTokShopCategory::where('category_id', $categoryId)->where('is_active', true)->first();
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
            $integration = TikTokShopIntegration::forMarket($category->market)->first();
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

            // Lấy category_version từ category
            $categoryVersion = $category->category_version;

            // Xóa attributes cũ nếu force sync (theo category_version)
            if ($force) {
                TikTokCategoryAttribute::clearCategoryAttributes($categoryId, $categoryVersion, $category->market);
            }

            // Lưu attributes mới (với category_version)
            $savedCount = 0;
            foreach ($attributes as $attribute) {
                TikTokCategoryAttribute::createOrUpdateFromApiData($categoryId, $attribute, $category->market, $categoryVersion);
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
     * API endpoint để lấy attributes của một category từ TikTok API
     * Không lấy từ database nữa, gọi trực tiếp API TikTok
     */
    public function getAttributes(Request $request)
    {
        $request->validate([
            'category_id' => 'required|string'
        ]);

        $categoryId = $request->input('category_id');
        $user = Auth::user();

        if (!$user || !$user->team) {
            return response()->json([
                'success' => false,
                'error' => 'User không có team'
            ], 400);
        }

        // Lấy market từ bảng user_tiktok_markets (fallback sang method cũ nếu chưa có)
        $userMarket = UserTikTokMarket::where('user_id', $user->id)->value('market')
            ?? $user->getPrimaryTikTokMarket()
            ?? 'UK'; // Fallback mặc định nếu chưa gán market

        $market = strtoupper(trim($userMarket));
        $categoryVersion = $market === 'US' ? 'v2' : 'v1';

        Log::info('Category attributes - Market from user tiktokMarkets', [
            'user_id' => $user->id,
            'user_market' => $market,
            'category_version' => $categoryVersion,
            'category_id' => $categoryId
        ]);

        $groupedCollections = TikTokCategoryAttribute::getByCategoryWithGrouping($categoryId, $categoryVersion, $market);

        $requiredCollection = collect($groupedCollections['required']);
        $optionalCollection = collect($groupedCollections['optional']);
        $productCollection = collect($groupedCollections['product_properties']);
        $salesCollection = collect($groupedCollections['sales_properties']);

        if ($productCollection->isEmpty() && $salesCollection->isEmpty()) {
            return response()->json([
                'success' => false,
                'error' => 'Không tìm thấy dữ liệu attributes trong hệ thống. Vui lòng sync category trước khi sử dụng.'
            ], 404);
        }

        $requiredAttributes = $requiredCollection->map(function ($attribute) {
            return $this->formatAttributeFromModel($attribute);
        })->values()->toArray();

        $optionalAttributes = $optionalCollection->map(function ($attribute) {
            return $this->formatAttributeFromModel($attribute);
        })->values()->toArray();

        $productProperties = $productCollection->map(function ($attribute) {
            return $this->formatAttributeFromModel($attribute);
        })->values()->toArray();

        $salesProperties = $salesCollection->map(function ($attribute) {
            return $this->formatAttributeFromModel($attribute);
        })->values()->toArray();

        $totalProductAttributes = count($productProperties);
        $totalSalesAttributes = count($salesProperties);
        $totalAttributes = $totalProductAttributes + $totalSalesAttributes;

        $groupedAttributes = [
            'required' => $requiredAttributes,
            'optional' => $optionalAttributes,
            'product_properties' => $productProperties,
            'sales_properties' => $salesProperties,
        ];

        Log::info('Category attributes fetched from database cache', [
            'category_id' => $categoryId,
            'user_id' => $user->id,
            'user_market' => $market,
            'category_version' => $categoryVersion,
            'total_attributes' => $totalAttributes,
            'product_property_count' => $totalProductAttributes,
            'required_count' => count($requiredAttributes),
            'optional_count' => count($optionalAttributes),
            'sales_property_count' => $totalSalesAttributes,
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'attributes' => $productProperties,
                'grouped' => $groupedAttributes,
                'stats' => [
                    'total' => $totalAttributes,
                    'required' => count($requiredAttributes),
                    'optional' => count($optionalAttributes),
                    'product_properties' => $totalProductAttributes,
                    'sales_properties' => $totalSalesAttributes,
                ]
            ]
        ]);
    }

    /**
     * Format attribute từ API response
     */
    private function formatAttributeFromApi(array $attr): array
    {
        // Parse values
        $values = [];
        if (isset($attr['values']) && is_array($attr['values'])) {
            foreach ($attr['values'] as $value) {
                if (is_array($value)) {
                    $values[] = [
                        'id' => $value['id'] ?? $value['value_id'] ?? null,
                        'name' => $value['name'] ?? $value['value'] ?? $value['display_name'] ?? '',
                    ];
                } else {
                    $values[] = [
                        'id' => $value,
                        'name' => $value,
                    ];
                }
            }
        }

        return [
            'attribute_id' => $attr['id'] ?? $attr['attribute_id'] ?? null,
            'id' => $attr['id'] ?? $attr['attribute_id'] ?? null,
            'name' => $attr['name'] ?? $attr['display_name'] ?? 'Unknown',
            'type' => $attr['type'] ?? 'PRODUCT_PROPERTY',
            'is_required' => (bool) ($attr['is_required'] ?? $attr['required'] ?? false),
            'is_multiple_selection' => (bool) ($attr['is_multiple_selection'] ?? $attr['multiple_selection'] ?? false),
            'is_customizable' => (bool) ($attr['is_customizable'] ?? $attr['customizable'] ?? false),
            'value_data_format' => $attr['value_data_format'] ?? $attr['data_format'] ?? null,
            'values' => $values,
            'description' => $attr['description'] ?? $attr['help_text'] ?? null,
        ];
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

        // Lấy category để lấy category_version
        $category = TikTokShopCategory::where('category_id', $categoryId)->where('is_active', true)->first();
        $categoryVersion = $category ? $category->category_version : null;
        $market = $category ? $category->market : null;

        $query = TikTokCategoryAttribute::where('category_id', $categoryId);
        if ($categoryVersion) {
            $query->where('category_version', $categoryVersion);
        }

        if ($market) {
            $query->where('market', $market);
        }

        $lastSync = $query->max('last_synced_at');
        $attributesCount = $query->count();
        $needsSync = TikTokCategoryAttribute::needsSync($categoryId, 24, $categoryVersion, $market);

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

    private function formatAttributeFromModel(TikTokCategoryAttribute $attribute): array
    {
        $values = collect($attribute->values ?? [])->map(function ($value) {
            if (is_array($value)) {
                return [
                    'id' => $value['id'] ?? $value['value_id'] ?? $value['value'] ?? null,
                    'name' => $value['name'] ?? $value['value'] ?? $value['display_name'] ?? '',
                ];
            }

            return [
                'id' => $value,
                'name' => (string) $value,
            ];
        })->values()->toArray();

        $attributeData = $attribute->attribute_data ?? [];

        return [
            'attribute_id' => $attribute->attribute_id,
            'id' => $attribute->attribute_id,
            'name' => $attribute->name,
            'type' => $attribute->type ?? 'PRODUCT_PROPERTY',
            'is_required' => (bool) $attribute->is_required,
            'is_multiple_selection' => (bool) $attribute->is_multiple_selection,
            'is_customizable' => (bool) $attribute->is_customizable,
            'value_data_format' => $attribute->value_data_format,
            'values' => $values,
            'description' => $attributeData['description'] ?? $attributeData['help_text'] ?? null,
        ];
    }
}
