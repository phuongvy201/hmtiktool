@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-slate-900 text-gray-100">
    <div class="container mx-auto px-4 py-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h1 class="text-3xl font-bold text-white mb-2">Thêm Thành viên Mới</h1>
                    <p class="text-gray-400">Thêm thành viên mới vào team: <span class="text-blue-400 font-medium">{{ auth()->user()->team->name ?? 'N/A' }}</span></p>
                </div>
                <a href="{{ route('team-admin.users.index') }}" class="bg-gray-600 hover:bg-gray-500 text-white px-6 py-3 rounded-lg font-medium transition-colors duration-200 flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Quay lại
                </a>
            </div>
        </div>

        <!-- Form -->
        <div class="max-w-2xl mx-auto">
            <div class="bg-gray-800 rounded-xl border border-gray-700 p-6">
                <form method="POST" action="{{ route('team-admin.users.store') }}">
                    @csrf

                    <!-- Name -->
                    <div class="mb-6">
                        <label for="name" class="block text-sm font-medium text-gray-300 mb-2">Họ và tên</label>
                        <input type="text" id="name" name="name" value="{{ old('name') }}" required
                               class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-3 text-white placeholder-gray-400 focus:outline-none focus:border-blue-500 @error('name') border-red-500 @enderror"
                               placeholder="Nhập họ và tên">
                        @error('name')
                            <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Email -->
                    <div class="mb-6">
                        <label for="email" class="block text-sm font-medium text-gray-300 mb-2">Email</label>
                        <input type="email" id="email" name="email" value="{{ old('email') }}" required
                               class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-3 text-white placeholder-gray-400 focus:outline-none focus:border-blue-500 @error('email') border-red-500 @enderror"
                               placeholder="Nhập email">
                        @error('email')
                            <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Password -->
                    <div class="mb-6">
                        <label for="password" class="block text-sm font-medium text-gray-300 mb-2">Mật khẩu</label>
                        <input type="password" id="password" name="password" required
                               class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-3 text-white placeholder-gray-400 focus:outline-none focus:border-blue-500 @error('password') border-red-500 @enderror"
                               placeholder="Nhập mật khẩu (tối thiểu 8 ký tự)">
                        @error('password')
                            <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Password Confirmation -->
                    <div class="mb-6">
                        <label for="password_confirmation" class="block text-sm font-medium text-gray-300 mb-2">Xác nhận mật khẩu</label>
                        <input type="password" id="password_confirmation" name="password_confirmation" required
                               class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-3 text-white placeholder-gray-400 focus:outline-none focus:border-blue-500"
                               placeholder="Nhập lại mật khẩu">
                    </div>

                    <!-- Role -->
                    <div class="mb-6">
                        <label for="role_id" class="block text-sm font-medium text-gray-300 mb-2">Vai trò</label>
                        <select id="role_id" name="role_id" required
                                class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-3 text-white focus:outline-none focus:border-blue-500 @error('role_id') border-red-500 @enderror">
                            <option value="">Chọn vai trò</option>
                            @foreach($roles as $role)
                                <option value="{{ $role->id }}" {{ old('role_id') == $role->id ? 'selected' : '' }}>
                                    {{ $role->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('role_id')
                            <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Team Info (Read-only) -->
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-300 mb-2">Team</label>
                        <div class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-3 text-gray-300">
                            {{ auth()->user()->team->name ?? 'N/A' }}
                        </div>
                        <p class="mt-1 text-sm text-gray-400">Thành viên sẽ được tự động thêm vào team của bạn</p>
                    </div>

                    <!-- Submit Button -->
                    <div class="flex justify-end space-x-4">
                        <a href="{{ route('team-admin.users.index') }}" 
                           class="bg-gray-600 hover:bg-gray-500 text-white px-6 py-3 rounded-lg font-medium transition-colors duration-200">
                            Hủy
                        </a>
                        <button type="submit" 
                                class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-medium transition-colors duration-200 flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                            Thêm Thành viên
                        </button>
                    </div>
                </form>
            </div>

            <!-- Help Information -->
            <div class="mt-6 bg-blue-500/10 border border-blue-500/20 rounded-lg p-4">
                <div class="flex items-start">
                    <svg class="w-5 h-5 text-blue-400 mt-0.5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <div>
                        <h3 class="text-blue-400 font-medium mb-1">Lưu ý quan trọng</h3>
                        <ul class="text-sm text-gray-300 space-y-1">
                            <li>• Thành viên mới sẽ được tự động thêm vào team của bạn</li>
                            <li>• Mật khẩu phải có ít nhất 8 ký tự</li>
                            <li>• Email phải là duy nhất trong hệ thống</li>
                            <li>• Thành viên sẽ nhận được email xác thực tài khoản</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
