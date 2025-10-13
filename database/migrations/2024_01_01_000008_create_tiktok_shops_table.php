<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tiktok_shops', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained()->onDelete('cascade');
            $table->foreignId('tiktok_shop_integration_id')->constrained()->onDelete('cascade');
            $table->string('shop_id')->unique();
            $table->string('shop_name');
            $table->string('seller_name');
            $table->string('seller_region');
            $table->string('open_id');
            $table->string('cipher')->nullable();
            $table->enum('status', ['active', 'inactive', 'suspended'])->default('active');
            $table->json('shop_data')->nullable();
            $table->timestamps();

            $table->index(['team_id', 'status']);
            $table->index('shop_id');
            $table->index(['tiktok_shop_integration_id', 'status']);
        });

        Schema::create('tiktok_shop_sellers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tiktok_shop_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('role', ['owner', 'manager', 'viewer'])->default('viewer');
            $table->json('permissions')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['tiktok_shop_id', 'user_id']);
            $table->index(['user_id', 'is_active']);
            $table->unique(['tiktok_shop_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tiktok_shop_sellers');
        Schema::dropIfExists('tiktok_shops');
    }
};
