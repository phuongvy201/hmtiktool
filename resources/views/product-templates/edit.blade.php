@extends('layouts.app')

@section('title', 'Chỉnh sửa Product Template')

@php
    // Helper function to safely handle array/string fields
    function safeFieldValue($value) {
        if (is_null($value)) {
            return '';
        }
        if (is_array($value)) {
            return implode("\n", $value);
        }
        if (is_string($value)) {
            // Try to decode JSON if it's a JSON string
            $decoded = json_decode($value, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return implode("\n", $decoded);
            }
            return $value;
        }
        return (string) $value;
    }
    
    // Helper function to safely handle any field value
    function safeValue($value) {
        if (is_null($value)) {
            return '';
        }
        if (is_array($value)) {
            return '';
        }
        return (string) $value;
    }
@endphp

@section('content')
<div class="min-h-screen bg-slate-900 text-gray-100">
    <div class="container mx-auto px-4 py-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center mb-6">
                <a href="{{ route('product-templates.index') }}" class="text-blue-400 hover:text-blue-300 mr-4">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                </a>
                <div>
                    <h1 class="text-3xl font-bold text-white mb-2">Chỉnh sửa Product Template</h1>
                    <p class="text-gray-400">Cập nhật thông tin template sản phẩm và quản lý variants</p>
                </div>
            </div>
        </div>

        <!-- Form -->
        <div class="w-full">
            <div class="bg-gray-800 rounded-xl border border-gray-700 p-6">
                <!-- Session Messages -->
                @if(session('success'))
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                        {{ session('success') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('product-templates.update', $productTemplate) }}" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    
                    <!-- Basic Information -->
                    <div class="mb-8">
                        <h3 class="text-lg font-semibold text-white mb-4 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                            </svg>
                            Thông tin cơ bản
                        </h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-300 mb-2">Tên sản phẩm *</label>
                                <input type="text" id="name" name="name" value="{{ old('name', safeValue($productTemplate->name)) }}" required
                                       class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-2 text-white placeholder-gray-400 focus:outline-none focus:border-blue-500 @error('name') border-red-500 @enderror">
                                @error('name')
                                    <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <div>
                                <label for="base_price" class="block text-sm font-medium text-gray-300 mb-2">Giá cơ bản (VNĐ) *</label>
                                <input type="number" id="base_price" name="base_price" step="0.01" value="{{ old('base_price', safeValue($productTemplate->base_price)) }}" required
                                       class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-2 text-white placeholder-gray-400 focus:outline-none focus:border-blue-500 @error('base_price') border-red-500 @enderror">
                                @error('base_price')
                                    <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <div>
                                <label for="list_price" class="block text-sm font-medium text-gray-300 mb-2">Giá niêm yết (VNĐ)</label>
                                <input type="number" id="list_price" name="list_price" step="0.01" value="{{ old('list_price', safeValue($productTemplate->list_price)) }}"
                                       class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-2 text-white placeholder-gray-400 focus:outline-none focus:border-blue-500 @error('list_price') border-red-500 @enderror">
                                @error('list_price')
                                    <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="mt-4">
                            <label for="category_search" class="block text-sm font-medium text-gray-300 mb-2">Tìm kiếm danh mục</label>
                            <div class="relative">
                                <input type="text" id="category_search" name="category_search" 
                                       placeholder="Nhập từ khóa tìm kiếm (ví dụ: T-shirt, Phone, Computer...)"
                                       class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-2 text-white focus:outline-none focus:border-blue-500 @error('category') border-red-500 @enderror">
                                <input type="hidden" id="category" name="category" value="{{ old('category', $productTemplate->category_id) }}">
                                
                                <!-- Search results dropdown -->
                                <div id="category_results" class="absolute z-50 w-full mt-1 bg-gray-800 border border-gray-600 rounded-lg shadow-lg max-h-60 overflow-y-auto hidden">
                                    <div class="p-2 text-gray-400 text-sm border-b border-gray-600">
                                        Kết quả tìm kiếm: <span id="results_count">0</span>
                                    </div>
                                    <div id="results_list" class="p-0">
                                        <!-- Results will be populated here -->
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Selected category display -->
                            <div id="selected_category_display" class="mt-2 p-2 bg-gray-800 rounded-lg border border-gray-600 {{ $productTemplate->category_id ? '' : 'hidden' }}">
                                <div class="flex items-center justify-between">
                                    <span class="text-white text-sm">
                                        <strong>Danh mục đã chọn:</strong> 
                                        <span id="selected_category_name">{{ $categories[$productTemplate->category_id] ?? '' }}</span>
                                    </span>
                                    <button type="button" id="clear_category" class="text-red-400 hover:text-red-300 text-sm">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                        </svg>
                                    </button>
                                </div>
                            </div>

                            <!-- Category Attributes Section -->
                            <div id="category_attributes_section" class="mt-4 {{ $productTemplate->category_id ? '' : 'hidden' }}">
                                <h4 class="text-md font-semibold text-white mb-3 flex items-center">
                                    <svg class="w-4 h-4 mr-2 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    Thuộc tính danh mục
                                </h4>
                                
                                <!-- Loading indicator -->
                                <div id="attributes_loading" class="hidden">
                                    <div class="flex items-center justify-center p-4">
                                        <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-blue-500"></div>
                                        <span class="ml-2 text-gray-300">Đang tải thuộc tính...</span>
                                    </div>
                                </div>

                                <!-- Required Attributes -->
                                <div id="required_attributes_container" class="mb-4">
                                    <h5 class="text-sm font-medium text-red-400 mb-2 flex items-center">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                        </svg>
                                        Thuộc tính bắt buộc
                                    </h5>
                                    <div id="required_attributes_list" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                                        <!-- Required attributes will be loaded here -->
                                    </div>
                                </div>

                                <!-- Optional Attributes -->
                                <div id="optional_attributes_container">
                                    <h5 class="text-sm font-medium text-gray-400 mb-2 flex items-center">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        Thuộc tính tùy chọn
                                    </h5>
                                    <div id="optional_attributes_list" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                                        <!-- Optional attributes will be loaded here -->
                                    </div>
                                </div>

                                <!-- No attributes message -->
                                <div id="no_attributes_message" class="hidden">
                                    <div class="text-center p-4 text-gray-400">
                                        <svg class="w-8 h-8 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                        </svg>
                                        <p>Không có thuộc tính nào cho danh mục này</p>
                                    </div>
                                </div>
                            </div>
                            
                            @error('category')
                                <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div class="mt-4">
                            <label for="description" class="block text-sm font-medium text-gray-300 mb-2">Mô tả</label>
                            <textarea id="description" name="description" rows="5" 
                                      class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-2 text-white placeholder-gray-400 focus:outline-none focus:border-blue-500 @error('description') border-red-500 @enderror whitespace-pre-wrap"
                                      placeholder="Nhập mô tả sản phẩm...&#10;&#10;Ví dụ:&#10;• Chất liệu: 100% cotton&#10;• Kiểu dáng: Regular fit&#10;• Màu sắc: Đen, Trắng, Xanh">{{ old('description', safeFieldValue($productTemplate->description)) }}</textarea>
                            <div class="mt-2 text-xs text-gray-400">
                                <p>💡 <strong>Mẹo:</strong> Sử dụng Enter để xuống dòng, dấu • để tạo danh sách</p>
                            </div>
                            @error('description')
                                <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Dimensions -->
                    <div class="mb-8">
                        <h3 class="text-lg font-semibold text-white mb-4 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path>
                            </svg>
                            Kích thước & Trọng lượng
                        </h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div>
                                <label for="weight" class="block text-sm font-medium text-gray-300 mb-2">Trọng lượng (kg)</label>
                                <input type="number" id="weight" name="weight" step="0.01" value="{{ old('weight', safeValue($productTemplate->weight)) }}"
                                       class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-2 text-white placeholder-gray-400 focus:outline-none focus:border-blue-500 @error('weight') border-red-500 @enderror">
                                @error('weight')
                                    <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <div>
                                <label for="height" class="block text-sm font-medium text-gray-300 mb-2">Chiều cao (cm)</label>
                                <input type="number" id="height" name="height" step="0.01" value="{{ old('height', safeValue($productTemplate->height)) }}"
                                       class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-2 text-white placeholder-gray-400 focus:outline-none focus:border-blue-500 @error('height') border-red-500 @enderror">
                                @error('height')
                                    <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <div>
                                <label for="width" class="block text-sm font-medium text-gray-300 mb-2">Chiều rộng (cm)</label>
                                <input type="number" id="width" name="width" step="0.01" value="{{ old('width', safeValue($productTemplate->width)) }}"
                                       class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-2 text-white placeholder-gray-400 focus:outline-none focus:border-blue-500 @error('width') border-red-500 @enderror">
                                @error('width')
                                    <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <div>
                                <label for="length" class="block text-sm font-medium text-gray-300 mb-2">Chiều dài (cm)</label>
                                <input type="number" id="length" name="length" step="0.01" value="{{ old('length', safeValue($productTemplate->length)) }}"
                                       class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-2 text-white placeholder-gray-400 focus:outline-none focus:border-blue-500 @error('length') border-red-500 @enderror">
                                @error('length')
                                    <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Media -->
                    <div class="mb-8">
                        <h3 class="text-lg font-semibold text-white mb-4 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            Hình ảnh & Video
                        </h3>
                        
                        <div class="grid grid-cols-1 gap-4">
                            <!-- Image Upload Manager Component -->
                            <div class="col-span-full">
                                <x-image-upload-manager 
                                    name="images"
                                    :multiple="true"
                                    :maxFiles="10"
                                    :existingImages="old('images', $productTemplate->images ?? [])"
                                    label="Hình ảnh sản phẩm"
                                />
                                @error('images')
                                    <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <!-- Size Chart and Video -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="size_chart_file" class="block text-sm font-medium text-gray-300 mb-2">Bảng size</label>
                                    <input type="file" id="size_chart_file" name="size_chart_files[]" accept="image/*"
                                           class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-2 text-white placeholder-gray-400 focus:outline-none focus:border-blue-500 @error('size_chart_files') border-red-500 @enderror">
                                    @if($productTemplate->size_chart)
                                        <div class="mt-2 p-2 bg-gray-700 rounded border border-gray-600">
                                            <p class="text-sm text-gray-300 mb-1">Bảng size hiện tại:</p>
                                            <img src="{{ $productTemplate->size_chart }}" alt="Current size chart" class="max-w-full h-32 object-contain rounded">
                                        </div>
                                    @endif
                                    @error('size_chart_files')
                                        <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                                
                                <div>
                                    <label for="product_video_file" class="block text-sm font-medium text-gray-300 mb-2">Video sản phẩm</label>
                                    <input type="file" id="product_video_file" name="product_video_files[]" accept="video/*"
                                           class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-2 text-white placeholder-gray-400 focus:outline-none focus:border-blue-500 @error('product_video_files') border-red-500 @enderror">
                                    @if($productTemplate->product_video)
                                        <div class="mt-2 p-2 bg-gray-700 rounded border border-gray-600">
                                            <p class="text-sm text-gray-300 mb-1">Video hiện tại:</p>
                                            <video controls class="max-w-full h-32 rounded">
                                                <source src="{{ $productTemplate->product_video }}" type="video/mp4">
                                                Trình duyệt của bạn không hỗ trợ video.
                                            </video>
                                        </div>
                                    @endif
                                    @error('product_video_files')
                                        <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Current Options Display -->
                    @if($productTemplate->options->count() > 0)
                    <div class="mb-8">
                        <h3 class="text-lg font-semibold text-white mb-4 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-pink-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                            </svg>
                            Tùy chọn hiện tại
                        </h3>
                        <div class="bg-gray-700 rounded-lg p-4">
                            <p class="text-gray-300 mb-4">Các tùy chọn và variants đã được tạo. Bạn có thể chỉnh sửa thông tin variants bên dưới.</p>
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                @foreach($productTemplate->options as $option)
                                    <div class="bg-gray-600 p-3 rounded border border-gray-500">
                                        <h4 class="font-medium text-white mb-2">{{ $option->name }}</h4>
                                        <div class="flex flex-wrap gap-1">
                                            @foreach($option->values as $value)
                                                <span class="px-2 py-1 bg-blue-600 text-blue-100 text-xs rounded">{{ $value->value }}</span>
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            <div class="mt-4">
                                <p class="text-sm text-gray-400">Tổng cộng: {{ $productTemplate->variants->count() }} variants</p>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Variants Management -->
                    @if($productTemplate->variants->count() > 0)
                    <div class="mb-8">
                        <h3 class="text-lg font-semibold text-white mb-4 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                            </svg>
                            Quản lý Variants
                        </h3>
                        
                        <div class="bg-gray-700 rounded-lg p-4 mb-4">
                            <div class="flex items-center justify-between mb-4">
                                <div>
                                    <p class="text-gray-300 text-sm">
                                        Tổng số variants: <span class="font-bold text-yellow-400">{{ $productTemplate->variants->count() }}</span>
                                    </p>
                                    <p class="text-gray-400 text-xs">Quản lý thông tin giá, số lượng và hình ảnh cho từng variant</p>
                                </div>
                                <button type="button" id="setBulkPriceBtn" class="px-4 py-2 bg-green-600 hover:bg-green-500 text-white rounded-lg text-sm transition-colors duration-200 flex items-center">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                                    </svg>
                                    Set Bulk Price
                                </button>
                            </div>
                        </div>
                        
                        <!-- Smart Bulk Edit Section -->
                        <div id="bulkEditSection" class="bg-gray-700 rounded-lg p-4 mb-4">
                            <h4 class="text-white font-medium mb-4 flex items-center">
                                <svg class="w-4 h-4 mr-2 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                </svg>
                                Chỉnh sửa thông minh
                            </h4>
                            
                            <!-- Quick Selection Tools -->
                            <div class="bg-gray-600 rounded-lg p-3 mb-4">
                                <h5 class="text-white font-medium mb-3">Công cụ chọn nhanh</h5>
                                <div class="flex flex-wrap gap-2">
                                    <button type="button" id="selectAllBtn" class="px-3 py-1 bg-blue-600 hover:bg-blue-500 text-white rounded text-sm transition-colors">
                                        Chọn tất cả
                                    </button>
                                    <button type="button" id="selectNoneBtn" class="px-3 py-1 bg-gray-600 hover:bg-gray-500 text-white rounded text-sm transition-colors">
                                        Bỏ chọn tất cả
                                    </button>
                                    <button type="button" id="selectInverseBtn" class="px-3 py-1 bg-purple-600 hover:bg-purple-500 text-white rounded text-sm transition-colors">
                                        Đảo ngược lựa chọn
                                    </button>
                                    <span class="text-sm text-gray-300 ml-2">
                                        Đã chọn: <span id="selectedCount" class="font-medium text-blue-400">0</span> variants
                                    </span>
                                </div>
                            </div>
                            
                            <!-- Smart Filters -->
                            <div class="bg-gray-600 rounded-lg p-3 mb-4">
                                <h5 class="text-white font-medium mb-3">Bộ lọc thông minh</h5>
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3" id="smartFilters">
                                    <!-- Smart filters will be generated here -->
                                </div>
                                <div class="flex items-center justify-between mt-3 pt-3 border-t border-gray-500">
                                    <div class="flex items-center space-x-3">
                                        <div class="flex items-center space-x-2">
                                            <label class="flex items-center text-sm text-gray-300">
                                                <input type="radio" name="filterLogic" value="AND" checked class="mr-1 rounded border-gray-500 bg-gray-600 text-purple-500 focus:ring-purple-500">
                                                Logic AND
                                            </label>
                                            <label class="flex items-center text-sm text-gray-300">
                                                <input type="radio" name="filterLogic" value="OR" class="mr-1 rounded border-gray-500 bg-gray-600 text-purple-500 focus:ring-purple-500">
                                                Logic OR
                                            </label>
                                        </div>
                                        <div class="flex space-x-2">
                                            <button type="button" id="applyMultiSelection" class="px-3 py-1 bg-purple-600 hover:bg-purple-500 text-white rounded text-sm transition-colors">
                                                Áp dụng lựa chọn
                                            </button>
                                            <button type="button" id="clearMultiSelection" class="px-3 py-1 bg-gray-500 hover:bg-gray-400 text-white rounded text-sm transition-colors">
                                                Xóa lựa chọn
                                            </button>
                                        </div>
                                    </div>
                                    <div class="text-sm text-gray-300">
                                        Đã chọn: <span id="multiSelectionCount" class="font-medium text-purple-400">0</span> giá trị
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Bulk Edit Form -->
                            <div class="bg-gray-600 rounded-lg p-3 mb-4">
                                <h5 class="text-white font-medium mb-3">Chỉnh sửa hàng loạt</h5>
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-3">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-300 mb-1">Giá bán (VNĐ)</label>
                                        <input type="number" id="bulkPrice" step="0.01" 
                                               class="w-full bg-gray-700 border border-gray-500 rounded px-3 py-2 text-white placeholder-gray-400 focus:outline-none focus:border-blue-500"
                                               placeholder="Nhập giá">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-300 mb-1">Giá niêm yết (VNĐ)</label>
                                        <input type="number" id="bulkListPrice" step="0.01" 
                                               class="w-full bg-gray-700 border border-gray-500 rounded px-3 py-2 text-white placeholder-gray-400 focus:outline-none focus:border-blue-500"
                                               placeholder="Nhập giá niêm yết">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-300 mb-1">Số lượng</label>
                                        <input type="number" id="bulkQuantity" min="0" 
                                               class="w-full bg-gray-700 border border-gray-500 rounded px-3 py-2 text-white placeholder-gray-400 focus:outline-none focus:border-blue-500"
                                               placeholder="Nhập số lượng">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-300 mb-1">Hình ảnh bulk</label>
                                        <input type="file" id="bulkImages" name="bulk_images_files[]" accept="image/*" multiple
                                               class="w-full bg-gray-700 border border-gray-500 rounded px-3 py-2 text-white placeholder-gray-400 focus:outline-none focus:border-blue-500">
                                    </div>
                                </div>
                                
                                <div class="flex items-center justify-between mt-3 pt-3 border-t border-gray-500">
                                    <div class="flex space-x-2">
                                        <button type="button" id="applyBulkEdit" class="px-4 py-2 bg-blue-600 hover:bg-blue-500 text-white rounded text-sm transition-colors">
                                            Áp dụng cho variants đã chọn
                                        </button>
                                        <button type="button" id="applyBulkEditAll" class="px-4 py-2 bg-green-600 hover:bg-green-500 text-white rounded text-sm transition-colors">
                                            Áp dụng cho tất cả
                                        </button>
                                    </div>
                                    <button type="button" id="clearBulkForm" class="px-3 py-1 bg-gray-500 hover:bg-gray-400 text-white rounded text-sm transition-colors">
                                        Xóa form
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Bulk Price Modal -->
                        <div id="bulkPriceModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
                            <div class="flex items-center justify-center min-h-screen p-4">
                                <div class="bg-gray-800 rounded-xl border border-gray-700 p-6 w-full max-w-md">
                                    <h4 class="text-lg font-semibold text-white mb-4">Set Bulk Price</h4>
                                    <div class="space-y-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-300 mb-2">Giá (VNĐ)</label>
                                            <input type="number" id="modalBulkPrice" step="0.01" 
                                                   class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-2 text-white placeholder-gray-400 focus:outline-none focus:border-blue-500">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-300 mb-2">Giá niêm yết (VNĐ) - Tùy chọn</label>
                                            <input type="number" id="modalBulkListPrice" step="0.01" 
                                                   class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-2 text-white placeholder-gray-400 focus:outline-none focus:border-blue-500">
                                        </div>
                                    </div>
                                    <div class="flex justify-end space-x-3 mt-6">
                                        <button type="button" id="cancelBulkPrice" 
                                                class="px-4 py-2 border border-gray-600 text-gray-300 rounded-lg hover:bg-gray-700 transition-colors duration-200">
                                            Hủy
                                        </button>
                                        <button type="button" id="applyBulkPrice" 
                                                class="px-4 py-2 bg-blue-600 hover:bg-blue-500 text-white rounded-lg font-medium transition-colors duration-200">
                                            Áp dụng
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-600">
                                <thead class="bg-gray-600">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">
                                            <input type="checkbox" id="selectAll" class="rounded border-gray-500 bg-gray-600 text-blue-500 focus:ring-blue-500">
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Hình ảnh</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">SKU</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Combination</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Giá</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Giá niêm yết</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Số lượng</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-gray-700 divide-y divide-gray-600">
                                    @foreach($productTemplate->variants as $variant)
                                        <tr class="hover:bg-gray-600 transition-colors duration-200">
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <input type="checkbox" class="variant-checkbox rounded border-gray-500 bg-gray-600 text-blue-500 focus:ring-blue-500" value="{{ $variant->id }}" data-variant-index="{{ $loop->index }}">
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                @php
                                                    $variantImage = null;
                                                    if (isset($variant->variant_data['image']) && $variant->variant_data['image']) {
                                                        $variantImage = $variant->variant_data['image'];
                                                    }
                                                @endphp
                                                @if($variantImage)
                                                    <div class="w-12 h-12 rounded-lg overflow-hidden border border-gray-600 cursor-pointer" onclick="openImageModal('{{ $variantImage }}')">
                                                        <img src="{{ $variantImage }}" alt="Variant image" class="w-full h-full object-cover hover:scale-110 transition-transform">
                                                    </div>
                                                @else
                                                    <div class="w-12 h-12 rounded-lg bg-gray-600 flex items-center justify-center">
                                                        <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                                        </svg>
                                                    </div>
                                                @endif
                                                <input type="file" name="variants[{{ $loop->index }}][image_file]" accept="image/*" 
                                                       class="mt-1 w-full text-xs text-gray-300 file:mr-4 file:py-1 file:px-2 file:rounded file:border-0 file:text-xs file:font-medium file:bg-blue-600 file:text-white hover:file:bg-blue-500">
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-white">
                                                {{ $variant->sku }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-white">
                                                @php
                                                    $combination = [];
                                                    foreach($variant->optionValues as $optionValue) {
                                                        $combination[] = $optionValue->option->name . ': ' . $optionValue->value;
                                                    }
                                                @endphp
                                                {{ implode(' / ', $combination) }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <input type="number" step="0.01" class="variant-price w-24 bg-gray-600 border border-gray-500 rounded text-sm text-white focus:outline-none focus:border-blue-500" 
                                                       value="{{ $variant->price }}" data-variant-id="{{ $variant->id }}">
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <input type="number" step="0.01" class="variant-list-price w-24 bg-gray-600 border border-gray-500 rounded text-sm text-white focus:outline-none focus:border-blue-500" 
                                                       value="{{ $variant->list_price }}" data-variant-id="{{ $variant->id }}">
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <input type="number" class="variant-quantity w-20 bg-gray-600 border border-gray-500 rounded text-sm text-white focus:outline-none focus:border-blue-500" 
                                                       value="{{ $variant->stock_quantity }}" data-variant-id="{{ $variant->id }}">
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                <button type="button" class="text-red-400 hover:text-red-300 transition-colors duration-200 remove-variant" data-variant-id="{{ $variant->id }}">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                    </svg>
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @endif

                    <!-- Actions -->
                    <div class="flex items-center justify-end space-x-4 pt-6 border-t border-gray-700">
                        <a href="{{ route('product-templates.index') }}" 
                           class="px-6 py-2 border border-gray-600 text-gray-300 rounded-lg hover:bg-gray-700 transition-colors duration-200">
                            Hủy
                        </a>
                        <button type="submit" 
                                class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition-colors duration-200">
                            Cập nhật Template
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Image Modal -->
<div id="imageModal" class="fixed inset-0 bg-black bg-opacity-75 z-50 hidden flex items-center justify-center p-4">
    <div class="relative max-w-4xl max-h-full">
        <button onclick="closeImageModal()" class="absolute -top-10 right-0 text-white hover:text-gray-300 transition-colors">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>
        <img id="modalImage" src="" alt="Full size image" class="max-w-full max-h-full rounded-lg">
    </div>
</div>
@endsection

@push('scripts')
<script>
    let allVariants = [];

    // Initialize on page load
    document.addEventListener('DOMContentLoaded', function() {
        // Generate variants data from existing variants
        generateVariantsData();
        
        // Setup smart bulk edit functionality
        setupSmartBulkEdit();
        
        // Setup existing functionality
        setupExistingFunctionality();
        
        // Setup form submit handler
        setupFormSubmitHandler();
    });

    // Generate variants data from existing variants
    function generateVariantsData() {
        const variantRows = document.querySelectorAll('tbody tr');
        allVariants = [];
        
        variantRows.forEach((row, index) => {
            const combinationCell = row.querySelector('td:nth-child(4)'); // Changed from 3 to 4 due to added image column
            if (combinationCell) {
                const combinationText = combinationCell.textContent.trim();
                
                // Parse combination text to extract attributes
                const attributes = combinationText.split(' / ').map(attr => {
                    const [name, value] = attr.split(': ');
                    return { name: name.trim(), value: value.trim() };
                });
                
                allVariants.push(attributes);
            } else {
                console.error(`Combination cell not found for row ${index}`);
            }
        });
        
        console.log('Generated variants data:', allVariants);
    }

    // Setup smart bulk edit functionality
    function setupSmartBulkEdit() {
        console.log('Setting up smart bulk edit...');
        
        // Generate smart filters
        generateSmartFilters();
        
        // Setup quick selection buttons
        setupQuickSelection();
        
        // Setup bulk edit form
        setupBulkEditForm();
        
        console.log('Smart bulk edit setup completed');
    }
    
    // Generate smart filters
    function generateSmartFilters() {
        console.log('Generating smart filters...');
        const smartFilters = document.getElementById('smartFilters');
        if (!smartFilters) {
            console.error('Smart filters container not found');
            return;
        }
        
        smartFilters.innerHTML = '';
        
        // Get all unique attributes from variants
        const attributeMap = {};
        allVariants.forEach(variant => {
            variant.forEach(attr => {
                if (!attributeMap[attr.name]) {
                    attributeMap[attr.name] = new Set();
                }
                attributeMap[attr.name].add(attr.value);
            });
        });
        
        console.log('Attribute map:', attributeMap);
        
        // Create filter sections for each attribute
        Object.entries(attributeMap).forEach(([attributeName, values]) => {
            const filterDiv = document.createElement('div');
            filterDiv.className = 'bg-gray-700 rounded p-3';
            filterDiv.innerHTML = `
                <h6 class="text-white font-medium mb-2 text-sm">${attributeName}</h6>
                <div class="space-y-1">
                    ${Array.from(values).map(value => `
                        <label class="flex items-center text-sm text-gray-300 cursor-pointer hover:bg-gray-600 rounded px-2 py-1 transition-colors">
                            <input type="checkbox" class="multi-filter-checkbox mr-2 rounded border-gray-500 bg-gray-600 text-purple-500 focus:ring-purple-500" 
                                   data-attribute="${attributeName}" data-value="${value}">
                            ${value} <span class="text-gray-400 ml-1">(${countVariantsWithAttribute(attributeName, value)})</span>
                        </label>
                    `).join('')}
                </div>
            `;
            smartFilters.appendChild(filterDiv);
        });
        
        console.log('Smart filters generated successfully');
        
        // Add change handlers for multi-filter checkboxes
        document.addEventListener('change', function(e) {
            if (e.target.classList.contains('multi-filter-checkbox')) {
                updateMultiSelectionCount();
            }
        });
    }
    
    // Count variants with specific attribute value
    function countVariantsWithAttribute(attributeName, attributeValue) {
        let count = 0;
        allVariants.forEach(variant => {
            const hasAttribute = variant.some(item => item.name === attributeName && item.value === attributeValue);
            if (hasAttribute) count++;
        });
        return count;
    }
    
    // Get selected multi-filter values
    function getSelectedMultiFilterValues() {
        const checkboxes = document.querySelectorAll('.multi-filter-checkbox:checked');
        return Array.from(checkboxes).map(checkbox => ({
            attribute: checkbox.dataset.attribute,
            value: checkbox.dataset.value
        }));
    }
    
    // Update multi-selection count
    function updateMultiSelectionCount() {
        const selectedCount = document.querySelectorAll('.multi-filter-checkbox:checked').length;
        const countElement = document.getElementById('multiSelectionCount');
        if (countElement) {
            countElement.textContent = selectedCount;
        }
    }
    
    // Select variants by multiple attribute values
    function selectVariantsByMultipleAttributeValues() {
        const selectedValues = getSelectedMultiFilterValues();
        
        if (selectedValues.length === 0) {
            showNotification('Vui lòng chọn ít nhất một giá trị thuộc tính!', 'error');
            return;
        }
        
        // Get selected logic (AND or OR)
        const selectedLogic = document.querySelector('input[name="filterLogic"]:checked').value;
        
        const checkboxes = document.querySelectorAll('.variant-checkbox');
        
        checkboxes.forEach((checkbox, index) => {
            const variant = allVariants[index];
            
            // Group selected values by attribute
            const selectedByAttribute = {};
            selectedValues.forEach(selection => {
                if (!selectedByAttribute[selection.attribute]) {
                    selectedByAttribute[selection.attribute] = [];
                }
                selectedByAttribute[selection.attribute].push(selection.value);
            });
            
            let shouldSelect = false;
            
            if (selectedLogic === 'AND') {
                // Logic AND: Variant must match ALL selected attributes
                shouldSelect = true;
                
                for (const [attributeName, requiredValues] of Object.entries(selectedByAttribute)) {
                    // Find the variant's value for this attribute
                    const variantAttribute = variant.find(item => item.name === attributeName);
                    
                    if (!variantAttribute) {
                        // If this attribute is required but variant doesn't have it
                        shouldSelect = false;
                        break;
                    }
                    
                    // Check if variant's value is in the required values for this attribute
                    if (!requiredValues.includes(variantAttribute.value)) {
                        shouldSelect = false;
                        break;
                    }
                }
            } else {
                // Logic OR: Variant must match ANY of the selected attribute values
                shouldSelect = selectedValues.some(selection => 
                    variant.some(item => item.name === selection.attribute && item.value === selection.value)
                );
            }
            
            checkbox.checked = shouldSelect;
        });
        
        updateSelectedCount();
        
        const selectedText = selectedValues.map(s => `${s.attribute}: ${s.value}`).join(', ');
        showNotification(`Đã chọn variants có: ${selectedText} (Logic: ${selectedLogic})`, 'success');
    }
    
    // Clear multi-selection
    function clearMultiSelection() {
        const checkboxes = document.querySelectorAll('.multi-filter-checkbox');
        checkboxes.forEach(checkbox => {
            checkbox.checked = false;
        });
        
        updateMultiSelectionCount();
        showNotification('Đã xóa lựa chọn thuộc tính', 'info');
    }
    
    // Setup quick selection buttons
    function setupQuickSelection() {
        const selectAllBtn = document.getElementById('selectAllBtn');
        const selectNoneBtn = document.getElementById('selectNoneBtn');
        const selectInverseBtn = document.getElementById('selectInverseBtn');
        
        if (selectAllBtn) {
            selectAllBtn.addEventListener('click', function() {
                const checkboxes = document.querySelectorAll('.variant-checkbox');
                checkboxes.forEach(checkbox => checkbox.checked = true);
                updateSelectedCount();
                showNotification('Đã chọn tất cả variants', 'success');
            });
        }
        
        if (selectNoneBtn) {
            selectNoneBtn.addEventListener('click', function() {
                const checkboxes = document.querySelectorAll('.variant-checkbox');
                checkboxes.forEach(checkbox => checkbox.checked = false);
                updateSelectedCount();
                showNotification('Đã bỏ chọn tất cả variants', 'info');
            });
        }
        
        if (selectInverseBtn) {
            selectInverseBtn.addEventListener('click', function() {
                const checkboxes = document.querySelectorAll('.variant-checkbox');
                checkboxes.forEach(checkbox => checkbox.checked = !checkbox.checked);
                updateSelectedCount();
                showNotification('Đã đảo ngược lựa chọn', 'info');
            });
        }
        
        // Update count when individual checkboxes change
        document.addEventListener('change', function(e) {
            if (e.target.classList.contains('variant-checkbox')) {
                updateSelectedCount();
            }
        });
    }
    
    // Setup bulk edit form
    function setupBulkEditForm() {
        const applyBulkEditBtn = document.getElementById('applyBulkEdit');
        const applyBulkEditAllBtn = document.getElementById('applyBulkEditAll');
        const clearBulkFormBtn = document.getElementById('clearBulkForm');
        const applyMultiSelectionBtn = document.getElementById('applyMultiSelection');
        const clearMultiSelectionBtn = document.getElementById('clearMultiSelection');
        
        if (applyBulkEditBtn) {
            applyBulkEditBtn.addEventListener('click', function() {
                const selectedVariants = getSelectedVariants();
                if (selectedVariants.length === 0) {
                    showNotification('Vui lòng chọn ít nhất một variant!', 'error');
                    return;
                }
                applyBulkEdit(selectedVariants);
            });
        }
        
        if (applyBulkEditAllBtn) {
            applyBulkEditAllBtn.addEventListener('click', function() {
                const allRows = document.querySelectorAll('tbody tr');
                const variantIndices = Array.from(allRows).map((_, index) => index);
                applyBulkEdit(variantIndices);
            });
        }
        
        if (clearBulkFormBtn) {
            clearBulkFormBtn.addEventListener('click', function() {
                document.getElementById('bulkPrice').value = '';
                document.getElementById('bulkListPrice').value = '';
                document.getElementById('bulkQuantity').value = '';
                document.getElementById('bulkImages').value = '';
                showNotification('Đã xóa form', 'info');
            });
        }
        
        // Multi-selection buttons
        if (applyMultiSelectionBtn) {
            applyMultiSelectionBtn.addEventListener('click', function() {
                selectVariantsByMultipleAttributeValues();
            });
        }
        
        if (clearMultiSelectionBtn) {
            clearMultiSelectionBtn.addEventListener('click', function() {
                clearMultiSelection();
            });
        }
    }
    
    // Get selected variant indices
    function getSelectedVariants() {
        const checkboxes = document.querySelectorAll('.variant-checkbox:checked');
        return Array.from(checkboxes).map((checkbox, index) => {
            // Get the row index of the checkbox
            const row = checkbox.closest('tr');
            const tbody = row.closest('tbody');
            const rows = Array.from(tbody.querySelectorAll('tr'));
            return rows.indexOf(row);
        });
    }
    
    // Update selected count
    function updateSelectedCount() {
        const selectedCount = document.querySelectorAll('.variant-checkbox:checked').length;
        const countElement = document.getElementById('selectedCount');
        if (countElement) {
            countElement.textContent = selectedCount;
        }
    }
    
    // Apply bulk edit to variants
    function applyBulkEdit(variantIndices) {
        const bulkPrice = document.getElementById('bulkPrice').value;
        const bulkListPrice = document.getElementById('bulkListPrice').value;
        const bulkQuantity = document.getElementById('bulkQuantity').value;
        const bulkImages = document.getElementById('bulkImages').files; // Sửa từ bulkImage thành bulkImages
        
        if (!bulkPrice && !bulkListPrice && !bulkQuantity && bulkImages.length === 0) {
            showNotification('Vui lòng nhập ít nhất một thông tin để áp dụng!', 'error');
            return;
        }
        
        let appliedCount = 0;
        
        // Get all variant rows
        const variantRows = document.querySelectorAll('tbody tr');
        
        variantIndices.forEach(index => {
            if (index < variantRows.length) {
                const row = variantRows[index];
                
                if (bulkPrice) {
                    const priceInput = row.querySelector('.variant-price');
                    if (priceInput) {
                        priceInput.value = bulkPrice;
                        appliedCount++;
                    }
                }
                
                if (bulkListPrice) {
                    const listPriceInput = row.querySelector('.variant-list-price');
                    if (listPriceInput) {
                        listPriceInput.value = bulkListPrice;
                        appliedCount++;
                    }
                }
                
                if (bulkQuantity) {
                    const quantityInput = row.querySelector('.variant-quantity');
                    if (quantityInput) {
                        quantityInput.value = bulkQuantity;
                        appliedCount++;
                    }
                }
                
                // Handle bulk images for variants
                if (bulkImages.length > 0) {
                    const firstFile = bulkImages[0]; // Take the first selected file
                    
                    // Apply the same file to the current variant
                    const imageInput = row.querySelector('input[name*="[image_file]"]');
                    if (imageInput) {
                        try {
                            // Method 1: Try DataTransfer (modern browsers)
                            if (typeof DataTransfer !== 'undefined') {
                                const dt = new DataTransfer();
                                dt.items.add(firstFile);
                                imageInput.files = dt.files;
                                console.log(`Set image file for variant ${index} using DataTransfer:`, firstFile.name);
                                appliedCount++;
                            } else {
                                // Method 2: Fallback for older browsers
                                console.warn('DataTransfer not supported, using fallback method');
                                // Mark for manual handling in form submission
                                imageInput.setAttribute('data-bulk-image', 'true');
                                imageInput.setAttribute('data-bulk-image-name', firstFile.name);
                                appliedCount++;
                            }
                            
                            // Mark this input as having a bulk image
                            imageInput.setAttribute('data-bulk-image', 'true');
                            imageInput.setAttribute('data-bulk-image-name', firstFile.name);
                            imageInput.setAttribute('data-bulk-image-size', firstFile.size);
                            imageInput.setAttribute('data-bulk-image-type', firstFile.type);
                            
                        } catch (error) {
                            console.error(`Error setting image file for variant ${index}:`, error);
                            // Mark for manual handling
                            imageInput.setAttribute('data-bulk-image', 'true');
                            imageInput.setAttribute('data-bulk-image-name', firstFile.name);
                            appliedCount++;
                        }
                    } else {
                        console.error(`Image file input not found for variant ${index}`);
                    }
                }
            }
        });
        
        showNotification(`Đã áp dụng thông tin cho ${appliedCount} trường dữ liệu!`, 'success');
        
                                // Mark variants as updated for form submission
                        variantIndices.forEach(index => {
                            const row = variantRows[index];
                            const priceInput = row.querySelector('.variant-price');
                            const listPriceInput = row.querySelector('.variant-list-price');
                            const quantityInput = row.querySelector('.variant-quantity');
                            const imageInput = row.querySelector('input[name*="[image_file]"]');
                            
                            // Add data attribute to mark as updated
                            if (priceInput) priceInput.setAttribute('data-bulk-updated', 'true');
                            if (listPriceInput) listPriceInput.setAttribute('data-bulk-updated', 'true');
                            if (quantityInput) quantityInput.setAttribute('data-bulk-updated', 'true');
                            if (imageInput && (imageInput.files.length > 0 || imageInput.hasAttribute('data-bulk-image'))) {
                                imageInput.setAttribute('data-bulk-updated', 'true');
                            }
                        });
                        
                        // Store bulk image file reference for form submission
                        if (bulkImages.length > 0) {
                            const bulkImageFile = bulkImages[0];
                            
                            // Create a hidden input to store the bulk image file
                            let bulkImageStorage = document.getElementById('bulkImageStorage');
                            if (!bulkImageStorage) {
                                bulkImageStorage = document.createElement('input');
                                bulkImageStorage.type = 'file';
                                bulkImageStorage.id = 'bulkImageStorage';
                                bulkImageStorage.name = 'bulk_image_file';
                                bulkImageStorage.accept = 'image/*';
                                bulkImageStorage.style.display = 'none';
                                
                                // Try to set the file
                                try {
                                    if (typeof DataTransfer !== 'undefined') {
                                        const dt = new DataTransfer();
                                        dt.items.add(bulkImageFile);
                                        bulkImageStorage.files = dt.files;
                                    }
                                } catch (error) {
                                    console.error('Error setting bulk image storage:', error);
                                }
                                
                                document.querySelector('form').appendChild(bulkImageStorage);
                                console.log('Created bulk image storage input');
                            }
                            
                            // Create a hidden input to store selected variant indices for bulk image
                            let bulkImageVariants = document.getElementById('bulkImageVariants');
                            if (!bulkImageVariants) {
                                bulkImageVariants = document.createElement('input');
                                bulkImageVariants.type = 'hidden';
                                bulkImageVariants.id = 'bulkImageVariants';
                                bulkImageVariants.name = 'bulk_image_variants';
                                bulkImageVariants.value = JSON.stringify(variantIndices);
                                document.querySelector('form').appendChild(bulkImageVariants);
                                console.log('Created bulk image variants input with indices:', variantIndices);
                            }
                        }
        
        // Clear bulk edit inputs
        document.getElementById('bulkPrice').value = '';
        document.getElementById('bulkListPrice').value = '';
        document.getElementById('bulkQuantity').value = '';
        document.getElementById('bulkImages').value = '';
    }

    // Setup existing functionality
    function setupExistingFunctionality() {
        // Select all variants
        const selectAllCheckbox = document.getElementById('selectAll');
        if (selectAllCheckbox) {
            selectAllCheckbox.addEventListener('change', function() {
                const checkboxes = document.querySelectorAll('.variant-checkbox');
                checkboxes.forEach(checkbox => {
                    checkbox.checked = this.checked;
                });
                updateSelectedCount();
            });
        }

        // Bulk price functionality
        const setBulkPriceBtn = document.getElementById('setBulkPriceBtn');
        if (setBulkPriceBtn) {
            setBulkPriceBtn.addEventListener('click', function() {
                const selectedVariants = document.querySelectorAll('.variant-checkbox:checked');
                if (selectedVariants.length === 0) {
                    showNotification('Vui lòng chọn ít nhất một variant.', 'error');
                    return;
                }
                document.getElementById('bulkPriceModal').classList.remove('hidden');
            });
        }

        const cancelBulkPrice = document.getElementById('cancelBulkPrice');
        if (cancelBulkPrice) {
            cancelBulkPrice.addEventListener('click', function() {
                document.getElementById('bulkPriceModal').classList.add('hidden');
            });
        }

        const applyBulkPrice = document.getElementById('applyBulkPrice');
        if (applyBulkPrice) {
            applyBulkPrice.addEventListener('click', function() {
                const price = document.getElementById('modalBulkPrice').value;
                const listPrice = document.getElementById('modalBulkListPrice').value;
                const selectedVariants = document.querySelectorAll('.variant-checkbox:checked');
                
                if (!price) {
                    showNotification('Vui lòng nhập giá.', 'error');
                    return;
                }

                const variantIds = Array.from(selectedVariants).map(cb => cb.value);
                
                fetch(`{{ route('product-templates.set-bulk-price', $productTemplate) }}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        variant_ids: variantIds,
                        price: parseFloat(price),
                        list_price: listPrice ? parseFloat(listPrice) : null
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        showNotification('Có lỗi xảy ra khi cập nhật giá.', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showNotification('Có lỗi xảy ra khi cập nhật giá.', 'error');
                });
            });
        }

        // Update variant on change
        document.querySelectorAll('.variant-price').forEach(input => {
            input.addEventListener('change', function() {
                updateVariant(this.dataset.variantId, 'price', parseFloat(this.value));
            });
        });

        document.querySelectorAll('.variant-list-price').forEach(input => {
            input.addEventListener('change', function() {
                updateVariant(this.dataset.variantId, 'list_price', parseFloat(this.value));
            });
        });

        document.querySelectorAll('.variant-quantity').forEach(input => {
            input.addEventListener('change', function() {
                updateVariant(this.dataset.variantId, 'quantity', parseInt(this.value));
            });
        });

        // Remove variant
        document.querySelectorAll('.remove-variant').forEach(button => {
            button.addEventListener('click', function() {
                if (confirm('Bạn có chắc chắn muốn xóa variant này?')) {
                    const variantId = this.dataset.variantId;
                    
                    fetch(`{{ route('product-templates.delete-variant', $productTemplate) }}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({ variant_id: variantId })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            this.closest('tr').remove();
                            showNotification('Đã xóa variant thành công!', 'success');
                        } else {
                            showNotification('Có lỗi xảy ra khi xóa variant.', 'error');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showNotification('Có lỗi xảy ra khi xóa variant.', 'error');
                    });
                }
            });
        });
    }

    // Update variant on change
    function updateVariant(variantId, field, value) {
        const variants = [{
            id: variantId,
            [field]: value
        }];

        fetch(`{{ route('product-templates.update-variants', $productTemplate) }}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ variants: variants })
        })
        .then(response => response.json())
        .then(data => {
            if (!data.success) {
                showNotification('Có lỗi xảy ra khi cập nhật variant.', 'error');
            } else {
                showNotification('Cập nhật variant thành công!', 'success');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Có lỗi xảy ra khi cập nhật variant.', 'error');
        });
    }

    // Helper function to create file input with bulk image
    function createFileInputWithBulkImage(variantIndex, bulkImageFile) {
        // Create a new file input
        const newFileInput = document.createElement('input');
        newFileInput.type = 'file';
        newFileInput.name = `variants[${variantIndex}][image_file]`;
        newFileInput.accept = 'image/*';
        newFileInput.style.display = 'none';
        
        // Try to set the file using DataTransfer
        try {
            if (typeof DataTransfer !== 'undefined') {
                const dt = new DataTransfer();
                dt.items.add(bulkImageFile);
                newFileInput.files = dt.files;
                console.log(`Created file input for variant ${variantIndex} with bulk image:`, bulkImageFile.name);
                return newFileInput;
            } else {
                console.warn('DataTransfer not supported, using fallback method');
            }
        } catch (error) {
            console.error('Error creating file input with DataTransfer:', error);
        }
        
        // Fallback: Create a hidden input with file data
        const fallbackInput = document.createElement('input');
        fallbackInput.type = 'hidden';
        fallbackInput.name = `variants[${variantIndex}][bulk_image_data]`;
        fallbackInput.value = JSON.stringify({
            name: bulkImageFile.name,
            size: bulkImageFile.size,
            type: bulkImageFile.type,
            lastModified: bulkImageFile.lastModified
        });
        fallbackInput.setAttribute('data-bulk-image-fallback', 'true');
        
        console.log(`Created fallback input for variant ${variantIndex} with bulk image data`);
        return fallbackInput;
    }

    // Setup form submit handler
    function setupFormSubmitHandler() {
        const form = document.querySelector('form');
        if (form) {
            form.addEventListener('submit', function(e) {
                console.log('Form submission started...');
                
                // Handle bulk images that couldn't be set via DataTransfer
                const bulkImageInput = document.getElementById('bulkImages');
                const bulkImageFile = bulkImageInput && bulkImageInput.files.length > 0 ? bulkImageInput.files[0] : null;
                
                if (bulkImageFile) {
                    console.log('Processing bulk image file:', bulkImageFile.name);
                    
                    // Find all variant image inputs that were marked for bulk update
                    const bulkImageInputs = document.querySelectorAll('input[data-bulk-image="true"]');
                    console.log('Found bulk image inputs:', bulkImageInputs.length);
                    
                    bulkImageInputs.forEach((imageInput, index) => {
                        try {
                            // Get variant index
                            const variantIndex = imageInput.closest('tr').querySelector('.variant-checkbox').dataset.variantIndex;
                            
                            // Check if the current input has files
                            if (!imageInput.files || imageInput.files.length === 0) {
                                // Create a new file input with the bulk image
                                const newFileInput = createFileInputWithBulkImage(variantIndex, bulkImageFile);
                                
                                // Replace the old input with the new one
                                imageInput.parentNode.replaceChild(newFileInput, imageInput);
                                
                                console.log(`Replaced file input for variant ${variantIndex} with bulk image`);
                            } else {
                                // Input already has files, just ensure name is correct
                                imageInput.name = `variants[${variantIndex}][image_file]`;
                                console.log(`File input for variant ${variantIndex} already has files`);
                            }
                            
                        } catch (error) {
                            console.error('Error handling bulk image for input', index, ':', error);
                        }
                    });
                }
                
                // Also check for bulk image storage input
                const bulkImageStorage = document.getElementById('bulkImageStorage');
                if (bulkImageStorage && bulkImageStorage.files && bulkImageStorage.files.length > 0) {
                    console.log('Found bulk image storage with file:', bulkImageStorage.files[0].name);
                }
                
                // Collect all variant data for form submission
                const variantRows = document.querySelectorAll('tbody tr');
                const variantsData = [];
                
                variantRows.forEach((row, index) => {
                    const priceInput = row.querySelector('.variant-price');
                    const listPriceInput = row.querySelector('.variant-list-price');
                    const quantityInput = row.querySelector('.variant-quantity');
                    const imageInput = row.querySelector('input[name*="[image_file]"]');
                    
                    if (priceInput || listPriceInput || quantityInput || (imageInput && (imageInput.files.length > 0 || imageInput.hasAttribute('data-bulk-image')))) {
                        const variantData = {
                            index: index
                        };
                        
                        if (priceInput && priceInput.value) {
                            variantData.price = priceInput.value;
                        }
                        
                        if (listPriceInput && listPriceInput.value) {
                            variantData.list_price = listPriceInput.value;
                        }
                        
                        if (quantityInput && quantityInput.value) {
                            variantData.quantity = quantityInput.value;
                        }
                        
                        if (imageInput && (imageInput.files.length > 0 || imageInput.hasAttribute('data-bulk-image'))) {
                            // Ensure the input name is correct
                            imageInput.name = `variants[${index}][image_file]`;
                            variantData.hasImage = true;
                        }
                        
                        variantsData.push(variantData);
                    }
                });
                
                console.log('Form submission - variants data:', variantsData);
                console.log('Form will submit with enctype="multipart/form-data"');
            });
        }
    }

    // Show notification
    function showNotification(message, type = 'info') {
        // Create notification element
        const notification = document.createElement('div');
        notification.className = `fixed top-4 right-4 z-50 px-6 py-3 rounded-lg text-white font-medium transition-all duration-300 transform translate-x-full`;
        
        if (type === 'success') {
            notification.className += ' bg-green-600';
        } else if (type === 'error') {
            notification.className += ' bg-red-600';
        } else {
            notification.className += ' bg-blue-600';
        }
        
        notification.textContent = message;
        document.body.appendChild(notification);
        
        // Animate in
        setTimeout(() => {
            notification.classList.remove('translate-x-full');
        }, 100);
        
        // Remove after 3 seconds
        setTimeout(() => {
            notification.classList.add('translate-x-full');
            setTimeout(() => {
                if (document.body.contains(notification)) {
                    document.body.removeChild(notification);
                }
            }, 300);
        }, 3000);
    }

    // Image Modal functions
    function openImageModal(imageSrc) {
        document.getElementById('modalImage').src = imageSrc;
        document.getElementById('imageModal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function closeImageModal() {
        document.getElementById('imageModal').classList.add('hidden');
        document.body.style.overflow = 'auto';
    }

    // Close modal when clicking outside the image
    document.getElementById('imageModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeImageModal();
        }
    });

    // Close modal with Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeImageModal();
        }
    });

    // Category search and attributes functionality
    const categories = @json($categories);
    let searchTimeout;
    
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('category_search');
        const resultsDiv = document.getElementById('category_results');
        const resultsList = document.getElementById('results_list');
        const resultsCount = document.getElementById('results_count');
        const categoryInput = document.getElementById('category');
        const selectedDisplay = document.getElementById('selected_category_display');
        const selectedName = document.getElementById('selected_category_name');
        const clearBtn = document.getElementById('clear_category');
        
        // Search functionality
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                const query = this.value.trim().toLowerCase();
                
                if (query.length < 2) {
                    resultsDiv.classList.add('hidden');
                    return;
                }
                
                // Filter categories
                const filtered = Object.entries(categories).filter(([id, name]) => 
                    name.toLowerCase().includes(query)
                ).slice(0, 10); // Limit to 10 results
                
                // Display results
                if (filtered.length > 0) {
                    resultsList.innerHTML = filtered.map(([id, name]) => `
                        <div class="category-result p-2 hover:bg-gray-700 cursor-pointer border-b border-gray-600 last:border-b-0" 
                             data-id="${id}" data-name="${name}">
                            <div class="text-white text-sm">${name}</div>
                            <div class="text-gray-400 text-xs">ID: ${id}</div>
                        </div>
                    `).join('');
                    
                    resultsCount.textContent = filtered.length;
                    resultsDiv.classList.remove('hidden');
                } else {
                    resultsList.innerHTML = `
                        <div class="p-3 text-gray-400 text-sm text-center">
                            Không tìm thấy danh mục nào phù hợp
                        </div>
                    `;
                    resultsCount.textContent = '0';
                    resultsDiv.classList.remove('hidden');
                }
            }, 300); // Debounce 300ms
        });
        
        // Handle result selection
        resultsList.addEventListener('click', function(e) {
            const result = e.target.closest('.category-result');
            if (result) {
                const id = result.dataset.id;
                const name = result.dataset.name;
                
                // Set values
                categoryInput.value = id;
                selectedName.textContent = name;
                
                // Show selected display
                selectedDisplay.classList.remove('hidden');
                
                // Hide results
                resultsDiv.classList.add('hidden');
                
                // Clear search input
                searchInput.value = '';
                
                // Load category attributes
                loadCategoryAttributes(id);
            }
        });
        
        // Clear selection
        clearBtn.addEventListener('click', function() {
            categoryInput.value = '';
            selectedName.textContent = '';
            selectedDisplay.classList.add('hidden');
            
            // Hide attributes section
            document.getElementById('category_attributes_section').classList.add('hidden');
        });
        
        // Hide results when clicking outside
        document.addEventListener('click', function(e) {
            if (!e.target.closest('#category_search') && !e.target.closest('#category_results')) {
                resultsDiv.classList.add('hidden');
            }
        });
        
        // Handle keyboard navigation
        searchInput.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                resultsDiv.classList.add('hidden');
            }
        });

        // Load category attributes function
        function loadCategoryAttributes(categoryId) {
            const attributesSection = document.getElementById('category_attributes_section');
            const loadingDiv = document.getElementById('attributes_loading');
            const requiredList = document.getElementById('required_attributes_list');
            const optionalList = document.getElementById('optional_attributes_list');
            const noAttributesDiv = document.getElementById('no_attributes_message');

            // Show loading and attributes section
            attributesSection.classList.remove('hidden');
            loadingDiv.classList.remove('hidden');
            requiredList.innerHTML = '';
            optionalList.innerHTML = '';
            noAttributesDiv.classList.add('hidden');

            // First, load existing attributes if not already loaded
            const loadExistingFirst = () => {
                if (!window.existingAttributes) {
                    return fetch(`/product-templates/{{ $productTemplate->id }}/existing-attributes`)
                        .then(response => response.json())
                        .then(data => {
                            if (data.success && data.attributes) {
                                window.existingAttributes = data.attributes;
                                console.log('Loaded existing attributes:', data.attributes);
                            }
                        })
                        .catch(error => {
                            console.error('Error loading existing attributes:', error);
                        });
                }
                return Promise.resolve();
            };

            // Then fetch attributes from API
            loadExistingFirst().then(() => {
                fetch(`/tik-tok-category-attributes/api/attributes?category_id=${categoryId}`)
                    .then(response => response.json())
                    .then(data => {
                        loadingDiv.classList.add('hidden');
                        
                        if (data.success && data.data) {
                            const groupedAttributes = data.data.grouped;
                            let hasAttributes = false;

                            // Display required attributes
                            if (groupedAttributes.required && groupedAttributes.required.length > 0) {
                                hasAttributes = true;
                                requiredList.innerHTML = groupedAttributes.required.map(attr => 
                                    createAttributeInput(attr, true)
                                ).join('');
                            } else {
                                requiredList.innerHTML = '<p class="text-gray-500 text-sm">Không có thuộc tính bắt buộc</p>';
                            }

                            // Display optional attributes
                            if (groupedAttributes.optional && groupedAttributes.optional.length > 0) {
                                hasAttributes = true;
                                optionalList.innerHTML = groupedAttributes.optional.map(attr => 
                                    createAttributeInput(attr, false)
                                ).join('');
                            } else {
                                optionalList.innerHTML = '<p class="text-gray-500 text-sm">Không có thuộc tính tùy chọn</p>';
                            }

                            if (!hasAttributes) {
                                noAttributesDiv.classList.remove('hidden');
                            }
                        } else {
                            noAttributesDiv.classList.remove('hidden');
                        }
                    })
                    .catch(error => {
                        console.error('Error loading attributes:', error);
                        loadingDiv.classList.add('hidden');
                        noAttributesDiv.classList.remove('hidden');
                    });
            });
        }

        // Create attribute input field
        function createAttributeInput(attribute, isRequired) {
            const requiredClass = isRequired ? 'border-red-500' : 'border-gray-600';
            const requiredLabel = isRequired ? ' <span class="text-red-400">*</span>' : '';
            
            let inputHtml = '';
            
            if (attribute.values && attribute.values.length > 0) {
                if (attribute.is_multiple_selection) {
                    // Dropdown-style checkbox selector
                    inputHtml = `
                        <div class="relative">
                            <button type="button" 
                                    class="w-full bg-gray-700 border ${requiredClass} rounded-lg px-2 py-1 text-sm text-white focus:outline-none focus:border-blue-500 flex items-center justify-between"
                                    onclick="toggleCheckboxDropdown('${attribute.attribute_id}')">
                                <span id="selected-text-${attribute.attribute_id}">-- Chọn --</span>
                                <i class="fas fa-chevron-down text-xs"></i>
                            </button>
                            <div id="checkbox-dropdown-${attribute.attribute_id}" 
                                 class="absolute z-10 w-full mt-1 bg-gray-700 border border-gray-600 rounded-lg shadow-lg hidden max-h-48 overflow-y-auto">
                                ${attribute.values.map(value => `
                                    <label class="flex items-center space-x-2 px-3 py-2 hover:bg-gray-600 cursor-pointer text-sm">
                                        <input type="checkbox" 
                                               name="attributes[${attribute.attribute_id}][]" 
                                               value="${value.id}"
                                               class="rounded border-gray-600 bg-gray-700 text-blue-500 focus:ring-blue-500 focus:ring-offset-0"
                                               onchange="updateSelectedText('${attribute.attribute_id}')"
                                               ${isRequired ? 'required' : ''}>
                                        <span class="text-white">${value.name}</span>
                                    </label>
                                `).join('')}
                            </div>
                        </div>
                    `;
                } else {
                    // Dropdown for single selection
                    inputHtml = `
                        <select name="attributes[${attribute.attribute_id}]" 
                                class="w-full bg-gray-700 border ${requiredClass} rounded-lg px-2 py-1 text-sm text-white focus:outline-none focus:border-blue-500"
                                ${isRequired ? 'required' : ''}>
                            <option value="">-- Chọn --</option>
                            ${attribute.values.map(value => 
                                `<option value="${value.id}">${value.name}</option>`
                            ).join('')}
                        </select>
                    `;
                }
            } else if (attribute.is_customizable) {
                // Text input for customizable attributes
                inputHtml = `
                    <input type="text" 
                           name="attributes[${attribute.attribute_id}]" 
                           placeholder="Nhập ${attribute.name}"
                           class="w-full bg-gray-700 border ${requiredClass} rounded-lg px-2 py-1 text-sm text-white placeholder-gray-400 focus:outline-none focus:border-blue-500"
                           ${isRequired ? 'required' : ''}>
                `;
            } else {
                // Regular text input
                inputHtml = `
                    <input type="text" 
                           name="attributes[${attribute.attribute_id}]" 
                           placeholder="Nhập ${attribute.name}"
                           class="w-full bg-gray-700 border ${requiredClass} rounded-lg px-2 py-1 text-sm text-white placeholder-gray-400 focus:outline-none focus:border-blue-500"
                           ${isRequired ? 'required' : ''}>
                `;
            }

            return `
                <div class="bg-gray-800 rounded-lg p-2 border border-gray-700">
                    <label class="block text-xs font-medium text-gray-300 mb-1">
                        ${attribute.name}${requiredLabel}
                        <span class="text-xs text-gray-500 ml-1">(${attribute.type})</span>
                    </label>
                    ${inputHtml}
                    ${attribute.value_data_format ? 
                        `<p class="text-xs text-gray-500 mt-1">${attribute.value_data_format}</p>` : ''
                    }
                    ${attribute.is_multiple_selection ? 
                        '<p class="text-xs text-blue-400 mt-1">Nhiều giá trị</p>' : ''
                    }
                </div>
            `;
        }

        // Load attributes on page load if category is already selected
        if (categoryInput.value) {
            loadCategoryAttributes(categoryInput.value);
        }

        // Load existing attributes if template has category attributes
        @if($productTemplate->category_id)
            loadExistingAttributes();
        @endif

        // Load existing attributes function
        function loadExistingAttributes() {
            const categoryId = '{{ $productTemplate->category_id }}';
            if (!categoryId) return;

            // Fetch existing attributes from backend
            fetch(`/product-templates/{{ $productTemplate->id }}/existing-attributes`)
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.attributes) {
                        // Store existing attributes for later use
                        window.existingAttributes = data.attributes;
                        console.log('Loaded existing attributes:', data.attributes);
                    }
                })
                .catch(error => {
                    console.error('Error loading existing attributes:', error);
                });
        }

        // Override createAttributeInput to include existing values
        function createAttributeInput(attribute, isRequired) {
            const requiredClass = isRequired ? 'border-red-500' : 'border-gray-600';
            const requiredLabel = isRequired ? ' <span class="text-red-400">*</span>' : '';
            
            // Get existing value for this attribute
            const existingValue = window.existingAttributes && window.existingAttributes[attribute.attribute_id];
            
            let inputHtml = '';
            
            if (attribute.values && attribute.values.length > 0) {
                if (attribute.is_multiple_selection) {
                    // Dropdown-style checkbox selector with existing values
                    const selectedValues = existingValue && Array.isArray(existingValue) ? existingValue : [];
                    const selectedText = selectedValues.length === 0 ? '-- Chọn --' : 
                                       selectedValues.length === 1 ? 
                                       attribute.values.find(v => v.id == selectedValues[0].value_id)?.name || '-- Chọn --' :
                                       `${selectedValues.length} mục đã chọn`;
                    
                    inputHtml = `
                        <div class="relative">
                            <button type="button" 
                                    class="w-full bg-gray-700 border ${requiredClass} rounded-lg px-2 py-1 text-sm text-white focus:outline-none focus:border-blue-500 flex items-center justify-between"
                                    onclick="toggleCheckboxDropdown('${attribute.attribute_id}')">
                                <span id="selected-text-${attribute.attribute_id}">${selectedText}</span>
                                <i class="fas fa-chevron-down text-xs"></i>
                            </button>
                            <div id="checkbox-dropdown-${attribute.attribute_id}" 
                                 class="absolute z-10 w-full mt-1 bg-gray-700 border border-gray-600 rounded-lg shadow-lg hidden max-h-48 overflow-y-auto">
                                ${attribute.values.map(value => {
                                    const isChecked = selectedValues.some(ev => ev.value_id == value.id) ? 'checked' : '';
                                    return `
                                        <label class="flex items-center space-x-2 px-3 py-2 hover:bg-gray-600 cursor-pointer text-sm">
                                            <input type="checkbox" 
                                                   name="attributes[${attribute.attribute_id}][]" 
                                                   value="${value.id}"
                                                   class="rounded border-gray-600 bg-gray-700 text-blue-500 focus:ring-blue-500 focus:ring-offset-0"
                                                   onchange="updateSelectedText('${attribute.attribute_id}')"
                                                   ${isChecked}
                                                   ${isRequired ? 'required' : ''}>
                                            <span class="text-white">${value.name}</span>
                                        </label>
                                    `;
                                }).join('')}
                            </div>
                        </div>
                    `;
                } else {
                    // Dropdown for single selection
                    inputHtml = `
                        <select name="attributes[${attribute.attribute_id}]" 
                                class="w-full bg-gray-700 border ${requiredClass} rounded-lg px-2 py-1 text-sm text-white focus:outline-none focus:border-blue-500"
                                ${isRequired ? 'required' : ''}>
                            <option value="">-- Chọn --</option>
                            ${attribute.values.map(value => {
                                const isSelected = existingValue && existingValue.value_id == value.id ? 'selected' : '';
                                return `<option value="${value.id}" ${isSelected}>${value.name}</option>`;
                            }).join('')}
                        </select>
                    `;
                }
            } else if (attribute.is_customizable) {
                // Text input for customizable attributes
                const existingTextValue = existingValue ? existingValue.value : '';
                inputHtml = `
                    <input type="text" 
                           name="attributes[${attribute.attribute_id}]" 
                           value="${existingTextValue}"
                           placeholder="Nhập ${attribute.name}"
                           class="w-full bg-gray-700 border ${requiredClass} rounded-lg px-2 py-1 text-sm text-white placeholder-gray-400 focus:outline-none focus:border-blue-500"
                           ${isRequired ? 'required' : ''}>
                `;
            } else {
                // Regular text input
                const existingTextValue = existingValue ? existingValue.value : '';
                inputHtml = `
                    <input type="text" 
                           name="attributes[${attribute.attribute_id}]" 
                           value="${existingTextValue}"
                           placeholder="Nhập ${attribute.name}"
                           class="w-full bg-gray-700 border ${requiredClass} rounded-lg px-2 py-1 text-sm text-white placeholder-gray-400 focus:outline-none focus:border-blue-500"
                           ${isRequired ? 'required' : ''}>
                `;
            }

            return `
                <div class="bg-gray-800 rounded-lg p-2 border border-gray-700">
                    <label class="block text-xs font-medium text-gray-300 mb-1">
                        ${attribute.name}${requiredLabel}
                        <span class="text-xs text-gray-500 ml-1">(${attribute.type})</span>
                    </label>
                    ${inputHtml}
                    ${attribute.value_data_format ? 
                        `<p class="text-xs text-gray-500 mt-1">${attribute.value_data_format}</p>` : ''
                    }
                    ${attribute.is_multiple_selection ? 
                        '<p class="text-xs text-blue-400 mt-1">Nhiều giá trị</p>' : ''
                    }
                    ${existingValue ? 
                        `<p class="text-xs text-green-400 mt-1">✓ Đã chọn: ${existingValue.value_name || existingValue.value}</p>` : ''
                    }
                </div>
            `;
        }

        // Dropdown checkbox functions
        window.toggleCheckboxDropdown = function(attributeId) {
            const dropdown = document.getElementById('checkbox-dropdown-' + attributeId);
            const isHidden = dropdown.classList.contains('hidden');
            
            // Close all other dropdowns
            document.querySelectorAll('[id^="checkbox-dropdown-"]').forEach(el => {
                if (el.id !== 'checkbox-dropdown-' + attributeId) {
                    el.classList.add('hidden');
                }
            });
            
            // Toggle current dropdown
            if (isHidden) {
                dropdown.classList.remove('hidden');
            } else {
                dropdown.classList.add('hidden');
            }
        };

        window.updateSelectedText = function(attributeId) {
            const checkboxes = document.querySelectorAll(`input[name="attributes[${attributeId}][]"]:checked`);
            const selectedText = document.getElementById('selected-text-' + attributeId);
            
            if (checkboxes.length === 0) {
                selectedText.textContent = '-- Chọn --';
            } else if (checkboxes.length === 1) {
                selectedText.textContent = checkboxes[0].nextElementSibling.textContent;
            } else {
                selectedText.textContent = `${checkboxes.length} mục đã chọn`;
            }
        };

        // Close dropdowns when clicking outside
        document.addEventListener('click', function(event) {
            if (!event.target.closest('[id^="checkbox-dropdown-"]') && 
                !event.target.closest('button[onclick^="toggleCheckboxDropdown"]')) {
                document.querySelectorAll('[id^="checkbox-dropdown-"]').forEach(el => {
                    el.classList.add('hidden');
                });
            }
        });
    });
</script>
@endpush
