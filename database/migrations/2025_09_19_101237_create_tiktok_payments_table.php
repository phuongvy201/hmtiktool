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
        Schema::create('tiktok_payments', function (Blueprint $table) {
            $table->id();
            $table->string('payment_id')->unique()->comment('TikTok Payment ID');
            $table->unsignedBigInteger('tiktok_shop_id')->comment('Foreign key to tiktok_shops table');
            $table->string('shop_name')->nullable()->comment('Shop name at time of payment');
            $table->string('shop_profile')->nullable()->comment('Shop profile at time of payment');
            $table->integer('create_time')->nullable()->comment('Payment creation timestamp');
            $table->integer('paid_time')->nullable()->comment('Payment paid timestamp');
            $table->string('status')->nullable()->comment('Payment status (PAID, PENDING, FAILED, etc.)');

            // Amount fields based on actual API response
            $table->decimal('amount_value', 15, 2)->default(0)->comment('Payment amount value');
            $table->string('amount_currency', 10)->default('GBP')->comment('Payment amount currency');
            $table->decimal('settlement_amount_value', 15, 2)->default(0)->comment('Settlement amount value');
            $table->string('settlement_amount_currency', 10)->default('GBP')->comment('Settlement amount currency');
            $table->decimal('reserve_amount_value', 15, 2)->default(0)->comment('Reserve amount value');
            $table->string('reserve_amount_currency', 10)->default('GBP')->comment('Reserve amount currency');
            $table->decimal('payment_amount_before_exchange_value', 15, 2)->default(0)->comment('Payment amount before exchange');
            $table->string('payment_amount_before_exchange_currency', 10)->default('GBP')->comment('Payment amount before exchange currency');
            $table->decimal('exchange_rate', 10, 6)->default(1.000000)->comment('Exchange rate');

            $table->string('bank_account')->nullable()->comment('Bank account number (masked)');
            $table->json('payment_data')->nullable()->comment('Full payment data from TikTok API');
            $table->timestamp('last_synced_at')->nullable()->comment('Last time this payment was synced from TikTok');
            $table->timestamps();

            // Indexes
            $table->index(['tiktok_shop_id', 'create_time']);
            $table->index(['status', 'create_time']);
            $table->index('last_synced_at');
            $table->index('create_time');
            $table->index('paid_time');

            // Foreign key constraint
            $table->foreign('tiktok_shop_id')->references('id')->on('tiktok_shops')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tiktok_payments');
    }
};
