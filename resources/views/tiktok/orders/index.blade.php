@extends('layouts.app')

@section('title', 'Danh sách đơn hàng TikTok')

@section('head')
<style>
.scrollbar-hide {
    -ms-overflow-style: none;  /* Internet Explorer 10+ */
    scrollbar-width: none;  /* Firefox */
}
.scrollbar-hide::-webkit-scrollbar { 
    display: none;  /* Safari and Chrome */
}
</style>
@endsection

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="bg-gray-800 shadow-lg rounded-lg">
        <!-- Header Section -->
        <div class="px-6 py-4 border-b border-gray-700">
            <h2 class="text-2xl font-bold text-white flex items-center">
                <i class="fas fa-shopping-cart mr-3 text-blue-400"></i>
                Danh sách đơn hàng TikTok
            </h2>
        </div>

        <!-- Filters and Actions Section -->
        <div class="px-3 sm:px-6 py-4 border-b border-gray-700 bg-gray-900">
            <!-- Date and Shop Filters -->
            <div class="flex flex-col lg:flex-row lg:items-center gap-4 mb-4">
                <!-- Date Range Filter -->
                <div class="flex flex-col w-full lg:w-auto">
                    <label class="block text-sm font-medium text-gray-300 mb-2">Khoảng thời gian:</label>
                    
                    <!-- Quick Date Buttons -->
                    <div class="flex flex-wrap gap-2 mb-3">
                        <button type="button" onclick="setQuickDate('today')" 
                                class="px-3 py-1 text-xs bg-gray-700 text-gray-300 rounded-full hover:bg-gray-600 transition-colors">
                            Hôm nay
                        </button>
                        <button type="button" onclick="setQuickDate('yesterday')" 
                                class="px-3 py-1 text-xs bg-gray-700 text-gray-300 rounded-full hover:bg-gray-600 transition-colors">
                            Hôm qua
                        </button>
                        <button type="button" onclick="setQuickDate('week')" 
                                class="px-3 py-1 text-xs bg-gray-700 text-gray-300 rounded-full hover:bg-gray-600 transition-colors">
                            7 ngày
                        </button>
                        <button type="button" onclick="setQuickDate('month')" 
                                class="px-3 py-1 text-xs bg-gray-700 text-gray-300 rounded-full hover:bg-gray-600 transition-colors">
                            Tháng này
                        </button>
                        <button type="button" onclick="setQuickDate('lastMonth')" 
                                class="px-3 py-1 text-xs bg-gray-700 text-gray-300 rounded-full hover:bg-gray-600 transition-colors">
                            Tháng trước
                        </button>
                    </div>
                    
                    <!-- Date Picker Inputs -->
                    <div class="flex flex-col sm:flex-row gap-2 w-full sm:w-auto">
                        <div class="relative">
                            <input type="date" id="dateFrom" name="date_from" 
                                   class="block w-full sm:w-40 px-3 py-2 border border-gray-600 rounded-md shadow-sm bg-gray-800 text-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500" 
                                   value="{{ request('date_from') ? date('Y-m-d', strtotime(request('date_from'))) : date('Y-m-01') }}">
                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                <i class="fas fa-calendar-alt text-gray-400 text-xs"></i>
                            </div>
                        </div>
                        <div class="flex items-center justify-center text-gray-400 text-sm">
                            <span>đến</span>
                        </div>
                        <div class="relative">
                            <input type="date" id="dateTo" name="date_to" 
                                   class="block w-full sm:w-40 px-3 py-2 border border-gray-600 rounded-md shadow-sm bg-gray-800 text-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500" 
                                   value="{{ request('date_to') ? date('Y-m-d', strtotime(request('date_to'))) : date('Y-m-d') }}">
                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                <i class="fas fa-calendar-alt text-gray-400 text-xs"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Shop Filter -->
                <div class="flex flex-col sm:flex-row sm:items-center w-full lg:w-auto">
                    <label class="block text-sm font-medium text-gray-300 mb-1 sm:mb-0 sm:mr-2">Shop:</label>
                    <select class="block w-full sm:w-48 px-3 py-2 border border-gray-600 rounded-md shadow-sm bg-gray-800 text-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500" id="shopFilter">
                        <option value="">Tất cả shops</option>
                        @foreach($shops as $shop)
                            <option value="{{ $shop->id }}" {{ request('shop_id') == $shop->id ? 'selected' : '' }}>
                                {{ $shop->shop_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Display Options -->
                <div class="flex flex-col sm:flex-row sm:items-center gap-2 sm:gap-4 w-full lg:w-auto lg:ml-auto">
                    <label class="flex items-center">
                        <input type="checkbox" class="h-4 w-4 text-pink-600 focus:ring-pink-500 border-gray-600 rounded bg-gray-800" checked>
                        <span class="ml-2 text-sm text-gray-300">Mới nhất trước</span>
                    </label>
                    <label class="flex items-center">
                        <input type="checkbox" class="h-4 w-4 text-pink-600 focus:ring-pink-500 border-gray-600 rounded bg-gray-800" checked>
                        <span class="ml-2 text-sm text-gray-300">Giao diện mới</span>
                    </label>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex flex-wrap items-center gap-2 sm:gap-3">
                <button class="px-3 py-2 sm:px-4 text-xs sm:text-sm bg-gray-700 text-gray-300 rounded-md hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-500">
                    <span class="hidden sm:inline">Tạo nhãn hàng loạt</span>
                    <span class="sm:hidden">Tạo nhãn</span>
                </button>
                <button class="px-3 py-2 sm:px-4 text-xs sm:text-sm bg-gray-700 text-gray-300 rounded-md hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-500">
                    <span class="hidden sm:inline">Tạo nhãn + Xuất</span>
                    <span class="sm:hidden">Nhãn + Xuất</span>
                </button>
                <button class="px-3 py-2 sm:px-4 text-xs sm:text-sm bg-gray-700 text-gray-300 rounded-md hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-500">
                    Xuất Excel
                </button>
                <button class="px-3 py-2 sm:px-4 text-xs sm:text-sm bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <span class="hidden sm:inline">Tải lên Tracking</span>
                    <span class="sm:hidden">Tracking</span>
                </button>
                <button class="px-3 py-2 sm:px-4 text-xs sm:text-sm bg-gray-700 text-gray-300 rounded-md hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-500">
                    <span class="hidden sm:inline">Đẩy đến Fulfillment</span>
                    <span class="sm:hidden">Fulfillment</span>
                </button>
                <button class="px-3 py-2 sm:px-4 text-xs sm:text-sm bg-green-600 text-white rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500" onclick="openSyncModal()">
                    <i class="fas fa-sync-alt mr-1 sm:mr-2"></i>
                    <span class="hidden sm:inline">Đồng bộ đơn hàng</span>
                    <span class="sm:hidden">Đồng bộ</span>
                </button>
            </div>
        </div>

        <!-- Status Tabs -->
        <div class="px-3 sm:px-6 py-3 border-b border-gray-700">
        <!-- Desktop Layout - Grid -->
        <div class="hidden lg:grid lg:grid-cols-7 lg:gap-2">
            <a href="{{ route('tiktok.orders.index') }}" 
               class="px-3 py-2 text-sm font-medium text-center rounded-lg transition-colors duration-200 {{ !request('status') ? 'bg-blue-600 text-white' : 'bg-gray-700 text-gray-300 hover:bg-gray-600' }}">
                Tất cả
            </a>
            <a href="{{ route('tiktok.orders.index', ['status' => 'AWAITING_SHIPMENT']) }}" 
               class="px-3 py-2 text-sm font-medium text-center rounded-lg transition-colors duration-200 {{ request('status') == 'AWAITING_SHIPMENT' ? 'bg-blue-600 text-white' : 'bg-gray-700 text-gray-300 hover:bg-gray-600' }}">
                Chờ giao hàng
            </a>
            <a href="{{ route('tiktok.orders.index', ['status' => 'AWAITING_COLLECTION']) }}" 
               class="px-3 py-2 text-sm font-medium text-center rounded-lg transition-colors duration-200 {{ request('status') == 'AWAITING_COLLECTION' ? 'bg-blue-600 text-white' : 'bg-gray-700 text-gray-300 hover:bg-gray-600' }}">
                Chờ lấy hàng
            </a>
            <a href="{{ route('tiktok.orders.index', ['status' => 'IN_TRANSIT']) }}" 
               class="px-3 py-2 text-sm font-medium text-center rounded-lg transition-colors duration-200 {{ request('status') == 'IN_TRANSIT' ? 'bg-blue-600 text-white' : 'bg-gray-700 text-gray-300 hover:bg-gray-600' }}">
                Đang vận chuyển
            </a>
            <a href="{{ route('tiktok.orders.index', ['status' => 'DELIVERED']) }}" 
               class="px-3 py-2 text-sm font-medium text-center rounded-lg transition-colors duration-200 {{ request('status') == 'DELIVERED' ? 'bg-blue-600 text-white' : 'bg-gray-700 text-gray-300 hover:bg-gray-600' }}">
                Đã giao
            </a>
            <a href="{{ route('tiktok.orders.index', ['status' => 'CANCELLED']) }}" 
               class="px-3 py-2 text-sm font-medium text-center rounded-lg transition-colors duration-200 {{ request('status') == 'CANCELLED' ? 'bg-blue-600 text-white' : 'bg-gray-700 text-gray-300 hover:bg-gray-600' }}">
                Đã hủy
            </a>
            <a href="{{ route('tiktok.orders.index', ['status' => 'UNPAID']) }}" 
               class="px-3 py-2 text-sm font-medium text-center rounded-lg transition-colors duration-200 {{ request('status') == 'UNPAID' ? 'bg-blue-600 text-white' : 'bg-gray-700 text-gray-300 hover:bg-gray-600' }}">
                Chưa thanh toán
            </a>
        </div>

        <!-- Tablet Layout - 2 Rows -->
        <div class="hidden md:grid lg:hidden md:grid-cols-4 md:gap-2 md:space-y-2">
            <a href="{{ route('tiktok.orders.index') }}" 
               class="px-3 py-2 text-sm font-medium text-center rounded-lg transition-colors duration-200 {{ !request('status') ? 'bg-blue-600 text-white' : 'bg-gray-700 text-gray-300 hover:bg-gray-600' }}">
                Tất cả
            </a>
            <a href="{{ route('tiktok.orders.index', ['status' => 'AWAITING_SHIPMENT']) }}" 
               class="px-3 py-2 text-sm font-medium text-center rounded-lg transition-colors duration-200 {{ request('status') == 'AWAITING_SHIPMENT' ? 'bg-blue-600 text-white' : 'bg-gray-700 text-gray-300 hover:bg-gray-600' }}">
                Chờ giao hàng
            </a>
            <a href="{{ route('tiktok.orders.index', ['status' => 'AWAITING_COLLECTION']) }}" 
               class="px-3 py-2 text-sm font-medium text-center rounded-lg transition-colors duration-200 {{ request('status') == 'AWAITING_COLLECTION' ? 'bg-blue-600 text-white' : 'bg-gray-700 text-gray-300 hover:bg-gray-600' }}">
                Chờ lấy hàng
            </a>
            <a href="{{ route('tiktok.orders.index', ['status' => 'IN_TRANSIT']) }}" 
               class="px-3 py-2 text-sm font-medium text-center rounded-lg transition-colors duration-200 {{ request('status') == 'IN_TRANSIT' ? 'bg-blue-600 text-white' : 'bg-gray-700 text-gray-300 hover:bg-gray-600' }}">
                Đang vận chuyển
            </a>
            <a href="{{ route('tiktok.orders.index', ['status' => 'DELIVERED']) }}" 
               class="px-3 py-2 text-sm font-medium text-center rounded-lg transition-colors duration-200 {{ request('status') == 'DELIVERED' ? 'bg-blue-600 text-white' : 'bg-gray-700 text-gray-300 hover:bg-gray-600' }}">
                Đã giao
            </a>
            <a href="{{ route('tiktok.orders.index', ['status' => 'CANCELLED']) }}" 
               class="px-3 py-2 text-sm font-medium text-center rounded-lg transition-colors duration-200 {{ request('status') == 'CANCELLED' ? 'bg-blue-600 text-white' : 'bg-gray-700 text-gray-300 hover:bg-gray-600' }}">
                Đã hủy
            </a>
            <a href="{{ route('tiktok.orders.index', ['status' => 'UNPAID']) }}" 
               class="px-3 py-2 text-sm font-medium text-center rounded-lg transition-colors duration-200 {{ request('status') == 'UNPAID' ? 'bg-blue-600 text-white' : 'bg-gray-700 text-gray-300 hover:bg-gray-600' }}">
                Chưa thanh toán
            </a>
        </div>

        <!-- Mobile Layout - Horizontal Scroll with Better Spacing -->
        <div class="md:hidden">
            <div class="flex space-x-2 pb-2 overflow-x-auto scrollbar-hide">
                <a href="{{ route('tiktok.orders.index') }}" 
                   class="px-4 py-2 text-xs font-medium whitespace-nowrap rounded-full transition-colors duration-200 {{ !request('status') ? 'bg-blue-600 text-white' : 'bg-gray-700 text-gray-300 hover:bg-gray-600' }}">
                    Tất cả
                </a>
                <a href="{{ route('tiktok.orders.index', ['status' => 'AWAITING_SHIPMENT']) }}" 
                   class="px-4 py-2 text-xs font-medium whitespace-nowrap rounded-full transition-colors duration-200 {{ request('status') == 'AWAITING_SHIPMENT' ? 'bg-blue-600 text-white' : 'bg-gray-700 text-gray-300 hover:bg-gray-600' }}">
                    Chờ giao
                </a>
                <a href="{{ route('tiktok.orders.index', ['status' => 'AWAITING_COLLECTION']) }}" 
                   class="px-4 py-2 text-xs font-medium whitespace-nowrap rounded-full transition-colors duration-200 {{ request('status') == 'AWAITING_COLLECTION' ? 'bg-blue-600 text-white' : 'bg-gray-700 text-gray-300 hover:bg-gray-600' }}">
                    Chờ lấy
                </a>
                <a href="{{ route('tiktok.orders.index', ['status' => 'IN_TRANSIT']) }}" 
                   class="px-4 py-2 text-xs font-medium whitespace-nowrap rounded-full transition-colors duration-200 {{ request('status') == 'IN_TRANSIT' ? 'bg-blue-600 text-white' : 'bg-gray-700 text-gray-300 hover:bg-gray-600' }}">
                    Đang giao
                </a>
                <a href="{{ route('tiktok.orders.index', ['status' => 'DELIVERED']) }}" 
                   class="px-4 py-2 text-xs font-medium whitespace-nowrap rounded-full transition-colors duration-200 {{ request('status') == 'DELIVERED' ? 'bg-blue-600 text-white' : 'bg-gray-700 text-gray-300 hover:bg-gray-600' }}">
                    Đã giao
                </a>
                <a href="{{ route('tiktok.orders.index', ['status' => 'CANCELLED']) }}" 
                   class="px-4 py-2 text-xs font-medium whitespace-nowrap rounded-full transition-colors duration-200 {{ request('status') == 'CANCELLED' ? 'bg-blue-600 text-white' : 'bg-gray-700 text-gray-300 hover:bg-gray-600' }}">
                    Đã hủy
                </a>
                <a href="{{ route('tiktok.orders.index', ['status' => 'UNPAID']) }}" 
                   class="px-4 py-2 text-xs font-medium whitespace-nowrap rounded-full transition-colors duration-200 {{ request('status') == 'UNPAID' ? 'bg-blue-600 text-white' : 'bg-gray-700 text-gray-300 hover:bg-gray-600' }}">
                    Chưa trả
                </a>
            </div>
        </div>
    </div>

        <!-- Orders Table -->
        <div class="overflow-hidden">
            @if($orders->count() > 0)
                <!-- Desktop Table View -->
                <div class="hidden lg:block overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-700">
                        <thead class="bg-gray-900">
                            <tr>
                                <th class="px-3 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">
                                    <input type="checkbox" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-600 rounded bg-gray-800">
                                </th>
                                <th class="px-3 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Order ID</th>
                                <th class="px-3 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Tracking</th>
                                <th class="px-3 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Shop</th>
                                <th class="px-3 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Status</th>
                                <th class="px-3 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Total</th>
                                <th class="px-3 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Shipping</th>
                                <th class="px-3 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-gray-800 divide-y divide-gray-700">
                            @foreach($orders as $order)
                                <tr class="hover:bg-gray-700">
                                    <!-- Checkbox -->
                                    <td class="px-3 py-4 whitespace-nowrap">
                                        <input type="checkbox" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-600 rounded bg-gray-800">
                                    </td>
                                    
                                    <!-- Order ID & Product Info -->
                                    <td class="px-3 py-4">
                                        <div class="flex items-start space-x-2">
                                            <!-- Product Image -->
                                            <div class="flex-shrink-0 h-10 w-10 bg-gray-700 rounded-md flex items-center justify-center">
                                                @if(isset($order->order_data['line_items'][0]['sku_image']))
                                                    <img src="{{ $order->order_data['line_items'][0]['sku_image'] }}" 
                                                         alt="Product" class="h-10 w-10 object-cover rounded-md">
                                                @else
                                                    <i class="fas fa-image text-gray-500 text-sm"></i>
                                                @endif
                                            </div>
                                            
                                            <div class="flex-1 min-w-0">
                                                <!-- Order ID -->
                                                <p class="text-sm font-bold text-blue-400">{{ $order->order_id }}</p>
                                                
                                                <!-- Order Date -->
                                                <p class="text-xs text-gray-400">
                                                    {{ $order->create_time ? $order->create_time->format('m/d/y, g:i A') : 'N/A' }}
                                                </p>
                                                
                                                <!-- SLA & Auto Cancel -->
                                                <div class="flex items-center space-x-2 mt-1">
                                                    <span class="text-xs text-orange-400">
                                                        SLA: {{ $order->create_time ? $order->create_time->addDays(3)->diffForHumans() : 'N/A' }}
                                                    </span>
                                                    <span class="text-xs text-red-400">
                                                        Auto: {{ $order->create_time ? $order->create_time->addDays(10)->diffForHumans() : 'N/A' }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    
                                    <!-- Tracking -->
                                    <td class="px-3 py-4 whitespace-nowrap">
                                        @php
                                            // Lấy tracking number từ nhiều vị trí có thể
                                            $trackingNumber = null;
                                            $shippingProviderName = null;
                                            
                                            // 1. Kiểm tra root level (từ form add tracking)
                                            if (isset($order->order_data['tracking_number']) && trim($order->order_data['tracking_number']) !== '') {
                                                $trackingNumber = $order->order_data['tracking_number'];
                                                $shippingProviderName = $order->order_data['shipping_provider_name'] ?? null;
                                            }
                                            // 2. Kiểm tra trong line_items (từ TikTok API)
                                            elseif (isset($order->order_data['line_items'][0]['tracking_number']) && trim($order->order_data['line_items'][0]['tracking_number']) !== '') {
                                                $trackingNumber = $order->order_data['line_items'][0]['tracking_number'];
                                                $shippingProviderName = $order->order_data['line_items'][0]['shipping_provider_name'] ?? null;
                                            }
                                            // 3. Kiểm tra shipping_provider từ root level (chỉ hiển thị tên provider nếu không có tracking)
                                            elseif (isset($order->order_data['shipping_provider']) && trim($order->order_data['shipping_provider']) !== '') {
                                                $shippingProviderName = $order->order_data['shipping_provider'];
                                            }
                                        @endphp
                                        
                                        @if($order->order_status == 'AWAITING_SHIPMENT' && ($order->shipping_type == 'SELLER' || !$order->shipping_type))
                                            <button onclick="openMarkShippedModal({{ $order->id }})" 
                                                    class="inline-flex items-center px-2 py-1 border border-transparent text-xs font-medium rounded-md text-white bg-teal-600 hover:bg-teal-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-teal-500">
                                                Thêm Tracking
                                            </button>
                                        @elseif($trackingNumber)
                                            <div class="flex flex-col">
                                                <span class="text-sm font-medium text-green-400">
                                                    {{ $trackingNumber }}
                                                </span>
                                                @if($shippingProviderName)
                                                    <span class="text-xs text-gray-400">
                                                        {{ $shippingProviderName }}
                                                    </span>
                                                @endif
                                            </div>
                                        @elseif($shippingProviderName && in_array($order->order_status, ['IN_TRANSIT', 'AWAITING_COLLECTION']))
                                            <div class="flex flex-col">
                                                <span class="text-sm font-medium text-green-400">Đã gửi</span>
                                                <span class="text-xs text-gray-400">
                                                    {{ $shippingProviderName }}
                                                </span>
                                            </div>
                                        @elseif(in_array($order->order_status, ['IN_TRANSIT', 'AWAITING_COLLECTION']))
                                            <span class="text-sm text-green-400">Đã gửi</span>
                                        @else
                                            <span class="text-sm text-gray-500">-</span>
                                        @endif
                                    </td>
                                    
                                    <!-- Shop Name -->
                                    <td class="px-3 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-200">{{ $order->shop->shop_name ?? 'N/A' }}</div>
                                    </td>
                                    
                                    <!-- Status -->
                                    <td class="px-3 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-3 py-2 rounded-full text-sm font-medium {{ $order->getStatusClasses() }}">
                                            <i class="{{ $order->getStatusIcon() }} mr-2"></i>
                                            {{ $order->getStatusText() }}
                                        </span>
                                    </td>
                                    
                                    <!-- Total -->
                                    <td class="px-3 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-200">
                                            {{ number_format($order->order_amount ?? 0, 2) }} {{ $order->currency ?? 'GBP' }}
                                        </div>
                                        <div class="text-xs text-gray-400">
                                            {{ count($order->order_data['line_items'] ?? []) }} item(s)
                                        </div>
                                    </td>
                                    
                                    <!-- Shipping Type -->
                                    <td class="px-3 py-4 whitespace-nowrap">
                                        <span class="text-sm text-gray-200">{{ $order->shipping_type ?? 'SELLER' }}</span>
                                    </td>
                                    
                                    <!-- Actions -->
                                    <td class="px-3 py-4 whitespace-nowrap">
                                        <div class="flex items-center space-x-2">
                                            <a href="{{ route('tiktok.orders.show', $order->id) }}" 
                                               class="inline-flex items-center px-2 py-1 border border-transparent text-xs font-medium rounded-md text-white bg-teal-600 hover:bg-teal-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-teal-500">
                                                Chi tiết
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Mobile & Tablet Card View -->
                <div class="lg:hidden">
                    <div class="space-y-4 p-3 sm:p-6">
                        @foreach($orders as $order)
                            <div class="bg-gray-700 rounded-lg p-4 border border-gray-600">
                                <!-- Header with checkbox and order info -->
                                <div class="flex items-start justify-between mb-3">
                                    <div class="flex items-start space-x-3">
                                        <input type="checkbox" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-600 rounded bg-gray-800 mt-1">
                                        <div class="flex items-start space-x-3">
                                            <!-- Product Image -->
                                            <div class="flex-shrink-0 h-12 w-12 bg-gray-600 rounded-md flex items-center justify-center">
                                                @if(isset($order->order_data['line_items'][0]['sku_image']))
                                                    <img src="{{ $order->order_data['line_items'][0]['sku_image'] }}" 
                                                         alt="Product" class="h-12 w-12 object-cover rounded-md">
                                                @else
                                                    <i class="fas fa-image text-gray-400"></i>
                                                @endif
                                            </div>
                                            <div>
                                                <p class="text-sm font-bold text-blue-400">{{ $order->order_id }}</p>
                                                <p class="text-xs text-gray-400">
                                                    {{ $order->create_time ? $order->create_time->format('m/d/y, g:i A') : 'N/A' }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                    <span class="inline-flex items-center px-3 py-2 rounded-full text-sm font-medium {{ $order->getStatusClasses() }}">
                                        <i class="{{ $order->getStatusIcon() }} mr-2"></i>
                                        {{ $order->getStatusText() }}
                                    </span>
                                </div>

                                <!-- Order details grid -->
                                <div class="grid grid-cols-2 gap-4 mb-3">
                                    <div>
                                        <p class="text-xs text-gray-400">Shop</p>
                                        <p class="text-sm text-gray-200">{{ $order->shop->shop_name ?? 'N/A' }}</p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-gray-400">Total</p>
                                        <p class="text-sm text-gray-200">
                                            {{ number_format($order->order_amount ?? 0, 2) }} {{ $order->currency ?? 'GBP' }}
                                        </p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-gray-400">Shipping</p>
                                        <p class="text-sm text-gray-200">{{ $order->shipping_type ?? 'SELLER' }}</p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-gray-400">Items</p>
                                        <p class="text-sm text-gray-200">{{ count($order->order_data['line_items'] ?? []) }} item(s)</p>
                                    </div>
                                </div>

                                <!-- SLA & Auto Cancel -->
                                <div class="flex items-center justify-between mb-3 text-xs">
                                    <span class="text-orange-400">
                                        SLA: {{ $order->create_time ? $order->create_time->addDays(3)->diffForHumans() : 'N/A' }}
                                    </span>
                                    <span class="text-red-400">
                                        Auto: {{ $order->create_time ? $order->create_time->addDays(10)->diffForHumans() : 'N/A' }}
                                    </span>
                                </div>

                                <!-- Tracking Info -->
                                @php
                                    // Lấy tracking number từ nhiều vị trí có thể (cho mobile view)
                                    $mobileTrackingNumber = null;
                                    $mobileShippingProviderName = null;
                                    
                                    // 1. Kiểm tra root level (từ form add tracking)
                                    if (isset($order->order_data['tracking_number']) && trim($order->order_data['tracking_number']) !== '') {
                                        $mobileTrackingNumber = $order->order_data['tracking_number'];
                                        $mobileShippingProviderName = $order->order_data['shipping_provider_name'] ?? null;
                                    }
                                    // 2. Kiểm tra trong line_items (từ TikTok API)
                                    elseif (isset($order->order_data['line_items'][0]['tracking_number']) && trim($order->order_data['line_items'][0]['tracking_number']) !== '') {
                                        $mobileTrackingNumber = $order->order_data['line_items'][0]['tracking_number'];
                                        $mobileShippingProviderName = $order->order_data['line_items'][0]['shipping_provider_name'] ?? null;
                                    }
                                    // 3. Kiểm tra shipping_provider từ root level (chỉ hiển thị tên provider nếu không có tracking)
                                    elseif (isset($order->order_data['shipping_provider']) && trim($order->order_data['shipping_provider']) !== '') {
                                        $mobileShippingProviderName = $order->order_data['shipping_provider'];
                                    }
                                @endphp
                                
                                @if(($mobileTrackingNumber || $mobileShippingProviderName) && in_array($order->order_status, ['IN_TRANSIT', 'AWAITING_COLLECTION']))
                                <div class="mb-3 p-2 bg-green-900/20 border border-green-700/50 rounded-lg">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            @if($mobileTrackingNumber)
                                                <p class="text-xs text-green-400 font-medium">Tracking Number</p>
                                                <p class="text-sm text-green-300">{{ $mobileTrackingNumber }}</p>
                                            @else
                                                <p class="text-xs text-green-400 font-medium">Shipping Status</p>
                                                <p class="text-sm text-green-300">Đã gửi</p>
                                            @endif
                                            @if($mobileShippingProviderName)
                                                <p class="text-xs text-gray-400">{{ $mobileShippingProviderName }}</p>
                                            @endif
                                        </div>
                                        <i class="fas fa-truck text-green-400"></i>
                                    </div>
                                </div>
                                @endif

                                <!-- Actions -->
                                <div class="flex items-center justify-between">
                                    @if($order->order_status == 'AWAITING_SHIPMENT' && ($order->shipping_type == 'SELLER' || !$order->shipping_type))
                                        <button onclick="openMarkShippedModal({{ $order->id }})" 
                                                class="inline-flex items-center px-3 py-1 border border-transparent text-xs font-medium rounded-md text-white bg-teal-600 hover:bg-teal-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-teal-500">
                                            Thêm Tracking
                                        </button>
                                    @elseif(in_array($order->order_status, ['IN_TRANSIT', 'AWAITING_COLLECTION']))
                                        <span class="text-sm text-green-400">Đã gửi</span>
                                    @else
                                        <span class="text-sm text-gray-500">-</span>
                                    @endif
                                    <a href="{{ route('tiktok.orders.show', $order->id) }}" 
                                       class="inline-flex items-center px-3 py-1 border border-transparent text-xs font-medium rounded-md text-white bg-teal-600 hover:bg-teal-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-teal-500">
                                        Chi tiết
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                
                <!-- Pagination -->
                <div class="px-3 sm:px-6 py-4 border-t border-gray-700 bg-gray-900">
                    <div class="flex flex-col sm:flex-row justify-between items-center gap-3">
                        <div class="text-xs sm:text-sm text-gray-300 text-center sm:text-left">
                            Hiển thị {{ $orders->firstItem() }} - {{ $orders->lastItem() }} 
                            trong tổng số {{ $orders->total() }} đơn hàng
                        </div>
                        <div class="w-full sm:w-auto">
                            {{ $orders->links() }}
                        </div>
                    </div>
                </div>
            @else
                <div class="text-center py-8 sm:py-12 px-3 sm:px-6">
                    <div class="mb-4">
                        <i class="fas fa-shopping-cart text-4xl sm:text-6xl text-gray-600"></i>
                    </div>
                    <h3 class="text-base sm:text-lg font-medium text-gray-200 mb-2">Không có đơn hàng nào</h3>
                    <p class="text-sm sm:text-base text-gray-400 mb-4 sm:mb-6 px-4">
                        Chưa có đơn hàng nào phù hợp với bộ lọc của bạn.
                    </p>
                    <button type="button" class="inline-flex items-center px-3 sm:px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 focus:ring-offset-gray-800" onclick="openSyncModal()">
                        <i class="fas fa-sync-alt mr-1 sm:mr-2"></i>
                        <span class="hidden sm:inline">Đồng bộ đơn hàng từ TikTok</span>
                        <span class="sm:hidden">Đồng bộ đơn hàng</span>
                    </button>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Sync Modal -->
<div class="fixed inset-0 bg-gray-900 bg-opacity-75 overflow-y-auto h-full w-full hidden" id="syncModal">
    <div class="relative top-20 mx-auto p-5 border border-gray-600 w-96 shadow-lg rounded-md bg-gray-800">
        <div class="mt-3">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-white flex items-center">
                    <i class="fas fa-sync-alt mr-2 text-blue-400"></i>
                    Đồng bộ đơn hàng từ TikTok
                </h3>
                <button type="button" class="text-gray-400 hover:text-gray-200" onclick="closeSyncModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form action="{{ route('tiktok.orders.sync') }}" method="POST" id="syncForm">
                @csrf
                <div class="mb-4">
                    <label for="sync_shop_id" class="block text-sm font-medium text-gray-300 mb-2">Chọn shop để đồng bộ</label>
                    <select class="block w-full px-3 py-2 border border-gray-600 rounded-md shadow-sm bg-gray-700 text-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500" id="sync_shop_id" name="shop_id" required>
                        <option value="">-- Chọn shop --</option>
                        @foreach($shops as $shop)
                            <option value="{{ $shop->id }}">
                                {{ $shop->shop_name }}
                                @if($shop->integration && $shop->integration->status === 'active')
                                    <span class="text-green-400">(Đang hoạt động)</span>
                                @else
                                    <span class="text-red-400">(Không hoạt động)</span>
                                @endif
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="bg-blue-900 border border-blue-700 rounded-md p-3 mb-4">
                    <div class="flex">
                        <i class="fas fa-info-circle text-blue-300 mr-2 mt-0.5"></i>
                        <p class="text-sm text-blue-200">
                            Quá trình đồng bộ có thể mất vài phút tùy thuộc vào số lượng đơn hàng.
                        </p>
                    </div>
                </div>
                <div class="flex justify-end space-x-3">
                    <button type="button" class="px-4 py-2 bg-gray-600 text-gray-200 rounded-md hover:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-gray-500" onclick="closeSyncModal()">Hủy</button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <i class="fas fa-sync-alt mr-1"></i>
                        Bắt đầu đồng bộ
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Mark as Shipped Modal -->
<div class="fixed inset-0 bg-gray-900 bg-opacity-75 overflow-y-auto h-full w-full hidden" id="markShippedModal">
    <div class="relative top-20 mx-auto p-5 border border-gray-600 w-full max-w-2xl shadow-lg rounded-md bg-gray-800">
        <div class="mt-3">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-white flex items-center">
                    <i class="fas fa-truck mr-2 text-teal-400"></i>
                    Đánh dấu gói hàng đã gửi
                </h3>
                <button type="button" class="text-gray-400 hover:text-gray-200" onclick="closeMarkShippedModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <!-- Order Info -->
            <div id="orderInfo" class="mb-4 p-3 bg-gray-700 rounded-lg">
                <!-- Order info will be loaded here -->
            </div>
            
            <form id="markShippedForm">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <!-- Tracking Number -->
                    <div>
                        <label for="tracking_number" class="block text-sm font-medium text-gray-300 mb-2">
                            Mã vận đơn <span class="text-red-400">*</span>
                        </label>
                        <input type="text" id="tracking_number" name="tracking_number" required
                               class="block w-full px-3 py-2 border border-gray-600 rounded-md shadow-sm bg-gray-700 text-gray-300 focus:outline-none focus:ring-teal-500 focus:border-teal-500"
                               placeholder="Nhập mã vận đơn">
                    </div>
                    
                    <!-- Shipping Provider -->
                    <div>
                        <label for="shipping_provider_id" class="block text-sm font-medium text-gray-300 mb-2">
                            Đơn vị vận chuyển <span class="text-red-400">*</span>
                        </label>
                        <select id="shipping_provider_id" name="shipping_provider_id" required
                                class="block w-full px-3 py-2 border border-gray-600 rounded-md shadow-sm bg-gray-700 text-gray-300 focus:outline-none focus:ring-teal-500 focus:border-teal-500">
                            <option value="">-- Chọn đơn vị vận chuyển --</option>
                        </select>
                        <div id="loadingProviders" class="text-sm text-gray-400 mt-1 hidden">
                            <i class="fas fa-spinner fa-spin mr-1"></i>Đang tải danh sách đơn vị vận chuyển...
                        </div>
                    </div>
                </div>
                
                <!-- Line Items Selection -->
                <div id="lineItemsSection" class="mb-4">
                    <label class="block text-sm font-medium text-gray-300 mb-2">Sản phẩm trong đơn hàng:</label>
                    <div id="lineItemsList" class="space-y-2">
                        <!-- Line items will be loaded here -->
                    </div>
                </div>
                
                <div class="bg-blue-900 border border-blue-700 rounded-md p-3 mb-4">
                    <div class="flex">
                        <i class="fas fa-info-circle text-blue-300 mr-2 mt-0.5"></i>
                        <p class="text-sm text-blue-200">
                            Sau khi đánh dấu gói hàng đã gửi, trạng thái đơn hàng sẽ chuyển thành "Đang vận chuyển".
                        </p>
                    </div>
                </div>
                
                <div class="flex justify-end space-x-3">
                    <button type="button" class="px-4 py-2 bg-gray-600 text-gray-200 rounded-md hover:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-gray-500" onclick="closeMarkShippedModal()">
                        Hủy
                    </button>
                    <button type="submit" class="px-4 py-2 bg-teal-600 text-white rounded-md hover:bg-teal-700 focus:outline-none focus:ring-2 focus:ring-teal-500">
                        <i class="fas fa-truck mr-1"></i>
                        Đánh dấu đã gửi
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
function refreshOrders() {
    window.location.reload();
}

function openSyncModal() {
    document.getElementById('syncModal').classList.remove('hidden');
}

function closeSyncModal() {
    document.getElementById('syncModal').classList.add('hidden');
}

// Auto submit form khi thay đổi shop filter
document.getElementById('shopFilter').addEventListener('change', function() {
    const shopId = this.value;
    const url = new URL(window.location);
    if (shopId) {
        url.searchParams.set('shop_id', shopId);
    } else {
        url.searchParams.delete('shop_id');
    }
    window.location.href = url.toString();
});

// Date picker functionality
document.getElementById('dateFrom').addEventListener('change', function() {
    updateDateFilter();
});

document.getElementById('dateTo').addEventListener('change', function() {
    updateDateFilter();
});

function updateDateFilter() {
    const dateFrom = document.getElementById('dateFrom').value;
    const dateTo = document.getElementById('dateTo').value;
    
    // Validate date range
    if (dateFrom && dateTo) {
        if (new Date(dateFrom) > new Date(dateTo)) {
            alert('Ngày bắt đầu không thể lớn hơn ngày kết thúc!');
            return;
        }
        
        const url = new URL(window.location);
        url.searchParams.set('date_from', dateFrom);
        url.searchParams.set('date_to', dateTo);
        window.location.href = url.toString();
    }
}

// Set max date for dateTo to today
document.addEventListener('DOMContentLoaded', function() {
    const dateToInput = document.getElementById('dateTo');
    const today = new Date().toISOString().split('T')[0];
    dateToInput.setAttribute('max', today);
    
    // Set min date for dateFrom to 1 year ago
    const dateFromInput = document.getElementById('dateFrom');
    const oneYearAgo = new Date();
    oneYearAgo.setFullYear(oneYearAgo.getFullYear() - 1);
    dateFromInput.setAttribute('min', oneYearAgo.toISOString().split('T')[0]);
});

// Quick date selection functions
function setQuickDate(type) {
    const today = new Date();
    const dateFromInput = document.getElementById('dateFrom');
    const dateToInput = document.getElementById('dateTo');
    
    let fromDate, toDate;
    
    switch(type) {
        case 'today':
            fromDate = toDate = today.toISOString().split('T')[0];
            break;
        case 'yesterday':
            const yesterday = new Date(today);
            yesterday.setDate(yesterday.getDate() - 1);
            fromDate = toDate = yesterday.toISOString().split('T')[0];
            break;
        case 'week':
            const weekAgo = new Date(today);
            weekAgo.setDate(weekAgo.getDate() - 7);
            fromDate = weekAgo.toISOString().split('T')[0];
            toDate = today.toISOString().split('T')[0];
            break;
        case 'month':
            const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
            fromDate = firstDay.toISOString().split('T')[0];
            toDate = today.toISOString().split('T')[0];
            break;
        case 'lastMonth':
            const lastMonth = new Date(today.getFullYear(), today.getMonth() - 1, 1);
            const lastMonthEnd = new Date(today.getFullYear(), today.getMonth(), 0);
            fromDate = lastMonth.toISOString().split('T')[0];
            toDate = lastMonthEnd.toISOString().split('T')[0];
            break;
        default:
            return;
    }
    
    dateFromInput.value = fromDate;
    dateToInput.value = toDate;
    
    // Trigger filter update
    updateDateFilter();
}

// Sync form submit
document.getElementById('syncForm').addEventListener('submit', function(e) {
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i>Đang đồng bộ...';
    
    // Re-enable button after 30 seconds
    setTimeout(() => {
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    }, 30000);
});

// Close modal when clicking outside
document.getElementById('syncModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeSyncModal();
    }
});

// Bulk actions functionality
document.querySelectorAll('input[type="checkbox"]').forEach(checkbox => {
    checkbox.addEventListener('change', function() {
        // Handle bulk selection logic here
        updateBulkActions();
    });
});

function updateBulkActions() {
    const checkedBoxes = document.querySelectorAll('tbody input[type="checkbox"]:checked');
    const bulkActions = document.querySelectorAll('.bulk-action');
    
    if (checkedBoxes.length > 0) {
        bulkActions.forEach(action => action.style.display = 'block');
    } else {
        bulkActions.forEach(action => action.style.display = 'none');
    }
}

// Select all functionality
document.querySelector('thead input[type="checkbox"]').addEventListener('change', function() {
    const tbodyCheckboxes = document.querySelectorAll('tbody input[type="checkbox"]');
    tbodyCheckboxes.forEach(checkbox => {
        checkbox.checked = this.checked;
    });
    updateBulkActions();
});

// Mark as Shipped Modal Functions
let currentOrderId = null;

function openMarkShippedModal(orderId) {
    currentOrderId = orderId;
    document.getElementById('markShippedModal').classList.remove('hidden');
    
    // Load order info and shipping providers
    loadOrderShippingInfo(orderId);
}

function closeMarkShippedModal() {
    document.getElementById('markShippedModal').classList.add('hidden');
    currentOrderId = null;
    
    // Reset form
    document.getElementById('markShippedForm').reset();
    document.getElementById('shipping_provider_id').innerHTML = '<option value="">-- Chọn đơn vị vận chuyển --</option>';
    document.getElementById('orderInfo').innerHTML = '';
    document.getElementById('lineItemsList').innerHTML = '';
}

async function loadOrderShippingInfo(orderId) {
    try {
        // Show loading state
        document.getElementById('loadingProviders').classList.remove('hidden');
        
        // Load order info
        const orderResponse = await fetch(`/tiktok/shipping/orders/${orderId}/info`);
        const orderData = await orderResponse.json();
        
        if (orderData.success) {
            // Display order info
            const orderInfo = orderData.data.order;
            document.getElementById('orderInfo').innerHTML = `
                <div class="flex items-center justify-between">
                    <div>
                        <h4 class="text-sm font-medium text-white">Order ID: ${orderInfo.order_id}</h4>
                        <p class="text-xs text-gray-400">Shop: ${orderInfo.shop_name}</p>
                    </div>
                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-orange-700 text-orange-200">
                        ${orderInfo.order_status}
                    </span>
                </div>
            `;
            
            // Load shipping providers
            if (orderInfo.delivery_option_id) {
                await loadShippingProviders(orderId, orderInfo.delivery_option_id);
            } else {
                showError('Không tìm thấy delivery_option_id trong đơn hàng');
            }
            
            // Load line items
            loadLineItems(orderData.data.line_items);
        } else {
            showError('Không thể tải thông tin đơn hàng: ' + orderData.error);
        }
    } catch (error) {
        console.error('Error loading order info:', error);
        showError('Có lỗi xảy ra khi tải thông tin đơn hàng');
    } finally {
        document.getElementById('loadingProviders').classList.add('hidden');
    }
}

async function loadShippingProviders(orderId, deliveryOptionId) {
    try {
        const response = await fetch(`/tiktok/shipping/orders/${orderId}/providers`);
        const data = await response.json();
        
        if (data.success) {
            const select = document.getElementById('shipping_provider_id');
            select.innerHTML = '<option value="">-- Chọn đơn vị vận chuyển --</option>';
            
            if (data.data.shipping_providers && data.data.shipping_providers.length > 0) {
                data.data.shipping_providers.forEach(provider => {
                    const option = document.createElement('option');
                    option.value = provider.id;
                    option.textContent = provider.name;
                    select.appendChild(option);
                });
            } else {
                showError('Không tìm thấy đơn vị vận chuyển nào');
            }
        } else {
            showError('Không thể tải danh sách đơn vị vận chuyển: ' + data.error);
        }
    } catch (error) {
        console.error('Error loading shipping providers:', error);
        showError('Có lỗi xảy ra khi tải danh sách đơn vị vận chuyển');
    }
}

function loadLineItems(lineItems) {
    const container = document.getElementById('lineItemsList');
    container.innerHTML = '';
    
    if (lineItems && lineItems.length > 0) {
        lineItems.forEach((item, index) => {
            const itemDiv = document.createElement('div');
            itemDiv.className = 'flex items-center p-2 bg-gray-600 rounded';
            itemDiv.innerHTML = `
                <input type="checkbox" id="line_item_${index}" name="order_line_item_ids[]" value="${item.id || index}" checked
                       class="h-4 w-4 text-teal-600 focus:ring-teal-500 border-gray-600 rounded bg-gray-700">
                <label for="line_item_${index}" class="ml-2 text-sm text-gray-300">
                    ${item.product_name || 'Sản phẩm'} - Số lượng: ${item.quantity || 1}
                </label>
            `;
            container.appendChild(itemDiv);
        });
    } else {
        container.innerHTML = '<p class="text-sm text-gray-400">Không có sản phẩm nào trong đơn hàng</p>';
    }
}

// Handle form submission
document.getElementById('markShippedForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    if (!currentOrderId) {
        showError('Không tìm thấy ID đơn hàng');
        return;
    }
    
    const formData = new FormData(this);
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    
    // Disable submit button
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i>Đang xử lý...';
    
    try {
        const response = await fetch(`/tiktok/shipping/orders/${currentOrderId}/mark-shipped`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                tracking_number: formData.get('tracking_number'),
                shipping_provider_id: formData.get('shipping_provider_id'),
                shipping_provider_name: document.getElementById('shipping_provider_id').selectedOptions[0]?.textContent || '',
                order_line_item_ids: Array.from(document.querySelectorAll('input[name="order_line_item_ids[]"]:checked')).map(cb => cb.value)
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            let successMessage = '✅ Đã đánh dấu gói hàng đã gửi thành công!';
            
            if (data.data.synced) {
                successMessage += ' Đã đồng bộ thông tin từ TikTok.';
                if (data.data.status_changed) {
                    successMessage += ` Trạng thái đã thay đổi thành: ${data.data.new_status}`;
                }
            } else {
                successMessage += ' (Không thể đồng bộ từ TikTok)';
                if (data.data.sync_message) {
                    successMessage += ` - ${data.data.sync_message}`;
                }
            }
            
            showSuccess(successMessage);
            closeMarkShippedModal();
            
            // Refresh the page to show updated status and tracking info
            setTimeout(() => {
                window.location.reload();
            }, 2000);
        } else {
            showError('❌ Lỗi: ' + data.error);
        }
    } catch (error) {
        console.error('Error marking as shipped:', error);
        showError('Có lỗi xảy ra khi đánh dấu gói hàng đã gửi');
    } finally {
        // Re-enable submit button
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    }
});

// Close modal when clicking outside
document.getElementById('markShippedModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeMarkShippedModal();
    }
});

// Utility functions
function showError(message) {
    showToast(message, 'error');
}

function showSuccess(message) {
    showToast(message, 'success');
}

function showToast(message, type = 'info') {
    const toast = document.createElement('div');
    const bgColor = type === 'error' ? 'bg-red-600' : type === 'success' ? 'bg-green-600' : 'bg-blue-600';
    toast.className = `fixed top-4 right-4 ${bgColor} text-white px-6 py-3 rounded-lg shadow-lg z-50`;
    toast.textContent = message;
    document.body.appendChild(toast);
    
    // Remove toast after 5 seconds
    setTimeout(() => {
        if (document.body.contains(toast)) {
            document.body.removeChild(toast);
        }
    }, 5000);
}
</script>
@endpush

