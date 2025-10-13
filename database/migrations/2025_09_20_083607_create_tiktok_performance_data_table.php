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
        Schema::create('tiktok_performance_data', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tiktok_shop_id');
            $table->date('start_date');
            $table->date('end_date');
            $table->string('granularity')->default('1D');
            $table->json('data')->comment('Performance data JSON');
            $table->timestamp('cached_at');
            $table->timestamps();

            $table->foreign('tiktok_shop_id')->references('id')->on('tiktok_shops')->onDelete('cascade');
            $table->unique(['tiktok_shop_id', 'start_date', 'end_date', 'granularity'], 'unique_performance_record');
            $table->index(['tiktok_shop_id', 'cached_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tiktok_performance_data');
    }
};
