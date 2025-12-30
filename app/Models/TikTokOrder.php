<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TikTokOrder extends Model
{
    use HasFactory;

    protected $table = 'tiktok_orders';

    protected $fillable = [
        'tiktok_shop_id',
        'order_id',
        'order_number',
        'order_status',
        'buyer_user_id',
        'buyer_username',
        'shipping_type',
        'is_buyer_request_cancel',
        'warehouse_id',
        'warehouse_name',
        'create_time',
        'update_time',
        'order_amount',
        'currency',
        'shipping_fee',
        'total_amount',
        'order_data',
        'raw_response',
        'sync_status',
        'last_synced_at',
    ];

    protected $casts = [
        'order_data' => 'array',
        'raw_response' => 'array',
        'create_time' => 'datetime',
        'update_time' => 'datetime',
        'last_synced_at' => 'datetime',
        'is_buyer_request_cancel' => 'boolean',
        'order_amount' => 'decimal:2',
        'shipping_fee' => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];

    /**
     * Relationship với TikTokShop
     */
    public function tiktokShop(): BelongsTo
    {
        return $this->belongsTo(TikTokShop::class, 'tiktok_shop_id');
    }

    /**
     * Alias relationship cho shop
     */
    public function shop(): BelongsTo
    {
        return $this->tiktokShop();
    }

    /**
     * Lấy màu sắc cho trạng thái đơn hàng
     */
    public function getStatusColor(): string
    {
        return match ($this->order_status) {
            'UNPAID' => 'red',
            'AWAITING_SHIPMENT' => 'orange',
            'AWAITING_COLLECTION' => 'blue',
            'IN_TRANSIT' => 'purple',
            'DELIVERED' => 'green',
            'CANCELLED' => 'gray',
            'REFUNDED' => 'indigo',
            default => 'gray'
        };
    }

    /**
     * Lấy text hiển thị cho trạng thái đơn hàng
     */
    public function getStatusText(): string
    {
        return match ($this->order_status) {
            'UNPAID' => 'Unpaid',
            'AWAITING_SHIPMENT' => 'Awaiting shipment',
            'AWAITING_COLLECTION' => 'Awaiting pickup',
            'IN_TRANSIT' => 'In transit',
            'DELIVERED' => 'Delivered',
            'CANCELLED' => 'Cancelled',
            'REFUNDED' => 'Refunded',
            default => $this->order_status ?: 'Unknown'
        };
    }

    /**
     * Lấy CSS classes cho status badge theo thiết kế mới
     */
    public function getStatusClasses(): string
    {
        return match ($this->order_status) {
            'UNPAID' => 'bg-red-700 text-red-200',
            'AWAITING_SHIPMENT' => 'bg-orange-700 text-orange-200',
            'AWAITING_COLLECTION' => 'bg-blue-700 text-blue-200',
            'IN_TRANSIT' => 'bg-purple-700 text-purple-200',
            'DELIVERED' => 'bg-green-700 text-green-200',
            'CANCELLED' => 'bg-gray-700 text-gray-200',
            'REFUNDED' => 'bg-indigo-700 text-indigo-200',
            default => 'bg-gray-700 text-gray-200'
        };
    }

    /**
     * Lấy icon cho trạng thái đơn hàng
     */
    public function getStatusIcon(): string
    {
        return match ($this->order_status) {
            'UNPAID' => 'fas fa-times-circle',
            'AWAITING_SHIPMENT' => 'fas fa-clock',
            'AWAITING_COLLECTION' => 'fas fa-hand-paper',
            'IN_TRANSIT' => 'fas fa-truck',
            'DELIVERED' => 'fas fa-check-circle',
            'CANCELLED' => 'fas fa-ban',
            'REFUNDED' => 'fas fa-undo',
            default => 'fas fa-question-circle'
        };
    }

    /**
     * Scope để lọc theo trạng thái đơn hàng
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('order_status', $status);
    }

    /**
     * Scope để lọc theo shop
     */
    public function scopeByShop($query, int $shopId)
    {
        return $query->where('tiktok_shop_id', $shopId);
    }

    /**
     * Scope để lọc theo khoảng thời gian tạo
     */
    public function scopeByCreateTimeRange($query, $startTime, $endTime)
    {
        return $query->whereBetween('create_time', [$startTime, $endTime]);
    }

    /**
     * Scope để lọc theo khoảng thời gian cập nhật
     */
    public function scopeByUpdateTimeRange($query, $startTime, $endTime)
    {
        return $query->whereBetween('update_time', [$startTime, $endTime]);
    }

    /**
     * Scope để lọc đơn hàng chưa đồng bộ
     */
    public function scopeNotSynced($query)
    {
        return $query->where('sync_status', '!=', 'synced');
    }

    /**
     * Scope để lọc đơn hàng đã đồng bộ
     */
    public function scopeSynced($query)
    {
        return $query->where('sync_status', 'synced');
    }

    /**
     * Lấy trạng thái đơn hàng bằng tiếng Việt
     */
    public function getStatusInVietnameseAttribute(): string
    {
        return match ($this->order_status) {
            'UNPAID' => 'Unpaid',
            'ON_HOLD' => 'On Hold',
            'AWAITING_SHIPMENT' => 'Awaiting Shipment',
            'PARTIALLY_SHIPPING' => 'Partially Shipped',
            'AWAITING_COLLECTION' => 'Awaiting Collection',
            'IN_TRANSIT' => 'In Transit',
            'DELIVERED' => 'Delivered',
            'COMPLETED' => 'Completed',
            'CANCELLED' => 'Cancelled',
            default => $this->order_status
        };
    }

    /**
     * Lấy phương thức vận chuyển bằng tiếng Việt
     */
    public function getShippingTypeInVietnameseAttribute(): string
    {
        return match ($this->shipping_type) {
            'TIKTOK' => 'TikTok Logistics',
            'SELLER' => 'Người bán tự vận chuyển',
            default => $this->shipping_type
        };
    }

    /**
     * Kiểm tra đơn hàng có yêu cầu hủy không
     */
    public function hasBuyerCancelRequest(): bool
    {
        return $this->is_buyer_request_cancel;
    }

    /**
     * Lấy thông tin chi tiết đơn hàng từ order_data
     */
    public function getOrderDetails(): ?array
    {
        return $this->order_data;
    }

    /**
     * Lấy danh sách sản phẩm trong đơn hàng
     */
    public function getOrderItems(): array
    {
        $orderData = $this->getOrderDetails();
        return $orderData['item_list'] ?? [];
    }

    /**
     * Lấy thông tin địa chỉ giao hàng
     */
    public function getShippingAddress(): ?array
    {
        $orderData = $this->getOrderDetails();
        return $orderData['shipping_info'] ?? null;
    }

    /**
     * Lấy thông tin người mua
     */
    public function getBuyerInfo(): ?array
    {
        $orderData = $this->getOrderDetails();
        return $orderData['buyer_info'] ?? null;
    }

    /**
     * Cập nhật trạng thái đồng bộ
     */
    public function markAsSynced(): void
    {
        $this->update([
            'sync_status' => 'synced',
            'last_synced_at' => now()
        ]);
    }

    /**
     * Đánh dấu lỗi đồng bộ
     */
    public function markSyncError(string $error): void
    {
        $this->update([
            'sync_status' => 'error',
            'sync_error' => $error,
            'last_synced_at' => now()
        ]);
    }

    /**
     * Lấy SLA date từ order_data (ưu tiên các field từ TikTok API)
     */
    public function getSlaDate(): ?\Carbon\Carbon
    {
        $orderData = $this->order_data ?? [];

        // Ưu tiên các field từ TikTok API
        $slaTime = null;
        if (isset($orderData['rts_sla_time'])) {
            $slaTime = $orderData['rts_sla_time'];
        } elseif (isset($orderData['tts_sla_time'])) {
            $slaTime = $orderData['tts_sla_time'];
        } elseif (isset($orderData['collection_due_time'])) {
            $slaTime = $orderData['collection_due_time'];
        } elseif (isset($orderData['cancel_order_sla_time'])) {
            $slaTime = $orderData['cancel_order_sla_time'];
        }

        if ($slaTime) {
            // Nếu là timestamp (số), convert sang datetime
            if (is_numeric($slaTime)) {
                return \Carbon\Carbon::createFromTimestamp($slaTime);
            }
            // Nếu là string, parse nó
            try {
                return \Carbon\Carbon::parse($slaTime);
            } catch (\Exception $e) {
                return null;
            }
        }

        // Fallback: tính từ create_time + 3 ngày nếu không có dữ liệu từ API
        if ($this->create_time) {
            return $this->create_time->copy()->addDays(3);
        }

        return null;
    }

    /**
     * Lấy Auto Cancel date từ order_data (ưu tiên các field từ TikTok API)
     */
    public function getAutoCancelDate(): ?\Carbon\Carbon
    {
        $orderData = $this->order_data ?? [];

        // Ưu tiên các field từ TikTok API
        $cancelTime = null;
        if (isset($orderData['cancel_order_sla_time'])) {
            $cancelTime = $orderData['cancel_order_sla_time'];
        } elseif (isset($orderData['collection_due_time'])) {
            $cancelTime = $orderData['collection_due_time'];
        } elseif (isset($orderData['cancel_time'])) {
            $cancelTime = $orderData['cancel_time'];
        }

        if ($cancelTime) {
            // Nếu là timestamp (số), convert sang datetime
            if (is_numeric($cancelTime)) {
                return \Carbon\Carbon::createFromTimestamp($cancelTime);
            }
            // Nếu là string, parse nó
            try {
                return \Carbon\Carbon::parse($cancelTime);
            } catch (\Exception $e) {
                return null;
            }
        }

        // Fallback: tính từ create_time + 10 ngày nếu không có dữ liệu từ API
        if ($this->create_time) {
            return $this->create_time->copy()->addDays(10);
        }

        return null;
    }
}
