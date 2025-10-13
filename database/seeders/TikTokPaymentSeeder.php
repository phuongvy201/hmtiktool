<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\TikTokPayment;
use App\Models\TikTokShop;
use Carbon\Carbon;

class TikTokPaymentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Lấy tất cả shops
        $shops = TikTokShop::all();
        
        if ($shops->isEmpty()) {
            $this->command->warn('No TikTok shops found. Please create shops first.');
            return;
        }

        $this->command->info('Creating sample TikTok payment data...');

        // Tạo dữ liệu giả cho mỗi shop
        foreach ($shops as $shop) {
            $this->createSamplePayments($shop);
        }

        $this->command->info('Sample TikTok payment data created successfully!');
    }

    private function createSamplePayments(TikTokShop $shop): void
    {
        $currencies = ['GBP', 'USD', 'EUR'];
        $statuses = ['PAID', 'PENDING', 'FAILED'];
        
        // Tạo 10-15 payments cho mỗi shop
        $paymentCount = rand(10, 15);
        
        for ($i = 0; $i < $paymentCount; $i++) {
            $currency = $currencies[array_rand($currencies)];
            $status = $statuses[array_rand($statuses)];
            
            // Tạo thời gian ngẫu nhiên trong 30 ngày qua
            $createTime = Carbon::now()->subDays(rand(0, 30))->subHours(rand(0, 23))->subMinutes(rand(0, 59));
            $paidTime = $status === 'PAID' ? $createTime->copy()->addHours(rand(1, 48)) : null;
            
            // Tạo số tiền ngẫu nhiên
            $amount = rand(1000, 50000) / 100; // 10.00 - 500.00
            $settlementAmount = $amount * (0.85 + (rand(0, 15) / 100)); // 85% - 100% của amount
            $reserveAmount = $amount - $settlementAmount; // Phần còn lại là reserve
            
            // Tạo bank account giả
            $bankAccount = '****' . str_pad(rand(1000, 9999), 4, '0', STR_PAD_LEFT);
            
            TikTokPayment::create([
                'payment_id' => 'PAY_' . $shop->id . '_' . time() . '_' . $i,
                'tiktok_shop_id' => $shop->id,
                'shop_name' => $shop->shop_name,
                'shop_profile' => $shop->shop_profile,
                'create_time' => $createTime->timestamp,
                'paid_time' => $paidTime ? $paidTime->timestamp : null,
                'status' => $status,
                'amount_value' => $amount,
                'amount_currency' => $currency,
                'settlement_amount_value' => $settlementAmount,
                'settlement_amount_currency' => $currency,
                'reserve_amount_value' => $reserveAmount,
                'reserve_amount_currency' => $currency,
                'payment_amount_before_exchange_value' => $amount,
                'payment_amount_before_exchange_currency' => $currency,
                'exchange_rate' => 1.000000,
                'bank_account' => $bankAccount,
                'payment_data' => [
                    'id' => 'PAY_' . $shop->id . '_' . time() . '_' . $i,
                    'create_time' => $createTime->timestamp,
                    'paid_time' => $paidTime ? $paidTime->timestamp : null,
                    'status' => $status,
                    'amount' => [
                        'value' => (string)$amount,
                        'currency' => $currency
                    ],
                    'settlement_amount' => [
                        'value' => (string)$settlementAmount,
                        'currency' => $currency
                    ],
                    'reserve_amount' => [
                        'value' => (string)$reserveAmount,
                        'currency' => $currency
                    ],
                    'payment_amount_before_exchange' => [
                        'value' => (string)$amount,
                        'currency' => $currency
                    ],
                    'exchange_rate' => '1.000000',
                    'bank_account' => $bankAccount
                ],
                'last_synced_at' => now(),
            ]);
        }
        
        $this->command->info("Created {$paymentCount} sample payments for shop: {$shop->shop_name}");
    }
}