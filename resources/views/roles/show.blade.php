@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-slate-900 text-gray-100">
    <div class="container mx-auto px-4 py-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center justify-between mb-6">
                <div class="flex items-center">
                    <a href="{{ route('roles.index') }}" class="text-blue-400 hover:text-blue-300 mr-4">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                    </a>
                    <div>
                        <h1 class="text-3xl font-bold text-white mb-2">Role Details</h1>
                        <p class="text-gray-400">Detailed information about role: {{ $role->name }}</p>
                    </div>
                </div>
                <div class="flex items-center space-x-3">
                    @can('edit-roles')
                    <a href="{{ route('roles.edit', $role) }}" class="bg-yellow-600 hover:bg-yellow-700 text-white px-4 py-2 rounded-lg font-medium transition-colors duration-200 flex items-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                        Edit
                    </a>
                    @endcan
                    @can('delete-roles')
                    <form action="{{ route('roles.destroy', $role) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this role?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg font-medium transition-colors duration-200 flex items-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                            Delete
                        </button>
                    </form>
                    @endcan
                </div>
            </div>
        </div>

        <!-- Role Information -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Role Card -->
            <div class="lg:col-span-1">
                <div class="bg-gray-800 rounded-xl border border-gray-700 p-6">
                    <div class="text-center mb-6">
                        <div class="w-24 h-24 bg-purple-500/20 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg class="w-12 h-12 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                            </svg>
                        </div>
                        <h2 class="text-xl font-bold text-white">{{ $role->name }}</h2>
                        <p class="text-gray-400">System role</p>
                    </div>
                    
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <span class="text-gray-400">Permissions:</span>
                            <span class="text-white font-medium">{{ $role->permissions->count() }}</span>
                        </div>
                        
                        <div class="flex items-center justify-between">
                            <span class="text-gray-400">Users:</span>
                            <span class="text-white font-medium">{{ $role->users->count() }}</span>
                        </div>
                        
                        <div class="flex items-center justify-between">
                            <span class="text-gray-400">Created at:</span>
                            <span class="text-gray-300">{{ $role->created_at->format('d/m/Y H:i') }}</span>
                        </div>
                        
                        <div class="flex items-center justify-between">
                            <span class="text-gray-400">Last updated:</span>
                            <span class="text-gray-300">{{ $role->updated_at->format('d/m/Y H:i') }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Details -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Permissions -->
                <div class="bg-gray-800 rounded-xl border border-gray-700 p-6">
                    <h3 class="text-lg font-semibold text-white mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Assigned Permissions
                    </h3>
                    
                    @if($role->permissions->count() > 0)
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                            @foreach($role->permissions as $permission)
                                <div class="bg-gray-700 rounded-lg p-3">
                                    <div class="flex items-center">
                                        <svg class="w-4 h-4 text-green-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                        <span class="text-gray-300 text-sm">{{ $permission->name }}</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-8">
                            <svg class="w-12 h-12 mx-auto text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <p class="text-gray-400">This role has no permissions</p>
                        </div>
                    @endif
                </div>

                <!-- Users with this role -->
                <div class="bg-gray-800 rounded-xl border border-gray-700 p-6">
                    <h3 class="text-lg font-semibold text-white mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                        </svg>
                        Users with this role
                    </h3>
                    
                    @if($role->users->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead class="bg-gray-700">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">User</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Email</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Type</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Created at</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-700">
                                    @foreach($role->users as $user)
                                    <tr class="hover:bg-gray-700 transition-colors duration-200">
                                        <td class="px-4 py-3">
                                            <div class="flex items-center">
                                                <div class="w-8 h-8 bg-blue-500/20 rounded-full flex items-center justify-center mr-3">
                                                    <span class="text-blue-400 font-semibold text-sm">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                                                </div>
                                                <span class="text-white font-medium">{{ $user->name }}</span>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3 text-gray-300">{{ $user->email }}</td>
                                        <td class="px-4 py-3">
                                            @if($user->is_system_user)
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-500/20 text-purple-400">
                                                    System Level
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-500/20 text-green-400">
                                                    Team Level
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-gray-300">{{ $user->created_at->format('d/m/Y') }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-8">
                            <svg class="w-12 h-12 mx-auto text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                            </svg>
                            <p class="text-gray-400">No users assigned to this role yet</p>
                        </div>
                    @endif
                </div>

                <!-- Permission Categories -->
                <div class="bg-gray-800 rounded-xl border border-gray-700 p-6">
                    <h3 class="text-lg font-semibold text-white mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                        </svg>
                        Permission Categories
                    </h3>
                    
                    @php
                        $permissionGroups = [
                            'user' => $role->permissions->filter(fn($p) => str_contains($p->name, 'user')),
                            'role' => $role->permissions->filter(fn($p) => str_contains($p->name, 'role')),
                            'team' => $role->permissions->filter(fn($p) => str_contains($p->name, 'team')),
                            'report' => $role->permissions->filter(fn($p) => str_contains($p->name, 'report')),
                            'other' => $role->permissions->filter(fn($p) => !str_contains($p->name, 'user') && !str_contains($p->name, 'role') && !str_contains($p->name, 'team') && !str_contains($p->name, 'report'))
                        ];
                    @endphp
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @foreach($permissionGroups as $group => $permissions)
                            @if($permissions->count() > 0)
                                <div class="bg-gray-700 rounded-lg p-4">
                                    <h4 class="text-sm font-medium text-gray-300 mb-3 capitalize">{{ $group }} Management ({{ $permissions->count() }})</h4>
                                    <div class="space-y-2">
                                        @foreach($permissions as $permission)
                                            <div class="flex items-center text-sm text-gray-400">
                                                <svg class="w-3 h-3 text-green-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                </svg>
                                                {{ $permission->name }}
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
