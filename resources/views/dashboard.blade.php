@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-slate-900 text-gray-100">
    <div class="container mx-auto px-4 py-8">
        <!-- Header Section -->
        <div class="mb-8">
            <h1 class="text-4xl font-bold text-white mb-2">Dashboard</h1>
            <div class="bg-gray-800 rounded-lg p-6 border border-gray-700">
                <h2 class="text-xl font-semibold text-white mb-2">Welcome back, {{ auth()->user()->name }}!</h2>
                <div class="flex items-center space-x-4 text-gray-300">
                    <span class="flex items-center">
                        <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path>
                        </svg>
                        {{ auth()->user()->roles->first()?->name ?? 'No Role' }}
                    </span>
                    <span class="flex items-center">
                        <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        {{ auth()->user()->isSystemUser() ? 'System Level' : 'Team Level' }}
                    </span>
                    @if(auth()->user()->team)
                    <span class="flex items-center">
                        <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3z"></path>
                        </svg>
                        {{ auth()->user()->team->name }}
                    </span>
                    @endif
                </div>
            </div>
        </div>

        <!-- Role-based Dashboard Content -->
        @if(auth()->user()->hasRole('system-admin'))
            @include('dashboard.partials.system-admin')
        @elseif(auth()->user()->hasRole('team-admin'))
            @include('dashboard.partials.team-admin')
        @elseif(auth()->user()->hasRole('manager'))
            @include('dashboard.partials.manager')
        @elseif(auth()->user()->hasRole('user'))
            @include('dashboard.partials.user')
        @elseif(auth()->user()->hasRole('viewer'))
            @include('dashboard.partials.viewer')
        @else
            @include('dashboard.partials.default')
        @endif

        <!-- Common Profile Section -->
        <div class="mt-8">
            <div class="bg-gray-800 rounded-xl border border-gray-700 hover:border-gray-600 transition-all duration-300 group">
                <div class="p-6">
                    <div class="flex items-center mb-4">
                        <div class="w-10 h-10 bg-indigo-500/20 rounded-lg flex items-center justify-center mr-3">
                            <svg class="w-6 h-6 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-white">Profile</h3>
                    </div>
                    <p class="text-gray-400 mb-4">Quản lý thông tin cá nhân</p>
                    <a href="{{ route('profile.edit') }}" class="block w-full bg-gray-700 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition-colors duration-200 text-center">Chỉnh sửa Profile</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection