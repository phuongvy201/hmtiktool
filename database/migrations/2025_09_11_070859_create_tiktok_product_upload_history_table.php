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
        Schema::create('tiktok_product_upload_history', function (Blueprint $table) {
            $table->id();

            // Thông tin user thực hiện upload
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('user_name')->nullable(); // Tên user để dễ tra cứu

            // Thông tin sản phẩm
            $table->unsignedBigInteger('product_id');
            $table->string('product_name');

            // Thông tin TikTok Shop
            $table->unsignedBigInteger('tiktok_shop_id');
            $table->string('shop_name');
            $table->string('shop_cipher');

            // Kết quả upload
            $table->enum('status', ['success', 'failed', 'pending'])->default('pending');
            $table->text('error_message')->nullable();
            $table->json('response_data')->nullable(); // Lưu toàn bộ response từ TikTok API

            // Thông tin TikTok
            $table->string('tiktok_product_id')->nullable(); // ID sản phẩm trên TikTok
            $table->json('tiktok_skus')->nullable(); // Danh sách SKUs được tạo trên TikTok

            // Metadata
            $table->string('idempotency_key')->nullable();
            $table->timestamp('uploaded_at')->nullable(); // Thời gian thực tế upload
            $table->json('request_data')->nullable(); // Dữ liệu request gửi lên TikTok (để debug)

            $table->timestamps();

            // Indexes
            $table->index(['user_id', 'created_at']);
            $table->index(['product_id', 'created_at']);
            $table->index(['tiktok_shop_id', 'created_at']);
            $table->index(['status', 'created_at']);
            $table->index('tiktok_product_id');
            $table->index('idempotency_key');

            // Foreign keys
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            $table->foreign('tiktok_shop_id')->references('id')->on('tiktok_shops')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tiktok_product_upload_history');
    }
};
