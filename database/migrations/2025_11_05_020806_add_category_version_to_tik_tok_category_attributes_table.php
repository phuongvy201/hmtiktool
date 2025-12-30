<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Kiểm tra xem cột category_version đã tồn tại chưa
        if (!Schema::hasColumn('tik_tok_category_attributes', 'category_version')) {
            Schema::table('tik_tok_category_attributes', function (Blueprint $table) {
                $table->string('category_version')->nullable()->after('category_id');
            });
        }

        // Kiểm tra và drop index cũ nếu tồn tại
        $oldIndexes = DB::select("SHOW INDEXES FROM `tik_tok_category_attributes` WHERE Key_name = 'tik_tok_category_attributes_category_id_attribute_id_index'");
        if (!empty($oldIndexes)) {
            DB::statement('ALTER TABLE `tik_tok_category_attributes` DROP INDEX `tik_tok_category_attributes_category_id_attribute_id_index`');
        }

        // Kiểm tra xem index mới đã tồn tại chưa trước khi tạo
        $newIndexes = DB::select("SHOW INDEXES FROM `tik_tok_category_attributes` WHERE Key_name = 'tiktok_cat_attr_composite_idx'");
        if (empty($newIndexes)) {
            Schema::table('tik_tok_category_attributes', function (Blueprint $table) {
                $table->index(['category_id', 'category_version', 'attribute_id'], 'tiktok_cat_attr_composite_idx');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tik_tok_category_attributes', function (Blueprint $table) {
            // Khôi phục index cũ
            $table->dropIndex('tiktok_cat_attr_composite_idx');
            $table->index(['category_id', 'attribute_id']);

            $table->dropColumn('category_version');
        });
    }
};
