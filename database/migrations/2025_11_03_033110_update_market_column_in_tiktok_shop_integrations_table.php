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
        // Thay đổi kiểu dữ liệu từ varchar sang enum và đảm bảo chỉ có giá trị US hoặc UK
        DB::statement("ALTER TABLE `tiktok_shop_integrations` MODIFY COLUMN `market` ENUM('US', 'UK') NOT NULL DEFAULT 'US'");

        // Cập nhật các giá trị không hợp lệ thành US
        DB::statement("UPDATE `tiktok_shop_integrations` SET `market` = 'US' WHERE `market` NOT IN ('US', 'UK')");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Quay lại varchar nếu cần
        DB::statement("ALTER TABLE `tiktok_shop_integrations` MODIFY COLUMN `market` VARCHAR(10) NOT NULL DEFAULT 'UK'");
    }
};
