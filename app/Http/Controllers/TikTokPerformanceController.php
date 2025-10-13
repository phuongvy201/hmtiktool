<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\TikTokShop;
use App\Services\TikTokShopPerformanceService;

class TikTokPerformanceController extends Controller
{
    private TikTokShopPerformanceService $performanceService;

    public function __construct(TikTokShopPerformanceService $performanceService)
    {
        $this->performanceService = $performanceService;
    }

    /**
     * Hiển thị GMV Dashboard
     */
    public function index(Request $request)
    {
        try {
            $user = Auth::user();

            // Lấy danh sách shops mà user có quyền truy cập
            $shops = $this->getAccessibleShops($user);

            // Lấy tham số filter
            $filters = [
                'shop_id' => $request->get('shop_id'),
                'start_date' => $request->get('start_date', date('Y-m-d', strtotime('-7 days'))),
                'end_date' => $request->get('end_date', date('Y-m-d')),
                'granularity' => $request->get('granularity', '1D'),
                'with_comparison' => $request->get('with_comparison', true),
                'currency' => $request->get('currency', 'USD')
            ];

            $performanceData = null;
            $selectedShop = null;

            // Nếu có chọn shop cụ thể
            if ($filters['shop_id']) {
                $selectedShop = $shops->find($filters['shop_id']);
                if ($selectedShop) {
                    $result = $this->performanceService->getShopPerformance($selectedShop, $filters);
                    if ($result['success']) {
                        $performanceData = $result['data'];
                    }
                }
            } else {
                // Nếu không chọn shop nào, sử dụng shop đầu tiên
                $firstShop = $shops->first();
                if ($firstShop) {
                    $result = $this->performanceService->getShopPerformance($firstShop, $filters);
                    if ($result['success']) {
                        $performanceData = $result['data'];
                    }
                }
            }

            return view('tiktok.performance.index', compact(
                'shops',
                'performanceData',
                'selectedShop',
                'filters'
            ));
        } catch (\Exception $e) {
            Log::error('Error in TikTokPerformanceController@index', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()->with('error', 'Có lỗi xảy ra khi tải dữ liệu performance');
        }
    }

    /**
     * Force refresh dữ liệu từ API
     */
    public function refresh(Request $request)
    {
        try {
            $user = Auth::user();
            $shops = $this->getAccessibleShops($user);

            $shopId = $request->get('shop_id');
            $shop = $shops->find($shopId);

            if (!$shop) {
                return response()->json([
                    'success' => false,
                    'message' => 'Shop không tồn tại hoặc không có quyền truy cập'
                ], 404);
            }

            $filters = [
                'start_date' => $request->get('start_date', date('Y-m-d', strtotime('-7 days'))),
                'end_date' => $request->get('end_date', date('Y-m-d')),
                'granularity' => $request->get('granularity', '1D'),
                'with_comparison' => $request->get('with_comparison', true),
                'currency' => $request->get('currency', 'USD')
            ];

            $result = $this->performanceService->getShopPerformance($shop, $filters);

            return response()->json($result);
        } catch (\Exception $e) {
            Log::error('Error in TikTokPerformanceController@refresh', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi refresh dữ liệu'
            ], 500);
        }
    }

    /**
     * API endpoint để lấy dữ liệu performance
     */
    public function getPerformanceData(Request $request)
    {
        try {
            $user = Auth::user();
            $shops = $this->getAccessibleShops($user);

            $filters = [
                'shop_id' => $request->get('shop_id'),
                'start_date' => $request->get('start_date', date('Y-m-d', strtotime('-7 days'))),
                'end_date' => $request->get('end_date', date('Y-m-d')),
                'granularity' => $request->get('granularity', '1D'),
                'with_comparison' => $request->get('with_comparison', true),
                'currency' => $request->get('currency', 'USD')
            ];

            if (!$filters['shop_id']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vui lòng chọn shop'
                ]);
            }

            $shop = $shops->find($filters['shop_id']);
            if (!$shop) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy shop'
                ]);
            }

            $result = $this->performanceService->getShopPerformance($shop, $filters);

            return response()->json($result);
        } catch (\Exception $e) {
            Log::error('Error in TikTokPerformanceController@getPerformanceData', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi lấy dữ liệu performance'
            ]);
        }
    }

    /**
     * Lấy danh sách shops mà user có quyền truy cập
     */
    private function getAccessibleShops($user)
    {
        if ($user->hasRole('system-admin')) {
            // System admin có thể xem tất cả shops
            return TikTokShop::with('integration')->get();
        } elseif ($user->hasRole('team-admin')) {
            // Team admin có thể xem tất cả shops trong team của mình
            return TikTokShop::where('team_id', $user->team_id)
                ->with('integration')
                ->get();
        } elseif ($user->hasRole('seller')) {
            // Seller chỉ xem được shops được assign cho mình
            return TikTokShop::whereHas('sellers', function ($query) use ($user) {
                $query->where('user_id', $user->id)
                    ->where('is_active', true);
            })->with('integration')->get();
        }

        return collect();
    }
}
