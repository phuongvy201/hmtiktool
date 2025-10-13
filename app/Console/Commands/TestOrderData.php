<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\TikTokOrder;

class TestOrderData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:order-data {order_id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test order data to check TikTok order ID and delivery option ID';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $orderId = $this->argument('order_id');

        try {
            // Tìm đơn hàng
            $order = TikTokOrder::with('shop')->findOrFail($orderId);

            $this->info("=== Order Data Analysis ===");
            $this->info("Database ID: {$order->id}");
            $this->info("TikTok Order ID: {$order->order_id}");
            $this->info("Order Status: {$order->order_status}");
            $this->info("Shop: {$order->shop->shop_name}");

            // Kiểm tra order_data
            $orderData = $order->order_data;
            if ($orderData) {
                $this->info("\n=== Order Data Structure ===");
                $this->info("Order Data Keys: " . implode(', ', array_keys($orderData)));

                // Kiểm tra delivery_option_id
                if (isset($orderData['delivery_option']['delivery_option_id'])) {
                    $this->info("Delivery Option ID: {$orderData['delivery_option']['delivery_option_id']}");
                } else {
                    $this->warn("❌ Không tìm thấy delivery_option_id trong order_data");
                    $this->info("Available keys in order_data: " . implode(', ', array_keys($orderData)));
                }

                // Kiểm tra line_items
                if (isset($orderData['line_items'])) {
                    $lineItems = $orderData['line_items'];
                    $this->info("Line Items Count: " . count($lineItems));
                    if (count($lineItems) > 0) {
                        $this->info("First Line Item ID: " . ($lineItems[0]['id'] ?? 'N/A'));
                    }
                } else {
                    $this->warn("❌ Không tìm thấy line_items trong order_data");
                }

                // Hiển thị toàn bộ order_data structure
                $this->info("\n=== Full Order Data ===");
                $this->line(json_encode($orderData, JSON_PRETTY_PRINT));
            } else {
                $this->error("❌ Order data is null or empty");
            }
        } catch (\Exception $e) {
            $this->error("Error: " . $e->getMessage());
        }
    }
}
