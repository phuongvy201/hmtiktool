@extends('layouts.app')

@section('title', 'Edit Product')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-3xl font-bold text-white">Edit Product</h1>
                <p class="text-gray-400 mt-2">Team: {{ $team->name }}</p>
            </div>
            <a href="{{ route('products.index') }}" 
               class="bg-gray-700 hover:bg-gray-600 text-white font-medium py-2 px-4 rounded-lg transition duration-200">
                <i class="fas fa-arrow-left mr-2"></i>Back
            </a>
        </div>

        <div class="bg-gray-800 rounded-lg shadow-sm border border-gray-700">
            <form method="POST" action="{{ route('products.update', $product) }}" enctype="multipart/form-data" class="p-6">
                @csrf
                @method('PUT')
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Basic Information -->
                    <div class="space-y-6">
                        <div>
                            <label for="title" class="block text-sm font-medium text-gray-300 mb-2">
                                Product Name <span class="text-red-400">*</span>
                            </label>
                            <input type="text" id="title" name="title" value="{{ old('title', $product->title) }}" required
                                   class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-white placeholder-gray-400 @error('title') border-red-500 @enderror"
                                   placeholder="Enter product name">
                            @error('title')
                                <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="sku" class="block text-sm font-medium text-gray-300 mb-2">
                                SKU
                            </label>
                            <input type="text" id="sku" name="sku" value="{{ old('sku', $product->sku) }}" required
                                   class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-white placeholder-gray-400 @error('sku') border-red-500 @enderror"
                                   placeholder="Enter product SKU code">
                            <p class="mt-1 text-sm text-gray-500">SKU can be duplicated between products</p>
                            @error('sku')
                                <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="price" class="block text-sm font-medium text-gray-300 mb-2">
                                Product Price <span class="text-red-400">*</span>
                            </label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400">$</span>
                                <input type="number" id="price" name="price" value="{{ old('price', $product->price) }}" step="0.01" min="0" required
                                       class="w-full pl-8 pr-3 py-2 bg-gray-700 border border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-white placeholder-gray-400 @error('price') border-red-500 @enderror"
                                       placeholder="0.00">
                            </div>
                            <p class="mt-1 text-sm text-gray-400">This price will be added to the template price</p>
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
                                <option value="">Select template</option>
                                @foreach($templates as $template)
                                    <option value="{{ $template->id }}" {{ old('product_template_id', $product->product_template_id) == $template->id ? 'selected' : '' }}>
                                        {{ $template->name }} (Price: ${{ number_format($template->base_price, 2) }})
                                    </option>
                                @endforeach
                            </select>
                            @error('product_template_id')
                                <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-300 mb-2">
                                Status <span class="text-red-400">*</span>
                            </label>
                            <select id="status" name="status" required
                                    class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-white @error('status') border-red-500 @enderror">
                                <option value="active" {{ old('status', $product->status) == 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ old('status', $product->status) == 'inactive' ? 'selected' : '' }}>Inactive</option>
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
                                Product Images <span class="text-gray-500">(can select multiple images)</span>
                            </label>
                            
                            <!-- Hiển thị ảnh hiện tại -->
                            @if($product->images->where('source', 'product')->count() > 0)
                                <div class="mb-4">
                                    <p class="text-sm text-gray-400 mb-2">Current Images:</p>
                                    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                                        @foreach($product->images->where('source', 'product') as $image)
                                            <div class="relative group">
                                                <div class="relative aspect-square bg-gray-600 rounded-lg overflow-hidden">
                                                    <img src="{{ $image->url }}" 
                                                         alt="Product Image" 
                                                         class="w-full h-full object-cover">
                                                    <div class="absolute top-2 left-2 bg-blue-500 text-white text-xs px-2 py-1 rounded">
                                                        {{ $image->is_primary ? 'Primary Image' : 'Image ' . ($loop->iteration) }}
                                                    </div>
                                                </div>
                                                <p class="text-xs text-gray-400 mt-1 truncate">{{ $image->file_name }}</p>
                                            </div>
                                        @endforeach
                                    </div>
                                    <p class="text-sm text-gray-500 mt-2">Select new images to replace all current images</p>
                                </div>
                            @endif
                            
                            <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-600 border-dashed rounded-md bg-gray-700">
                                <div class="space-y-1 text-center">
                                    <i class="fas fa-cloud-upload-alt text-3xl text-gray-500"></i>
                                    <div class="flex text-sm text-gray-400">
                                        <label for="product_images" class="relative cursor-pointer bg-gray-700 rounded-md font-medium text-blue-400 hover:text-blue-300 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-blue-500">
                                            <span>Upload Images</span>
                                            <input id="product_images" name="product_images[]" type="file" class="sr-only" accept="image/*" multiple>
                                        </label>
                                        <p class="pl-1">or drag and drop</p>
                                    </div>
                                    <p class="text-xs text-gray-500">PNG, JPG, GIF max 2MB per image</p>
                                </div>
                            </div>
                            
                            <!-- Preview ảnh đã chọn -->
                            <div id="image-preview" class="mt-4 grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 hidden">
                                <!-- Ảnh preview sẽ được thêm vào đây bằng JavaScript -->
                            </div>
                            <p class="text-xs text-gray-500 mt-2 hidden" id="drag-hint">
                                <i class="fas fa-info-circle mr-1"></i>Kéo thả hình ảnh để sắp xếp lại thứ tự
                            </p>
                            
                            @error('product_images.*')
                                <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-300 mb-2">
                                Product Description
                            </label>
                            <textarea id="description" name="description" rows="6"
                                      class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-white placeholder-gray-400 @error('description') border-red-500 @enderror"
                                      placeholder="Enter detailed product description...">{{ old('description', $product->description) }}</textarea>
                            @error('description')
                                <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Price Preview -->
                        <div class="bg-gray-700 rounded-lg p-4 border border-gray-600">
                            <h3 class="text-sm font-medium text-gray-300 mb-2">Price Preview</h3>
                            <div class="space-y-2 text-sm">
                                <div class="flex justify-between">
                                    <span class="text-gray-400">Product Price:</span>
                                    <span class="font-medium text-white" id="product-price">${{ number_format($product->price, 2) }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-400">Template Price:</span>
                                    <span class="font-medium text-white" id="template-price">$0.00</span>
                                </div>
                                <hr class="border-gray-600">
                                <div class="flex justify-between">
                                    <span class="text-white font-medium">Total Price:</span>
                                    <span class="text-lg font-bold text-blue-400" id="total-price">${{ number_format($product->total_price, 2) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end space-x-4 mt-8 pt-6 border-t border-gray-700">
                    <a href="{{ route('products.index') }}" 
                       class="bg-gray-700 hover:bg-gray-600 text-white font-medium py-2 px-6 rounded-lg transition duration-200">
                        Cancel
                    </a>
                    <button type="submit" 
                            class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-6 rounded-lg transition duration-200">
                        <i class="fas fa-save mr-2"></i>Update Product
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('styles')
<style>
    .image-preview-item {
        transition: transform 0.2s ease, opacity 0.2s ease;
    }
    
    .image-preview-item.dragging {
        opacity: 0.5;
        transform: scale(0.95);
    }
    
    .image-preview-item.drag-over {
        border-color: #3b82f6 !important;
        transform: scale(1.05);
    }
    
    .image-preview-item:hover {
        transform: translateY(-2px);
    }
</style>
@endpush

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
    const dragHint = document.getElementById('drag-hint');
    const dropZone = document.querySelector('.border-dashed');
    
    // Store files and their preview data
    let imageFiles = [];
    let imagePreviews = [];

    // Handle file selection
    fileInput.addEventListener('change', function(e) {
        handleFiles(e.target.files);
    });

    // Handle drag and drop from outside
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

        // Filter only image files
        const imageFilesArray = Array.from(files).filter(file => file.type.startsWith('image/'));
        if (imageFilesArray.length === 0) return;

        // Add new files to existing array
        imageFiles = [...imageFiles, ...imageFilesArray];
        
        // Render previews
        renderPreviews();
        updateFileInput();
    }

    function renderPreviews() {
        // Clear existing preview
        imagePreview.innerHTML = '';
        
        if (imageFiles.length === 0) {
            imagePreview.classList.add('hidden');
            dragHint.classList.add('hidden');
            return;
        }

        imagePreview.classList.remove('hidden');
        dragHint.classList.remove('hidden');

        // Read all files and create previews
        imagePreviews = [];
        let loadedCount = 0;

        imageFiles.forEach((file, index) => {
            const reader = new FileReader();
            reader.onload = function(e) {
                imagePreviews[index] = {
                    file: file,
                    preview: e.target.result,
                    index: index
                };
                loadedCount++;

                // When all files are loaded, render them
                if (loadedCount === imageFiles.length) {
                    renderPreviewItems();
                }
            };
            reader.readAsDataURL(file);
        });
    }

    function renderPreviewItems() {
        imagePreview.innerHTML = '';
        
        imagePreviews.forEach((item, index) => {
            const previewItem = document.createElement('div');
            previewItem.className = 'relative group image-preview-item cursor-move';
            previewItem.draggable = true;
            previewItem.dataset.index = index;
            
            previewItem.innerHTML = `
                <div class="relative aspect-square bg-gray-600 rounded-lg overflow-hidden border-2 border-transparent hover:border-blue-500 transition-all duration-200">
                    <img src="${item.preview}" alt="Preview" class="w-full h-full object-cover pointer-events-none">
                    <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-50 transition-all duration-200 flex items-center justify-center">
                        <button type="button" class="remove-image-btn opacity-0 group-hover:opacity-100 bg-red-500 hover:bg-red-600 text-white rounded-full w-8 h-8 flex items-center justify-center transition-all duration-200" data-index="${index}">
                            <i class="fas fa-times text-sm"></i>
                        </button>
                    </div>
                    <div class="absolute top-2 left-2 bg-blue-500 text-white text-xs px-2 py-1 rounded">
                        ${index === 0 ? 'Primary Image' : 'Image ' + (index + 1)}
                    </div>
                    <div class="absolute top-2 right-2 bg-gray-800 bg-opacity-75 text-white text-xs px-2 py-1 rounded">
                        <i class="fas fa-grip-vertical"></i>
                    </div>
                </div>
                <p class="text-xs text-gray-400 mt-1 truncate">${item.file.name}</p>
            `;
            
            imagePreview.appendChild(previewItem);
        });

        // Attach drag and drop event listeners
        attachDragAndDropListeners();
    }

    function attachDragAndDropListeners() {
        const items = imagePreview.querySelectorAll('.image-preview-item');
        
        items.forEach(item => {
            item.addEventListener('dragstart', handleDragStart);
            item.addEventListener('dragover', handleDragOver);
            item.addEventListener('drop', handleDrop);
            item.addEventListener('dragend', handleDragEnd);
            item.addEventListener('dragenter', handleDragEnter);
            item.addEventListener('dragleave', handleDragLeave);
        });
    }

    let draggedElement = null;
    let draggedIndex = null;

    function handleDragStart(e) {
        draggedElement = this;
        draggedIndex = parseInt(this.dataset.index);
        this.classList.add('opacity-50', 'scale-95');
        e.dataTransfer.effectAllowed = 'move';
        e.dataTransfer.setData('text/html', this.innerHTML);
    }

    function handleDragOver(e) {
        if (e.preventDefault) {
            e.preventDefault();
        }
        e.dataTransfer.dropEffect = 'move';
        return false;
    }

    function handleDragEnter(e) {
        if (this !== draggedElement) {
            this.classList.add('border-blue-400', 'scale-105');
        }
    }

    function handleDragLeave(e) {
        this.classList.remove('border-blue-400', 'scale-105');
    }

    function handleDrop(e) {
        if (e.stopPropagation) {
            e.stopPropagation();
        }

        if (draggedElement !== this) {
            const dropIndex = parseInt(this.dataset.index);
            
            // Reorder arrays
            const draggedPreview = imagePreviews[draggedIndex];
            const draggedFile = imageFiles[draggedIndex];
            
            // Remove from old position
            imagePreviews.splice(draggedIndex, 1);
            imageFiles.splice(draggedIndex, 1);
            
            // Insert at new position
            imagePreviews.splice(dropIndex, 0, draggedPreview);
            imageFiles.splice(dropIndex, 0, draggedFile);
            
            // Update indices
            imagePreviews.forEach((item, index) => {
                item.index = index;
            });
            
            // Re-render
            renderPreviewItems();
            updateFileInput();
        }

        return false;
    }

    function handleDragEnd(e) {
        const items = imagePreview.querySelectorAll('.image-preview-item');
        items.forEach(item => {
            item.classList.remove('opacity-50', 'scale-95', 'border-blue-400', 'scale-105');
        });
    }

    function updateFileInput() {
        // Update file input with reordered files
        const dataTransfer = new DataTransfer();
        imageFiles.forEach(file => dataTransfer.items.add(file));
        fileInput.files = dataTransfer.files;
    }

    // Handle image removal
    imagePreview.addEventListener('click', function(e) {
        if (e.target.closest('.remove-image-btn')) {
            const index = parseInt(e.target.closest('.remove-image-btn').dataset.index);
            
            // Remove from arrays
            imageFiles.splice(index, 1);
            imagePreviews.splice(index, 1);
            
            // Update indices
            imagePreviews.forEach((item, idx) => {
                item.index = idx;
            });
            
            // Re-render
            renderPreviewItems();
            updateFileInput();
        }
    });
});
</script>
@endpush
@endsection

