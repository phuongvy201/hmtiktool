@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-slate-900 text-gray-100">
    <div class="container mx-auto px-4 py-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center mb-6">
                <a href="{{ route('tiktok-shop.index') }}" class="text-blue-400 hover:text-blue-300 mr-4">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                </a>
                <div>
                    <h1 class="text-3xl font-bold text-white mb-2">Chỉnh sửa tích hợp TikTok Shop</h1>
                    <p class="text-gray-400">Cập nhật thông tin tích hợp cho team: <span class="text-blue-400 font-medium">{{ $integration->team->name }}</span></p>
                </div>
            </div>
        </div>

        <!-- Form -->
        <div class="max-w-2xl mx-auto">
            <div class="bg-gray-800 rounded-xl border border-gray-700 p-6">
                <form action="{{ route('tiktok-shop.update', $integration) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <!-- Team Information -->
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold text-white mb-4 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                            Thông tin Team
                        </h3>
                        
                        <div class="bg-gray-700 rounded-lg p-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-400 mb-1">Tên Team</label>
                                    <p class="text-white font-medium">{{ $integration->team->name }}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-400 mb-1">ID Team</label>
                                    <p class="text-gray-300">{{ $integration->team->id }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- App Information -->
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold text-white mb-4 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                            </svg>
                            Thông tin ứng dụng TikTok Shop
                        </h3>
                        
                        <div class="space-y-4">
                            <div>
                                <label for="app_key" class="block text-sm font-medium text-gray-300 mb-2">App Key *</label>
                                <input type="text" id="app_key" name="app_key" value="{{ old('app_key', $integration->app_key) }}" required
                                       class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-2 text-white placeholder-gray-400 focus:outline-none focus:border-blue-500 @error('app_key') border-red-500 @enderror"
                                       placeholder="Nhập App Key từ TikTok Partner Center">
                                @error('app_key')
                                    <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <div>
                                <label for="app_secret" class="block text-sm font-medium text-gray-300 mb-2">App Secret *</label>
                                <input type="password" id="app_secret" name="app_secret" value="{{ old('app_secret', $integration->app_secret) }}" required
                                       class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-2 text-white placeholder-gray-400 focus:outline-none focus:border-blue-500 @error('app_secret') border-red-500 @enderror"
                                       placeholder="Nhập App Secret từ TikTok Partner Center">
                                @error('app_secret')
                                    <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Current Status -->
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold text-white mb-4 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            Trạng thái hiện tại
                        </h3>
                        
                        <div class="bg-gray-700 rounded-lg p-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-400 mb-1">Trạng thái</label>
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium border {{ $integration->status_badge_class }}">
                                        {{ $integration->status_text }}
                                    </span>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-400 mb-1">Ngày tạo</label>
                                    <p class="text-gray-300">{{ $integration->created_at->format('d/m/Y H:i') }}</p>
                                </div>
                                @if($integration->shop_name)
                                <div>
                                    <label class="block text-sm font-medium text-gray-400 mb-1">Tên Shop</label>
                                    <p class="text-white">{{ $integration->shop_name }}</p>
                                </div>
                                @endif
                                @if($integration->error_message)
                                <div>
                                    <label class="block text-sm font-medium text-gray-400 mb-1">Lỗi</label>
                                    <p class="text-red-400 text-sm">{{ $integration->error_message }}</p>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Important Notes -->
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold text-white mb-4 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                            </svg>
                            Lưu ý quan trọng
                        </h3>
                        
                        <div class="bg-red-500/10 border border-red-500/20 rounded-lg p-4">
                            <ul class="list-disc list-inside space-y-2 text-gray-300 text-sm">
                                <li>Khi cập nhật App Key/Secret, trạng thái sẽ reset về "Chờ xác thực"</li>
                                <li>Team admin sẽ cần thực hiện lại quá trình ủy quyền TikTok Shop</li>
                                <li>Thông tin token hiện tại sẽ bị xóa khi cập nhật</li>
                                <li>Chỉ cập nhật khi thực sự cần thiết</li>
                            </ul>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center justify-end space-x-4 pt-6 border-t border-gray-700">
                        <a href="{{ route('tiktok-shop.index') }}" 
                           class="px-6 py-2 border border-gray-600 text-gray-300 rounded-lg hover:bg-gray-700 transition-colors duration-200">
                            Hủy
                        </a>
                        <button type="submit" 
                                class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition-colors duration-200">
                            Cập nhật tích hợp
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
