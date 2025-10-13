<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tiktok_shop_categories', function (Blueprint $table) {
            $table->id();
            $table->string('category_id')->unique();
            $table->string('category_name');
            $table->string('parent_category_id')->nullable();
            $table->integer('level')->default(1);
            $table->boolean('is_leaf')->default(false);
            $table->boolean('is_active')->default(true);
            $table->json('category_data')->nullable();
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();

            $table->index('parent_category_id');
            $table->index(['level', 'is_active']);
            $table->index('last_synced_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tiktok_shop_categories');
    }
};
