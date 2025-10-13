<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\TikTokOrder;
use App\Services\TikTokOrderService;

class TestOrderByIdsAPI extends Command
{
    protected $signature = 'test:order-by-ids-api {order_id}';
    protected $description = 'Test GET /order/202507/orders API to find order by ID';

    public function handle()
    {
        $orderId = $this->argument('order_id');

        $this->info("Testing GET /order/202507/orders API for order ID: {$orderId}");

        // Find order in database
        $order = TikTokOrder::with('tiktokShop')->find($orderId);

        if (!$order) {
            $this->error("Order not found in database: {$orderId}");
            return 1;
        }

        $this->info("Found order: {$order->order_id} (TikTok ID)");
        $this->info("Shop: {$order->tiktokShop->shop_name}");
        $this->info("Current status: {$order->order_status}");

        if (isset($order->order_data['tracking_number']) && trim($order->order_data['tracking_number']) !== '') {
            $this->info("Current tracking: {$order->order_data['tracking_number']}");
        } else {
            $this->warn("No tracking number found");
        }

        $this->info("\n🔄 Searching order using GET /order/202507/orders...");

        $tiktokOrderService = new TikTokOrderService();
        $result = $tiktokOrderService->searchOrderById($order->tiktokShop, $order->order_id);

        if ($result['success']) {
            $this->info("✅ Search successful!");

            $orders = $result['data']['orders'] ?? [];
            if (!empty($orders)) {
                $orderData = $orders[0];

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
                $this->warn("❌ No orders found in API response");
            }
        } else {
            $this->error("❌ Search failed!");
            $this->error("Error: {$result['message']}");
            return 1;
        }

        return 0;
    }
}
