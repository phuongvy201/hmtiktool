@extends('layouts.app')

@section('title', 'Chi tiết Sản phẩm')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-3xl font-bold text-white">Chi tiết Sản phẩm</h1>
                <p class="text-gray-400 mt-2">Team: {{ $team->name }}</p>
            </div>
            <div class="flex space-x-3">
                @can('update-products')
                <a href="{{ route('products.edit', $product) }}" 
                   class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg transition duration-200">
                    <i class="fas fa-edit mr-2"></i>Chỉnh sửa
                </a>
                
                <button id="uploadToTikTokBtn" 
                        class="bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-4 rounded-lg transition duration-200">
                    <i class="fas fa-upload mr-2"></i>Upload lên TikTok
                </button>
                @endcan
                
                <a href="{{ route('products.index') }}" 
                   class="bg-gray-700 hover:bg-gray-600 text-white font-medium py-2 px-4 rounded-lg transition duration-200">
                    <i class="fas fa-arrow-left mr-2"></i>Quay lại
                </a>
            </div>
        </div>

        <div class="bg-gray-800 rounded-lg shadow-sm border border-gray-700 overflow-hidden">
            <div class="p-6">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <!-- Product Image -->
                    <div>
                        @if($product->primaryImage)
                            <img src="{{ $product->primaryImage->url }}" 
                                 alt="{{ $product->title }}" 
                                 class="w-full h-96 object-cover rounded-lg border border-gray-600">
                        @elseif($product->images->where('source', 'product')->count() > 0)
                            <img src="{{ $product->images->where('source', 'product')->first()->url }}" 
                                 alt="{{ $product->title }}" 
                                 class="w-full h-96 object-cover rounded-lg border border-gray-600">
                        @else
                            <div class="w-full h-96 bg-gray-700 rounded-lg border border-gray-600 flex items-center justify-center">
                                <div class="text-center">
                                    <i class="fas fa-image text-6xl text-gray-500 mb-4"></i>
                                    <p class="text-gray-400">Không có ảnh</p>
                                </div>
                            </div>
                        @endif
                    </div>

                    <!-- Product Details -->
                    <div class="space-y-6">
                        <div>
                            <h2 class="text-2xl font-bold text-white mb-2">{{ $product->title }}</h2>
                            <div class="flex items-center space-x-4">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    {{ $product->status === 'active' ? 'bg-green-900 text-green-200' : 'bg-red-900 text-red-200' }}">
                                    {{ $product->status === 'active' ? 'Hoạt động' : 'Không hoạt động' }}
                                </span>
                                @if($product->is_active)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-900 text-green-200">
                                        Kích hoạt
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-700 text-gray-300">
                                        Vô hiệu
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="bg-gray-700 rounded-lg p-4 border border-gray-600">
                            <h3 class="text-lg font-medium text-white mb-3">Thông tin giá</h3>
                            <div class="space-y-2">
                                <div class="flex justify-between">
                                    <span class="text-gray-400">Giá sản phẩm:</span>
                                    <span class="font-medium text-white">${{ number_format($product->price, 2) }}</span>
                                </div>
                                @if($product->productTemplate)
                                    <div class="flex justify-between">
                                        <span class="text-gray-400">Giá template:</span>
                                        <span class="font-medium text-white">${{ number_format($product->productTemplate->base_price, 2) }}</span>
                                    </div>
                                    <hr class="border-gray-600">
                                    <div class="flex justify-between">
                                        <span class="text-white font-medium">Tổng giá:</span>
                                        <span class="text-xl font-bold text-blue-400">${{ number_format($product->total_price, 2) }}</span>
                                    </div>
                                @else
                                    <div class="flex justify-between">
                                        <span class="text-white font-medium">Tổng giá:</span>
                                        <span class="text-xl font-bold text-blue-400">${{ number_format($product->price, 2) }}</span>
                                    </div>
                                @endif
                            </div>
                        </div>

                        @if($product->description)
                            <div>
                                <h3 class="text-lg font-medium text-white mb-3">Mô tả</h3>
                                <div class="bg-gray-700 rounded-lg p-4 border border-gray-600">
                                    <p class="text-gray-300 whitespace-pre-wrap">{{ $product->description }}</p>
                                </div>
                            </div>
                        @endif

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-400">SKU</label>
                                <p class="mt-1 text-sm text-white font-mono">{{ $product->sku }}</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-400">Template</label>
                                <p class="mt-1 text-sm text-white">
                                    @if($product->productTemplate)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-900 text-blue-200">
                                            {{ $product->productTemplate->name }}
                                        </span>
                                    @else
                                        <span class="text-gray-500">Không có template</span>
                                    @endif
                                </p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-400">Người tạo</label>
                                <p class="mt-1 text-sm text-white">{{ $product->user->name }}</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-400">Ngày tạo</label>
                                <p class="mt-1 text-sm text-white">{{ $product->created_at->format('d/m/Y H:i') }}</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-400">Cập nhật lần cuối</label>
                                <p class="mt-1 text-sm text-white">{{ $product->updated_at->format('d/m/Y H:i') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Product Images Gallery -->
        @if($product->images->where('source', 'product')->count() > 1)
        <div class="bg-gray-800 rounded-lg shadow-sm border border-gray-700 overflow-hidden mt-6">
            <div class="p-6">
                <h3 class="text-lg font-medium text-white mb-4">Tất cả hình ảnh sản phẩm</h3>
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                    @foreach($product->images->where('source', 'product') as $image)
                        <div class="relative group">
                            <div class="relative aspect-square bg-gray-600 rounded-lg overflow-hidden">
                                <img src="{{ $image->url }}" 
                                     alt="{{ $image->file_name }}" 
                                     class="w-full h-full object-cover">
                                <div class="absolute top-2 left-2 bg-blue-500 text-white text-xs px-2 py-1 rounded">
                                    {{ $image->is_primary ? 'Ảnh chính' : 'Ảnh ' . ($loop->iteration) }}
                                </div>
                            </div>
                            <p class="text-xs text-gray-400 mt-1 truncate">{{ $image->file_name }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif


    </div>
</div>

<!-- Upload to TikTok Modal -->
<div id="uploadModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-gray-800 rounded-lg shadow-xl max-w-md w-full">
            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-white">Upload hình ảnh lên TikTok</h3>
                    <button id="closeModal" class="text-gray-400 hover:text-white">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                
                <div id="uploadStatus" class="mb-4">
                    <div class="flex items-center">
                        <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-blue-500 mr-2"></div>
                        <span class="text-gray-300">Đang upload hình ảnh...</span>
                    </div>
                </div>
                
                <div id="uploadResult" class="hidden">
                    <div id="successMessage" class="hidden">
                        <div class="flex items-center text-green-400 mb-2">
                            <i class="fas fa-check-circle mr-2"></i>
                            <span id="successText"></span>
                        </div>
                    </div>
                    <div id="errorMessage" class="hidden">
                        <div class="flex items-center text-red-400 mb-2">
                            <i class="fas fa-exclamation-circle mr-2"></i>
                            <span id="errorText"></span>
                        </div>
                    </div>
                </div>
                
                <div class="flex justify-end space-x-3">
                    <button id="closeModalBtn" class="bg-gray-600 hover:bg-gray-700 text-white font-medium py-2 px-4 rounded-lg transition duration-200">
                        Đóng
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const uploadBtn = document.getElementById('uploadToTikTokBtn');
    const modal = document.getElementById('uploadModal');
    const closeModal = document.getElementById('closeModal');
    const closeModalBtn = document.getElementById('closeModalBtn');
    const uploadStatus = document.getElementById('uploadStatus');
    const uploadResult = document.getElementById('uploadResult');
    const successMessage = document.getElementById('successMessage');
    const errorMessage = document.getElementById('errorMessage');
    const successText = document.getElementById('successText');
    const errorText = document.getElementById('errorText');

    uploadBtn.addEventListener('click', function() {
        // Hiển thị modal
        modal.classList.remove('hidden');
        uploadStatus.classList.remove('hidden');
        uploadResult.classList.add('hidden');
        
        // Disable button
        uploadBtn.disabled = true;
        uploadBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Đang upload...';
        
        // Gọi API upload
        fetch('{{ route("products.upload-images-to-tiktok", $product) }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            uploadStatus.classList.add('hidden');
            uploadResult.classList.remove('hidden');
            
            if (data.success) {
                successMessage.classList.remove('hidden');
                errorMessage.classList.add('hidden');
                successText.textContent = data.message;
            } else {
                successMessage.classList.add('hidden');
                errorMessage.classList.remove('hidden');
                errorText.textContent = data.message;
            }
        })
        .catch(error => {
            uploadStatus.classList.add('hidden');
            uploadResult.classList.remove('hidden');
            successMessage.classList.add('hidden');
            errorMessage.classList.remove('hidden');
            errorText.textContent = 'Có lỗi xảy ra khi upload: ' + error.message;
        })
        .finally(() => {
            // Re-enable button
            uploadBtn.disabled = false;
            uploadBtn.innerHTML = '<i class="fas fa-upload mr-2"></i>Upload lên TikTok';
        });
    });

    function closeModalFunction() {
        modal.classList.add('hidden');
        uploadStatus.classList.remove('hidden');
        uploadResult.classList.add('hidden');
        successMessage.classList.add('hidden');
        errorMessage.classList.add('hidden');
    }

    closeModal.addEventListener('click', closeModalFunction);
    closeModalBtn.addEventListener('click', closeModalFunction);
    
    // Close modal when clicking outside
    modal.addEventListener('click', function(e) {
        if (e.target === modal) {
            closeModalFunction();
        }
    });
});
</script>
@endsection
