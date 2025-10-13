@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-slate-900 text-gray-100">
    <div class="container mx-auto px-4 py-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center mb-6">
                <a href="{{ route('service-packages.index') }}" class="text-blue-400 hover:text-blue-300 mr-4">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                </a>
                <div>
                    <h1 class="text-3xl font-bold text-white mb-2">Tạo Gói Dịch vụ Mới</h1>
                    <p class="text-gray-400">Tạo gói dịch vụ mới với các tính năng và giới hạn</p>
                </div>
            </div>
        </div>

        <!-- Form -->
        <div class="max-w-4xl mx-auto">
            <div class="bg-gray-800 rounded-xl border border-gray-700 p-6">
                <form method="POST" action="{{ route('service-packages.store') }}" class="space-y-6">
                    @csrf

                    <!-- Basic Information -->
                    <div class="border-b border-gray-700 pb-6">
                        <h3 class="text-lg font-semibold text-white mb-4 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            Thông tin cơ bản
                        </h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Name -->
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-300 mb-1">
                                    Tên gói dịch vụ <span class="text-red-400">*</span>
                                </label>
                                <input type="text" 
                                       id="name" 
                                       name="name" 
                                       value="{{ old('name') }}"
                                       required
                                       class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-2 text-white placeholder-gray-400 focus:outline-none focus:border-blue-500"
                                       placeholder="Ví dụ: Gói Cơ bản, Gói Pro, Gói Enterprise">
                                @error('name')
                                    <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Sort Order -->
                            <div>
                                <label for="sort_order" class="block text-sm font-medium text-gray-300 mb-1">
                                    Thứ tự hiển thị
                                </label>
                                <input type="number" 
                                       id="sort_order" 
                                       name="sort_order" 
                                       value="{{ old('sort_order', 0) }}"
                                       min="0"
                                       class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-2 text-white placeholder-gray-400 focus:outline-none focus:border-blue-500"
                                       placeholder="0">
                                @error('sort_order')
                                    <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Description -->
                        <div class="mt-6">
                            <label for="description" class="block text-sm font-medium text-gray-300 mb-1">
                                Mô tả
                            </label>
                            <textarea id="description" 
                                      name="description" 
                                      rows="3"
                                      class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-2 text-white placeholder-gray-400 focus:outline-none focus:border-blue-500"
                                      placeholder="Mô tả chi tiết về gói dịch vụ...">{{ old('description') }}</textarea>
                            @error('description')
                                <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Pricing -->
                    <div class="border-b border-gray-700 pb-6">
                        <h3 class="text-lg font-semibold text-white mb-4 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                            </svg>
                            Thông tin giá
                        </h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <!-- Price -->
                            <div>
                                <label for="price" class="block text-sm font-medium text-gray-300 mb-1">
                                    Giá <span class="text-red-400">*</span>
                                </label>
                                <input type="number" 
                                       id="price" 
                                       name="price" 
                                       value="{{ old('price', 0) }}"
                                       min="0"
                                       step="0.01"
                                       required
                                       class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-2 text-white placeholder-gray-400 focus:outline-none focus:border-blue-500"
                                       placeholder="0">
                                @error('price')
                                    <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Currency -->
                            <div>
                                <label for="currency" class="block text-sm font-medium text-gray-300 mb-1">
                                    Đơn vị tiền tệ <span class="text-red-400">*</span>
                                </label>
                                <select id="currency" 
                                        name="currency" 
                                        required
                                        class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-2 text-white focus:outline-none focus:border-blue-500">
                                    <option value="VND" {{ old('currency', 'VND') === 'VND' ? 'selected' : '' }}>VND</option>
                                    <option value="USD" {{ old('currency') === 'USD' ? 'selected' : '' }}>USD</option>
                                    <option value="EUR" {{ old('currency') === 'EUR' ? 'selected' : '' }}>EUR</option>
                                    <option value="GPB" {{ old('currency') === 'EUR' ? 'selected' : '' }}>EUR</option>
                                </select>
                                @error('currency')
                                    <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Duration -->
                            <div>
                                <label for="duration_days" class="block text-sm font-medium text-gray-300 mb-1">
                                    Thời hạn (ngày) <span class="text-red-400">*</span>
                                </label>
                                <input type="number" 
                                       id="duration_days" 
                                       name="duration_days" 
                                       value="{{ old('duration_days', 30) }}"
                                       min="1"
                                       required
                                       class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-2 text-white placeholder-gray-400 focus:outline-none focus:border-blue-500"
                                       placeholder="30">
                                @error('duration_days')
                                    <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Limits -->
                    <div class="border-b border-gray-700 pb-6">
                        <h3 class="text-lg font-semibold text-white mb-4 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                            </svg>
                            Giới hạn sử dụng
                        </h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <!-- Max Users -->
                            <div>
                                <label for="max_users" class="block text-sm font-medium text-gray-300 mb-1">
                                    Số lượng user tối đa <span class="text-red-400">*</span>
                                </label>
                                <input type="number" 
                                       id="max_users" 
                                       name="max_users" 
                                       value="{{ old('max_users', 1) }}"
                                       min="1"
                                       required
                                       class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-2 text-white placeholder-gray-400 focus:outline-none focus:border-blue-500"
                                       placeholder="1">
                                @error('max_users')
                                    <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Max Projects -->
                            <div>
                                <label for="max_projects" class="block text-sm font-medium text-gray-300 mb-1">
                                    Số dự án tối đa <span class="text-red-400">*</span>
                                </label>
                                <input type="number" 
                                       id="max_projects" 
                                       name="max_projects" 
                                       value="{{ old('max_projects', 5) }}"
                                       min="1"
                                       required
                                       class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-2 text-white placeholder-gray-400 focus:outline-none focus:border-blue-500"
                                       placeholder="5">
                                @error('max_projects')
                                    <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Max Storage -->
                            <div>
                                <label for="max_storage_gb" class="block text-sm font-medium text-gray-300 mb-1">
                                    Dung lượng lưu trữ (GB) <span class="text-red-400">*</span>
                                </label>
                                <input type="number" 
                                       id="max_storage_gb" 
                                       name="max_storage_gb" 
                                       value="{{ old('max_storage_gb', 1) }}"
                                       min="1"
                                       required
                                       class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-2 text-white placeholder-gray-400 focus:outline-none focus:border-blue-500"
                                       placeholder="1">
                                @error('max_storage_gb')
                                    <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Features -->
                    <div class="border-b border-gray-700 pb-6">
                        <h3 class="text-lg font-semibold text-white mb-4 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path>
                            </svg>
                            Tính năng
                        </h3>
                        
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-300 mb-2">
                                    Tính năng được cấp
                                </label>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    @php
                                        $availableFeatures = [
                                            'user_management' => 'Quản lý người dùng',
                                            'project_management' => 'Quản lý dự án',
                                            'file_upload' => 'Tải file lên',
                                            'api_access' => 'Truy cập API',
                                            'advanced_analytics' => 'Phân tích nâng cao',
                                            'priority_support' => 'Hỗ trợ ưu tiên',
                                            'custom_branding' => 'Tùy chỉnh thương hiệu',
                                            'backup_restore' => 'Sao lưu & Khôi phục',
                                            'team_collaboration' => 'Làm việc nhóm',
                                            'advanced_security' => 'Bảo mật nâng cao',
                                        ];
                                    @endphp
                                    
                                    @foreach($availableFeatures as $key => $label)
                                        <label class="flex items-center">
                                            <input type="checkbox" 
                                                   name="features[]" 
                                                   value="{{ $key }}"
                                                   {{ in_array($key, old('features', [])) ? 'checked' : '' }}
                                                   class="rounded border-gray-600 text-blue-600 bg-gray-700 focus:ring-blue-500 focus:ring-2">
                                            <span class="ml-2 text-sm text-gray-300">{{ $label }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Status -->
                    <div class="border-b border-gray-700 pb-6">
                        <h3 class="text-lg font-semibold text-white mb-4 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            Trạng thái
                        </h3>
                        
                        <div class="space-y-4">
                            <label class="flex items-center">
                                <input type="checkbox" 
                                       name="is_active" 
                                       value="1"
                                       {{ old('is_active', true) ? 'checked' : '' }}
                                       class="rounded border-gray-600 text-blue-600 bg-gray-700 focus:ring-blue-500 focus:ring-2">
                                <span class="ml-2 text-sm text-gray-300">Kích hoạt gói dịch vụ</span>
                            </label>

                            <label class="flex items-center">
                                <input type="checkbox" 
                                       name="is_featured" 
                                       value="1"
                                       {{ old('is_featured') ? 'checked' : '' }}
                                       class="rounded border-gray-600 text-blue-600 bg-gray-700 focus:ring-blue-500 focus:ring-2">
                                <span class="ml-2 text-sm text-gray-300">Đánh dấu nổi bật</span>
                            </label>
                        </div>
                    </div>

                    <!-- Submit Buttons -->
                    <div class="flex justify-end space-x-4 pt-6">
                        <a href="{{ route('service-packages.index') }}" 
                           class="px-6 py-2 border border-gray-600 text-gray-300 rounded-lg hover:bg-gray-700 transition-colors duration-200">
                            Hủy
                        </a>
                        <button type="submit" 
                                class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition-colors duration-200">
                            <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            Tạo gói dịch vụ
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
