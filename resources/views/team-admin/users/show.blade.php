@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-slate-900 text-gray-100">
    <div class="container mx-auto px-4 py-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex justify-between items-center mb-6">
                <div class="flex items-center">
                    <a href="{{ route('team-admin.users.index') }}" class="text-blue-400 hover:text-blue-300 mr-4">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                    </a>
                    <div>
                        <h1 class="text-3xl font-bold text-white mb-2">Member Details</h1>
                        <p class="text-gray-400">Detailed information about member: <span class="text-blue-400 font-medium">{{ $user->name }}</span></p>
                    </div>
                </div>
                <div class="flex items-center space-x-3">
                    <a href="{{ route('team-admin.users.edit', $user) }}" class="bg-yellow-600 hover:bg-yellow-700 text-white px-4 py-2 rounded-lg font-medium transition-colors duration-200 flex items-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                        Edit
                    </a>
                    <form action="{{ route('team-admin.users.destroy', $user) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to remove this member from the team?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg font-medium transition-colors duration-200 flex items-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                            Remove from team
                        </button>
                    </form>
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

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main Information -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Basic Information -->
                <div class="bg-gray-800 rounded-xl border border-gray-700 p-6">
                    <h3 class="text-lg font-semibold text-white mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                        Basic Information
                    </h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-400 mb-1">Full name</label>
                            <p class="text-white font-medium">{{ $user->name }}</p>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-400 mb-1">Email</label>
                            <p class="text-white">{{ $user->email }}</p>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-400 mb-1">User ID</label>
                            <p class="text-gray-300">{{ $user->id }}</p>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-400 mb-1">Joined at</label>
                            <p class="text-gray-300">{{ $user->created_at->format('d/m/Y H:i') }}</p>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-400 mb-1">TikTok market</label>
                            @if($user->getPrimaryTikTokMarket())
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-500/20 text-blue-400">
                                    {{ $user->getPrimaryTikTokMarket() }}
                                </span>
                            @else
                                <p class="text-gray-500">Not assigned</p>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Role and Permissions -->
                <div class="bg-gray-800 rounded-xl border border-gray-700 p-6">
                    <h3 class="text-lg font-semibold text-white mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                        </svg>
                        Roles and permissions
                    </h3>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-400 mb-2">Current roles</label>
                            @if($user->roles->count() > 0)
                                <div class="flex flex-wrap gap-2">
                                    @foreach($user->roles as $role)
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-purple-500/20 text-purple-400 border border-purple-500/30">
                                            {{ $role->name }}
                                        </span>
                                    @endforeach
                                </div>
                            @else
                                <span class="text-gray-500 text-sm">No roles assigned</span>
                            @endif
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-400 mb-2">Permissions</label>
                            @if($user->permissions->count() > 0)
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                                    @foreach($user->permissions as $permission)
                                        <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-blue-500/20 text-blue-400">
                                            {{ $permission->name }}
                                        </span>
                                    @endforeach
                                </div>
                            @else
                                <span class="text-gray-500 text-sm">No individual permissions</span>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Account Status -->
                <div class="bg-gray-800 rounded-xl border border-gray-700 p-6">
                    <h3 class="text-lg font-semibold text-white mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Account status
                    </h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-400 mb-2">Email verification</label>
                            @if($user->email_verified_at)
                                <div class="flex items-center">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-500/20 text-green-400 border border-green-500/30">
                                        <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                        </svg>
                                        Verified
                                    </span>
                                </div>
                                <p class="text-gray-400 text-sm mt-1">Verified at: {{ $user->email_verified_at->format('d/m/Y H:i') }}</p>
                            @else
                                <div class="flex items-center">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-yellow-500/20 text-yellow-400 border border-yellow-500/30">
                                        <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                        </svg>
                                        Not verified
                                    </span>
                                </div>
                            @endif
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-400 mb-2">Active status</label>
                            <div class="flex items-center">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-500/20 text-green-400 border border-green-500/30">
                                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                    </svg>
                                    Active
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Activity Information -->
                <div class="bg-gray-800 rounded-xl border border-gray-700 p-6">
                    <h3 class="text-lg font-semibold text-white mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                        </svg>
                        Activity information
                    </h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-400 mb-1">Last login</label>
                            @if($user->last_login_at)
                                <p class="text-gray-300">{{ $user->last_login_at->format('d/m/Y H:i') }}</p>
                            @else
                                <p class="text-gray-500 text-sm">Not logged in yet</p>
                            @endif
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-400 mb-1">Login count</label>
                            <p class="text-gray-300">{{ $user->login_count ?? 0 }} times</p>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-400 mb-1">Last updated</label>
                            <p class="text-gray-300">{{ $user->updated_at->format('d/m/Y H:i') }}</p>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-400 mb-1">Time since joined</label>
                            <p class="text-gray-300">{{ $user->created_at->diffForHumans() }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- User Avatar -->
                <div class="bg-gray-800 rounded-xl border border-gray-700 p-6">
                    <div class="flex flex-col items-center">
                        <div class="w-24 h-24 bg-blue-500/20 rounded-full flex items-center justify-center mb-4">
                            @if($user->hasAvatar())
                                <img src="{{ $user->avatar_url }}" 
                                     alt="{{ $user->name }}" 
                                     class="w-full h-full object-cover rounded-full">
                            @else
                                <span class="text-blue-400 font-bold text-2xl">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                            @endif
                        </div>
                        <h3 class="text-xl font-bold text-white mb-1">{{ $user->name }}</h3>
                        <p class="text-gray-400 text-sm">{{ $user->email }}</p>
                        <p class="text-gray-500 text-xs mt-1">ID: {{ $user->id }}</p>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="bg-gray-800 rounded-xl border border-gray-700 p-6">
                        <h3 class="text-lg font-semibold text-white mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4"></path>
                        </svg>
                        Quick actions
                    </h3>
                    
                    <div class="space-y-3">
                        <a href="{{ route('team-admin.users.edit', $user) }}" class="w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium transition-colors duration-200 flex items-center justify-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                            </svg>
                            Edit information
                        </a>
                        
                        @if(!$user->email_verified_at)
                            <button class="w-full bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-medium transition-colors duration-200 flex items-center justify-center">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                </svg>
                                Send verification email
                            </button>
                        @endif
                        
                        <button class="w-full bg-yellow-600 hover:bg-yellow-700 text-white px-4 py-2 rounded-lg font-medium transition-colors duration-200 flex items-center justify-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                            </svg>
                            Reset password
                        </button>
                        
                        <form action="{{ route('team-admin.users.destroy', $user) }}" method="POST" onsubmit="return confirm('Are you sure you want to remove this member from the team?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg font-medium transition-colors duration-200 flex items-center justify-center">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                                Remove from team
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Team Information -->
                <div class="bg-gray-800 rounded-xl border border-gray-700 p-6">
                    <h3 class="text-lg font-semibold text-white mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                        Team information
                    </h3>
                    
                    <div class="space-y-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-400 mb-1">Current team</label>
                            <p class="text-white font-medium">{{ auth()->user()->team->name ?? 'N/A' }}</p>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-400 mb-1">Position in team</label>
                            @if($user->roles->count() > 0)
                                <p class="text-gray-300">{{ $user->roles->first()->name }}</p>
                            @else
                                <p class="text-gray-500 text-sm">No roles assigned</p>
                            @endif
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-400 mb-1">Joined team on</label>
                            <p class="text-gray-300">{{ $user->created_at->format('d/m/Y') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
