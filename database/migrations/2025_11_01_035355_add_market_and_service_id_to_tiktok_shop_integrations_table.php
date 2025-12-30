<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('tiktok_shop_integrations', function (Blueprint $table) {
            // Thêm cột market với kiểu VARCHAR(10), default 'UK'
            $table->string('market', 10)->default('UK')->after('team_id');

            // Thêm cột service_id nếu chưa có (nullable)
            if (!Schema::hasColumn('tiktok_shop_integrations', 'service_id')) {
                $table->unsignedBigInteger('service_id')->nullable()->after('market');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tiktok_shop_integrations', function (Blueprint $table) {
            // Xóa cột service_id nếu có
            if (Schema::hasColumn('tiktok_shop_integrations', 'service_id')) {
                $table->dropColumn('service_id');
            }

            // Xóa cột market
            $table->dropColumn('market');
        });
    }
};
