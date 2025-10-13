@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-slate-900 text-gray-100">
    <div class="container mx-auto px-4 py-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h1 class="text-3xl font-bold text-white mb-2">Link Authorization cho Khách hàng</h1>
                    <p class="text-gray-400">Tạo link để khách hàng lấy authorization code từ TikTok Shop</p>
                </div>
                <a href="{{ route('team.tiktok-shop.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg font-medium transition-colors duration-200">
                    Quay lại
                </a>
            </div>
        </div>

        <!-- Authorization Link -->
        <div class="bg-gray-800 rounded-xl border border-gray-700 p-6 mb-8">
            <h3 class="text-lg font-semibold text-white mb-4 flex items-center">
                <i class="fas fa-link mr-2 text-green-400"></i>
                Link Authorization
            </h3>
            
            <div class="bg-green-500/10 border border-green-500/20 rounded-lg p-4 mb-6">
                <h4 class="text-sm font-medium text-green-400 mb-2">Hướng dẫn sử dụng:</h4>
                <ol class="text-sm text-gray-300 space-y-1 list-decimal list-inside">
                    <li>Copy link bên dưới và gửi cho khách hàng</li>
                    <li>Khách hàng click vào link và đăng nhập TikTok Shop</li>
                    <li>Khách hàng đồng ý cấp quyền cho ứng dụng</li>
                    <li>Khách hàng sẽ thấy authorization code để copy</li>
                    <li>Khách hàng gửi authorization code cho bạn</li>
                </ol>
            </div>

            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Authorization Link:</label>
                    <div class="flex items-center space-x-2">
                        <input type="text" 
                               value="{{ $authUrl }}" 
                               readonly 
                               class="flex-1 bg-gray-700 border border-gray-600 rounded-lg px-3 py-2 text-white text-sm"
                               id="authLink">
                        <button onclick="copyToClipboard('authLink')" 
                                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors duration-200">
                            <i class="fas fa-copy mr-1"></i>Copy
                        </button>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Token xác thực:</label>
                    <div class="flex items-center space-x-2">
                        <input type="text" 
                               value="{{ $authToken }}" 
                               readonly 
                               class="flex-1 bg-gray-700 border border-gray-600 rounded-lg px-3 py-2 text-white text-sm font-mono"
                               id="authToken">
                        <button onclick="copyToClipboard('authToken')" 
                                class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors duration-200">
                            <i class="fas fa-copy mr-1"></i>Copy
                        </button>
                    </div>
                </div>
            </div>

            <div class="mt-6 p-4 bg-yellow-500/10 border border-yellow-500/20 rounded-lg">
                <h4 class="text-sm font-medium text-yellow-400 mb-2">Lưu ý quan trọng:</h4>
                <ul class="text-sm text-gray-300 space-y-1 list-disc list-inside">
                    <li>Link này có thời hạn 1 giờ, sau đó cần tạo link mới</li>
                    <li>Mỗi link chỉ có thể sử dụng 1 lần</li>
                    <li>Khách hàng cần có tài khoản TikTok Shop hợp lệ</li>
                    <li>Authorization code chỉ có hiệu lực trong thời gian ngắn</li>
                </ul>
            </div>
        </div>

        <!-- Test Link -->
        <div class="bg-gray-800 rounded-xl border border-gray-700 p-6">
            <h3 class="text-lg font-semibold text-white mb-4 flex items-center">
                <i class="fas fa-flask mr-2 text-blue-400"></i>
                Test Link
            </h3>
            
            <p class="text-gray-400 mb-4">Click vào link bên dưới để test quá trình authorization:</p>
            
            <div class="text-center">
                <a href="{{ $authUrl }}" 
                   target="_blank"
                   class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg font-medium transition-colors duration-200 inline-flex items-center">
                    <i class="fas fa-external-link-alt mr-2"></i>
                    Test Authorization Link
                </a>
            </div>
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
        button.classList.remove('bg-blue-600', 'hover:bg-blue-700');
        
        setTimeout(() => {
            button.innerHTML = originalText;
            button.classList.remove('bg-green-600');
            button.classList.add('bg-blue-600', 'hover:bg-blue-700');
        }, 2000);
        
    } catch (err) {
        console.error('Failed to copy: ', err);
        alert('Không thể copy. Vui lòng copy thủ công.');
    }
}
</script>
@endsection
