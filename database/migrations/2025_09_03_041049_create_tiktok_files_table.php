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
        Schema::create('tiktok_files', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tiktok_shop_integration_id')->nullable();
            $table->unsignedBigInteger('product_id')->nullable();
            $table->unsignedBigInteger('product_template_id')->nullable();
            $table->string('file_name');
            $table->string('file_path')->nullable(); // Local path hoặc S3 URL
            $table->string('file_type'); // pdf, mp4, mov, mkv, wmv, webm, avi, 3gp, flv, mpeg
            $table->string('source')->default('manual'); // manual, template, product
            $table->string('use_case')->default('PRODUCT_VIDEO'); // PRODUCT_VIDEO, CERTIFICATION, etc.
            $table->bigInteger('file_size')->nullable(); // Size in bytes
            $table->string('tiktok_uri')->nullable(); // TikTok resource URI
            $table->string('tiktok_url')->nullable(); // TikTok resource URL
            $table->string('tiktok_resource_id')->nullable(); // TikTok resource ID
            $table->boolean('is_uploaded_to_tiktok')->default(false);
            $table->timestamp('tiktok_uploaded_at')->nullable();
            $table->text('upload_response')->nullable(); // Full response from TikTok API
            $table->text('error_message')->nullable(); // Error message if upload failed
            $table->timestamps();

            // Indexes
            $table->index('tiktok_shop_integration_id');
            $table->index('product_id');
            $table->index('product_template_id');
            $table->index('file_type');
            $table->index('source');
            $table->index('is_uploaded_to_tiktok');

            // Foreign keys
            $table->foreign('tiktok_shop_integration_id')->references('id')->on('tiktok_shop_integrations')->onDelete('set null');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            $table->foreign('product_template_id')->references('id')->on('product_templates')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tiktok_files');
    }
};
