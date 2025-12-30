<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tiktok_shop_integrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained()->onDelete('cascade');
            $table->text('access_token')->nullable();
            $table->text('refresh_token')->nullable();
            // đổi thành bigInteger ngay từ đầu
            $table->bigInteger('access_token_expires_at')->nullable();
            $table->bigInteger('refresh_token_expires_at')->nullable();
            $table->enum('status', ['pending', 'active', 'error', 'disconnected'])->default('pending');
            $table->text('error_message')->nullable();
            $table->json('additional_data')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('tiktok_shop_integrations');
    }
};
