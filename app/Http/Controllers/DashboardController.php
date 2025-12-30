<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductTemplate;
use App\Models\TikTokShop;
use App\Models\TikTokOrder;
use App\Models\TikTokShopIntegration;
use App\Services\TikTokAnalyticsCacheService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class DashboardController extends Controller
{

    public function index(Request $request)
    {
        $user = Auth::user();
        $team = $user->team;

        // Thống kê cho component quản lý sản phẩm
        $templateCount = 0;
        $productCount = 0;

        if ($team) {
            $templateCount = ProductTemplate::byTeam($team->id)->count();
            $productCount = Product::byTeam($team->id)->count();
        }

        // Lấy thống kê orders theo shop với filter thời gian
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        $shopStats = $this->getOrderStatistics($user, $team, $startDate, $endDate);

        // Lấy danh sách tích hợp TikTok Shop của team
        $integrations = [];
        if ($team) {
            $integrations = TikTokShopIntegration::where('team_id', $team->id)
                ->orderBy('created_at', 'desc')
                ->get();
        }

        // Nếu là AJAX request, trả về JSON
        if ($request->ajax()) {
            return response()->json($shopStats);
        }

        return view('dashboard', compact('templateCount', 'productCount', 'shopStats', 'integrations'));
    }

    /**
     * Lấy thống kê đơn hàng theo shop với filter thời gian
     */
    private function getOrderStatistics($user, $team, $startDate = null, $endDate = null)
    {
        if (!$team) {
            return [
                'shops' => [],
                'total' => [
                    'total_orders' => 0,
                    'success_orders' => 0,
                    'cancel_orders' => 0,
                ]
            ];
        }

        // Xác định shops có thể xem được với eager loading relationships
        $shops = $this->getAccessibleShops($user, $team);

        if ($shops->isEmpty()) {
            return [
                'shops' => [],
                'total' => [
                    'total_orders' => 0,
                    'success_orders' => 0,
                    'cancel_orders' => 0,
                ]
            ];
        }

        $stats = [];
        $shopIds = $shops->pluck('id')->toArray();

        // Query orders cho tất cả shops
        $ordersQuery = TikTokOrder::whereIn('tiktok_shop_id', $shopIds);

        // Áp dụng filter thời gian nếu có
        if ($startDate) {
            $ordersQuery->where('create_time', '>=', Carbon::parse($startDate)->startOfDay());
        }
        if ($endDate) {
            $ordersQuery->where('create_time', '<=', Carbon::parse($endDate)->endOfDay());
        }

        foreach ($shops as $shop) {
            // Lấy orders của shop với filter thời gian
            $shopOrdersQuery = (clone $ordersQuery)->where('tiktok_shop_id', $shop->id);

            // Tổng số đơn hàng
            $totalOrders = (clone $shopOrdersQuery)->count();

            // Success orders (DELIVERED)
            $successOrders = (clone $shopOrdersQuery)->where('order_status', 'DELIVERED')->count();

            // Cancel orders (CANCELLED)
            $cancelOrders = (clone $shopOrdersQuery)->where('order_status', 'CANCELLED')->count();

            // Lấy danh sách staffs (sellers/users) của shop với tên
            $staffs = $shop->teamMembers()->with('user')->get();
            $staffsNames = $staffs->map(function ($seller) {
                return $seller->user->name ?? 'N/A';
            })->filter()->toArray();

            // Lấy profile name từ integration
            $profileName = $shop->integration->name ?? 'N/A';

            $stats[] = [
                'shop' => $shop,
                'shop_id' => $shop->id,
                'shop_name' => $shop->shop_name,
                'profile' => $profileName,
                'total_orders' => $totalOrders,
                'success_orders' => $successOrders,
                'cancel_orders' => $cancelOrders,
                'staffs_count' => $staffs->count(),
                'staffs_names' => $staffsNames,
            ];
        }

        // Tính tổng
        $totalAllOrders = collect($stats)->sum('total_orders');
        $totalSuccess = collect($stats)->sum('success_orders');
        $totalCancel = collect($stats)->sum('cancel_orders');

        return [
            'shops' => $stats,
            'total' => [
                'total_orders' => $totalAllOrders,
                'success_orders' => $totalSuccess,
                'cancel_orders' => $totalCancel,
            ]
        ];
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
                ->with(['integration', 'sellers.user'])
                ->get();
        } else {
            // Seller chỉ xem được shops của mình
            return TikTokShop::where('team_id', $team->id)
                ->where('status', 'active')
                ->whereHas('teamMembers', function ($query) use ($user) {
                    $query->where('user_id', $user->id);
                })
                ->with(['integration', 'sellers.user'])
                ->get();
        }
    }

    /**
     * Lấy số lượng listing đang active từ TikTok API
     * Sử dụng method từ TikTokShopAnalyticsController
     */
    private function getActiveListings($shop)
    {
        try {
            $integration = $shop->integration;

            if (!$integration || !$integration->isActive()) {
                return 0;
            }

            // Sử dụng reflection để gọi private method từ TikTokShopAnalyticsController
            $analyticsController = new \App\Http\Controllers\TikTokShopAnalyticsController();
            $reflection = new \ReflectionClass($analyticsController);
            $method = $reflection->getMethod('callTikTokProductAPI');
            $method->setAccessible(true);

            $response = $method->invoke($analyticsController, $shop, [
                'page_size' => 100,
                'page_number' => 1,
                'product_status' => 'ONLINE'
            ]);

            if ($response['success'] && isset($response['data']['products'])) {
                $activeCount = 0;
                foreach ($response['data']['products'] as $product) {
                    if (($product['status'] ?? '') === 'ACTIVATE') {
                        $activeCount++;
                    }
                }
                return $activeCount;
            }

            return 0;
        } catch (\Exception $e) {
            Log::error('Error getting active listings in dashboard', [
                'shop_id' => $shop->id,
                'error' => $e->getMessage()
            ]);
            return 0;
        }
    }
}
