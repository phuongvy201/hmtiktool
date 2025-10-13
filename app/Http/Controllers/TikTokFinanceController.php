<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\TikTokShop;
use App\Services\TikTokFinanceService;

class TikTokFinanceController extends Controller
{
    private TikTokFinanceService $tiktokFinanceService;

    public function __construct(TikTokFinanceService $tiktokFinanceService)
    {
        $this->tiktokFinanceService = $tiktokFinanceService;
    }

    /**
     * Hiển thị danh sách payments
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
                'date_from' => $request->get('date_from'),
                'date_to' => $request->get('date_to'),
                'page_size' => $request->get('page_size', 20),
                'sort_order' => $request->get('sort_order', 'DESC')
            ];

            $payments = [];
            $totalAmount = 0;
            $totalReserve = 0;
            $totalSettle = 0;

            // Nếu có chọn shop cụ thể
            if ($filters['shop_id']) {
                $shop = $shops->find($filters['shop_id']);
                if ($shop) {
                    // Lấy từ database thay vì API trực tiếp
                    $result = $this->tiktokFinanceService->getPaymentsFromDatabase($shop, $filters);
                    if ($result['success']) {
                        $payments = $result['data']['payments'] ?? [];
                        $totalAmount = $result['data']['total_amount'] ?? 0;
                        $totalReserve = $result['data']['total_reserve'] ?? 0;
                        $totalSettle = $result['data']['total_settle'] ?? 0;
                    }
                }
            } else {
                // Nếu không chọn shop nào, lấy payments từ tất cả shops mà user có quyền truy cập
                $result = $this->tiktokFinanceService->getPaymentsFromMultipleShops($shops, $filters);
                if ($result['success']) {
                    $payments = $result['data']['payments'] ?? [];
                    $totalAmount = $result['data']['total_amount'] ?? 0;
                    $totalReserve = $result['data']['total_reserve'] ?? 0;
                    $totalSettle = $result['data']['total_settle'] ?? 0;
                }
            }

            return view('tiktok.finance.index', compact(
                'shops',
                'payments',
                'filters',
                'totalAmount',
                'totalReserve',
                'totalSettle'
            ));
        } catch (\Exception $e) {
            Log::error('Error in TikTokFinanceController@index', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()->with('error', 'Có lỗi xảy ra khi tải dữ liệu finance');
        }
    }

    /**
     * Export payments to Excel
     */
    public function export(Request $request)
    {
        try {
            $user = Auth::user();
            $shops = $this->getAccessibleShops($user);

            $filters = [
                'shop_id' => $request->get('shop_id'),
                'date_from' => $request->get('date_from'),
                'date_to' => $request->get('date_to'),
                'page_size' => 100, // Export nhiều hơn
                'sort_order' => $request->get('sort_order', 'DESC')
            ];

            if (!$filters['shop_id']) {
                return redirect()->back()->with('error', 'Vui lòng chọn shop để export');
            }

            $shop = $shops->find($filters['shop_id']);
            if (!$shop) {
                return redirect()->back()->with('error', 'Không tìm thấy shop');
            }

            $result = $this->tiktokFinanceService->getPayments($shop, $filters);
            if (!$result['success']) {
                return redirect()->back()->with('error', 'Không thể lấy dữ liệu payments: ' . $result['message']);
            }

            $payments = $result['data']['payments'] ?? [];

            // Tạo Excel file
            return $this->tiktokFinanceService->exportToExcel($payments, $shop, $filters);
        } catch (\Exception $e) {
            Log::error('Error in TikTokFinanceController@export', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()->with('error', 'Có lỗi xảy ra khi export dữ liệu');
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
