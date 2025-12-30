@extends('layouts.app')

@section('title', 'Order details #' . $order->order_id)

@section('content')
<div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8 py-4 sm:py-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center mb-4 sm:mb-6 gap-4">
        <div class="flex-1">
            <nav class="flex" aria-label="Breadcrumb">
                <ol class="flex items-center space-x-2 sm:space-x-4">
                    <li>        
                        <a href="{{ route('tiktok.orders.index') }}" class="text-blue-400 hover:text-blue-300 text-sm sm:text-base">TikTok Orders</a>
                    </li>
                    <li>
                        <div class="flex items-center">
                            <i class="fas fa-chevron-right text-gray-500 mx-1 sm:mx-2 text-xs"></i>
                            <span class="text-gray-400 text-sm sm:text-base">Order details</span>       
                        </div>
                    </li>
                </ol>
            </nav>
            <h1 class="text-xl sm:text-2xl lg:text-3xl font-bold text-white mt-2 flex flex-col sm:flex-row sm:items-center gap-2">
                <span class="flex items-center">
                    <i class="fas fa-shopping-cart mr-2 sm:mr-3 text-blue-400"></i>
                    <span class="hidden sm:inline">Order #</span>
                    <span class="break-all">{{ $order->order_id }}</span>
                </span>
            </h1>
        </div>
        <div class="flex-shrink-0">
            <a href="{{ route('tiktok.orders.index') }}" class="inline-flex items-center px-3 sm:px-4 py-2 border border-gray-600 text-xs sm:text-sm font-medium rounded-md text-gray-300 bg-gray-800 hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 focus:ring-offset-gray-800">
                <i class="fas fa-arrow-left mr-1 sm:mr-2"></i>
                <span class="hidden sm:inline">Back</span>  
                <span class="sm:hidden">Back</span>
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 sm:gap-6">
        <!-- Order Info -->
        <div class="lg:col-span-2 space-y-4 sm:space-y-6">
            <div class="bg-gray-800 shadow-lg rounded-lg">
                <div class="px-3 sm:px-6 py-3 sm:py-4 border-b border-gray-700">
                    <h3 class="text-base sm:text-lg font-medium text-white flex items-center">
                        <i class="fas fa-info-circle mr-2 text-blue-400"></i>
                        Order Information
                    </h3>   
                </div>
                <div class="px-3 sm:px-6 py-3 sm:py-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
                        <div class="space-y-3 sm:space-y-4">
                            <div>
                                <dt class="text-xs sm:text-sm font-medium text-gray-400">Order ID</dt>
                                <dd class="mt-1 flex items-center">
                                    <span class="text-sm text-gray-200 break-all">{{ $order->order_id }}</span>
                                    <button onclick="copyToClipboard('{{ $order->order_id }}')" class="ml-2 p-1 text-blue-400 hover:text-blue-300 focus:outline-none" title="Copy Order ID">
                                        <i class="fas fa-copy text-xs"></i>
                                    </button>
                                </dd>
                            </div>
                            <div>
                                <dt class="text-xs sm:text-sm font-medium text-gray-400">Order Number</dt>
                                <dd class="mt-1 flex items-center">
                                    <span class="text-sm text-gray-200">{{ $order->order_number ?: 'N/A' }}</span>
                                    @if($order->order_number)
                                        <button onclick="copyToClipboard('{{ $order->order_number }}')" class="ml-2 p-1 text-blue-400 hover:text-blue-300 focus:outline-none" title="Copy Order Number">
                                            <i class="fas fa-copy text-xs"></i>
                                        </button>
                                    @endif
                                </dd>
                            </div>
                            <div>
                                <dt class="text-xs sm:text-sm font-medium text-gray-400">Status</dt>
                                <dd class="mt-2">
                                    <span class="inline-flex items-center px-4 py-3 rounded-full text-base font-medium {{ $order->getStatusClasses() }}">
                                        <i class="{{ $order->getStatusIcon() }} mr-3 text-lg"></i>
                                        {{ $order->getStatusText() }}
                                    </span>
                                </dd>
                            </div>
                            <div>
                                <dt class="text-xs sm:text-sm font-medium text-gray-400">Shop</dt>
                                <dd class="mt-1 flex items-center">
                                    <div class="flex-shrink-0 h-6 w-6 sm:h-8 sm:w-8 mr-2 sm:mr-3">
                                        <div class="h-6 w-6 sm:h-8 sm:w-8 rounded-full bg-blue-600 flex items-center justify-center">
                                            <span class="text-xs sm:text-sm font-medium text-white">
                                                {{ substr($order->shop->shop_name ?? 'S', 0, 1) }}
                                            </span>
                                        </div>
                                    </div>
                                    <span class="text-sm text-gray-200 truncate">{{ $order->shop->shop_name ?? 'N/A' }}</span>
                                </dd>
                            </div>
                            <div>
                                <dt class="text-xs sm:text-sm font-medium text-gray-400">Created date</dt>
                                <dd class="mt-1 text-sm text-gray-200">
                                    {{ $order->create_time ? $order->create_time->format('d/m/Y H:i:s') : 'N/A' }}
                                </dd>
                            </div>
                            <div>
                                <dt class="text-xs sm:text-sm font-medium text-gray-400">Last updated</dt>
                                <dd class="mt-1 text-sm text-gray-200">
                                    {{ $order->update_time ? $order->update_time->format('d/m/Y H:i:s') : 'N/A' }}
                                </dd>
                            </div>
                        </div>
                        <div class="space-y-3 sm:space-y-4">
                            <div>
                                <dt class="text-xs sm:text-sm font-medium text-gray-400">Buyer</dt>
                                <dd class="mt-1 flex items-center">
                                    <span class="text-sm text-gray-200">{{ $order->buyer_username ?: 'N/A' }}</span>
                                    @if($order->buyer_username)
                                        <button onclick="copyToClipboard('{{ $order->buyer_username }}')" class="ml-2 p-1 text-blue-400 hover:text-blue-300 focus:outline-none" title="Copy Buyer Name">
                                            <i class="fas fa-copy text-xs"></i>
                                        </button>
                                    @endif
                                </dd>
                            </div>
                            <div>
                                <dt class="text-xs sm:text-sm font-medium text-gray-400">User ID</dt>
                                <dd class="mt-1 flex items-center">
                                    <span class="text-sm text-gray-200 break-all">{{ $order->buyer_user_id ?: 'N/A' }}</span>
                                    @if($order->buyer_user_id)
                                        <button onclick="copyToClipboard('{{ $order->buyer_user_id }}')" class="ml-2 p-1 text-blue-400 hover:text-blue-300 focus:outline-none" title="Copy User ID">
                                            <i class="fas fa-copy text-xs"></i>
                                        </button>
                                    @endif
                                </dd>
                            </div>
                            <div>
                                <dt class="text-xs sm:text-sm font-medium text-gray-400">Shipping method</dt>
                                <dd class="mt-1 text-sm text-gray-200">{{ $order->shipping_type ?: 'N/A' }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs sm:text-sm font-medium text-gray-400">Warehouse</dt>
                                <dd class="mt-1 text-sm text-gray-200">{{ $order->warehouse_name ?: $order->warehouse_id ?: 'N/A' }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs sm:text-sm font-medium text-gray-400">Sync Status</dt>
                                <dd class="mt-1">
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $order->sync_status === 'synced' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                        {{ $order->sync_status === 'synced' ? 'Synced' : 'Not synced' }}
                                    </span>
                                </dd>
                            </div>
                            <div>
                                <dt class="text-xs sm:text-sm font-medium text-gray-400">Last sync</dt>
                                <dd class="mt-1 text-sm text-gray-200">
                                    {{ $order->last_synced_at ? $order->last_synced_at->format('d/m/Y H:i:s') : 'N/A' }}
                                </dd>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Order Items -->
            @if($order->order_data && isset($order->order_data['line_items']))
                <div class="bg-gray-800 shadow-lg rounded-lg">
                    <div class="px-3 sm:px-6 py-3 sm:py-4 border-b border-gray-700">
                        <h3 class="text-base sm:text-lg font-medium text-white flex items-center">
                            <i class="fas fa-box mr-2 text-blue-400"></i>
                                    Products in order
                        </h3>
                    </div>
                    <div class="overflow-hidden">
                        <!-- Desktop Table View -->
                        <div class="hidden md:block overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-700">
                                <thead class="bg-gray-900">
                                    <tr>
                                        <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Product</th>
                                        <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">SKU</th>
                                        <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Original price</th>
                                        <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Sale price</th>
                                        <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-gray-800 divide-y divide-gray-700">
                                    @foreach($order->order_data['line_items'] as $item)
                                        <tr class="hover:bg-gray-700">
                                            <td class="px-3 sm:px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    @if(isset($item['sku_image']))
                                                        <img src="{{ $item['sku_image'] }}" 
                                                             alt="{{ $item['product_name'] ?? 'Product' }}"
                                                             class="h-10 w-10 sm:h-12 sm:w-12 rounded-lg object-cover mr-3 sm:mr-4">
                                                    @endif
                                                    <div class="min-w-0">
                                                        <div class="text-sm font-medium text-gray-200 truncate">{{ $item['product_name'] ?? 'N/A' }}</div>
                                                        <div class="text-sm text-gray-400 truncate">{{ $item['sku_name'] ?? 'N/A' }}</div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-3 sm:px-6 py-4 whitespace-nowrap">
                                                <span class="inline-flex px-2 py-1 text-xs font-medium bg-gray-700 text-gray-300 rounded-full">
                                                    {{ $item['seller_sku'] ?? 'N/A' }}
                                                </span>
                                            </td>
                                            <td class="px-3 sm:px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-400 line-through">
                                                    {{ number_format($item['original_price'] ?? 0, 2) }}
                                                </div>
                                                <div class="text-xs text-gray-500">{{ $item['currency'] ?? 'GBP' }}</div>
                                            </td>
                                            <td class="px-3 sm:px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm font-bold text-blue-400">
                                                    {{ number_format($item['sale_price'] ?? 0, 2) }}
                                                </div>
                                                <div class="text-xs text-gray-500">{{ $item['currency'] ?? 'GBP' }}</div>
                                            </td>
                                            <td class="px-3 sm:px-6 py-4 whitespace-nowrap">
                                                @php
                                                    $packageStatus = $item['package_status'] ?? 'UNKNOWN';
                                                    $statusColor = match($packageStatus) {
                                                        'TO_FULFILL' => 'yellow',
                                                        'PROCESSING' => 'blue',
                                                        default => 'green'
                                                    };
                                                @endphp
                                                <span class="inline-flex items-center px-3 py-2 rounded-full text-sm font-medium bg-{{ $statusColor }}-700 text-{{ $statusColor }}-200">
                                                    <i class="fas fa-box mr-2"></i>
                                                    {{ $item['display_status'] ?? $packageStatus ?? 'N/A' }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Mobile Card View -->
                        <div class="md:hidden">
                            <div class="space-y-3 p-3 sm:p-6">
                                @foreach($order->order_data['line_items'] as $item)
                                    <div class="bg-gray-700 rounded-lg p-3 border border-gray-600">
                                        <div class="flex items-start space-x-3">
                                            @if(isset($item['sku_image']))
                                                <img src="{{ $item['sku_image'] }}" 
                                                     alt="{{ $item['product_name'] ?? 'Product' }}"
                                                     class="h-12 w-12 rounded-lg object-cover flex-shrink-0">
                                            @endif
                                            <div class="flex-1 min-w-0">
                                                <h4 class="text-sm font-medium text-gray-200 truncate">{{ $item['product_name'] ?? 'N/A' }}</h4>
                                                <p class="text-xs text-gray-400 truncate">{{ $item['sku_name'] ?? 'N/A' }}</p>
                                                <div class="flex items-center justify-between mt-2">
                                                    <span class="inline-flex px-2 py-1 text-xs font-medium bg-gray-600 text-gray-300 rounded-full">
                                                        {{ $item['seller_sku'] ?? 'N/A' }}
                                                    </span>
                                                    @php
                                                        $packageStatus = $item['package_status'] ?? 'UNKNOWN';
                                                        $statusColor = match($packageStatus) {
                                                            'TO_FULFILL' => 'yellow',
                                                            'PROCESSING' => 'blue',
                                                            default => 'green'
                                                        };
                                                    @endphp
                                                    <span class="inline-flex items-center px-3 py-2 rounded-full text-sm font-medium bg-{{ $statusColor }}-700 text-{{ $statusColor }}-200">
                                                        <i class="fas fa-box mr-2"></i>
                                                        {{ $item['display_status'] ?? $packageStatus ?? 'N/A' }}
                                                    </span>
                                                </div>
                                                <div class="flex justify-between items-center mt-2">
                                                    <div class="text-xs text-gray-400">
                                                        <div class="line-through">{{ number_format($item['original_price'] ?? 0, 2) }} {{ $item['currency'] ?? 'GBP' }}</div>
                                                        <div class="text-sm font-bold text-blue-400">{{ number_format($item['sale_price'] ?? 0, 2) }} {{ $item['currency'] ?? 'GBP' }}</div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <!-- Order Summary -->
        <div class="space-y-4 sm:space-y-6">
            <!-- Payment Info -->
            @if($order->order_data && isset($order->order_data['payment']))
                <div class="bg-gray-800 shadow-lg rounded-lg">
                    <div class="px-3 sm:px-6 py-3 sm:py-4 border-b border-gray-700">
                        <h3 class="text-base sm:text-lg font-medium text-white flex items-center">
                            <i class="fas fa-credit-card mr-2 text-blue-400"></i>
                            Payment Information
                        </h3>
                    </div>
                    <div class="px-3 sm:px-6 py-3 sm:py-4">
                        @php $payment = $order->order_data['payment']; @endphp
                        <div class="space-y-2 sm:space-y-3">
                            <div class="flex justify-between">
                                <span class="text-xs sm:text-sm text-gray-400">Total product price:</span>
                                <span class="text-xs sm:text-sm font-medium text-gray-200">{{ number_format($payment['sub_total'] ?? 0, 2) }} {{ $payment['currency'] ?? 'GBP' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-xs sm:text-sm text-gray-400">Shipping fee:</span>
                                <span class="text-xs sm:text-sm font-medium text-gray-200">{{ number_format($payment['shipping_fee'] ?? 0, 2) }} {{ $payment['currency'] ?? 'GBP' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-xs sm:text-sm text-gray-400">Platform discount:</span>
                                <span class="text-xs sm:text-sm font-medium text-green-400">-{{ number_format($payment['platform_discount'] ?? 0, 2) }} {{ $payment['currency'] ?? 'GBP' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-xs sm:text-sm text-gray-400">Seller discount:</span>
                                <span class="text-xs sm:text-sm font-medium text-green-400">-{{ number_format($payment['seller_discount'] ?? 0, 2) }} {{ $payment['currency'] ?? 'GBP' }}</span>
                            </div>
                            <div class="border-t border-gray-700 pt-2 sm:pt-3">
                                <div class="flex justify-between">
                                    <span class="text-sm sm:text-base font-medium text-gray-200">Total:</span>
                                    <span class="text-sm sm:text-base font-bold text-blue-400">{{ number_format($payment['total_amount'] ?? 0, 2) }} {{ $payment['currency'] ?? 'GBP' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Shipping Info -->
            @if($order->order_data && isset($order->order_data['recipient_address']))
                <div class="bg-gray-800 shadow-lg rounded-lg">
                    <div class="px-3 sm:px-6 py-3 sm:py-4 border-b border-gray-700">
                        <h3 class="text-base sm:text-lg font-medium text-white flex items-center">
                            <i class="fas fa-truck mr-2 text-blue-400"></i>
                            Shipping Information
                        </h3>
                    </div>
                    <div class="px-3 sm:px-6 py-3 sm:py-4">
                        @php $address = $order->order_data['recipient_address']; @endphp
                        <div class="space-y-2 sm:space-y-3">
                            <div>
                                <dt class="text-xs sm:text-sm font-medium text-gray-400">Recipient</dt>
                                <dd class="mt-1 flex items-center">
                                    <span class="text-sm text-gray-200">{{ $address['name'] ?? 'N/A' }}</span>
                                    @if($address['name'] ?? false)
                                        <button onclick="copyToClipboard('{{ $address['name'] }}')" class="ml-2 p-1 text-blue-400 hover:text-blue-300 focus:outline-none" title="Copy Recipient Name">
                                            <i class="fas fa-copy text-xs"></i>
                                        </button>
                                    @endif
                                </dd>
                            </div>
                            <div>
                                                <dt class="text-xs sm:text-sm font-medium text-gray-400">Phone
                                <dd class="mt-1 flex items-center">
                                    <span class="text-sm text-gray-200">{{ $address['phone_number'] ?? 'N/A' }}</span>
                                    @if($address['phone_number'] ?? false)
                                        <button onclick="copyToClipboard('{{ $address['phone_number'] }}')" class="ml-2 p-1 text-blue-400 hover:text-blue-300 focus:outline-none" title="Copy Phone Number">
                                            <i class="fas fa-copy text-xs"></i>
                                        </button>
                                    @endif
                                </dd>
                            </div>
                            <div>
                                <dt class="text-xs sm:text-sm font-medium text-gray-400">Address</dt>
                                <dd class="mt-1 flex items-start">
                                    <span class="text-sm text-gray-200 break-words flex-1">{{ $address['full_address'] ?? 'N/A' }}</span>
                                    @if($address['full_address'] ?? false)
                                        <button onclick="copyToClipboard('{{ $address['full_address'] }}')" class="ml-2 p-1 text-blue-400 hover:text-blue-300 focus:outline-none flex-shrink-0" title="Copy Address">
                                            <i class="fas fa-copy text-xs"></i>
                                        </button>
                                    @endif
                                </dd>
                            </div>
                            @if(isset($address['postal_code']) && $address['postal_code'])
                                <div>
                                    <dt class="text-xs sm:text-sm font-medium text-gray-400">Postal code</dt>
                                    <dd class="mt-1 flex items-center">
                                        <span class="text-sm text-gray-200">{{ $address['postal_code'] }}</span>
                                        <button onclick="copyToClipboard('{{ $address['postal_code'] }}')" class="ml-2 p-1 text-blue-400 hover:text-blue-300 focus:outline-none" title="Copy Postal Code">
                                            <i class="fas fa-copy text-xs"></i>
                                        </button>
                                    </dd>
                                </div>
                            @endif
                            @if(isset($order->order_data['shipping_provider']))
                                <div>
                                    <dt class="text-xs sm:text-sm font-medium text-gray-400">Shipping provider</dt>
                                    <dd class="mt-1 text-sm text-gray-200">{{ $order->order_data['shipping_provider'] }}</dd>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endif

            <!-- Raw Data -->
            <div class="bg-gray-800 shadow-lg rounded-lg">
                <div class="px-3 sm:px-6 py-3 sm:py-4 border-b border-gray-700">
                    <h3 class="text-base sm:text-lg font-medium text-white flex items-center">
                        <i class="fas fa-code mr-2 text-blue-400"></i>
                        Raw data
                    </h3>
                </div>
                <div class="px-3 sm:px-6 py-3 sm:py-4">
                    <button class="inline-flex items-center px-2 sm:px-3 py-2 border border-gray-600 text-xs sm:text-sm font-medium rounded-md text-gray-300 bg-gray-800 hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 focus:ring-offset-gray-800 mb-3 w-full sm:w-auto" type="button" onclick="toggleRawData()">
                        <i class="fas fa-eye mr-1 sm:mr-2"></i>
                        <span id="rawDataToggleText">View raw data</span>
                    </button>
                    <div class="hidden" id="rawData">
                        <pre class="bg-gray-900 p-2 sm:p-4 rounded-lg text-xs overflow-auto max-h-64 sm:max-h-96 border border-gray-700 text-gray-300"><code>{{ json_encode($order->order_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</code></pre>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function toggleRawData() {
    const rawData = document.getElementById('rawData');
    const toggleText = document.getElementById('rawDataToggleText');
    
    if (rawData.classList.contains('hidden')) {
        rawData.classList.remove('hidden');
        toggleText.textContent = 'Hide raw data';
    } else {
        rawData.classList.add('hidden');
        toggleText.textContent = 'View raw data';
    }
}

function copyToClipboard(text) {
    // Create a temporary textarea element
    const textarea = document.createElement('textarea');
    textarea.value = text;
    textarea.style.position = 'fixed';
    textarea.style.left = '-999999px';
    textarea.style.top = '-999999px';
    document.body.appendChild(textarea);
    
    // Select and copy the text
    textarea.focus();
    textarea.select();
    
    try {
        document.execCommand('copy');
        
        // Show success feedback
        showCopyNotification('Copied: ' + text);
    } catch (err) {
        console.error('Failed to copy text: ', err);
        
        // Fallback for modern browsers
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(text).then(() => {
                showCopyNotification('Copied: ' + text);
            }).catch(err => {
                console.error('Failed to copy text: ', err);
                showCopyNotification('Error copying', 'error');
            });
        } else {
                    showCopyNotification('Error copying', 'error');
        }
    }
    
    // Remove the temporary element
    document.body.removeChild(textarea);
}

function showCopyNotification(message, type = 'success') {
    // Remove existing notifications
    const existingNotification = document.querySelector('.copy-notification');
    if (existingNotification) {
        existingNotification.remove();
    }
    
    // Create notification element
    const notification = document.createElement('div');
    notification.className = 'copy-notification fixed top-4 right-4 z-50 px-4 py-2 rounded-lg shadow-lg text-sm font-medium transition-all duration-300 transform translate-x-full';
    
    if (type === 'success') {
        notification.classList.add('bg-green-600', 'text-white');
    } else {
        notification.classList.add('bg-red-600', 'text-white');
    }
    
    notification.textContent = message;
    document.body.appendChild(notification);
    
    // Animate in
    setTimeout(() => {
        notification.classList.remove('translate-x-full');
    }, 100);
    
    // Animate out and remove
    setTimeout(() => {
        notification.classList.add('translate-x-full');
        setTimeout(() => {
            if (notification.parentNode) {
                notification.remove();
            }
        }, 300);
    }, 2000);
}
</script>
@endpush