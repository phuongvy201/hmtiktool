#!/bin/bash

# Script để setup Laravel Scheduler trên AWS EC2
# Chạy script này với quyền root hoặc sudo

echo "🚀 Bắt đầu setup Laravel Scheduler trên EC2..."

# Lấy đường dẫn hiện tại của project
PROJECT_PATH=$(pwd)
if [ ! -f "$PROJECT_PATH/artisan" ]; then
    echo "❌ Không tìm thấy file artisan. Vui lòng chạy script này trong thư mục gốc của Laravel project."
    exit 1
fi

echo "📁 Project path: $PROJECT_PATH"

# Tạo log directory nếu chưa có
mkdir -p "$PROJECT_PATH/storage/logs"
chmod -R 775 "$PROJECT_PATH/storage/logs"

# Kiểm tra xem cron job đã tồn tại chưa
CRON_CMD="* * * * * cd $PROJECT_PATH && php artisan schedule:run >> /dev/null 2>&1"
CRON_EXISTS=$(crontab -l 2>/dev/null | grep -F "$CRON_CMD" | wc -l)

if [ "$CRON_EXISTS" -eq 0 ]; then
    echo "➕ Thêm Laravel Scheduler vào crontab..."
    
    # Lấy crontab hiện tại và thêm Laravel scheduler
    (crontab -l 2>/dev/null; echo "$CRON_CMD") | crontab -
    
    echo "✅ Đã thêm Laravel Scheduler vào crontab!"
else
    echo "ℹ️  Laravel Scheduler đã tồn tại trong crontab."
fi

# Hiển thị crontab hiện tại
echo ""
echo "📋 Crontab hiện tại:"
crontab -l

echo ""
echo "✅ Hoàn thành setup!"
echo ""
echo "📝 Các scheduled tasks đã được cấu hình:"
echo "   - tiktok:refresh-tokens: Hàng ngày lúc 2:00 AM"
echo "   - tiktok:sync-orders: Nhiều lần trong ngày"
echo "   - backup:database: Hàng ngày lúc 1:00 AM"
echo "   - tiktok:sync-finance: Hàng ngày lúc 3:00 AM"
echo ""
echo "📊 Để xem logs, kiểm tra:"
echo "   - storage/logs/tiktok-token-refresh.log"
echo "   - storage/logs/tiktok-orders-sync.log"
echo "   - storage/logs/tiktok-orders-sync-recent.log"
echo "   - storage/logs/tiktok-orders-awaiting-shipment.log"
echo "   - storage/logs/tiktok-orders-in-transit.log"
echo "   - storage/logs/tiktok-orders-full-sync.log"
echo "   - storage/logs/tiktok-sync-monitoring.log"
echo "   - storage/logs/tiktok-dispatch-jobs.log"
echo ""
echo "🔍 Để test scheduler, chạy:"
echo "   php artisan schedule:list"
echo "   php artisan schedule:run"
echo ""
