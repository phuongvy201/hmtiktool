<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('tik_tok_category_attributes', 'market')) {
            Schema::table('tik_tok_category_attributes', function (Blueprint $table) {
                $table->string('market', 10)->default('UK')->after('category_version');
            });
        }

        // Đồng bộ dữ liệu market và category_version từ bảng categories (nếu có)
        DB::statement(
            "UPDATE `tik_tok_category_attributes` attr " .
                "JOIN `tiktok_shop_categories` cat ON cat.category_id = attr.category_id " .
                "SET attr.market = UPPER(cat.market), " .
                "    attr.category_version = COALESCE(attr.category_version, cat.category_version) " .
                "WHERE cat.market IS NOT NULL"
        );

        // Tạo index phục vụ truy vấn theo market + version + attribute
        $existingIndex = collect(DB::select("SHOW INDEXES FROM `tik_tok_category_attributes` WHERE Key_name = 'tiktok_cat_attr_market_idx'"));
        if ($existingIndex->isEmpty()) {
            Schema::table('tik_tok_category_attributes', function (Blueprint $table) {
                $table->index([
                    'category_id',
                    'category_version',
                    'market',
                    'attribute_id'
                ], 'tiktok_cat_attr_market_idx');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('tik_tok_category_attributes', 'market')) {
            $hasIndex = collect(DB::select("SHOW INDEXES FROM `tik_tok_category_attributes` WHERE Key_name = 'tiktok_cat_attr_market_idx'"))->isNotEmpty();

            Schema::table('tik_tok_category_attributes', function (Blueprint $table) use ($hasIndex) {
                if ($hasIndex) {
                    $table->dropIndex('tiktok_cat_attr_market_idx');
                }

                $table->dropColumn('market');
            });
        }
    }
};
