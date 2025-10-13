<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\TikTokOrder;
use App\Services\TikTokOrderService;

class TestMarkShippedFlow extends Command
{
    protected $signature = 'test:mark-shipped-flow {order_id}';
    protected $description = 'Test the complete mark as shipped flow including status update from TikTok';

    public function handle()
    {
        $orderId = $this->argument('order_id');

        $this->info("Testing mark as shipped flow for order ID: {$orderId}");

        // Find order in database
        $order = TikTokOrder::with('tiktokShop')->find($orderId);

        if (!$order) {
            $this->error("Order not found in database: {$orderId}");
            return 1;
        }

        $this->info("Found order: {$order->order_id} (TikTok ID)");
        $this->info("Shop: {$order->tiktokShop->shop_name}");

        $this->info("\n📊 BEFORE Mark as Shipped:");
        $this->info("Status: {$order->order_status}");
        $this->info("Update time: {$order->update_time}");

        if (isset($order->order_data['tracking_number']) && trim($order->order_data['tracking_number']) !== '') {
            $this->info("Tracking: {$order->order_data['tracking_number']}");
        } else {
            $this->warn("No tracking number");
        }

        if (isset($order->order_data['shipping_provider_name'])) {
            $this->info("Provider: {$order->order_data['shipping_provider_name']}");
        } else {
            $this->warn("No shipping provider name");
        }

        $this->info("\n🔄 Simulating Mark as Shipped...");

        // Simulate the flow: update tracking info first
        $orderData = $order->order_data;
        $orderData['tracking_number'] = 'TEST123456789';
        $orderData['shipping_provider_id'] = 'test_provider_id';
        $orderData['shipping_provider_name'] = 'Test Provider';

        $order->update([
            'order_data' => $orderData,
            'update_time' => now()
        ]);

        $this->info("✅ Updated tracking info locally");

        // Then sync from TikTok to get the real status
        $this->info("🔄 Syncing from TikTok API...");

        $tiktokOrderService = new TikTokOrderService();
        $result = $tiktokOrderService->syncSingleOrder($order->tiktokShop, $order->order_id);

        if ($result['success']) {
            $this->info("✅ Sync successful!");

            // Refresh order data
            $order->refresh();

            $this->info("\n📊 AFTER Mark as Shipped & Sync:");
            $this->info("Status: {$order->order_status}");
            $this->info("Update time: {$order->update_time}");

            if (isset($order->order_data['tracking_number']) && trim($order->order_data['tracking_number']) !== '') {
                $this->info("Tracking: {$order->order_data['tracking_number']}");
            } else {
                $this->warn("No tracking number");
            }

            if (isset($order->order_data['shipping_provider_name'])) {
                $this->info("Provider: {$order->order_data['shipping_provider_name']}");
            } else {
                $this->warn("No shipping provider name");
            }

            $this->info("\n🎯 Summary:");
            $this->info("- Tracking info updated locally first");
            $this->info("- Status updated from TikTok API during sync");
            $this->info("- Final status: {$order->order_status}");
        } else {
            $this->error("❌ Sync failed!");
            $this->error("Error: {$result['message']}");
            return 1;
        }

        return 0;
    }
}
