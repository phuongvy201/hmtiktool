@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-slate-900 text-gray-100">
    <div class="container mx-auto px-4 py-8">
        <!-- Header Section -->
        <div class="mb-8">
            <h1 class="text-4xl font-bold text-white mb-2">Roles Team</h1>
            <div class="bg-gray-800 rounded-lg p-6 border border-gray-700">
                <h2 class="text-xl font-semibold text-white mb-2">{{ $team->name }}</h2>
                <p class="text-gray-400">View the roles used in your team</p>
            </div>
        </div>

        <!-- Team Roles Overview -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            <!-- Roles Summary -->
            <div class="bg-gray-800 rounded-xl border border-gray-700 p-6">
                <h3 class="text-lg font-semibold text-white mb-4 flex items-center">
                    <i class="fas fa-shield-alt mr-2 text-blue-400"></i>
                    Overview of Roles
                </h3>
                <div class="space-y-3">
                    @forelse($teamRoles as $role)
                    <div class="flex items-center justify-between bg-gray-700 rounded-lg p-3">
                        <div class="flex items-center">
                            <div class="w-8 h-8 bg-blue-500/20 rounded-lg flex items-center justify-center mr-3">
                                <i class="fas fa-user-tag text-blue-400 text-sm"></i>
                            </div>
                            <div>
                                <h4 class="font-medium text-white">{{ ucfirst($role->name) }}</h4>
                                <p class="text-xs text-gray-400">
                                    {{ $teamMembers->filter(fn($member) => $member->hasRole($role->name))->count() }} members
                                </p>
                            </div>
                        </div>
                        <div class="text-right">
                            <span class="text-xs text-gray-400">ID: {{ $role->id }}</span>
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-8">
                        <i class="fas fa-info-circle text-gray-400 text-4xl mb-4"></i>
                        <p class="text-gray-400">No roles assigned in the team</p>
                    </div>
                    @endforelse
                </div>
            </div>

            <!-- Team Members by Role -->
            <div class="bg-gray-800 rounded-xl border border-gray-700 p-6">
                <h3 class="text-lg font-semibold text-white mb-4 flex items-center">
                    <i class="fas fa-users mr-2 text-green-400"></i>
                    Members by Role
                </h3>
                <div class="space-y-3">
                    @forelse($teamRoles as $role)
                    <div class="bg-gray-700 rounded-lg p-3">
                        <h4 class="font-medium text-white mb-2">{{ ucfirst($role->name) }}</h4>
                        <div class="space-y-1">
                            @php
                                $membersWithRole = $teamMembers->filter(fn($member) => $member->hasRole($role->name));
                            @endphp
                            @forelse($membersWithRole as $member)
                            <div class="flex items-center justify-between text-sm">
                                <div class="flex items-center">
                                    <div class="w-6 h-6 bg-gray-600 rounded-full flex items-center justify-center mr-2">
                                        <span class="text-xs text-gray-300">{{ strtoupper(substr($member->name, 0, 1)) }}</span>
                                    </div>
                                    <span class="text-gray-300">{{ $member->name }}</span>
                                </div>
                                <span class="text-xs text-gray-400">{{ $member->email }}</span>
                            </div>
                            @empty
                            <p class="text-xs text-gray-400">No members</p>
                            @endforelse
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-8">
                        <i class="fas fa-user-slash text-gray-400 text-4xl mb-4"></i>
                        <p class="text-gray-400">No members in the team</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Permissions Overview -->
        <div class="bg-gray-800 rounded-xl border border-gray-700 p-6 mb-8">
            <h3 class="text-lg font-semibold text-white mb-4 flex items-center">
                <i class="fas fa-key mr-2 text-yellow-400"></i>
                Permissions of Team
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @php
                    $allPermissions = $teamRoles->pluck('permissions')->flatten()->unique('id');
                @endphp
                @forelse($allPermissions as $permission)
                <div class="bg-gray-700 rounded-lg p-3">
                    <div class="flex items-center justify-between">
                        <div>
                            <h4 class="font-medium text-white text-sm">{{ $permission->name }}</h4>
                            <p class="text-xs text-gray-400">{{ $permission->guard_name }}</p>
                        </div>
                        <i class="fas fa-check-circle text-green-400"></i>
                    </div>
                </div>
                @empty
                <div class="col-span-full text-center py-8">
                    <i class="fas fa-lock text-gray-400 text-4xl mb-4"></i>
                        <p class="text-gray-400">No permissions assigned</p>
                </div>
                @endforelse
            </div>
        </div>

        <!-- Actions -->
        <div class="flex justify-between items-center">
            <a href="{{ route('team-admin.dashboard') }}" class="bg-gray-700 hover:bg-gray-600 text-white px-6 py-2 rounded-lg transition-colors duration-200 flex items-center">
                <i class="fas fa-arrow-left mr-2"></i>
                    Back to Dashboard
            </a>
            
            <a href="{{ route('team-admin.users.index') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg transition-colors duration-200 flex items-center">
                <i class="fas fa-users mr-2"></i>
                Manage Members
            </a>
        </div>
    </div>
</div>
@endsection
