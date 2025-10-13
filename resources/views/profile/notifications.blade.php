@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-slate-900 text-gray-100">
    <div class="container mx-auto px-4 py-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center mb-6">
                <a href="{{ route('profile.edit') }}" class="text-blue-400 hover:text-blue-300 mr-4">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                </a>
                <div>
                    <h1 class="text-3xl font-bold text-white mb-2">Thông báo</h1>
                    <p class="text-gray-400">Quản lý thông báo và cài đặt</p>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Notification Settings -->
            <div class="lg:col-span-1">
                <div class="bg-gray-800 rounded-xl border border-gray-700 p-6 mb-6">
                    <h2 class="text-xl font-semibold text-white mb-4">Cài đặt thông báo</h2>
                    <div class="space-y-4">
                        <div class="flex items-center justify-between p-3 bg-gray-700 rounded-lg">
                            <div>
                                <h3 class="text-white font-medium">Email thông báo</h3>
                                <p class="text-gray-400 text-sm">Nhận thông báo qua email</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" class="sr-only peer" checked>
                                <div class="w-11 h-6 bg-gray-600 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-800 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                            </label>
                        </div>

                        <div class="flex items-center justify-between p-3 bg-gray-700 rounded-lg">
                            <div>
                                <h3 class="text-white font-medium">Thông báo hệ thống</h3>
                                <p class="text-gray-400 text-sm">Thông báo trong ứng dụng</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" class="sr-only peer" checked>
                                <div class="w-11 h-6 bg-gray-600 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-800 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                            </label>
                        </div>

                        <div class="flex items-center justify-between p-3 bg-gray-700 rounded-lg">
                            <div>
                                <h3 class="text-white font-medium">Thông báo bảo mật</h3>
                                <p class="text-gray-400 text-sm">Cảnh báo về bảo mật</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" class="sr-only peer" checked>
                                <div class="w-11 h-6 bg-gray-600 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-800 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="bg-gray-800 rounded-xl border border-gray-700 p-6">
                    <h3 class="text-lg font-semibold text-white mb-4">Thao tác nhanh</h3>
                    <div class="space-y-2">
                        <button class="w-full flex items-center p-3 text-gray-300 hover:bg-gray-700 rounded-lg transition-colors duration-200">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            Đánh dấu tất cả đã đọc
                        </button>
                        <button class="w-full flex items-center p-3 text-gray-300 hover:bg-gray-700 rounded-lg transition-colors duration-200">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                            Xóa tất cả
                        </button>
                    </div>
                </div>
            </div>

            <!-- Notifications List -->
            <div class="lg:col-span-2">
                <div class="bg-gray-800 rounded-xl border border-gray-700 p-6">
                    <div class="flex items-center justify-between mb-6">
                        <div class="flex items-center">
                            <div class="w-10 h-10 bg-blue-500/20 rounded-lg flex items-center justify-center mr-3">
                                <svg class="w-6 h-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5zM4.19 4.19A2 2 0 004 6v10a2 2 0 002 2h10a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-1.81 1.19z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 9h.01M15 9h.01"></path>
                                </svg>
                            </div>
                            <h2 class="text-xl font-semibold text-white">Thông báo</h2>
                        </div>
                        <div class="flex items-center space-x-2">
                            <button class="px-3 py-1 bg-blue-600 hover:bg-blue-700 text-white text-sm rounded-lg transition-colors duration-200">
                                Lọc
                            </button>
                            <button class="px-3 py-1 bg-gray-600 hover:bg-gray-700 text-white text-sm rounded-lg transition-colors duration-200">
                                Tất cả
                            </button>
                        </div>
                    </div>

                    @if($notifications->count() > 0)
                        <div class="space-y-3">
                            @foreach($notifications as $notification)
                                <div class="flex items-start space-x-4 p-4 bg-gray-700 rounded-lg {{ !$notification->read_at ? 'border-l-4 border-blue-500' : '' }}">
                                    <div class="w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0
                                        @if($notification->type === 'security') bg-red-500/20
                                        @elseif($notification->type === 'system') bg-blue-500/20
                                        @elseif($notification->type === 'update') bg-green-500/20
                                        @else bg-gray-500/20 @endif">
                                        @if($notification->type === 'security')
                                            <svg class="w-4 h-4 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                            </svg>
                                        @elseif($notification->type === 'system')
                                            <svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            </svg>
                                        @elseif($notification->type === 'update')
                                            <svg class="w-4 h-4 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                            </svg>
                                        @else
                                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                        @endif
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-start justify-between">
                                            <div class="flex-1">
                                                <p class="text-white font-medium">{{ $notification->title ?? 'Thông báo' }}</p>
                                                <p class="text-gray-400 text-sm mt-1">{{ $notification->message ?? 'Nội dung thông báo' }}</p>
                                                <p class="text-gray-500 text-xs mt-2">{{ $notification->created_at->diffForHumans() }}</p>
                                            </div>
                                            <div class="flex items-center space-x-2 ml-4">
                                                @if(!$notification->read_at)
                                                    <form method="POST" action="{{ route('profile.notifications.read', $notification->id) }}" class="inline">
                                                        @csrf
                                                        <button type="submit" class="text-blue-400 hover:text-blue-300 text-sm">
                                                            Đánh dấu đã đọc
                                                        </button>
                                                    </form>
                                                @endif
                                                <button class="text-red-400 hover:text-red-300 text-sm">
                                                    Xóa
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <!-- Pagination -->
                        <div class="mt-6">
                            <div class="flex items-center justify-between">
                                <p class="text-sm text-gray-400">Hiển thị {{ $notifications->count() }} thông báo</p>
                                <div class="flex items-center space-x-2">
                                    <button class="px-3 py-1 bg-gray-600 hover:bg-gray-700 text-white text-sm rounded-lg transition-colors duration-200">
                                        Trước
                                    </button>
                                    <span class="text-sm text-gray-400">Trang 1</span>
                                    <button class="px-3 py-1 bg-gray-600 hover:bg-gray-700 text-white text-sm rounded-lg transition-colors duration-200">
                                        Sau
                                    </button>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="text-center py-12">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5zM4.19 4.19A2 2 0 004 6v10a2 2 0 002 2h10a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-1.81 1.19z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 9h.01M15 9h.01"></path>
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-300">Không có thông báo</h3>
                            <p class="mt-1 text-sm text-gray-400">Bạn đã đọc tất cả thông báo.</p>
                        </div>
                    @endif
                </div>

                <!-- Notification Types -->
                <div class="bg-gray-800 rounded-xl border border-gray-700 p-6 mt-6">
                    <h2 class="text-xl font-semibold text-white mb-4">Loại thông báo</h2>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="bg-gray-700 rounded-lg p-4">
                            <div class="flex items-center mb-2">
                                <div class="w-6 h-6 bg-red-500/20 rounded-full flex items-center justify-center mr-2">
                                    <svg class="w-4 h-4 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                    </svg>
                                </div>
                                <h3 class="text-white font-medium">Bảo mật</h3>
                            </div>
                            <p class="text-gray-400 text-sm">Cảnh báo về bảo mật tài khoản</p>
                        </div>

                        <div class="bg-gray-700 rounded-lg p-4">
                            <div class="flex items-center mb-2">
                                <div class="w-6 h-6 bg-blue-500/20 rounded-full flex items-center justify-center mr-2">
                                    <svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    </svg>
                                </div>
                                <h3 class="text-white font-medium">Hệ thống</h3>
                            </div>
                            <p class="text-gray-400 text-sm">Thông báo từ hệ thống</p>
                        </div>

                        <div class="bg-gray-700 rounded-lg p-4">
                            <div class="flex items-center mb-2">
                                <div class="w-6 h-6 bg-green-500/20 rounded-full flex items-center justify-center mr-2">
                                    <svg class="w-4 h-4 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                    </svg>
                                </div>
                                <h3 class="text-white font-medium">Cập nhật</h3>
                            </div>
                            <p class="text-gray-400 text-sm">Thông báo cập nhật</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
