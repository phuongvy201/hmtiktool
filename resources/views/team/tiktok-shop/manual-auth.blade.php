@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-slate-900 text-gray-100">
    <div class="container mx-auto px-4 py-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h1 class="text-3xl font-bold text-white mb-2">Kết nối TikTok Shop - Nhập Code</h1>
                    <p class="text-gray-400">Nhập authorization code để kết nối TikTok Shop cho team: <span class="text-blue-400 font-medium">{{ $team->name }}</span></p>
                </div>
                <a href="{{ route('team.tiktok-shop.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg font-medium transition-colors duration-200">
                    <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Quay lại
                </a>
            </div>
        </div>

        <!-- Success/Error Messages -->
        @if(session('success'))
            <div class="bg-green-500/20 border border-green-500/50 text-green-400 px-4 py-3 rounded-lg mb-6">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-500/20 border border-red-500/50 text-red-400 px-4 py-3 rounded-lg mb-6">
                {{ session('error') }}
            </div>
        @endif

        <!-- Manual Authorization Form -->
        <div class="max-w-2xl mx-auto">
            <div class="bg-gray-800 rounded-xl border border-gray-700 p-8">
                <div class="text-center mb-8">
                    <div class="w-20 h-20 bg-blue-500/20 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-10 h-10 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                        </svg>
                    </div>
                    <h2 class="text-2xl font-bold text-white mb-2">Nhập Authorization Code</h2>
                    <p class="text-gray-400">Nhập authorization code mà seller đã cung cấp để hoàn tất kết nối</p>
                </div>

                <form action="{{ route('team.tiktok-shop.process-auth-code') }}" method="POST">
            <input type="hidden" name="integration_id" value="{{ $integration->id }}">
                    @csrf
                    <div class="space-y-6">
                        <div>
                            <label for="auth_code" class="block text-sm font-medium text-gray-300 mb-2">
                                Authorization Code
                            </label>
                            <input 
                                type="text" 
                                id="auth_code" 
                                name="auth_code" 
                                required
                                class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                placeholder="Nhập authorization code từ seller..."
                                value="{{ old('auth_code') }}"
                            >
                            @error('auth_code')
                                <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="bg-blue-500/10 border border-blue-500/20 rounded-lg p-4">
                            <h3 class="text-sm font-medium text-blue-400 mb-2">Hướng dẫn lấy Authorization Code:</h3>
                            <ol class="text-sm text-gray-300 space-y-1 list-decimal list-inside">
                                <li>Copy link OAuth bên dưới và gửi cho seller</li>
                                <li>Seller click vào link và đăng nhập TikTok Shop</li>
                                <li>Seller đồng ý với các quyền được yêu cầu</li>
                                <li><strong class="text-yellow-400">Seller copy authorization code và gửi lại cho bạn NGAY LẬP TỨC</strong></li>
                                <li><strong class="text-yellow-400">Nhập code vào ô bên trên và nhấn "Kết nối" trong vòng 10 phút</strong></li>
                            </ol>
                            <div class="mt-3 p-3 bg-yellow-500/10 border border-yellow-500/20 rounded">
                                <p class="text-xs text-yellow-400">
                                    <strong>Lưu ý quan trọng:</strong> Authorization code chỉ có hiệu lực trong 10-15 phút và chỉ sử dụng được 1 lần. 
                                    Nếu code hết hạn hoặc đã sử dụng, bạn cần lấy code mới.
                                </p>
                            </div>
                        </div>

                        <div class="bg-gray-700 rounded-lg p-4">
                            <h3 class="text-sm font-medium text-gray-300 mb-2">Link OAuth cho Seller:</h3>
                            <div class="flex items-center space-x-2">
                                <input 
                                    type="text" 
                                    value="{{ $integration->getAuthorizationUrl() }}" 
                                    readonly
                                    class="flex-1 bg-gray-600 border border-gray-500 text-gray-300 rounded px-3 py-2 text-sm"
                                >
                                <button 
                                    type="button" 
                                    onclick="copyToClipboard(this.previousElementSibling)"
                                    class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-2 rounded text-sm transition-colors duration-200"
                                >
                                    Copy
                                </button>
                            </div>
                        </div>

                        <div class="flex justify-end space-x-4">
                            <a href="{{ route('team.tiktok-shop.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-6 py-3 rounded-lg font-medium transition-colors duration-200">
                                Hủy
                            </a>
                            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-medium transition-colors duration-200 flex items-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path>
                                </svg>
                                Kết nối
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Shop Data Form -->
            <div class="bg-gray-800 rounded-xl border border-gray-700 p-8 mt-6">
                <div class="text-center mb-8">
                    <div class="w-20 h-20 bg-green-500/20 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-10 h-10 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <h2 class="text-2xl font-bold text-white mb-2">Nhập Dữ Liệu Shop</h2>
                    <p class="text-gray-400">Nếu bạn đã có dữ liệu JSON của shop, có thể nhập trực tiếp vào đây</p>
                </div>

                <form action="{{ route('team.tiktok-shop.process-shop-data') }}" method="POST">
                    <input type="hidden" name="integration_id" value="{{ $integration->id }}">
                    @csrf
                    <div class="space-y-6">
                        <div>
                            <label for="shop_data" class="block text-sm font-medium text-gray-300 mb-2">
                                Dữ Liệu Shop (JSON)
                            </label>
                            <textarea 
                                id="shop_data" 
                                name="shop_data" 
                                rows="8"
                                required
                                class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent font-mono text-sm"
                                placeholder='{"id": "7494088027748009056", "code": "GBLCMNQH68", "name": "BLUPRINTER Tees", "cipher": "GCP_P3DQQQAAAADHGmVrcj6COQOADjHSJeoe", "region": "GB", "seller_type": "LOCAL"}'
                            >{{ old('shop_data') }}</textarea>
                            @error('shop_data')
                                <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="bg-green-500/10 border border-green-500/20 rounded-lg p-4">
                            <h3 class="text-sm font-medium text-green-400 mb-2">Hướng dẫn sử dụng:</h3>
                            <ol class="text-sm text-gray-300 space-y-1 list-decimal list-inside">
                                <li>Copy dữ liệu JSON shop từ response của TikTok API</li>
                                <li>Dán vào ô textarea bên trên</li>
                                <li>Đảm bảo format JSON hợp lệ</li>
                                <li>Nhấn "Lưu Shop" để lưu thông tin shop</li>
                            </ol>
                            <div class="mt-3 p-3 bg-blue-500/10 border border-blue-500/20 rounded">
                                <p class="text-xs text-blue-400">
                                    <strong>Ví dụ dữ liệu:</strong> Dữ liệu JSON phải chứa các trường: id, code, name, cipher, region, seller_type
                                </p>
                            </div>
                        </div>

                        <div class="flex justify-end space-x-4">
                            <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg font-medium transition-colors duration-200 flex items-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                Lưu Shop
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Integration Info -->
            <div class="bg-gray-800 rounded-xl border border-gray-700 p-6 mt-6">
                <h3 class="text-lg font-semibold text-white mb-4">Thông tin tích hợp</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-400 mb-1">App Key</label>
                        <p class="text-gray-300">{{ $integration->getAppKey() }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-400 mb-1">Trạng thái</label>
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium border {{ $integration->status_badge_class }}">
                            {{ $integration->status_text }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Benefits -->
            <div class="bg-gray-800 rounded-xl border border-gray-700 p-6 mt-6">
                <h3 class="text-lg font-semibold text-white mb-4">Lợi ích khi kết nối</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="flex items-start space-x-3">
                        <div class="w-8 h-8 bg-green-500/20 rounded-full flex items-center justify-center flex-shrink-0 mt-1">
                            <svg class="w-4 h-4 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div>
                            <h4 class="text-sm font-medium text-white">Quản lý đơn hàng</h4>
                            <p class="text-xs text-gray-400">Tự động đồng bộ và quản lý đơn hàng từ TikTok Shop</p>
                        </div>
                    </div>
                    <div class="flex items-start space-x-3">
                        <div class="w-8 h-8 bg-blue-500/20 rounded-full flex items-center justify-center flex-shrink-0 mt-1">
                            <svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                            </svg>
                        </div>
                        <div>
                            <h4 class="text-sm font-medium text-white">Quản lý sản phẩm</h4>
                            <p class="text-xs text-gray-400">Đồng bộ và cập nhật thông tin sản phẩm</p>
                        </div>
                    </div>
                    <div class="flex items-start space-x-3">
                        <div class="w-8 h-8 bg-purple-500/20 rounded-full flex items-center justify-center flex-shrink-0 mt-1">
                            <svg class="w-4 h-4 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                            </svg>
                        </div>
                        <div>
                            <h4 class="text-sm font-medium text-white">Phân quyền Seller</h4>
                            <p class="text-xs text-gray-400">Phân quyền quản lý shop cho từng seller</p>
                        </div>
                    </div>
                    <div class="flex items-start space-x-3">
                        <div class="w-8 h-8 bg-yellow-500/20 rounded-full flex items-center justify-center flex-shrink-0 mt-1">
                            <svg class="w-4 h-4 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                            </svg>
                        </div>
                        <div>
                            <h4 class="text-sm font-medium text-white">Báo cáo & Thống kê</h4>
                            <p class="text-xs text-gray-400">Theo dõi hiệu suất bán hàng và phân tích dữ liệu</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function copyToClipboard(input) {
    input.select();
    input.setSelectionRange(0, 99999); // For mobile devices
    document.execCommand('copy');
    
    // Show feedback
    const button = input.nextElementSibling;
    const originalText = button.textContent;
    button.textContent = 'Copied!';
    button.classList.add('bg-green-600');
    button.classList.remove('bg-blue-600');
    
    setTimeout(() => {
        button.textContent = originalText;
        button.classList.remove('bg-green-600');
        button.classList.add('bg-blue-600');
    }, 2000);
}
</script>
@endsection
