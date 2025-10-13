@props([
    'name' => 'images',
    'multiple' => true,
    'maxFiles' => 10,
    'accept' => 'image/*',
    'folder' => 'product-images',
    'existingImages' => [],
    'label' => 'Hình ảnh sản phẩm'
])

<div class="image-uploader-component" data-name="{{ $name }}" data-multiple="{{ $multiple ? 'true' : 'false' }}" data-max-files="{{ $maxFiles }}" data-folder="{{ $folder }}">
    <label class="block text-sm font-medium text-gray-300 mb-2">{{ $label }}</label>
    
    <!-- URL Input Section -->
    <div class="mb-4">
        <div class="flex gap-2">
            <input type="text" 
                   class="url-input flex-1 bg-gray-700 border border-gray-600 rounded-lg px-4 py-2 text-white placeholder-gray-400 focus:outline-none focus:border-blue-500" 
                   placeholder="Dán URL hình ảnh hoặc nhiều URL (mỗi URL một dòng)">
            <button type="button" class="add-url-btn px-4 py-2 bg-blue-600 hover:bg-blue-500 text-white rounded-lg transition-colors">
                <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                Thêm URL
            </button>
        </div>
        <button type="button" class="format-urls-btn mt-2 px-3 py-1 bg-purple-600 hover:bg-purple-500 text-white text-sm rounded transition-colors">
            Format URLs
        </button>
    </div>

    <!-- File Upload Section -->
    <div class="upload-area border-2 border-dashed border-gray-600 rounded-lg p-6 text-center bg-gray-700 hover:bg-gray-650 transition-colors cursor-pointer">
        <input type="file" 
               class="file-input hidden" 
               {{ $multiple ? 'multiple' : '' }} 
               accept="{{ $accept }}">
        
        <div class="upload-content">
            <svg class="w-12 h-12 mx-auto mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
            </svg>
            <p class="text-gray-300 mb-2">Kéo thả file vào đây hoặc click để chọn</p>
            <p class="text-gray-400 text-sm">Hỗ trợ: JPG, PNG, GIF, WebP (tối đa 5MB mỗi file)</p>
            @if($multiple)
                <p class="text-gray-400 text-sm">Tối đa {{ $maxFiles }} file</p>
            @endif
        </div>

        <div class="upload-progress hidden">
            <div class="progress-bar bg-gray-600 rounded-full h-2 mb-2">
                <div class="progress-fill bg-blue-500 h-2 rounded-full transition-all duration-300" style="width: 0%"></div>
            </div>
            <p class="text-gray-300 text-sm">Đang upload...</p>
        </div>
    </div>

    <!-- Image Preview Section -->
    <div class="image-preview-container mt-4">
        <div class="images-grid grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
            <!-- Existing images will be loaded here -->
        </div>
    </div>

    <!-- Hidden inputs for form submission -->
    <div class="hidden-inputs">
        @if($multiple)
            @foreach($existingImages as $index => $image)
                <input type="hidden" name="{{ $name }}[{{ $index }}]" value="{{ $image }}">
            @endforeach
        @else
            <input type="hidden" name="{{ $name }}" value="{{ $existingImages[0] ?? '' }}">
        @endif
    </div>

    <!-- Error Messages -->
    <div class="error-messages mt-2 hidden">
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded text-sm">
            <ul class="error-list"></ul>
        </div>
    </div>
</div>

@push('styles')
<style>
.image-uploader-component .upload-area.dragover {
    border-color: #3b82f6;
    background-color: #1e40af;
}

.image-preview-item {
    position: relative;
    aspect-ratio: 1;
    border-radius: 0.5rem;
    overflow: hidden;
    border: 2px solid #374151;
    background: #1f2937;
}

.image-preview-item img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.image-preview-item .remove-btn {
    position: absolute;
    top: 4px;
    right: 4px;
    background: rgba(239, 68, 68, 0.9);
    color: white;
    border: none;
    border-radius: 50%;
    width: 24px;
    height: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: background-color 0.2s;
}

.image-preview-item .remove-btn:hover {
    background: rgba(239, 68, 68, 1);
}

.image-preview-item .loading-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.7);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
}

.image-preview-item.loading {
    opacity: 0.7;
}
</style>
@endpush

@push('scripts')
<script>
class ImageUploader {
    constructor(container) {
        this.container = container;
        this.name = container.dataset.name;
        this.multiple = container.dataset.multiple === 'true';
        this.maxFiles = parseInt(container.dataset.maxFiles);
        this.folder = container.dataset.folder;
        
        this.uploadArea = container.querySelector('.upload-area');
        this.fileInput = container.querySelector('.file-input');
        this.urlInput = container.querySelector('.url-input');
        this.addUrlBtn = container.querySelector('.add-url-btn');
        this.formatUrlsBtn = container.querySelector('.format-urls-btn');
        this.imagesGrid = container.querySelector('.images-grid');
        this.hiddenInputs = container.querySelector('.hidden-inputs');
        this.errorMessages = container.querySelector('.error-messages');
        
        this.images = [];
        this.config = null;
        
        this.init();
    }
    
    async init() {
        // Load configuration
        await this.loadConfig();
        
        // Load existing images
        this.loadExistingImages();
        
        // Setup event listeners
        this.setupEventListeners();
    }
    
    async loadConfig() {
        try {
            const response = await fetch('/api/images/config');
            const data = await response.json();
            if (data.success) {
                this.config = data.data;
            }
        } catch (error) {
            console.error('Failed to load upload config:', error);
        }
    }
    
    loadExistingImages() {
        const existingInputs = this.hiddenInputs.querySelectorAll('input[type="hidden"]');
        existingInputs.forEach(input => {
            if (input.value) {
                this.images.push({
                    url: input.value,
                    uploaded: true,
                    type: 'url'
                });
            }
        });
        this.renderImages();
    }
    
    setupEventListeners() {
        // File input
        this.fileInput.addEventListener('change', (e) => {
            this.handleFileSelect(e.target.files);
        });
        
        // Upload area click
        this.uploadArea.addEventListener('click', () => {
            this.fileInput.click();
        });
        
        // Drag and drop
        this.uploadArea.addEventListener('dragover', (e) => {
            e.preventDefault();
            this.uploadArea.classList.add('dragover');
        });
        
        this.uploadArea.addEventListener('dragleave', () => {
            this.uploadArea.classList.remove('dragover');
        });
        
        this.uploadArea.addEventListener('drop', (e) => {
            e.preventDefault();
            this.uploadArea.classList.remove('dragover');
            this.handleFileSelect(e.dataTransfer.files);
        });
        
        // URL input
        this.addUrlBtn.addEventListener('click', () => {
            this.handleUrlAdd();
        });
        
        this.urlInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                this.handleUrlAdd();
            }
        });
        
        // Format URLs button
        this.formatUrlsBtn.addEventListener('click', () => {
            this.formatUrls();
        });
    }
    
    async handleFileSelect(files) {
        const fileArray = Array.from(files);
        
        // Check file count limit
        if (!this.multiple && fileArray.length > 1) {
            this.showError(['Chỉ được chọn 1 file']);
            return;
        }
        
        if (this.images.length + fileArray.length > this.maxFiles) {
            this.showError([`Tối đa ${this.maxFiles} hình ảnh`]);
            return;
        }
        
        // Validate files
        const validFiles = [];
        const errors = [];
        
        for (const file of fileArray) {
            const validation = this.validateFile(file);
            if (validation.valid) {
                validFiles.push(file);
            } else {
                errors.push(`${file.name}: ${validation.error}`);
            }
        }
        
        if (errors.length > 0) {
            this.showError(errors);
            return;
        }
        
        // Upload files
        for (const file of validFiles) {
            await this.uploadFile(file);
        }
    }
    
    validateFile(file) {
        if (!this.config) {
            return { valid: false, error: 'Cấu hình chưa được tải' };
        }
        
        // Check file size
        if (file.size > this.config.max_file_size) {
            return { valid: false, error: 'Kích thước file quá lớn (tối đa 5MB)' };
        }
        
        // Check file type
        const extension = file.name.split('.').pop().toLowerCase();
        if (!this.config.allowed_extensions.includes(extension)) {
            return { valid: false, error: 'Định dạng file không được hỗ trợ' };
        }
        
        return { valid: true };
    }
    
    async uploadFile(file) {
        // Add image to preview with loading state
        const imageObj = {
            file: file,
            url: URL.createObjectURL(file),
            uploaded: false,
            loading: true,
            type: 'file'
        };
        
        this.images.push(imageObj);
        this.renderImages();
        
        // Upload file
        const formData = new FormData();
        formData.append('image', file);
        formData.append('folder', this.folder);
        
        try {
            const response = await fetch('/api/images/upload/single', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            });
            
            const data = await response.json();
            
            if (data.success) {
                // Update image object
                imageObj.url = data.data.url;
                imageObj.path = data.data.path;
                imageObj.uploaded = true;
                imageObj.loading = false;
                
                this.renderImages();
                this.updateHiddenInputs();
                this.hideError();
            } else {
                this.removeImage(this.images.indexOf(imageObj));
                this.showError([data.error || 'Upload failed']);
            }
        } catch (error) {
            this.removeImage(this.images.indexOf(imageObj));
            this.showError(['Lỗi kết nối khi upload file']);
        }
    }
    
    async handleUrlAdd() {
        const urlValue = this.urlInput.value.trim();
        if (!urlValue) return;
        
        // Handle multiple URLs (separated by newlines)
        const urls = urlValue.split('\n').map(url => url.trim()).filter(url => url);
        
        for (const url of urls) {
            await this.addUrlImage(url);
        }
        
        this.urlInput.value = '';
    }
    
    async addUrlImage(url) {
        // Check if URL already exists
        if (this.images.some(img => img.url === url)) {
            this.showError(['URL này đã tồn tại']);
            return;
        }
        
        // Check file count limit
        if (this.images.length >= this.maxFiles) {
            this.showError([`Tối đa ${this.maxFiles} hình ảnh`]);
            return;
        }
        
        // Add image with loading state
        const imageObj = {
            url: url,
            uploaded: false,
            loading: true,
            type: 'url'
        };
        
        this.images.push(imageObj);
        this.renderImages();
        
        // Validate URL
        try {
            const response = await fetch('/api/images/validate-url', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ url: url })
            });
            
            const data = await response.json();
            
            if (data.success) {
                imageObj.uploaded = true;
                imageObj.loading = false;
                this.renderImages();
                this.updateHiddenInputs();
                this.hideError();
            } else {
                this.removeImage(this.images.indexOf(imageObj));
                this.showError([data.error || 'URL không hợp lệ']);
            }
        } catch (error) {
            this.removeImage(this.images.indexOf(imageObj));
            this.showError(['Lỗi khi kiểm tra URL']);
        }
    }
    
    formatUrls() {
        const urlValue = this.urlInput.value.trim();
        if (!urlValue) return;
        
        // Split by various delimiters and clean up URLs
        const urls = urlValue
            .split(/[\n,;]/)
            .map(url => url.trim())
            .filter(url => url && this.isValidUrl(url))
            .filter((url, index, array) => array.indexOf(url) === index); // Remove duplicates
        
        this.urlInput.value = urls.join('\n');
    }
    
    isValidUrl(string) {
        try {
            new URL(string);
            return true;
        } catch (_) {
            return false;
        }
    }
    
    removeImage(index) {
        if (index >= 0 && index < this.images.length) {
            // Revoke object URL if it's a file
            const image = this.images[index];
            if (image.type === 'file' && image.url.startsWith('blob:')) {
                URL.revokeObjectURL(image.url);
            }
            
            this.images.splice(index, 1);
            this.renderImages();
            this.updateHiddenInputs();
        }
    }
    
    renderImages() {
        this.imagesGrid.innerHTML = '';
        
        this.images.forEach((image, index) => {
            const imageDiv = document.createElement('div');
            imageDiv.className = `image-preview-item ${image.loading ? 'loading' : ''}`;
            
            imageDiv.innerHTML = `
                <img src="${image.url}" alt="Preview ${index + 1}" loading="lazy">
                <button type="button" class="remove-btn" onclick="window.imageUploaders['${this.name}'].removeImage(${index})">
                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                    </svg>
                </button>
                ${image.loading ? '<div class="loading-overlay"><div class="animate-spin rounded-full h-6 w-6 border-b-2 border-white"></div></div>' : ''}
            `;
            
            this.imagesGrid.appendChild(imageDiv);
        });
    }
    
    updateHiddenInputs() {
        this.hiddenInputs.innerHTML = '';
        
        const uploadedImages = this.images.filter(img => img.uploaded);
        
        if (this.multiple) {
            uploadedImages.forEach((image, index) => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = `${this.name}[${index}]`;
                input.value = image.url;
                this.hiddenInputs.appendChild(input);
            });
        } else {
            if (uploadedImages.length > 0) {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = this.name;
                input.value = uploadedImages[0].url;
                this.hiddenInputs.appendChild(input);
            }
        }
    }
    
    showError(errors) {
        const errorList = this.errorMessages.querySelector('.error-list');
        errorList.innerHTML = errors.map(error => `<li>${error}</li>`).join('');
        this.errorMessages.classList.remove('hidden');
        
        // Auto hide after 5 seconds
        setTimeout(() => {
            this.hideError();
        }, 5000);
    }
    
    hideError() {
        this.errorMessages.classList.add('hidden');
    }
    
    getImages() {
        return this.images.filter(img => img.uploaded);
    }
    
    clearImages() {
        this.images.forEach(image => {
            if (image.type === 'file' && image.url.startsWith('blob:')) {
                URL.revokeObjectURL(image.url);
            }
        });
        this.images = [];
        this.renderImages();
        this.updateHiddenInputs();
    }
}

// Global registry for image uploaders
window.imageUploaders = window.imageUploaders || {};

// Initialize all image uploaders on page load
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.image-uploader-component').forEach(container => {
        const name = container.dataset.name;
        window.imageUploaders[name] = new ImageUploader(container);
    });
});
</script>
@endpush
