<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\TikTokOrder;
use App\Models\TikTokShop;
use App\Services\TikTokOrderService;

class TikTokWebhookController extends Controller
{
    /**
     * Xử lý webhook từ TikTok Partner
     */
    public function handleWebhook(Request $request)
    {
        Log::info('TikTok Webhook received', [
            'headers' => $request->headers->all(),
            'body' => $request->all()
        ]);

        try {
            // Verify webhook signature (nếu TikTok có cung cấp)
            if (!$this->verifyWebhookSignature($request)) {
                Log::warning('Invalid webhook signature');
                return response()->json(['error' => 'Invalid signature'], 403);
            }

            $payload = $request->all();
            $eventType = $payload['event_type'] ?? null;

            switch ($eventType) {
                case 'order.status.updated':
                    $this->handleOrderStatusUpdate($payload);
                    break;

                case 'order.created':
                    $this->handleOrderCreated($payload);
                    break;

                default:
                    Log::info('Unhandled webhook event type', ['event_type' => $eventType]);
            }

            return response()->json(['status' => 'success'], 200);
        } catch (\Exception $e) {
            Log::error('Webhook processing error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json(['error' => 'Internal server error'], 500);
        }
    }

    /**
     * Verify webhook signature từ TikTok
     */
    private function verifyWebhookSignature(Request $request): bool
    {
        // TikTok có thể gửi signature trong các header khác nhau
        $signature = $request->header('X-TikTok-Signature')
            ?? $request->header('X-Signature')
            ?? $request->input('signature');

        if (!$signature) {
            Log::warning('No signature found in webhook request');
            return false;
        }

        // Tính toán expected signature
        $payload = $request->getContent();
        $secret = config('services.tiktok.webhook_secret');

        if (!$secret) {
            Log::warning('Webhook secret not configured');
            return false;
        }

        $expectedSignature = hash_hmac('sha256', $payload, $secret);

        $isValid = hash_equals($expectedSignature, $signature);

        if (!$isValid) {
            Log::warning('Invalid webhook signature', [
                'received' => $signature,
                'expected' => $expectedSignature,
                'payload_length' => strlen($payload)
            ]);
        }

        return $isValid;
    }

    /**
     * Xử lý khi order status được cập nhật
     */
    private function handleOrderStatusUpdate(array $payload)
    {
        Log::info('Processing order status update webhook', ['payload' => $payload]);

        // TikTok có thể gửi order_id hoặc id
        $orderId = $payload['order_id'] ?? $payload['id'] ?? null;
        $newStatus = $payload['order_status'] ?? $payload['status'] ?? null;
        $shopId = $payload['shop_id'] ?? null;
        $updateTime = $payload['update_time'] ?? null;

        if (!$orderId || !$newStatus) {
            Log::warning('Missing order_id or order_status in webhook payload', [
                'order_id' => $orderId,
                'order_status' => $newStatus,
                'payload_keys' => array_keys($payload)
            ]);
            return;
        }

        // Tìm order trong database
        $order = TikTokOrder::where('order_id', $orderId)->first();

        if (!$order) {
            Log::warning('Order not found in database, attempting to sync', ['order_id' => $orderId]);

            // Nếu không tìm thấy order, thử sync từ API
            if ($shopId) {
                $shop = TikTokShop::where('shop_id', $shopId)->first();
                if ($shop) {
                    $orderService = new TikTokOrderService();
                    $orderService->syncSingleOrder($shop, $orderId);
                    $order = TikTokOrder::where('order_id', $orderId)->first();
                }
            }

            if (!$order) {
                Log::error('Failed to find or sync order', ['order_id' => $orderId]);
                return;
            }
        }

        // Lưu status cũ để log
        $oldStatus = $order->order_status;

        // Cập nhật status và các thông tin khác
        $updateData = [
            'order_status' => $newStatus,
            'last_synced_at' => now()
        ];

        // Cập nhật update_time nếu có
        if ($updateTime) {
            $updateData['update_time'] = date('Y-m-d H:i:s', $updateTime);
        }

        $order->update($updateData);

        Log::info('Order status updated via webhook', [
            'order_id' => $orderId,
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
            'shop_id' => $order->tiktok_shop_id
        ]);

        // Trigger event để thông báo cho các service khác (nếu cần)
        // event(new OrderStatusUpdated($order, $oldStatus, $newStatus));
    }

    /**
     * Xử lý khi order mới được tạo
     */
    private function handleOrderCreated(array $payload)
    {
        $orderId = $payload['order_id'] ?? null;
        $shopId = $payload['shop_id'] ?? null;

        if (!$orderId || !$shopId) {
            Log::warning('Missing order_id or shop_id in webhook payload');
            return;
        }

        // Tìm shop
        $shop = TikTokShop::where('shop_id', $shopId)->first();

        if (!$shop) {
            Log::warning('Shop not found in database', ['shop_id' => $shopId]);
            return;
        }

        // Sync order mới từ TikTok API
        $orderService = new TikTokOrderService();
        $orderService->syncSingleOrder($shop, $orderId);

        Log::info('New order synced via webhook', [
            'order_id' => $orderId,
            'shop_id' => $shopId
        ]);
    }

    /**
     * Test endpoint để kiểm tra webhook
     */
    public function testWebhook(Request $request)
    {
        Log::info('Webhook test endpoint called', $request->all());

        return response()->json([
            'status' => 'success',
            'message' => 'Webhook endpoint is working',
            'timestamp' => now()->toISOString(),
            'received_data' => $request->all()
        ]);
    }
}
