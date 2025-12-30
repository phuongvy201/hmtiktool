<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('tiktok_shop_categories', function (Blueprint $table) {
            // Xóa unique constraint trên category_id (vì bây giờ category_id có thể trùng giữa các market)
            $table->dropUnique(['category_id']);

            // Thêm field market (US hoặc UK)
            $table->string('market', 10)->default('UK')->after('id');

            // Thêm field category_version (v1 hoặc v2)
            $table->string('category_version', 10)->default('v1')->after('market');

            // Tạo unique constraint mới trên (category_id, market) để đảm bảo không trùng
            $table->unique(['category_id', 'market'], 'tiktok_shop_categories_category_id_market_unique');

            // Thêm index cho market và category_version để query nhanh hơn
            $table->index('market');
            $table->index('category_version');
            $table->index(['market', 'category_version']);
        });

        // Update các categories hiện có với market và category_version mặc định
        // Giả sử categories hiện có là US v2 (vì trước đây có thể đã sync từ US)
        // Hoặc có thể set thành UK v1 tùy theo logic
        DB::statement("UPDATE tiktok_shop_categories SET market = 'US', category_version = 'v2' WHERE market IS NULL OR category_version IS NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tiktok_shop_categories', function (Blueprint $table) {
            // Xóa unique constraint mới
            $table->dropUnique('tiktok_shop_categories_category_id_market_unique');

            // Xóa các index
            $table->dropIndex(['market']);
            $table->dropIndex(['category_version']);
            $table->dropIndex(['market', 'category_version']);

            // Xóa các columns
            $table->dropColumn(['market', 'category_version']);

            // Khôi phục unique constraint trên category_id
            $table->unique('category_id');
        });
    }
};
