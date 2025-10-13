@extends('layouts.app')

@section('title', $productTemplate->name)

@push('styles')
<style>
    .rich-content {
        white-space: pre-wrap;
        word-wrap: break-word;
        line-height: 1.6;
    }
    
    .rich-content strong {
        font-weight: 600;
        color: #fbbf24;
    }
    
    .rich-content em {
        font-style: italic;
        color: #a3bffa;
    }
    
    .rich-content ul, .rich-content ol {
        margin-left: 1.5rem;
        margin-top: 0.5rem;
        margin-bottom: 0.5rem;
    }
    
    .rich-content li {
        margin-bottom: 0.25rem;
    }
    
    .rich-content p {
        margin-bottom: 0.75rem;
    }
    
    .rich-content p:last-child {
        margin-bottom: 0;
    }
</style>
@endpush

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
                <div class="flex-1">
                    <h1 class="text-3xl font-bold text-white mb-2">{{ $productTemplate->name }}</h1>
                    <p class="text-gray-400">Chi tiết template sản phẩm</p>
                </div>
                <div class="flex space-x-3">
                    <a href="{{ route('product-templates.edit', $productTemplate) }}" 
                       class="px-4 py-2 bg-blue-600 hover:bg-blue-500 text-white rounded-lg font-medium transition-colors duration-200 flex items-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                        Chỉnh sửa
                    </a>
                </div>
            </div>
        </div>

        <div class="max-w-6xl mx-auto space-y-6">
            <!-- Basic Information -->
            <div class="bg-gray-800 rounded-xl border border-gray-700 p-6">
                <h3 class="text-lg font-semibold text-white mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                    </svg>
                    Thông tin cơ bản
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <p class="text-sm font-medium text-gray-400 mb-1">Tên sản phẩm</p>
                        <p class="text-lg text-white">{{ $productTemplate->name }}</p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-400 mb-1">Danh mục</p>
                        <p class="text-lg text-white">{{ $productTemplate->category_name ?? $productTemplate->category_id }}</p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-400 mb-1">Giá cơ bản</p>
                        <p class="text-lg font-semibold text-green-400">{{ $productTemplate->base_price }}</p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-400 mb-1">Giá niêm yết</p>
                        <p class="text-lg text-white">{{ $productTemplate->list_price ?? 'N/A' }}</p>
                    </div>
                    @if($productTemplate->description)
                        <div class="md:col-span-2">
                            <p class="text-sm font-medium text-gray-400 mb-2">Mô tả</p>
                            <div class="bg-gray-700 rounded-lg p-4 border border-gray-600">
                                <div class="rich-content text-white leading-relaxed">
                                    {!! \App\Helpers\TextHelper::formatDescription($productTemplate->description) !!}
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Dimensions -->
            @if($productTemplate->weight || $productTemplate->height || $productTemplate->width || $productTemplate->length)
            <div class="bg-gray-800 rounded-xl border border-gray-700 p-6">
                <h3 class="text-lg font-semibold text-white mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path>
                    </svg>
                    Kích thước & Trọng lượng
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                    @if($productTemplate->weight)
                        <div>
                            <p class="text-sm font-medium text-gray-400 mb-1">Trọng lượng</p>
                            <p class="text-lg text-white">{{ $productTemplate->weight }} kg</p>
                        </div>
                    @endif
                    @if($productTemplate->height)
                        <div>
                            <p class="text-sm font-medium text-gray-400 mb-1">Chiều cao</p>
                            <p class="text-lg text-white">{{ $productTemplate->height }} cm</p>
                        </div>
                    @endif
                    @if($productTemplate->width)
                        <div>
                            <p class="text-sm font-medium text-gray-400 mb-1">Chiều rộng</p>
                            <p class="text-lg text-white">{{ $productTemplate->width }} cm</p>
                        </div>
                    @endif
                    @if($productTemplate->length)
                        <div>
                            <p class="text-sm font-medium text-gray-400 mb-1">Chiều dài</p>
                            <p class="text-lg text-white">{{ $productTemplate->length }} cm</p>
                        </div>
                    @endif
                </div>
            </div>
            @endif

            <!-- Images & Media -->
            @if(($productTemplate->images && count($productTemplate->images) > 0) || $productTemplate->size_chart || $productTemplate->product_video)
            <div class="bg-gray-800 rounded-xl border border-gray-700 p-6">
                <h3 class="text-lg font-semibold text-white mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    Hình ảnh & Media
                </h3>
                
                <!-- Product Images -->
                @if($productTemplate->images && count($productTemplate->images) > 0)
                <div class="mb-6">
                    <h4 class="text-md font-medium text-white mb-3">Hình ảnh sản phẩm</h4>
                    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
                        @foreach($productTemplate->images as $image)
                            <div class="relative group cursor-pointer" onclick="openImageModal('{{ $image }}')">
                                <img src="{{ $image }}" alt="Product image" 
                                     class="w-full h-32 object-cover rounded-lg border border-gray-600 hover:border-blue-400 transition-colors">
                                <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-20 transition-all rounded-lg flex items-center justify-center">
                                    <svg class="w-8 h-8 text-white opacity-0 group-hover:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7"></path>
                                    </svg>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                @endif

                <!-- Size Chart & Video -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    @if($productTemplate->size_chart)
                    <div>
                        <h4 class="text-md font-medium text-white mb-3">Bảng size</h4>
                        <div class="relative group cursor-pointer" onclick="openImageModal('{{ $productTemplate->size_chart }}')">
                            <img src="{{ $productTemplate->size_chart }}" alt="Size chart" 
                                 class="w-full h-48 object-cover rounded-lg border border-gray-600 hover:border-blue-400 transition-colors">
                            <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-20 transition-all rounded-lg flex items-center justify-center">
                                <svg class="w-8 h-8 text-white opacity-0 group-hover:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7"></path>
                                </svg>
                            </div>
                        </div>
                    </div>
                    @endif

                    @if($productTemplate->product_video)
                    <div>
                        <h4 class="text-md font-medium text-white mb-3">Video sản phẩm</h4>
                        <div class="relative">
                            <video controls class="w-full h-48 rounded-lg border border-gray-600">
                                <source src="{{ $productTemplate->product_video }}" type="video/mp4">
                                Trình duyệt của bạn không hỗ trợ video.
                            </video>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
            @endif

            <!-- Category Attributes -->
            @if($productTemplate->categoryAttributes && $productTemplate->categoryAttributes->count() > 0)
            <div class="bg-gray-800 rounded-xl border border-gray-700 p-6">
                <h3 class="text-lg font-semibold text-white mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    Thuộc tính danh mục
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($productTemplate->categoryAttributes as $attribute)
                        <div class="border border-gray-600 rounded-lg p-4 bg-gray-700">
                            <div class="flex items-center justify-between mb-2">
                                <h4 class="font-medium text-white">{{ $attribute->attribute_name }}</h4>
                                @if($attribute->is_required)
                                    <span class="px-2 py-1 bg-red-600 text-red-100 text-xs rounded-full">Bắt buộc</span>
                                @else
                                    <span class="px-2 py-1 bg-gray-600 text-gray-100 text-xs rounded-full">Tùy chọn</span>
                                @endif
                            </div>
                            <div class="text-sm text-gray-300 mb-1">
                                <span class="font-medium">Loại:</span> 
                                @if($attribute->attribute_type === 'PRODUCT_PROPERTY')
                                    <span class="text-blue-400">Thuộc tính sản phẩm</span>
                                @elseif($attribute->attribute_type === 'SALES_PROPERTY')
                                    <span class="text-purple-400">Thuộc tính bán hàng</span>
                                @else
                                    <span class="text-gray-400">{{ $attribute->attribute_type }}</span>
                                @endif
                            </div>
                            <div class="text-sm text-gray-300">
                                <span class="font-medium">Giá trị:</span> 
                                @if($attribute->value_name)
                                    <span class="text-gray-400">{{ $attribute->value_name }}</span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Product Options -->
            @if($productTemplate->options->count() > 0)
            <div class="bg-gray-800 rounded-xl border border-gray-700 p-6">
                <h3 class="text-lg font-semibold text-white mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-pink-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                    </svg>
                    Tùy chọn sản phẩm
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($productTemplate->options as $option)
                        <div class="border border-gray-600 rounded-lg p-4 bg-gray-700">
                            <h4 class="font-medium text-white mb-3">{{ $option->name }}</h4>
                            <div class="flex flex-wrap gap-2">
                                @foreach($option->values as $value)
                                    <span class="px-3 py-1 bg-blue-600 text-blue-100 text-sm rounded-full">{{ $value->value }}</span>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Variants Display -->
            @if($productTemplate->variants->count() > 0)
            <div class="bg-gray-800 rounded-xl border border-gray-700 p-6">
                <h3 class="text-lg font-semibold text-white mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                    </svg>
                    Variants ({{ $productTemplate->variants->count() }})
                </h3>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-700">
                        <thead class="bg-gray-700">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Hình ảnh</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">SKU</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Combination</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Giá</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Giá niêm yết</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Số lượng</th>
                            </tr>
                        </thead>
                        <tbody class="bg-gray-800 divide-y divide-gray-700">
                            @foreach($productTemplate->variants as $variant)
                                <tr class="hover:bg-gray-700 transition-colors duration-200">
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
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-green-400 font-medium">
                                        {{ $variant->price }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-white">
                                        {{ $variant->list_price ?? 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-white">
                                        {{ $variant->stock_quantity }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

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

@push('scripts')
<script>
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
</script>
@endpush

@push('styles')
<style>
    /* Custom styles for rich text content */
    .rich-content {
        line-height: 1.6;
    }
    
    .rich-content h1, .rich-content h2, .rich-content h3 {
        color: #f9fafb;
        font-weight: 600;
        margin: 1rem 0 0.5rem 0;
    }
    
    .rich-content h1 { font-size: 1.5rem; }
    .rich-content h2 { font-size: 1.25rem; }
    .rich-content h3 { font-size: 1.125rem; }
    
    .rich-content p {
        color: #e5e7eb;
        margin: 0.5rem 0;
    }
    
    .rich-content strong {
        color: #f9fafb;
        font-weight: 600;
    }
    
    .rich-content em {
        font-style: italic;
    }
    
    .rich-content u {
        text-decoration: underline;
    }
    
    .rich-content s {
        text-decoration: line-through;
    }
    
    .rich-content ul, .rich-content ol {
        margin: 0.5rem 0;
        padding-left: 1.5rem;
        color: #e5e7eb;
    }
    
    .rich-content ul {
        list-style-type: disc;
    }
    
    .rich-content ol {
        list-style-type: decimal;
    }
    
    .rich-content li {
        margin: 0.25rem 0;
    }
    
    .rich-content blockquote {
        border-left: 4px solid #3b82f6;
        padding-left: 1rem;
        margin: 1rem 0;
        font-style: italic;
        color: #d1d5db;
        background-color: #374151;
        padding: 1rem;
        border-radius: 0.375rem;
    }
    
    .rich-content code {
        background-color: #1f2937;
        color: #fbbf24;
        padding: 0.125rem 0.25rem;
        border-radius: 0.25rem;
        font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', monospace;
        font-size: 0.875rem;
    }
    
    .rich-content pre {
        background-color: #1f2937;
        color: #e5e7eb;
        padding: 1rem;
        border-radius: 0.5rem;
        overflow-x: auto;
        margin: 1rem 0;
        border: 1px solid #374151;
    }
    
    .rich-content pre code {
        background: none;
        padding: 0;
        color: inherit;
    }
    
    .rich-content a {
        color: #60a5fa;
        text-decoration: underline;
        transition: color 0.2s;
    }
    
    .rich-content a:hover {
        color: #93c5fd;
    }
    
    .rich-content .ql-align-center {
        text-align: center;
    }
    
    .rich-content .ql-align-right {
        text-align: right;
    }
    
    .rich-content .ql-align-justify {
        text-align: justify;
    }
    
    /* Color classes from Quill */
    .rich-content .ql-color-white { color: #ffffff !important; }
    .rich-content .ql-color-red { color: #ef4444 !important; }
    .rich-content .ql-color-orange { color: #f97316 !important; }
    .rich-content .ql-color-yellow { color: #eab308 !important; }
    .rich-content .ql-color-green { color: #22c55e !important; }
    .rich-content .ql-color-blue { color: #3b82f6 !important; }
    .rich-content .ql-color-purple { color: #a855f7 !important; }
    
    .rich-content .ql-bg-black { background-color: #000000 !important; }
    .rich-content .ql-bg-red { background-color: #ef4444 !important; }
    .rich-content .ql-bg-orange { background-color: #f97316 !important; }
    .rich-content .ql-bg-yellow { background-color: #eab308 !important; }
    .rich-content .ql-bg-green { background-color: #22c55e !important; }
    .rich-content .ql-bg-blue { background-color: #3b82f6 !important; }
    .rich-content .ql-bg-purple { background-color: #a855f7 !important; }
</style>
@endpush
