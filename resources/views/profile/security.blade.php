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
                    <h1 class="text-3xl font-bold text-white mb-2">Security</h1>
                    <p class="text-gray-400">Manage security settings and verification</p>
                </div>
            </div>
        </div>

        <!-- Success/Error Messages -->
        @if(session('success'))
            <div class="bg-green-500/20 border border-green-500/50 text-green-400 px-4 py-3 rounded-lg mb-6">
                {{ session('success') }}
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Security Overview -->
            <div class="space-y-6">
                <!-- Account Status -->
                <div class="bg-gray-800 rounded-xl border border-gray-700 p-6">
                    <div class="flex items-center mb-6">
                        <div class="w-10 h-10 bg-blue-500/20 rounded-lg flex items-center justify-center mr-3">
                            <svg class="w-6 h-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <h2 class="text-xl font-semibold text-white">Account status</h2>
                    </div>

                    <div class="space-y-4">
                        <div class="flex items-center justify-between p-4 bg-gray-700 rounded-lg">
                            <div class="flex items-center">
                                <div class="w-8 h-8 rounded-full flex items-center justify-center mr-3 {{ $securityInfo['email_verified'] ? 'bg-green-500/20' : 'bg-red-500/20' }}">
                                    @if($securityInfo['email_verified'])
                                        <svg class="w-4 h-4 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                    @else
                                        <svg class="w-4 h-4 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                        </svg>
                                    @endif
                                </div>
                                <div>
                                    <h3 class="text-white font-medium">Email verification</h3>
                                    <p class="text-gray-400 text-sm">
                                        {{ $securityInfo['email_verified'] ? 'Verified' : 'Not verified' }}
                                    </p>
                                </div>
                            </div>
                            @if(!$securityInfo['email_verified'])
                                <a href="{{ route('verification.notice') }}" 
                                   class="px-3 py-1 bg-blue-600 hover:bg-blue-700 text-white text-sm rounded-lg transition-colors duration-200">
                                    Verify
                                </a>
                            @endif
                        </div>

                        <div class="flex items-center justify-between p-4 bg-gray-700 rounded-lg">
                            <div class="flex items-center">
                                <div class="w-8 h-8 rounded-full flex items-center justify-center mr-3 {{ $securityInfo['two_factor_enabled'] ? 'bg-green-500/20' : 'bg-gray-500/20' }}">
                                    @if($securityInfo['two_factor_enabled'])
                                        <svg class="w-4 h-4 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                    @else
                                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                        </svg>
                                    @endif
                                </div>
                                <div>
                                    <h3 class="text-white font-medium">Two-factor authentication</h3>
                                    <p class="text-gray-400 text-sm">
                                        {{ $securityInfo['two_factor_enabled'] ? 'Enabled' : 'Not enabled' }}
                                    </p>
                                </div>
                            </div>
                            <form method="POST" action="{{ route('profile.two-factor') }}" class="inline">
                                @csrf
                                <button type="submit" 
                                        class="px-3 py-1 {{ $securityInfo['two_factor_enabled'] ? 'bg-red-600 hover:bg-red-700' : 'bg-green-600 hover:bg-green-700' }} text-white text-sm rounded-lg transition-colors duration-200">
                                    {{ $securityInfo['two_factor_enabled'] ? 'Disable' : 'Enable' }}
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Login History -->
                <div class="bg-gray-800 rounded-xl border border-gray-700 p-6">
                    <div class="flex items-center mb-6">
                        <div class="w-10 h-10 bg-purple-500/20 rounded-lg flex items-center justify-center mr-3">
                            <svg class="w-6 h-6 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                            </svg>
                        </div>
                        <h2 class="text-xl font-semibold text-white">Login history</h2>
                    </div>

                    <div class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div class="bg-gray-700 rounded-lg p-4 text-center">
                                <div class="text-2xl font-bold text-blue-400">{{ $securityInfo['login_count'] }}</div>
                                <div class="text-sm text-gray-400">Total login count</div>
                            </div>
                            <div class="bg-gray-700 rounded-lg p-4 text-center">
                                <div class="text-sm text-gray-400">Last login</div>
                                <div class="text-sm text-white">{{ $securityInfo['last_login'] }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Security Settings -->
            <div class="space-y-6">
                <!-- Password Settings -->
                <div class="bg-gray-800 rounded-xl border border-gray-700 p-6">
                    <div class="flex items-center mb-6">
                        <div class="w-10 h-10 bg-green-500/20 rounded-lg flex items-center justify-center mr-3">
                            <svg class="w-6 h-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                            </svg>
                        </div>
                        <h2 class="text-xl font-semibold text-white">Password settings</h2>
                    </div>

                    <div class="space-y-4">
                        <div class="p-4 bg-gray-700 rounded-lg">
                            <h3 class="text-white font-medium mb-2">Password strength</h3>
                            <div class="flex items-center space-x-2">
                                <div class="flex-1 bg-gray-600 rounded-full h-2">
                                    <div class="bg-green-500 h-2 rounded-full" style="width: 75%"></div>
                                </div>
                                <span class="text-sm text-gray-400">Strong</span>
                            </div>
                        </div>

                        <div class="p-4 bg-gray-700 rounded-lg">
                            <h3 class="text-white font-medium mb-2">Password requirements</h3>
                            <ul class="text-sm text-gray-400 space-y-1">
                                <li class="flex items-center">
                                    <svg class="w-4 h-4 text-green-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    At least 8 characters
                                </li>
                                <li class="flex items-center">
                                    <svg class="w-4 h-4 text-green-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    Include uppercase and lowercase letters
                                </li>
                                <li class="flex items-center">
                                    <svg class="w-4 h-4 text-green-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    Include numbers and special characters
                                </li>
                            </ul>
                        </div>

                        <a href="{{ route('profile.edit') }}" 
                           class="block w-full text-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors duration-200">
                            Change password
                        </a>
                    </div>
                </div>

                <!-- Session Management -->
                <div class="bg-gray-800 rounded-xl border border-gray-700 p-6">
                    <div class="flex items-center mb-6">
                        <div class="w-10 h-10 bg-orange-500/20 rounded-lg flex items-center justify-center mr-3">
                            <svg class="w-6 h-6 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                            </svg>
                        </div>
                        <h2 class="text-xl font-semibold text-white">Session management</h2>
                    </div>

                    <div class="space-y-4">
                        <div class="p-4 bg-gray-700 rounded-lg">
                            <h3 class="text-white font-medium mb-2">Current session</h3>
                            <p class="text-sm text-gray-400 mb-3">Thiết bị này</p>
                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    <div class="w-8 h-8 bg-green-500/20 rounded-full flex items-center justify-center mr-3">
                                        <svg class="w-4 h-4 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="text-white text-sm">Desktop - Windows</p>
                                                <p class="text-gray-400 text-xs">Active</p>
                                    </div>
                                </div>
                                <span class="text-green-400 text-sm">Current</span>
                            </div>
                        </div>

                        <form method="POST" action="{{ route('logout') }}" class="block">
                            @csrf
                            <button type="submit" 
                                    class="w-full px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition-colors duration-200">
                                Log out all devices
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Security Tips -->
                <div class="bg-blue-500/10 border border-blue-500/20 rounded-xl p-6">
                    <div class="flex items-center mb-4">
                        <div class="w-8 h-8 bg-blue-500/20 rounded-lg flex items-center justify-center mr-3">
                            <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                            <h3 class="text-lg font-semibold text-white">Security tips</h3>
                    </div>
                    <ul class="text-sm text-gray-300 space-y-2">
                        <li>• Use a strong password and do not share it with anyone</li>
                        <li>• Enable two-factor authentication to enhance security</li>
                        <li>• Log out when using public devices</li>
                        <li>• Regularly check login activity</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
