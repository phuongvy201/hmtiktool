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
     * Xử lý webhook từ TikTok Partner (chung cho tất cả categories)
     * 
     * Categories:
     * - type: 1 = Order Status Change
     * - type: 7 = Auth Expire
     */
    public function handleWebhook(Request $request)
    {
        Log::info('TikTok Webhook received', [
            'headers' => $request->headers->all(),
            'body' => $request->all()
        ]);

        try {
            // Bỏ qua signature verification vì TikTok webhook không gửi kèm signature
            // Log để theo dõi
            Log::info('Processing webhook without signature verification');

            $payload = $request->all();
            
            // Xử lý dựa trên field 'type' (TikTok webhook format)
            $type = $payload['type'] ?? null;
            
            if ($type === null) {
                // Fallback: xử lý format cũ với event_type
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
            } else {
                // Xử lý theo type (TikTok webhook format mới)
                switch ($type) {
                    case 1: // Order Status Change
                        $this->handleOrderStatusChangeByType($payload);
                        break;
                    
                    case 7: // Auth Expire
                        $this->handleAuthExpire($payload);
                        break;
                    
                    default:
                        Log::info('Unhandled webhook type', [
                            'type' => $type,
                            'notification_id' => $payload['tts_notification_id'] ?? null
                        ]);
                }
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Webhook processed successfully'
            ], 200);
        } catch (\Exception $e) {
            Log::error('Webhook processing error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'payload' => $request->all()
            ]);

            return response()->json(['error' => 'Internal server error'], 500);
        }
    }

    /**
     * Verify webhook signature từ TikTok
     */
    private function verifyWebhookSignature(Request $request): bool
    {
        // TikTok webhook không gửi kèm signature, nên luôn return true để bỏ qua verification
        Log::info('Skipping webhook signature verification - TikTok does not send signature');
        return true;
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
            // Nếu là timestamp (số), convert sang datetime
            if (is_numeric($updateTime)) {
                $updateData['update_time'] = date('Y-m-d H:i:s', $updateTime);
            } else {
                $updateData['update_time'] = $updateTime;
            }
        }

        // Lưu thông tin webhook vào order_data nếu có
        $isOnHold = $payload['is_on_hold_order'] ?? false;
        $orderData = $order->order_data ?? [];
        $orderData['webhook_info'] = [
            'is_on_hold_order' => $isOnHold,
            'last_webhook_update' => now()->toISOString(),
            'notification_id' => $payload['tts_notification_id'] ?? null,
            'webhook_type' => $payload['type'] ?? null
        ];
        $updateData['order_data'] = $orderData;

        $order->update($updateData);

        Log::info('Order status updated via webhook', [
            'order_id' => $orderId,
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
            'is_on_hold_order' => $isOnHold,
            'shop_id' => $order->tiktok_shop_id,
            'notification_id' => $payload['tts_notification_id'] ?? null
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
     * Xử lý webhook thay đổi status order (endpoint riêng)
     * Format webhook từ TikTok:
     * {
     *   "type": 1,
     *   "tts_notification_id": "7327112393057371910",
     *   "shop_id": "7494049642642441621",
     *   "timestamp": 1644412885,
     *   "data": {
     *     "order_id": "576486316948490001",
     *     "order_status": "UNPAID",
     *     "is_on_hold_order": false,
     *     "update_time": 1644412885
     *   }
     * }
     */
    public function handleOrderStatusChange(Request $request)
    {
        Log::info('TikTok Order Status Change Webhook received', [
            'headers' => $request->headers->all(),
            'body' => $request->all()
        ]);

        try {
            // Bỏ qua signature verification vì TikTok webhook không gửi kèm signature
            Log::info('Processing order status change webhook without signature verification');

            $payload = $request->all();
            
            // Kiểm tra format webhook
            if (!isset($payload['data']) || !is_array($payload['data'])) {
                Log::warning('Invalid webhook format: missing data field', ['payload' => $payload]);
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid webhook format: missing data field'
                ], 400);
            }

            // Extract dữ liệu từ data object
            $data = $payload['data'];
            $shopId = $payload['shop_id'] ?? null;
            $notificationId = $payload['tts_notification_id'] ?? null;
            $timestamp = $payload['timestamp'] ?? null;

            // Chuẩn bị payload cho handleOrderStatusUpdate
            $orderPayload = [
                'order_id' => $data['order_id'] ?? null,
                'order_status' => $data['order_status'] ?? null,
                'shop_id' => $shopId,
                'update_time' => $data['update_time'] ?? $timestamp,
                'is_on_hold_order' => $data['is_on_hold_order'] ?? false,
                'tts_notification_id' => $notificationId,
                'type' => $payload['type'] ?? null
            ];

            // Xử lý thay đổi status
            $this->handleOrderStatusUpdate($orderPayload);

            return response()->json([
                'status' => 'success',
                'message' => 'Order status updated successfully',
                'notification_id' => $notificationId,
                'timestamp' => now()->toISOString()
            ], 200);
        } catch (\Exception $e) {
            Log::error('Order status webhook processing error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'payload' => $request->all()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Internal server error',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Xử lý Order Status Change (type: 1) từ handleWebhook
     */
    private function handleOrderStatusChangeByType(array $payload)
    {
        Log::info('Processing Order Status Change webhook (type: 1)', [
            'notification_id' => $payload['tts_notification_id'] ?? null,
            'shop_id' => $payload['shop_id'] ?? null
        ]);

        // Kiểm tra format webhook
        if (!isset($payload['data']) || !is_array($payload['data'])) {
            Log::warning('Invalid webhook format: missing data field', ['payload' => $payload]);
            return;
        }

        // Extract dữ liệu từ data object
        $data = $payload['data'];
        $shopId = $payload['shop_id'] ?? null;
        $notificationId = $payload['tts_notification_id'] ?? null;
        $timestamp = $payload['timestamp'] ?? null;

        // Chuẩn bị payload cho handleOrderStatusUpdate
        $orderPayload = [
            'order_id' => $data['order_id'] ?? null,
            'order_status' => $data['order_status'] ?? null,
            'shop_id' => $shopId,
            'update_time' => $data['update_time'] ?? $timestamp,
            'is_on_hold_order' => $data['is_on_hold_order'] ?? false,
            'tts_notification_id' => $notificationId,
            'type' => $payload['type'] ?? null
        ];

        // Xử lý thay đổi status
        $this->handleOrderStatusUpdate($orderPayload);
    }

    /**
     * Xử lý Auth Expire webhook (type: 7)
     * TikTok sẽ gửi thông báo 30 ngày trước khi authorization hết hạn
     * và gửi mỗi ngày lúc 0:00 cho đến khi seller re-authorize
     */
    private function handleAuthExpire(array $payload)
    {
        Log::warning('TikTok Auth Expire webhook received (type: 7)', [
            'notification_id' => $payload['tts_notification_id'] ?? null,
            'shop_id' => $payload['shop_id'] ?? null,
            'timestamp' => $payload['timestamp'] ?? null,
            'data' => $payload['data'] ?? null
        ]);

        try {
            $shopId = $payload['shop_id'] ?? null;
            $data = $payload['data'] ?? [];
            $expireDate = $data['expire_date'] ?? $data['expires_at'] ?? null;
            $daysUntilExpire = $data['days_until_expire'] ?? null;

            if (!$shopId) {
                Log::warning('Missing shop_id in Auth Expire webhook', ['payload' => $payload]);
                return;
            }

            // Tìm shop trong database
            $shop = TikTokShop::where('shop_id', $shopId)->first();

            if (!$shop) {
                Log::warning('Shop not found for Auth Expire webhook', ['shop_id' => $shopId]);
                return;
            }

            // Lưu thông tin cảnh báo vào database hoặc log
            Log::critical('TikTok Shop Authorization Expiring', [
                'shop_id' => $shopId,
                'shop_name' => $shop->shop_name ?? 'N/A',
                'expire_date' => $expireDate,
                'days_until_expire' => $daysUntilExpire,
                'notification_id' => $payload['tts_notification_id'] ?? null
            ]);

            // TODO: Có thể thêm logic để:
            // 1. Gửi email cảnh báo cho admin/seller
            // 2. Lưu vào bảng notifications
            // 3. Tạo task để nhắc nhở seller re-authorize
            // event(new AuthExpiringWarning($shop, $expireDate, $daysUntilExpire));

        } catch (\Exception $e) {
            Log::error('Error processing Auth Expire webhook', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'payload' => $payload
            ]);
        }
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
