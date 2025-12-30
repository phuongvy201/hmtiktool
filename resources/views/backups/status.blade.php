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
                        <h1 class="text-3xl font-bold text-white mb-2">Backup Status</h1>
                        <p class="text-gray-400">Overview of backup & restore system</p>
                    </div>
                </div>
                <div class="flex space-x-3">
                    <a href="{{ route('backups.create') }}" 
                       class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition-colors duration-200">
                        <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        Create Backup
                    </a>
                </div>
            </div>
        </div>

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

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- System Information -->
            <div class="bg-gray-800 rounded-xl border border-gray-700 p-6">
                <div class="flex items-center mb-6">
                    <div class="w-10 h-10 bg-blue-500/20 rounded-lg flex items-center justify-center mr-3">
                        <svg class="w-6 h-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"></path>
                        </svg>
                    </div>
                    <h2 class="text-xl font-semibold text-white">System Information</h2>
                </div>

                <div class="space-y-4">
                    <div class="flex justify-between items-center py-2 border-b border-gray-700">
                        <span class="text-gray-400">Backup directory:</span>
                        <span class="text-white font-mono text-sm">{{ $status['backup_directory'] }}</span>
                    </div>
                    <div class="flex justify-between items-center py-2 border-b border-gray-700">
                        <span class="text-gray-400">Available space:</span>
                        <span class="text-green-400 font-medium">{{ $status['available_space'] }}</span>
                    </div>
                    <div class="flex justify-between items-center py-2 border-b border-gray-700">
                        <span class="text-gray-400">Success rate:</span>
                        <span class="text-white font-medium">
                            @if($status['total_backups'] > 0)
                                {{ round(($status['successful_backups'] / $status['total_backups']) * 100, 1) }}%
                            @else
                                0%
                            @endif
                        </span>
                    </div>
                    <div class="flex justify-between items-center py-2">
                        <span class="text-gray-400">Latest backup:</span>
                        <span class="text-white">
                            @if($status['latest_backup'])
                                {{ $status['latest_backup']->created_at->diffForHumans() }}
                            @else
                                None yet
                            @endif
                        </span>
                    </div>
                </div>
            </div>

            <!-- Health Status -->
            <div class="bg-gray-800 rounded-xl border border-gray-700 p-6">
                <div class="flex items-center mb-6">
                    <div class="w-10 h-10 bg-green-500/20 rounded-lg flex items-center justify-center mr-3">
                        <svg class="w-6 h-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <h2 class="text-xl font-semibold text-white">Health Status</h2>
                </div>

                <div class="space-y-4">
                    @php
                        $healthScore = 0;
                        $totalChecks = 4;
                        
                        if ($status['total_backups'] > 0) $healthScore++;
                        if ($status['successful_backups'] > 0) $healthScore++;
                        if ($status['failed_backups'] == 0) $healthScore++;
                        if ($status['latest_backup'] && $status['latest_backup']->created_at->diffInDays(now()) <= 7) $healthScore++;
                        
                        $healthPercentage = ($healthScore / $totalChecks) * 100;
                    @endphp

                    <div class="mb-4">
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-gray-400">Health score:</span>
                            <span class="text-white font-medium">{{ $healthScore }}/{{ $totalChecks }}</span>
                        </div>
                        <div class="w-full bg-gray-700 rounded-full h-2">
                            <div class="bg-{{ $healthPercentage >= 75 ? 'green' : ($healthPercentage >= 50 ? 'yellow' : 'red') }}-500 h-2 rounded-full transition-all duration-300" 
                                 style="width: {{ $healthPercentage }}%"></div>
                        </div>
                    </div>

                    <div class="space-y-3">
                        <div class="flex items-center">
                            @if($status['total_backups'] > 0)
                                <svg class="w-5 h-5 text-green-400 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                </svg>
                            @else
                                <svg class="w-5 h-5 text-red-400 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                </svg>
                            @endif
                            <span class="text-gray-300">There are backups in the system</span>
                        </div>

                        <div class="flex items-center">
                            @if($status['successful_backups'] > 0)
                                <svg class="w-5 h-5 text-green-400 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                </svg>
                            @else
                                <svg class="w-5 h-5 text-red-400 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                </svg>
                            @endif
                            <span class="text-gray-300">Successful backups exist</span>
                        </div>

                        <div class="flex items-center">
                            @if($status['failed_backups'] == 0)
                                <svg class="w-5 h-5 text-green-400 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                </svg>
                            @else
                                <svg class="w-5 h-5 text-red-400 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                </svg>
                            @endif
                            <span class="text-gray-300">No failed backups</span>
                        </div>

                        <div class="flex items-center">
                            @if($status['latest_backup'] && $status['latest_backup']->created_at->diffInDays(now()) <= 7)
                                <svg class="w-5 h-5 text-green-400 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                </svg>
                            @else
                                <svg class="w-5 h-5 text-red-400 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                </svg>
                            @endif
                            <span class="text-gray-300">Recent backup (≤ 7 days)</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Backups -->
        <div class="bg-gray-800 rounded-xl border border-gray-700 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-700">
                <h3 class="text-lg font-semibold text-white">Recent Backups</h3>
            </div>

            @if($recentBackups->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-700">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Info</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Size</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Time</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-700">
                            @foreach($recentBackups as $backup)
                                <tr class="hover:bg-gray-700/50">
                                    <td class="px-6 py-4">
                                        <div>
                                            <div class="text-sm font-medium text-white">{{ $backup->filename }}</div>
                                            <div class="text-sm text-gray-400">{{ $backup->description }}</div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $backup->status_badge_class }}">
                                            {{ ucfirst($backup->status) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-300">
                                        {{ $backup->formatted_file_size }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-300">
                                        <div>{{ $backup->created_at->format('d/m/Y H:i') }}</div>
                                        <div class="text-xs text-gray-400">{{ $backup->created_at->diffForHumans() }}</div>
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
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="px-6 py-12 text-center">
                    <svg class="w-12 h-12 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                    </svg>
                    <h3 class="text-lg font-medium text-gray-300 mb-2">No backups yet</h3>
                    <p class="text-gray-400 mb-4">Create the first backup to get started</p>
                    <a href="{{ route('backups.create') }}" 
                       class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition-colors duration-200">
                        Create first Backup
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
