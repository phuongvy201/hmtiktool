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
        Schema::create('publish_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('team_id')->constrained()->onDelete('cascade');
            $table->string('tiktok_product_id')->nullable(); // ID sản phẩm trên TikTok
            $table->string('tiktok_shop_id')->nullable(); // ID shop TikTok
            $table->enum('status', ['pending', 'processing', 'success', 'failed'])->default('pending');
            $table->enum('action', ['create', 'update', 'delete'])->default('create');
            $table->json('request_data')->nullable(); // Dữ liệu gửi lên TikTok API
            $table->json('response_data')->nullable(); // Dữ liệu trả về từ TikTok API
            $table->text('error_message')->nullable(); // Thông báo lỗi nếu có
            $table->timestamp('published_at')->nullable(); // Thời gian đăng thành công
            $table->timestamp('failed_at')->nullable(); // Thời gian thất bại
            $table->integer('retry_count')->default(0); // Số lần thử lại
            $table->timestamp('next_retry_at')->nullable(); // Thời gian thử lại tiếp theo
            $table->timestamps();

            $table->index(['product_id', 'status']);
            $table->index(['team_id', 'status']);
            $table->index(['user_id', 'created_at']);
            $table->index(['status', 'next_retry_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('publish_histories');
    }
};
