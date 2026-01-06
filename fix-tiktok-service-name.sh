#!/bin/bash

# Script để fix tên file TikTokShopProductService trên production
# Chạy script này trên EC2 server

echo "🔧 Fixing TikTokShopProductService file name..."

PROJECT_PATH="/var/www/hmtiktool"
SERVICES_PATH="$PROJECT_PATH/app/Services"

cd "$PROJECT_PATH" || exit 1

# Kiểm tra file cũ (tên sai)
if [ -f "$SERVICES_PATH/TiktokShopProductService.php" ]; then
    echo "⚠️  Tìm thấy file cũ: TiktokShopProductService.php"
    
    # Kiểm tra file mới đã tồn tại chưa
    if [ -f "$SERVICES_PATH/TikTokShopProductService.php" ]; then
        echo "✅ File mới đã tồn tại: TikTokShopProductService.php"
        echo "🗑️  Xóa file cũ..."
        rm -f "$SERVICES_PATH/TiktokShopProductService.php"
        echo "✅ Đã xóa file cũ"
    else
        echo "📝 Đổi tên file cũ thành tên đúng..."
        mv "$SERVICES_PATH/TiktokShopProductService.php" "$SERVICES_PATH/TikTokShopProductService.php"
        echo "✅ Đã đổi tên file"
    fi
else
    echo "ℹ️  Không tìm thấy file cũ"
fi

# Kiểm tra file mới
if [ -f "$SERVICES_PATH/TikTokShopProductService.php" ]; then
    echo "✅ File mới tồn tại: TikTokShopProductService.php"
    
    # Kiểm tra class name trong file
    if grep -q "class TikTokShopProductService" "$SERVICES_PATH/TikTokShopProductService.php"; then
        echo "✅ Class name đúng: TikTokShopProductService"
    else
        echo "❌ Class name không đúng trong file!"
        exit 1
    fi
else
    echo "❌ File TikTokShopProductService.php không tồn tại!"
    exit 1
fi

# Clear Laravel cache
echo "🧹 Clearing Laravel cache..."
php artisan clear-compiled
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Regenerate autoload
echo "🔄 Regenerating autoload..."
composer dump-autoload

echo ""
echo "✅ Hoàn thành! Vui lòng test lại."
echo ""
echo "📝 Để kiểm tra:"
echo "   php artisan tinker"
echo "   use App\Services\TikTokShopProductService;"
echo "   new TikTokShopProductService();"
