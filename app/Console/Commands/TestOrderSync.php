<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\TikTokOrder;
use App\Services\TikTokOrderService;

class TestOrderSync extends Command
{
    protected $signature = 'test:order-sync {order_id}';
    protected $description = 'Test sync single order from TikTok API';

    public function handle()
    {
        $orderId = $this->argument('order_id');

        $this->info("Testing sync for order ID: {$orderId}");

        // Find order in database
        $order = TikTokOrder::with('tiktokShop')->find($orderId);

        if (!$order) {
            $this->error("Order not found in database: {$orderId}");
            return 1;
        }

        $this->info("Found order: {$order->order_id} (TikTok ID)");
        $this->info("Shop: {$order->tiktokShop->shop_name}");
        $this->info("Current status: {$order->order_status}");

        if (isset($order->order_data['tracking_number'])) {
            $this->info("Current tracking: {$order->order_data['tracking_number']}");
        } else {
            $this->warn("No tracking number found in order_data");
        }

        // Test sync
        $this->info("\n🔄 Syncing order from TikTok API...");

        $tiktokOrderService = new TikTokOrderService();
        $result = $tiktokOrderService->syncSingleOrder($order->tiktokShop, $order->order_id);

        if ($result['success']) {
            $this->info("✅ Sync successful!");
            $this->info("Message: {$result['message']}");

            // Refresh order data
            $order->refresh();
            $this->info("\n📊 Updated order data:");
            $this->info("Status: {$order->order_status}");
            $this->info("Update time: {$order->update_time}");

            if (isset($order->order_data['tracking_number'])) {
                $this->info("Tracking: {$order->order_data['tracking_number']}");
                if (isset($order->order_data['shipping_provider_name'])) {
                    $this->info("Provider: {$order->order_data['shipping_provider_name']}");
                }
            }
        } else {
            $this->error("❌ Sync failed!");
            $this->error("Error: {$result['message']}");
            return 1;
        }

        return 0;
    }
}
