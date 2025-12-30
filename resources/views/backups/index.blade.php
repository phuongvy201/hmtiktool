@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-slate-900 text-gray-100">
    <div class="container mx-auto px-4 py-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-3xl font-bold text-white mb-2">Backup & Restore Management</h1>
                    <p class="text-gray-400">Create, manage, and restore system data</p>
                </div>
                <div class="flex space-x-3">
                    <a href="{{ route('backups.create') }}" 
                       class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition-colors duration-200">
                        <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        Create Backup
                    </a>
                    <a href="{{ route('backups.status') }}" 
                       class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg font-medium transition-colors duration-200">
                        <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                        Status
                    </a>
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

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="bg-gray-800 rounded-xl border border-gray-700 p-6">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-blue-500/20 rounded-lg flex items-center justify-center mr-4">
                        <svg class="w-6 h-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="text-gray-400 text-sm">Total Backups</p>
                        <p class="text-2xl font-bold text-white">{{ $status['total_backups'] }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-gray-800 rounded-xl border border-gray-700 p-6">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-green-500/20 rounded-lg flex items-center justify-center mr-4">
                        <svg class="w-6 h-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="text-gray-400 text-sm">Successful</p>
                        <p class="text-2xl font-bold text-white">{{ $status['successful_backups'] }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-gray-800 rounded-xl border border-gray-700 p-6">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-red-500/20 rounded-lg flex items-center justify-center mr-4">
                        <svg class="w-6 h-6 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="text-gray-400 text-sm">Failed</p>
                        <p class="text-2xl font-bold text-white">{{ $status['failed_backups'] }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-gray-800 rounded-xl border border-gray-700 p-6">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-purple-500/20 rounded-lg flex items-center justify-center mr-4">
                        <svg class="w-6 h-6 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="text-gray-400 text-sm">Total Size</p>
                        <p class="text-2xl font-bold text-white">{{ $status['total_size'] }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="bg-gray-800 rounded-xl border border-gray-700 p-6 mb-8">
            <h3 class="text-lg font-semibold text-white mb-4">Quick Actions</h3>
            <div class="flex flex-wrap gap-3">
                <form method="POST" action="{{ route('backups.auto-backup') }}" class="inline">
                    @csrf
                    <button type="submit" 
                            class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition-colors duration-200">
                        <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                        </svg>
                        Auto Backup
                    </button>
                </form>

                <button onclick="showCleanupModal()" 
                        class="px-4 py-2 bg-orange-600 hover:bg-orange-700 text-white rounded-lg font-medium transition-colors duration-200">
                    <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                    </svg>
                    Clean up old backups
                </button>

                <a href="{{ route('backups.export') }}" 
                   class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg font-medium transition-colors duration-200">
                    <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    Export list
                </a>
            </div>
        </div>

        <!-- Backup List -->
        <div class="bg-gray-800 rounded-xl border border-gray-700 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-700">
                <h3 class="text-lg font-semibold text-white">Backup List</h3>
            </div>

            @if($backups->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-700">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Info</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Size</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Time</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Creator</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-700">
                            @foreach($backups as $backup)
                                <tr class="hover:bg-gray-700/50">
                                    <td class="px-6 py-4">
                                        <div>
                                            <div class="text-sm font-medium text-white">{{ $backup->filename }}</div>
                                            <div class="text-sm text-gray-400">{{ $backup->description }}</div>
                                            <div class="flex items-center mt-1">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $backup->type_badge_class }}">
                                                    {{ ucfirst($backup->type) }}
                                                </span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $backup->status_badge_class }}">
                                            @if($backup->status === 'success')
                                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                                </svg>
                                            @elseif($backup->status === 'failed')
                                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                                </svg>
                                            @else
                                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>
                                                </svg>
                                            @endif
                                            {{ ucfirst($backup->status) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-300">
                                        {{ $backup->formatted_file_size }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-300">
                                        <div>{{ $backup->created_at->format('d/m/Y H:i') }}</div>
                                        <div class="text-xs text-gray-400">{{ $backup->formatted_duration }}</div>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-300">
                                        {{ $backup->creator?->name ?? 'System' }}
                                    </td>
                                    <td class="px-6 py-4 text-sm font-medium">
                                        <div class="flex space-x-2">
                                                <a href="{{ route('backups.show', $backup) }}" 
                                               class="text-blue-400 hover:text-blue-300">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                                </svg>
                                            </a>
                                            
                                            @if($backup->isCompleted() && $backup->type === 'backup')
                                                <a href="{{ route('backups.download', $backup) }}" 
                                                   class="text-green-400 hover:text-green-300">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                                    </svg>
                                                </a>
                                            @endif

                                            @if($backup->isCompleted() && $backup->type === 'backup')
                                                <button onclick="showRestoreModal({{ $backup->id }}, '{{ $backup->filename }}')" 
                                                        class="text-purple-400 hover:text-purple-300">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                                    </svg>
                                                </button>
                                            @endif

                                            <form method="POST" action="{{ route('backups.destroy', $backup) }}" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" 
                                                        onclick="return confirm('Are you sure you want to delete this backup?')"
                                                        class="text-red-400 hover:text-red-300">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                    </svg>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="px-6 py-4 border-t border-gray-700">
                    {{ $backups->links() }}
                </div>
            @else
                <div class="px-6 py-12 text-center">
                    <svg class="w-12 h-12 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                    </svg>
                    <h3 class="text-lg font-medium text-gray-300 mb-2">No backups yet</h3>
                    <p class="text-gray-400 mb-4">Create your first backup to protect system data</p>
                    <a href="{{ route('backups.create') }}" 
                       class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition-colors duration-200">
                        Create first Backup
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Cleanup Modal -->
<div id="cleanupModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-gray-800 rounded-xl border border-gray-700 p-6 w-full max-w-md">
            <h3 class="text-lg font-semibold text-white mb-4">Clean up old backups</h3>
            <p class="text-gray-400 mb-6">Delete old backups to save storage.</p>
            
            <form method="post" action="{{ route('backups.cleanup') }}" class="space-y-4">
                @csrf
                
                <div>
                    <label for="days_to_keep" class="block text-sm font-medium text-gray-300 mb-2">Keep backups for (days)</label>
                    <input type="number" id="days_to_keep" name="days_to_keep" value="30" min="1" max="365"
                           class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-2 text-white placeholder-gray-400 focus:outline-none focus:border-blue-500">
                </div>
                
                <div class="flex justify-end space-x-2">
                    <button type="button" onclick="hideCleanupModal()"
                            class="px-4 py-2 border border-gray-600 text-gray-300 rounded-lg hover:bg-gray-700 transition-colors duration-200">
                        Cancel
                    </button>
                    <button type="submit"
                            class="px-4 py-2 bg-orange-600 hover:bg-orange-700 text-white rounded-lg transition-colors duration-200">
                        Clean up
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Restore Modal -->
<div id="restoreModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-gray-800 rounded-xl border border-gray-700 p-6 w-full max-w-md">
            <h3 class="text-lg font-semibold text-white mb-4">Restore from Backup</h3>
            <p class="text-gray-400 mb-6">Warning: This action will overwrite current data!</p>
            
            <form id="restoreForm" method="post" class="space-y-4">
                @csrf
                
                <div>
                    <label for="restore_description" class="block text-sm font-medium text-gray-300 mb-2">Description</label>
                    <input type="text" id="restore_description" name="description" 
                           class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-2 text-white placeholder-gray-400 focus:outline-none focus:border-blue-500"
                           placeholder="Describe the restore reason">
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
function showCleanupModal() {
    document.getElementById('cleanupModal').classList.remove('hidden');
}

function hideCleanupModal() {
    document.getElementById('cleanupModal').classList.add('hidden');
}

function showRestoreModal(backupId, filename) {
    document.getElementById('restoreForm').action = `/backups/${backupId}/restore`;
    document.getElementById('restore_description').value = `Restore from backup: ${filename}`;
    document.getElementById('restoreModal').classList.remove('hidden');
}

function hideRestoreModal() {
    document.getElementById('restoreModal').classList.add('hidden');
}

// Close modals when clicking outside
document.getElementById('cleanupModal').addEventListener('click', function(e) {
    if (e.target === this) {
        hideCleanupModal();
    }
});

document.getElementById('restoreModal').addEventListener('click', function(e) {
    if (e.target === this) {
        hideRestoreModal();
    }
});
</script>
@endsection
