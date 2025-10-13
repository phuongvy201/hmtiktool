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
        Schema::create('prod_template_category_attributes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_template_id')->constrained()->onDelete('cascade');
            $table->string('category_id'); // TikTok category ID
            $table->string('attribute_id'); // TikTok attribute ID
            $table->string('attribute_name'); // Tên attribute (e.g., "Occasion", "Material")
            $table->enum('attribute_type', ['PRODUCT_PROPERTY', 'SALES_PROPERTY']); // Loại attribute
            $table->boolean('is_required')->default(false); // Có bắt buộc không
            $table->string('value')->nullable(); // Giá trị được chọn/nhập
            $table->string('value_id')->nullable(); // ID của giá trị nếu có (cho dropdown)
            $table->string('value_name')->nullable(); // Tên của giá trị nếu có (cho dropdown)
            $table->json('attribute_data')->nullable(); // Dữ liệu đầy đủ của attribute từ TikTok API
            $table->timestamps();

            // Indexes
            $table->index(['product_template_id', 'category_id'], 'template_cat_idx');
            $table->index(['category_id', 'attribute_id'], 'cat_attr_idx');
            $table->index('is_required', 'required_idx');
            $table->index('attribute_type', 'type_idx');

            // Unique constraint để tránh duplicate
            $table->unique(['product_template_id', 'category_id', 'attribute_id'], 'template_category_attribute_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prod_template_category_attributes');
    }
};
