@extends('layouts.app')

@section('title', 'TikTok Finance - Thanh toán')

@section('head')
<style>
.scrollbar-hide {
    -ms-overflow-style: none;  /* Internet Explorer 10+ */
    scrollbar-width: none;  /* Firefox */
}
.scrollbar-hide::-webkit-scrollbar { 
    display: none;  /* Safari and Chrome */
}

/* Custom date range picker styles */
.date-range-input {
    background: linear-gradient(135deg, #1f2937 0%, #374151 100%);
    border: 1px solid #4b5563;
    transition: all 0.3s ease;
}

.date-range-input:focus {
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

/* Status badges */
.status-paid {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    color: white;
    box-shadow: 0 2px 4px rgba(16, 185, 129, 0.3);
}

.status-pending {
    background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
    color: white;
    box-shadow: 0 2px 4px rgba(245, 158, 11, 0.3);
}

.status-failed {
    background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
    color: white;
    box-shadow: 0 2px 4px rgba(239, 68, 68, 0.3);
}

/* Amount highlighting */
.amount-positive {
    color: #10b981;
    font-weight: 600;
}

.amount-zero {
    color: #6b7280;
}

/* Hover effects */
.payment-row:hover {
    background: linear-gradient(135deg, #374151 0%, #4b5563 100%);
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
}
</style>
@endsection

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="bg-gray-800 shadow-lg rounded-lg">
        <!-- Header Section -->
        <div class="px-6 py-4 border-b border-gray-700">
            <h2 class="text-2xl font-bold text-white flex items-center">
                <i class="fas fa-chart-line mr-3 text-green-400"></i>
                TikTok Finance - Thanh toán
            </h2>
            <p class="text-gray-400 mt-1">Quản lý và theo dõi thanh toán từ TikTok Shop</p>
        </div>

        <!-- Filters Section -->
        <div class="px-3 sm:px-6 py-4 border-b border-gray-700 bg-gray-900">
            <form method="GET" action="{{ route('tiktok.finance.index') }}" class="space-y-4">
                <div class="grid grid-cols-1 lg:grid-cols-4 gap-4">
                    <!-- Date Range Filter -->
                    <div class="lg:col-span-2">
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
                        <div class="flex flex-col sm:flex-row gap-2">
                            <div class="relative flex-1">
                                <input type="date" id="dateFrom" name="date_from" 
                                       class="date-range-input block w-full px-3 py-2 rounded-md shadow-sm text-gray-300 focus:outline-none" 
                                       value="{{ request('date_from') ? date('Y-m-d', strtotime(request('date_from'))) : date('Y-m-01') }}">
                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                    <i class="fas fa-calendar-alt text-gray-400 text-xs"></i>
                                </div>
                            </div>
                            <div class="flex items-center justify-center text-gray-400 text-sm">
                                <span>đến</span>
                            </div>
                            <div class="relative flex-1">
                                <input type="date" id="dateTo" name="date_to" 
                                       class="date-range-input block w-full px-3 py-2 rounded-md shadow-sm text-gray-300 focus:outline-none" 
                                       value="{{ request('date_to') ? date('Y-m-d', strtotime(request('date_to'))) : date('Y-m-d') }}">
                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                    <i class="fas fa-calendar-alt text-gray-400 text-xs"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Shop Filter -->
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">Shop:</label>
                        <select name="shop_id" class="block w-full px-3 py-2 border border-gray-600 rounded-md shadow-sm bg-gray-800 text-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            <option value="">-- Chọn shop --</option>
                            @foreach($shops as $shop)
                                <option value="{{ $shop->id }}" {{ request('shop_id') == $shop->id ? 'selected' : '' }}>
                                    {{ $shop->shop_name }}
                                    @if($shop->integration && $shop->integration->status === 'active')
                                        <span class="text-green-400">(Hoạt động)</span>
                                    @else
                                        <span class="text-red-400">(Không hoạt động)</span>
                                    @endif
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Actions -->
                    <div class="flex flex-col justify-end space-y-2">
                        <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-md transition duration-200">
                            <i class="fas fa-search mr-2"></i>Tìm kiếm
                        </button>
                        @if(request('shop_id'))
                        <a href="{{ route('tiktok.finance.export', request()->all()) }}" 
                           class="w-full bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-4 rounded-md transition duration-200 text-center">
                            <i class="fas fa-download mr-2"></i>Export Excel
                        </a>
                        @endif
                    </div>
                </div>
            </form>
        </div>

        <!-- Summary Cards -->
        @if(!empty($payments))
        <div class="px-3 sm:px-6 py-4 border-b border-gray-700 bg-gray-900">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-gradient-to-r from-blue-600 to-blue-700 rounded-lg p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-blue-200 text-sm font-medium">Tổng Amount</p>
                            <p class="text-white text-2xl font-bold">${{ number_format($totalAmount, 2) }}</p>
                        </div>
                        <div class="p-3 bg-blue-500/20 rounded-full">
                            <i class="fas fa-dollar-sign text-blue-200 text-xl"></i>
                        </div>
                    </div>
                </div>
                
                <div class="bg-gradient-to-r from-orange-600 to-orange-700 rounded-lg p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-orange-200 text-sm font-medium">Tổng Reserve</p>
                            <p class="text-white text-2xl font-bold">${{ number_format($totalReserve, 2) }}</p>
                        </div>
                        <div class="p-3 bg-orange-500/20 rounded-full">
                            <i class="fas fa-lock text-orange-200 text-xl"></i>
                        </div>
                    </div>
                </div>
                
                <div class="bg-gradient-to-r from-green-600 to-green-700 rounded-lg p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-green-200 text-sm font-medium">Tổng Settle</p>
                            <p class="text-white text-2xl font-bold">${{ number_format($totalSettle, 2) }}</p>
                        </div>
                        <div class="p-3 bg-green-500/20 rounded-full">
                            <i class="fas fa-check-circle text-green-200 text-xl"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Payments Table -->
        <div class="overflow-hidden">
            @if(!empty($payments))
                <!-- Desktop Table View -->
                <div class="hidden lg:block overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-700">
                        <thead class="bg-gray-900">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">ID</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Shop Name</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Shop Profile</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Date Created</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Date Paid</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Amount</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Reserve</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Settle</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Bank Account</th>
                            </tr>
                        </thead>
                        <tbody class="bg-gray-800 divide-y divide-gray-700">
                            @foreach($payments as $payment)
                                <tr class="payment-row transition-all duration-200">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-white font-mono">
                                        {{ $payment->payment_id }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-200">
                                        {{ $payment->shop_name }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-200">
                                        {{ $payment->shop_profile ?? 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300">
                                        {{ $payment->create_time_formatted }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300">
                                        {{ $payment->paid_time_formatted }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="{{ $payment->status_classes }}">
                                            <i class="{{ $payment->status_icon }} mr-1"></i>
                                            {{ $payment->status }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium {{ $payment->amount_value > 0 ? 'amount-positive' : 'amount-zero' }}">
                                        {{ $payment->formatted_amount }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300">
                                        {{ $payment->formatted_reserve_amount }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium {{ $payment->settlement_amount_value > 0 ? 'amount-positive' : 'amount-zero' }}">
                                        {{ $payment->formatted_settlement_amount }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300 font-mono">
                                        {{ $payment->masked_bank_account }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Mobile & Tablet Card View -->
                <div class="lg:hidden">
                    <div class="space-y-4 p-3 sm:p-6">
                        @foreach($payments as $payment)
                            <div class="bg-gray-700 rounded-lg p-4 border border-gray-600">
                                <!-- Header -->
                                <div class="flex items-start justify-between mb-3">
                                    <div>
                                        <p class="text-sm font-bold text-white font-mono">{{ $payment->payment_id }}</p>
                                        <p class="text-xs text-gray-400">{{ $payment->shop_name }}</p>
                                    </div>
                                    <span class="{{ $payment->status_classes }}">
                                        <i class="{{ $payment->status_icon }} mr-1"></i>
                                        {{ $payment->status }}
                                    </span>
                                </div>

                                <!-- Payment details grid -->
                                <div class="grid grid-cols-2 gap-4 mb-3">
                                    <div>
                                        <p class="text-xs text-gray-400">Date Created</p>
                                        <p class="text-sm text-gray-200">
                                            {{ $payment->create_time_formatted }}
                                        </p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-gray-400">Date Paid</p>
                                        <p class="text-sm text-gray-200">
                                            {{ $payment->paid_time_formatted }}
                                        </p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-gray-400">Amount</p>
                                        <p class="text-sm font-medium {{ $payment->amount_value > 0 ? 'amount-positive' : 'amount-zero' }}">
                                            {{ $payment->formatted_amount }}
                                        </p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-gray-400">Settle</p>
                                        <p class="text-sm font-medium {{ $payment->settlement_amount_value > 0 ? 'amount-positive' : 'amount-zero' }}">
                                            {{ $payment->formatted_settlement_amount }}
                                        </p>
                                    </div>
                                </div>

                                <!-- Bank Account -->
                                <div class="pt-3 border-t border-gray-600">
                                    <p class="text-xs text-gray-400">Bank Account</p>
                                    <p class="text-sm text-gray-300 font-mono">
                                        {{ $payment->masked_bank_account }}
                                    </p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @else
                <div class="text-center py-8 sm:py-12 px-3 sm:px-6">
                    <div class="mb-4">
                        <i class="fas fa-chart-line text-4xl sm:text-6xl text-gray-600"></i>
                    </div>
                    <h3 class="text-base sm:text-lg font-medium text-gray-200 mb-2">Chưa có dữ liệu thanh toán</h3>
                    <p class="text-sm sm:text-base text-gray-400 mb-4 sm:mb-6 px-4">
                        Vui lòng chọn shop và khoảng thời gian để xem dữ liệu thanh toán.
                    </p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
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
}

// Auto submit form when shop changes
document.querySelector('select[name="shop_id"]').addEventListener('change', function() {
    if (this.value) {
        this.form.submit();
    }
});
</script>
@endpush
