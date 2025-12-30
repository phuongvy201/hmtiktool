<!-- Default Dashboard -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
    <!-- My Profile -->
    <div class="bg-gray-800 rounded-xl border border-gray-700 hover:border-gray-600 transition-all duration-300 group">
        <div class="p-6">
            <div class="flex items-center mb-4">
                <div class="w-10 h-10 bg-indigo-500/20 rounded-lg flex items-center justify-center mr-3">
                    <svg class="w-6 h-6 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-white">My Profile</h3>
            </div>
            <p class="text-gray-400 mb-4">Manage personal information</p>
            <a href="{{ route('profile.edit') }}" class="block w-full bg-gray-700 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition-colors duration-200 text-center">Edit Profile</a>
        </div>
    </div>

    <!-- Help & Support -->
    <div class="bg-gray-800 rounded-xl border border-gray-700 hover:border-gray-600 transition-all duration-300 group">
        <div class="p-6">
            <div class="flex items-center mb-4">
                <div class="w-10 h-10 bg-yellow-500/20 rounded-lg flex items-center justify-center mr-3">
                    <svg class="w-6 h-6 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-white">Help & Support</h3>
            </div>
            <p class="text-gray-400 mb-4">Guides and support</p>
            <a href="#" class="block w-full bg-gray-700 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition-colors duration-200 text-center">View Guides</a>
        </div>
    </div>

    <!-- Contact Admin -->
    <div class="bg-gray-800 rounded-xl border border-gray-700 hover:border-gray-600 transition-all duration-300 group">
        <div class="p-6">
            <div class="flex items-center mb-4">
                <div class="w-10 h-10 bg-blue-500/20 rounded-lg flex items-center justify-center mr-3">
                    <svg class="w-6 h-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-white">Contact Admin</h3>
            </div>
            <p class="text-gray-400 mb-4">Contact administrators</p>
            <a href="#" class="block w-full bg-gray-700 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition-colors duration-200 text-center">Contact</a>
        </div>
    </div>

    <!-- Product Management Component -->
    @include('components.product-management')
</div>

<!-- Default Statistics -->
<div class="bg-gray-800 rounded-xl border border-gray-700 p-6 mb-8">
    <h2 class="text-xl font-semibold text-white mb-6 flex items-center">
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
        </svg>
        Account Overview
    </h2>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-gray-700 rounded-lg p-4 border border-gray-600">
            <div class="flex items-center justify-between">
                <div>
                    <h4 class="text-sm font-medium text-gray-400">My Team</h4>
                    <p class="text-2xl font-bold text-white">{{ auth()->user()->team ? auth()->user()->team->name : 'No Team' }}</p>
                </div>
                <div class="w-8 h-8 bg-purple-500/20 rounded-lg flex items-center justify-center">
                    <svg class="w-4 h-4 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                </div>
            </div>
        </div>
        <div class="bg-gray-700 rounded-lg p-4 border border-gray-600">
            <div class="flex items-center justify-between">
                <div>
                    <h4 class="text-sm font-medium text-gray-400">My Role</h4>
                    <p class="text-2xl font-bold text-white">{{ auth()->user()->roles->first()?->name ?? 'No Role' }}</p>
                </div>
                <div class="w-8 h-8 bg-blue-500/20 rounded-lg flex items-center justify-center">
                    <svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                    </svg>
                </div>
            </div>
        </div>
        <div class="bg-gray-700 rounded-lg p-4 border border-gray-600">
            <div class="flex items-center justify-between">
                <div>
                    <h4 class="text-sm font-medium text-gray-400">Account Status</h4>
                    <p class="text-2xl font-bold text-white">{{ auth()->user()->email_verified_at ? 'Verified' : 'Pending' }}</p>
                </div>
                <div class="w-8 h-8 bg-green-500/20 rounded-lg flex items-center justify-center">
                    <svg class="w-4 h-4 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Welcome Message -->
<div class="bg-gray-800 rounded-xl border border-gray-700 p-6 mb-8">
    <div class="text-center">
        <div class="w-16 h-16 bg-blue-500/20 rounded-full flex items-center justify-center mx-auto mb-4">
            <svg class="w-8 h-8 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
            </svg>
        </div>
        <h3 class="text-xl font-semibold text-white mb-2">Welcome to HMTik!</h3>
        <p class="text-gray-400 mb-4">Your account has not been assigned permissions. Please contact an administrator to get access.</p>
        <div class="flex justify-center space-x-4">
            <a href="{{ route('profile.edit') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg transition-colors duration-200">
                Update Profile
            </a>
            <a href="#" class="bg-gray-600 hover:bg-gray-500 text-white px-6 py-2 rounded-lg transition-colors duration-200">
                Contact Admin
            </a>
        </div>
    </div>
</div>
