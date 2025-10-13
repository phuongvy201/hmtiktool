@props(['name' => 'images', 'multiple' => true, 'maxFiles' => 10, 'existingImages' => [], 'label' => 'Hình ảnh'])

<div class="image-upload-manager" data-name="{{ $name }}" data-multiple="{{ $multiple ? 'true' : 'false' }}" data-max-files="{{ $maxFiles }}">
    <label class="block text-sm font-medium text-gray-300 mb-2">{{ $label }}</label>
    
    <!-- File Upload Section -->
    <div class="upload-tab-content" data-tab="file">
        <div class="mb-4">
            <!-- File Drop Zone -->
            <div class="file-drop-zone border-2 border-dashed border-gray-600 rounded-lg p-6 text-center hover:border-gray-500 transition-colors cursor-pointer" 
                 onclick="document.getElementById('{{ $name }}_file_input').click()">
                <svg class="w-12 h-12 mx-auto mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                </svg>
                <p class="text-gray-300 mb-2">Kéo thả file vào đây hoặc click để chọn</p>
                <p class="text-gray-400 text-sm">Hỗ trợ: JPG, PNG, GIF, WebP (tối đa {{ $maxFiles }} file)</p>
            </div>
            
            <input type="file" 
                   id="{{ $name }}_file_input" 
                   class="hidden" 
                   accept="image/*" 
                   {{ $multiple ? 'multiple' : '' }}>
            
            <!-- Upload Progress -->
            <div id="{{ $name }}_upload_progress" class="hidden mt-4">
                <div class="bg-gray-700 rounded-lg p-4">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-white text-sm">Đang upload...</span>
                        <span id="{{ $name }}_progress_text" class="text-blue-400 text-sm">0%</span>
                    </div>
                    <div class="w-full bg-gray-600 rounded-full h-2">
                        <div id="{{ $name }}_progress_bar" class="bg-blue-500 h-2 rounded-full transition-all duration-300" style="width: 0%"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Image Preview Grid -->
    <div class="image-preview-grid grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 mt-4" id="{{ $name }}_preview_grid">
        <!-- Existing images -->
        @foreach($existingImages as $index => $image)
            <div class="image-preview-item relative group">
                <img src="{{ $image }}" alt="Image {{ $index + 1 }}" class="w-full h-32 object-cover rounded-lg border border-gray-600">
                <button type="button" class="absolute top-2 right-2 bg-red-600 hover:bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity" 
                        onclick="removeImage('{{ $name }}', this)">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
                <input type="hidden" name="{{ $name }}[]" value="{{ $image }}">
            </div>
        @endforeach
    </div>
    
    <!-- No Images Message -->
    <div id="{{ $name }}_no_images" class="text-center py-8 text-gray-400 {{ count($existingImages) > 0 ? 'hidden' : '' }}">
        <svg class="w-16 h-16 mx-auto mb-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
        </svg>
        <p>Chưa có hình ảnh nào</p>
        <p class="text-sm">Kéo thả file hoặc click vào vùng upload để thêm hình ảnh</p>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // File input change handler
    document.querySelectorAll('[id$="_file_input"]').forEach(input => {
        input.addEventListener('change', function() {
            const name = this.id.replace('_file_input', '');
            handleFileUpload(name, this.files);
        });
    });
    
    // Drag and drop functionality
    document.querySelectorAll('.file-drop-zone').forEach(zone => {
        zone.addEventListener('dragover', function(e) {
            e.preventDefault();
            this.classList.add('border-blue-500', 'bg-blue-900', 'bg-opacity-10');
        });
        
        zone.addEventListener('dragleave', function(e) {
            e.preventDefault();
            this.classList.remove('border-blue-500', 'bg-blue-900', 'bg-opacity-10');
        });
        
        zone.addEventListener('drop', function(e) {
            e.preventDefault();
            this.classList.remove('border-blue-500', 'bg-blue-900', 'bg-opacity-10');
            
            const name = this.closest('.image-upload-manager').dataset.name;
            const files = Array.from(e.dataTransfer.files).filter(file => file.type.startsWith('image/'));
            
            if (files.length > 0) {
                handleFileUpload(name, files);
            }
        });
    });
});

// Handle file selection (store temporarily, not upload yet)
function handleFileUpload(name, files) {
    const maxFiles = parseInt(document.querySelector(`[data-name="${name}"]`).dataset.maxFiles);
    const currentImages = document.querySelectorAll(`#${name}_preview_grid .image-preview-item`).length;
    
    if (currentImages + files.length > maxFiles) {
        showNotification(`Chỉ có thể thêm tối đa ${maxFiles} hình ảnh!`, 'error');
        return;
    }
    
    // Validate file types and sizes
    const validFiles = [];
    const invalidFiles = [];
    
    Array.from(files).forEach(file => {
        // Check file type
        if (!file.type.startsWith('image/')) {
            invalidFiles.push(`${file.name}: Không phải file hình ảnh`);
            return;
        }
        
        // Check file size (max 10MB)
        if (file.size > 10 * 1024 * 1024) {
            invalidFiles.push(`${file.name}: Kích thước quá lớn (>10MB)`);
            return;
        }
        
        validFiles.push(file);
    });
    
    if (invalidFiles.length > 0) {
        showNotification(`File không hợp lệ: ${invalidFiles.join(', ')}`, 'error');
    }
    
    if (validFiles.length > 0) {
        // Store files temporarily and create previews
        validFiles.forEach(file => {
            addFileToPreview(name, file);
        });
        
        showNotification(`Đã chọn ${validFiles.length} hình ảnh. Sẽ upload khi submit form.`, 'success');
    }
}

// Add file to preview (with temporary object URL)
function addFileToPreview(name, file) {
    const previewGrid = document.getElementById(`${name}_preview_grid`);
    const noImagesMsg = document.getElementById(`${name}_no_images`);
    
    // Create temporary URL for preview
    const tempUrl = URL.createObjectURL(file);
    
    const imageDiv = document.createElement('div');
    imageDiv.className = 'image-preview-item relative group';
    imageDiv.innerHTML = `
        <img src="${tempUrl}" alt="Selected image" class="w-full h-32 object-cover rounded-lg border border-gray-600">
        <div class="absolute top-2 left-2 bg-blue-600 text-white text-xs px-2 py-1 rounded">
            Sẽ upload
        </div>
        <button type="button" class="absolute top-2 right-2 bg-red-600 hover:bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity" 
                onclick="removeImage('${name}', this)">
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>
    `;
    
    // Create a real file input and assign the file to it
    const fileInput = document.createElement('input');
    fileInput.type = 'file';
    fileInput.name = 'image_files[]';
    fileInput.style.display = 'none';
    fileInput.setAttribute('data-temp-url', tempUrl);
    
    // Use DataTransfer to assign the file to the input
    const dt = new DataTransfer();
    dt.items.add(file);
    fileInput.files = dt.files;
    
    imageDiv.appendChild(fileInput);
    previewGrid.appendChild(imageDiv);
    noImagesMsg.classList.add('hidden');
    
    console.log('Added file to preview:', {
        name: file.name,
        size: file.size,
        type: file.type,
        inputName: fileInput.name,
        inputFiles: fileInput.files.length
    });
}

// Add image to preview grid
function addImageToPreview(name, url) {
    const previewGrid = document.getElementById(`${name}_preview_grid`);
    const noImagesMsg = document.getElementById(`${name}_no_images`);
    
    const imageDiv = document.createElement('div');
    imageDiv.className = 'image-preview-item relative group';
    imageDiv.innerHTML = `
        <img src="${url}" alt="Uploaded image" class="w-full h-32 object-cover rounded-lg border border-gray-600" 
             onerror="this.parentElement.remove(); showNotification('Không thể tải hình ảnh: ${url}', 'error');">
        <button type="button" class="absolute top-2 right-2 bg-red-600 hover:bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity" 
                onclick="removeImage('${name}', this)">
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>
        <input type="hidden" name="${name}[]" value="${url}">
    `;
    
    previewGrid.appendChild(imageDiv);
    noImagesMsg.classList.add('hidden');
}

// Remove image from preview
function removeImage(name, button) {
    const imageItem = button.closest('.image-preview-item');
    
    // Clean up temporary URL if exists
    const fileInput = imageItem.querySelector('input[type="file"]');
    if (fileInput && fileInput.dataset.tempUrl) {
        URL.revokeObjectURL(fileInput.dataset.tempUrl);
    }
    
    imageItem.remove();
    
    // Show no images message if no images left
    const remainingImages = document.querySelectorAll(`#${name}_preview_grid .image-preview-item`);
    if (remainingImages.length === 0) {
        document.getElementById(`${name}_no_images`).classList.remove('hidden');
    }
    
    showNotification('Đã xóa hình ảnh!', 'info');
}

// Show notification function (reuse from main form)
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 z-50 px-6 py-3 rounded-lg text-white font-medium transition-all duration-300 transform translate-x-full`;
    
    if (type === 'success') {
        notification.className += ' bg-green-600';
    } else if (type === 'error') {
        notification.className += ' bg-red-600';
    } else {
        notification.className += ' bg-blue-600';
    }
    
    notification.textContent = message;
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.classList.remove('translate-x-full');
    }, 100);
    
    setTimeout(() => {
        notification.classList.add('translate-x-full');
        setTimeout(() => {
            if (document.body.contains(notification)) {
                document.body.removeChild(notification);
            }
        }, 300);
    }, 3000);
}
</script>
@endpush
