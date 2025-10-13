@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-slate-900 text-gray-100">
    <div class="container mx-auto px-4 py-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center mb-6">
                <a href="{{ route('backups.index') }}" class="text-blue-400 hover:text-blue-300 mr-4">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                </a>
                <div>
                    <h1 class="text-3xl font-bold text-white mb-2">Tạo Backup Mới</h1>
                    <p class="text-gray-400">Tạo bản sao lưu dữ liệu hệ thống</p>
                </div>
            </div>
        </div>

        <!-- Error Messages -->
        @if($errors->any())
            <div class="bg-red-500/20 border border-red-500/50 text-red-400 px-4 py-3 rounded-lg mb-6">
                <ul class="list-disc list-inside">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Left Column - Form -->
            <div class="lg:col-span-2">
                <div class="bg-gray-800 rounded-xl border border-gray-700 p-6">
                    <div class="flex items-center mb-6">
                        <div class="w-10 h-10 bg-blue-500/20 rounded-lg flex items-center justify-center mr-3">
                            <svg class="w-6 h-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                            </svg>
                        </div>
                        <h2 class="text-xl font-semibold text-white">Thông tin Backup</h2>
                    </div>

                    <form method="POST" action="{{ route('backups.store') }}" class="space-y-6">
                        @csrf

                        <!-- Description -->
                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-300 mb-2">Mô tả</label>
                            <input type="text" id="description" name="description" value="{{ old('description') }}"
                                   class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-2 text-white placeholder-gray-400 focus:outline-none focus:border-blue-500"
                                   placeholder="Mô tả ngắn gọn về backup này">
                            <p class="text-sm text-gray-400 mt-1">Mô tả giúp bạn nhớ lý do tạo backup này</p>
                        </div>

                        <!-- Compression Type -->
                        <div>
                            <label for="compression_type" class="block text-sm font-medium text-gray-300 mb-2">Loại nén</label>
                            <select id="compression_type" name="compression_type"
                                    class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-2 text-white focus:outline-none focus:border-blue-500">
                                <option value="gzip" {{ old('compression_type', 'gzip') === 'gzip' ? 'selected' : '' }}>Gzip (Khuyến nghị)</option>
                                <option value="none" {{ old('compression_type') === 'none' ? 'selected' : '' }}>Không nén</option>
                            </select>
                            <p class="text-sm text-gray-400 mt-1">Gzip giúp giảm kích thước file backup</p>
                        </div>

                        <!-- Encryption -->
                        <div>
                            <div class="flex items-center mb-2">
                                <input type="checkbox" id="is_encrypted" name="is_encrypted" value="1" {{ old('is_encrypted') ? 'checked' : '' }}
                                       class="w-4 h-4 text-blue-600 bg-gray-700 border-gray-600 rounded focus:ring-blue-500 focus:ring-2">
                                <label for="is_encrypted" class="ml-2 text-sm font-medium text-gray-300">Mã hóa backup</label>
                            </div>
                            <p class="text-sm text-gray-400 ml-6">Mã hóa để bảo vệ dữ liệu nhạy cảm</p>
                        </div>

                        <!-- Encryption Key -->
                        <div id="encryption_key_section" class="{{ old('is_encrypted') ? '' : 'hidden' }}">
                            <label for="encryption_key" class="block text-sm font-medium text-gray-300 mb-2">Khóa mã hóa</label>
                            <input type="password" id="encryption_key" name="encryption_key" value="{{ old('encryption_key') }}"
                                   class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-2 text-white placeholder-gray-400 focus:outline-none focus:border-blue-500"
                                   placeholder="Nhập khóa mã hóa (ít nhất 8 ký tự)">
                            <p class="text-sm text-gray-400 mt-1">Lưu ý: Khóa này cần thiết để restore backup</p>
                        </div>

                        <!-- Excluded Tables -->
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">Bảng loại trừ</label>
                            <div class="bg-gray-700 border border-gray-600 rounded-lg p-4 max-h-48 overflow-y-auto">
                                <div class="grid grid-cols-2 gap-3">
                                    @foreach($tables as $table)
                                        <div class="flex items-center">
                                            <input type="checkbox" id="excluded_tables_{{ $table }}" 
                                                   name="excluded_tables[]" value="{{ $table }}"
                                                   {{ in_array($table, old('excluded_tables', [])) ? 'checked' : '' }}
                                                   class="w-4 h-4 text-blue-600 bg-gray-600 border-gray-500 rounded focus:ring-blue-500 focus:ring-2">
                                            <label for="excluded_tables_{{ $table }}" class="ml-2 text-sm text-gray-300">{{ $table }}</label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                            <p class="text-sm text-gray-400 mt-1">Chọn các bảng không cần backup</p>
                        </div>

                        <!-- Submit Button -->
                        <div class="flex justify-end pt-4">
                            <a href="{{ route('backups.index') }}" 
                               class="px-6 py-2 border border-gray-600 text-gray-300 rounded-lg hover:bg-gray-700 transition-colors duration-200 mr-3">
                                Hủy
                            </a>
                            <button type="submit" 
                                    class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition-colors duration-200">
                                <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                                </svg>
                                Tạo Backup
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Right Column - Info -->
            <div class="lg:col-span-1">
                <!-- System Status -->
                <div class="bg-gray-800 rounded-xl border border-gray-700 p-6 mb-6">
                    <h3 class="text-lg font-semibold text-white mb-4">Trạng thái hệ thống</h3>
                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-gray-400">Tổng backup:</span>
                            <span class="text-white font-medium">{{ $status['total_backups'] }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-400">Thành công:</span>
                            <span class="text-green-400 font-medium">{{ $status['successful_backups'] }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-400">Thất bại:</span>
                            <span class="text-red-400 font-medium">{{ $status['failed_backups'] }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-400">Tổng dung lượng:</span>
                            <span class="text-white font-medium">{{ $status['total_size'] }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-400">Dung lượng còn trống:</span>
                            <span class="text-white font-medium">{{ $status['available_space'] }}</span>
                        </div>
                    </div>
                </div>

                <!-- Tips -->
                <div class="bg-blue-500/10 border border-blue-500/20 rounded-xl p-6">
                    <h3 class="text-lg font-semibold text-blue-400 mb-4">💡 Lưu ý quan trọng</h3>
                    <ul class="space-y-2 text-sm text-blue-300">
                        <li>• Backup sẽ bao gồm tất cả dữ liệu quan trọng</li>
                        <li>• Nên tạo backup định kỳ hàng ngày</li>
                        <li>• Lưu trữ backup ở nhiều nơi khác nhau</li>
                        <li>• Kiểm tra backup sau khi tạo</li>
                        <li>• Mã hóa backup nếu chứa dữ liệu nhạy cảm</li>
                    </ul>
                </div>

                <!-- Latest Backup -->
                @if($status['latest_backup'])
                    <div class="bg-gray-800 rounded-xl border border-gray-700 p-6 mt-6">
                        <h3 class="text-lg font-semibold text-white mb-4">Backup gần nhất</h3>
                        <div class="space-y-2">
                            <div class="text-sm">
                                <span class="text-gray-400">File:</span>
                                <span class="text-white">{{ $status['latest_backup']->filename }}</span>
                            </div>
                            <div class="text-sm">
                                <span class="text-gray-400">Kích thước:</span>
                                <span class="text-white">{{ $status['latest_backup']->formatted_file_size }}</span>
                            </div>
                            <div class="text-sm">
                                <span class="text-gray-400">Thời gian:</span>
                                <span class="text-white">{{ $status['latest_backup']->created_at->format('d/m/Y H:i') }}</span>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('is_encrypted').addEventListener('change', function() {
    const encryptionSection = document.getElementById('encryption_key_section');
    if (this.checked) {
        encryptionSection.classList.remove('hidden');
        document.getElementById('encryption_key').required = true;
    } else {
        encryptionSection.classList.add('hidden');
        document.getElementById('encryption_key').required = false;
    }
});
</script>
@endsection
