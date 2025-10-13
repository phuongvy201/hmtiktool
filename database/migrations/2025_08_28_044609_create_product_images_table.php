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
        Schema::create('product_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->string('file_name')->nullable(); // Tên file gốc
            $table->string('file_path')->nullable(); // Đường dẫn file local
            $table->string('tiktok_uri')->nullable(); // URI từ TikTok API
            $table->string('tiktok_resource_id')->nullable(); // Resource ID từ TikTok
            $table->enum('type', ['image', 'video'])->default('image');
            $table->integer('sort_order')->default(0); // Thứ tự hiển thị
            $table->boolean('is_primary')->default(false); // Ảnh chính
            $table->boolean('is_uploaded_to_tiktok')->default(false); // Đã upload lên TikTok chưa
            $table->timestamp('tiktok_uploaded_at')->nullable(); // Thời gian upload lên TikTok
            $table->timestamps();

            $table->index(['product_id', 'sort_order']);
            $table->index(['is_uploaded_to_tiktok']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_images');
    }
};
