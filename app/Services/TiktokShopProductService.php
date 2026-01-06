<?php

namespace App\Services;

use App\Models\Product;
use App\Models\TikTokShop;
use App\Models\TikTokShopIntegration;
use App\Models\TikTokProductUploadHistory;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use App\Services\TikTokSignatureService;
use App\Services\TikTokShopService;

class TikTokShopProductService
{
    private const API_VERSION = '202309';
    /**
     * Upload sản phẩm lên TikTok Shop
     */
    public function uploadProduct(Product $product, TikTokShop $shop, ?int $userId = null): array
    {
        Log::info('=== START UPLOAD PRODUCT TO TIKTOK ===', [
            'product_id' => $product->id,
            'shop_id' => $shop->id,
            'shop_cipher' => $shop->getShopCipher(),
            'user_id' => $userId
        ]);

        // Lấy user market để xác định category version
        $userMarket = null;
        if ($userId) {
            $user = \App\Models\User::find($userId);
            if ($user) {
                $userMarket = $user->getPrimaryTikTokMarket();
            }
        }

        // Tạo bản ghi lịch sử upload
        $uploadHistory = $this->createUploadHistory($product, $shop, $userId);

        try {
            // Kiểm tra integration có hoạt động không
            $integration = $shop->integration;
            if (!$integration || !$integration->isActive()) {
                throw new \Exception('TikTok Shop integration không hoạt động hoặc token đã hết hạn');
            }

            // Kiểm tra access token
            if ($integration->isAccessTokenExpired()) {
                // Thử refresh token
                $refreshResult = $integration->refreshAccessToken();
                if (!$refreshResult['success']) {
                    throw new \Exception('Không thể refresh token: ' . $refreshResult['message']);
                }
            }

            // Tạo timestamp một lần duy nhất
            $timestamp = time();

            // Xác định category version dựa trên user market hoặc integration market
            $market = $userMarket ?? $integration->market;
            $categoryVersion = $this->getCategoryVersionFromMarket($market);

            // Chuẩn bị dữ liệu sản phẩm (truyền market và category version để validate category)
            $productData = $this->prepareProductData($product, $shop, $timestamp, $market, $categoryVersion);

            // Cập nhật lịch sử với request data
            $uploadHistory->update([
                'request_data' => $productData,
                'idempotency_key' => $productData['idempotency_key'] ?? null
            ]);

            // Gọi API upload sản phẩm (truyền user market để xác định category version)
            $response = $this->callUploadProductAPI($productData, $integration, $shop, $timestamp, $userMarket);

            if ($response['success']) {
                // Cập nhật lịch sử thành công
                $this->updateUploadHistorySuccess($uploadHistory, $response['data']);

                Log::info('Product upload successful', [
                    'product_id' => $product->id,
                    'shop_id' => $shop->id,
                    'tiktok_product_id' => $response['data']['product_id'] ?? null,
                    'upload_history_id' => $uploadHistory->id
                ]);

                return [
                    'success' => true,
                    'message' => 'Upload sản phẩm thành công',
                    'data' => $response['data'],
                    'upload_history_id' => $uploadHistory->id
                ];
            } else {
                // Cập nhật lịch sử thất bại
                $this->updateUploadHistoryFailed($uploadHistory, $response['message'] ?? 'Lỗi không xác định khi upload sản phẩm', $response);

                throw new \Exception($response['message'] ?? 'Lỗi không xác định khi upload sản phẩm');
            }
        } catch (\Exception $e) {
            // Cập nhật lịch sử thất bại
            $this->updateUploadHistoryFailed($uploadHistory, $e->getMessage());

            Log::error('Product upload failed', [
                'product_id' => $product->id,
                'shop_id' => $shop->id,
                'error' => $e->getMessage(),
                'upload_history_id' => $uploadHistory->id
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage(),
                'upload_history_id' => $uploadHistory->id
            ];
        } finally {
            Log::info('=== END UPLOAD PRODUCT TO TIKTOK ===');
        }
    }
    private function prepareProductData(Product $product, TikTokShop $shop, int $timestamp, ?string $market = null, ?string $categoryVersion = null): array
    {
        // Lấy thông tin template nếu có (bao gồm variants, options, optionValues)
        $template = $product->productTemplate;
        if ($template) {
            $template->load([
                'variants.optionValues.option',
                'options.values'
            ]);
        }

        // Xác định category_id đúng với market và category version
        $categoryId = $this->getValidCategoryId($template, $market, $categoryVersion);

        // Chuẩn bị ảnh chính từ ProductImage với TikTok URI
        $mainImages = [];

        // 1. Kiểm tra và upload ảnh sản phẩm nếu chưa có TikTok URI
        $this->ensureProductImagesHaveTikTokUri($product, $shop);

        // 2. Lấy ảnh từ relationship images với TikTok URI (refresh để lấy dữ liệu mới)
        $product->load('images'); // Refresh relationship
        if ($product->images && $product->images->count() > 0) {
            foreach ($product->images as $image) {
                // Sử dụng ảnh đã có TikTok URI
                if ($image->tiktok_uri) {
                    $mainImages[] = [
                        'uri' => $image->tiktok_uri
                    ];
                }
            }
        }

        // 3. Nếu không có ảnh sản phẩm với TikTok URI, lấy ảnh random từ ProductImage
        if (empty($mainImages)) {
            $randomImage = \App\Models\ProductImage::whereNotNull('tiktok_uri')
                ->where('tiktok_uri', '!=', '')
                ->inRandomOrder()
                ->first();

            if ($randomImage) {
                $mainImages[] = [
                    'uri' => $randomImage->tiktok_uri
                ];
                Log::info('Using random image for product', [
                    'product_id' => $product->id,
                    'random_image_id' => $randomImage->id,
                    'tiktok_uri' => $randomImage->tiktok_uri
                ]);
            }
        }

        // 4. Nếu vẫn không có ảnh, sử dụng ảnh mặc định
        if (empty($mainImages)) {
            $mainImages[] = ['uri' => 'default_product_image_uri'];
            Log::warning('No TikTok images found, using default', [
                'product_id' => $product->id
            ]);
        }

        // Lấy warehouse ID thực tế từ TikTok API
        $warehouseId = $this->getDefaultWarehouseId($shop);

        // Chuẩn bị SKU data theo cấu trúc TikTok API
        $skus = $this->prepareSkusData($product, $template, $warehouseId);

        // Chuẩn bị package weight từ template hoặc giá trị mặc định
        $packageWeight = $this->preparePackageWeight($template, $shop->seller_region);

        $productData = [
            'save_mode' => 'LISTING',
            'title' => $product->title,
            'description' => $product->description ?? $template->description ?? '',
            'category_id' => $categoryId,
            'main_images' => $mainImages,
            'skus' => $skus,
            'package_weight' => $packageWeight,
            'is_cod_allowed' => false,
            'idempotency_key' => $this->generateIdempotencyKey($product, $shop, $timestamp),
        ];

        // Thêm package_dimensions nếu có (theo cấu trúc TikTok API)
        // Bắt buộc có package_dimensions cho thị trường UK
        // TikTok yêu cầu length phải là số nguyên dương (positive whole number)
        // Đảm bảo length >= 2 để tránh lỗi "Incorrect parcel length format"
        $length = max(2, round($template->length ?? 10.00));
        $productData['package_dimensions'] = [
            'height' => (string) round($template->height ?? 10.00),
            'width' => (string) round($template->width ?? 10.00),
            'length' => (string) $length, // Đảm bảo >= 2
            'unit' => $this->getDimensionUnit($shop->seller_region)
        ];

        // Thêm product_attributes từ ProdTemplateCategoryAttribute
        $productAttributes = $this->prepareProductAttributes($template);
        if (!empty($productAttributes)) {
            $productData['product_attributes'] = $productAttributes;
        }

        // Thêm size chart nếu có (từ product hoặc template)
        $sizeChart = $product->size_chart ?? ($template ? $template->size_chart : null) ?? null;
        if ($sizeChart) {
            $sizeChartUri = $this->ensureSizeChartHasTikTokUri($sizeChart, $shop);
            if ($sizeChartUri) {
                $productData['size_chart'] = [
                    'uri' => $sizeChartUri
                ];
            }
        }

        // Thêm product video nếu có (từ product hoặc template)
        $productVideo = $product->product_video ?? ($template ? $template->product_video : null) ?? null;
        if ($productVideo) {
            $videoUri = $this->ensureVideoHasTikTokUri($productVideo, $shop);
            if ($videoUri) {
                $productData['video'] = [
                    'uri' => $videoUri
                ];
            }
        }

        Log::info('Prepared product data', [
            'product_id' => $product->id,
            'title' => $productData['title'],
            'main_images_count' => count($mainImages),
            'has_dimensions' => isset($productData['package_dimensions']),
            'has_size_chart' => isset($productData['size_chart']),
            'has_video' => isset($productData['video']),
            'warehouse_id' => $warehouseId,
            'sku_inventory' => $skus[0]['inventory'],
            'idempotency_key' => $productData['idempotency_key']
        ]);

        return $productData;
    }
    private function callUploadProductAPI(array $productData, TikTokShopIntegration $integration, TikTokShop $shop, int $timestamp, ?string $userMarket = null): array
    {
        $url = 'https://open-api.tiktokglobalshop.com/product/' . self::API_VERSION . '/products';

        // Category version: Ưu tiên lấy từ user market, nếu không có thì lấy từ integration market
        // US = v2, UK và các region khác = v1
        $categoryVersion = $this->getCategoryVersionFromMarket($userMarket ?? $integration->market);

        // Sử dụng timestamp đã được truyền vào
        $queryParams = [
            'shop_cipher' => $shop->getShopCipher(),
            'app_key' => $integration->getAppKey(),
            'timestamp' => $timestamp,
            'category_version' => $categoryVersion
        ];

        // Tạo signature sử dụng TikTokSignatureService (BAO GỒM shop_cipher và category_version trong signature)
        $signature = TikTokSignatureService::generateProductUploadSignature(
            $integration->getAppKey(),
            $integration->getAppSecret(),
            (string) $timestamp,
            $productData,
            $shop->getShopCipher(),
            $categoryVersion
        );

        $queryParams['sign'] = $signature;

        Log::info('Using category version for product upload', [
            'integration_id' => $integration->id,
            'integration_market' => $integration->market,
            'user_market' => $userMarket,
            'category_version' => $categoryVersion,
            'note' => 'Category version determined by user market (if available), otherwise integration market'
        ]);

        $headers = [
            'Content-Type' => 'application/json',
            'x-tts-access-token' => $integration->access_token
        ];

        // Log request details giống như TikTokImageUploadService
        Log::info('TikTok Product Upload API Request Details', [
            'url' => $url,
            'headers' => [
                'x-tts-access-token' => substr($integration->access_token, 0, 20) . '...',
                'content-type' => 'application/json'
            ],
            'query_params' => $queryParams,
            'product_data' => $productData,
            'app_key_full' => $integration->getAppKey(),
            'app_secret_length' => strlen($integration->getAppSecret()),
            'access_token_full' => $integration->access_token,
            'shop_cipher' => $shop->getShopCipher(),
            'sign_generation' => [
                'app_key' => $integration->getAppKey(),
                'app_secret_length' => strlen($integration->getAppSecret()),
                'timestamp' => $timestamp,
                'sign' => $signature,
                'method' => 'TikTokSignatureService::generateProductUploadSignature',
                'api_path' => '/product/' . self::API_VERSION . '/products',
                'content_type' => 'application/json',
                'has_body_params' => !empty($productData),
                'note' => 'shop_cipher INCLUDED in signature generation and query params'
            ]
        ]);

        // Đảm bảo JSON encoding nhất quán với signature generation
        $jsonBody = json_encode($productData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        $response = Http::withHeaders($headers)
            ->timeout(30)
            ->withBody($jsonBody, 'application/json')
            ->post($url . '?' . http_build_query($queryParams));

        $httpCode = $response->status();
        $responseData = $response->json();

        // Log response details giống như TikTokImageUploadService
        Log::info('TikTok Product Upload API Response Details', [
            'status_code' => $httpCode,
            'response_body' => $responseData,
            'response_headers' => $response->headers()
        ]);

        if ($httpCode === 200 && isset($responseData['code']) && $responseData['code'] === 0) {
            return [
                'success' => true,
                'data' => $responseData['data'] ?? []
            ];
        } else {
            return [
                'success' => false,
                'message' => $responseData['message'] ?? 'API call failed',
                'code' => $responseData['code'] ?? null
            ];
        }
    }

    private function getCurrencyByRegion(?string $region): string
    {
        return match ($region) {
            'BR' => 'BRL',
            'FR', 'DE', 'IE', 'IT', 'ES' => 'EUR',
            'UK' => 'GBP',
            'ID' => 'IDR',
            'JP' => 'JPY',
            'MX' => 'MXN',
            'MY' => 'MYR',
            'PH' => 'PHP',
            'SG' => 'SGD',
            'TH' => 'THB',
            'VN' => 'VND',
            default => 'GBP'
        };
    }

    /**
     * Lấy warehouse ID mặc định từ TikTok API
     */
    private function getDefaultWarehouseId(TikTokShop $shop): string
    {
        try {
            // Lấy warehouses từ TikTok API
            $tiktokService = new TikTokShopService();
            $warehousesResult = $tiktokService->getWarehouses($shop->integration, $shop->id);

            if ($warehousesResult['success']) {
                $warehouses = $warehousesResult['data'];

                if (!empty($warehouses)) {
                    // Ưu tiên warehouse mặc định (is_default = true) trước
                    $defaultWarehouse = null;
                    $salesWarehouse = null;
                    $firstWarehouse = null;

                    foreach ($warehouses as $warehouse) {
                        if (isset($warehouse['id'])) {
                            if (!$firstWarehouse) {
                                $firstWarehouse = $warehouse;
                            }

                            // Ưu tiên warehouse mặc định trước
                            if ($warehouse['is_default'] ?? false) {
                                $defaultWarehouse = $warehouse;
                            }

                            // Nếu không có default, tìm Sales Warehouse
                            if ($warehouse['type'] === 'SALES_WAREHOUSE') {
                                $salesWarehouse = $warehouse;
                            }
                        }
                    }

                    // Ưu tiên: Default Warehouse > Sales Warehouse > First Warehouse
                    $selectedWarehouse = $defaultWarehouse ?? $salesWarehouse ?? $firstWarehouse;

                    if ($selectedWarehouse) {
                        Log::info('Using warehouse from TikTok API', [
                            'shop_id' => $shop->shop_id,
                            'warehouse_id' => $selectedWarehouse['id'],
                            'warehouse_name' => $selectedWarehouse['name'] ?? 'Unknown',
                            'warehouse_type' => $selectedWarehouse['type'] ?? 'Unknown',
                            'is_default' => $selectedWarehouse['is_default'] ?? false,
                            'selection_reason' => $defaultWarehouse ? 'DEFAULT_WAREHOUSE' : ($salesWarehouse ? 'SALES_WAREHOUSE' : 'FIRST_WAREHOUSE')
                        ]);
                        return $selectedWarehouse['id'];
                    }
                } else {
                    Log::warning('No warehouses found from TikTok API - shop may not have warehouses setup', [
                        'shop_id' => $shop->shop_id,
                        'message' => $warehousesResult['message'] ?? 'No warehouses available'
                    ]);
                }
            }

            Log::warning('No warehouses found from TikTok API, using default', [
                'shop_id' => $shop->shop_id
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get warehouses from TikTok API', [
                'shop_id' => $shop->shop_id,
                'error' => $e->getMessage()
            ]);
        }

        // Fallback to default warehouse ID
        return 'UK_WAREHOUSE_001';
    }

    /**
     * Lấy category ID hợp lệ dựa trên market và category version
     */
    private function getValidCategoryId($template, ?string $market = null, ?string $categoryVersion = null): string
    {
        // Nếu có template và category_id từ template
        if ($template && $template->category_id) {
            $templateCategoryId = $template->category_id;

            // Nếu có market và category version, kiểm tra xem category có thuộc về version đó không
            if ($market && $categoryVersion) {
                $category = \App\Models\TikTokShopCategory::where('category_id', $templateCategoryId)
                    ->where('market', $market)
                    ->where('category_version', $categoryVersion)
                    ->where('is_leaf', true)
                    ->first();

                if ($category) {
                    Log::info('Using template category ID that matches market and version', [
                        'category_id' => $templateCategoryId,
                        'market' => $market,
                        'category_version' => $categoryVersion
                    ]);
                    return $templateCategoryId;
                } else {
                    // Category không thuộc về version này, tìm category tương ứng hoặc default
                    Log::warning('Template category ID does not match market/version, finding alternative', [
                        'template_category_id' => $templateCategoryId,
                        'market' => $market,
                        'category_version' => $categoryVersion
                    ]);

                    // Tìm category leaf đầu tiên của market và version này
                    $alternativeCategory = \App\Models\TikTokShopCategory::where('market', $market)
                        ->where('category_version', $categoryVersion)
                        ->where('is_leaf', true)
                        ->first();

                    if ($alternativeCategory) {
                        Log::info('Using alternative category ID from same market/version', [
                            'original_category_id' => $templateCategoryId,
                            'alternative_category_id' => $alternativeCategory->category_id,
                            'market' => $market,
                            'category_version' => $categoryVersion
                        ]);
                        return $alternativeCategory->category_id;
                    }
                }
            } else {
                // Không có market/version info, dùng category từ template
                return $templateCategoryId;
            }
        }

        // Nếu không có template category hoặc không tìm thấy category hợp lệ, lấy default category
        return $this->getDefaultCategoryId($market, $categoryVersion);
    }

    /**
     * Lấy category ID mặc định dựa trên market và category version
     */
    private function getDefaultCategoryId(?string $market = null, ?string $categoryVersion = null): string
    {
        // Nếu có market và category version, tìm category leaf đầu tiên
        if ($market && $categoryVersion) {
            $category = \App\Models\TikTokShopCategory::where('market', $market)
                ->where('category_version', $categoryVersion)
                ->where('is_leaf', true)
                ->first();

            if ($category) {
                Log::info('Using default category ID from market/version', [
                    'category_id' => $category->category_id,
                    'market' => $market,
                    'category_version' => $categoryVersion
                ]);
                return $category->category_id;
            }
        }

        // Fallback về category ID mặc định
        Log::warning('Using fallback default category ID', [
            'market' => $market,
            'category_version' => $categoryVersion
        ]);
        return '1000001'; // Default category ID
    }

    /**
     * Chuẩn bị SKUs data từ ProductTemplateVariant (đầy đủ các trường theo TikTok API)
     */
    private function prepareSkusData(Product $product, $template, string $warehouseId): array
    {
        $skus = [];

        // Lấy variants từ template
        if ($template && $template->variants) {
            foreach ($template->variants as $variant) {
                $sku = [
                    'sales_attributes' => $this->prepareSalesAttributes($variant),
                    'inventory' => [
                        [
                            'warehouse_id' => $warehouseId,
                            'quantity' => $variant->stock_quantity ?? 999
                        ]
                    ],
                    'price' => [
                        'currency' => 'GBP', // Cố định GBP
                        'amount' => (string) $variant->price,
                    ],
                    'seller_sku' => $variant->sku, // Lấy từ ProductTemplateVariant
                ];

                // Chỉ thêm list_price nếu có giá trị
                if ($variant->list_price) {
                    $sku['list_price'] = [
                        'amount' => (string) $variant->list_price,
                        'currency' => 'GBP'
                    ];
                }


                $skus[] = $sku;
            }
        }

        // Nếu không có variants, tạo SKU mặc định
        if (empty($skus)) {
            // Tạo variant giả để sử dụng prepareSalesAttributes
            $fakeVariant = (object) [
                'variant_data' => null,
                'price' => $product->total_price
            ];

            $skus[] = [
                'sales_attributes' => $this->prepareSalesAttributes($fakeVariant),
                'inventory' => [
                    [
                        'warehouse_id' => $warehouseId,
                        'quantity' => 999
                    ]
                ],
                'price' => [
                    'currency' => 'GBP',
                    'amount' => (string) $product->total_price,
                    'sale_price' => (string) $product->total_price
                ],
                'seller_sku' => 'SKU-' . $product->id . '-' . time(),
                'external_sku_id' => (string) $product->id,

                'list_price' => [
                    'amount' => (string) $product->total_price,
                    'currency' => 'GBP'
                ],
                'external_list_prices' => [
                    [
                        'source' => 'SHOPIFY_COMPARE_AT_PRICE',
                        'amount' => (string) $product->total_price,
                        'currency' => 'GBP'
                    ]
                ]
            ];
        }

        return $skus;
    }

    /**
     * Chuẩn bị sales_attributes từ database (ProductTemplateOption, ProductTemplateOptionValue)
     */
    private function prepareSalesAttributes($variant): array
    {
        $salesAttributes = [];

        // Lấy sales_attributes từ optionValues của variant
        if (isset($variant->optionValues) && $variant->optionValues->count() > 0) {
            foreach ($variant->optionValues as $optionValue) {
                $salesAttributes[] = [
                    'value_name' => $optionValue->value,
                    'name' => $optionValue->option->name ?? 'Unknown',
                ];
            }
        }

        // Fallback: Thêm sales_attributes từ variant_data nếu có
        if (empty($salesAttributes) && $variant->variant_data && is_array($variant->variant_data)) {
            foreach ($variant->variant_data as $key => $value) {
                $salesAttributes[] = [
                    'id' => '100089',
                    'value_id' => '1729592969712207000',
                    'value_name' => $value,
                    'name' => ucfirst($key),
                ];
            }
        }

        // Nếu vẫn không có, tạo sales_attributes mặc định
        if (empty($salesAttributes)) {
            $salesAttributes[] = [
                'id' => '100089',
                'value_id' => '1729592969712207000',
                'value_name' => (string) $variant->price,
                'name' => 'Price',
            ];
        }

        return $salesAttributes;
    }

    /**
     * Chuẩn bị package weight (theo cấu trúc TikTok API)
     */
    private function preparePackageWeight($template, ?string $region): array
    {
        $weight = $template->weight ?? 1.0; // Default 1kg
        $unit = $this->getWeightUnit($region);

        // Format weight theo quy tắc TikTok Shop
        $formattedWeight = $this->formatWeightValue($weight, $unit);

        return [
            'value' => $formattedWeight,
            'unit' => $unit
        ];
    }

    /**
     * Tạo idempotency_key duy nhất cho request
     * Format: product_{product_id}_shop_{shop_id}_{timestamp}_{uuid}
     */
    private function generateIdempotencyKey(Product $product, TikTokShop $shop, int $timestamp = null): string
    {
        $timestamp = $timestamp ?? time();
        $uuid = \Illuminate\Support\Str::uuid()->toString();

        // Tạo key với format: product_{id}_shop_{id}_{timestamp}_{uuid}
        $key = "product_{$product->id}_shop_{$shop->id}_{$timestamp}_{$uuid}";

        // Đảm bảo không vượt quá 128 ký tự
        if (strlen($key) > 128) {
            $key = "prod_{$product->id}_shop_{$shop->id}_{$timestamp}_" . substr($uuid, 0, 8);
        }

        return $key;
    }

    /**
     * Chuẩn bị product_attributes từ ProdTemplateCategoryAttribute
     */
    private function prepareProductAttributes($template): array
    {
        if (!$template) {
            return [];
        }

        $attributes = \App\Models\ProdTemplateCategoryAttribute::where('product_template_id', $template->id)
            ->where('attribute_type', 'PRODUCT_PROPERTY')
            ->get();

        $productAttributes = [];

        foreach ($attributes as $attribute) {
            $attributeData = [
                'id' => $attribute->attribute_id,
                'values' => []
            ];

            // Xử lý value_id và value_name
            $valueIds = [];
            $valueNames = [];

            if ($attribute->value_id) {
                if (is_array($attribute->value_id)) {
                    $valueIds = $attribute->value_id;
                } elseif (is_string($attribute->value_id)) {
                    // Nếu là JSON string, decode
                    if (str_starts_with($attribute->value_id, '[')) {
                        $valueIds = json_decode($attribute->value_id, true) ?: [];
                    } else {
                        // Nếu là string đơn giản, tạo array
                        $valueIds = [$attribute->value_id];
                    }
                }
            }

            if ($attribute->value_name) {
                if (is_array($attribute->value_name)) {
                    $valueNames = $attribute->value_name;
                } elseif (is_string($attribute->value_name)) {
                    // Nếu là JSON string, decode
                    if (str_starts_with($attribute->value_name, '[')) {
                        $valueNames = json_decode($attribute->value_name, true) ?: [];
                    } else {
                        // Nếu là string đơn giản, tạo array
                        $valueNames = [$attribute->value_name];
                    }
                }
            }

            // Tạo values array từ value_id và value_name
            for ($i = 0; $i < max(count($valueIds), count($valueNames)); $i++) {
                $valueId = $valueIds[$i] ?? null;
                $valueName = $valueNames[$i] ?? null;

                if ($valueId && $valueName) {
                    $attributeData['values'][] = [
                        'id' => $valueId,
                        'name' => $valueName
                    ];
                }
            }

            // Chỉ thêm attribute nếu có values
            if (!empty($attributeData['values'])) {
                $productAttributes[] = $attributeData;
            }
        }

        return $productAttributes;
    }

    /**
     * Format weight value theo quy tắc TikTok Shop
     * - GRAM: integer
     * - KILOGRAM: up to 3 decimal places  
     * - POUND: up to 2 decimal places
     */
    private function formatWeightValue(float $weight, string $unit): string
    {
        switch ($unit) {
            case 'GRAM':
                return (string) round($weight * 1000); // Convert kg to gram, integer
            case 'KILOGRAM':
                return number_format($weight, 3, '.', ''); // Up to 3 decimal places
            case 'POUND':
                $pounds = $weight * 2.20462; // Convert kg to pounds
                return number_format($pounds, 2, '.', ''); // Up to 2 decimal places
            default:
                return number_format($weight, 3, '.', ''); // Default to kg format
        }
    }

    /**
     * Lấy đơn vị trọng lượng theo region
     */
    private function getWeightUnit(?string $region): string
    {
        // Sử dụng mặc định là KILOGRAM cho tất cả regions
        return 'KILOGRAM';
    }

    /**
     * Lấy đơn vị kích thước theo region
     */
    private function getDimensionUnit(?string $region): string
    {
        // Sử dụng mặc định là CENTIMETER cho tất cả regions
        return 'CENTIMETER';
    }

    /**
     * Đảm bảo tất cả ảnh sản phẩm đã có TikTok URI
     */
    private function ensureProductImagesHaveTikTokUri(Product $product, TikTokShop $shop): void
    {
        try {
            // Lấy tất cả ảnh sản phẩm chưa có TikTok URI
            $imagesWithoutUri = $product->images()
                ->where(function ($query) {
                    $query->whereNull('tiktok_uri')
                        ->orWhere('tiktok_uri', '');
                })
                ->get();

            if ($imagesWithoutUri->isEmpty()) {
                Log::info('All product images already have TikTok URI', [
                    'product_id' => $product->id
                ]);
                return;
            }

            Log::info('Found images without TikTok URI, uploading...', [
                'product_id' => $product->id,
                'count' => $imagesWithoutUri->count()
            ]);

            // Sử dụng TikTokImageUploadService để upload ảnh
            $imageUploadService = new \App\Services\TikTokImageUploadService($shop->integration);

            foreach ($imagesWithoutUri as $image) {
                try {
                    // Upload ảnh lên TikTok bằng phương thức public
                    $uploadResult = $this->uploadImageToTikTok($imageUploadService, $image->file_path, 'MAIN_IMAGE');

                    if ($uploadResult['success']) {
                        // Cập nhật ProductImage với TikTok URI
                        $image->markAsUploadedToTiktok(
                            $uploadResult['data']['uri'] ?? null,
                            $uploadResult['data']['url'] ?? null
                        );

                        Log::info('Successfully uploaded product image to TikTok', [
                            'product_id' => $product->id,
                            'image_id' => $image->id,
                            'file_name' => $image->file_name,
                            'tiktok_uri' => $uploadResult['data']['uri'] ?? null
                        ]);
                    } else {
                        Log::error('Failed to upload product image to TikTok', [
                            'product_id' => $product->id,
                            'image_id' => $image->id,
                            'file_name' => $image->file_name,
                            'error' => $uploadResult['message'] ?? 'Unknown error'
                        ]);
                    }
                } catch (\Exception $e) {
                    Log::error('Exception while uploading product image', [
                        'product_id' => $product->id,
                        'image_id' => $image->id,
                        'file_name' => $image->file_name,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            // Nếu có template, cũng upload ảnh template
            if ($product->productTemplate) {
                $this->uploadTemplateImages($product, $shop);
            }
        } catch (\Exception $e) {
            Log::error('Error ensuring product images have TikTok URI', [
                'product_id' => $product->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Upload ảnh template lên TikTok
     */
    private function uploadTemplateImages(Product $product, TikTokShop $shop): void
    {
        try {
            $template = $product->productTemplate;
            if (!$template || !$template->images) {
                return;
            }

            Log::info('Uploading template images to TikTok', [
                'product_id' => $product->id,
                'template_id' => $template->id
            ]);

            $imageUploadService = new \App\Services\TikTokImageUploadService($shop->integration);

            // Xử lý template images (có thể là array hoặc string)
            $templateImages = is_array($template->images) ? $template->images : [$template->images];

            foreach ($templateImages as $index => $templateImage) {
                try {
                    $filePath = null;
                    $fileName = null;

                    if (is_array($templateImage) && isset($templateImage['file_path'])) {
                        $filePath = $templateImage['file_path'];
                        $fileName = $templateImage['file_name'] ?? 'template_' . ($index + 1);
                    } elseif (is_string($templateImage)) {
                        $filePath = $templateImage;
                        $fileName = 'template_' . ($index + 1) . '.jpg';
                    }

                    if (!$filePath) {
                        continue;
                    }

                    // Upload ảnh template lên TikTok
                    $uploadResult = $this->uploadImageToTikTok($imageUploadService, $filePath, 'MAIN_IMAGE');

                    if ($uploadResult['success']) {
                        // Tạo ProductImage record mới cho template image
                        \App\Models\ProductImage::create([
                            'product_id' => $product->id,
                            'file_name' => $fileName,
                            'file_path' => $filePath,
                            'tiktok_uri' => $uploadResult['data']['uri'] ?? null,
                            'tiktok_resource_id' => $uploadResult['data']['url'] ?? null,
                            'type' => 'image',
                            'source' => 'template',
                            'sort_order' => $product->images()->count() + $index + 1,
                            'is_primary' => false,
                            'is_uploaded_to_tiktok' => true,
                            'tiktok_uploaded_at' => now()
                        ]);

                        Log::info('Successfully uploaded template image to TikTok', [
                            'product_id' => $product->id,
                            'template_id' => $template->id,
                            'file_name' => $fileName,
                            'tiktok_uri' => $uploadResult['data']['uri'] ?? null
                        ]);
                    } else {
                        Log::error('Failed to upload template image to TikTok', [
                            'product_id' => $product->id,
                            'template_id' => $template->id,
                            'file_name' => $fileName,
                            'error' => $uploadResult['message'] ?? 'Unknown error'
                        ]);
                    }
                } catch (\Exception $e) {
                    Log::error('Exception while uploading template image', [
                        'product_id' => $product->id,
                        'template_id' => $template->id,
                        'index' => $index,
                        'error' => $e->getMessage()
                    ]);
                }
            }
        } catch (\Exception $e) {
            Log::error('Error uploading template images', [
                'product_id' => $product->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Upload ảnh lên TikTok bằng TikTokImageUploadService
     */
    private function uploadImageToTikTok(\App\Services\TikTokImageUploadService $imageUploadService, string $filePath, string $useCase = 'MAIN_IMAGE'): array
    {
        try {
            // Tạo một Product tạm thời để sử dụng uploadProductImages
            $tempProduct = new \App\Models\Product();
            $tempProduct->id = 0; // ID tạm thời

            // Tạo ProductImage tạm thời
            $tempImage = new \App\Models\ProductImage();
            $tempImage->file_path = $filePath;
            $tempImage->file_name = basename($filePath);

            // Sử dụng reflection để gọi protected method uploadSingleImage
            $reflection = new \ReflectionClass($imageUploadService);
            $method = $reflection->getMethod('uploadSingleImage');
            $method->setAccessible(true);

            return $method->invoke($imageUploadService, $filePath, $useCase);
        } catch (\Exception $e) {
            Log::error('Error in uploadImageToTikTok', [
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
     * Lấy TikTok URI từ local image (cần implement)
     */
    private function getTikTokImageUri(string $imagePath): ?string
    {
        // TODO: Implement logic upload image lên TikTok và lấy URI
        // Tạm thời return null
        return null;
    }


    /**
     * Upload hàng loạt sản phẩm
     */
    public function bulkUploadProducts(array $productIds, array $shopIds, ?int $userId = null): array
    {
        $results = [
            'success_count' => 0,
            'failure_count' => 0,
            'details' => []
        ];

        Log::info('Starting bulk upload', [
            'product_count' => count($productIds),
            'shop_count' => count($shopIds)
        ]);

        foreach ($productIds as $productId) {
            $product = Product::find($productId);
            if (!$product) {
                $results['failure_count']++;
                $results['details'][] = [
                    'product_id' => $productId,
                    'status' => 'failed',
                    'message' => 'Sản phẩm không tồn tại'
                ];
                continue;
            }

            foreach ($shopIds as $shopId) {
                $shop = TikTokShop::find($shopId);
                if (!$shop) {
                    $results['failure_count']++;
                    $results['details'][] = [
                        'product_id' => $productId,
                        'shop_id' => $shopId,
                        'status' => 'failed',
                        'message' => 'Shop không tồn tại'
                    ];
                    continue;
                }

                $uploadResult = $this->uploadProduct($product, $shop, $userId);

                if ($uploadResult['success']) {
                    $results['success_count']++;
                    $results['details'][] = [
                        'product_id' => $productId,
                        'shop_id' => $shopId,
                        'product_title' => $product->title,
                        'shop_name' => $shop->shop_name,
                        'status' => 'success',
                        'message' => $uploadResult['message']
                    ];
                } else {
                    $results['failure_count']++;
                    $results['details'][] = [
                        'product_id' => $productId,
                        'shop_id' => $shopId,
                        'product_title' => $product->title,
                        'shop_name' => $shop->shop_name,
                        'status' => 'failed',
                        'message' => $uploadResult['message']
                    ];
                }
            }
        }

        Log::info('Bulk upload completed', [
            'success_count' => $results['success_count'],
            'failure_count' => $results['failure_count']
        ]);

        return $results;
    }

    /**
     * Lấy category version từ market
     * US = v2, UK và các region khác = v1
     */
    private function getCategoryVersionFromMarket(?string $market): string
    {
        $market = strtoupper(trim($market ?? ''));
        return $market === 'US' ? 'v2' : 'v1';
    }

    /**
     * Tạo bản ghi lịch sử upload
     */
    private function createUploadHistory(Product $product, TikTokShop $shop, ?int $userId = null): TikTokProductUploadHistory
    {
        $userName = null;
        if ($userId) {
            $user = \App\Models\User::find($userId);
            $userName = $user ? $user->name : "User ID: {$userId}";
        }

        return TikTokProductUploadHistory::create([
            'user_id' => $userId,
            'user_name' => $userName,
            'product_id' => $product->id,
            'product_name' => $product->title,
            'tiktok_shop_id' => $shop->id,
            'shop_name' => $shop->shop_name,
            'shop_cipher' => $shop->getShopCipher(),
            'status' => 'pending',
        ]);
    }

    /**
     * Cập nhật lịch sử upload thành công
     */
    private function updateUploadHistorySuccess(TikTokProductUploadHistory $uploadHistory, array $responseData): void
    {
        $uploadHistory->update([
            'status' => 'success',
            'tiktok_product_id' => $responseData['product_id'] ?? null,
            'tiktok_skus' => $responseData['skus'] ?? null,
            'response_data' => $responseData,
            'uploaded_at' => now(),
        ]);
    }

    /**
     * Cập nhật lịch sử upload thất bại
     */
    private function updateUploadHistoryFailed(TikTokProductUploadHistory $uploadHistory, string $errorMessage, ?array $responseData = null): void
    {
        $uploadHistory->update([
            'status' => 'failed',
            'error_message' => $errorMessage,
            'response_data' => $responseData,
            'uploaded_at' => now(),
        ]);
    }

    /**
     * Đảm bảo size chart đã có TikTok URI (upload nếu chưa có)
     */
    private function ensureSizeChartHasTikTokUri(string $sizeChartUrl, TikTokShop $shop): ?string
    {
        try {
            // Size chart là image, upload như image bình thường
            $imageUploadService = new \App\Services\TikTokImageUploadService($shop->integration);
            
            // Upload size chart image
            $result = $this->uploadImageToTikTok($imageUploadService, $sizeChartUrl, 'MAIN_IMAGE');
            
            if ($result['success'] && isset($result['data']['uri'])) {
                Log::info('Size chart uploaded to TikTok successfully', [
                    'size_chart_url' => $sizeChartUrl,
                    'tiktok_uri' => $result['data']['uri']
                ]);
                return $result['data']['uri'];
            } else {
                Log::warning('Failed to upload size chart to TikTok', [
                    'size_chart_url' => $sizeChartUrl,
                    'error' => $result['message'] ?? 'Unknown error'
                ]);
                return null;
            }
        } catch (\Exception $e) {
            Log::error('Error uploading size chart to TikTok', [
                'size_chart_url' => $sizeChartUrl,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Đảm bảo video đã có TikTok URI (upload nếu chưa có)
     */
    private function ensureVideoHasTikTokUri(string $videoUrl, TikTokShop $shop): ?string
    {
        try {
            // Tạo Product tạm thời để sử dụng uploadProductVideo
            $tempProduct = new \App\Models\Product();
            $tempProduct->id = 0;
            
            $imageUploadService = new \App\Services\TikTokImageUploadService($shop->integration);
            
            // Upload video
            $result = $imageUploadService->uploadProductVideo($tempProduct, $videoUrl);
            
            if ($result['success'] && isset($result['data']['uri'])) {
                Log::info('Product video uploaded to TikTok successfully', [
                    'video_url' => $videoUrl,
                    'tiktok_uri' => $result['data']['uri']
                ]);
                return $result['data']['uri'];
            } else {
                Log::warning('Failed to upload product video to TikTok', [
                    'video_url' => $videoUrl,
                    'error' => $result['message'] ?? 'Unknown error'
                ]);
                return null;
            }
        } catch (\Exception $e) {
            Log::error('Error uploading product video to TikTok', [
                'video_url' => $videoUrl,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }
}
