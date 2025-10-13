@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-slate-900 text-gray-100">
    <div class="container mx-auto px-4 py-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h1 class="text-3xl font-bold text-white mb-2">Tạo tích hợp TikTok Shop mới</h1>
                    <p class="text-gray-400">Thêm tích hợp TikTok Shop cho team: <span class="text-blue-400 font-medium">{{ $team->name }}</span></p>
                </div>
                <a href="{{ route('team.tiktok-shop.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg font-medium transition-colors duration-200">
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

        @if($errors->any())
            <div class="bg-red-500/20 border border-red-500/50 text-red-400 px-4 py-3 rounded-lg mb-6">
                <ul class="list-disc list-inside">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Create Integration Form -->
        <div class="bg-gray-800 rounded-xl border border-gray-700 p-6 mb-8">
            <h3 class="text-lg font-semibold text-white mb-4">Tạo tích hợp mới</h3>
            
            <div class="text-center mb-6">
                <div class="w-20 h-20 bg-blue-500/20 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-10 h-10 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-white mb-2">Tạo tích hợp TikTok Shop</h3>
                <p class="text-gray-400">Hệ thống sẽ sử dụng App Key/Secret chung do system-admin cấp</p>
            </div>
            
            <form action="{{ route('team.tiktok-shop.store-integration') }}" method="POST">
                @csrf
                
                <div class="bg-blue-500/10 border border-blue-500/20 rounded-lg p-4 mb-6">
                    <h4 class="text-sm font-medium text-blue-400 mb-2">Thông tin tích hợp:</h4>
                    <ul class="text-sm text-gray-300 space-y-1">
                        <li>• App Key: <span class="text-green-400">{{ config('tiktok-shop.app_key') ?? env('TIKTOK_SHOP_APP_KEY', 'Chưa cấu hình') }}</span></li>
                        <li>• App Secret: <span class="text-green-400">[Đã cấu hình bởi system-admin]</span></li>
                        <li>• Trạng thái: <span class="text-yellow-400">Chờ kết nối</span></li>
                    </ul>
                </div>

                <div class="flex justify-center">
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-8 py-3 rounded-lg font-medium transition-colors duration-200 flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        Tạo tích hợp
                    </button>
                </div>
            </form>
        </div>

        <!-- Authorization Code Generator -->
        <div class="bg-gray-800 rounded-xl border border-gray-700 p-6 mb-8">
            <h3 class="text-lg font-semibold text-white mb-4 flex items-center">
                <i class="fas fa-link mr-2 text-green-400"></i>
                Tạo Link Authorization cho Khách hàng
            </h3>
            
            <div class="bg-green-500/10 border border-green-500/20 rounded-lg p-4 mb-6">
                <h4 class="text-sm font-medium text-green-400 mb-2">Cách sử dụng:</h4>
                <ol class="text-sm text-gray-300 space-y-1 list-decimal list-inside">
                    <li>Tạo tích hợp TikTok Shop trước</li>
                    <li>Click "Tạo Link Authorization" để tạo link cho khách hàng</li>
                    <li>Gửi link cho khách hàng để họ click và lấy authorization code</li>
                    <li>Khách hàng sẽ thấy authorization code để copy</li>
                </ol>
            </div>

            <div class="text-center">
                <a href="{{ route('team.tiktok-shop.generate-auth-link') }}" class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg font-medium transition-colors duration-200 inline-flex items-center">
                    <i class="fas fa-external-link-alt mr-2"></i>
                    Tạo Link Authorization
                </a>
            </div>
        </div>

        <!-- Instructions -->
        <div class="bg-gray-800 rounded-xl border border-gray-700 p-6">
            <h3 class="text-lg font-semibold text-white mb-4">Hướng dẫn lấy App Key và App Secret</h3>
            
            <div class="space-y-4 text-gray-300">
                <div>
                    <h4 class="text-md font-semibold text-blue-400 mb-2">Bước 1: Truy cập TikTok Partner Center</h4>
                    <ol class="list-decimal list-inside space-y-1 text-sm ml-4">
                        <li>Đăng nhập vào <a href="https://partner.tiktok.com" target="_blank" class="text-blue-400 hover:underline">TikTok Partner Center</a></li>
                        <li>Chọn "App & Service" từ menu bên trái</li>
                        <li>Click vào TikTok Shop Application của bạn</li>
                    </ol>
                </div>
                
                <div>
                    <h4 class="text-md font-semibold text-green-400 mb-2">Bước 2: Lấy thông tin App</h4>
                    <ol class="list-decimal list-inside space-y-1 text-sm ml-4">
                        <li>Cuộn xuống phần "Developing"</li>
                        <li>Copy "App Key" và "App Secret"</li>
                        <li>Đảm bảo App đã được approve hoặc đang trong trạng thái review</li>
                    </ol>
                </div>
                
                <div>
                    <h4 class="text-md font-semibold text-yellow-400 mb-2">Bước 3: Cấu hình App</h4>
                    <ol class="list-decimal list-inside space-y-1 text-sm ml-4">
                        <li>Thêm redirect URI: <code class="bg-gray-700 px-2 py-1 rounded">{{ route('team.tiktok-shop.callback') }}</code></li>
                        <li>Đảm bảo App có đủ scopes cần thiết</li>
                        <li>Lưu lại cấu hình</li>
                    </ol>
                </div>
            </div>

            <div class="mt-6 p-4 bg-yellow-500/10 border border-yellow-500/20 rounded-lg">
                <h4 class="text-md font-semibold text-yellow-400 mb-2">Lưu ý quan trọng:</h4>
                <ul class="list-disc list-inside space-y-1 text-gray-300 text-sm">
                    <li>App Key và App Secret là thông tin bảo mật, không chia sẻ với người khác</li>
                    <li>Mỗi team có thể tạo nhiều tích hợp để quản lý nhiều TikTok accounts</li>
                    <li>Sau khi tạo tích hợp, bạn cần kết nối để có thể sử dụng</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
