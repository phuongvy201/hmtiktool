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
                    <h1 class="text-3xl font-bold text-white mb-2">Activity</h1>
                    <p class="text-gray-400">Activity history and login</p>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Activity Stats -->
            <div class="lg:col-span-1">
                <div class="bg-gray-800 rounded-xl border border-gray-700 p-6 mb-6">
                    <h2 class="text-xl font-semibold text-white mb-4">Activity statistics</h2>
                    <div class="space-y-4">
                        <div class="bg-gray-700 rounded-lg p-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-gray-400 text-sm">Login count</p>
                                    <p class="text-2xl font-bold text-blue-400">{{ $user->login_count ?? 0 }}</p>
                                </div>
                                <div class="w-12 h-12 bg-blue-500/20 rounded-lg flex items-center justify-center">
                                    <svg class="w-6 h-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path>
                                    </svg>
                                </div>
                            </div>
                        </div>

                        <div class="bg-gray-700 rounded-lg p-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-gray-400 text-sm">Last login</p>
                                    <p class="text-sm text-white">{{ $user->last_login_at ? $user->last_login_at->diffForHumans() : 'Never logged in' }}</p>
                                </div>
                                <div class="w-12 h-12 bg-green-500/20 rounded-lg flex items-center justify-center">
                                    <svg class="w-6 h-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                            </div>
                        </div>

                        <div class="bg-gray-700 rounded-lg p-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-gray-400 text-sm">Member since</p>
                                    <p class="text-sm text-white">{{ $user->created_at->format('d/m/Y') }}</p>
                                </div>
                                <div class="w-12 h-12 bg-purple-500/20 rounded-lg flex items-center justify-center">
                                    <svg class="w-6 h-6 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="bg-gray-800 rounded-xl border border-gray-700 p-6">
                    <h3 class="text-lg font-semibold text-white mb-4">Quick actions</h3>
                    <div class="space-y-2">
                        <a href="{{ route('profile.edit') }}" 
                           class="flex items-center p-3 text-gray-300 hover:bg-gray-700 rounded-lg transition-colors duration-200">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                            Personal profile
                        </a>
                        <a href="{{ route('profile.security') }}" 
                           class="flex items-center p-3 text-gray-300 hover:bg-gray-700 rounded-lg transition-colors duration-200">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                            </svg>
                            Security
                        </a>
                        <a href="{{ route('profile.notifications') }}" 
                           class="flex items-center p-3 text-gray-300 hover:bg-gray-700 rounded-lg transition-colors duration-200">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5zM4.19 4.19A2 2 0 004 6v10a2 2 0 002 2h10a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-1.81 1.19z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 9h.01M15 9h.01"></path>
                            </svg>
                            Notifications
                        </a>
                    </div>
                </div>
            </div>

            <!-- Activity Timeline -->
            <div class="lg:col-span-2">
                <div class="bg-gray-800 rounded-xl border border-gray-700 p-6">
                    <div class="flex items-center justify-between mb-6">
                        <div class="flex items-center">
                            <div class="w-10 h-10 bg-blue-500/20 rounded-lg flex items-center justify-center mr-3">
                                <svg class="w-6 h-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                </svg>
                            </div>
                            <h2 class="text-xl font-semibold text-white">Activity history</h2>
                        </div>
                        <div class="flex items-center space-x-2">
                            <button class="px-3 py-1 bg-blue-600 hover:bg-blue-700 text-white text-sm rounded-lg transition-colors duration-200">
                                    Filter
                            </button>
                            <button class="px-3 py-1 bg-gray-600 hover:bg-gray-700 text-white text-sm rounded-lg transition-colors duration-200">
                                Export
                            </button>
                        </div>
                    </div>

                    @if($activities->count() > 0)
                        <div class="space-y-4">
                            @foreach($activities as $activity)
                                <div class="flex items-start space-x-4 p-4 bg-gray-700 rounded-lg">
                                    <div class="w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0
                                        @if($activity->type === 'login') bg-green-500/20
                                        @elseif($activity->type === 'profile_update') bg-blue-500/20
                                        @elseif($activity->type === 'password_change') bg-orange-500/20
                                        @else bg-gray-500/20 @endif">
                                        @if($activity->type === 'login')
                                            <svg class="w-4 h-4 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path>
                                            </svg>
                                        @elseif($activity->type === 'profile_update')
                                            <svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                            </svg>
                                        @elseif($activity->type === 'password_change')
                                            <svg class="w-4 h-4 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                            </svg>
                                        @else
                                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                        @endif
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-white font-medium">{{ $activity->description ?? 'Activity not specified' }}</p>
                                        <p class="text-gray-400 text-sm">{{ $activity->created_at->diffForHumans() }}</p>
                                        @if($activity->ip_address)
                                            <p class="text-gray-500 text-xs">IP: {{ $activity->ip_address }}</p>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <!-- Pagination -->
                        <div class="mt-6">
                            {{-- {{ $activities->links() }} --}}
                            <div class="flex items-center justify-between">
                                <p class="text-sm text-gray-400">Displaying {{ $activities->count() }} activities</p>
                                <div class="flex items-center space-x-2">
                                    <button class="px-3 py-1 bg-gray-600 hover:bg-gray-700 text-white text-sm rounded-lg transition-colors duration-200">
                                        Previous
                                    </button>
                                    <span class="text-sm text-gray-400">Trang 1</span>
                                    <button class="px-3 py-1 bg-gray-600 hover:bg-gray-700 text-white text-sm rounded-lg transition-colors duration-200">
                                        Next
                                    </button>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="text-center py-12">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-300">No activities</h3>
                            <p class="mt-1 text-sm text-gray-400">Your activities will appear here.</p>
                        </div>
                    @endif
                </div>

                <!-- Recent Logins -->
                <div class="bg-gray-800 rounded-xl border border-gray-700 p-6 mt-6">
                    <div class="flex items-center mb-6">
                        <div class="w-10 h-10 bg-green-500/20 rounded-lg flex items-center justify-center mr-3">
                            <svg class="w-6 h-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path>
                            </svg>
                        </div>
                        <h2 class="text-xl font-semibold text-white">Recent logins</h2>
                    </div>

                    <div class="space-y-3">
                        <!-- Sample login entries -->
                        <div class="flex items-center justify-between p-3 bg-gray-700 rounded-lg">
                            <div class="flex items-center">
                                <div class="w-8 h-8 bg-green-500/20 rounded-full flex items-center justify-center mr-3">
                                    <svg class="w-4 h-4 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-white text-sm">Desktop - Windows</p>
                                    <p class="text-gray-400 text-xs">192.168.1.100</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="text-white text-sm">Today</p>
                                <p class="text-gray-400 text-xs">14:30</p>
                            </div>
                        </div>

                        <div class="flex items-center justify-between p-3 bg-gray-700 rounded-lg">
                            <div class="flex items-center">
                                <div class="w-8 h-8 bg-blue-500/20 rounded-full flex items-center justify-center mr-3">
                                    <svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-white text-sm">Mobile - iOS</p>
                                    <p class="text-gray-400 text-xs">192.168.1.101</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="text-white text-sm">Yesterday</p>
                                <p class="text-gray-400 text-xs">09:15</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
