@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-slate-900 text-gray-100">
    <div class="container mx-auto px-4 py-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center mb-6">
                <a href="{{ route('dashboard') }}" class="text-blue-400 hover:text-blue-300 mr-4">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                </a>
                <div>
                    <h1 class="text-3xl font-bold text-white mb-2">Personal profile</h1>
                    <p class="text-gray-400">Manage personal information and account security</p>
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

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Left Column - Profile Info -->
            <div class="lg:col-span-1">
                <!-- Profile Card -->
                <div class="bg-gray-800 rounded-xl border border-gray-700 p-6 mb-6">
                    <div class="text-center">
                        <!-- Avatar -->
                        <div class="relative inline-block mb-4">
                            <img src="{{ 'https://ui-avatars.com/api/?name=' . urlencode($user->name) . '&color=7C3AED&background=1F2937&size=128&bold=true' }}" 
                                 alt="{{ $user->display_name }}" 
                                 class="w-24 h-24 rounded-full border-4 border-gray-700">
                        </div>

                        <!-- User Info -->
                        <h2 class="text-xl font-semibold text-white mb-2">{{ $user->display_name }}</h2>
                        <p class="text-gray-400 mb-1">{{ $user->email }}</p>
                        <div class="flex items-center justify-center space-x-2 mb-4">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                {{ $user->primary_role_name }}
                            </span>
                            @if($user->team)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    {{ $user->team_name }}
                                </span>
                            @endif
                            @if(!empty($primaryMarket))
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                    Market: {{ $primaryMarket }}
                                </span>
                            @endif
                        </div>
                        @if(!empty($userMarkets))
                            <p class="text-xs text-gray-400 mb-4">
                                Assigned markets: {{ implode(', ', $userMarkets) }}
                            </p>
                        @endif

                        <!-- Stats -->
                        <div class="grid grid-cols-2 gap-4 text-center">
                            <div class="bg-gray-700 rounded-lg p-3">
                                <div class="text-2xl font-bold text-blue-400">{{ $user->login_count ?? 0 }}</div>
                                <div class="text-xs text-gray-400">Login count</div>
                            </div>
                            <div class="bg-gray-700 rounded-lg p-3">
                                <div class="text-2xl font-bold {{ $user->email_verified_at ? 'text-green-400' : 'text-red-400' }}">
                                    {{ $user->email_verified_at ? '✓' : '✗' }}
                                </div>
                                <div class="text-xs text-gray-400">Email verified</div>
                                @if(!$user->email_verified_at)
                                    <form method="POST" action="{{ route('profile.send-verification-email') }}" class="mt-2">
                                        @csrf
                                        <button type="submit" 
                                                class="text-xs bg-blue-600 hover:bg-blue-700 text-white px-2 py-1 rounded transition-colors duration-200">
                                            Send verification email
                                        </button>
                                    </form>
                                @else
                                    <div class="text-xs text-green-400 mt-1">
                                        Verified: {{ $user->email_verified_at->format('d/m/Y') }}
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="bg-gray-800 rounded-xl border border-gray-700 p-6">
                    <h3 class="text-lg font-semibold text-white mb-4">Quick actions</h3>
                    <div class="space-y-2">
                        <a href="{{ route('profile.security') }}" 
                           class="flex items-center p-3 text-gray-300 hover:bg-gray-700 rounded-lg transition-colors duration-200">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                            </svg>
                            Security
                        </a>
                        <a href="{{ route('profile.activity') }}" 
                           class="flex items-center p-3 text-gray-300 hover:bg-gray-700 rounded-lg transition-colors duration-200">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                            </svg>
                            Activity
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

            <!-- Right Column - Forms -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Profile Information Form -->
                <div class="bg-gray-800 rounded-xl border border-gray-700 p-6">
                    <div class="flex items-center mb-6">
                        <div class="w-10 h-10 bg-blue-500/20 rounded-lg flex items-center justify-center mr-3">
                            <svg class="w-6 h-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                        </div>
                        <h2 class="text-xl font-semibold text-white">Personal information</h2>
                    </div>

                    <form method="post" action="{{ route('profile.update') }}" class="space-y-4">
                        @csrf
                        @method('patch')

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                        <label for="name" class="block text-sm font-medium text-gray-300 mb-2">Name</label>
                                <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}" required
                                       class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-2 text-white placeholder-gray-400 focus:outline-none focus:border-blue-500 @error('name') border-red-500 @enderror">
                                @error('name')
                                    <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-300 mb-2">
                                    Email
                                    @if($user->email_verified_at)
                                        <span class="ml-2 text-xs text-green-400">(Verified)</span>
                                    @else
                                        <span class="ml-2 text-xs text-red-400">(Not verified)</span>
                                    @endif
                                </label>
                                <div class="flex items-center gap-2">
                                    <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}" required
                                           class="flex-1 bg-gray-700 border border-gray-600 rounded-lg px-4 py-2 text-white placeholder-gray-400 focus:outline-none focus:border-blue-500 @error('email') border-red-500 @enderror">
                                    @if(!$user->email_verified_at)
                                        <button type="button"
                                                onclick="document.getElementById('verify-email-form').submit()"
                                                class="px-3 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm rounded-lg transition-colors duration-200 whitespace-nowrap">
                                            <i class="fas fa-envelope mr-1"></i>
                                            Send verification email
                                        </button>
                                    @endif
                                </div>
                                @error('email')
                                    <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="flex justify-end pt-4">
                            <button type="submit" 
                                    class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition-colors duration-200">
                                <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                Update information
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Password Update Form -->
                <div class="bg-gray-800 rounded-xl border border-gray-700 p-6">
                    <div class="flex items-center mb-6">
                        <div class="w-10 h-10 bg-green-500/20 rounded-lg flex items-center justify-center mr-3">
                            <svg class="w-6 h-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                            </svg>
                        </div>
                        <h2 class="text-xl font-semibold text-white">Change password</h2>
                    </div>

                    <form method="post" action="{{ route('profile.password') }}" class="space-y-4">
                        @csrf
                        @method('put')

                        <div>
                            <label for="current_password" class="block text-sm font-medium text-gray-300 mb-2">Current password</label>
                            <input type="password" id="current_password" name="current_password" required
                                   class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-2 text-white placeholder-gray-400 focus:outline-none focus:border-blue-500 @error('current_password') border-red-500 @enderror">
                            @error('current_password')
                                <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="password" class="block text-sm font-medium text-gray-300 mb-2">New password</label>
                                <input type="password" id="password" name="password" required
                                       class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-2 text-white placeholder-gray-400 focus:outline-none focus:border-blue-500 @error('password') border-red-500 @enderror">
                                @error('password')
                                    <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="password_confirmation" class="block text-sm font-medium text-gray-300 mb-2">Confirm password</label>
                                <input type="password" id="password_confirmation" name="password_confirmation" required
                                       class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-2 text-white placeholder-gray-400 focus:outline-none focus:border-blue-500">
                            </div>
                        </div>

                        <div class="flex justify-end pt-4">
                            <button type="submit" 
                                    class="px-6 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg font-medium transition-colors duration-200">
                                <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                </svg>
                                Change password
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Danger Zone -->
                <div class="bg-red-500/10 border border-red-500/20 rounded-xl p-6">
                    <div class="flex items-center mb-6">
                        <div class="w-10 h-10 bg-red-500/20 rounded-lg flex items-center justify-center mr-3">
                            <svg class="w-6 h-6 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                            </svg>
                        </div>
                        <h2 class="text-xl font-semibold text-white">Danger zone</h2>
                    </div>

                    <div class="space-y-4">
                        <div class="flex items-center justify-between p-4 bg-red-500/10 rounded-lg border border-red-500/20">
                            <div>
                                        <h3 class="text-lg font-medium text-white">Delete account</h3>
                                <p class="text-gray-400 text-sm">Permanently delete your account and all your data</p>
                            </div>
                            <button onclick="showDeleteModal()" 
                                    class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg font-medium transition-colors duration-200">
                                Delete account
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Hidden Verify Email Form -->
<form id="verify-email-form" method="POST" action="{{ route('profile.send-verification-email') }}" class="hidden">
    @csrf
</form>

<!-- Delete Account Modal -->
<div id="deleteModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-gray-800 rounded-xl border border-gray-700 p-6 w-full max-w-md">
            <h3 class="text-lg font-semibold text-white mb-4">Confirm delete account</h3>
            <p class="text-gray-400 mb-6">This action cannot be undone. All your data will be permanently deleted.</p>
            
            <form method="post" action="{{ route('profile.destroy') }}" class="space-y-4">
                @csrf
                @method('delete')
                
                <div>
                    <label for="delete_password" class="block text-sm font-medium text-gray-300 mb-2">Enter password to confirm</label>
                    <input type="password" id="delete_password" name="password" required
                           class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-2 text-white placeholder-gray-400 focus:outline-none focus:border-red-500">
                </div>
                
                <div class="flex justify-end space-x-2">
                    <button type="button" onclick="hideDeleteModal()"
                            class="px-4 py-2 border border-gray-600 text-gray-300 rounded-lg hover:bg-gray-700 transition-colors duration-200">
                        Cancel
                    </button>
                    <button type="submit"
                            class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition-colors duration-200">
                                    Confirm delete
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function showDeleteModal() {
    document.getElementById('deleteModal').classList.remove('hidden');
}

function hideDeleteModal() {
    document.getElementById('deleteModal').classList.add('hidden');
}

// Close modal when clicking outside
document.getElementById('deleteModal').addEventListener('click', function(e) {
    if (e.target === this) {
        hideDeleteModal();
    }
});
</script>
@endsection
