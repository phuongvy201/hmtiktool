@extends('layouts.app')

@section('title', 'Product Management')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold text-white">Product Management</h1>
            <p class="text-gray-400 mt-2">Team: {{ $team->name }}</p>
        </div>
        
        <div class="flex items-center space-x-3">
            @can('view-products')
            <a href="{{ route('products.export', request()->query()) }}" 
               class="bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-4 rounded-lg transition duration-200 flex items-center">
                <i class="fas fa-download mr-2"></i>Export CSV
            </a>
            @endcan
            
            @can('create-products')
            <button type="button" onclick="showImportModal()" 
                    class="bg-purple-600 hover:bg-purple-700 text-white font-medium py-2 px-4 rounded-lg transition duration-200 flex items-center">
                <i class="fas fa-upload mr-2"></i>Import CSV
            </button>
            <a href="{{ route('products.download-template') }}" 
               class="bg-gray-600 hover:bg-gray-700 text-white font-medium py-2 px-3 rounded-lg transition duration-200 flex items-center" 
               title="Download CSV Template">
                <i class="fas fa-file-download"></i>
            </a>
            <a href="{{ route('products.create') }}" 
               class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg transition duration-200 flex items-center">
                <i class="fas fa-plus mr-2"></i>Create New Product
            </a>
            @endcan
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-gray-800 rounded-lg shadow-sm border border-gray-700 mb-6">
        <div class="p-4 border-b border-gray-700 flex items-center justify-between cursor-pointer" onclick="toggleFilters()">
            <div class="flex items-center space-x-3">
                <i class="fas fa-filter text-blue-400"></i>
                <h3 class="text-sm font-semibold text-white">Product Filters</h3>
                @if(request()->hasAny(['search', 'status', 'template_id', 'sku', 'user_id', 'is_active']))
                    <span class="bg-blue-500 text-white text-xs px-2 py-1 rounded-full" id="activeFiltersCount">
                        {{ collect(['search', 'status', 'template_id', 'sku', 'user_id', 'is_active'])->filter(fn($key) => request()->filled($key))->count() }}
                    </span>
                @endif
            </div>
            <i class="fas fa-chevron-down text-gray-400 transition-transform duration-200" id="filterToggleIcon"></i>
        </div>
        
        <form method="GET" action="{{ route('products.index') }}" id="filterForm" class="p-6 hidden">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-4">
                <!-- Search -->
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">
                        <i class="fas fa-search mr-1 text-blue-400"></i>Search
                    </label>
                    <input type="text" name="search" value="{{ request('search') }}" 
                           placeholder="Name, description, SKU..."
                           class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-white placeholder-gray-400">
                </div>

                <!-- SKU -->
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">
                        <i class="fas fa-barcode mr-1 text-purple-400"></i>SKU
                    </label>
                    <input type="text" name="sku" value="{{ request('sku') }}" 
                           placeholder="Enter SKU..."
                           class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-white placeholder-gray-400">
                </div>
                
                <!-- Status -->
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">
                        <i class="fas fa-tag mr-1 text-green-400"></i>Status
                    </label>
                    <select name="status" class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-white">
                        <option value="">All</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>

                <!-- Is Active -->
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">
                        <i class="fas fa-toggle-on mr-1 text-yellow-400"></i>Enabled
                    </label>
                    <select name="is_active" class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-white">
                        <option value="">All</option>
                        <option value="1" {{ request('is_active') == '1' ? 'selected' : '' }}>Enabled</option>
                        <option value="0" {{ request('is_active') == '0' ? 'selected' : '' }}>Disabled</option>
                    </select>
                </div>
                
                <!-- Template -->
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">
                        <i class="fas fa-layer-group mr-1 text-indigo-400"></i>Template
                    </label>
                    <select name="template_id" class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-white">
                        <option value="">All Templates</option>
                        @foreach($templates as $template)
                            <option value="{{ $template->id }}" {{ request('template_id') == $template->id ? 'selected' : '' }}>
                                {{ $template->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- User (Admin only) -->
                @if(auth()->user()->hasRole('team-admin') || auth()->user()->hasRole('system-admin'))
                @if(isset($teamUsers) && $teamUsers->count() > 0)
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">
                        <i class="fas fa-user mr-1 text-pink-400"></i>Creator
                    </label>
                    <select name="user_id" class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-white">
                        <option value="">All Creators</option>
                        @foreach($teamUsers as $teamUser)
                            <option value="{{ $teamUser->id }}" {{ request('user_id') == $teamUser->id ? 'selected' : '' }}>
                                {{ $teamUser->name }} ({{ $teamUser->email }})
                            </option>
                        @endforeach
                    </select>
                </div>
                @endif
                @endif
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center justify-between pt-4 border-t border-gray-700">
                <a href="{{ route('products.index') }}" class="text-sm text-gray-400 hover:text-white transition-colors flex items-center">
                    <i class="fas fa-times mr-2"></i>Clear Filters
                </a>
                <div class="flex items-center space-x-3">
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-6 rounded-md transition duration-200 flex items-center">
                        <i class="fas fa-search mr-2"></i>Apply Filters
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Bulk Upload Section -->
    @if(isset($tiktokShops) && $tiktokShops->count() > 0)
    <div class="bg-gradient-to-r from-gray-800 to-gray-700 rounded-xl shadow-lg border border-gray-600 p-6 mb-6">
        <div class="flex items-center justify-between mb-6">
            <div class="flex items-center space-x-3">
                <div class="p-2 bg-green-500/20 rounded-lg">
                    <i class="fas fa-upload text-green-400 text-lg"></i>
                </div>
                <div>
                    <h3 class="text-xl font-semibold text-white">Bulk Upload to TikTok Shop</h3>
                    <p class="text-sm text-gray-400">Select products and shops to upload simultaneously</p>
                </div>
            </div>
            <div class="flex items-center space-x-3">
                <button type="button" onclick="selectAllProducts()" 
                        class="flex items-center px-3 py-2 bg-blue-500/20 hover:bg-blue-500/30 text-blue-400 rounded-lg transition-colors text-sm">
                    <i class="fas fa-check-square mr-2"></i>Select All Products
                </button>
                <button type="button" onclick="deselectAllProducts()" 
                        class="flex items-center px-3 py-2 bg-gray-500/20 hover:bg-gray-500/30 text-gray-400 rounded-lg transition-colors text-sm">
                    <i class="fas fa-square mr-2"></i>Deselect All Products
                </button>
            </div>
        </div>
        
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Shop Selection -->
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <label class="text-sm font-medium text-gray-300">Select TikTok Shop</label>
                    <div class="flex items-center space-x-2">
                        <button type="button" onclick="selectAllShops()" 
                                class="text-xs text-blue-400 hover:text-blue-300">
                            <i class="fas fa-check-square mr-1"></i>All
                        </button>
                        <span class="text-gray-500">|</span>
                        <button type="button" onclick="deselectAllShops()" 
                                class="text-xs text-gray-400 hover:text-gray-300">
                            <i class="fas fa-square mr-1"></i>Deselect
                        </button>
                    </div>
                </div>
                
                <div class="space-y-2 max-h-48 overflow-y-auto">
                    @foreach($tiktokShops as $shop)
                        <label class="flex items-center p-3 bg-gray-600/50 hover:bg-gray-600 rounded-lg border border-gray-500/50 cursor-pointer transition-all duration-200 hover:border-blue-500/50 group">
                            <input type="checkbox" class="shop-checkbox rounded border-gray-500 bg-gray-700 text-blue-600 focus:ring-blue-500 focus:ring-2 mr-3 group-hover:border-blue-400" 
                                   value="{{ $shop->id }}" data-shop-name="{{ $shop->shop_name }}" onchange="updateSelectedCount()">
                            <div class="flex-1 min-w-0">
                                <div class="text-sm font-medium text-white truncate">{{ $shop->shop_name }}</div>
                                <div class="flex items-center space-x-2 mt-1">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $shop->status_badge_class }}">
                                        {{ $shop->status_text }}
                                    </span>
                                    @if($shop->seller_name)
                                        <span class="text-xs text-gray-400">• {{ $shop->seller_name }}</span>
                                    @endif
                                </div>
                            </div>
                        </label>
                    @endforeach
                </div>
            </div>
            
            <!-- Upload Action -->
            <div class="space-y-4">
                <div class="text-sm font-medium text-gray-300">Actions</div>
                
                <div class="bg-gray-600/30 rounded-lg p-4 border border-gray-500/50">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-sm text-gray-300">Selection Status:</span>
                        <div class="flex items-center space-x-4 text-sm">
                            <span class="text-blue-400">
                                <i class="fas fa-box mr-1"></i>
                                <span id="selectedProductCount">0</span> products
                            </span>
                            <span class="text-green-400">
                                <i class="fas fa-store mr-1"></i>
                                <span id="selectedShopCount">0</span> shops
                            </span>
                        </div>
                    </div>
                    
                    <button type="button" onclick="bulkUploadToTikTok()" 
                            class="w-full bg-gradient-to-r from-green-600 to-green-500 hover:from-green-700 hover:to-green-600 text-white font-medium py-3 px-4 rounded-lg transition-all duration-200 disabled:from-gray-600 disabled:to-gray-600 disabled:cursor-not-allowed shadow-lg hover:shadow-green-500/25"
                            id="bulkUploadBtn" disabled>
                        <i class="fas fa-upload mr-2"></i>
                        <span id="uploadButtonText">Bulk Upload</span>
                    </button>
                </div>
                
                <div class="text-xs text-gray-500 bg-gray-700/50 rounded-lg p-3">
                    <i class="fas fa-info-circle mr-1"></i>
                    <strong>Note:</strong> Products will be uploaded to all selected shops. This process may take several minutes depending on the number of products and shops.
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Products List -->
    <div class="bg-gray-800 rounded-lg shadow-sm border border-gray-700 overflow-hidden">
        @if($products->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-700">
                    <thead class="bg-gray-700">
                        <tr>
                            <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">
                                <input type="checkbox" id="selectAllCheckbox" class="rounded border-gray-600 bg-gray-700 text-blue-600 focus:ring-blue-500 focus:ring-2" onchange="toggleAllProducts(this)">
                            </th>
                            <th class="px-2 sm:px-4 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider w-32 sm:w-40">
                                Product
                            </th>
                            <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider hidden sm:table-cell">
                                SKU
                            </th>
                            <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider hidden md:table-cell">
                                Template
                            </th>
                            @if(auth()->user()->hasRole('system-admin') || auth()->user()->hasRole('team-admin'))
                            <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider hidden lg:table-cell">
                                Market
                            </th>
                            @endif
                            <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">
                                Price
                            </th>
                            <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider hidden xl:table-cell">
                                Creator
                            </th>
                            <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider hidden lg:table-cell">
                                Upload History
                            </th>
                            <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-gray-800 divide-y divide-gray-700">
                        @foreach($products as $product)
                        <tr class="hover:bg-gray-700">
                            <td class="px-3 sm:px-6 py-4">
                                <input type="checkbox" class="product-checkbox rounded border-gray-600 bg-gray-700 text-blue-600 focus:ring-blue-500 focus:ring-2" 
                                       value="{{ $product->id }}" onchange="updateSelectedCount()">
                            </td>
                            <td class="px-2 sm:px-4 py-3 w-32 sm:w-40">
                                <div class="flex items-start">
                                    @if($product->primaryImage)
                                        <img class="h-6 w-6 sm:h-8 sm:w-8 rounded object-cover mr-2 flex-shrink-0 mt-0.5" 
                                             src="{{ $product->primaryImage->url }}" 
                                             alt="{{ $product->title }}">
                                    @elseif($product->images->count() > 0)
                                        <img class="h-6 w-6 sm:h-8 sm:w-8 rounded object-cover mr-2 flex-shrink-0 mt-0.5" 
                                             src="{{ $product->images->first()->url }}" 
                                             alt="{{ $product->title }}">
                                    @else
                                        <div class="h-6 w-6 sm:h-8 sm:w-8 rounded bg-gray-600 flex items-center justify-center mr-2 flex-shrink-0 mt-0.5">
                                            <i class="fas fa-image text-gray-400 text-xs"></i>
                                        </div>
                                    @endif
                                    <div class="min-w-0 flex-1">
                                        <div class="text-xs font-medium text-white leading-tight break-words">{{ $product->title }}</div>
                                        @if($product->description)
                                            <div class="text-xs text-gray-400 truncate max-w-[120px] hidden lg:block">{{ $product->description }}</div>
                                        @endif
                                    </div>
                                </div>
                            </td>

                            <td class="px-3 sm:px-6 py-4 hidden sm:table-cell">
                                <div class="text-xs sm:text-sm text-white font-mono truncate">{{ $product->sku }}</div>
                            </td>
                            <td class="px-3 sm:px-6 py-4 hidden md:table-cell">
                                @if($product->productTemplate)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-900 text-blue-200 truncate">
                                        {{ $product->productTemplate->name }}
                                    </span>
                                @else
                                    <span class="text-gray-500 text-xs">-</span>
                                @endif
                            </td>
                            @if(auth()->user()->hasRole('system-admin') || auth()->user()->hasRole('team-admin'))
                            <td class="px-3 sm:px-6 py-4 hidden lg:table-cell">
                                @php
                                    $productHistories = $uploadHistories->get($product->id, collect());
                                    $markets = $productHistories
                                        ->filter(function($history) {
                                            return $history->tiktokShop && $history->tiktokShop->integration;
                                        })
                                        ->map(function($history) {
                                            return $history->tiktokShop->integration->market;
                                        })
                                        ->unique()
                                        ->sort()
                                        ->values();
                                @endphp
                                @if($markets->count() > 0)
                                    <div class="flex flex-wrap gap-1">
                                        @foreach($markets as $market)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium 
                                                @if($market === 'US') bg-blue-900/50 text-blue-300 border border-blue-700/50
                                                @elseif($market === 'UK') bg-purple-900/50 text-purple-300 border border-purple-700/50
                                                @else bg-gray-700 text-gray-300 border border-gray-600
                                                @endif">
                                                @if($market === 'US')
                                                    🇺🇸 US
                                                @elseif($market === 'UK')
                                                    🇬🇧 UK
                                                @else
                                                    {{ $market }}
                                                @endif
                                            </span>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-gray-500 text-xs">-</span>
                                @endif
                            </td>
                            @endif
                            <td class="px-3 sm:px-6 py-4">
                                <div class="text-xs sm:text-sm text-white">
                                    <span class="font-medium">${{ number_format($product->total_price, 2) }}</span>
                                    @if($product->productTemplate)
                                        <div class="text-xs text-gray-400 hidden lg:block">
                                            (SP: ${{ number_format($product->price, 2) }} + Template: ${{ number_format($product->productTemplate->base_price, 2) }})
                                        </div>
                                    @endif
                                </div>
                            </td>
                            <td class="px-3 sm:px-6 py-4 hidden xl:table-cell text-xs sm:text-sm text-white">
                                <div class="truncate">{{ $product->user->name }}</div>
                            </td>
                            <td class="px-3 sm:px-6 py-4 hidden lg:table-cell">
                                @php
                                    $productHistories = $uploadHistories->get($product->id, collect());
                                @endphp
                                @if($productHistories->count() > 0)
                                    <div class="space-y-1">
                                        @foreach($productHistories->take(3) as $history)
                                            <div class="flex items-center space-x-2">
                                                <span class="text-xs text-white truncate max-w-[120px]" title="{{ $history->shop_name }}">
                                                    {{ $history->shop_name }}
                                                </span>
                                                <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-xs font-medium
                                                    @if($history->status === 'success') bg-green-900 text-green-200
                                                    @elseif($history->status === 'failed') bg-red-900 text-red-200
                                                    @else bg-yellow-900 text-yellow-200 @endif">
                                                    @if($history->status === 'success')
                                                        <i class="fas fa-check-circle mr-1"></i>Success
                                                    @elseif($history->status === 'failed')
                                                        <i class="fas fa-times-circle mr-1"></i>Failed
                                                    @else
                                                        <i class="fas fa-clock mr-1"></i>Processing
                                                    @endif
                                                </span>
                                            </div>
                                        @endforeach
                                        @if($productHistories->count() > 3)
                                            <div class="text-xs text-gray-400">
                                                +{{ $productHistories->count() - 3 }} more
                                            </div>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-gray-500 text-xs">Not uploaded</span>
                                @endif
                            </td>
                            <td class="px-3 sm:px-6 py-4 text-sm font-medium">
                                <div class="flex items-center space-x-1">
                                    @can('view-products')
                                    <a href="{{ route('products.show', $product) }}" 
                                       class="group relative text-blue-400 hover:text-blue-300 flex items-center p-1 rounded transition-colors" title="View Details">
                                        <i class="fas fa-eye text-sm"></i>
                                        <span class="absolute left-full ml-2 px-2 py-1 bg-gray-800 text-white text-xs rounded opacity-0 group-hover:opacity-100 transition-opacity duration-200 whitespace-nowrap z-10">
                                            View Details
                                        </span>
                                    </a>
                                    @endcan
                                    
                                    @php
                                        $productHistories = $uploadHistories->get($product->id, collect());
                                    @endphp
                                    @if($productHistories->count() > 0)
                                    <button type="button" 
                                            class="group relative text-purple-400 hover:text-purple-300 flex items-center p-1 rounded transition-colors z-20" 
                                            title="View Upload History"
                                            data-product-id="{{ $product->id }}"
                                            onclick="event.preventDefault(); event.stopPropagation(); showUploadHistory({{ $product->id }});">
                                        <i class="fas fa-history text-sm"></i>
                                        <span class="absolute left-full ml-2 px-2 py-1 bg-gray-800 text-white text-xs rounded opacity-0 group-hover:opacity-100 transition-opacity duration-200 whitespace-nowrap z-30">
                                            Upload History ({{ $productHistories->count() }})
                                        </span>
                                    </button>
                                    @endif
                                    
                                    @can('update-products')
                                    <a href="{{ route('products.edit', $product) }}" 
                                       class="group relative text-indigo-400 hover:text-indigo-300 flex items-center p-1 rounded transition-colors" title="Edit">
                                        <i class="fas fa-edit text-sm"></i>
                                        <span class="absolute left-full ml-2 px-2 py-1 bg-gray-800 text-white text-xs rounded opacity-0 group-hover:opacity-100 transition-opacity duration-200 whitespace-nowrap z-10">
                                            Edit
                                        </span>
                                    </a>
                                    
                                    <form method="POST" action="{{ route('products.toggle-status', $product) }}" class="inline">
                                        @csrf
                                        <button type="submit" class="group relative text-yellow-400 hover:text-yellow-300 flex items-center p-1 rounded transition-colors" title="Toggle Status">
                                            <i class="fas {{ $product->is_active ? 'fa-toggle-on' : 'fa-toggle-off' }} text-sm"></i>
                                            <span class="absolute left-full ml-2 px-2 py-1 bg-gray-800 text-white text-xs rounded opacity-0 group-hover:opacity-100 transition-opacity duration-200 whitespace-nowrap z-10">
                                                {{ $product->is_active ? 'Disable' : 'Enable' }}
                                            </span>
                                        </button>
                                    </form>
                                    
                                 
                                    @endcan
                                    
                                    @can('delete-products')
                                    <form method="POST" action="{{ route('products.destroy', $product) }}" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" 
                                                onclick="return confirm('Are you sure you want to delete this product?')"
                                                class="group relative text-red-400 hover:text-red-300 flex items-center p-1 rounded transition-colors" title="Delete">
                                            <i class="fas fa-trash text-sm"></i>
                                            <span class="absolute left-full ml-2 px-2 py-1 bg-gray-800 text-white text-xs rounded opacity-0 group-hover:opacity-100 transition-opacity duration-200 whitespace-nowrap z-10">
                                                Delete Product
                                            </span>
                                        </button>
                                    </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <div class="bg-gray-800 px-4 py-3 border-t border-gray-700 sm:px-6">
                {{ $products->links() }}
            </div>
        @else
            <div class="text-center py-12">
                <i class="fas fa-box-open text-4xl text-gray-500 mb-4"></i>
                <h3 class="text-lg font-medium text-white mb-2">No Products Yet</h3>
                <p class="text-gray-400 mb-6">Start by creating your first product.</p>
                @can('create-products')
                <a href="{{ route('products.create') }}" 
                   class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg transition duration-200">
                    <i class="fas fa-plus mr-2"></i>Create First Product
                </a>
                @endcan
            </div>
        @endif
    </div>
</div>

<!-- Import Modal -->
<div id="importModal" class="fixed inset-0 bg-gray-900 bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-gray-800 rounded-lg shadow-xl border border-gray-700 w-full max-w-md">
            <div class="flex items-center justify-between p-6 border-b border-gray-700">
                <h3 class="text-lg font-semibold text-white">Import Products from CSV</h3>
                <button onclick="closeImportModal()" class="text-gray-400 hover:text-white">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            
            <form id="importForm" method="POST" action="{{ route('products.import') }}" enctype="multipart/form-data" class="p-6">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">
                            Select CSV File
                        </label>
                        <input type="file" name="file" id="importFile" accept=".csv,.txt" required
                               class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent text-white">
                        <p class="text-xs text-gray-400 mt-1">
                            CSV files only (max 10MB)
                        </p>
                    </div>
                    
                    <div class="bg-blue-900/30 border border-blue-700/50 rounded-lg p-4">
                        <div class="flex items-start">
                            <i class="fas fa-info-circle text-blue-400 mt-0.5 mr-3"></i>
                            <div class="text-sm text-gray-300">
                                <p class="font-medium mb-2">Note:</p>
                                <ul class="list-disc list-inside space-y-1 text-xs">
                                    <li>Required fields: Product Name, SKU, Price</li>
                                    <li>SKU must be unique within the team</li>
                                    <li>Template ID must exist in the team</li>
                                    <li>Status: active or inactive</li>
                                    <li>Enabled: Yes or No</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    
                    <div class="flex items-center justify-between pt-4">
                        <a href="{{ route('products.download-template') }}" 
                           class="text-sm text-purple-400 hover:text-purple-300 flex items-center">
                            <i class="fas fa-file-download mr-2"></i>Download Template
                        </a>
                        <div class="flex space-x-3">
                            <button type="button" onclick="closeImportModal()" 
                                    class="px-4 py-2 bg-gray-700 hover:bg-gray-600 text-white rounded-lg transition duration-200">
                                Cancel
                            </button>
                            <button type="submit" 
                                    class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg transition duration-200">
                                <i class="fas fa-upload mr-2"></i>Import
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Upload History Modal -->
<div id="uploadHistoryModal" class="fixed inset-0 bg-gray-900 bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-gray-800 rounded-lg shadow-xl border border-gray-700 w-full max-w-7xl max-h-[95vh] overflow-hidden">
            <div class="flex items-center justify-between p-6 border-b border-gray-700">
                <h3 class="text-lg font-semibold text-white">TikTok Shop Upload History</h3>
                <button onclick="closeUploadHistoryModal()" class="text-gray-400 hover:text-white">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            
            <div class="p-6 overflow-y-auto max-h-[calc(95vh-120px)]">
                <div id="uploadHistoryContent">
                    <!-- Content will be loaded here -->
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Single product upload
function uploadToTikTok(productId) {
    if (!confirm('Are you sure you want to upload product images to TikTok Shop?')) {
        return;
    }
    
    // Show loading
    const button = event.target.closest('button');
    const originalContent = button.innerHTML;
    button.disabled = true;
    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    
    fetch(`/products/${productId}/upload-images-to-tiktok`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('✅ ' + data.message);
        } else {
            alert('❌ ' + data.message);
        }
    })
    .catch(error => {
        alert('❌ An error occurred: ' + error.message);
    })
    .finally(() => {
        // Restore button
        button.disabled = false;
        button.innerHTML = originalContent;
    });
}

// Bulk upload functions
function selectAllProducts() {
    const checkboxes = document.querySelectorAll('.product-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.checked = true;
    });
    document.getElementById('selectAllCheckbox').checked = true;
    updateSelectedCount();
}

function deselectAllProducts() {
    const checkboxes = document.querySelectorAll('.product-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.checked = false;
    });
    document.getElementById('selectAllCheckbox').checked = false;
    updateSelectedCount();
}

function toggleAllProducts(masterCheckbox) {
    const checkboxes = document.querySelectorAll('.product-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.checked = masterCheckbox.checked;
    });
    updateSelectedCount();
}

function updateSelectedCount() {
    const selectedCheckboxes = document.querySelectorAll('.product-checkbox:checked');
    const selectedShops = document.querySelectorAll('.shop-checkbox:checked');
    const productCount = selectedCheckboxes.length;
    const shopCount = selectedShops.length;
    
    // Update counters
    const selectedCountEl = document.getElementById('selectedCount');
    const selectedProductCountEl = document.getElementById('selectedProductCount');
    const selectedShopCountEl = document.getElementById('selectedShopCount');
    const uploadButtonTextEl = document.getElementById('uploadButtonText');
    
    if (selectedCountEl) selectedCountEl.textContent = productCount;
    if (selectedProductCountEl) selectedProductCountEl.textContent = productCount;
    if (selectedShopCountEl) selectedShopCountEl.textContent = shopCount;
    
    // Update upload button text and state
    const bulkUploadBtn = document.getElementById('bulkUploadBtn');
    if (bulkUploadBtn) {
        if (productCount > 0 && shopCount > 0) {
            bulkUploadBtn.disabled = false;
            if (uploadButtonTextEl) {
                uploadButtonTextEl.textContent = `Upload ${productCount} products to ${shopCount} shops`;
            }
        } else {
            bulkUploadBtn.disabled = true;
            if (uploadButtonTextEl) {
                uploadButtonTextEl.textContent = 'Bulk Upload';
            }
        }
    }
    
    // Update master checkbox state
    const totalCheckboxes = document.querySelectorAll('.product-checkbox');
    const masterCheckbox = document.getElementById('selectAllCheckbox');
    
    if (masterCheckbox) {
        if (productCount === 0) {
            masterCheckbox.checked = false;
            masterCheckbox.indeterminate = false;
        } else if (productCount === totalCheckboxes.length) {
            masterCheckbox.checked = true;
            masterCheckbox.indeterminate = false;
        } else {
            masterCheckbox.checked = false;
            masterCheckbox.indeterminate = true;
        }
    }
}

function selectAllShops() {
    const shopCheckboxes = document.querySelectorAll('.shop-checkbox');
    shopCheckboxes.forEach(checkbox => {
        checkbox.checked = true;
    });
    updateSelectedCount();
}

function deselectAllShops() {
    const shopCheckboxes = document.querySelectorAll('.shop-checkbox');
    shopCheckboxes.forEach(checkbox => {
        checkbox.checked = false;
    });
    updateSelectedCount();
}

function bulkUploadToTikTok() {
    const selectedProducts = Array.from(document.querySelectorAll('.product-checkbox:checked'))
        .map(checkbox => checkbox.value);
    
    const selectedShops = Array.from(document.querySelectorAll('.shop-checkbox:checked'))
        .map(checkbox => ({
            id: checkbox.value,
            name: checkbox.dataset.shopName
        }));
    
    if (selectedProducts.length === 0) {
        alert('Please select at least one product to upload.');
        return;
    }
    
    if (selectedShops.length === 0) {
        alert('Please select at least one TikTok Shop to upload.');
        return;
    }
    
    const shopNames = selectedShops.map(shop => shop.name).join(', ');
    if (!confirm(`Are you sure you want to upload ${selectedProducts.length} products to ${selectedShops.length} shop(s):\n${shopNames}?`)) {
        return;
    }
    
    // Show loading
    const button = document.getElementById('bulkUploadBtn');
    const originalContent = button.innerHTML;
    button.disabled = true;
    button.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Uploading...';
    
    fetch('/products/upload-to-tiktok', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            product_ids: selectedProducts,
            tiktok_shop_ids: selectedShops.map(shop => shop.id)
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(`✅ ${data.message}\n\nDetails:\n- Success: ${data.success_count}\n- Failed: ${data.failure_count}`);
            
            // Reset selections
            deselectAllProducts();
            deselectAllShops();
        } else {
            alert('❌ ' + data.message);
        }
    })
    .catch(error => {
        alert('❌ An error occurred: ' + error.message);
    })
    .finally(() => {
        // Restore button
        button.disabled = false;
        button.innerHTML = originalContent;
    });
}

// Upload History Modal Functions
function showUploadHistory(productId) {
    console.log('Opening upload history for product:', productId);
    
    const modal = document.getElementById('uploadHistoryModal');
    const content = document.getElementById('uploadHistoryContent');
    
    if (!modal || !content) {
        console.error('Modal elements not found');
        return;
    }
    
    // Show loading
    content.innerHTML = '<div class="flex items-center justify-center py-8"><i class="fas fa-spinner fa-spin text-2xl text-blue-400"></i></div>';
    modal.classList.remove('hidden');
    
    // Load upload history data
    const productHistories = @json($uploadHistories);
    const histories = productHistories[productId] || [];
    
    if (histories.length === 0) {
        content.innerHTML = '<div class="text-center py-8 text-gray-400">No upload history found</div>';
        return;
    }
    
    // Build history table
    let tableHTML = `
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-700">
                <thead class="bg-gray-700">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Product</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Shop</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Integration</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Error</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Created At</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-gray-800 divide-y divide-gray-700">
    `;
    
    histories.forEach(history => {
        const statusBadge = getStatusBadge(history.status);
        const errorText = history.error_message || '-';
        const createdAt = new Date(history.created_at).toLocaleString('vi-VN');
        const integrationName = history.integration_name
            || (history.integration && history.integration.name)
            || (history.tiktok_shop && history.tiktok_shop.integration && history.tiktok_shop.integration.name)
            || history.integration_market
            || '-';
        const shopLabel = history.shop_name && integrationName
            ? `${history.shop_name} (${integrationName})`
            : (history.shop_name || integrationName || '-');
        const productImage = history.product?.primary_image?.url
            || history.product?.images?.[0]?.url
            || null;
        const productImageHtml = productImage
            ? `<img class="h-10 w-10 rounded object-cover mr-3 flex-shrink-0" src="${productImage}" alt="${history.product_name || 'Product'}">`
            : `<div class="h-10 w-10 rounded bg-gray-600 flex items-center justify-center mr-3 flex-shrink-0">
                    <i class="fas fa-image text-gray-400 text-sm"></i>
               </div>`;
        
        tableHTML += `
            <tr class="hover:bg-gray-700">
                <td class="px-6 py-4 whitespace-nowrap text-sm text-white">${history.id}</td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center">
                        ${productImageHtml}
                        <div class="text-sm text-white truncate max-w-[200px]" title="${history.product_name}">
                            ${history.product_name}
                        </div>
                    </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-white">${shopLabel}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-white">${integrationName}</td>
                <td class="px-6 py-4 whitespace-nowrap">${statusBadge}</td>
                <td class="px-6 py-4 text-sm text-gray-300 max-w-md">
                    <div class="break-words whitespace-pre-wrap max-h-32 overflow-y-auto" title="${errorText}">
                        ${errorText}
                    </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300">${createdAt}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                    ${history.status === 'failed' ? 
                        `<button onclick="retryUpload(${history.id})" class="text-green-400 hover:text-green-300">
                            <i class="fas fa-redo mr-1"></i>Retry
                        </button>` : 
                        `<span class="text-gray-500">-</span>`
                    }
                </td>
            </tr>
        `;
    });
    
    tableHTML += `
                </tbody>
            </table>
        </div>
    `;
    
    content.innerHTML = tableHTML;
}

function closeUploadHistoryModal() {
    const modal = document.getElementById('uploadHistoryModal');
    if (modal) {
        modal.classList.add('hidden');
    }
}

// Close modal when clicking outside
document.addEventListener('click', function(event) {
    const modal = document.getElementById('uploadHistoryModal');
    if (modal && !modal.classList.contains('hidden')) {
        if (event.target === modal) {
            closeUploadHistoryModal();
        }
    }
});

// Close modal with Escape key
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closeUploadHistoryModal();
    }
});

function getStatusBadge(status) {
    switch(status) {
        case 'success':
            return '<span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-900 text-green-200">✓ Success</span>';
        case 'failed':
            return '<span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-900 text-red-200">✗ Failed</span>';
        case 'pending':
            return '<span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-yellow-900 text-yellow-200">⏳ Processing</span>';
        default:
            return '<span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-700 text-gray-300">? Unknown</span>';
    }
}

function retryUpload(historyId) {
    if (!confirm('Are you sure you want to retry the upload?')) {
        return;
    }
    
    // Show loading
    const button = event.target.closest('button');
    const originalContent = button.innerHTML;
    button.disabled = true;
    button.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i>Processing...';
    
    fetch(`/products/retry-upload/${historyId}`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('✅ ' + data.message);
            // Refresh the page to show updated history
            location.reload();
        } else {
            alert('❌ ' + data.message);
        }
    })
    .catch(error => {
        alert('❌ An error occurred: ' + error.message);
    })
    .finally(() => {
        // Restore button
        button.disabled = false;
        button.innerHTML = originalContent;
    });
}

// Import Modal Functions
function showImportModal() {
    const modal = document.getElementById('importModal');
    if (modal) {
        modal.classList.remove('hidden');
    }
}

function closeImportModal() {
    const modal = document.getElementById('importModal');
    if (modal) {
        modal.classList.add('hidden');
        // Reset form
        const form = document.getElementById('importForm');
        if (form) {
            form.reset();
        }
    }
}

// Close import modal when clicking outside
document.addEventListener('click', function(event) {
    const modal = document.getElementById('importModal');
    if (modal && !modal.classList.contains('hidden')) {
        if (event.target === modal) {
            closeImportModal();
        }
    }
});

// Close import modal with Escape key
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closeImportModal();
    }
});

// Handle import form submission
document.addEventListener('DOMContentLoaded', function() {
    const importForm = document.getElementById('importForm');
    if (importForm) {
        importForm.addEventListener('submit', function(e) {
            const fileInput = document.getElementById('importFile');
            if (!fileInput || !fileInput.files || fileInput.files.length === 0) {
                e.preventDefault();
                alert('Please select a CSV file to import.');
                return false;
            }
            
            const file = fileInput.files[0];
            if (file.size > 10 * 1024 * 1024) { // 10MB
                e.preventDefault();
                alert('File is too large. Please select a file smaller than 10MB.');
                return false;
            }
            
            // Show loading
            const submitBtn = importForm.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Processing...';
            }
        });
    }
});

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    updateSelectedCount();
    
    // Check if filters are active and show filter panel
    @if(request()->hasAny(['search', 'status', 'template_id', 'sku', 'user_id', 'is_active']))
    const filterForm = document.getElementById('filterForm');
    if (filterForm) {
        filterForm.classList.remove('hidden');
        const icon = document.getElementById('filterToggleIcon');
        if (icon) {
            icon.classList.add('rotate-180');
        }
    }
    @endif
});

// Toggle Filters
function toggleFilters() {
    const filterForm = document.getElementById('filterForm');
    const icon = document.getElementById('filterToggleIcon');
    
    if (filterForm && icon) {
        filterForm.classList.toggle('hidden');
        icon.classList.toggle('rotate-180');
    }
}
</script>
@endsection
