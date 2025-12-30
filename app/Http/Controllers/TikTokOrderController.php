<?php

namespace App\Http\Controllers;

use App\Models\TikTokOrder;
use App\Models\TikTokShop;
use App\Services\TikTokOrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;

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
                    'userRole' => $user->primary_role_name
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
                'userRole' => $user->primary_role_name
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
                'userRole' => $user->primary_role_name
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
                return redirect()->back()->with(
                    'success',
                    "Đồng bộ thành công {$result['total_orders']} đơn hàng từ shop {$shop->shop_name}"
                );
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
                ->whereHas('teamMembers', function ($query) use ($user) {
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
            $query->where(function ($q) use ($searchTerm) {
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
     * Export đơn hàng ra Excel
     */
    public function export(Request $request)
    {
        // Log ngay từ đầu để đảm bảo request đã đến
        Log::info('=== EXPORT REQUEST RECEIVED ===', [
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'user_id' => Auth::id(),
            'query' => $request->query(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);

        // Ghi log vào file riêng để dễ kiểm tra
        file_put_contents(
            storage_path('logs/export-debug.log'),
            date('Y-m-d H:i:s') . " - Export request received\n" .
                "URL: " . $request->fullUrl() . "\n" .
                "User ID: " . Auth::id() . "\n\n",
            FILE_APPEND
        );

        try {
            Log::info('Export request started', ['user_id' => Auth::id(), 'query' => $request->query()]);

            $user = Auth::user();
            if (!$user) {
                Log::error('User not authenticated');
                return redirect()->back()->with('error', 'Bạn chưa đăng nhập');
            }

            $team = $user->team;
            if (!$team) {
                Log::error('User has no team', ['user_id' => $user->id]);
                return redirect()->back()->with('error', 'Bạn không thuộc team nào');
            }

            Log::info('Building filters', ['team_id' => $team->id]);

            // Lấy filters từ request (giống như index)
            $filters = $this->buildFilters($request);

            Log::info('Getting accessible shops', ['filters' => $filters]);

            // Xác định shops có thể xem được
            $shops = $this->getAccessibleShops($user, $team);

            if ($shops->isEmpty()) {
                Log::warning('No shops available for export', ['user_id' => $user->id, 'team_id' => $team->id]);
                return redirect()->back()->with('error', 'Không có shop nào để export');
            }

            Log::info('Getting orders for shops', ['shop_count' => $shops->count()]);

            // Kiểm tra xem có selected orders không
            $selectedOrderIds = $request->input('selected_orders', []);

            if (!empty($selectedOrderIds)) {
                // Chỉ lấy những orders được chọn
                Log::info('Exporting selected orders', ['selected_count' => count($selectedOrderIds)]);
                $orders = TikTokOrder::whereIn('id', $selectedOrderIds)
                    ->whereIn('tiktok_shop_id', $shops->pluck('id'))
                    ->with('shop')
                    ->get();
            } else {
                // Lấy tất cả đơn hàng (không phân trang) và load relationship shop
                $orders = $this->getOrdersForShops($shops, $filters)->with('shop')->get();
            }

            Log::info('Orders retrieved', ['order_count' => $orders->count()]);

            if ($orders->isEmpty()) {
                Log::warning('No orders found for export');
                return redirect()->back()->with('error', 'Không có đơn hàng nào để export');
            }

            Log::info('Starting Excel export', ['order_count' => $orders->count()]);

            // Tạo file Excel
            return $this->exportToExcel($orders, $filters);
        } catch (\Exception $e) {
            Log::error('Error exporting orders to Excel', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()->with('error', 'Có lỗi xảy ra khi export đơn hàng: ' . $e->getMessage());
        }
    }

    /**
     * Export đơn hàng ra Excel file (.xlsx)
     */
    private function exportToExcel($orders, $filters)
    {
        try {
            Log::info('Creating spreadsheet', ['order_count' => $orders->count()]);

            // Tạo Spreadsheet mới
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            Log::info('Spreadsheet created successfully');

            // Định nghĩa các cột header theo yêu cầu mới (theo nhóm user cung cấp)
            $headers = [
                // Các cột đầu (từ raw_response)
                'Order ID',
                'Link Label',
                'Mockup',
                'Order Status',
                'Order Substatus',

                // 1️⃣ Huỷ / Trả hàng
                'Cancellation / Return Type',
                'SKU Quantity of return',
                'Order Refund Amount',
                'Cancelled Time',
                'Cancel By',
                'Cancel Reason',

                // 2️⃣ Loại đơn & sản phẩm
                'Normal or Pre-order',
                'SKU ID',
                'Seller SKU',
                'Product Name',
                'Variation',
                'Quantity',
                'Sku Quantity',
                'SKU Unit Original Price',

                // 3️⃣ Giá trị sản phẩm & giảm giá
                'SKU Subtotal Before Discount',
                'SKU Platform Discount',
                'SKU Seller Discount',
                'SKU Subtotal After Discount',

                // 4️⃣ Phí vận chuyển
                'Original Shipping Fee',
                'Shipping Fee After Discount',
                'Shipping Fee Seller Discount',
                'Shipping Fee Platform Discount',
                'Retail Delivery Fee',

                // 5️⃣ Thanh toán & thuế
                'Payment Method',
                'Payment platform discount',
                'Taxes',
                'Order Amount',
                'Earn',

                // 6️⃣ Thời gian xử lý đơn
                'Created Time',
                'Paid Time',
                'RTS Time',
                'Shipped Time',
                'Delivered Time',

                // 7️⃣ Vận hành & giao hàng
                'Fulfillment Type',
                'Warehouse Name',
                'Tracking ID',
                'Delivery Option Type',
                'Delivery Option',
                'Shipping Provider Name',
                'Package ID',
                'Weight (kg)',

                // 8️⃣ Khách hàng & địa chỉ
                'Buyer Username',
                'Buyer Message',
                'Recipient',
                'Phone #',
                'Country',
                'State',
                'City',
                'Zipcode',
                'Detail Address',
                'Additional address information',
                'House Name or Number',
                'Delivery Instruction',
                'Address Line 1',
                'Address Line 2',

                // 9️⃣ Thông tin bổ sung
                'Product Category',
                'Seller Note',
                'Shipping Information',
                'Shop Name',
            ];

            // Helper function để convert số index thành column letter (0->A, 1->B, ..., 25->Z, 26->AA, ...)
            $getColumnLetter = function ($index) {
                $result = '';
                $index++; // Excel columns start from 1, not 0
                while ($index > 0) {
                    $index--;
                    $result = chr(65 + ($index % 26)) . $result;
                    $index = intval($index / 26);
                }
                return $result;
            };

            // Thiết lập header row với style
            $headerRow = 1;
            $colIndex = 0;
            foreach ($headers as $header) {
                $col = $getColumnLetter($colIndex);
                $sheet->setCellValue($col . $headerRow, $header);
                $sheet->getStyle($col . $headerRow)->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => '4472C4']
                    ],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => '000000']
                        ]
                    ]
                ]);
                $colIndex++;
            }

            // Auto width cho columns
            for ($i = 0; $i < count($headers); $i++) {
                $col = $getColumnLetter($i);
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }

            // Xử lý từng đơn hàng
            $row = 2;
            $firstOrderLogged = false;
            foreach ($orders as $order) {
                // Ưu tiên raw_response (đầy đủ nhất), fallback order_data
                $orderData = $order->raw_response ?? $order->order_data ?? [];
                // TikTok có thể dùng line_items hoặc item_list
                $lineItems = $orderData['line_items'] ?? $orderData['item_list'] ?? [];
                $shippingInfo = $orderData['shipping_info'] ?? [];
                $buyerInfo = $orderData['buyer_info'] ?? [];

                // Log structure của order đầu tiên để debug
                if (!$firstOrderLogged && !empty($orderData)) {
                    Log::info('First order data structure', [
                        'order_id' => $order->order_id,
                        'order_data_keys' => array_keys($orderData),
                        'has_line_items' => isset($orderData['line_items']),
                        'has_item_list' => isset($orderData['item_list']),
                        'first_line_item_keys' => !empty($lineItems) ? array_keys($lineItems[0] ?? []) : []
                    ]);
                    $firstOrderLogged = true;
                }

                // Nếu đơn hàng có nhiều line items, mỗi item sẽ là một dòng
                if (empty($lineItems)) {
                    // Nếu không có line items, tạo một dòng với thông tin đơn hàng
                    $this->buildOrderRowExcel($sheet, $row, $order, null, $orderData, $shippingInfo, $buyerInfo);
                    $row++;
                } else {
                    // Mỗi line item là một dòng
                    foreach ($lineItems as $lineItem) {
                        $this->buildOrderRowExcel($sheet, $row, $order, $lineItem, $orderData, $shippingInfo, $buyerInfo);
                        $row++;
                    }
                }
            }

            Log::info('Creating Xlsx writer');

            // Tạo Writer và save vào memory
            $writer = new Xlsx($spreadsheet);
            $filename = 'tiktok_orders_' . date('Y-m-d_H-i-s') . '.xlsx';

            Log::info('Creating temporary file', ['filename' => $filename]);

            // Tạo temporary file
            $tempFile = tempnam(sys_get_temp_dir(), 'tiktok_orders_');
            if (!$tempFile) {
                throw new \Exception('Không thể tạo temporary file');
            }

            Log::info('Saving spreadsheet to file', ['temp_file' => $tempFile]);
            $writer->save($tempFile);

            Log::info('File saved successfully, returning download response', [
                'temp_file' => $tempFile,
                'filename' => $filename,
                'file_size' => filesize($tempFile)
            ]);

            // Kiểm tra file có tồn tại không
            if (!file_exists($tempFile)) {
                Log::error('Temporary file does not exist', ['temp_file' => $tempFile]);
                throw new \Exception('File không tồn tại sau khi tạo');
            }

            Log::info('Creating download response');

            // Return download response
            $response = response()->download($tempFile, $filename, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ])->deleteFileAfterSend(true);

            Log::info('Download response created', [
                'filename' => $filename,
                'file_size' => filesize($tempFile)
            ]);

            return $response;
        } catch (\Exception $e) {
            Log::error('Error in exportToExcel', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Xây dựng một dòng dữ liệu cho đơn hàng trong Excel
     */
    private function buildOrderRowExcel($sheet, $rowNum, $order, $lineItem, $orderData, $shippingInfo, $buyerInfo)
    {
        $colIndex = 0;

        // Helper function để convert số index thành column letter (0->A, 1->B, ..., 25->Z, 26->AA, ...)
        $getColumnLetter = function ($index) {
            $result = '';
            $index++; // Excel columns start from 1, not 0
            while ($index > 0) {
                $index--;
                $result = chr(65 + ($index % 26)) . $result;
                $index = intval($index / 26);
            }
            return $result;
        };

        // Helper function để set giá trị
        $setValue = function ($value) use (&$sheet, &$colIndex, $rowNum, $getColumnLetter) {
            $col = $getColumnLetter($colIndex);
            $sheet->setCellValue($col . $rowNum, $value ?? '');
            $colIndex++;
        };

        // Helper function để format timestamp
        $formatTime = function ($timestamp) {
            if (empty($timestamp)) return '';
            if (is_numeric($timestamp)) {
                return date('Y-m-d H:i:s', $timestamp);
            }
            return $timestamp;
        };

        // Đảm bảo $lineItem là array để tránh lỗi khi null
        $lineItem = $lineItem ?? [];

        // Đảm bảo $lineItem là array để tránh lỗi khi null
        $lineItem = $lineItem ?? [];

        $payment = $orderData['payment'] ?? [];
        $address = $shippingInfo['recipient_address'] ?? $shippingInfo['address'] ?? $orderData['recipient_address'] ?? $orderData['shipping_address'] ?? [];

        // Chuẩn hóa địa chỉ từ district_info nếu thiếu country/state/city
        $districtInfo = $address['district_info'] ?? [];
        $getDistrict = function ($level) use ($districtInfo) {
            foreach ($districtInfo as $d) {
                if (($d['address_level'] ?? '') === $level) {
                    return $d['address_name'] ?? '';
                }
            }
            return '';
        };
        $countryVal = $address['country'] ?? $getDistrict('L0');
        $stateVal   = $address['state'] ?? $getDistrict('L1');
        $cityVal    = $address['city'] ?? $getDistrict('L3') ?: $getDistrict('L2');
        $zipVal     = $address['zipcode'] ?? ($address['zip'] ?? ($address['postal_code'] ?? ''));

        // Các cột đầu (từ raw_response)
        $orderIdRaw = $orderData['id'] ?? $order->order_id ?? '';
        $setValue($orderIdRaw);

        $linkLabel = $orderData['link_label'] ?? $orderData['label'] ?? '';
        $setValue($linkLabel);

        $mockup = $lineItem['sku_image'] ?? $orderData['mockup'] ?? '';
        $setValue($mockup);

        $orderStatusRaw = $orderData['status'] ?? $order->order_status ?? '';
        $setValue($orderStatusRaw);

        $orderSubStatus = $orderData['sub_status'] ?? $orderData['substatus'] ?? $orderData['display_status'] ?? ($lineItem['display_status'] ?? '');
        $setValue($orderSubStatus);

        // Nhóm 1: Huỷ / Trả hàng
        $cancelType = $orderData['cancel_type'] ?? $orderData['cancellation']['type'] ?? $orderData['return_type'] ?? '';
        $setValue($cancelType);

        $skuReturnQty = $lineItem['return_quantity'] ?? $orderData['return_quantity'] ?? '';
        $setValue($skuReturnQty);

        $refundAmount = $orderData['refund_amount'] ?? ($orderData['refund']['amount'] ?? '');
        $setValue($refundAmount);

        $cancelledTime = $formatTime($orderData['cancel_time'] ?? ($orderData['refund']['cancel_time'] ?? null));
        $setValue($cancelledTime);

        $cancelBy = $orderData['cancel_by'] ?? ($orderData['refund']['cancel_by'] ?? '');
        $setValue($cancelBy);

        $cancelReason = $orderData['cancel_reason'] ?? ($orderData['refund']['reason'] ?? '');
        $setValue($cancelReason);

        // Nhóm 2: Loại đơn & sản phẩm
        $orderType = $orderData['order_type'] ?? $orderData['pre_order_type'] ?? ($orderData['is_preorder'] ?? '');
        $setValue($orderType);

        $setValue($lineItem['sku_id'] ?? '');
        $setValue($lineItem['seller_sku'] ?? '');
        $setValue($lineItem['product_name'] ?? '');
        $setValue($lineItem['sku_name'] ?? '');

        $quantity = $lineItem['quantity'] ?? 1;
        $setValue($quantity);
        $setValue($lineItem['sku_quantity'] ?? $quantity);
        $setValue($lineItem['original_price'] ?? '');

        // Nhóm 3: Giá trị & giảm giá
        $subtotalBefore = null;
        if (isset($lineItem['original_price']) && is_numeric($lineItem['original_price'])) {
            $subtotalBefore = ($lineItem['original_price']) * $quantity;
        }
        $setValue($subtotalBefore);

        $platformDiscount = $lineItem['platform_discount'] ?? ($payment['platform_discount'] ?? '');
        $setValue($platformDiscount);

        $sellerDiscount = $lineItem['seller_discount'] ?? ($payment['seller_discount'] ?? '');
        $setValue($sellerDiscount);

        $subtotalAfter = null;
        if ($subtotalBefore !== null) {
            $subtotalAfter = $subtotalBefore - (is_numeric($platformDiscount) ? $platformDiscount : 0) - (is_numeric($sellerDiscount) ? $sellerDiscount : 0);
        } elseif (isset($lineItem['sale_price'])) {
            $subtotalAfter = $lineItem['sale_price'] * $quantity;
        }
        $setValue($subtotalAfter);

        // Nhóm 4: Phí vận chuyển
        $setValue($payment['original_shipping_fee'] ?? ($orderData['shipping_fee'] ?? ''));
        $setValue($payment['shipping_fee_after_discount'] ?? '');
        $setValue($payment['shipping_fee_seller_discount'] ?? '');
        $setValue($payment['shipping_fee_platform_discount'] ?? '');
        $setValue($payment['retail_delivery_fee'] ?? '');

        // Nhóm 5: Thanh toán & thuế
        $setValue($payment['method'] ?? ($orderData['payment_method'] ?? ''));
        $setValue($payment['payment_platform_discount'] ?? ($payment['platform_discount'] ?? ''));
        $setValue($payment['taxes'] ?? ($payment['tax'] ?? ''));

        $orderAmount = $payment['total_amount'] ?? $order->order_amount ?? '';
        $setValue($orderAmount);

        $earn = $payment['seller_income'] ?? ($payment['settlement'] ?? ($payment['earnings'] ?? ''));
        $setValue($earn);

        // Nhóm 6: Thời gian xử lý
        $setValue($formatTime($orderData['create_time'] ?? ($order->create_time ? $order->create_time->timestamp : null)));
        $setValue($formatTime($orderData['paid_time'] ?? $orderData['pay_time'] ?? $orderData['payment_time'] ?? null));
        $setValue($formatTime($orderData['rts_time'] ?? $orderData['ready_to_ship_time'] ?? null));
        $setValue($formatTime($orderData['ship_time'] ?? $orderData['shipping_time'] ?? null));
        $setValue($formatTime($orderData['delivery_time'] ?? $orderData['delivered_time'] ?? null));

        // Nhóm 7: Vận hành & giao hàng
        $setValue($orderData['fulfillment_type'] ?? $orderData['fulfillment'] ?? '');
        $setValue($orderData['warehouse_name'] ?? ($shippingInfo['warehouse_name'] ?? ''));

        $trackingId = $orderData['tracking_number'] ?? ($lineItem['tracking_number'] ?? '');
        $setValue($trackingId);

        $setValue($orderData['delivery_option_type'] ?? '');
        $setValue($orderData['delivery_option'] ?? '');
        $setValue($orderData['shipping_provider_name'] ?? ($lineItem['shipping_provider_name'] ?? ($orderData['shipping_provider'] ?? '')));
        $setValue($orderData['package_id'] ?? '');

        $weight = $orderData['weight'] ?? ($shippingInfo['weight'] ?? ($lineItem['weight'] ?? ''));
        $setValue($weight);

        // Nhóm 8: Khách hàng & địa chỉ
        $setValue($order->buyer_username ?? ($buyerInfo['buyer_username'] ?? ''));
        $setValue($orderData['buyer_message'] ?? '');
        $setValue($address['name'] ?? '');
        $setValue($address['phone'] ?? ($address['phone_number'] ?? ''));
        $setValue($countryVal);
        $setValue($stateVal);
        $setValue($cityVal);
        $setValue($zipVal);
        $setValue($address['address_detail'] ?? ($address['full_address'] ?? ''));
        $setValue($address['address_detail_2'] ?? ($address['additional'] ?? ''));
        $setValue($address['house_number'] ?? '');
        $setValue($address['delivery_instruction'] ?? '');
        $setValue($address['address_line1'] ?? '');
        $setValue($address['address_line2'] ?? '');

        // Nhóm 9: Bổ sung
        $setValue($lineItem['category_name'] ?? ($orderData['product_category'] ?? ''));
        $setValue($orderData['seller_note'] ?? '');
        $setValue($orderData['shipping_information'] ?? '');
        $setValue($order->shop->shop_name ?? '');
    }

    /**
     * Escape giá trị cho CSV (xử lý dấu phẩy, dấu ngoặc kép, xuống dòng)
     */
    private function escapeCsvValue($value)
    {
        if ($value === null || $value === '') {
            return '';
        }

        // Convert to string
        $value = (string) $value;

        // Nếu có dấu phẩy, dấu ngoặc kép, hoặc xuống dòng thì bao bằng dấu ngoặc kép
        if (strpos($value, ',') !== false || strpos($value, '"') !== false || strpos($value, "\n") !== false) {
            // Escape dấu ngoặc kép bằng cách thêm dấu ngoặc kép
            $value = str_replace('"', '""', $value);
            return '"' . $value . '"';
        }

        return $value;
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
