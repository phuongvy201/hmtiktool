<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\TikTokOrder;
use App\Models\TikTokShop;
use App\Services\TikTokShippingService;
use App\Services\TikTokOrderService;

class TikTokShippingController extends Controller
{
    private TikTokShippingService $tiktokShippingService;
    private TikTokOrderService $tiktokOrderService;

    public function __construct(TikTokShippingService $tiktokShippingService, TikTokOrderService $tiktokOrderService)
    {
        $this->tiktokShippingService = $tiktokShippingService;
        $this->tiktokOrderService = $tiktokOrderService;
    }

    /**
     * Lấy danh sách đơn vị vận chuyển cho một đơn hàng
     */
    public function getShippingProviders(Request $request, $orderId)
    {
        try {
            $user = Auth::user();

            // Tìm đơn hàng
            $order = TikTokOrder::with('shop')->findOrFail($orderId);

            // Kiểm tra quyền truy cập
            if (!$this->canAccessOrder($user, $order)) {
                return response()->json([
                    'success' => false,
                    'error' => 'Không có quyền truy cập đơn hàng này'
                ], 403);
            }

            // Lấy delivery_option_id từ order_data
            $deliveryOptionId = $order->order_data['delivery_option_id'] ?? null;

            if (!$deliveryOptionId) {
                return response()->json([
                    'success' => false,
                    'error' => 'Không tìm thấy delivery_option_id trong đơn hàng'
                ], 400);
            }

            Log::info('Getting shipping providers for order', [
                'order_id' => $orderId,
                'delivery_option_id' => $deliveryOptionId,
                'shop_id' => $order->shop->id
            ]);

            // Gọi TikTok API để lấy shipping providers
            $result = TikTokShippingService::getShippingProviders($order->shop, $deliveryOptionId);

            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'data' => [
                        'order_id' => $orderId,
                        'delivery_option_id' => $deliveryOptionId,
                        'shipping_providers' => $result['data']['shipping_providers'] ?? []
                    ]
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'error' => $result['error']
                ], 400);
            }
        } catch (\Exception $e) {
            Log::error('Error getting shipping providers', [
                'order_id' => $orderId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Có lỗi xảy ra khi lấy danh sách đơn vị vận chuyển'
            ], 500);
        }
    }

    /**
     * Mark package as shipped
     */
    public function markAsShipped(Request $request, $orderId)
    {
        try {
            $user = Auth::user();

            // Validate request
            $request->validate([
                'tracking_number' => 'required|string|max:255',
                'shipping_provider_id' => 'required|string|max:255',
                'order_line_item_ids' => 'sometimes|array',
                'order_line_item_ids.*' => 'string'
            ]);

            // Tìm đơn hàng
            $order = TikTokOrder::with('shop')->findOrFail($orderId);

            // Kiểm tra quyền truy cập
            if (!$this->canAccessOrder($user, $order)) {
                return response()->json([
                    'success' => false,
                    'error' => 'Không có quyền truy cập đơn hàng này'
                ], 403);
            }

            // Kiểm tra trạng thái đơn hàng
            if ($order->order_status !== 'AWAITING_SHIPMENT') {
                return response()->json([
                    'success' => false,
                    'error' => 'Đơn hàng không ở trạng thái chờ giao hàng'
                ], 400);
            }

            Log::info('Marking package as shipped', [
                'database_order_id' => $orderId,
                'tiktok_order_id' => $order->order_id,
                'tracking_number' => $request->tracking_number,
                'shipping_provider_id' => $request->shipping_provider_id,
                'order_line_item_ids' => $request->order_line_item_ids,
                'shop_id' => $order->shop->id
            ]);

            // Gọi TikTok API để mark package as shipped
            $result = TikTokShippingService::markPackageAsShipped(
                $order->shop,
                $order->order_id, // Sử dụng TikTok order_id thay vì database ID
                $request->tracking_number,
                $request->shipping_provider_id,
                $request->order_line_item_ids ?? []
            );

            if ($result['success']) {
                // Cập nhật trạng thái đơn hàng và tracking info trong database
                $orderData = $order->order_data;
                $orderData['tracking_number'] = $request->tracking_number;
                $orderData['shipping_provider_id'] = $request->shipping_provider_id;

                // Lấy tên shipping provider từ response hoặc từ danh sách providers
                if (isset($result['data']['shipping_provider_name'])) {
                    $orderData['shipping_provider_name'] = $result['data']['shipping_provider_name'];
                } else {
                    // Lấy tên từ request nếu có
                    $orderData['shipping_provider_name'] = $request->input('shipping_provider_name');
                }

                // Chỉ cập nhật tracking info, không thay đổi status
                $order->update([
                    'order_data' => $orderData,
                    'update_time' => now()
                ]);

                // Tìm và cập nhật đơn hàng từ TikTok API sau khi add tracking
                $syncResult = $this->syncOrderFromTikTok($order);

                if ($syncResult['success']) {
                    Log::info('Order synced successfully after marking as shipped', [
                        'database_order_id' => $orderId,
                        'tiktok_order_id' => $order->order_id,
                        'tracking_number' => $request->tracking_number,
                        'new_status' => $syncResult['new_status']
                    ]);
                } else {
                    Log::warning('Failed to sync order after marking as shipped', [
                        'database_order_id' => $orderId,
                        'tiktok_order_id' => $order->order_id,
                        'sync_error' => $syncResult['message']
                    ]);
                }

                Log::info('Package marked as shipped successfully', [
                    'order_id' => $orderId,
                    'tracking_number' => $request->tracking_number
                ]);

                // Lấy status mới nhất sau khi sync
                $order->refresh();
                $newStatus = $order->order_status;

                return response()->json([
                    'success' => true,
                    'message' => $syncResult['success'] ?
                        'Đã đánh dấu gói hàng đã được gửi thành công và đồng bộ thông tin từ TikTok' :
                        'Đã đánh dấu gói hàng đã được gửi thành công (không thể đồng bộ từ TikTok)',
                    'data' => [
                        'order_id' => $orderId,
                        'tracking_number' => $request->tracking_number,
                        'shipping_provider_id' => $request->shipping_provider_id,
                        'new_status' => $newStatus,
                        'synced' => $syncResult['success'],
                        'status_changed' => $syncResult['status_changed'] ?? false,
                        'sync_message' => $syncResult['message'] ?? null
                    ]
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'error' => $result['error']
                ], 400);
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'error' => 'Dữ liệu không hợp lệ',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error marking package as shipped', [
                'order_id' => $orderId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Có lỗi xảy ra khi đánh dấu gói hàng đã gửi'
            ], 500);
        }
    }

    /**
     * Lấy thông tin chi tiết đơn hàng để hiển thị form mark as shipped
     */
    public function getOrderShippingInfo(Request $request, $orderId)
    {
        try {
            $user = Auth::user();

            // Tìm đơn hàng
            $order = TikTokOrder::with('shop')->findOrFail($orderId);

            // Kiểm tra quyền truy cập
            if (!$this->canAccessOrder($user, $order)) {
                return response()->json([
                    'success' => false,
                    'error' => 'Không có quyền truy cập đơn hàng này'
                ], 403);
            }

            // Lấy thông tin sản phẩm trong đơn hàng
            $lineItems = $order->order_data['line_items'] ?? [];
            $deliveryOptionId = $order->order_data['delivery_option_id'] ?? null;

            return response()->json([
                'success' => true,
                'data' => [
                    'order' => [
                        'id' => $order->id,
                        'order_id' => $order->order_id,
                        'order_status' => $order->order_status,
                        'shop_name' => $order->shop->shop_name,
                        'delivery_option_id' => $deliveryOptionId
                    ],
                    'line_items' => $lineItems,
                    'can_mark_shipped' => $order->order_status === 'AWAITING_SHIPMENT' &&
                        ($order->shipping_type === 'SELLER' || !$order->shipping_type)
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting order shipping info', [
                'order_id' => $orderId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Có lỗi xảy ra khi lấy thông tin đơn hàng'
            ], 500);
        }
    }

    /**
     * Đồng bộ đơn hàng từ TikTok API
     */
    private function syncOrderFromTikTok(TikTokOrder $order): array
    {
        try {
            Log::info('Syncing order from TikTok API', [
                'database_order_id' => $order->id,
                'tiktok_order_id' => $order->order_id,
                'shop_id' => $order->shop->id
            ]);

            // Gọi API TikTok để lấy thông tin đơn hàng mới nhất
            $searchResult = $this->tiktokOrderService->searchOrderById(
                $order->shop,
                $order->order_id  // TikTok order ID
            );

            if ($searchResult['success'] && !empty($searchResult['data']['orders'])) {
                $orderData = $searchResult['data']['orders'][0];
                $oldStatus = $order->order_status;
                $newStatus = $orderData['status'] ?? $oldStatus;

                // Cập nhật đơn hàng với thông tin mới từ TikTok
                $order->update([
                    'order_status' => $newStatus,
                    'order_data' => $orderData,
                    'update_time' => isset($orderData['update_time']) ?
                        \Carbon\Carbon::createFromTimestamp($orderData['update_time']) : now(),
                    'last_synced_at' => now()
                ]);

                Log::info('Order synced successfully from TikTok', [
                    'database_order_id' => $order->id,
                    'tiktok_order_id' => $order->order_id,
                    'old_status' => $oldStatus,
                    'new_status' => $newStatus,
                    'status_changed' => $oldStatus !== $newStatus
                ]);

                return [
                    'success' => true,
                    'message' => 'Order synced successfully',
                    'new_status' => $newStatus,
                    'status_changed' => $oldStatus !== $newStatus
                ];
            } else {
                $errorMessage = $searchResult['message'] ?? 'Unknown error';
                Log::warning('Failed to sync order from TikTok', [
                    'database_order_id' => $order->id,
                    'tiktok_order_id' => $order->order_id,
                    'error' => $errorMessage
                ]);

                return [
                    'success' => false,
                    'message' => $errorMessage
                ];
            }
        } catch (\Exception $e) {
            Log::error('Exception during order sync from TikTok', [
                'database_order_id' => $order->id,
                'tiktok_order_id' => $order->order_id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'message' => 'Exception: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Kiểm tra quyền truy cập đơn hàng
     */
    private function canAccessOrder($user, $order)
    {
        if ($user->hasRole('team-admin')) {
            // Team admin có thể truy cập tất cả đơn hàng trong team
            return $order->shop->team_id === $user->team_id;
        } elseif ($user->hasRole('seller')) {
            // Seller chỉ có thể truy cập đơn hàng của shop được assign
            return $order->shop->teamMembers()->where('user_id', $user->id)->exists();
        }

        return false;
    }
}
