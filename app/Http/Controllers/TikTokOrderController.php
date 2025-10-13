<?php

namespace App\Http\Controllers;

use App\Models\TikTokOrder;
use App\Models\TikTokShop;
use App\Services\TikTokOrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class TikTokOrderController extends Controller
{
    protected $orderService;

    public function __construct(TikTokOrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    /**
     * Hiển thị danh sách đơn hàng
     */
    public function index(Request $request)
    {
        try {
            $user = Auth::user();
            $team = $user->team;
            
            if (!$team) {
                return redirect()->back()->with('error', 'Bạn không thuộc team nào');
            }

            // Lấy filters từ request
            $filters = $this->buildFilters($request);
            
            // Xác định shops có thể xem được
            $shops = $this->getAccessibleShops($user, $team);
            
            if ($shops->isEmpty()) {
                return view('tiktok.orders.index', [
                    'orders' => collect(),
                    'shops' => collect(),
                    'filters' => $filters,
                    'totalCount' => 0,
                    'userRole' => $user->getPrimaryRoleNameAttribute()
                ]);
            }

            // Lấy đơn hàng từ database
            $orders = $this->getOrdersForShops($shops, $filters);
            
            // Pagination
            $perPage = $request->get('per_page', 20);
            $orders = $orders->paginate($perPage)->withQueryString();

            return view('tiktok.orders.index', [
                'orders' => $orders,
                'shops' => $shops,
                'filters' => $filters,
                'totalCount' => $orders->total(),
                'userRole' => $user->getPrimaryRoleNameAttribute()
            ]);

        } catch (\Exception $e) {
            Log::error('Error loading orders page', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);
            
            return redirect()->back()->with('error', 'Có lỗi xảy ra khi tải danh sách đơn hàng');
        }
    }

    /**
     * Hiển thị chi tiết đơn hàng
     */
    public function show($orderId)
    {
        try {
            $user = Auth::user();
            $team = $user->team;
            
            if (!$team) {
                return redirect()->back()->with('error', 'Bạn không thuộc team nào');
            }

            // Tìm đơn hàng
            $order = TikTokOrder::with('shop')->findOrFail($orderId);
            
            // Kiểm tra quyền truy cập
            if (!$this->canAccessOrder($user, $order)) {
                return redirect()->route('tiktok.orders.index')
                    ->with('error', 'Bạn không có quyền xem đơn hàng này');
            }

            return view('tiktok.orders.show', [
                'order' => $order,
                'userRole' => $user->getPrimaryRoleNameAttribute()
            ]);

        } catch (\Exception $e) {
            Log::error('Error loading order details', [
                'user_id' => Auth::id(),
                'order_id' => $orderId,
                'error' => $e->getMessage()
            ]);
            
            return redirect()->back()->with('error', 'Có lỗi xảy ra khi tải chi tiết đơn hàng');
        }
    }

    /**
     * Đồng bộ đơn hàng từ TikTok API
     */
    public function sync(Request $request)
    {
        try {
            $user = Auth::user();
            $team = $user->team;
            
            if (!$team) {
                return redirect()->back()->with('error', 'Bạn không thuộc team nào');
            }

            $shopId = $request->get('shop_id');
            if (!$shopId) {
                return redirect()->back()->with('error', 'Vui lòng chọn shop');
            }

            $shop = TikTokShop::where('id', $shopId)
                ->where('team_id', $team->id)
                ->first();

            if (!$shop) {
                return redirect()->back()->with('error', 'Shop không tồn tại hoặc không thuộc team của bạn');
            }

            // Kiểm tra quyền truy cập shop
            if (!$this->canAccessShop($user, $shop)) {
                return redirect()->back()->with('error', 'Bạn không có quyền truy cập shop này');
            }

            // Đồng bộ đơn hàng
            $result = $this->orderService->syncAllOrders($shop);
            
            if ($result['success']) {
                return redirect()->back()->with('success', 
                    "Đồng bộ thành công {$result['total_orders']} đơn hàng từ shop {$shop->shop_name}");
            } else {
                return redirect()->back()->with('error', 'Lỗi khi đồng bộ đơn hàng: ' . $result['message']);
            }

        } catch (\Exception $e) {
            Log::error('Error syncing orders', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);
            
            return redirect()->back()->with('error', 'Có lỗi xảy ra khi đồng bộ đơn hàng');
        }
    }

    /**
     * Xây dựng filters từ request
     */
    private function buildFilters(Request $request): array
    {
        $filters = [];
        
        // Trạng thái đơn hàng
        if ($request->filled('status')) {
            $filters['order_status'] = $request->get('status');
        }
        
        // Shop
        if ($request->filled('shop_id')) {
            $filters['shop_id'] = $request->get('shop_id');
        }
        
        // Khoảng thời gian
        if ($request->filled('date_from')) {
            $filters['create_time_ge'] = strtotime($request->get('date_from'));
        }
        
        if ($request->filled('date_to')) {
            $filters['create_time_lt'] = strtotime($request->get('date_to'));
        }
        
        // Tìm kiếm theo order ID hoặc buyer
        if ($request->filled('search')) {
            $filters['search'] = $request->get('search');
        }
        
        return $filters;
    }

    /**
     * Lấy danh sách shops có thể truy cập
     */
    private function getAccessibleShops($user, $team)
    {
        if ($user->hasRole('team-admin')) {
            // Team admin có thể xem tất cả shops trong team
            return TikTokShop::where('team_id', $team->id)
                ->where('status', 'active')
                ->with('integration')
                ->get();
        } else {
            // Seller chỉ xem được shops của mình
            return TikTokShop::where('team_id', $team->id)
                ->where('status', 'active')
                ->whereHas('teamMembers', function($query) use ($user) {
                    $query->where('user_id', $user->id);
                })
                ->with('integration')
                ->get();
        }
    }

    /**
     * Lấy đơn hàng cho các shops
     */
    private function getOrdersForShops($shops, $filters)
    {
        $shopIds = $shops->pluck('id')->toArray();
        
        $query = TikTokOrder::with(['shop', 'shop.integration'])
            ->whereIn('tiktok_shop_id', $shopIds);

        // Filter theo shop
        if (isset($filters['shop_id'])) {
            $query->where('tiktok_shop_id', $filters['shop_id']);
        }

        // Filter theo trạng thái
        if (isset($filters['order_status'])) {
            $query->where('order_status', $filters['order_status']);
        }

        // Filter theo thời gian
        if (isset($filters['create_time_ge'])) {
            $query->where('create_time', '>=', date('Y-m-d H:i:s', $filters['create_time_ge']));
        }

        if (isset($filters['create_time_lt'])) {
            $query->where('create_time', '<=', date('Y-m-d H:i:s', $filters['create_time_lt']));
        }

        // Tìm kiếm
        if (isset($filters['search'])) {
            $searchTerm = $filters['search'];
            $query->where(function($q) use ($searchTerm) {
                $q->where('order_id', 'like', "%{$searchTerm}%")
                  ->orWhere('order_number', 'like', "%{$searchTerm}%")
                  ->orWhere('buyer_username', 'like', "%{$searchTerm}%");
            });
        }

        return $query->orderBy('create_time', 'desc');
    }

    /**
     * Kiểm tra quyền truy cập đơn hàng
     */
    private function canAccessOrder($user, $order)
    {
        if ($user->hasRole('team-admin')) {
            // Team admin có thể xem tất cả đơn hàng trong team
            return $order->shop && $order->shop->team_id === $user->team_id;
        } else {
            // Seller chỉ xem được đơn hàng của shops mà họ có quyền
            return $order->shop && 
                   $order->shop->team_id === $user->team_id &&
                   $order->shop->teamMembers()->where('user_id', $user->id)->exists();
        }
    }

    /**
     * Kiểm tra quyền truy cập shop
     */
    private function canAccessShop($user, $shop)
    {
        if ($user->hasRole('team-admin')) {
            return $shop->team_id === $user->team_id;
        } else {
            return $shop->team_id === $user->team_id &&
                   $shop->teamMembers()->where('user_id', $user->id)->exists();
        }
    }

    /**
     * API endpoint để lấy đơn hàng (cho AJAX)
     */
    public function apiOrders(Request $request)
    {
        try {
            $user = Auth::user();
            $team = $user->team;
            
            if (!$team) {
                return response()->json(['error' => 'Bạn không thuộc team nào'], 403);
            }

            $filters = $this->buildFilters($request);
            $shops = $this->getAccessibleShops($user, $team);
            
            if ($shops->isEmpty()) {
                return response()->json([
                    'orders' => [],
                    'total' => 0
                ]);
            }

            $orders = $this->getOrdersForShops($shops, $filters);
            $perPage = $request->get('per_page', 20);
            $orders = $orders->paginate($perPage);

            return response()->json([
                'orders' => $orders->items(),
                'total' => $orders->total(),
                'current_page' => $orders->currentPage(),
                'last_page' => $orders->lastPage(),
                'per_page' => $orders->perPage()
            ]);

        } catch (\Exception $e) {
            Log::error('API orders error', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);
            
            return response()->json(['error' => 'Có lỗi xảy ra'], 500);
        }
    }
}
