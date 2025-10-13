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
        Schema::table('product_templates', function (Blueprint $table) {
            // Thêm các cột còn thiếu
            $table->decimal('base_price', 10, 2)->nullable()->after('category_id');
            $table->decimal('list_price', 10, 2)->nullable()->after('base_price');
            $table->decimal('weight', 8, 2)->nullable()->after('list_price');
            $table->decimal('height', 8, 2)->nullable()->after('weight');
            $table->decimal('width', 8, 2)->nullable()->after('height');
            $table->decimal('length', 8, 2)->nullable()->after('width');
            $table->json('images')->nullable()->after('length');
            $table->string('size_chart')->nullable()->after('images');
            $table->string('product_video')->nullable()->after('size_chart');
            $table->json('general_attributes')->nullable()->after('product_video');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_templates', function (Blueprint $table) {
            $table->dropColumn([
                'base_price',
                'list_price', 
                'weight',
                'height',
                'width',
                'length',
                'images',
                'size_chart',
                'product_video',
                'general_attributes'
            ]);
        });
    }
};
