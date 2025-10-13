@extends('layouts.app')

@section('title', 'Product Templates')

@push('styles')
<style>
    .line-clamp-3 {
        display: -webkit-box;
        -webkit-line-clamp: 3;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    
    .whitespace-pre-wrap {
        white-space: pre-wrap;
    }
</style>
@endpush

@section('content')
<div class="min-h-screen bg-slate-900 text-gray-100">
    <div class="container mx-auto px-4 py-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-3xl font-bold text-white mb-2">{{ __('Product Templates') }}</h1>
                    <p class="text-gray-400">Quản lý template sản phẩm</p>
                </div>
                <a href="{{ route('product-templates.create') }}" 
                   class="px-4 py-2 bg-blue-600 hover:bg-blue-500 text-white rounded-lg font-medium transition-colors duration-200 flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    Tạo Template Mới
                </a>
            </div>

            @if(session('success'))
                <div class="bg-green-900 border border-green-700 text-green-100 px-4 py-3 rounded-lg mb-6">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="bg-red-900 border border-red-700 text-red-100 px-4 py-3 rounded-lg mb-6">
                    {{ session('error') }}
                </div>
            @endif
        </div>

        @if($templates->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($templates as $template)
                    <div class="bg-gray-800 border border-gray-700 rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 hover:border-gray-600">
                                    <!-- Template Image -->
                                    @php
                                        $firstImage = null;
                                        if ($template->images && count($template->images) > 0) {
                                            $firstImage = $template->images[0];
                                        }
                                    @endphp
                                    @if($firstImage)
                                        <div class="aspect-w-16 aspect-h-9 bg-gray-700 rounded-t-xl overflow-hidden">
                                            <img src="{{ $firstImage }}" alt="{{ $template->name }}" 
                                                 class="w-full h-48 object-cover hover:scale-105 transition-transform duration-300">
                                        </div>
                                    @else
                                        <div class="h-48 bg-gray-700 rounded-t-xl flex items-center justify-center">
                                            <svg class="w-16 h-16 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                            </svg>
                                        </div>
                                    @endif
                                    
                                    <div class="p-6">
                                        <!-- Header với ID và Status -->
                                        <div class="flex justify-between items-start mb-3">
                                            <div>
                                                <div class="text-xs text-gray-400 mb-1">ID: #{{ $template->id }}</div>
                                                <h3 class="text-lg font-semibold text-white">{{ $template->name }}</h3>
                                            </div>
                                            <div class="flex flex-col space-y-1">
                                                @if($template->status === 'draft')
                                                    <span class="px-2 py-1 text-xs font-medium bg-yellow-900 text-yellow-100 rounded-full text-center">
                                                        Nháp
                                                    </span>
                                                @else
                                                    <span class="px-2 py-1 text-xs font-medium bg-green-900 text-green-100 rounded-full text-center">
                                                        Đã xuất bản
                                                    </span>
                                                @endif
                                                <span class="px-2 py-1 text-xs font-medium bg-blue-900 text-blue-100 rounded-full text-center">
                                                    {{ $template->category }}
                                                </span>
                                            </div>
                                        </div>
                                        
                                        @if($template->description)
                                            <div class="text-gray-300 text-sm mb-4 line-clamp-3 rich-content-preview">
                                                {!! strip_tags($template->description, '<strong><em><u>') !!}
                                            </div>
                                        @endif

                                        <div class="space-y-2 mb-4">
                                            <div class="flex justify-between text-sm">
                                                <span class="text-gray-400">Giá cơ bản:</span>
                                                <span class="font-medium text-green-400">{{ $template->base_price }}</span>
                                            </div>
                                            @if($template->list_price)
                                                <div class="flex justify-between text-sm">
                                                    <span class="text-gray-400">Giá niêm yết:</span>
                                                    <span class="font-medium text-white">{{ $template->list_price }}</span>
                                                </div>
                                            @endif
                                        </div>

                                        <div class="flex items-center justify-between text-sm text-gray-400 mb-4">
                                            <span>{{ $template->options->count() }} options</span>
                                            <span>{{ $template->variants->count() }} variants</span>
                                        </div>
                                        
                                        @if($template->description)
                                            <div class="mb-4">
                                                <p class="text-xs text-gray-400 mb-1">Mô tả:</p>
                                                <div class="text-sm text-gray-300 bg-gray-700 rounded p-2 border border-gray-600 max-h-20 overflow-hidden">
                                                    <div class="whitespace-pre-wrap line-clamp-3">
                                                        {{ \App\Helpers\TextHelper::getFirstParagraph($template->description) }}
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                        
                                        <!-- Hiển thị thông tin người tạo -->
                                        <div class="text-xs text-gray-400 mb-4 p-3 bg-gray-700 rounded-lg border border-gray-600">
                                            <div class="flex items-center justify-between">
                                                <span>Tạo bởi: {{ $template->user->name ?? 'N/A' }}</span>
                                                <span>{{ $template->created_at->format('d/m/Y H:i') }}</span>
                                            </div>
                                            @if(auth()->user()->hasRole('team-admin') && $template->user_id !== auth()->id())
                                                <div class="mt-1 text-orange-400">
                                                    <svg class="inline w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                                                    </svg>
                                                    Template của thành viên khác
                                                </div>
                                            @endif
                                        </div>

                                        <div class="flex flex-col space-y-3">
                                            <!-- Main Actions -->
                                            <div class="flex space-x-2">
                                                <a href="{{ route('product-templates.show', $template) }}" 
                                                   class="flex-1 bg-gray-700 hover:bg-gray-600 text-gray-100 text-center py-2 px-3 rounded-lg text-sm transition-colors duration-200 flex items-center justify-center">
                                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                                    </svg>
                                                    Xem chi tiết
                                                </a>
                                                @can('update', $template)
                                                    <a href="{{ route('product-templates.edit', $template) }}" 
                                                       class="flex-1 bg-blue-600 hover:bg-blue-500 text-white text-center py-2 px-3 rounded-lg text-sm transition-colors duration-200 flex items-center justify-center">
                                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                        </svg>
                                                        Chỉnh sửa
                                                    </a>
                                                @endcan
                                            </div>
                                            
                                            <!-- Secondary Actions -->
                                            <div class="flex space-x-2">
                                                <form action="{{ route('product-templates.duplicate', $template) }}" method="POST" class="flex-1">
                                                    @csrf
                                                    <button type="submit" 
                                                            class="w-full bg-green-600 hover:bg-green-500 text-white text-center py-2 px-3 rounded-lg text-sm transition-colors duration-200 flex items-center justify-center">
                                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                                                        </svg>
                                                        Copy
                                                    </button>
                                                </form>
                                                @can('delete', $template)
                                                    <form action="{{ route('product-templates.destroy', $template) }}" method="POST" 
                                                          class="flex-1" onsubmit="return confirm('Bạn có chắc chắn muốn xóa template này?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" 
                                                                class="w-full bg-red-600 hover:bg-red-500 text-white text-center py-2 px-3 rounded-lg text-sm transition-colors duration-200 flex items-center justify-center">
                                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                            </svg>
                                                            Xóa
                                                        </button>
                                                    </form>
                                                @endcan
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="mt-8">
                            {{ $templates->links() }}
                        </div>
                    @else
                        <div class="text-center py-16">
                            <div class="text-gray-500 mb-6">
                                <svg class="mx-auto h-16 w-16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                            </div>
                            <h3 class="text-xl font-semibold text-white mb-3">Chưa có template nào</h3>
                            <p class="text-gray-400 mb-8 text-lg">Bắt đầu tạo template sản phẩm đầu tiên của bạn.</p>
                            <a href="{{ route('product-templates.create') }}" 
                               class="bg-blue-600 hover:bg-blue-500 text-white font-medium py-3 px-6 rounded-lg transition-colors duration-200 inline-flex items-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                </svg>
                                Tạo Template Đầu Tiên
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    /* Dark theme styles for pagination */
    .pagination {
        @apply flex justify-center space-x-1;
    }
    
    .pagination .page-item .page-link {
        @apply px-3 py-2 text-sm font-medium text-gray-300 bg-gray-800 border border-gray-700 rounded-lg hover:bg-gray-700 hover:text-white transition-colors duration-200;
    }
    
    .pagination .page-item.active .page-link {
        @apply bg-blue-600 text-white border-blue-600;
    }
    
    .pagination .page-item.disabled .page-link {
        @apply text-gray-500 bg-gray-800 border-gray-700 cursor-not-allowed;
    }
    
    /* Rich content preview styles for dark theme */
    .rich-content-preview {
        line-height: 1.5;
    }
    
    .rich-content-preview strong {
        @apply text-white font-semibold;
    }
    
    .rich-content-preview em {
        @apply text-gray-300 italic;
    }
    
    .rich-content-preview u {
        @apply text-gray-300 underline;
    }
    
    /* Line clamp for description */
    .line-clamp-3 {
        display: -webkit-box;
        -webkit-line-clamp: 3;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
</style>
@endpush
