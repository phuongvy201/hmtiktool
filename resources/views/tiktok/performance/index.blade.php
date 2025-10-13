@extends('layouts.app')

@section('title', 'GMV Performance Dashboard')

@section('head')
<style>
/* Custom animations and effects */
.glass-effect {
    background: rgba(31, 41, 55, 0.8);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(75, 85, 99, 0.3);
}

.metric-card {
    transition: all 0.3s ease;
    transform: translateY(0);
}

.metric-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.3), 0 10px 10px -5px rgba(0, 0, 0, 0.2);
}

.gradient-text {
    background: linear-gradient(135deg, #3b82f6, #8b5cf6, #ec4899);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.pulse-animation {
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.7; }
}

.chart-container {
    position: relative;
    height: 300px;
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

.trend-up {
    color: #10b981;
}

.trend-down {
    color: #ef4444;
}

.trend-neutral {
    color: #6b7280;
}

/* Custom scrollbar */
.custom-scrollbar::-webkit-scrollbar {
    width: 6px;
}

.custom-scrollbar::-webkit-scrollbar-track {
    background: #374151;
    border-radius: 3px;
}

.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #6b7280;
    border-radius: 3px;
}

.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: #9ca3af;
}
</style>
@endsection

@section('content')
<!-- Loading Overlay -->
<div id="loadingOverlay" class="loading-overlay" style="display: none;">
    <div class="text-center">
        <div class="loading-spinner mb-4"></div>
        <div class="text-white text-lg font-medium">Đang tải dữ liệu performance...</div>
        <div class="text-gray-300 text-sm mt-2">Vui lòng chờ trong giây lát</div>
    </div>
</div>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    <!-- Enhanced Header -->
    <div class="mb-8">
        <div class="bg-gradient-to-r from-blue-600 via-purple-600 to-pink-600 rounded-2xl p-8 shadow-2xl">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-4xl font-bold text-white flex items-center">
                        <div class="p-3 bg-white bg-opacity-20 rounded-xl mr-4">
                            <i class="fas fa-chart-line text-3xl"></i>
                        </div>
                        <span class="gradient-text">GMV Performance Dashboard</span>
                    </h1>
                    <p class="text-blue-100 mt-3 flex items-center text-lg">
                        <i class="fas fa-store mr-2"></i>
                        TikTok Shop Analytics & Performance Metrics
                    </p>
                </div>
                <div class="hidden lg:block">
                    <div class="text-right">
                        <div class="text-3xl font-bold text-white" id="totalGmv">
                            @if($performanceData && isset($performanceData['summary']))
                                ${{ number_format($performanceData['summary']['total_gmv'], 2) }}
                            @else
                                $0.00
                            @endif
                        </div>
                        <div class="text-blue-100">Total GMV</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters Section -->
    <div class="bg-gray-800 shadow-xl rounded-xl overflow-hidden mb-8">
        <div class="px-6 py-6 bg-gradient-to-r from-gray-800 to-gray-900">
            <form method="GET" action="{{ route('tiktok.performance.index') }}" id="performanceForm" class="space-y-6">
                <div class="grid grid-cols-1 lg:grid-cols-6 gap-6">
                    <!-- Shop Selection -->
                    <div class="lg:col-span-2">
                        <label class="block text-sm font-medium text-gray-300 mb-3">Shop Selection:</label>
                        <select name="shop_id" id="shopSelect" class="block w-full px-4 py-3 border border-gray-600 rounded-xl shadow-sm bg-gray-700 text-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-300">
                            <option value="">-- Chọn shop để xem performance --</option>
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

                    <!-- Date Range -->
                    <div class="lg:col-span-2">
                        <label class="block text-sm font-medium text-gray-300 mb-3">Khoảng thời gian:</label>
                        <div class="grid grid-cols-2 gap-3">
                            <input type="date" name="start_date" id="startDate" 
                                   class="block w-full px-3 py-3 border border-gray-600 rounded-xl shadow-sm bg-gray-700 text-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-300" 
                                   value="{{ request('start_date', date('Y-m-d', strtotime('-7 days'))) }}">
                            <input type="date" name="end_date" id="endDate" 
                                   class="block w-full px-3 py-3 border border-gray-600 rounded-xl shadow-sm bg-gray-700 text-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-300" 
                                   value="{{ request('end_date', date('Y-m-d')) }}">
                        </div>
                    </div>

                    <!-- Granularity -->
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-3">Chi tiết:</label>
                        <select name="granularity" id="granularity" class="block w-full px-4 py-3 border border-gray-600 rounded-xl shadow-sm bg-gray-700 text-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-300">
                            <option value="1D" {{ request('granularity', '1D') == '1D' ? 'selected' : '' }}>Theo ngày</option>
                            <option value="ALL" {{ request('granularity') == 'ALL' ? 'selected' : '' }}>Tổng hợp</option>
                        </select>
                    </div>

                    <!-- Actions -->
                    <div class="flex flex-col justify-end space-y-3">
                        <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-3 px-4 rounded-xl transition duration-200 flex items-center justify-center">
                            <i class="fas fa-search mr-2"></i>Load Data
                        </button>
                        <button type="button" id="refreshData" class="w-full bg-green-600 hover:bg-green-700 text-white font-medium py-3 px-4 rounded-xl transition duration-200 flex items-center justify-center">
                            <i class="fas fa-sync-alt mr-2"></i>Refresh
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @if($performanceData && $selectedShop)
        <!-- Key Metrics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <!-- GMV Card -->
            <div class="metric-card bg-gradient-to-br from-blue-600 to-blue-700 rounded-xl p-6 text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-blue-200 text-sm font-medium">Total GMV</p>
                        <p class="text-3xl font-bold">${{ number_format($performanceData['summary']['total_gmv'], 2) }}</p>
                        <p class="text-blue-200 text-xs mt-1">{{ $performanceData['summary']['total_orders'] }} orders</p>
                    </div>
                    <div class="p-3 bg-blue-500 bg-opacity-20 rounded-full">
                        <i class="fas fa-dollar-sign text-blue-200 text-2xl"></i>
                    </div>
                </div>
            </div>

            <!-- Orders Card -->
            <div class="metric-card bg-gradient-to-br from-green-600 to-green-700 rounded-xl p-6 text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-green-200 text-sm font-medium">Total Orders</p>
                        <p class="text-3xl font-bold">{{ number_format($performanceData['summary']['total_orders']) }}</p>
                        <p class="text-green-200 text-xs mt-1">{{ number_format($performanceData['summary']['total_units']) }} units</p>
                    </div>
                    <div class="p-3 bg-green-500 bg-opacity-20 rounded-full">
                        <i class="fas fa-shopping-cart text-green-200 text-2xl"></i>
                    </div>
                </div>
            </div>

            <!-- Buyers Card -->
            <div class="metric-card bg-gradient-to-br from-purple-600 to-purple-700 rounded-xl p-6 text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-purple-200 text-sm font-medium">Total Buyers</p>
                        <p class="text-3xl font-bold">{{ number_format($performanceData['summary']['total_buyers']) }}</p>
                        <p class="text-purple-200 text-xs mt-1">${{ number_format($performanceData['summary']['avg_order_value'], 2) }} AOV</p>
                    </div>
                    <div class="p-3 bg-purple-500 bg-opacity-20 rounded-full">
                        <i class="fas fa-users text-purple-200 text-2xl"></i>
                    </div>
                </div>
            </div>

            <!-- Conversion Card -->
            <div class="metric-card bg-gradient-to-br from-orange-600 to-orange-700 rounded-xl p-6 text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-orange-200 text-sm font-medium">Conversion Rate</p>
                        <p class="text-3xl font-bold">{{ number_format($performanceData['summary']['conversion_rate'], 2) }}%</p>
                        <p class="text-orange-200 text-xs mt-1">{{ number_format($performanceData['summary']['total_impressions']) }} impressions</p>
                    </div>
                    <div class="p-3 bg-orange-500 bg-opacity-20 rounded-full">
                        <i class="fas fa-chart-line text-orange-200 text-2xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts and Detailed Analysis -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
            <!-- GMV Trend Chart -->
            <div class="bg-gray-800 shadow-xl rounded-xl overflow-hidden">
                <div class="px-6 py-4 bg-gradient-to-r from-gray-800 to-gray-900 border-b border-gray-700">
                    <h3 class="text-lg font-semibold text-white flex items-center">
                        <i class="fas fa-chart-area mr-3 text-blue-400"></i>
                        GMV Trend
                    </h3>
                </div>
                <div class="p-6">
                    <div class="chart-container">
                        <canvas id="gmvChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Orders Analysis -->
            <div class="bg-gray-800 shadow-xl rounded-xl overflow-hidden">
                <div class="px-6 py-4 bg-gradient-to-r from-gray-800 to-gray-900 border-b border-gray-700">
                    <h3 class="text-lg font-semibold text-white flex items-center">
                        <i class="fas fa-shopping-cart mr-3 text-green-400"></i>
                        Orders Analysis
                    </h3>
                </div>
                <div class="p-6">
                    <div class="chart-container">
                        <canvas id="ordersChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Detailed Performance Table -->
        <div class="bg-gray-800 shadow-xl rounded-xl overflow-hidden">
            <div class="px-6 py-4 bg-gradient-to-r from-gray-800 to-gray-900 border-b border-gray-700">
                <h3 class="text-lg font-semibold text-white flex items-center">
                    <i class="fas fa-table mr-3 text-purple-400"></i>
                    Daily Performance Breakdown
                </h3>
            </div>
            <div class="overflow-x-auto custom-scrollbar">
                <table class="min-w-full divide-y divide-gray-700">
                    <thead class="bg-gradient-to-r from-gray-700 to-gray-800">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Date</th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">GMV</th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Orders</th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Units Sold</th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Buyers</th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">AOV</th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Impressions</th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Page Views</th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Refunds</th>
                        </tr>
                    </thead>
                    <tbody class="bg-gray-800 divide-y divide-gray-700">
                        @forelse($performanceData['current_period'] as $day)
                        <tr class="hover:bg-gray-700 transition-colors duration-200">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-white">
                                {{ \Carbon\Carbon::parse($day['start_date'])->format('M j, Y') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-green-400">
                                ${{ number_format($day['gmv']['amount'], 2) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-white">
                                {{ number_format($day['orders']) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-white">
                                {{ number_format($day['units_sold']) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-white">
                                {{ number_format($day['buyers']) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-white">
                                ${{ number_format($day['avg_order_value']['amount'], 2) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-white">
                                {{ number_format($day['product_impressions']) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-white">
                                {{ number_format($day['product_page_views']) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-red-400">
                                ${{ number_format($day['refunds']['amount'], 2) }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center">
                                    <i class="fas fa-chart-line text-4xl text-gray-400 mb-4"></i>
                                    <div class="text-gray-400 text-lg font-medium">Không có dữ liệu performance</div>
                                    <div class="text-gray-500 text-sm mt-2">Vui lòng chọn shop và khoảng thời gian</div>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @else
        <!-- Empty State -->
        <div class="bg-gray-800 shadow-xl rounded-xl overflow-hidden">
            <div class="px-6 py-12 text-center">
                <div class="flex flex-col items-center">
                    <i class="fas fa-chart-line text-6xl text-gray-400 mb-6"></i>
                    <h3 class="text-2xl font-bold text-white mb-4">Welcome to GMV Performance Dashboard</h3>
                    <p class="text-gray-400 text-lg mb-6 max-w-2xl">
                        Chọn một shop từ dropdown trên để xem các chỉ số performance chi tiết bao gồm GMV, orders, conversion rate và nhiều hơn nữa.
                    </p>
                    <div class="flex items-center space-x-4 text-sm text-gray-500">
                        <div class="flex items-center">
                            <i class="fas fa-dollar-sign mr-2"></i>
                            GMV Tracking
                        </div>
                        <div class="flex items-center">
                            <i class="fas fa-shopping-cart mr-2"></i>
                            Order Analytics
                        </div>
                        <div class="flex items-center">
                            <i class="fas fa-chart-line mr-2"></i>
                            Conversion Metrics
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
// Global variables
let isLoading = false;
let gmvChart = null;
let ordersChart = null;

// Performance data from server
const performanceData = @json($performanceData ?? null);

// Initialize charts when data is available
document.addEventListener('DOMContentLoaded', function() {
    if (performanceData && performanceData.current_period) {
        initializeCharts();
    }
    
    // Set max date for date inputs
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('endDate').setAttribute('max', today);
    
    // Auto submit when shop changes
    document.getElementById('shopSelect').addEventListener('change', function() {
        if (this.value) {
            showLoadingOverlay();
            document.getElementById('performanceForm').submit();
        }
    });
});

// Initialize charts
function initializeCharts() {
    if (!performanceData || !performanceData.current_period) return;
    
    const dates = performanceData.current_period.map(day => 
        new Date(day.start_date).toLocaleDateString('en-US', { month: 'short', day: 'numeric' })
    );
    
    const gmvData = performanceData.current_period.map(day => day.gmv.amount);
    const ordersData = performanceData.current_period.map(day => day.orders);
    
    // GMV Chart
    const gmvCtx = document.getElementById('gmvChart').getContext('2d');
    if (gmvChart) gmvChart.destroy();
    
    gmvChart = new Chart(gmvCtx, {
        type: 'line',
        data: {
            labels: dates,
            datasets: [{
                label: 'GMV ($)',
                data: gmvData,
                borderColor: '#3b82f6',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                borderWidth: 3,
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    labels: {
                        color: '#d1d5db'
                    }
                }
            },
            scales: {
                x: {
                    ticks: {
                        color: '#9ca3af'
                    },
                    grid: {
                        color: '#374151'
                    }
                },
                y: {
                    ticks: {
                        color: '#9ca3af',
                        callback: function(value) {
                            return '$' + value.toLocaleString();
                        }
                    },
                    grid: {
                        color: '#374151'
                    }
                }
            }
        }
    });
    
    // Orders Chart
    const ordersCtx = document.getElementById('ordersChart').getContext('2d');
    if (ordersChart) ordersChart.destroy();
    
    ordersChart = new Chart(ordersCtx, {
        type: 'bar',
        data: {
            labels: dates,
            datasets: [{
                label: 'Orders',
                data: ordersData,
                backgroundColor: 'rgba(16, 185, 129, 0.8)',
                borderColor: '#10b981',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    labels: {
                        color: '#d1d5db'
                    }
                }
            },
            scales: {
                x: {
                    ticks: {
                        color: '#9ca3af'
                    },
                    grid: {
                        color: '#374151'
                    }
                },
                y: {
                    ticks: {
                        color: '#9ca3af'
                    },
                    grid: {
                        color: '#374151'
                    }
                }
            }
        }
    });
}

// Refresh data functionality
document.getElementById('refreshData').addEventListener('click', function() {
    if (isLoading) return;
    
    const shopId = document.getElementById('shopSelect').value;
    if (!shopId) {
        showError('Vui lòng chọn shop trước khi refresh');
        return;
    }
    
    refreshPerformanceData();
});

// Function to refresh performance data via AJAX
async function refreshPerformanceData() {
    if (isLoading) return;
    
    isLoading = true;
    showLoadingOverlay();
    
    try {
        const formData = new FormData(document.getElementById('performanceForm'));
        const params = new URLSearchParams(formData);
        
        const response = await fetch(`{{ route('tiktok.performance.data') }}?${params}`, {
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
            showError('Có lỗi xảy ra khi tải dữ liệu: ' + (data.message || 'Unknown error'));
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
    const toast = document.createElement('div');
    toast.className = 'fixed top-4 right-4 bg-red-600 text-white px-6 py-3 rounded-lg shadow-lg z-50';
    toast.textContent = message;
    document.body.appendChild(toast);
    
    setTimeout(() => {
        document.body.removeChild(toast);
    }, 5000);
}
</script>
@endsection
