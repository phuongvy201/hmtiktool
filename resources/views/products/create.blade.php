@extends('layouts.app')

@section('title', 'Tạo Sản phẩm Mới')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-3xl font-bold text-white">Tạo Sản phẩm Mới</h1>
                <p class="text-gray-400 mt-2">Team: {{ $team->name }}</p>
            </div>
            <a href="{{ route('products.index') }}" 
               class="bg-gray-700 hover:bg-gray-600 text-white font-medium py-2 px-4 rounded-lg transition duration-200">
                <i class="fas fa-arrow-left mr-2"></i>Quay lại
            </a>
        </div>

        <div class="bg-gray-800 rounded-lg shadow-sm border border-gray-700">
            <form method="POST" action="{{ route('products.store') }}" enctype="multipart/form-data" class="p-6">
                @csrf
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Basic Information -->
                    <div class="space-y-6">
                        <div>
                            <label for="title" class="block text-sm font-medium text-gray-300 mb-2">
                                Tên sản phẩm <span class="text-red-400">*</span>
                            </label>
                            <input type="text" id="title" name="title" value="{{ old('title') }}" required
                                   class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-white placeholder-gray-400 @error('title') border-red-500 @enderror"
                                   placeholder="Nhập tên sản phẩm">
                            @error('title')
                                <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                      

                        <div>
                            <label for="sku" class="block text-sm font-medium text-gray-300 mb-2">
                                SKU <span class="text-red-400">*</span>
                            </label>
                            <input type="text" id="sku" name="sku" value="{{ old('sku') }}" required
                                   class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-white placeholder-gray-400 @error('sku') border-red-500 @enderror"
                                   placeholder="Nhập mã SKU sản phẩm">
                            @error('sku')
                                <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="price" class="block text-sm font-medium text-gray-300 mb-2">
                                Giá sản phẩm <span class="text-red-400">*</span>
                            </label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400">$</span>
                                <input type="number" id="price" name="price" value="{{ old('price', 0) }}" step="0.01" min="0" required
                                       class="w-full pl-8 pr-3 py-2 bg-gray-700 border border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-white placeholder-gray-400 @error('price') border-red-500 @enderror"
                                       placeholder="0.00">
                            </div>
                            <p class="mt-1 text-sm text-gray-400">Giá này sẽ được cộng với giá template</p>
                            @error('price')
                                <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="product_template_id" class="block text-sm font-medium text-gray-300 mb-2">
                                Template <span class="text-red-400">*</span>
                            </label>
                            <select id="product_template_id" name="product_template_id" required
                                    class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-white @error('product_template_id') border-red-500 @enderror">
                                <option value="">Chọn template</option>
                                @foreach($templates as $template)
                                    <option value="{{ $template->id }}" {{ old('product_template_id') == $template->id ? 'selected' : '' }}>
                                        {{ $template->name }} (Giá: ${{ number_format($template->base_price, 2) }})
                                    </option>
                                @endforeach
                            </select>
                            @error('product_template_id')
                                <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-300 mb-2">
                                Trạng thái <span class="text-red-400">*</span>
                            </label>
                            <select id="status" name="status" required
                                    class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-white @error('status') border-red-500 @enderror">
                                <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>Hoạt động</option>
                                <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Không hoạt động</option>
                            </select>
                            @error('status')
                                <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Image and Description -->
                    <div class="space-y-6">
                        <div>
                            <label for="product_images" class="block text-sm font-medium text-gray-300 mb-2">
                                Ảnh sản phẩm <span class="text-gray-500">(có thể chọn nhiều ảnh)</span>
                            </label>
                            <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-600 border-dashed rounded-md bg-gray-700">
                                <div class="space-y-1 text-center">
                                    <i class="fas fa-cloud-upload-alt text-3xl text-gray-500"></i>
                                    <div class="flex text-sm text-gray-400">
                                        <label for="product_images" class="relative cursor-pointer bg-gray-700 rounded-md font-medium text-blue-400 hover:text-blue-300 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-blue-500">
                                            <span>Tải ảnh lên</span>
                                            <input id="product_images" name="product_images[]" type="file" class="sr-only" accept="image/*" multiple>
                                        </label>
                                        <p class="pl-1">hoặc kéo thả</p>
                                    </div>
                                    <p class="text-xs text-gray-500">PNG, JPG, GIF tối đa 2MB mỗi ảnh</p>
                                </div>
                            </div>
                            
                            <!-- Preview ảnh đã chọn -->
                            <div id="image-preview" class="mt-4 grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 hidden">
                                <!-- Ảnh preview sẽ được thêm vào đây bằng JavaScript -->
                            </div>
                            
                            @error('product_images.*')
                                <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-300 mb-2">
                                Mô tả sản phẩm
                            </label>
                            <textarea id="description" name="description" rows="6"
                                      class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-white placeholder-gray-400 @error('description') border-red-500 @enderror"
                                      placeholder="Nhập mô tả chi tiết về sản phẩm...">{{ old('description') }}</textarea>
                            @error('description')
                                <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Price Preview -->
                        <div class="bg-gray-700 rounded-lg p-4 border border-gray-600">
                            <h3 class="text-sm font-medium text-gray-300 mb-2">Xem trước giá</h3>
                            <div class="space-y-2 text-sm">
                                <div class="flex justify-between">
                                    <span class="text-gray-400">Giá sản phẩm:</span>
                                    <span class="font-medium text-white" id="product-price">$0.00</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-400">Giá template:</span>
                                    <span class="font-medium text-white" id="template-price">$0.00</span>
                                </div>
                                <hr class="border-gray-600">
                                <div class="flex justify-between">
                                    <span class="text-white font-medium">Tổng giá:</span>
                                    <span class="text-lg font-bold text-blue-400" id="total-price">$0.00</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end space-x-4 mt-8 pt-6 border-t border-gray-700">
                    <a href="{{ route('products.index') }}" 
                       class="bg-gray-700 hover:bg-gray-600 text-white font-medium py-2 px-6 rounded-lg transition duration-200">
                        Hủy
                    </a>
                    <button type="submit" 
                            class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-6 rounded-lg transition duration-200">
                        <i class="fas fa-save mr-2"></i>Tạo sản phẩm
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Price preview functionality
    const priceInput = document.getElementById('price');
    const templateSelect = document.getElementById('product_template_id');
    const productPriceSpan = document.getElementById('product-price');
    const templatePriceSpan = document.getElementById('template-price');
    const totalPriceSpan = document.getElementById('total-price');

    // Template data
    const templates = @json($templates->mapWithKeys(function($template) {
        return [$template->id => ['base_price' => is_numeric($template->base_price) ? (float) $template->base_price : 0]];
    }));

    function updatePricePreview() {
        const productPrice = parseFloat(priceInput.value) || 0;
        const templateId = parseInt(templateSelect.value);
        let templatePrice = 0;
        
        if (templateId && templates[templateId]) {
            templatePrice = parseFloat(templates[templateId].base_price) || 0;
        }
        
        const totalPrice = productPrice + templatePrice;

        productPriceSpan.textContent = '$' + productPrice.toFixed(2);
        templatePriceSpan.textContent = '$' + templatePrice.toFixed(2);
        totalPriceSpan.textContent = '$' + totalPrice.toFixed(2);
    }

    priceInput.addEventListener('input', updatePricePreview);
    templateSelect.addEventListener('change', updatePricePreview);

    // Initial calculation
    updatePricePreview();

    // Image upload and preview functionality
    const fileInput = document.getElementById('product_images');
    const imagePreview = document.getElementById('image-preview');
    const dropZone = document.querySelector('.border-dashed');

    // Handle file selection
    fileInput.addEventListener('change', function(e) {
        handleFiles(e.target.files);
    });

    // Handle drag and drop
    dropZone.addEventListener('dragover', function(e) {
        e.preventDefault();
        dropZone.classList.add('border-blue-400', 'bg-gray-600');
    });

    dropZone.addEventListener('dragleave', function(e) {
        e.preventDefault();
        dropZone.classList.remove('border-blue-400', 'bg-gray-600');
    });

    dropZone.addEventListener('drop', function(e) {
        e.preventDefault();
        dropZone.classList.remove('border-blue-400', 'bg-gray-600');
        
        const files = e.dataTransfer.files;
        handleFiles(files);
    });

    function handleFiles(files) {
        if (files.length === 0) return;

        // Clear existing preview
        imagePreview.innerHTML = '';
        imagePreview.classList.remove('hidden');

        Array.from(files).forEach((file, index) => {
            if (!file.type.startsWith('image/')) return;

            const reader = new FileReader();
            reader.onload = function(e) {
                const previewItem = document.createElement('div');
                previewItem.className = 'relative group';
                previewItem.innerHTML = `
                    <div class="relative aspect-square bg-gray-600 rounded-lg overflow-hidden">
                        <img src="${e.target.result}" alt="Preview" class="w-full h-full object-cover">
                        <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-50 transition-all duration-200 flex items-center justify-center">
                            <button type="button" class="remove-image-btn opacity-0 group-hover:opacity-100 bg-red-500 hover:bg-red-600 text-white rounded-full w-8 h-8 flex items-center justify-center transition-all duration-200" data-index="${index}">
                                <i class="fas fa-times text-sm"></i>
                            </button>
                        </div>
                        <div class="absolute top-2 left-2 bg-blue-500 text-white text-xs px-2 py-1 rounded">
                            ${index === 0 ? 'Ảnh chính' : 'Ảnh ' + (index + 1)}
                        </div>
                    </div>
                    <p class="text-xs text-gray-400 mt-1 truncate">${file.name}</p>
                `;
                imagePreview.appendChild(previewItem);
            };
            reader.readAsDataURL(file);
        });

        // Update file input
        const dataTransfer = new DataTransfer();
        Array.from(files).forEach(file => dataTransfer.items.add(file));
        fileInput.files = dataTransfer.files;
    }

    // Handle image removal
    imagePreview.addEventListener('click', function(e) {
        if (e.target.closest('.remove-image-btn')) {
            const index = parseInt(e.target.closest('.remove-image-btn').dataset.index);
            const files = Array.from(fileInput.files);
            files.splice(index, 1);
            
            const dataTransfer = new DataTransfer();
            files.forEach(file => dataTransfer.items.add(file));
            fileInput.files = dataTransfer.files;
            
            handleFiles(fileInput.files);
        }
    });
});
</script>
@endpush
@endsection

