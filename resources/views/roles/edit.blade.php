@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-slate-900 text-gray-100">
    <div class="container mx-auto px-4 py-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center mb-6">
                <a href="{{ route('roles.index') }}" class="text-blue-400 hover:text-blue-300 mr-4">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                </a>
                <div>
                    <h1 class="text-3xl font-bold text-white mb-2">Edit Role</h1>
                    <p class="text-gray-400">Update role information: {{ $role->name }}</p>
                </div>
            </div>
        </div>

        <!-- Form -->
        <div class="max-w-4xl mx-auto">
            <div class="bg-gray-800 rounded-xl border border-gray-700 p-6">
                <form action="{{ route('roles.update', $role) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <!-- Basic Information -->
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold text-white mb-4 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                            </svg>
                            Role Information
                        </h3>
                        
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-300 mb-2">Role name *</label>
                            <input type="text" id="name" name="name" value="{{ old('name', $role->name) }}" required
                                   placeholder="e.g. admin, manager, user..."
                                   class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-2 text-white placeholder-gray-400 focus:outline-none focus:border-blue-500 @error('name') border-red-500 @enderror">
                            @error('name')
                                <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Permissions -->
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold text-white mb-4 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            Permissions
                        </h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            @foreach($permissions as $permission)
                            <label class="flex items-center p-3 bg-gray-700 rounded-lg hover:bg-gray-600 transition-colors duration-200 cursor-pointer">
                                <input type="checkbox" name="permissions[]" value="{{ $permission->name }}" 
                                       {{ in_array($permission->name, old('permissions', $role->permissions->pluck('name')->toArray())) ? 'checked' : '' }}
                                       class="w-4 h-4 text-blue-600 bg-gray-600 border-gray-500 rounded focus:ring-blue-500 focus:ring-2">
                                <span class="ml-3 text-gray-300">{{ $permission->name }}</span>
                            </label>
                            @endforeach
                        </div>
                        @error('permissions')
                            <p class="text-red-400 text-sm mt-2">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Permission Categories -->
                    <div class="mb-6">
                            <h3 class="text-lg font-semibold text-white mb-4 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                            </svg>
                            Quick select by group
                        </h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="bg-gray-700 rounded-lg p-4">
                                <h4 class="text-sm font-medium text-gray-300 mb-3">User Management</h4>
                                <div class="space-y-2">
                                    <label class="flex items-center">
                                        <input type="checkbox" class="permission-group" data-group="user" 
                                               class="w-4 h-4 text-blue-600 bg-gray-600 border-gray-500 rounded focus:ring-blue-500 focus:ring-2">
                                        <span class="ml-2 text-sm text-gray-300">All user permissions</span>
                                    </label>
                                </div>
                            </div>
                            
                            <div class="bg-gray-700 rounded-lg p-4">
                                <h4 class="text-sm font-medium text-gray-300 mb-3">Role Management</h4>
                                <div class="space-y-2">
                                    <label class="flex items-center">
                                        <input type="checkbox" class="permission-group" data-group="role" 
                                               class="w-4 h-4 text-blue-600 bg-gray-600 border-gray-500 rounded focus:ring-blue-500 focus:ring-2">
                                        <span class="ml-2 text-sm text-gray-300">All role permissions</span>
                                    </label>
                                </div>
                            </div>
                            
                            <div class="bg-gray-700 rounded-lg p-4">
                                <h4 class="text-sm font-medium text-gray-300 mb-3">Team Management</h4>
                                <div class="space-y-2">
                                    <label class="flex items-center">
                                        <input type="checkbox" class="permission-group" data-group="team" 
                                               class="w-4 h-4 text-blue-600 bg-gray-600 border-gray-500 rounded focus:ring-blue-500 focus:ring-2">
                                        <span class="ml-2 text-sm text-gray-300">All team permissions</span>
                                    </label>
                                </div>
                            </div>
                            
                            <div class="bg-gray-700 rounded-lg p-4">
                                <h4 class="text-sm font-medium text-gray-300 mb-3">Reports</h4>
                                <div class="space-y-2">
                                    <label class="flex items-center">
                                        <input type="checkbox" class="permission-group" data-group="report" 
                                               class="w-4 h-4 text-blue-600 bg-gray-600 border-gray-500 rounded focus:ring-blue-500 focus:ring-2">
                                        <span class="ml-2 text-sm text-gray-300">All report permissions</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Current Role Info -->
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold text-white mb-4 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            Current Information
                        </h3>
                        
                        <div class="bg-gray-700 rounded-lg p-4">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                                <div>
                                    <span class="text-gray-400">Current permissions:</span>
                                    <span class="text-white font-medium ml-2">{{ $role->permissions->count() }}</span>
                                </div>
                                <div>
                                    <span class="text-gray-400">Users:</span>
                                    <span class="text-white font-medium ml-2">{{ $role->users->count() }}</span>
                                </div>
                                <div>
                                    <span class="text-gray-400">Created at:</span>
                                    <span class="text-gray-300 ml-2">{{ $role->created_at->format('d/m/Y') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center justify-end space-x-4 pt-6 border-t border-gray-700">
                        <a href="{{ route('roles.index') }}" 
                           class="px-6 py-2 border border-gray-600 text-gray-300 rounded-lg hover:bg-gray-700 transition-colors duration-200">
                            Cancel
                        </a>
                        <button type="submit" 
                                class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition-colors duration-200">
                            Update Role
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Permission group checkboxes
    const permissionGroups = document.querySelectorAll('.permission-group');
    
    permissionGroups.forEach(group => {
        group.addEventListener('change', function() {
            const groupType = this.dataset.group;
            const isChecked = this.checked;
            
            // Get all permissions that match the group
            const permissions = document.querySelectorAll(`input[name="permissions[]"]`);
            
            permissions.forEach(permission => {
                if (permission.value.includes(groupType)) {
                    permission.checked = isChecked;
                }
            });
        });
    });
});
</script>
@endsection
