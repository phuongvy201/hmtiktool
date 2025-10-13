<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('team_id')->constrained()->onDelete('cascade');

            $table->string('name');
            $table->text('description')->nullable();
            $table->string('category_id')->nullable();

            // Các cột bổ sung từ migration thứ hai
            $table->decimal('base_price', 10, 2)->nullable();
            $table->decimal('list_price', 10, 2)->nullable();
            $table->decimal('weight', 8, 2)->nullable();
            $table->decimal('height', 8, 2)->nullable();
            $table->decimal('width', 8, 2)->nullable();
            $table->decimal('length', 8, 2)->nullable();
            $table->json('images')->nullable();
            $table->string('size_chart')->nullable();
            $table->string('product_video')->nullable();
            $table->json('general_attributes')->nullable();

            $table->json('product_data')->nullable();
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->index(['user_id', 'is_active']);
            $table->index('category_id');
        });

        Schema::create('prod_template_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_template_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('type'); // size, color, material, etc.
            $table->boolean('is_required')->default(false);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index(['product_template_id', 'sort_order']);
        });

        Schema::create('prod_option_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prod_template_option_id')->constrained('prod_template_options')->onDelete('cascade');
            $table->string('value');
            $table->string('label')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index(['prod_template_option_id', 'sort_order']);
        });

        Schema::create('prod_template_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_template_id')->constrained()->onDelete('cascade');
            $table->string('sku')->nullable();
            $table->decimal('price', 10, 2)->nullable();
            $table->integer('stock_quantity')->nullable();
            $table->json('variant_data')->nullable();
            $table->timestamps();

            $table->index(['product_template_id', 'sku']);
        });

        Schema::create('prod_variant_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prod_template_variant_id')->constrained('prod_template_variants')->onDelete('cascade');
            $table->foreignId('prod_template_option_id')->constrained('prod_template_options')->onDelete('cascade');
            $table->foreignId('prod_option_value_id')->constrained('prod_option_values')->onDelete('cascade');
            $table->timestamps();

            $table->unique(['prod_template_variant_id', 'prod_template_option_id'], 'prod_variant_option_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prod_variant_options');
        Schema::dropIfExists('prod_template_variants');
        Schema::dropIfExists('prod_option_values');
        Schema::dropIfExists('prod_template_options');
        Schema::dropIfExists('product_templates');
    }
};
