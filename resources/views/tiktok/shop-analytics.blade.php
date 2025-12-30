@extends('layouts.app')

@section('title', 'Shop Orders Analysis')

@section('head')
<style>
.scrollbar-hide {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
.scrollbar-hide::-webkit-scrollbar { 
    display: none;
}

/* Enhanced animations */
.daily-card {
    transition: all 0.3s ease;
    transform: translateY(0);
}

.daily-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.3), 0 10px 10px -5px rgba(0, 0, 0, 0.2);
}

.stats-card {
    background: linear-gradient(135deg, rgba(59, 130, 246, 0.1) 0%, rgba(147, 51, 234, 0.1) 100%);
    border: 1px solid rgba(59, 130, 246, 0.2);
    transition: all 0.3s ease;
}

.stats-card:hover {
    background: linear-gradient(135deg, rgba(59, 130, 246, 0.2) 0%, rgba(147, 51, 234, 0.2) 100%);
    border-color: rgba(59, 130, 246, 0.4);
}

.glass-effect {
    background: rgba(31, 41, 55, 0.8);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(75, 85, 99, 0.3);
}

.table-row-hover:hover {
    background: linear-gradient(90deg, rgba(59, 130, 246, 0.05) 0%, rgba(147, 51, 234, 0.05) 100%);
}

.pulse-animation {
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0%, 100% {
        opacity: 1;
    }
    50% {
        opacity: 0.7;
    }
}

/* Skeleton Loading Styles */
.skeleton {
    background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
    background-size: 200% 100%;
    animation: loading 1.5s infinite;
}

.skeleton-dark {
    background: linear-gradient(90deg, #374151 25%, #4b5563 50%, #374151 75%);
    background-size: 200% 100%;
    animation: loading 1.5s infinite;
}

@keyframes loading {
    0% {
        background-position: 200% 0;
    }
    100% {
        background-position: -200% 0;
    }
}

.loading-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.8);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 9999;
}

.loading-spinner {
    width: 50px;
    height: 50px;
    border: 4px solid #374151;
    border-top: 4px solid #3b82f6;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
</style>
@endsection

@section('content')
<!-- Loading Overlay -->
<div id="loadingOverlay" class="loading-overlay" style="display: none;">
    <div class="text-center">
        <div class="loading-spinner mb-4"></div>
        <div class="text-white text-lg font-medium">Loading analytics data...</div>
        <div class="text-gray-300 text-sm mt-2">Please wait for a moment</div>
    </div>
</div>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    <!-- Enhanced Header with Gradient -->
    <div class="mb-6">
        <div class="bg-gradient-to-r from-blue-600 via-purple-600 to-indigo-600 rounded-xl p-6 shadow-2xl">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-white flex items-center">
                        <div class="p-2 bg-white bg-opacity-20 rounded-lg mr-4">
                            <i class="fas fa-chart-line text-2xl"></i>
                        </div>
                        Shop Orders Analysis
                    </h1>
                    <p class="text-blue-100 mt-2 flex items-center text-lg">
                        <i class="fas fa-calendar-alt mr-2"></i>
                        Last 7 Days (GMT+7) - Excluding canceled orders
                    </p>
                </div>
                <div class="hidden md:block">
                    <div class="text-right">
                        <div class="text-2xl font-bold text-white">
                            {{ collect($dailyOrders)->sum('orders') }}
                        </div>
                        <div class="text-blue-100">Total Orders</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-gray-800 shadow-xl rounded-xl overflow-hidden">
        <!-- Stats Overview Cards -->
        <div class="px-6 py-4 bg-gradient-to-r from-gray-800 to-gray-900 border-b border-gray-700">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="stats-card rounded-lg p-4">
                    <div class="flex items-center">
                        <div class="p-2 bg-blue-500 bg-opacity-20 rounded-lg">
                            <i class="fas fa-shopping-cart text-blue-400"></i>
                        </div>
                        <div class="ml-3">
                            <div class="text-sm text-gray-400">Total Orders</div>
                            <div class="text-xl font-bold text-white">{{ collect($dailyOrders)->sum('orders') }}</div>
                        </div>
                    </div>
                </div>
                
                <div class="stats-card rounded-lg p-4">
                    <div class="flex items-center">
                        <div class="p-2 bg-green-500 bg-opacity-20 rounded-lg">
                            <i class="fas fa-boxes text-green-400"></i>
                        </div>
                        <div class="ml-3">
                            <div class="text-sm text-gray-400">Total Items</div>
                            <div class="text-xl font-bold text-white">{{ collect($dailyOrders)->sum('items') }}</div>
                        </div>
                    </div>
                </div>
                
                <div class="stats-card rounded-lg p-4">
                    <div class="flex items-center">
                        <div class="p-2 bg-purple-500 bg-opacity-20 rounded-lg">
                            <i class="fas fa-store text-purple-400"></i>
                        </div>
                        <div class="ml-3">
                            <div class="text-sm text-gray-400">Active shops</div>
                            <div class="text-xl font-bold text-white">{{ count($analytics) }}</div>
                        </div>
                    </div>
                </div>
                
                <div class="stats-card rounded-lg p-4">
                    <div class="flex items-center">
                        <div class="p-2 bg-orange-500 bg-opacity-20 rounded-lg">
                            <i class="fas fa-chart-line text-orange-400"></i>
                        </div>
                        <div class="ml-3">
                            <div class="text-sm text-gray-400">Avg daily orders</div>
                            <div class="text-xl font-bold text-white">{{ round(collect($dailyOrders)->avg('orders'), 1) }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Enhanced Daily Orders Section -->
        <div class="px-6 py-6 border-b border-gray-700">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-xl font-bold text-white flex items-center">
                    <i class="fas fa-calendar-week mr-3 text-blue-400"></i>
                    Daily orders breakdown
                </h3>
                <div class="text-sm text-gray-400">
                    <i class="fas fa-info-circle mr-1"></i>
                        Hover for details
                </div>
            </div>
            
            <!-- Enhanced Daily Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-7 gap-4">
                @php
                    $colors = [
                        ['from' => 'from-blue-600', 'to' => 'to-blue-700', 'icon' => 'fas fa-sun'],
                        ['from' => 'from-purple-500', 'to' => 'to-purple-600', 'icon' => 'fas fa-moon'],
                        ['from' => 'from-purple-600', 'to' => 'to-purple-700', 'icon' => 'fas fa-calendar-minus'],
                        ['from' => 'from-pink-500', 'to' => 'to-pink-600', 'icon' => 'fas fa-calendar-times'],
                        ['from' => 'from-pink-400', 'to' => 'to-pink-500', 'icon' => 'fas fa-calendar-check'],
                        ['from' => 'from-orange-400', 'to' => 'to-orange-500', 'icon' => 'fas fa-calendar-day'],
                        ['from' => 'from-orange-500', 'to' => 'to-orange-600', 'icon' => 'fas fa-calendar-week']
                    ];
                @endphp
                
                @foreach($dailyOrders as $index => $day)
                <div class="daily-card bg-gradient-to-br {{ $colors[$index]['from'] }} {{ $colors[$index]['to'] }} rounded-xl p-5 text-white relative overflow-hidden group">
                    <!-- Background Pattern -->
                    <div class="absolute top-0 right-0 w-16 h-16 bg-white bg-opacity-10 rounded-full -mr-8 -mt-8"></div>
                    <div class="absolute bottom-0 left-0 w-12 h-12 bg-white bg-opacity-5 rounded-full -ml-6 -mb-6"></div>
                    
                    <!-- Content -->
                    <div class="relative z-10">
                        <div class="flex items-center justify-between mb-2">
                            <i class="{{ $colors[$index]['icon'] }} text-lg opacity-80"></i>
                            <div class="text-xs bg-white bg-opacity-20 px-2 py-1 rounded-full">
                                {{ $day['date']->format('M j') }}
                            </div>
                        </div>
                        <div class="text-sm font-medium opacity-90 mb-1">{{ $day['day'] }}</div>
                        <div class="text-3xl font-bold mb-1">{{ $day['orders'] }}</div>
                        <div class="text-xs opacity-75 flex items-center">
                            <i class="fas fa-box mr-1"></i>
                            {{ $day['items'] }} items
                        </div>
                    </div>
                    
                    <!-- Hover Effect -->
                    <div class="absolute inset-0 bg-white bg-opacity-10 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Enhanced Search Section -->
        <div class="px-6 py-6 border-b border-gray-700 bg-gradient-to-r from-gray-800 to-gray-900">
            <div class="flex flex-col md:flex-row gap-4 items-center">
                <div class="flex-1 relative">
                    <input type="text" 
                           id="shopSearch" 
                           placeholder="Search by Shop ID or Profile..." 
                           class="w-full px-4 py-3 pl-12 bg-gray-700 border border-gray-600 rounded-xl text-gray-300 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-300">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                        <i class="fas fa-search text-gray-400"></i>
                    </div>
                </div>
                <div class="flex gap-2">
                    <button id="refreshData" class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition-colors duration-200 flex items-center">
                        <i class="fas fa-sync-alt mr-2"></i>
                        Refresh Data
                    </button>
                    <button class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors duration-200 flex items-center">
                        <i class="fas fa-filter mr-2"></i>
                        Filter
                    </button>
                    <button class="px-4 py-2 bg-gray-700 hover:bg-gray-600 text-gray-300 rounded-lg transition-colors duration-200 flex items-center">
                        <i class="fas fa-download mr-2"></i>
                        Export
                    </button>
                </div>
            </div>
        </div>

        <!-- Enhanced Analytics Table -->
        <div class="overflow-x-auto">
            <div class="px-6 py-4 bg-gradient-to-r from-gray-800 to-gray-900 border-b border-gray-700">
                <h3 class="text-lg font-semibold text-white flex items-center">
                    <i class="fas fa-table mr-3 text-blue-400"></i>
                    Shop Performance Details
                </h3>
            </div>
            
            <table class="min-w-full divide-y divide-gray-700">
                <thead class="bg-gradient-to-r from-gray-700 to-gray-800">
                    <tr>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-medium text-gray-300 uppercase tracking-wider cursor-pointer hover:bg-gray-600 transition-colors">
                            <div class="flex items-center">
                                <i class="fas fa-store mr-2 text-blue-400"></i>
                                Shop Profile
                                <i class="fas fa-sort-up ml-1 text-blue-400"></i>
                            </div>
                        </th>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-medium text-gray-300 uppercase tracking-wider cursor-pointer hover:bg-gray-600 transition-colors">
                            <div class="flex items-center">
                                <i class="fas fa-list-alt mr-2 text-green-400"></i>
                                Active Listing
                                <i class="fas fa-sort-up ml-1 text-blue-400"></i>
                            </div>
                        </th>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-medium text-gray-300 uppercase tracking-wider cursor-pointer hover:bg-gray-600 transition-colors">
                            <div class="flex items-center">
                                <i class="fas fa-plus-circle mr-2 text-yellow-400"></i>
                                Today Listing
                                <i class="fas fa-sort-up ml-1 text-blue-400"></i>
                            </div>
                        </th>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-medium text-gray-300 uppercase tracking-wider cursor-pointer hover:bg-gray-600 transition-colors">
                            <div class="flex items-center">
                                <i class="fas fa-plus mr-2 text-orange-400"></i>
                                Yesterday Listing
                                <i class="fas fa-sort-up ml-1 text-blue-400"></i>
                            </div>
                        </th>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-medium text-gray-300 uppercase tracking-wider cursor-pointer hover:bg-gray-600 transition-colors">
                            <div class="flex items-center">
                                <i class="fas fa-chart-bar mr-2 text-purple-400"></i>
                                All Time Order
                                <i class="fas fa-sort-up ml-1 text-blue-400"></i>
                            </div>
                        </th>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-medium text-gray-300 uppercase tracking-wider cursor-pointer hover:bg-gray-600 transition-colors">
                            <div class="flex items-center">
                                <i class="fas fa-sun mr-2 text-blue-400"></i>
                                Today
                                <i class="fas fa-sort-up ml-1 text-blue-400"></i>
                            </div>
                        </th>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-medium text-gray-300 uppercase tracking-wider cursor-pointer hover:bg-gray-600 transition-colors">
                            <div class="flex items-center">
                                <i class="fas fa-moon mr-2 text-purple-400"></i>
                                Yesterday
                                <i class="fas fa-sort-up ml-1 text-blue-400"></i>
                            </div>
                        </th>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-medium text-gray-300 uppercase tracking-wider cursor-pointer hover:bg-gray-600 transition-colors">
                            <div class="flex items-center">
                                <i class="fas fa-calendar-minus mr-2 text-pink-400"></i>
                                2 Days Ago
                                <i class="fas fa-sort-up ml-1 text-blue-400"></i>
                            </div>
                        </th>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-medium text-gray-300 uppercase tracking-wider cursor-pointer hover:bg-gray-600 transition-colors">
                            <div class="flex items-center">
                                <i class="fas fa-calendar-times mr-2 text-red-400"></i>
                                3 Days Ago
                                <i class="fas fa-sort-up ml-1 text-blue-400"></i>
                            </div>
                        </th>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-medium text-gray-300 uppercase tracking-wider cursor-pointer hover:bg-gray-600 transition-colors">
                            <div class="flex items-center">
                                <i class="fas fa-calendar-check mr-2 text-green-400"></i>
                                4 Days Ago
                                <i class="fas fa-sort-up ml-1 text-blue-400"></i>
                            </div>
                        </th>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-medium text-gray-300 uppercase tracking-wider cursor-pointer hover:bg-gray-600 transition-colors">
                            <div class="flex items-center">
                                <i class="fas fa-calendar-day mr-2 text-yellow-400"></i>
                                5 Days Ago
                                <i class="fas fa-sort-up ml-1 text-blue-400"></i>
                            </div>
                        </th>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-medium text-gray-300 uppercase tracking-wider cursor-pointer hover:bg-gray-600 transition-colors">
                            <div class="flex items-center">
                                <i class="fas fa-calendar-week mr-2 text-indigo-400"></i>
                                6 Days Ago
                                <i class="fas fa-sort-up ml-1 text-blue-400"></i>
                            </div>
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-gray-800 divide-y divide-gray-700">
                    @forelse($paginatedAnalytics ?? $analytics as $shopData)
                    <tr class="table-row-hover transition-all duration-200 hover:shadow-lg">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-10 w-10">
                                    <div class="h-10 w-10 rounded-full bg-gradient-to-r from-blue-500 to-purple-600 flex items-center justify-center">
                                        <i class="fas fa-store text-white text-sm"></i>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-white">
                                        {{ $shopData['shop']->shop_name ?? 'Unknown Shop' }}
                                    </div>
                                    <div class="text-xs text-gray-400">
                                        ID: {{ $shopData['shop']->shop_id ?? 'N/A' }}
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    {{ $shopData['active_listings'] }}
                                </span>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                    {{ $shopData['today_listings'] }}
                                </span>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800">
                                    {{ $shopData['yesterday_listings'] }}
                                </span>
                            </div>
                        </td>
                       
                        <td class="px-6 py-4 whitespace-nowrap">
                            <a href="{{ route('tiktok.orders.index', ['shop_id' => $shopData['shop']->id]) }}" 
                               class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800 hover:bg-blue-200 transition-colors">
                                <i class="fas fa-external-link-alt mr-1"></i>
                                {{ $shopData['all_time_orders'] }}
                            </a>
                        </td>
                        @foreach($shopData['daily_orders'] as $index => $dailyOrder)
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            <div class="flex flex-col items-center">
                                <span class="text-sm font-medium text-white">{{ $dailyOrder['orders'] }}</span>
                                @if($dailyOrder['orders'] > 0)
                                    <div class="w-2 h-2 bg-green-400 rounded-full mt-1 pulse-animation"></div>
                                @endif
                            </div>
                        </td>
                        @endforeach
                    </tr>
                    @empty
                    <tr>
                        <td colspan="12" class="px-6 py-12 text-center">
                            <div class="flex flex-col items-center">
                                <i class="fas fa-chart-line text-4xl text-gray-400 mb-4"></i>
                                <div class="text-gray-400 text-lg font-medium">Không có dữ liệu analytics</div>
                                <div class="text-gray-500 text-sm mt-2">Hãy kiểm tra lại kết nối hoặc quyền truy cập</div>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if(isset($pagination) && $pagination['total'] > $pagination['per_page'])
        <div class="px-6 py-4 bg-gray-800 border-t border-gray-700">
            <div class="flex items-center justify-between">
                <div class="text-sm text-gray-400">
                    Hiển thị {{ $pagination['from'] }} đến {{ $pagination['to'] }} trong tổng số {{ $pagination['total'] }} shop
                </div>
                <div class="flex items-center space-x-2">
                    @if($pagination['current_page'] > 1)
                        <a href="?page={{ $pagination['current_page'] - 1 }}&per_page={{ $pagination['per_page'] }}" 
                           class="px-3 py-2 bg-gray-700 hover:bg-gray-600 text-gray-300 rounded-lg transition-colors">
                            <i class="fas fa-chevron-left"></i>
                        </a>
                    @endif
                    
                    @for($i = max(1, $pagination['current_page'] - 2); $i <= min($pagination['last_page'], $pagination['current_page'] + 2); $i++)
                        <a href="?page={{ $i }}&per_page={{ $pagination['per_page'] }}" 
                           class="px-3 py-2 {{ $i == $pagination['current_page'] ? 'bg-blue-600 text-white' : 'bg-gray-700 hover:bg-gray-600 text-gray-300' }} rounded-lg transition-colors">
                            {{ $i }}
                        </a>
                    @endfor
                    
                    @if($pagination['has_more_pages'])
                        <a href="?page={{ $pagination['current_page'] + 1 }}&per_page={{ $pagination['per_page'] }}" 
                           class="px-3 py-2 bg-gray-700 hover:bg-gray-600 text-gray-300 rounded-lg transition-colors">
                            <i class="fas fa-chevron-right"></i>
                        </a>
                    @endif
                </div>
            </div>
        </div>
        @endif

        <!-- Enhanced Mobile Card View (Hidden on desktop) -->
        <div class="lg:hidden p-6 bg-gradient-to-b from-gray-800 to-gray-900">
            <div class="space-y-6">
                @forelse($analytics as $shopData)
                <div class="glass-effect rounded-xl p-6 hover:shadow-xl transition-all duration-300">
                    <div class="flex items-center mb-4">
                        <div class="flex-shrink-0 h-12 w-12">
                            <div class="h-12 w-12 rounded-full bg-gradient-to-r from-blue-500 to-purple-600 flex items-center justify-center">
                                <i class="fas fa-store text-white"></i>
                            </div>
                        </div>
                        <div class="ml-4 flex-1">
                            <div class="text-white font-semibold text-lg">{{ $shopData['shop']->shop_name ?? 'Unknown Shop' }}</div>
                            <div class="text-gray-400 text-sm">ID: {{ $shopData['shop']->shop_id ?? 'N/A' }}</div>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div class="bg-gray-700 rounded-lg p-3">
                            <div class="flex items-center mb-1">
                                <i class="fas fa-list-alt text-green-400 mr-2"></i>
                                <div class="text-gray-400 text-sm">Active Listing</div>
                            </div>
                            <div class="text-white text-xl font-bold">{{ $shopData['active_listings'] }}</div>
                        </div>
                        <div class="bg-gray-700 rounded-lg p-3">
                            <div class="flex items-center mb-1">
                                <i class="fas fa-chart-bar text-purple-400 mr-2"></i>
                                <div class="text-gray-400 text-sm">All Time Orders</div>
                            </div>
                            <a href="{{ route('tiktok.orders.index', ['shop_id' => $shopData['shop']->id]) }}" 
                               class="text-blue-400 hover:text-blue-300 transition-colors text-xl font-bold">
                                {{ $shopData['all_time_orders'] }}
                            </a>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div class="bg-gray-700 rounded-lg p-3">
                            <div class="flex items-center mb-1">
                                <i class="fas fa-sun text-blue-400 mr-2"></i>
                                <div class="text-gray-400 text-sm">Today</div>
                            </div>
                            <div class="text-white text-xl font-bold flex items-center">
                                {{ $shopData['today_listings'] }}
                                @if($shopData['today_listings'] > 0)
                                    <div class="w-2 h-2 bg-green-400 rounded-full ml-2 pulse-animation"></div>
                                @endif
                            </div>
                        </div>
                        <div class="bg-gray-700 rounded-lg p-3">
                            <div class="flex items-center mb-1">
                                <i class="fas fa-moon text-purple-400 mr-2"></i>
                                <div class="text-gray-400 text-sm">Yesterday</div>
                            </div>
                            <div class="text-white text-xl font-bold flex items-center">
                                {{ $shopData['yesterday_listings'] }}
                                @if($shopData['yesterday_listings'] > 0)
                                    <div class="w-2 h-2 bg-green-400 rounded-full ml-2 pulse-animation"></div>
                                @endif
                            </div>
                        </div>
                    </div>
                    
                </div>
                @empty
                <div class="glass-effect rounded-xl p-12 text-center">
                    <div class="flex flex-col items-center">
                        <i class="fas fa-chart-line text-6xl text-gray-400 mb-6"></i>
                        <div class="text-gray-400 text-xl font-medium mb-2">Không có dữ liệu analytics</div>
                        <div class="text-gray-500 text-sm">Hãy kiểm tra lại kết nối hoặc quyền truy cập</div>
                    </div>
                </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

<script>
// Global variables
let isLoading = false;

// Search functionality
document.getElementById('shopSearch').addEventListener('input', function() {
    const searchTerm = this.value.toLowerCase();
    const rows = document.querySelectorAll('tbody tr');
    
    rows.forEach(row => {
        const shopName = row.querySelector('td:first-child').textContent.toLowerCase();
        if (shopName.includes(searchTerm)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
});

// Table sorting functionality
document.querySelectorAll('th[scope="col"]').forEach(header => {
    header.addEventListener('click', function() {
        const table = this.closest('table');
        const tbody = table.querySelector('tbody');
        const rows = Array.from(tbody.querySelectorAll('tr'));
        const columnIndex = Array.from(this.parentNode.children).indexOf(this);
        
        // Simple sorting (you can enhance this)
        rows.sort((a, b) => {
            const aText = a.children[columnIndex].textContent.trim();
            const bText = b.children[columnIndex].textContent.trim();
            
            // Try to parse as numbers first
            const aNum = parseFloat(aText);
            const bNum = parseFloat(bText);
            
            if (!isNaN(aNum) && !isNaN(bNum)) {
                return aNum - bNum;
            }
            
            return aText.localeCompare(bText);
        });
        
        // Re-append sorted rows
        rows.forEach(row => tbody.appendChild(row));
    });
});

// Refresh data functionality
document.getElementById('refreshData').addEventListener('click', function() {
    if (isLoading) return;
    
    refreshAnalyticsData();
});

// Function to refresh analytics data via AJAX
async function refreshAnalyticsData() {
    if (isLoading) return;
    
    isLoading = true;
    showLoadingOverlay();
    
    try {
        const response = await fetch('{{ route("tiktok.analytics.data") }}', {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        });
        
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        
        const data = await response.json();
        
        if (data.success) {
            // Reload the page to show updated data
            window.location.reload();
        } else {
            showError('Có lỗi xảy ra khi tải dữ liệu: ' + (data.error || 'Unknown error'));
        }
    } catch (error) {
        console.error('Error refreshing data:', error);
        showError('Có lỗi xảy ra khi tải dữ liệu. Vui lòng thử lại.');
    } finally {
        isLoading = false;
        hideLoadingOverlay();
    }
}

// Show loading overlay
function showLoadingOverlay() {
    document.getElementById('loadingOverlay').style.display = 'flex';
}

// Hide loading overlay
function hideLoadingOverlay() {
    document.getElementById('loadingOverlay').style.display = 'none';
}

// Show error message
function showError(message) {
    // Create a simple toast notification
    const toast = document.createElement('div');
    toast.className = 'fixed top-4 right-4 bg-red-600 text-white px-6 py-3 rounded-lg shadow-lg z-50';
    toast.textContent = message;
    document.body.appendChild(toast);
    
    // Remove toast after 5 seconds
    setTimeout(() => {
        document.body.removeChild(toast);
    }, 5000);
}

// Auto-refresh data every 5 minutes
setInterval(() => {
    if (!isLoading) {
        refreshAnalyticsData();
    }
}, 300000); // 5 minutes

// Show loading state on page load
document.addEventListener('DOMContentLoaded', function() {
    // Check if data is being loaded
    const hasData = document.querySelector('tbody tr');
    if (!hasData) {
        showLoadingOverlay();
    }
});
</script>
@endsection
