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
        // Trước tiên, cập nhật dữ liệu hiện tại để đảm bảo JSON hợp lệ
        DB::table('prod_template_category_attributes')
            ->whereNotNull('value')
            ->where('value', '!=', '')
            ->update([
                'value' => DB::raw('CONCAT(\'"\', value, \'"\')')
            ]);

        DB::table('prod_template_category_attributes')
            ->whereNotNull('value_id')
            ->where('value_id', '!=', '')
            ->update([
                'value_id' => DB::raw('CONCAT(\'"\', value_id, \'"\')')
            ]);

        DB::table('prod_template_category_attributes')
            ->whereNotNull('value_name')
            ->where('value_name', '!=', '')
            ->update([
                'value_name' => DB::raw('CONCAT(\'"\', value_name, \'"\')')
            ]);

        Schema::table('prod_template_category_attributes', function (Blueprint $table) {
            // Thay đổi các field từ string thành json để hỗ trợ array values
            $table->json('value')->nullable()->change();
            $table->json('value_id')->nullable()->change();
            $table->json('value_name')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('prod_template_category_attributes', function (Blueprint $table) {
            // Revert về string
            $table->string('value')->nullable()->change();
            $table->string('value_id')->nullable()->change();
            $table->string('value_name')->nullable()->change();
        });
    }
};
