@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-slate-900 text-gray-100">
    <div class="container mx-auto px-4 py-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h1 class="text-3xl font-bold text-white mb-2">Role Management</h1>
                    <p class="text-gray-400">Manage roles and permissions in the system</p>
                </div>
                @can('create-roles')
                @unless(auth()->user()->hasRole('team-admin'))
                <a href="{{ route('roles.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-medium transition-colors duration-200 flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    Add Role
                </a>
                @endunless
                @endcan
            </div>
        </div>

        <!-- Search and Filters -->
        <div class="bg-gray-800 rounded-xl border border-gray-700 p-6 mb-6">
            <form method="GET" action="{{ route('roles.index') }}" class="flex flex-wrap gap-4">
                <div class="flex-1 min-w-64">
                    <input type="text" name="search" value="{{ request('search') }}" 
                           placeholder="Search by role name..." 
                           class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-2 text-white placeholder-gray-400 focus:outline-none focus:border-blue-500">
                </div>
                <div class="flex gap-2">
                    <select name="permission_count" class="bg-gray-700 border border-gray-600 rounded-lg px-4 py-2 text-white focus:outline-none focus:border-blue-500">
                        <option value="">All permissions</option>
                        <option value="0" {{ request('permission_count') == '0' ? 'selected' : '' }}>No permissions</option>
                        <option value="1-5" {{ request('permission_count') == '1-5' ? 'selected' : '' }}>1-5 permissions</option>
                        <option value="6-10" {{ request('permission_count') == '6-10' ? 'selected' : '' }}>6-10 permissions</option>
                        <option value="10+" {{ request('permission_count') == '10+' ? 'selected' : '' }}>10+ permissions</option>
                    </select>
                    <select name="user_count" class="bg-gray-700 border border-gray-600 rounded-lg px-4 py-2 text-white focus:outline-none focus:border-blue-500">
                        <option value="">All users</option>
                        <option value="0" {{ request('user_count') == '0' ? 'selected' : '' }}>No users</option>
                        <option value="1-5" {{ request('user_count') == '1-5' ? 'selected' : '' }}>1-5 users</option>
                        <option value="6-10" {{ request('user_count') == '6-10' ? 'selected' : '' }}>6-10 users</option>
                        <option value="10+" {{ request('user_count') == '10+' ? 'selected' : '' }}>10+ users</option>
                    </select>
                    <button type="submit" class="bg-gray-700 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition-colors duration-200">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </button>
                    @if(request('search') || request('permission_count') || request('user_count'))
                        <a href="{{ route('roles.index') }}" class="bg-gray-600 hover:bg-gray-500 text-white px-4 py-2 rounded-lg transition-colors duration-200">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </a>
                    @endif
                </div>
            </form>
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

        <!-- Roles Table -->
        <div class="bg-gray-800 rounded-xl border border-gray-700 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-700">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Role</th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Permissions</th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Users</th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Created at</th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-700">
                        @forelse($roles as $role)
                        <tr class="hover:bg-gray-700 transition-colors duration-200">
                            <td class="px-6 py-4">
                                <div class="flex items-center">
                                    <div class="w-10 h-10 bg-purple-500/20 rounded-lg flex items-center justify-center mr-3">
                                        <svg class="w-6 h-6 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                                        </svg>
                                    </div>
                                    <div>
                                        <div class="text-white font-medium">{{ $role->name }}</div>
                                        <div class="text-gray-400 text-sm">{{ $role->permissions->count() }} permissions</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                @if($role->permissions->count() > 0)
                                    <div class="flex flex-wrap gap-1">
                                        @foreach($role->permissions->take(3) as $permission)
                                            <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-gray-700 text-gray-300">
                                                {{ $permission->name }}
                                            </span>
                                        @endforeach
                                        @if($role->permissions->count() > 3)
                                            <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-gray-600 text-gray-400">
                                                +{{ $role->permissions->count() - 3 }} more
                                            </span>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-gray-500 text-sm">No permissions</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center">
                                    <span class="text-gray-300 mr-2">{{ $role->users->count() }} users</span>
                                    @if($role->users->count() > 0)
                                        <div class="flex -space-x-2">
                                            @foreach($role->users->take(3) as $user)
                                                <div class="w-6 h-6 bg-blue-500/20 rounded-full flex items-center justify-center border-2 border-gray-700">
                                                    <span class="text-blue-400 text-xs font-semibold">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                                                </div>
                                            @endforeach
                                            @if($role->users->count() > 3)
                                                <div class="w-6 h-6 bg-gray-600 rounded-full flex items-center justify-center border-2 border-gray-700">
                                                    <span class="text-gray-400 text-xs font-semibold">+{{ $role->users->count() - 3 }}</span>
                                                </div>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-gray-300">{{ $role->created_at->format('d/m/Y') }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center space-x-2">
                                    @can('view-roles')
                                    <a href="{{ route('roles.show', $role) }}" class="text-blue-400 hover:text-blue-300 transition-colors duration-200" title="View details">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        </svg>
                                    </a>
                                    @endcan
                                    @can('edit-roles')
                                    @unless(auth()->user()->hasRole('team-admin'))
                                    <a href="{{ route('roles.edit', $role) }}" class="text-yellow-400 hover:text-yellow-300 transition-colors duration-200" title="Edit">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                        </svg>
                                    </a>
                                    @endunless
                                    @endcan
                                    @can('delete-roles')
                                    @unless(auth()->user()->hasRole('team-admin'))
                                    <form action="{{ route('roles.destroy', $role) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this role?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-400 hover:text-red-300 transition-colors duration-200" title="Delete">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                            </svg>
                                        </button>
                                    </form>
                                    @endunless
                                    @endcan
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-gray-400">
                                <div class="flex flex-col items-center">
                                    <svg class="w-12 h-12 mb-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                                    </svg>
                                    <p class="text-lg font-medium">No roles found</p>
                                    <p class="text-sm">Start by creating a new role</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination -->
        @if($roles->hasPages())
        <div class="mt-6">
            {{ $roles->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
