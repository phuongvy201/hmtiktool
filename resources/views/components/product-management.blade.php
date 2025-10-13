<div class="bg-gray-800 rounded-xl border border-gray-700 hover:border-gray-600 transition-all duration-300 group">
    <div class="p-6">
        <div class="flex items-center mb-4">
            <div class="w-10 h-10 bg-indigo-500/20 rounded-lg flex items-center justify-center mr-3">
                <svg class="w-6 h-6 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                </svg>
            </div>
            <h3 class="text-lg font-semibold text-white">Quản lý Sản phẩm</h3>
        </div>
        <p class="text-gray-400 mb-4">Quản lý template và sản phẩm của team</p>
        
        <div class="space-y-3">
            @can('view-product-templates')
            <a href="{{ route('product-templates.index') }}" 
               class="block w-full bg-gray-700 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition-colors duration-200 text-center text-sm">
                <i class="fas fa-layer-group mr-2"></i>Quản lý Templates
            </a>
            @endcan
            
            @can('view-products')
            <a href="{{ route('products.index') }}" 
               class="block w-full bg-gray-700 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition-colors duration-200 text-center text-sm">
                <i class="fas fa-box mr-2"></i>Quản lý Sản phẩm
            </a>
            @endcan
            
            @can('create-products')
            <a href="{{ route('products.create') }}" 
               class="block w-full bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg transition-colors duration-200 text-center text-sm">
                <i class="fas fa-plus mr-2"></i>Tạo sản phẩm mới
            </a>
            @endcan
        </div>
        
        <!-- Quick Stats -->
        <div class="mt-4 pt-4 border-t border-gray-700">
            <div class="grid grid-cols-2 gap-4 text-sm">
                <div class="text-center">
                    <div class="text-indigo-400 font-semibold">{{ $templateCount ?? 0 }}</div>
                    <div class="text-gray-500">Templates</div>
                </div>
                <div class="text-center">
                    <div class="text-green-400 font-semibold">{{ $productCount ?? 0 }}</div>
                    <div class="text-gray-500">Sản phẩm</div>
                </div>
            </div>
        </div>
    </div>
</div>
