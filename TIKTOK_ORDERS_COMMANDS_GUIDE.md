# TikTok Orders Commands Guide

Hướng dẫn sử dụng các Artisan commands để test và quản lý đơn hàng TikTok Shop.

## 📋 Danh sách Commands

### 1. `tiktok:test-orders` - Test đầy đủ

Command chính để test tất cả chức năng của TikTok Orders API.

#### Cách sử dụng:

```bash
# Test cơ bản với shop đầu tiên
php artisan tiktok:test-orders

# Test với shop cụ thể
php artisan tiktok:test-orders --shop-id=1

# Test với trạng thái cụ thể
php artisan tiktok:test-orders --status=UNPAID

# Test với số ngày khác
php artisan tiktok:test-orders --days=30

# Test với giới hạn số lượng
php artisan tiktok:test-orders --limit=50

# Test và đồng bộ đơn hàng vào database
php artisan tiktok:test-orders --sync

# Hiển thị danh sách shops
php artisan tiktok:test-orders --list-shops

# Kết hợp nhiều options
php artisan tiktok:test-orders --shop-id=1 --status=DELIVERED --days=14 --limit=100 --sync
```

#### Options:

-   `--shop-id=` : ID của shop cần test
-   `--status=` : Trạng thái đơn hàng cần lọc (UNPAID, AWAITING_SHIPMENT, IN_TRANSIT, DELIVERED, etc.)
-   `--days=7` : Số ngày gần đây để lọc đơn hàng (mặc định 7 ngày)
-   `--limit=20` : Số lượng đơn hàng tối đa (mặc định 20)
-   `--sync` : Đồng bộ đơn hàng vào database
-   `--list-shops` : Hiển thị danh sách shops

#### Các test được thực hiện:

1. **Test 1**: Tìm kiếm đơn hàng từ API
2. **Test 2**: Đồng bộ đơn hàng vào database (nếu có --sync)
3. **Test 3**: Lấy đơn hàng từ database
4. **Test 4**: Thống kê đơn hàng
5. **Test 5**: Sử dụng TikTokShopService

### 2. `tiktok:quick-test` - Test nhanh

Command đơn giản để test nhanh API lấy đơn hàng.

#### Cách sử dụng:

```bash
# Test nhanh với shop đầu tiên
php artisan tiktok:quick-test

# Test nhanh với shop cụ thể
php artisan tiktok:quick-test --shop-id=1

# Test nhanh với trạng thái khác
php artisan tiktok:quick-test --status=DELIVERED

# Test nhanh với shop và trạng thái cụ thể
php artisan tiktok:quick-test --shop-id=1 --status=IN_TRANSIT
```

#### Options:

-   `--shop-id=` : ID của shop cần test
-   `--status=UNPAID` : Trạng thái đơn hàng (mặc định UNPAID)

## 🚀 Ví dụ sử dụng thực tế

### 1. Kiểm tra shops có sẵn

```bash
php artisan tiktok:test-orders --list-shops
```

Output:

```
📋 Danh sách TikTok Shops:
┌────┬─────────────────┬─────────────────┬─────────────┬─────────┐
│ ID │ Shop Name       │ Shop ID         │ Integration │ Status  │
├────┼─────────────────┼─────────────────┼─────────────┼─────────┤
│ 1  │ My TikTok Shop  │ 123456789       │ My App      │ ✅ Active│
│ 2  │ Test Shop       │ 987654321       │ Test App    │ ❌ Inactive│
└────┴─────────────────┴─────────────────┴─────────────┴─────────┘
```

### 2. Test cơ bản

```bash
php artisan tiktok:test-orders
```

Output:

```
=== TIKTOK ORDERS API TEST ===

✅ Shop được chọn:
   - ID: 1
   - Tên: My TikTok Shop
   - Shop ID: 123456789
   - Integration: My App
   - Status: Active

🔍 Bắt đầu test với filters:
   - order_status: UNPAID
   - create_time_ge: 2025-01-08 08:00:00
   - create_time_lt: 2025-01-15 08:00:00

🔍 Test 1: Tìm kiếm đơn hàng từ API
----------------------------------------
✅ Tìm thấy 5 đơn hàng

📋 Danh sách đơn hàng:
┌─────┬─────────────────────┬─────────┬────────┬──────────┬─────────┬─────────────┐
│ STT │ Order ID            │ Status  │ Amount │ Currency │ Buyer   │ Created     │
├─────┼─────────────────────┼─────────┼────────┼──────────┼─────────┼─────────────┤
│ 1   │ 1234567890123456... │ UNPAID  │ 25.99  │ GBP      │ buyer1  │ 2025-01-14  │
│ 2   │ 1234567890123457... │ UNPAID  │ 15.50  │ GBP      │ buyer2  │ 2025-01-13  │
└─────┴─────────────────────┴─────────┴────────┴──────────┴─────────┴─────────────┘
```

### 3. Test với đồng bộ

```bash
php artisan tiktok:test-orders --sync --days=30
```

Output:

```
🔄 Test 2: Đồng bộ đơn hàng vào database
----------------------------------------
⚠️  Bắt đầu đồng bộ đơn hàng (có thể mất vài phút)...
✅ Đồng bộ thành công: 150 đơn hàng

💾 Test 3: Lấy đơn hàng từ database
----------------------------------------
✅ Tìm thấy 150 đơn hàng trong database

📊 Test 4: Thống kê đơn hàng
----------------------------------------
✅ Tổng số đơn hàng: 150

📊 Phân bố theo trạng thái:
┌─────────────┬───────┬─────────────┐
│ Status      │ Count │ Total Amount│
├─────────────┼───────┼─────────────┤
│ UNPAID      │ 25    │ 1,250.00 GBP│
│ DELIVERED   │ 100   │ 5,000.00 GBP│
│ IN_TRANSIT  │ 25    │ 1,250.00 GBP│
└─────────────┴───────┴─────────────┘
```

### 4. Test nhanh

```bash
php artisan tiktok:quick-test --status=DELIVERED
```

Output:

```
🚀 QUICK TEST TIKTOK ORDERS API

✅ Testing shop: My TikTok Shop (ID: 1)

🔍 Tìm kiếm đơn hàng với status: DELIVERED
✅ Tìm thấy 3 đơn hàng

1. Order ID: 1234567890123456... | Status: DELIVERED | Amount: 25.99 GBP | Buyer: buyer1
2. Order ID: 1234567890123457... | Status: DELIVERED | Amount: 15.50 GBP | Buyer: buyer2
3. Order ID: 1234567890123458... | Status: DELIVERED | Amount: 45.00 GBP | Buyer: buyer3

🎉 Test hoàn thành!
```

## 🔧 Troubleshooting

### Lỗi thường gặp

#### 1. "Không tìm thấy TikTok Shop nào trong database"

```bash
# Kiểm tra danh sách shops
php artisan tiktok:test-orders --list-shops

# Nếu không có shop, cần tạo shop trước
# Hoặc kiểm tra database có dữ liệu không
```

#### 2. "Shop không có integration"

```bash
# Cần tạo TikTokShopIntegration cho shop
# Hoặc kiểm tra relationship trong database
```

#### 3. "Integration không hoạt động hoặc token đã hết hạn"

```bash
# Cần refresh access token
# Hoặc tạo lại integration
```

#### 4. "API call failed"

```bash
# Kiểm tra log để xem chi tiết lỗi
tail -f storage/logs/laravel.log

# Có thể do:
# - App key/secret không đúng
# - Signature generation lỗi
# - Network issues
# - TikTok API rate limit
```

### Debug Commands

```bash
# Xem log chi tiết
tail -f storage/logs/laravel.log

# Test với log level debug
# Trong .env: LOG_LEVEL=debug

# Kiểm tra database
php artisan tinker
>>> App\Models\TikTokShop::count()
>>> App\Models\TikTokShopIntegration::count()
>>> App\Models\TikTokOrder::count()
```

## 📊 Các trạng thái đơn hàng

| Trạng thái          | Mô tả               | Tiếng Việt          |
| ------------------- | ------------------- | ------------------- |
| UNPAID              | Chưa thanh toán     | Chưa thanh toán     |
| ON_HOLD             | Tạm giữ             | Tạm giữ             |
| AWAITING_SHIPMENT   | Chờ vận chuyển      | Chờ vận chuyển      |
| PARTIALLY_SHIPPING  | Vận chuyển một phần | Vận chuyển một phần |
| AWAITING_COLLECTION | Chờ thu thập        | Chờ thu thập        |
| IN_TRANSIT          | Đang vận chuyển     | Đang vận chuyển     |
| DELIVERED           | Đã giao hàng        | Đã giao hàng        |
| COMPLETED           | Hoàn thành          | Hoàn thành          |
| CANCELLED           | Đã hủy              | Đã hủy              |

## 🎯 Use Cases thực tế

### 1. Kiểm tra đơn hàng chưa thanh toán hàng ngày

```bash
# Tạo cron job chạy hàng ngày
# 0 9 * * * cd /path/to/project && php artisan tiktok:quick-test --status=UNPAID
```

### 2. Đồng bộ đơn hàng tuần

```bash
# Chạy mỗi tuần để đồng bộ đơn hàng
php artisan tiktok:test-orders --sync --days=7
```

### 3. Kiểm tra đơn hàng đã giao

```bash
# Kiểm tra đơn hàng đã giao trong tháng
php artisan tiktok:test-orders --status=DELIVERED --days=30
```

### 4. Thống kê đơn hàng

```bash
# Lấy thống kê đầy đủ
php artisan tiktok:test-orders --days=90
```

## 🔄 Tự động hóa

### Tạo Cron Job

```bash
# Mở crontab
crontab -e

# Thêm các job sau:

# Kiểm tra đơn hàng chưa thanh toán mỗi 2 giờ
0 */2 * * * cd /path/to/project && php artisan tiktok:quick-test --status=UNPAID

# Đồng bộ đơn hàng mỗi ngày lúc 2h sáng
0 2 * * * cd /path/to/project && php artisan tiktok:test-orders --sync --days=1

# Thống kê đơn hàng mỗi tuần
0 3 * * 1 cd /path/to/project && php artisan tiktok:test-orders --days=7
```

### Tạo Custom Command

```php
// app/Console/Commands/DailyOrderCheck.php
class DailyOrderCheck extends Command
{
    protected $signature = 'tiktok:daily-check';

    public function handle()
    {
        // Kiểm tra đơn hàng chưa thanh toán
        $this->call('tiktok:quick-test', ['--status' => 'UNPAID']);

        // Gửi email báo cáo nếu cần
        // ...
    }
}
```

## 📝 Logs và Monitoring

### Xem logs

```bash
# Xem log real-time
tail -f storage/logs/laravel.log

# Xem log của ngày hôm nay
grep "$(date '+%Y-%m-%d')" storage/logs/laravel.log

# Xem log lỗi
grep "ERROR" storage/logs/laravel.log
```

### Monitoring Commands

```bash
# Kiểm tra trạng thái hệ thống
php artisan tiktok:test-orders --list-shops

# Test kết nối API
php artisan tiktok:quick-test

# Kiểm tra database
php artisan tinker
>>> App\Models\TikTokOrder::where('sync_status', 'error')->count()
```

---

**Tác giả**: AI Assistant  
**Ngày tạo**: 2025-01-15  
**Phiên bản**: 1.0.0
