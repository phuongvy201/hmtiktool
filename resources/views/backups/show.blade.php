@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-slate-900 text-gray-100">
    <div class="container mx-auto px-4 py-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center justify-between mb-6">
                <div class="flex items-center">
                    <a href="{{ route('backups.index') }}" class="text-blue-400 hover:text-blue-300 mr-4">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                    </a>
                    <div>
                        <h1 class="text-3xl font-bold text-white mb-2">Backup Details</h1>
                        <p class="text-gray-400">{{ $backup->filename }}</p>
                    </div>
                </div>
                <div class="flex space-x-3">
                    @if($backup->isCompleted() && $backup->type === 'backup')
                        <a href="{{ route('backups.download', $backup) }}" 
                           class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg font-medium transition-colors duration-200">
                            <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            Download
                        </a>
                        <button onclick="showRestoreModal()" 
                                class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg font-medium transition-colors duration-200">
                            <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                            </svg>
                            Restore
                        </button>
                    @endif
                </div>
            </div>
        </div>

        <!-- Success/Error Messages -->
        @if(session('success'))
            <div class="bg-green-500/20 border border-green-500/50 text-green-400 px-4 py-3 rounded-lg mb-6">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-500/20 border border-red-500/50 text-red-400 px-4 py-3 rounded-lg mb-6">
                {{ session('error') }}
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Left Column - Details -->
            <div class="lg:col-span-2">
                <!-- Basic Information -->
                <div class="bg-gray-800 rounded-xl border border-gray-700 p-6 mb-6">
                    <div class="flex items-center mb-6">
                        <div class="w-10 h-10 bg-blue-500/20 rounded-lg flex items-center justify-center mr-3">
                            <svg class="w-6 h-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <h2 class="text-xl font-semibold text-white">Basic Information</h2>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-400 mb-1">File name</label>
                            <p class="text-white font-medium">{{ $backup->filename }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-400 mb-1">Type</label>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $backup->type_badge_class }}">
                                {{ ucfirst($backup->type) }}
                            </span>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-400 mb-1">Status</label>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $backup->status_badge_class }}">
                                {{ ucfirst($backup->status) }}
                            </span>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-400 mb-1">Description</label>
                            <p class="text-white">{{ $backup->description ?: 'No description' }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-400 mb-1">Size</label>
                            <p class="text-white">{{ $backup->formatted_file_size }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-400 mb-1">Duration</label>
                            <p class="text-white">{{ $backup->formatted_duration }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-400 mb-1">Tables</label>
                            <p class="text-white">{{ $backup->tables_count }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-400 mb-1">Records</label>
                            <p class="text-white">{{ number_format($backup->records_count) }}</p>
                        </div>
                    </div>
                </div>

                <!-- Timing Information -->
                <div class="bg-gray-800 rounded-xl border border-gray-700 p-6 mb-6">
                    <div class="flex items-center mb-6">
                        <div class="w-10 h-10 bg-green-500/20 rounded-lg flex items-center justify-center mr-3">
                            <svg class="w-6 h-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <h2 class="text-xl font-semibold text-white">Timing Information</h2>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-400 mb-1">Started</label>
                            <p class="text-white">{{ $backup->started_at->format('d/m/Y H:i:s') }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-400 mb-1">Completed</label>
                            <p class="text-white">{{ $backup->completed_at ? $backup->completed_at->format('d/m/Y H:i:s') : 'Not completed' }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-400 mb-1">Creator</label>
                            <p class="text-white">{{ $backup->creator?->name ?? 'System' }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-400 mb-1">File age</label>
                            <p class="text-white">{{ $backup->file_age_in_days }} days</p>
                        </div>
                    </div>
                </div>

                <!-- Technical Details -->
                <div class="bg-gray-800 rounded-xl border border-gray-700 p-6 mb-6">
                    <div class="flex items-center mb-6">
                        <div class="w-10 h-10 bg-purple-500/20 rounded-lg flex items-center justify-center mr-3">
                            <svg class="w-6 h-6 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                        </div>
                        <h2 class="text-xl font-semibold text-white">Technical Details</h2>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-400 mb-1">Compression type</label>
                            <p class="text-white">{{ ucfirst($backup->compression_type) }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-400 mb-1">Encrypted</label>
                            <p class="text-white">{{ $backup->is_encrypted ? 'Yes' : 'No' }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-400 mb-1">File path</label>
                            <p class="text-white text-sm break-all">{{ $backup->file_path ?: 'N/A' }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-400 mb-1">File exists</label>
                            <p class="text-white">{{ $backup->fileExists() ? 'Yes' : 'No' }}</p>
                        </div>
                    </div>

                    @if($backup->excluded_tables && count($backup->excluded_tables) > 0)
                        <div class="mt-6">
                            <label class="block text-sm font-medium text-gray-400 mb-2">Excluded tables</label>
                            <div class="flex flex-wrap gap-2">
                                @foreach($backup->excluded_tables as $table)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        {{ $table }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    @if($backup->tables_list && count($backup->tables_list) > 0)
                        <div class="mt-6">
                            <label class="block text-sm font-medium text-gray-400 mb-2">Backed-up tables</label>
                            <div class="flex flex-wrap gap-2">
                                @foreach($backup->tables_list as $table)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        {{ $table }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Error Information -->
                @if($backup->error_message)
                    <div class="bg-red-500/10 border border-red-500/20 rounded-xl p-6">
                        <div class="flex items-center mb-4">
                            <div class="w-10 h-10 bg-red-500/20 rounded-lg flex items-center justify-center mr-3">
                                <svg class="w-6 h-6 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                </svg>
                            </div>
                            <h2 class="text-xl font-semibold text-white">Error Information</h2>
                        </div>
                        <div class="bg-red-500/20 border border-red-500/30 rounded-lg p-4">
                            <p class="text-red-300 whitespace-pre-wrap">{{ $backup->error_message }}</p>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Right Column - Actions -->
            <div class="lg:col-span-1">
                <!-- Actions -->
                <div class="bg-gray-800 rounded-xl border border-gray-700 p-6 mb-6">
                    <h3 class="text-lg font-semibold text-white mb-4">Actions</h3>
                    <div class="space-y-3">
                        @if($backup->isCompleted() && $backup->type === 'backup')
                            <a href="{{ route('backups.download', $backup) }}" 
                               class="w-full flex items-center justify-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg font-medium transition-colors duration-200">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                Download
                            </a>
                            
                            <button onclick="showRestoreModal()" 
                                    class="w-full flex items-center justify-center px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg font-medium transition-colors duration-200">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                </svg>
                                Restore
                            </button>
                        @endif

                        <form method="POST" action="{{ route('backups.destroy', $backup) }}" class="w-full">
                            @csrf
                            @method('DELETE')
                            <button type="submit" 
                                onclick="return confirm('Are you sure you want to delete this backup?')"
                                    class="w-full flex items-center justify-center px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg font-medium transition-colors duration-200">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                                Delete backup
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Status Card -->
                <div class="bg-gray-800 rounded-xl border border-gray-700 p-6 mb-6">
                    <h3 class="text-lg font-semibold text-white mb-4">Status</h3>
                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-gray-400">Type:</span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $backup->type_badge_class }}">
                                {{ ucfirst($backup->type) }}
                            </span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-400">Status:</span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $backup->status_badge_class }}">
                                {{ ucfirst($backup->status) }}
                            </span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-400">File exists:</span>
                            <span class="text-{{ $backup->fileExists() ? 'green' : 'red' }}-400">
                                {{ $backup->fileExists() ? 'Yes' : 'No' }}
                            </span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-400">Encrypted:</span>
                            <span class="text-{{ $backup->is_encrypted ? 'green' : 'gray' }}-400">
                                {{ $backup->is_encrypted ? 'Yes' : 'No' }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- File Information -->
                <div class="bg-gray-800 rounded-xl border border-gray-700 p-6">
                    <h3 class="text-lg font-semibold text-white mb-4">File Information</h3>
                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-gray-400">Size:</span>
                            <span class="text-white">{{ $backup->formatted_file_size }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-400">File age:</span>
                            <span class="text-white">{{ $backup->file_age_in_days }} days</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-400">Duration:</span>
                            <span class="text-white">{{ $backup->formatted_duration }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-400">Tables:</span>
                            <span class="text-white">{{ $backup->tables_count }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-400">Records:</span>
                            <span class="text-white">{{ number_format($backup->records_count) }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Restore Modal -->
<div id="restoreModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-gray-800 rounded-xl border border-gray-700 p-6 w-full max-w-md">
            <h3 class="text-lg font-semibold text-white mb-4">Restore from Backup</h3>
            <p class="text-gray-400 mb-6">Warning: This action will overwrite current data!</p>
            
            <form method="post" action="{{ route('backups.restore', $backup) }}" class="space-y-4">
                @csrf
                
                <div>
                    <label for="restore_description" class="block text-sm font-medium text-gray-300 mb-2">Description</label>
                    <input type="text" id="restore_description" name="description" 
                           value="Restore from backup: {{ $backup->filename }}"
                           class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-2 text-white placeholder-gray-400 focus:outline-none focus:border-blue-500">
                </div>
                
                <div class="flex items-center">
                    <input type="checkbox" id="confirm_restore" name="confirm_restore" required
                           class="w-4 h-4 text-blue-600 bg-gray-700 border-gray-600 rounded focus:ring-blue-500 focus:ring-2">
                    <label for="confirm_restore" class="ml-2 text-sm text-gray-300">
                        I understand current data will be overwritten
                    </label>
                </div>
                
                <div class="flex justify-end space-x-2">
                    <button type="button" onclick="hideRestoreModal()"
                            class="px-4 py-2 border border-gray-600 text-gray-300 rounded-lg hover:bg-gray-700 transition-colors duration-200">
                        Cancel
                    </button>
                    <button type="submit"
                            class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg transition-colors duration-200">
                        Restore
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function showRestoreModal() {
    document.getElementById('restoreModal').classList.remove('hidden');
}

function hideRestoreModal() {
    document.getElementById('restoreModal').classList.add('hidden');
}

// Close modal when clicking outside
document.getElementById('restoreModal').addEventListener('click', function(e) {
    if (e.target === this) {
        hideRestoreModal();
    }
});
</script>
@endsection
