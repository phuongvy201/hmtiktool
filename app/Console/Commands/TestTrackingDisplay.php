<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\TikTokOrder;

class TestTrackingDisplay extends Command
{
    protected $signature = 'test:tracking-display {order_id}';
    protected $description = 'Test tracking number display logic for an order';

    public function handle()
    {
        $orderId = $this->argument('order_id');

        $this->info("Testing tracking display for order ID: {$orderId}");

        // Find order in database
        $order = TikTokOrder::find($orderId);

        if (!$order) {
            $this->error("Order not found in database: {$orderId}");
            return 1;
        }

        $this->info("Found order: {$order->order_id} (TikTok ID)");
        $this->info("Status: {$order->order_status}");
        $this->info("Shipping Type: {$order->shipping_type}");

        $this->info("\n📊 Order Data Analysis:");

        // Check root level tracking
        if (isset($order->order_data['tracking_number']) && !empty($order->order_data['tracking_number'])) {
            $this->info("✅ Root level tracking: {$order->order_data['tracking_number']}");
            if (isset($order->order_data['shipping_provider_name'])) {
                $this->info("   Provider: {$order->order_data['shipping_provider_name']}");
            }
        } else {
            $this->warn("❌ No root level tracking number");
        }

        // Check line_items tracking
        if (isset($order->order_data['line_items'][0]['tracking_number']) && !empty($order->order_data['line_items'][0]['tracking_number'])) {
            $this->info("✅ Line items tracking: {$order->order_data['line_items'][0]['tracking_number']}");
            if (isset($order->order_data['line_items'][0]['shipping_provider_name'])) {
                $this->info("   Provider: {$order->order_data['line_items'][0]['shipping_provider_name']}");
            }
        } else {
            $this->warn("❌ No line items tracking number");
        }

        // Check shipping_provider
        if (isset($order->order_data['shipping_provider']) && !empty($order->order_data['shipping_provider'])) {
            $this->info("✅ Shipping provider: {$order->order_data['shipping_provider']}");
        } else {
            $this->warn("❌ No shipping provider");
        }

        // Test display logic
        $this->info("\n🎯 Display Logic Test:");

        $trackingNumber = null;
        $shippingProviderName = null;

        // 1. Kiểm tra root level (từ form add tracking)
        if (isset($order->order_data['tracking_number']) && trim($order->order_data['tracking_number']) !== '') {
            $trackingNumber = $order->order_data['tracking_number'];
            $shippingProviderName = $order->order_data['shipping_provider_name'] ?? null;
            $this->info("📍 Using root level tracking: {$trackingNumber}");
        }
        // 2. Kiểm tra trong line_items (từ TikTok API)
        elseif (isset($order->order_data['line_items'][0]['tracking_number']) && trim($order->order_data['line_items'][0]['tracking_number']) !== '') {
            $trackingNumber = $order->order_data['line_items'][0]['tracking_number'];
            $shippingProviderName = $order->order_data['line_items'][0]['shipping_provider_name'] ?? null;
            $this->info("📍 Using line items tracking: {$trackingNumber}");
        }
        // 3. Kiểm tra shipping_provider từ root level
        elseif (isset($order->order_data['shipping_provider']) && trim($order->order_data['shipping_provider']) !== '') {
            $shippingProviderName = $order->order_data['shipping_provider'];
            $this->info("📍 Using shipping provider: {$shippingProviderName}");
        }

        if ($trackingNumber) {
            $this->info("✅ Will display tracking: {$trackingNumber}");
            if ($shippingProviderName) {
                $this->info("   With provider: {$shippingProviderName}");
            }
        } elseif ($shippingProviderName) {
            $this->info("✅ Will display 'Đã gửi' with provider: {$shippingProviderName}");
        } else {
            $this->warn("❌ No tracking number or provider to display");
        }

        // Check if should show "Thêm Tracking" button
        if ($order->order_status == 'AWAITING_SHIPMENT' && ($order->shipping_type == 'SELLER' || !$order->shipping_type)) {
            $this->info("🔘 Will show 'Thêm Tracking' button");
        } elseif ($trackingNumber) {
            $this->info("✅ Will show tracking number (any status)");
        } elseif ($shippingProviderName && in_array($order->order_status, ['IN_TRANSIT', 'AWAITING_COLLECTION'])) {
            $this->info("📦 Will show 'Đã gửi' with provider name");
        } elseif (in_array($order->order_status, ['IN_TRANSIT', 'AWAITING_COLLECTION'])) {
            $this->info("📦 Will show 'Đã gửi'");
        } else {
            $this->info("➖ Will show '-'");
        }

        return 0;
    }
}
