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
                                         <h1 class="text-3xl font-bold text-white mb-2">Tạo tích hợp TikTok Shop</h1>
                     <p class="text-gray-400">Thiết lập tích hợp TikTok Shop cho team được chọn</p>
                </div>
            </div>
        </div>

        <!-- Form -->
        <div class="max-w-2xl mx-auto">
            <div class="bg-gray-800 rounded-xl border border-gray-700 p-6">
                <form action="{{ route('tiktok-shop.store') }}" method="POST">
                    @csrf
                    
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
                            <label for="team_id" class="block text-sm font-medium text-gray-300 mb-2">Chọn Team *</label>
                            <select id="team_id" name="team_id" required
                                    class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-2 text-white focus:outline-none focus:border-blue-500 @error('team_id') border-red-500 @enderror">
                                <option value="">Chọn team...</option>
                                @foreach($teams as $team)
                                    <option value="{{ $team->id }}" {{ old('team_id') == $team->id ? 'selected' : '' }}>
                                        {{ $team->name }} (ID: {{ $team->id }})
                                    </option>
                                @endforeach
                            </select>
                            @error('team_id')
                                <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div>
                            <label for="app_key" class="block text-sm font-medium text-gray-300 mb-2">App Key *</label>
                            <input type="text" id="app_key" name="app_key" value="{{ old('app_key') }}" required
                                   class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-2 text-white placeholder-gray-400 focus:outline-none focus:border-blue-500 @error('app_key') border-red-500 @enderror"
                                   placeholder="Nhập App Key từ TikTok Partner Center">
                            @error('app_key')
                                <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div>
                            <label for="app_secret" class="block text-sm font-medium text-gray-300 mb-2">App Secret *</label>
                            <input type="password" id="app_secret" name="app_secret" value="{{ old('app_secret') }}" required
                                   class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-2 text-white placeholder-gray-400 focus:outline-none focus:border-blue-500 @error('app_secret') border-red-500 @enderror"
                                   placeholder="Nhập App Secret từ TikTok Partner Center">
                            @error('app_secret')
                                <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                    </div>

                    <!-- Setup Instructions -->
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold text-white mb-4 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            Hướng dẫn thiết lập
                        </h3>
                        
                        <div class="bg-blue-500/10 border border-blue-500/20 rounded-lg p-4">
                            <h4 class="text-md font-semibold text-blue-400 mb-3">Các bước cần thực hiện:</h4>
                            <ol class="list-decimal list-inside space-y-2 text-gray-300 text-sm">
                                <li>
                                    <strong>Đăng ký Developer:</strong> Truy cập 
                                    <a href="https://partner.tiktok.com" target="_blank" class="text-blue-400 hover:text-blue-300 underline">TikTok Partner Center</a>
                                </li>
                                <li>
                                    <strong>Tạo Application:</strong> Tạo ứng dụng mới trong Partner Center
                                </li>
                                <li>
                                    <strong>Bật API Access:</strong> Kích hoạt quyền truy cập API cho ứng dụng
                                </li>
                                <li>
                                    <strong>Lấy thông tin:</strong> Copy App Key và App Secret từ ứng dụng
                                </li>
                                <li>
                                    <strong>Nhập thông tin:</strong> Điền App Key và App Secret vào form trên
                                </li>
                            </ol>
                        </div>
                    </div>

                    <!-- Important Notes -->
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold text-white mb-4 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                            </svg>
                            Lưu ý quan trọng
                        </h3>
                        
                        <div class="bg-yellow-500/10 border border-yellow-500/20 rounded-lg p-4">
                            <ul class="list-disc list-inside space-y-2 text-gray-300 text-sm">
                                <li>App Key và App Secret phải được lấy từ TikTok Partner Center</li>
                                <li>Thông tin này sẽ được mã hóa và bảo mật trong hệ thống</li>
                                <li>Sau khi tạo tích hợp, bạn cần hoàn tất quá trình ủy quyền</li>
                                <li>Nên test bằng Seller Center Development Shop trước khi sử dụng production</li>
                                <li>Access Token có hiệu lực 7 ngày, Refresh Token có hiệu lực 30 ngày</li>
                            </ul>
                        </div>
                    </div>

                    <!-- API Scopes -->
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold text-white mb-4 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                            </svg>
                            Quyền truy cập API
                        </h3>
                        
                        <div class="bg-purple-500/10 border border-purple-500/20 rounded-lg p-4">
                            <p class="text-gray-300 text-sm mb-3">Tích hợp này sẽ yêu cầu các quyền sau:</p>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                <div class="flex items-center">
                                    <svg class="w-4 h-4 text-green-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                    </svg>
                                    <span class="text-gray-300 text-sm">read_products</span>
                                </div>
                                <div class="flex items-center">
                                    <svg class="w-4 h-4 text-green-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                    </svg>
                                    <span class="text-gray-300 text-sm">write_products</span>
                                </div>
                                <div class="flex items-center">
                                    <svg class="w-4 h-4 text-green-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                    </svg>
                                    <span class="text-gray-300 text-sm">read_orders</span>
                                </div>
                                <div class="flex items-center">
                                    <svg class="w-4 h-4 text-green-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                    </svg>
                                    <span class="text-gray-300 text-sm">write_orders</span>
                                </div>
                            </div>
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
                            Tạo tích hợp
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
