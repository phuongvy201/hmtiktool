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
        Schema::create('user_tiktok_markets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('market', 10)->comment('US or UK');
            $table->timestamps();

            // Một user chỉ có thể có 1 market duy nhất (có thể thay đổi nếu cần nhiều markets)
            $table->unique(['user_id', 'market'], 'user_market_unique');
            $table->index('user_id');
            $table->index('market');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_tiktok_markets');
    }
};
