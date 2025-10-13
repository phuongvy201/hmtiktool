# Demo TikTok Orders Commands

Hướng dẫn demo các commands để test TikTok Orders API.

## 🚀 Cách sử dụng

### 1. Kiểm tra danh sách shops

```bash
php artisan tiktok:test-orders --list-shops
```

**Kết quả mong đợi:**

```
📋 Danh sách TikTok Shops:
┌────┬─────────────────┬─────────────────┬─────────────┬─────────┐
│ ID │ Shop Name       │ Shop ID         │ Integration │ Status  │
├────┼─────────────────┼─────────────────┼─────────────┼─────────┤
│ 1  │ My TikTok Shop  │ 123456789       │ My App      │ ✅ Active│
└────┴─────────────────┴─────────────────┴─────────────┴─────────┘
```

### 2. Test nhanh (Khuyến nghị bắt đầu)

```bash
php artisan tiktok:quick-test
```

**Kết quả mong đợi:**

```
🚀 QUICK TEST TIKTOK ORDERS API

✅ Testing shop: My TikTok Shop (ID: 1)

🔍 Tìm kiếm đơn hàng với status: UNPAID
✅ Tìm thấy 5 đơn hàng

1. Order ID: 1234567890123456... | Status: UNPAID | Amount: 25.99 GBP | Buyer: buyer1
2. Order ID: 1234567890123457... | Status: UNPAID | Amount: 15.50 GBP | Buyer: buyer2

🎉 Test hoàn thành!
```

### 3. Test đầy đủ

```bash
php artisan tiktok:test-orders
```

**Kết quả mong đợi:**

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

💾 Test 3: Lấy đơn hàng từ database
----------------------------------------
✅ Tìm thấy 0 đơn hàng trong database

📊 Test 4: Thống kê đơn hàng
----------------------------------------
✅ Tổng số đơn hàng: 0

🏪 Test 5: Sử dụng TikTokShopService
----------------------------------------
✅ TikTokShopService: Tìm thấy 5 đơn hàng

🎉 Hoàn thành tất cả test!
========================
```

### 4. Test với đồng bộ

```bash
php artisan tiktok:test-orders --sync
```

**Kết quả mong đợi:**

```
🔄 Test 2: Đồng bộ đơn hàng vào database
----------------------------------------
⚠️  Bắt đầu đồng bộ đơn hàng (có thể mất vài phút)...
✅ Đồng bộ thành công: 5 đơn hàng

💾 Test 3: Lấy đơn hàng từ database
----------------------------------------
✅ Tìm thấy 5 đơn hàng trong database

📋 Đơn hàng đã lưu:
┌─────┬─────────────────────┬─────────────┬────────┬──────────┬─────────────┬─────────────┐
│ STT │ Order ID            │ Status (VN) │ Amount │ Currency │ Created     │ Synced      │
├─────┼─────────────────────┼─────────────┼────────┼──────────┼─────────────┼─────────────┤
│ 1   │ 1234567890123456... │ Chưa thanh toán │ 25.99  │ GBP      │ 2025-01-14  │ 2025-01-15  │
│ 2   │ 1234567890123457... │ Chưa thanh toán │ 15.50  │ GBP      │ 2025-01-13  │ 2025-01-15  │
└─────┴─────────────────────┴─────────────┴────────┴──────────┴─────────────┴─────────────┘
```

## 🔧 Các options khác

### Test với shop cụ thể

```bash
php artisan tiktok:test-orders --shop-id=1
php artisan tiktok:quick-test --shop-id=1
```

### Test với trạng thái khác

```bash
php artisan tiktok:test-orders --status=DELIVERED
php artisan tiktok:quick-test --status=IN_TRANSIT
```

### Test với số ngày khác

```bash
php artisan tiktok:test-orders --days=30
php artisan tiktok:test-orders --days=1
```

### Test với giới hạn số lượng

```bash
php artisan tiktok:test-orders --limit=50
php artisan tiktok:test-orders --limit=5
```

### Kết hợp nhiều options

```bash
php artisan tiktok:test-orders --shop-id=1 --status=DELIVERED --days=14 --limit=100 --sync
```

## ⚠️ Lưu ý quan trọng

### 1. Cần có dữ liệu trước

-   Phải có ít nhất 1 TikTokShop trong database
-   Shop phải có TikTokShopIntegration
-   Integration phải có access token hợp lệ

### 2. Nếu gặp lỗi

```bash
# Kiểm tra shops
php artisan tiktok:test-orders --list-shops

# Nếu không có shop, cần tạo trước
# Hoặc kiểm tra database có dữ liệu không
```

### 3. Rate limiting

-   TikTok API có giới hạn request
-   Không nên chạy quá nhiều lần liên tiếp
-   Sử dụng `--sync` cẩn thận với số lượng lớn

## 🎯 Workflow thực tế

### 1. Lần đầu setup

```bash
# 1. Kiểm tra shops
php artisan tiktok:test-orders --list-shops

# 2. Test kết nối API
php artisan tiktok:quick-test

# 3. Nếu OK, đồng bộ một ít đơn hàng
php artisan tiktok:test-orders --sync --days=1 --limit=10
```

### 2. Kiểm tra hàng ngày

```bash
# Kiểm tra đơn hàng chưa thanh toán
php artisan tiktok:quick-test --status=UNPAID

# Kiểm tra đơn hàng đã giao
php artisan tiktok:quick-test --status=DELIVERED
```

### 3. Đồng bộ định kỳ

```bash
# Đồng bộ đơn hàng trong 7 ngày qua
php artisan tiktok:test-orders --sync --days=7

# Đồng bộ đơn hàng trong 30 ngày qua
php artisan tiktok:test-orders --sync --days=30
```

### 4. Thống kê

```bash
# Lấy thống kê đầy đủ
php artisan tiktok:test-orders --days=90
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

## 🔍 Debug

### Xem log chi tiết

```bash
# Xem log real-time
tail -f storage/logs/laravel.log

# Xem log lỗi
grep "ERROR" storage/logs/laravel.log
```

### Kiểm tra database

```bash
php artisan tinker

# Kiểm tra số lượng records
>>> App\Models\TikTokShop::count()
>>> App\Models\TikTokShopIntegration::count()
>>> App\Models\TikTokOrder::count()

# Kiểm tra shop cụ thể
>>> App\Models\TikTokShop::with('integration')->first()
```

## 🎉 Kết luận

Commands này cung cấp:

1. **Test nhanh**: `tiktok:quick-test` - Kiểm tra API nhanh chóng
2. **Test đầy đủ**: `tiktok:test-orders` - Test tất cả chức năng
3. **Quản lý shops**: `--list-shops` - Xem danh sách shops
4. **Đồng bộ dữ liệu**: `--sync` - Lưu đơn hàng vào database
5. **Linh hoạt**: Nhiều options để tùy chỉnh

Sử dụng commands này để:

-   Kiểm tra kết nối API
-   Test các chức năng
-   Đồng bộ dữ liệu đơn hàng
-   Thống kê và báo cáo
-   Debug và troubleshooting

---

**Tác giả**: AI Assistant  
**Ngày tạo**: 2025-01-15  
**Phiên bản**: 1.0.0
