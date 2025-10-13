@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-slate-900 text-gray-100">
    <div class="container mx-auto px-4 py-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="text-center">
                @if($success)
                    <div class="w-20 h-20 bg-green-500/20 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-check-circle text-green-400 text-4xl"></i>
                    </div>
                    <h1 class="text-3xl font-bold text-white mb-2">Authorization Thành công!</h1>
                    <p class="text-gray-400">Bạn đã lấy được authorization code từ TikTok Shop</p>
                @else
                    <div class="w-20 h-20 bg-red-500/20 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-times-circle text-red-400 text-4xl"></i>
                    </div>
                    <h1 class="text-3xl font-bold text-white mb-2">Authorization Thất bại</h1>
                    <p class="text-gray-400">Có lỗi xảy ra trong quá trình authorization</p>
                @endif
            </div>
        </div>

        @if($success)
        <!-- Success Result -->
        <div class="bg-gray-800 rounded-xl border border-gray-700 p-6 mb-8">
            <h3 class="text-lg font-semibold text-white mb-4 flex items-center">
                <i class="fas fa-key mr-2 text-green-400"></i>
                Authorization Code
            </h3>
            
            <div class="bg-green-500/10 border border-green-500/20 rounded-lg p-4 mb-6">
                <h4 class="text-sm font-medium text-green-400 mb-2">Thành công!</h4>
                <p class="text-sm text-gray-300">Bạn đã lấy được authorization code. Hãy copy code này và gửi cho team admin để hoàn tất kết nối.</p>
            </div>

            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Authorization Code:</label>
                    <div class="flex items-center space-x-2">
                        <input type="text" 
                               value="{{ $authCode }}" 
                               readonly 
                               class="flex-1 bg-gray-700 border border-gray-600 rounded-lg px-3 py-2 text-white text-sm font-mono"
                               id="authCode">
                        <button onclick="copyToClipboard('authCode')" 
                                class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors duration-200">
                            <i class="fas fa-copy mr-1"></i>Copy Code
                        </button>
                    </div>
                </div>

                @if(isset($appKey) || isset($locale) || isset($shopRegion))
                <div class="bg-blue-500/10 border border-blue-500/20 rounded-lg p-4">
                    <h4 class="text-sm font-medium text-blue-400 mb-2">Thông tin từ TikTok:</h4>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                        @if(isset($appKey))
                        <div>
                            <span class="text-gray-400">App Key:</span>
                            <span class="text-white font-mono">{{ $appKey }}</span>
                        </div>
                        @endif
                        @if(isset($locale))
                        <div>
                            <span class="text-gray-400">Locale:</span>
                            <span class="text-white">{{ $locale }}</span>
                        </div>
                        @endif
                        @if(isset($shopRegion))
                        <div>
                            <span class="text-gray-400">Shop Region:</span>
                            <span class="text-white">{{ $shopRegion }}</span>
                        </div>
                        @endif
                    </div>
                </div>
                @endif

                <div class="bg-blue-500/10 border border-blue-500/20 rounded-lg p-4">
                    <h4 class="text-sm font-medium text-blue-400 mb-2">Bước tiếp theo:</h4>
                    <ol class="text-sm text-gray-300 space-y-1 list-decimal list-inside">
                        <li>Copy authorization code ở trên</li>
                        <li>Gửi code này cho team admin</li>
                        <li>Team admin sẽ sử dụng code để hoàn tất kết nối TikTok Shop</li>
                        <li>Bạn sẽ được thông báo khi kết nối thành công</li>
                    </ol>
                </div>
            </div>
        </div>

        <!-- Share Code -->
        <div class="bg-gray-800 rounded-xl border border-gray-700 p-6 mb-8">
            <h3 class="text-lg font-semibold text-white mb-4 flex items-center">
                <i class="fas fa-share mr-2 text-blue-400"></i>
                Chia sẻ Code
            </h3>
            
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Tin nhắn mẫu:</label>
                    <textarea readonly 
                              class="w-full bg-gray-700 border border-gray-600 rounded-lg px-3 py-2 text-white text-sm"
                              rows="4"
                              id="messageTemplate">Xin chào,

Tôi đã lấy được authorization code từ TikTok Shop:

{{ $authCode }}

Vui lòng sử dụng code này để hoàn tất kết nối TikTok Shop.

Cảm ơn!</textarea>
                </div>
                
                <div class="flex space-x-2">
                    <button onclick="copyToClipboard('messageTemplate')" 
                            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors duration-200">
                        <i class="fas fa-copy mr-1"></i>Copy Tin nhắn
                    </button>
                    <button onclick="copyToClipboard('authCode')" 
                            class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors duration-200">
                        <i class="fas fa-key mr-1"></i>Copy Code
                    </button>
                </div>
            </div>
        </div>

        @else
        <!-- Error Result -->
        <div class="bg-gray-800 rounded-xl border border-gray-700 p-6 mb-8">
            <h3 class="text-lg font-semibold text-white mb-4 flex items-center">
                <i class="fas fa-exclamation-triangle mr-2 text-red-400"></i>
                Lỗi Authorization
            </h3>
            
            <div class="bg-red-500/10 border border-red-500/20 rounded-lg p-4 mb-6">
                <h4 class="text-sm font-medium text-red-400 mb-2">Lỗi:</h4>
                <p class="text-sm text-gray-300">{{ $message }}</p>
            </div>

            <div class="space-y-4">
                <div class="bg-yellow-500/10 border border-yellow-500/20 rounded-lg p-4">
                    <h4 class="text-sm font-medium text-yellow-400 mb-2">Có thể thử lại:</h4>
                    <ul class="text-sm text-gray-300 space-y-1 list-disc list-inside">
                        <li>Yêu cầu team admin tạo link authorization mới</li>
                        <li>Đảm bảo bạn đã đăng nhập đúng tài khoản TikTok Shop</li>
                        <li>Kiểm tra kết nối internet</li>
                        <li>Thử lại sau vài phút</li>
                    </ul>
                </div>
            </div>
        </div>
        @endif

        <!-- Actions -->
        <div class="text-center">
            @if($success)
                <a href="{{ route('team.tiktok-shop.index') }}" 
                   class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg font-medium transition-colors duration-200 inline-flex items-center mr-4">
                    <i class="fas fa-check mr-2"></i>
                    Hoàn thành
                </a>
            @else
                <a href="{{ route('team.tiktok-shop.index') }}" 
                   class="bg-gray-600 hover:bg-gray-700 text-white px-6 py-3 rounded-lg font-medium transition-colors duration-200 inline-flex items-center">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Quay lại
                </a>
            @endif
        </div>
    </div>
</div>

<script>
function copyToClipboard(elementId) {
    const element = document.getElementById(elementId);
    element.select();
    element.setSelectionRange(0, 99999); // For mobile devices
    
    try {
        document.execCommand('copy');
        
        // Show success message
        const button = event.target.closest('button');
        const originalText = button.innerHTML;
        button.innerHTML = '<i class="fas fa-check mr-1"></i>Copied!';
        button.classList.add('bg-green-600');
        button.classList.remove('bg-blue-600', 'hover:bg-blue-700', 'bg-green-600', 'hover:bg-green-700');
        
        setTimeout(() => {
            button.innerHTML = originalText;
            if (button.innerHTML.includes('Copy Tin nhắn')) {
                button.classList.remove('bg-green-600');
                button.classList.add('bg-blue-600', 'hover:bg-blue-700');
            } else {
                button.classList.remove('bg-green-600');
                button.classList.add('bg-green-600', 'hover:bg-green-700');
            }
        }, 2000);
        
    } catch (err) {
        console.error('Failed to copy: ', err);
        alert('Không thể copy. Vui lòng copy thủ công.');
    }
}
</script>
@endsection
