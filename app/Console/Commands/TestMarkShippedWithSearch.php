<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\TikTokOrder;
use App\Services\TikTokOrderService;

class TestMarkShippedWithSearch extends Command
{
    protected $signature = 'test:mark-shipped-search {order_id}';
    protected $description = 'Test mark as shipped flow with POST /order/202309/orders/search API';

    public function handle()
    {
        $orderId = $this->argument('order_id');

        $this->info("Testing mark as shipped flow with search API for order ID: {$orderId}");

        // Find order in database
        $order = TikTokOrder::with('tiktokShop')->find($orderId);

        if (!$order) {
            $this->error("Order not found in database: {$orderId}");
            return 1;
        }

        $this->info("Found order: {$order->order_id} (TikTok ID)");
        $this->info("Shop: {$order->tiktokShop->shop_name}");

        $this->info("\n📊 BEFORE Search:");
        $this->info("Status: {$order->order_status}");
        $this->info("Update time: {$order->update_time}");

        if (isset($order->order_data['tracking_number']) && trim($order->order_data['tracking_number']) !== '') {
            $this->info("Tracking: {$order->order_data['tracking_number']}");
        } else {
            $this->warn("No tracking number");
        }

        $this->info("\n🔄 Searching order using POST /order/202309/orders/search...");

        $tiktokOrderService = new TikTokOrderService();
        $result = $tiktokOrderService->searchOrderById($order->tiktokShop, $order->order_id);

        if ($result['success'] && !empty($result['data']['orders'])) {
            $this->info("✅ Search successful!");

            $orderData = $result['data']['orders'][0];

            $this->info("\n📊 Order data from TikTok API:");
            $this->info("Status: {$orderData['status']}");
            $this->info("Update time: " . (isset($orderData['update_time']) ?
                \Carbon\Carbon::createFromTimestamp($orderData['update_time'])->format('Y-m-d H:i:s') : 'N/A'));

            // Check tracking info in line_items
            if (
                isset($orderData['line_items'][0]['tracking_number']) &&
                trim($orderData['line_items'][0]['tracking_number']) !== ''
            ) {
                $this->info("Tracking: {$orderData['line_items'][0]['tracking_number']}");
                if (isset($orderData['line_items'][0]['shipping_provider_name'])) {
                    $this->info("Provider: {$orderData['line_items'][0]['shipping_provider_name']}");
                }
            } else {
                $this->warn("No tracking number in API response");
            }

            // Check shipping provider
            if (isset($orderData['shipping_provider']) && trim($orderData['shipping_provider']) !== '') {
                $this->info("Shipping provider: {$orderData['shipping_provider']}");
            }

            $this->info("\n🎯 Summary:");
            $this->info("- API call successful");
            $this->info("- Order found in TikTok system");
            $this->info("- Status: {$orderData['status']}");
            $this->info("- Ready for mark as shipped flow");
        } else {
            $this->error("❌ Search failed!");
            $this->error("Error: " . ($result['message'] ?? 'Unknown error'));
            return 1;
        }

        return 0;
    }
}
