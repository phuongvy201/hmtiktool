# TikTok Orders API Integration

Tài liệu hướng dẫn sử dụng API Get Orders từ TikTok Shop để lấy và lưu trữ dữ liệu đơn hàng.

## 📋 Tổng quan

Hệ thống này cung cấp các service và model để:

-   Gọi API Get Orders từ TikTok Shop
-   Lưu trữ dữ liệu đơn hàng vào database
-   Tìm kiếm và lọc đơn hàng theo nhiều tiêu chí
-   Đồng bộ đơn hàng tự động

## 🗂️ Cấu trúc Files

### Models

-   `app/Models/TikTokOrder.php` - Model để lưu trữ dữ liệu đơn hàng

### Services

-   `app/Services/TikTokOrderService.php` - Service chính để làm việc với đơn hàng
-   `app/Services/TikTokShopService.php` - Service tổng quát (đã có method searchOrders)
-   `app/Services/TikTokSignatureService.php` - Service tạo signature (đã có method generateOrderSearchSignature)

### Database

-   `database/migrations/2025_09_15_081938_create_tiktok_orders_table.php` - Migration tạo bảng tiktok_orders

### Test Files

-   `test_tiktok_orders_api.php` - File test và demo cách sử dụng

## 🚀 Cài đặt

### 1. Chạy Migration

```bash
php artisan migrate
```

### 2. Kiểm tra cấu hình

Đảm bảo bạn đã có:

-   TikTokShop model với integration
-   TikTokShopIntegration với access token hợp lệ
-   App key và app secret được cấu hình đúng

## 📖 Cách sử dụng

### 1. Sử dụng TikTokOrderService (Khuyến nghị)

```php
use App\Services\TikTokOrderService;
use App\Models\TikTokShop;

$shop = TikTokShop::with('integration')->find(1);
$orderService = new TikTokOrderService();

// Tìm kiếm đơn hàng cơ bản
$result = $orderService->searchOrders($shop, [
    'order_status' => 'UNPAID',
    'create_time_ge' => strtotime('-7 days'),
    'create_time_lt' => time()
], 20);

if ($result['success']) {
    $orders = $result['data']['order_list'];
    // Xử lý dữ liệu đơn hàng
}
```

### 2. Tìm kiếm theo trạng thái

```php
// Lấy đơn hàng chưa thanh toán
$result = $orderService->getOrdersByStatus($shop, 'UNPAID', 50);

// Lấy đơn hàng đang vận chuyển
$result = $orderService->getOrdersByStatus($shop, 'IN_TRANSIT', 50);
```

### 3. Tìm kiếm theo khoảng thời gian

```php
// Lấy đơn hàng trong 30 ngày qua
$startTime = strtotime('-30 days');
$endTime = time();

$result = $orderService->getOrdersByTimeRange($shop, $startTime, $endTime, 100);
```

### 4. Đồng bộ tất cả đơn hàng

```php
// Đồng bộ đơn hàng trong 7 ngày qua
$filters = [
    'create_time_ge' => strtotime('-7 days'),
    'create_time_lt' => time()
];

$result = $orderService->syncAllOrders($shop, $filters);

if ($result['success']) {
    echo "Đồng bộ thành công: " . $result['total_orders'] . " đơn hàng";
}
```

### 5. Lấy đơn hàng từ database

```php
// Lấy đơn hàng đã lưu trong database
$result = $orderService->getStoredOrders($shop, [
    'order_status' => 'DELIVERED',
    'limit' => 50
]);

if ($result['success']) {
    $orders = $result['data'];
    foreach ($orders as $order) {
        echo "Order ID: " . $order->order_id;
        echo "Status: " . $order->status_in_vietnamese;
        echo "Amount: " . $order->order_amount . " " . $order->currency;
    }
}
```

### 6. Sử dụng TikTokShopService trực tiếp

```php
use App\Services\TikTokShopService;

$shopService = new TikTokShopService();
$result = $shopService->searchOrders(
    $shop->integration,
    $shop->id,
    ['order_status' => 'UNPAID'],
    20
);
```

## 🔍 Filters hỗ trợ

### Body Parameters (JSON)

```php
$filters = [
    // Trạng thái đơn hàng
    'order_status' => 'UNPAID|ON_HOLD|AWAITING_SHIPMENT|PARTIALLY_SHIPPING|AWAITING_COLLECTION|IN_TRANSIT|DELIVERED|COMPLETED|CANCELLED',

    // Thời gian tạo (Unix timestamp)
    'create_time_ge' => 1623812664, // Từ thời điểm này
    'create_time_lt' => 1623812664, // Đến thời điểm này

    // Thời gian cập nhật (Unix timestamp)
    'update_time_ge' => 1623812664, // Từ thời điểm này
    'update_time_lt' => 1623812664, // Đến thời điểm này

    // Phương thức vận chuyển
    'shipping_type' => 'TIKTOK|SELLER',

    // ID người mua
    'buyer_user_id' => '7213489962827123654',

    // Người mua có yêu cầu hủy không
    'is_buyer_request_cancel' => false,

    // Danh sách kho
    'warehouse_ids' => [
        '7000714532876273888',
        '7000714532876273666'
    ]
];
```

### Query Parameters

```php
$queryParams = [
    'page_size' => 20,        // 1-100, mặc định 20
    'sort_order' => 'DESC',   // ASC|DESC, mặc định DESC
    'sort_field' => 'create_time', // create_time|update_time, mặc định create_time
    'page_token' => 'string'  // Token phân trang, không cần ở lần gọi đầu
];
```

## 📊 Model TikTokOrder

### Các trường chính

```php
// Thông tin cơ bản
'order_id' => 'string',           // ID đơn hàng từ TikTok
'order_number' => 'string',       // Số đơn hàng
'order_status' => 'string',       // Trạng thái đơn hàng
'buyer_user_id' => 'string',      // ID người mua
'buyer_username' => 'string',     // Tên người mua

// Thông tin vận chuyển
'shipping_type' => 'string',      // Phương thức vận chuyển
'is_buyer_request_cancel' => 'boolean', // Yêu cầu hủy
'warehouse_id' => 'string',       // ID kho
'warehouse_name' => 'string',     // Tên kho

// Thời gian
'create_time' => 'datetime',      // Thời gian tạo
'update_time' => 'datetime',      // Thời gian cập nhật

// Tài chính
'order_amount' => 'decimal',      // Giá trị đơn hàng
'currency' => 'string',           // Đơn vị tiền tệ
'shipping_fee' => 'decimal',      // Phí vận chuyển
'total_amount' => 'decimal',      // Tổng tiền

// Dữ liệu JSON
'order_data' => 'array',          // Dữ liệu chi tiết đơn hàng
'raw_response' => 'array',        // Response gốc từ API

// Trạng thái đồng bộ
'sync_status' => 'string',        // pending|synced|error
'sync_error' => 'text',           // Lỗi đồng bộ
'last_synced_at' => 'datetime'    // Thời gian đồng bộ cuối
```

### Các method hữu ích

```php
// Lấy trạng thái bằng tiếng Việt
$order->status_in_vietnamese; // "Chưa thanh toán", "Đang vận chuyển", etc.

// Lấy phương thức vận chuyển bằng tiếng Việt
$order->shipping_type_in_vietnamese; // "TikTok Logistics", "Người bán tự vận chuyển"

// Kiểm tra yêu cầu hủy
$order->hasBuyerCancelRequest(); // true/false

// Lấy thông tin chi tiết
$order->getOrderDetails();        // Dữ liệu chi tiết đơn hàng
$order->getOrderItems();          // Danh sách sản phẩm
$order->getShippingAddress();     // Địa chỉ giao hàng
$order->getBuyerInfo();           // Thông tin người mua

// Cập nhật trạng thái đồng bộ
$order->markAsSynced();           // Đánh dấu đã đồng bộ
$order->markSyncError('Lỗi');     // Đánh dấu lỗi đồng bộ
```

### Scopes hữu ích

```php
// Lọc theo trạng thái
TikTokOrder::byStatus('UNPAID')->get();

// Lọc theo shop
TikTokOrder::byShop(1)->get();

// Lọc theo khoảng thời gian tạo
TikTokOrder::byCreateTimeRange($start, $end)->get();

// Lọc theo khoảng thời gian cập nhật
TikTokOrder::byUpdateTimeRange($start, $end)->get();

// Lọc đơn hàng chưa đồng bộ
TikTokOrder::notSynced()->get();

// Lọc đơn hàng đã đồng bộ
TikTokOrder::synced()->get();
```

## 🧪 Test và Demo

Chạy file test để kiểm tra tất cả chức năng:

```bash
php test_tiktok_orders_api.php
```

File test sẽ thực hiện:

1. Tìm kiếm đơn hàng cơ bản
2. Tìm kiếm theo trạng thái
3. Tìm kiếm theo khoảng thời gian
4. Đồng bộ đơn hàng
5. Lấy đơn hàng từ database
6. Thống kê đơn hàng
7. Test TikTokShopService

## ⚠️ Lưu ý quan trọng

### Rate Limiting

-   TikTok API có giới hạn số request/phút
-   Sử dụng `sleep(1)` giữa các request khi đồng bộ nhiều đơn hàng
-   Không nên đồng bộ quá nhiều đơn hàng cùng lúc

### Token Management

-   Access token có thể hết hạn
-   Service tự động refresh token khi cần
-   Kiểm tra `isActive()` và `isAccessTokenExpired()` trước khi gọi API

### Error Handling

-   Tất cả method đều trả về array với `success` và `message`
-   Log chi tiết được ghi vào Laravel log
-   Xử lý exception và trả về thông báo lỗi rõ ràng

### Database Performance

-   Bảng `tiktok_orders` có các index để tối ưu truy vấn
-   Sử dụng pagination khi lấy nhiều đơn hàng
-   Cân nhắc xóa dữ liệu cũ để tránh bảng quá lớn

## 🔧 Troubleshooting

### Lỗi thường gặp

1. **"TikTok Shop integration không hoạt động"**

    - Kiểm tra integration có active không
    - Kiểm tra access token có hợp lệ không

2. **"Không thể refresh token"**

    - Kiểm tra app key và app secret
    - Kiểm tra refresh token có hợp lệ không

3. **"Shop không tồn tại"**

    - Kiểm tra shop_id có đúng không
    - Kiểm tra shop có integration không

4. **"API call failed"**
    - Kiểm tra signature generation
    - Kiểm tra request format
    - Xem log chi tiết để debug

### Debug

Bật log debug để xem chi tiết:

```php
// Trong config/logging.php
'level' => 'debug'
```

Hoặc xem log file:

```bash
tail -f storage/logs/laravel.log
```

## 📚 API Reference

### TikTokOrderService Methods

-   `searchOrders($shop, $filters, $pageSize, $sortOrder, $sortField, $pageToken)`
-   `getOrdersByStatus($shop, $status, $limit)`
-   `getOrdersByTimeRange($shop, $startTime, $endTime, $limit)`
-   `syncAllOrders($shop, $filters)`
-   `getStoredOrders($shop, $filters)`

### TikTokShopService Methods

-   `searchOrders($integration, $shopId, $filters, $pageSize, $sortOrder, $sortField, $pageToken)`

### TikTokSignatureService Methods

-   `generateOrderSearchSignature($appKey, $appSecret, $timestamp, $bodyParams, $shopCipher)`

## 🎯 Ví dụ sử dụng thực tế

### 1. Dashboard thống kê đơn hàng

```php
// Lấy thống kê đơn hàng theo trạng thái
$stats = TikTokOrder::where('tiktok_shop_id', $shopId)
    ->selectRaw('order_status, COUNT(*) as count, SUM(order_amount) as total_amount')
    ->groupBy('order_status')
    ->get();

// Lấy đơn hàng mới nhất
$recentOrders = TikTokOrder::where('tiktok_shop_id', $shopId)
    ->orderBy('create_time', 'DESC')
    ->limit(10)
    ->get();
```

### 2. Đồng bộ đơn hàng định kỳ

```php
// Tạo command để chạy cron job
// app/Console/Commands/SyncTikTokOrders.php

public function handle()
{
    $shops = TikTokShop::with('integration')->get();

    foreach ($shops as $shop) {
        if ($shop->integration && $shop->integration->isActive()) {
            $orderService = new TikTokOrderService();

            // Đồng bộ đơn hàng trong 24h qua
            $filters = [
                'create_time_ge' => strtotime('-24 hours'),
                'create_time_lt' => time()
            ];

            $result = $orderService->syncAllOrders($shop, $filters);

            if ($result['success']) {
                $this->info("Shop {$shop->shop_name}: Đồng bộ {$result['total_orders']} đơn hàng");
            } else {
                $this->error("Shop {$shop->shop_name}: {$result['message']}");
            }
        }
    }
}
```

### 3. Webhook xử lý đơn hàng

```php
// Khi nhận webhook từ TikTok về thay đổi đơn hàng
public function handleOrderWebhook(Request $request)
{
    $orderId = $request->input('order_id');
    $shopId = $request->input('shop_id');

    $shop = TikTokShop::find($shopId);
    if (!$shop) {
        return response()->json(['error' => 'Shop not found'], 404);
    }

    $orderService = new TikTokOrderService();

    // Lấy thông tin đơn hàng mới nhất
    $result = $orderService->searchOrders($shop, [
        'order_id' => $orderId
    ], 1);

    if ($result['success']) {
        // Đơn hàng đã được cập nhật trong database
        $this->info("Order {$orderId} updated successfully");
    }
}
```

---

**Tác giả**: AI Assistant  
**Ngày tạo**: 2025-01-15  
**Phiên bản**: 1.0.0
