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
        Schema::create('tiktok_orders', function (Blueprint $table) {
            $table->id();

            // Thông tin shop
            $table->unsignedBigInteger('tiktok_shop_id');
            $table->foreign('tiktok_shop_id')->references('id')->on('tiktok_shops')->onDelete('cascade');

            // Thông tin đơn hàng từ TikTok
            $table->string('order_id')->unique()->comment('ID đơn hàng từ TikTok');
            $table->string('order_number')->nullable()->comment('Số đơn hàng');
            $table->string('order_status')->comment('Trạng thái đơn hàng');
            $table->string('buyer_user_id')->nullable()->comment('ID người mua');
            $table->string('buyer_username')->nullable()->comment('Tên người mua');

            // Thông tin vận chuyển
            $table->string('shipping_type')->nullable()->comment('Phương thức vận chuyển');
            $table->boolean('is_buyer_request_cancel')->default(false)->comment('Người mua có yêu cầu hủy không');

            // Thông tin kho
            $table->string('warehouse_id')->nullable()->comment('ID kho');
            $table->string('warehouse_name')->nullable()->comment('Tên kho');

            // Thời gian
            $table->timestamp('create_time')->nullable()->comment('Thời gian tạo đơn hàng');
            $table->timestamp('update_time')->nullable()->comment('Thời gian cập nhật đơn hàng');

            // Thông tin tài chính
            $table->decimal('order_amount', 15, 2)->nullable()->comment('Giá trị đơn hàng');
            $table->string('currency', 3)->default('GBP')->comment('Đơn vị tiền tệ');
            $table->decimal('shipping_fee', 15, 2)->nullable()->comment('Phí vận chuyển');
            $table->decimal('total_amount', 15, 2)->nullable()->comment('Tổng tiền');

            // Dữ liệu JSON
            $table->json('order_data')->nullable()->comment('Dữ liệu chi tiết đơn hàng');
            $table->json('raw_response')->nullable()->comment('Response gốc từ API');

            // Trạng thái đồng bộ
            $table->string('sync_status')->default('pending')->comment('Trạng thái đồng bộ: pending, synced, error');
            $table->text('sync_error')->nullable()->comment('Lỗi đồng bộ');
            $table->timestamp('last_synced_at')->nullable()->comment('Thời gian đồng bộ cuối');

            $table->timestamps();

            // Indexes
            $table->index(['tiktok_shop_id', 'order_status']);
            $table->index(['create_time']);
            $table->index(['update_time']);
            $table->index(['sync_status']);
            $table->index(['buyer_user_id']);
            $table->index(['warehouse_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tiktok_orders');
    }
};
