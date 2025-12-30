@extends('layouts.app')

@section('title', 'Tạo Product Template')

@push('head')
<meta name="csrf-token" content="{{ csrf_token() }}">
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
                <div>
                    <h1 class="text-3xl font-bold text-white mb-2">Create Product Template</h1>
                    <p class="text-gray-400">Create a new product template with all the information and variants</p>
                </div>
            </div>
        </div>

        <!-- Form -->
        <div class="w-full">
            <div class="bg-gray-800 rounded-xl border border-gray-700 p-6">
                <!-- Session Messages -->
                @if(session('success'))
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                        {{ session('success') }}
                    </div>
                @endif

                @if(session('error'))
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                        {{ session('error') }}
                    </div>
                @endif

                <!-- Validation Errors -->
                @if ($errors->any())
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                        <h4 class="font-bold mb-2">There was an error:</h4>
                        <ul class="list-disc list-inside space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif



                <form method="POST" action="{{ route('product-templates.store') }}" enctype="multipart/form-data" id="templateForm" onsubmit="return handleAttributesSubmit(this);">
                    @csrf
                    
                    <!-- Basic Information -->
                    <div class="mb-8">
                        <h3 class="text-lg font-semibold text-white mb-4 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                            </svg>
                            Basic Information
                        </h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-300 mb-2">Product Template Name *</label>
                                <input type="text" id="name" name="name" value="{{ old('name') }}" required
                                       class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-2 text-white placeholder-gray-400 focus:outline-none focus:border-blue-500 @error('name') border-red-500 @enderror">
                                @error('name')
                                    <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <div>
                                <label for="base_price" class="block text-sm font-medium text-gray-300 mb-2">Base Price *</label>
                                <input type="number" id="base_price" name="base_price" step="0.01" value="{{ old('base_price') }}" required
                                       class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-2 text-white placeholder-gray-400 focus:outline-none focus:border-blue-500 @error('base_price') border-red-500 @enderror">
                                @error('base_price')
                                    <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <div>
                                <label for="list_price" class="block text-sm font-medium text-gray-300 mb-2">List Price (Optional)</label>
                                <input type="number" id="list_price" name="list_price" step="0.01" value="{{ old('list_price') }}"
                                       class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-2 text-white placeholder-gray-400 focus:outline-none focus:border-blue-500 @error('list_price') border-red-500 @enderror">
                                @error('list_price')
                                    <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="mt-4">
                            <label for="category_search" class="block text-sm font-medium text-gray-300 mb-2">Search Category</label>
                            <div class="relative">
                                <input type="text" id="category_search" name="category_search" 
                                       placeholder="Enter search keyword (e.g. T-shirt, Phone, Computer...)"
                                       class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-2 text-white focus:outline-none focus:border-blue-500 @error('category') border-red-500 @enderror">
                                <input type="hidden" id="category" name="category" value="{{ old('category') }}">
                                
                                <!-- Search results dropdown -->
                                <div id="category_results" class="absolute z-50 w-full mt-1 bg-gray-800 border border-gray-600 rounded-lg shadow-lg max-h-60 overflow-y-auto hidden">
                                    <div class="p-2 text-gray-400 text-sm border-b border-gray-600">
                                        Search Results: <span id="results_count">0</span>
                                    </div>
                                    <div id="results_list" class="p-0">
                                        <!-- Results will be populated here -->
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Selected category display -->
                            <div id="selected_category_display" class="mt-2 p-2 bg-gray-800 rounded-lg border border-gray-600 {{ $oldCategoryName ? '' : 'hidden' }}">
                                <div class="flex items-center justify-between">
                                    <span class="text-white text-sm">
                                        <strong>Selected Category:</strong> 
                                        <span id="selected_category_name">{{ $oldCategoryName }}</span>
                                    </span>
                                    <button type="button" id="clear_category" class="text-red-400 hover:text-red-300 text-sm">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                        </svg>
                                    </button>
                                </div>
                            </div>

                            <!-- Category Attributes Section -->
                            <div id="category_attributes_section" class="mt-4 {{ $oldCategoryName ? '' : 'hidden' }}">
                                <h4 class="text-md font-semibold text-white mb-3 flex items-center">
                                    <svg class="w-4 h-4 mr-2 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    Category Attributes
                                </h4>
                                
                                <!-- Loading indicator -->
                                <div id="attributes_loading" class="hidden">
                                    <div class="flex items-center justify-center p-4">
                                        <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-blue-500"></div>
                                        <span class="ml-2 text-gray-300">Loading attributes...</span>
                                    </div>
                                </div>

                                <!-- Required Attributes -->
                                <div id="required_attributes_container" class="mb-4">
                                    <h5 class="text-sm font-medium text-red-400 mb-2 flex items-center">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                        </svg>
                                        Required Attributes
                                    </h5>
                                    <div id="required_attributes_list" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                                        <!-- Required attributes will be loaded here -->
                                    </div>
                                </div>

                                <!-- Optional Attributes -->
                                <div id="optional_attributes_container">
                                    <h5 class="text-sm font-medium text-gray-400 mb-2 flex items-center">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        Optional Attributes
                                    </h5>
                                    <div id="optional_attributes_list" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                                        <!-- Optional attributes will be loaded here -->
                                    </div>
                                </div>

                                <!-- No attributes message -->
                                <div id="no_attributes_message" class="hidden">
                                    <div class="text-center p-4 text-gray-400">
                                        <svg class="w-8 h-8 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                        </svg>
                                        <p>No attributes found for this category</p>
                                    </div>
                                </div>
                            </div>
                            
                            @error('category')
                                <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <script>
                            // Don't load categories right away - load via AJAX when searching
                            let searchTimeout;
                            let categoriesCache = {};
                            
                            document.addEventListener('DOMContentLoaded', function() {
                                const searchInput = document.getElementById('category_search');
                                const resultsDiv = document.getElementById('category_results');
                                const resultsList = document.getElementById('results_list');
                                const resultsCount = document.getElementById('results_count');
                                const categoryInput = document.getElementById('category');
                                const selectedDisplay = document.getElementById('selected_category_display');
                                const selectedName = document.getElementById('selected_category_name');
                                const clearBtn = document.getElementById('clear_category');
                                
                                // Search functionality - load qua AJAX
                                searchInput.addEventListener('input', function() {
                                    clearTimeout(searchTimeout);
                                    const query = this.value.trim();
                                    
                                    if (query.length < 2) {
                                        resultsDiv.classList.add('hidden');
                                        return;
                                    }
                                    
                                    // Show loading
                                    resultsList.innerHTML = '<div class="p-3 text-gray-400 text-sm text-center">Đang tìm kiếm...</div>';
                                    resultsDiv.classList.remove('hidden');
                                    
                                    searchTimeout = setTimeout(() => {
                                        // Load categories qua AJAX
                                        fetch(`{{ route('product-templates.search-categories') }}?q=${encodeURIComponent(query)}`, {
                                            method: 'GET',
                                            headers: {
                                                'Accept': 'application/json',
                                                'X-Requested-With': 'XMLHttpRequest'
                                            },
                                            credentials: 'same-origin'
                                        })
                                        .then(response => response.json())
                                        .then(data => {
                                            const categories = data.categories || {};
                                            
                                            // Cache results
                                            Object.assign(categoriesCache, categories);
                                            
                                            // Filter and limit results
                                            const filtered = Object.entries(categories).slice(0, 20);
                                        
                                            // Display results
                                            if (filtered.length > 0) {
                                                resultsList.innerHTML = filtered.map(([id, name]) => `
                                                    <div class="category-result p-2 hover:bg-gray-700 cursor-pointer border-b border-gray-600 last:border-b-0" 
                                                         data-id="${id}" data-name="${name}">
                                                        <div class="text-white text-sm">${name}</div>
                                                        <div class="text-gray-400 text-xs">ID: ${id}</div>
                                                    </div>
                                                `).join('');
                                                
                                                resultsCount.textContent = filtered.length;
                                                resultsDiv.classList.remove('hidden');
                                            } else {
                                                resultsList.innerHTML = `
                                                    <div class="p-3 text-gray-400 text-sm text-center">
                                                        No categories found matching your search
                                                    </div>
                                                `;
                                                resultsCount.textContent = '0';
                                                resultsDiv.classList.remove('hidden');
                                            }
                                        })
                                        .catch(error => {
                                            console.error('Error searching categories:', error);
                                            resultsList.innerHTML = `
                                                <div class="p-3 text-red-400 text-sm text-center">
                                                    Error searching categories
                                                </div>
                                            `;
                                        });
                                    }, 500); // Debounce 500ms
                                });
                                
                                // Handle result selection
                                resultsList.addEventListener('click', function(e) {
                                    const result = e.target.closest('.category-result');
                                    if (result) {
                                        const id = result.dataset.id;
                                        const name = result.dataset.name;
                                        
                                        // Set values
                                        categoryInput.value = id;
                                        selectedName.textContent = name;
                                        
                                        // Show selected display
                                        selectedDisplay.classList.remove('hidden');
                                        
                                        // Hide results
                                        resultsDiv.classList.add('hidden');
                                        
                                        // Clear search input
                                        searchInput.value = '';
                                        
                                        // Load category attributes
                                        loadCategoryAttributes(id);
                                    }
                                });
                                
                                // Clear selection
                                clearBtn.addEventListener('click', function() {
                                    categoryInput.value = '';
                                    selectedName.textContent = '';
                                    selectedDisplay.classList.add('hidden');
                                    
                                    // Hide attributes section
                                    document.getElementById('category_attributes_section').classList.add('hidden');
                                });
                                
                                // Hide results when clicking outside
                                document.addEventListener('click', function(e) {
                                    if (!e.target.closest('#category_search') && !e.target.closest('#category_results')) {
                                        resultsDiv.classList.add('hidden');
                                    }
                                });
                                
                                // Handle keyboard navigation
                                searchInput.addEventListener('keydown', function(e) {
                                    if (e.key === 'Escape') {
                                        resultsDiv.classList.add('hidden');
                                    }
                                });

                                // Load category attributes function
                                function loadCategoryAttributes(categoryId) {
                                    const attributesSection = document.getElementById('category_attributes_section');
                                    const loadingDiv = document.getElementById('attributes_loading');
                                    const requiredList = document.getElementById('required_attributes_list');
                                    const optionalList = document.getElementById('optional_attributes_list');
                                    const noAttributesDiv = document.getElementById('no_attributes_message');

                                    // Show loading and attributes section
                                    attributesSection.classList.remove('hidden');
                                    loadingDiv.classList.remove('hidden');
                                    requiredList.innerHTML = '';
                                    optionalList.innerHTML = '';
                                    noAttributesDiv.classList.add('hidden');

                                    // Fetch attributes from API
                                    fetch(`/tik-tok-category-attributes/api/attributes?category_id=${categoryId}`)
                                        .then(response => response.json())
                                        .then(data => {
                                            loadingDiv.classList.add('hidden');
                                            
                                            if (data.success && data.data) {
                                                const groupedAttributes = data.data.grouped;
                                                let hasAttributes = false;

                                                // Display required attributes
                                                if (groupedAttributes.required && groupedAttributes.required.length > 0) {
                                                    hasAttributes = true;
                                                    requiredList.innerHTML = groupedAttributes.required.map(attr => 
                                                        createAttributeInput(attr, true)
                                                    ).join('');
                                                } else {
                                                            requiredList.innerHTML = '<p class="text-gray-500 text-sm">No required attributes found</p>';
                                                }

                                                // Display optional attributes
                                                if (groupedAttributes.optional && groupedAttributes.optional.length > 0) {
                                                    hasAttributes = true;
                                                    optionalList.innerHTML = groupedAttributes.optional.map(attr => 
                                                        createAttributeInput(attr, false)
                                                    ).join('');
                                                } else {
                                                    optionalList.innerHTML = '<p class="text-gray-500 text-sm">Không có thuộc tính tùy chọn</p>';
                                                }

                                                if (!hasAttributes) {
                                                    noAttributesDiv.classList.remove('hidden');
                                                }
                                            } else {
                                                noAttributesDiv.classList.remove('hidden');
                                            }
                                        })
                                        .catch(error => {
                                            console.error('Error loading attributes:', error);
                                            loadingDiv.classList.add('hidden');
                                            noAttributesDiv.classList.remove('hidden');
                                        });
                                }

                                // Create attribute input field
                                function createAttributeInput(attribute, isRequired) {
                                    const requiredClass = isRequired ? 'border-red-500' : 'border-gray-600';
                                    const requiredLabel = isRequired ? ' <span class="text-red-400">*</span>' : '';
                                    
                                    let inputHtml = '';
                                    
                                    if (attribute.values && attribute.values.length > 0) {
                                        if (attribute.is_multiple_selection) {
                                            // Dropdown-style checkbox selector
                                            inputHtml = `
                                                <div class="relative">
                                                    <button type="button" 
                                                            class="w-full bg-gray-700 border ${requiredClass} rounded-lg px-2 py-1 text-sm text-white focus:outline-none focus:border-blue-500 flex items-center justify-between"
                                                            onclick="toggleCheckboxDropdown('${attribute.attribute_id}')">
                                                        <span id="selected-text-${attribute.attribute_id}">-- Select --</span>
                                                        <i class="fas fa-chevron-down text-xs"></i>
                                                    </button>
                                                    <div id="checkbox-dropdown-${attribute.attribute_id}" 
                                                         class="absolute z-10 w-full mt-1 bg-gray-700 border border-gray-600 rounded-lg shadow-lg hidden max-h-48 overflow-y-auto">
                                                        ${attribute.values.map(value => `
                                                            <label class="flex items-center space-x-2 px-3 py-2 hover:bg-gray-600 cursor-pointer text-sm">
                                                                <input type="checkbox" 
                                                                       name="attributes[${attribute.attribute_id}][]" 
                                                                       value="${value.id}"
                                                                       class="rounded border-gray-600 bg-gray-700 text-blue-500 focus:ring-blue-500 focus:ring-offset-0"
                                                                       onchange="updateSelectedText('${attribute.attribute_id}')"
                                                                       ${isRequired ? 'required' : ''}>
                                                                <span class="text-white">${value.name}</span>
                                                            </label>
                                                        `).join('')}
                                                    </div>
                                                </div>
                                            `;
                                        } else {
                                            // Dropdown for single selection
                                            inputHtml = `
                                                <select name="attributes[${attribute.attribute_id}]" 
                                                        class="w-full bg-gray-700 border ${requiredClass} rounded-lg px-2 py-1 text-sm text-white focus:outline-none focus:border-blue-500"
                                                        ${isRequired ? 'required' : ''}>
                                                    <option value="">-- Select --</option>
                                                    ${attribute.values.map(value => 
                                                        `<option value="${value.id}">${value.name}</option>`
                                                    ).join('')}
                                                </select>
                                            `;
                                        }
                                    } else if (attribute.is_customizable) {
                                        // Text input for customizable attributes
                                        inputHtml = `
                                            <input type="text" 
                                                   name="attributes[${attribute.attribute_id}]" 
                                                   placeholder="Enter ${attribute.name}"
                                                   class="w-full bg-gray-700 border ${requiredClass} rounded-lg px-2 py-1 text-sm text-white placeholder-gray-400 focus:outline-none focus:border-blue-500"
                                                   ${isRequired ? 'required' : ''}>
                                        `;
                                    } else {
                                        // Regular text input
                                        inputHtml = `
                                            <input type="text" 
                                                   name="attributes[${attribute.attribute_id}]" 
                                                   placeholder="Enter ${attribute.name}"
                                                   class="w-full bg-gray-700 border ${requiredClass} rounded-lg px-2 py-1 text-sm text-white placeholder-gray-400 focus:outline-none focus:border-blue-500"
                                                   ${isRequired ? 'required' : ''}>
                                        `;
                                    }

                                    return `
                                        <div class="bg-gray-800 rounded-lg p-2 border border-gray-700">
                                            <label class="block text-xs font-medium text-gray-300 mb-1">
                                                ${attribute.name}${requiredLabel}
                                                <span class="text-xs text-gray-500 ml-1">(${attribute.type})</span>
                                            </label>
                                            ${inputHtml}
                                            ${attribute.value_data_format ? 
                                                `<p class="text-xs text-gray-500 mt-1">${attribute.value_data_format}</p>` : ''
                                            }
                                            ${attribute.is_multiple_selection ? 
                                                '<p class="text-xs text-blue-400 mt-1">Multiple values</p>' : ''
                                            }
                                        </div>
                                    `;
                                }

                                // Load attributes on page load if category is already selected
                                if (categoryInput.value) {
                                    loadCategoryAttributes(categoryInput.value);
                                }
                            });

                            // Dropdown checkbox functions
                            window.toggleCheckboxDropdown = function(attributeId) {
                                const dropdown = document.getElementById('checkbox-dropdown-' + attributeId);
                                const isHidden = dropdown.classList.contains('hidden');
                                
                                // Close all other dropdowns
                                document.querySelectorAll('[id^="checkbox-dropdown-"]').forEach(el => {
                                    if (el.id !== 'checkbox-dropdown-' + attributeId) {
                                        el.classList.add('hidden');
                                    }
                                });
                                
                                // Toggle current dropdown
                                if (isHidden) {
                                    dropdown.classList.remove('hidden');
                                } else {
                                    dropdown.classList.add('hidden');
                                }
                            };

                            window.updateSelectedText = function(attributeId) {
                                const checkboxes = document.querySelectorAll(`input[name="attributes[${attributeId}][]"]:checked`);
                                const selectedText = document.getElementById('selected-text-' + attributeId);
                                
                                if (checkboxes.length === 0) {
                                    selectedText.textContent = '-- Select --';
                                } else if (checkboxes.length === 1) {
                                    selectedText.textContent = checkboxes[0].nextElementSibling.textContent;
                                } else {
                                    selectedText.textContent = `${checkboxes.length} items selected`;
                                }
                            };

                            // Close dropdowns when clicking outside
                            document.addEventListener('click', function(event) {
                                if (!event.target.closest('[id^="checkbox-dropdown-"]') && 
                                    !event.target.closest('button[onclick^="toggleCheckboxDropdown"]')) {
                                    document.querySelectorAll('[id^="checkbox-dropdown-"]').forEach(el => {
                                        el.classList.add('hidden');
                                    });
                                }
                            });
                            
                        </script>
                        
                        <div class="mt-4">
                            <label for="description" class="block text-sm font-medium text-gray-300 mb-2">Description</label>
                            <textarea id="description" name="description" rows="5" 
                                      class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-2 text-white placeholder-gray-400 focus:outline-none focus:border-blue-500 @error('description') border-red-500 @enderror whitespace-pre-wrap"
                                      placeholder="Enter product description...&#10;&#10;Example:&#10;• Material: 100% cotton&#10;• Style: Regular fit&#10;• Color: Black, White, Green">{{ old('description') }}</textarea>
                            <div class="mt-2 text-xs text-gray-400">
                                <p>💡 <strong>Tip:</strong> Use Enter to create a new line, and • to create a list</p>
                            </div>
                            @error('description')
                                <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Dimensions -->
                    <div class="my-8">
                        <h3 class="text-lg font-semibold text-white mb-4 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path>
                            </svg>
                                    Dimensions & Weight
                        </h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div>
                                <label for="weight" class="block text-sm font-medium text-gray-300 mb-2">Weight (kg)</label>
                                <input type="number" id="weight" name="weight" step="0.01" value="{{ old('weight') }}"
                                       class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-2 text-white placeholder-gray-400 focus:outline-none focus:border-blue-500 @error('weight') border-red-500 @enderror">
                                @error('weight')
                                    <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <div>
                                <label for="height" class="block text-sm font-medium text-gray-300 mb-2">Height (cm)</label>
                                <input type="number" id="height" name="height" step="0.01" value="{{ old('height') }}"
                                       class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-2 text-white placeholder-gray-400 focus:outline-none focus:border-blue-500 @error('height') border-red-500 @enderror">
                                @error('height')
                                    <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <div>
                                <label for="width" class="block text-sm font-medium text-gray-300 mb-2">Width (cm)</label>
                                <input type="number" id="width" name="width" step="0.01" value="{{ old('width') }}"
                                       class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-2 text-white placeholder-gray-400 focus:outline-none focus:border-blue-500 @error('width') border-red-500 @enderror">
                                @error('width')
                                    <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <div>
                                <label for="length" class="block text-sm font-medium text-gray-300 mb-2">Length (cm)</label>
                                <input type="number" id="length" name="length" step="0.01" value="{{ old('length') }}"
                                       class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-2 text-white placeholder-gray-400 focus:outline-none focus:border-blue-500 @error('length') border-red-500 @enderror">
                                @error('length')
                                    <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Media -->
                    <div class="mb-8">
                        <h3 class="text-lg font-semibold text-white mb-4 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            Images & Video
                        </h3>
                        
                        <div class="grid grid-cols-1 gap-4">
                            <!-- Image Upload Manager Component -->
                            <div class="col-span-full">
                                <x-image-upload-manager 
                                    name="images"
                                    :multiple="true"
                                    :maxFiles="10"
                                    :existingImages="old('images', [])"
                                    label="Product Images"
                                />
                                @error('images')
                                    <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <!-- Size Chart and Video -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="size_chart_file" class="block text-sm font-medium text-gray-300 mb-2">Size Chart</label>
                                    <input type="file" id="size_chart_file" name="size_chart_files[]" accept="image/*"
                                           class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-2 text-white placeholder-gray-400 focus:outline-none focus:border-blue-500 @error('size_chart_files') border-red-500 @enderror">
                                    @error('size_chart_files')
                                        <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                                
                                <div>
                                    <label for="product_video_file" class="block text-sm font-medium text-gray-300 mb-2">Product Video</label>
                                    <input type="file" id="product_video_file" name="product_video_files[]" accept="video/*"
                                           class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-2 text-white placeholder-gray-400 focus:outline-none focus:border-blue-500 @error('product_video_files') border-red-500 @enderror">
                                    @error('product_video_files')
                                        <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Product Options Matrix -->
                    <div class="mb-8">
                        <h3 class="text-lg font-semibold text-white mb-4 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-pink-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                            </svg>
                            Product Options Matrix
                        </h3>
                       
                        <div class="bg-gray-700 rounded-lg p-4 mb-4">
                            <p class="text-gray-300 text-sm mb-4">
                                <strong>Guide:</strong> Add classification attributes (Color, Size, Type...) and corresponding values. 
                                The system will automatically create product variants (variants) based on all possible combinations.    
                            </p>
                        </div>
                        
                        <div id="productOptions" class="space-y-4">
                            <!-- Options will be added here dynamically -->
                        </div>
                        
                        <button type="button" id="addOption" class="mt-4 px-4 py-2 bg-blue-600 hover:bg-blue-500 text-white rounded-lg transition-colors duration-200 flex items-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                            Add Classification Attribute
                        </button>
                    </div>

                    <!-- Variants Management -->
                    <div class="mb-8" id="variantsSection" style="display: none;">
                        <h3 class="text-lg font-semibold text-white mb-4 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                            </svg>
                                Manage Variants
                        </h3>
                        
                                                 <div class="bg-gray-700 rounded-lg p-4 mb-4">
                             <div class="flex items-center justify-between mb-4">
                                 <div>
                                     <p class="text-gray-300 text-sm">
                                         Total variants: <span id="totalVariants" class="font-bold text-yellow-400">0</span>
                                     </p>
                                     <p class="text-gray-400 text-xs">Each variant will have its own price and quantity information</p>
                                 </div>
                                 <button type="button" id="generateVariants" class="px-4 py-2 bg-green-600 hover:bg-green-500 text-white rounded-lg text-sm transition-colors duration-200">
                                     Create Variants
                                 </button>
                             </div>
                         </div>
                         
                                                   <!-- Smart Bulk Edit Section -->
                          <div id="bulkEditSection" class="bg-gray-700 rounded-lg p-4 mb-4" style="display: none;">
                              <h4 class="text-white font-medium mb-4 flex items-center">
                                  <svg class="w-4 h-4 mr-2 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                  </svg>
                                  Smart Edit
                              </h4>
                              
                              <!-- Quick Selection Tools -->
                              <div class="bg-gray-600 rounded-lg p-3 mb-4">
                                  <h5 class="text-white font-medium mb-3">Quick Selection Tools</h5>
                                  <div class="flex flex-wrap gap-2">
                                      <button type="button" id="selectAllBtn" class="px-3 py-1 bg-blue-600 hover:bg-blue-500 text-white rounded text-sm transition-colors">
                                          Select All
                                      </button>
                                      <button type="button" id="selectNoneBtn" class="px-3 py-1 bg-gray-600 hover:bg-gray-500 text-white rounded text-sm transition-colors">
                                          Select None
                                      </button>
                                      <button type="button" id="selectInverseBtn" class="px-3 py-1 bg-purple-600 hover:bg-purple-500 text-white rounded text-sm transition-colors">
                                          Inverse Selection
                                      </button>
                                      <span class="text-sm text-gray-300 ml-2">
                                          Selected: <span id="selectedCount" class="font-medium text-blue-400">0</span> variants
                                      </span>
                                  </div>
                              </div>
                              
                                                             <!-- Smart Filters -->
                               <div class="bg-gray-600 rounded-lg p-3 mb-4">
                              <div class="flex items-center justify-between flex-wrap gap-2 mb-3">
                                  <h5 class="text-white font-medium">Smart Filters</h5>
                                  <div class="flex items-center gap-2">
                                      <button type="button" onclick="selectAllFilterValues()" class="px-3 py-1 bg-blue-600 hover:bg-blue-500 text-white rounded text-sm transition-colors">
                                          Select all values
                                      </button>
                                      <button type="button" onclick="clearAllFilterValues()" class="px-3 py-1 bg-gray-600 hover:bg-gray-500 text-white rounded text-sm transition-colors">
                                          Clear values
                                      </button>
                                  </div>
                              </div>
                                   <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3" id="smartFilters">
                                       <!-- Smart filters will be generated here -->
                                   </div>
                                                                       <div class="flex items-center justify-between mt-3 pt-3 border-t border-gray-500">
                                        <div class="flex items-center space-x-3">
                                            <div class="flex items-center space-x-2">
                                                <label class="flex items-center text-sm text-gray-300">
                                                    <input type="radio" name="filterLogic" value="AND" checked class="mr-1 rounded border-gray-500 bg-gray-600 text-purple-500 focus:ring-purple-500">
                                                    Logic AND
                                                </label>
                                                <label class="flex items-center text-sm text-gray-300">
                                                    <input type="radio" name="filterLogic" value="OR" class="mr-1 rounded border-gray-500 bg-gray-600 text-purple-500 focus:ring-purple-500">
                                                    Logic OR
                                                </label>
                                            </div>
                                            <div class="flex space-x-2">
                                                <button type="button" id="applyMultiSelection" class="px-3 py-1 bg-purple-600 hover:bg-purple-500 text-white rounded text-sm transition-colors">
                                                        Apply Selection
                                                </button>
                                                <button type="button" id="clearMultiSelection" class="px-3 py-1 bg-gray-500 hover:bg-gray-400 text-white rounded text-sm transition-colors">
                                                    Clear Selection
                                                </button>
                                            </div>
                                        </div>
                                        <div class="text-sm text-gray-300">
                                            Selected: <span id="multiSelectionCount" class="font-medium text-purple-400">0</span> values
                                        </div>
                                    </div>
                               </div>
                              
                              <!-- Bulk Edit Form -->
                              <div class="bg-gray-600 rounded-lg p-3 mb-4">
                                  <h5 class="text-white font-medium mb-3">Bulk Edit</h5>
                                  <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-3">
                                      <div>
                                          <label class="block text-sm font-medium text-gray-300 mb-1">Sale Price (*)</label>
                                          <input type="number" id="bulkPrice" step="0.01" 
                                                 class="w-full bg-gray-700 border border-gray-500 rounded px-3 py-2 text-white placeholder-gray-400 focus:outline-none focus:border-blue-500"
                                                 placeholder="Enter price">
                                      </div>
                                      <div>
                                          <label class="block text-sm font-medium text-gray-300 mb-1">List Price (Optional) </label>
                                          <input type="number" id="bulkListPrice" step="0.01" 
                                                 class="w-full bg-gray-700 border border-gray-500 rounded px-3 py-2 text-white placeholder-gray-400 focus:outline-none focus:border-blue-500"
                                                 placeholder="Enter list price">
                                      </div>
                                      <div>
                                          <label class="block text-sm font-medium text-gray-300 mb-1">Quantity</label>
                                          <input type="number" id="bulkQuantity" min="0" 
                                                 class="w-full bg-gray-700 border border-gray-500 rounded px-3 py-2 text-white placeholder-gray-400 focus:outline-none focus:border-blue-500"
                                                 placeholder="Enter quantity">
                                      </div>
                                      <div>
                                          <label class="block text-sm font-medium text-gray-300 mb-1">Bulk Images</label>
                                          <input type="file" id="bulkImages" name="bulk_images_files[]" accept="image/*" multiple
                                                 class="w-full bg-gray-700 border border-gray-500 rounded px-3 py-2 text-white placeholder-gray-400 focus:outline-none focus:border-blue-500">
                                      </div>
                                  </div>
                                  
                                  <div class="flex items-center justify-between mt-3 pt-3 border-t border-gray-500">
                                      <div class="flex space-x-2">
                                          <button type="button" id="applyBulkEdit" class="px-4 py-2 bg-blue-600 hover:bg-blue-500 text-white rounded text-sm transition-colors" onclick="console.log('Button clicked directly!')">
                                              Apply to selected variants
                                          </button>
                                          <button type="button" id="applyBulkEditAll" class="px-4 py-2 bg-green-600 hover:bg-green-500 text-white rounded text-sm transition-colors">
                                              Apply to all
                                          </button>
                                      </div>
                                      <button type="button" id="clearBulkForm" class="px-3 py-1 bg-gray-500 hover:bg-gray-400 text-white rounded text-sm transition-colors">
                                            Clear Form
                                      </button>
                                  </div>
                              </div>
                          </div>
                         
                         <div id="variantsContainer" class="space-y-4">
                            <!-- Variants will be generated here -->
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center justify-end space-x-4 pt-6 border-t border-gray-700">
                        <a href="{{ route('product-templates.index') }}" 
                           class="px-6 py-2 border border-gray-600 text-gray-300 rounded-lg hover:bg-gray-700 transition-colors duration-200">
                            Cancel
                        </a>
                        <button type="submit" 
                                class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition-colors duration-200">
                            Create Template
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    let optionIndex = 0;
    let variantIndex = 0;
    let allVariants = [];

    // Add option
    document.addEventListener('DOMContentLoaded', function() {
        const addOptionBtn = document.getElementById('addOption');
        if (addOptionBtn) {
            addOptionBtn.addEventListener('click', function() {
                console.log('Add option button clicked!');
                const optionsContainer = document.getElementById('productOptions');
                if (!optionsContainer) {
                    console.error('productOptions container not found!');
                    return;
                }
                const optionDiv = document.createElement('div');
                optionDiv.className = 'border border-gray-600 rounded-lg p-4 bg-gray-700';
                optionDiv.innerHTML = `
                    <div class="flex justify-between items-center mb-4">
                        <h4 class="font-medium text-white">Classification Attribute ${optionIndex + 1}</h4>
                        <button type="button" class="text-red-400 hover:text-red-300 transition-colors duration-200" onclick="removeOption(this);">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                        </button>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">Attribute Name (e.g. Color, Size, Type)</label>
                            <input name="options[${optionIndex}][name]" type="text" required
                                   class="w-full bg-gray-600 border border-gray-500 rounded-lg px-4 py-2 text-white placeholder-gray-400 focus:outline-none focus:border-blue-500"
                                   onchange="updateVariantsSection()">
                        </div>
                        <div>
                                <label class="block text-sm font-medium text-gray-300 mb-2">Values (separated by comma)</label>
                            <input name="options[${optionIndex}][values_string]" type="text" required
                                   class="w-full bg-gray-600 border border-gray-500 rounded-lg px-4 py-2 text-white placeholder-gray-400 focus:outline-none focus:border-blue-500" 
                                   placeholder="Black, White, Red"
                                   onchange="updateVariantsSection()">
                        </div>
                    </div>
                `;
                optionsContainer.appendChild(optionDiv);
                optionIndex++;
                updateVariantsSection();
            });
        } else {
            console.error('addOption button not found!');
        }
        
                 // Generate variants button
         const generateVariantsBtn = document.getElementById('generateVariants');
         if (generateVariantsBtn) {
             generateVariantsBtn.addEventListener('click', function() {
                 generateAllVariants();
             });
         }
     });

     // Remove option
    function removeOption(button) {
        button.parentElement.parentElement.remove();
        updateVariantsSection();
    }

    // Update variants section visibility and count
    function updateVariantsSection() {
        const options = document.querySelectorAll('[name*="[name]"]');
        const valuesInputs = document.querySelectorAll('[name*="[values_string]"]');
        const variantsSection = document.getElementById('variantsSection');
        const totalVariantsSpan = document.getElementById('totalVariants');
        
        let totalVariants = 1;
        let hasValidOptions = false;
        
        for (let i = 0; i < options.length; i++) {
            const optionName = options[i].value.trim();
            const valuesString = valuesInputs[i].value.trim();
            
            if (optionName && valuesString) {
                const values = valuesString.split(',').map(v => v.trim()).filter(v => v);
                if (values.length > 0) {
                    totalVariants *= values.length;
                    hasValidOptions = true;
                }
            }
        }
        
        if (hasValidOptions && totalVariants > 0) {
            variantsSection.style.display = 'block';
            totalVariantsSpan.textContent = totalVariants;
        } else {
            variantsSection.style.display = 'none';
            totalVariantsSpan.textContent = '0';
        }
    }

    // Generate all variants using Cartesian product
    function generateAllVariants() {
        const options = document.querySelectorAll('[name*="[name]"]');
        const valuesInputs = document.querySelectorAll('[name*="[values_string]"]');
        
        // Collect all options and their values
        let allOptions = [];
        for (let i = 0; i < options.length; i++) {
            const optionName = options[i].value.trim();
            const valuesString = valuesInputs[i].value.trim();
            
            if (optionName && valuesString) {
                const values = valuesString.split(',').map(v => v.trim()).filter(v => v);
                if (values.length > 0) {
                    allOptions.push({
                        name: optionName,
                        values: values
                    });
                }
            }
        }
        
        if (allOptions.length === 0) {
            alert('Please enter at least one classification attribute and corresponding values.');
            return;
        }
        
        // Generate Cartesian product
        allVariants = generateCartesianProduct(allOptions);
        
        renderVariants();
                 // Show success message
         showNotification(`Successfully created ${allVariants.length} variants!`, 'success');
         
         // Show bulk edit section
         document.getElementById('bulkEditSection').style.display = 'block';
         
         // Setup smart bulk edit functionality
         console.log('Setting up smart bulk edit...');
         setupSmartBulkEdit();
         console.log('Smart bulk edit setup completed');
     }

    // Generate Cartesian product of all option combinations
    function generateCartesianProduct(options) {
        if (options.length === 0) return [];
        
        const result = [];
        
        function generateCombos(combo, index) {
            if (index === options.length) {
                result.push(combo);
                return;
            }
            
            for (const value of options[index].values) {
                generateCombos([...combo, { name: options[index].name, value: value }], index + 1);
            }
        }
        
        generateCombos([], 0);
        return result;
    }

         // Create variant input form
     function createVariantInput(variant, index, savedValues = {}) {
         const variantDiv = document.createElement('div');
         variantDiv.className = 'border border-gray-600 rounded-lg p-4 bg-gray-700';
         
         // Create variant combination string
         const combinationString = variant.map(item => `${item.name}: ${item.value}`).join(' / ');
         // Defaults: price = base_price, quantity = 100
         const basePriceField = document.getElementById('base_price');
         const basePriceValue = basePriceField ? parseFloat(basePriceField.value || '0') || '' : '';
         const defaultPrice = savedValues.price ?? basePriceValue;
         const defaultListPrice = savedValues.list_price ?? '';
         const defaultQuantity = savedValues.quantity ?? 100;
         
         variantDiv.innerHTML = `
             <div class="flex justify-between items-center mb-4">
                 <div class="flex items-center space-x-3">
                     <input type="checkbox" class="variant-checkbox mr-2 rounded border-gray-500 bg-gray-600 text-blue-500 focus:ring-blue-500" data-index="${index}" ${savedValues.checked ? 'checked' : ''}>
                     <div>
                         <h4 class="font-medium text-white">Variant ${index + 1}</h4>
                         <p class="text-gray-400 text-sm">${combinationString}</p>
                     </div>
                 </div>
                 <div class="flex items-center space-x-2">
                     <span class="text-green-400 text-sm font-medium">✓ Created</span>
                     <button type="button" class="text-red-400 hover:text-red-300 text-sm" onclick="removeVariant(${index})" title="Remove this variant">
                         <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                             <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                         </svg>
                     </button>
                 </div>
             </div>
             
             <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                 <div>
                     <label class="block text-sm font-medium text-gray-300 mb-2">Sale Price  *</label>
                     <input name="variants[${index}][price]" type="number" step="0.01" required
                            class="w-full bg-gray-600 border border-gray-500 rounded-lg px-4 py-2 text-white placeholder-gray-400 focus:outline-none focus:border-blue-500"
                            placeholder="0.00" value="${defaultPrice}">
                 </div>
                 <div>
                     <label class="block text-sm font-medium text-gray-300 mb-2">List Price (Optional) </label>
                     <input name="variants[${index}][list_price]" type="number" step="0.01"
                            class="w-full bg-gray-600 border border-gray-500 rounded-lg px-4 py-2 text-white placeholder-gray-400 focus:outline-none focus:border-blue-500"
                            placeholder="0.00" value="${defaultListPrice}">
                 </div>
                 <div>
                     <label class="block text-sm font-medium text-gray-300 mb-2">Quantity *</label>
                     <input name="variants[${index}][quantity]" type="number" min="0" required
                            class="w-full bg-gray-600 border border-gray-500 rounded-lg px-4 py-2 text-white placeholder-gray-400 focus:outline-none focus:border-blue-500"
                            placeholder="0" value="${defaultQuantity}">
                 </div>
                 <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">Images</label>
                     <input name="variants[${index}][image_file]" type="file" accept="image/*"
                            class="w-full bg-gray-600 border border-gray-500 rounded-lg px-4 py-2 text-white placeholder-gray-400 focus:outline-none focus:border-blue-500">
                 </div>
             </div>
             
             <!-- Hidden inputs for variant data -->
             <input type="hidden" name="variants[${index}][combination]" value="${encodeURIComponent(JSON.stringify(variant))}">
             <input type="hidden" name="variants[${index}][combination_string]" value="${combinationString}">
         `;
         
         return variantDiv;
     }

    // Lưu giá trị các input hiện tại của variants theo combination_string
    function getVariantFormValues() {
        const values = {};
        document.querySelectorAll('.variant-checkbox').forEach(cb => {
            const idx = parseInt(cb.dataset.index);
            const comboInput = document.querySelector(`input[name="variants[${idx}][combination_string]"]`);
            const key = comboInput ? comboInput.value : `idx-${idx}`;
            const priceInput = document.querySelector(`input[name="variants[${idx}][price]"]`);
            const listPriceInput = document.querySelector(`input[name="variants[${idx}][list_price]"]`);
            const qtyInput = document.querySelector(`input[name="variants[${idx}][quantity]"]`);
            values[key] = {
                checked: cb.checked,
                price: priceInput?.value ?? '',
                list_price: listPriceInput?.value ?? '',
                quantity: qtyInput?.value ?? '',
            };
        });
        return values;
    }

    // Render variants list from allVariants, giữ lại giá trị đã nhập
    function renderVariants(savedValues = {}) {
        const variantsContainer = document.getElementById('variantsContainer');
        variantsContainer.innerHTML = '';
        variantIndex = 0;
        allVariants.forEach((variant, index) => {
            const combinationString = variant.map(item => `${item.name}: ${item.value}`).join(' / ');
            const preset = savedValues[combinationString] || {};
            variantsContainer.appendChild(createVariantInput(variant, index, preset));
            variantIndex++;
        });
        document.getElementById('variantsSection').style.display = allVariants.length ? 'block' : 'none';
        document.getElementById('totalVariants').textContent = allVariants.length;
        updateSelectedCount();
    }

    // Remove a variant by index and re-render (giữ lại dữ liệu các variant còn lại)
    function removeVariant(index) {
        const savedValues = getVariantFormValues();
        allVariants.splice(index, 1);
        renderVariants(savedValues);
        showNotification('Variant removed', 'info');
     }

    // Show notification
    function showNotification(message, type = 'info') {
        // Create notification element
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
        
        // Animate in
        setTimeout(() => {
            notification.classList.remove('translate-x-full');
        }, 100);
        
        // Remove after 3 seconds
        setTimeout(() => {
            notification.classList.add('translate-x-full');
            setTimeout(() => {
                document.body.removeChild(notification);
            }, 300);
        }, 3000);
    }

    // AJAX Form submission to prevent page reload
    document.getElementById('templateForm').addEventListener('submit', function(e) {
        e.preventDefault(); // Prevent normal form submission
        collectAttributesForSubmit(this);
        
        // Clear previous errors
        clearValidationErrors();
        
        // Client-side validation
        if (!validateForm()) {
            return false;
        }
        
        // Show loading state
        const submitBtn = this.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        submitBtn.disabled = true;
        submitBtn.innerHTML = `
            <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white inline" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            Creating template...
        `;
        
        // Prepare form data
        const formData = new FormData(this);
        
        // Submit via AJAX
        fetch(this.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Success - redirect to index page
                showNotification(data.message || 'Template created successfully!', 'success');
                setTimeout(() => {
                    window.location.href = data.redirect || '/product-templates';
                }, 1500);
            } else {
                // Validation errors
                showValidationErrors(data.errors || {});
                showNotification(data.message || 'An error occurred, please check again!', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('An error occurred when creating template!', 'error');
        })
        .finally(() => {
            // Reset submit button
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        });
    });

    // Client-side form validation
    function validateForm() {
        let isValid = true;
        const errors = [];
        
        // Check required fields
        const name = document.getElementById('name');
        if (!name.value.trim()) {
            showFieldError('name', 'Product template name is required');
            isValid = false;
        }
        
        const basePrice = document.getElementById('base_price');
        if (!basePrice.value || parseFloat(basePrice.value) < 0) {
            showFieldError('base_price', 'Base price must be a positive number');
            isValid = false;
        }
        
        // Check variants if they exist
        const variants = document.querySelectorAll('[name*="variants["]');
        if (variants.length > 0) {
            variants.forEach((variantContainer, index) => {
                const priceInput = variantContainer.querySelector('[name*="[price]"]');
                const quantityInput = variantContainer.querySelector('[name*="[quantity]"]');
                
                if (priceInput && (!priceInput.value || parseFloat(priceInput.value) < 0)) {
                    showFieldError(`variants.${index}.price`, 'Sale price must be a positive number');
                    isValid = false;
                }
                
                if (quantityInput && (!quantityInput.value || parseInt(quantityInput.value) < 0)) {
                        showFieldError(`variants.${index}.quantity`, 'Quantity must be a positive integer');
                    isValid = false;
                }
            });
        }
        
        return isValid;
    }
    
    // Show field-specific error
    function showFieldError(fieldName, message) {
        const field = document.querySelector(`[name="${fieldName}"]`) || 
                     document.querySelector(`[name*="${fieldName}"]`);
        
        if (field) {
            field.classList.add('border-red-500');
            
            // Remove existing error message
            const existingError = field.parentNode.querySelector('.field-error');
            if (existingError) {
                existingError.remove();
            }
            
            // Add new error message
            const errorDiv = document.createElement('div');
            errorDiv.className = 'field-error text-red-400 text-sm mt-1';
            errorDiv.textContent = message;
            field.parentNode.appendChild(errorDiv);
        }
    }
    
    // Clear all validation errors
    function clearValidationErrors() {
        // Remove red borders
        document.querySelectorAll('.border-red-500').forEach(field => {
            field.classList.remove('border-red-500');
        });
        
        // Remove error messages
        document.querySelectorAll('.field-error').forEach(error => {
            error.remove();
        });
        
        // Clear general error messages
        const errorContainer = document.querySelector('.validation-errors');
        if (errorContainer) {
            errorContainer.remove();
        }
    }
    
    // Show validation errors from server
    function showValidationErrors(errors) {
        clearValidationErrors();
        
        // Show general errors
        if (Object.keys(errors).length > 0) {
            const errorContainer = document.createElement('div');
            errorContainer.className = 'validation-errors bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4';
            
                let errorHtml = '<h4 class="font-bold mb-2">An error occurred:</h4><ul class="list-disc list-inside space-y-1">';
            
            Object.values(errors).forEach(errorMessages => {
                if (Array.isArray(errorMessages)) {
                    errorMessages.forEach(message => {
                        errorHtml += `<li>${message}</li>`;
                    });
                } else {
                    errorHtml += `<li>${errorMessages}</li>`;
                }
            });
            
            errorHtml += '</ul>';
            errorContainer.innerHTML = errorHtml;
            
            // Insert at the top of the form
            const form = document.getElementById('templateForm');
            form.parentNode.insertBefore(errorContainer, form);
        }
        
        // Show field-specific errors
        Object.keys(errors).forEach(fieldName => {
            const field = document.querySelector(`[name="${fieldName}"]`) || 
                         document.querySelector(`[name*="${fieldName}"]`);
            
            if (field) {
                field.classList.add('border-red-500');
                
                const errorMessages = errors[fieldName];
                const message = Array.isArray(errorMessages) ? errorMessages[0] : errorMessages;
                
                const errorDiv = document.createElement('div');
                errorDiv.className = 'field-error text-red-400 text-sm mt-1';
                errorDiv.textContent = message;
                field.parentNode.appendChild(errorDiv);
            }
        });
    }

         // Setup smart bulk edit functionality
     function setupSmartBulkEdit() {
         console.log('setupSmartBulkEdit called');
         try {
             // Generate smart filters
             console.log('Generating smart filters...');
             generateSmartFilters();
             
             // Setup quick selection buttons
             console.log('Setting up quick selection...');
             setupQuickSelection();
             
             // Setup bulk edit form
             console.log('Setting up bulk edit form...');
             setupBulkEditForm();
             console.log('setupSmartBulkEdit completed successfully');
         } catch (error) {
             console.error('Error in setupSmartBulkEdit:', error);
         }
     }
     
     // Generate smart filters
     function generateSmartFilters() {
         const smartFilters = document.getElementById('smartFilters');
         smartFilters.innerHTML = '';
         
         // Get all options from the form
         const options = document.querySelectorAll('[name*="[name]"]');
         const valuesInputs = document.querySelectorAll('[name*="[values_string]"]');

        // Helper buttons listener (attach once)
        if (!window.smartFiltersListenerAttachedCreate) {
            smartFilters.addEventListener('click', function(e) {
                const selectAllBtn = e.target.closest('[data-action="select-attr-all"]');
                const clearBtn = e.target.closest('[data-action="clear-attr"]');
                if (selectAllBtn) {
                    const attr = selectAllBtn.dataset.attribute;
                    selectAllValuesForAttribute(attr);
                }
                if (clearBtn) {
                    const attr = clearBtn.dataset.attribute;
                    clearValuesForAttribute(attr);
                }
            });
            window.smartFiltersListenerAttachedCreate = true;
        }
         
         options.forEach((option, index) => {
             const optionName = option.value.trim();
             const valuesString = valuesInputs[index].value.trim();
             
             if (optionName && valuesString) {
                 const values = valuesString.split(',').map(v => v.trim()).filter(v => v);
                 
                 // Create smart filter section
                 const filterDiv = document.createElement('div');
                 filterDiv.className = 'bg-gray-700 rounded p-3';
                 filterDiv.innerHTML = `
                    <div class="flex items-center justify-between mb-2">
                        <h6 class="text-white font-medium text-sm">${optionName}</h6>
                        <div class="flex items-center gap-2 text-xs">
                            <button type="button" data-action="select-attr-all" data-attribute="${optionName}"
                                    class="px-2 py-1 bg-blue-600 hover:bg-blue-500 text-white rounded">
                                Select all
                            </button>
                            <button type="button" data-action="clear-attr" data-attribute="${optionName}"
                                    class="px-2 py-1 bg-gray-600 hover:bg-gray-500 text-white rounded">
                                Clear
                            </button>
                        </div>
                    </div>
                     <div class="space-y-1">
                         ${values.map(value => `
                             <label class="flex items-center text-sm text-gray-300 cursor-pointer hover:bg-gray-600 rounded px-2 py-1 transition-colors">
                                 <input type="checkbox" class="multi-filter-checkbox mr-2 rounded border-gray-500 bg-gray-600 text-purple-500 focus:ring-purple-500" 
                                        data-attribute="${optionName}" data-value="${value}">
                                 ${value} <span class="text-gray-400 ml-1">(${countVariantsWithAttribute(optionName, value)})</span>
                             </label>
                         `).join('')}
                     </div>
                 `;
                 smartFilters.appendChild(filterDiv);
             }
         });
         
         // Add change handlers for multi-filter checkboxes
         document.addEventListener('change', function(e) {
             if (e.target.classList.contains('multi-filter-checkbox')) {
                 updateMultiSelectionCount();
             }
         });
     }

    // Select all values of a specific attribute
    function selectAllValuesForAttribute(attributeName) {
        document.querySelectorAll(`.multi-filter-checkbox[data-attribute="${attributeName}"]`).forEach(cb => cb.checked = true);
        updateMultiSelectionCount();
        showNotification(`Selected all values of ${attributeName}`, 'success');
    }

    // Clear values of a specific attribute
    function clearValuesForAttribute(attributeName) {
        document.querySelectorAll(`.multi-filter-checkbox[data-attribute="${attributeName}"]`).forEach(cb => cb.checked = false);
        updateMultiSelectionCount();
        showNotification(`Cleared values of ${attributeName}`, 'info');
     }
     
     // Count variants with specific attribute value
     function countVariantsWithAttribute(attributeName, attributeValue) {
         let count = 0;
         allVariants.forEach(variant => {
             const hasAttribute = variant.some(item => item.name === attributeName && item.value === attributeValue);
             if (hasAttribute) count++;
         });
         return count;
     }
     
     // Get selected multi-filter values
     function getSelectedMultiFilterValues() {
         const checkboxes = document.querySelectorAll('.multi-filter-checkbox:checked');
         return Array.from(checkboxes).map(checkbox => ({
             attribute: checkbox.dataset.attribute,
             value: checkbox.dataset.value
         }));
     }
     
     // Update multi-selection count
     function updateMultiSelectionCount() {
         const selectedCount = document.querySelectorAll('.multi-filter-checkbox:checked').length;
         document.getElementById('multiSelectionCount').textContent = selectedCount;
     }

function selectAllFilterValues() {
    document.querySelectorAll('.multi-filter-checkbox').forEach(cb => cb.checked = true);
    updateMultiSelectionCount();
    showNotification('Selected all attribute values', 'success');
}

function clearAllFilterValues() {
    document.querySelectorAll('.multi-filter-checkbox').forEach(cb => cb.checked = false);
    updateMultiSelectionCount();
    showNotification('Cleared attribute selections', 'info');
}

// Collect attribute inputs into a JSON string (additional payload)
function collectAttributesForSubmit(form) {
    const payload = {};
    const inputs = form.querySelectorAll('[name^="attributes["]');
    inputs.forEach(el => {
        const match = el.name.match(/^attributes\[(.+?)\](\[\])?$/);
        if (!match) return;
        const attrId = match[1];
        const isArray = !!match[2];

        if (el.type === 'checkbox') {
            if (!el.checked) return;
            payload[attrId] = payload[attrId] || [];
            payload[attrId].push(el.value);
        } else {
            const val = el.value?.trim();
            if (!val) return;
            if (isArray) {
                payload[attrId] = payload[attrId] || [];
                payload[attrId].push(val);
            } else {
                payload[attrId] = val;
            }
        }
    });

    let hidden = form.querySelector('input[name="attributes_json"]');
    if (!hidden) {
        hidden = document.createElement('input');
        hidden.type = 'hidden';
        hidden.name = 'attributes_json';
        form.appendChild(hidden);
    }
    hidden.value = JSON.stringify(payload);
    console.log('[ATTR][submit] payload', payload);
    return true;
}

// Inline handler fallback
function handleAttributesSubmit(form) {
    try {
        collectAttributesForSubmit(form);
    } catch (err) {
        console.error('[ATTR] handleAttributesSubmit error', err);
    }
    return true;
}
     
           // Select variants by multiple attribute values
      function selectVariantsByMultipleAttributeValues() {
          const selectedValues = getSelectedMultiFilterValues();
          
          if (selectedValues.length === 0) {
                showNotification('Please select at least one attribute value!', 'error');
              return;
          }
          
          // Get selected logic (AND or OR)
          const selectedLogic = document.querySelector('input[name="filterLogic"]:checked').value;
          
          const checkboxes = document.querySelectorAll('.variant-checkbox');
          
          checkboxes.forEach((checkbox, index) => {
              const variant = allVariants[index];
              
              // Group selected values by attribute
              const selectedByAttribute = {};
              selectedValues.forEach(selection => {
                  if (!selectedByAttribute[selection.attribute]) {
                      selectedByAttribute[selection.attribute] = [];
                  }
                  selectedByAttribute[selection.attribute].push(selection.value);
              });
              
              let shouldSelect = false;
              
              if (selectedLogic === 'AND') {
                  // Logic AND: Variant must match ALL selected attributes
                  shouldSelect = true;
                  
                  for (const [attributeName, requiredValues] of Object.entries(selectedByAttribute)) {
                      // Find the variant's value for this attribute
                      const variantAttribute = variant.find(item => item.name === attributeName);
                      
                      if (!variantAttribute) {
                          // If this attribute is required but variant doesn't have it
                          shouldSelect = false;
                          break;
                      }
                      
                      // Check if variant's value is in the required values for this attribute
                      if (!requiredValues.includes(variantAttribute.value)) {
                          shouldSelect = false;
                          break;
                      }
                  }
              } else {
                  // Logic OR: Variant must match ANY of the selected attribute values
                  shouldSelect = selectedValues.some(selection => 
                      variant.some(item => item.name === selection.attribute && item.value === selection.value)
                  );
              }
              
              checkbox.checked = shouldSelect;
          });
          
          updateSelectedCount();
          
          const selectedText = selectedValues.map(s => `${s.attribute}: ${s.value}`).join(', ');
          showNotification(`Selected variants with: ${selectedText} (Logic: ${selectedLogic})`, 'success');
      }
     
     // Clear multi-selection
     function clearMultiSelection() {
         const checkboxes = document.querySelectorAll('.multi-filter-checkbox');
         checkboxes.forEach(checkbox => {
             checkbox.checked = false;
         });
         
         updateMultiSelectionCount();
         showNotification('Attribute selection cleared', 'info');
     }
     
     // Setup quick selection buttons
     function setupQuickSelection() {
         const selectAllBtn = document.getElementById('selectAllBtn');
         const selectNoneBtn = document.getElementById('selectNoneBtn');
         const selectInverseBtn = document.getElementById('selectInverseBtn');
         
         selectAllBtn.addEventListener('click', function() {
             const checkboxes = document.querySelectorAll('.variant-checkbox');
             checkboxes.forEach(checkbox => checkbox.checked = true);
             updateSelectedCount();
             showNotification('All variants selected', 'success');
         });
         
         selectNoneBtn.addEventListener('click', function() {
             const checkboxes = document.querySelectorAll('.variant-checkbox');
             checkboxes.forEach(checkbox => checkbox.checked = false);
             updateSelectedCount();
             showNotification('All variants unselected', 'info');
         });
         
         selectInverseBtn.addEventListener('click', function() {
             const checkboxes = document.querySelectorAll('.variant-checkbox');
             checkboxes.forEach(checkbox => checkbox.checked = !checkbox.checked);
             updateSelectedCount();
             showNotification('Selection inverted', 'info');
         });
         
         // Update count when individual checkboxes change
         document.addEventListener('change', function(e) {
             if (e.target.classList.contains('variant-checkbox')) {
                 updateSelectedCount();
             }
         });
     }
     
     // Setup bulk edit form
     function setupBulkEditForm() {
         console.log('Setting up bulk edit form...');
         const applyBulkEditBtn = document.getElementById('applyBulkEdit');
         const applyBulkEditAllBtn = document.getElementById('applyBulkEditAll');
         const clearBulkFormBtn = document.getElementById('clearBulkForm');
         const applyMultiSelectionBtn = document.getElementById('applyMultiSelection');
         const clearMultiSelectionBtn = document.getElementById('clearMultiSelection');
         
         console.log('Found elements:', {
             applyBulkEditBtn: !!applyBulkEditBtn,
             applyBulkEditAllBtn: !!applyBulkEditAllBtn,
             clearBulkFormBtn: !!clearBulkFormBtn,
             applyMultiSelectionBtn: !!applyMultiSelectionBtn,
             clearMultiSelectionBtn: !!clearMultiSelectionBtn
         });
         
         applyBulkEditBtn.addEventListener('click', function() {
             console.log('Apply bulk edit button clicked!');
             const selectedVariants = getSelectedVariants();
             console.log('Selected variants:', selectedVariants);
             if (selectedVariants.length === 0) {
                 showNotification('Please select at least one variant!', 'error');
                 return;
             }
             applyBulkEdit(selectedVariants);
         });
         
         applyBulkEditAllBtn.addEventListener('click', function() {
             const allVariants = document.querySelectorAll('.variant-checkbox');
             const variantIndices = Array.from(allVariants).map((_, index) => index);
             applyBulkEdit(variantIndices);
         });
         
         clearBulkFormBtn.addEventListener('click', function() {
             document.getElementById('bulkPrice').value = '';
             document.getElementById('bulkListPrice').value = '';
             document.getElementById('bulkQuantity').value = '';
             document.getElementById('bulkImages').value = '';
                showNotification('Form cleared', 'info');
         });
         
         // Multi-selection buttons
         applyMultiSelectionBtn.addEventListener('click', function() {
             selectVariantsByMultipleAttributeValues();
         });
         
         clearMultiSelectionBtn.addEventListener('click', function() {
             clearMultiSelection();
         });
     }
     
     // Get selected variant indices
     function getSelectedVariants() {
         const checkboxes = document.querySelectorAll('.variant-checkbox:checked');
         return Array.from(checkboxes).map(checkbox => parseInt(checkbox.dataset.index));
     }
     
     // Update selected count
     function updateSelectedCount() {
         const selectedCount = document.querySelectorAll('.variant-checkbox:checked').length;
         document.getElementById('selectedCount').textContent = selectedCount;
     }
     
     // Apply bulk edit to variants
     function applyBulkEdit(variantIndices) {
         const bulkPrice = document.getElementById('bulkPrice').value;
         const bulkListPrice = document.getElementById('bulkListPrice').value;
         const bulkQuantity = document.getElementById('bulkQuantity').value;
         const bulkImages = document.getElementById('bulkImages').files; // Sửa từ bulkImage thành bulkImages
         
         if (!bulkPrice && !bulkListPrice && !bulkQuantity && bulkImages.length === 0) {
             showNotification('Please enter at least one information to apply!', 'error');
             return;
         }
         
         console.log('Applying bulk edit to variants:', variantIndices);
         console.log('Bulk values:', { bulkPrice, bulkListPrice, bulkQuantity, bulkImagesCount: bulkImages.length });
         
         variantIndices.forEach(index => {
             console.log(`Processing variant ${index}`);
             
             if (bulkPrice) {
                 const priceInput = document.querySelector(`input[name="variants[${index}][price]"]`);
                 if (priceInput) {
                     priceInput.value = bulkPrice;
                     console.log(`Set price for variant ${index}: ${bulkPrice}`);
                 } else {
                     console.error(`Price input not found for variant ${index}`);
                 }
             }
             
             if (bulkListPrice) {
                 const listPriceInput = document.querySelector(`input[name="variants[${index}][list_price]"]`);
                 if (listPriceInput) {
                     listPriceInput.value = bulkListPrice;
                     console.log(`Set list price for variant ${index}: ${bulkListPrice}`);
                 } else {
                     console.error(`List price input not found for variant ${index}`);
                 }
             }
             
             if (bulkQuantity) {
                 const quantityInput = document.querySelector(`input[name="variants[${index}][quantity]"]`);
                 if (quantityInput) {
                     quantityInput.value = bulkQuantity;
                     console.log(`Set quantity for variant ${index}: ${bulkQuantity}`);
                 } else {
                     console.error(`Quantity input not found for variant ${index}`);
                 }
             }
             
             // Handle bulk images for variants
             if (bulkImages.length > 0) {
                 const firstFile = bulkImages[0]; // Take the first selected file
                 
                 // Apply the same file to all selected variants
                 variantIndices.forEach(index => {
                     const imageInput = document.querySelector(`input[name="variants[${index}][image_file]"]`);
                     if (imageInput) {
                         // Create a new DataTransfer to assign the file
                         const dt = new DataTransfer();
                         dt.items.add(firstFile);
                         imageInput.files = dt.files;
                         console.log(`Set image file for variant ${index}:`, firstFile.name);
                     } else {
                         console.error(`Image file input not found for variant ${index}`);
                     }
                 });
             }
         });
         
                showNotification(`Information applied to ${variantIndices.length} variants!`, 'success');
         
         // Clear bulk edit inputs
         document.getElementById('bulkPrice').value = '';
         document.getElementById('bulkListPrice').value = '';
         document.getElementById('bulkQuantity').value = '';
         document.getElementById('bulkImages').value = '';
     }
     
     // Initialize on page load
     document.addEventListener('DOMContentLoaded', function() {
         console.log('DOM loaded, initializing...');
         updateVariantsSection();
         
         // Test if button exists
         const addOptionBtn = document.getElementById('addOption');
         console.log('Add option button found:', addOptionBtn);
         
         if (addOptionBtn) {
             console.log('Button text:', addOptionBtn.textContent);
         }
         

     });
     

</script>
@endpush
@endsection
