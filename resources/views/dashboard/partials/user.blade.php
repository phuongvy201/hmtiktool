<!-- User Dashboard -->
<!-- Overview Section -->
<div class="mb-8">
    <h2 class="text-xl font-semibold text-white mb-6 flex items-center">
        <i class="fas fa-eye mr-2 text-blue-400"></i>
        View Information
    </h2>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <!-- Team & Organization -->
        <div class="bg-gray-800 rounded-xl border border-gray-700 hover:border-gray-600 transition-all duration-300 group">
            <div class="p-6">
                <div class="flex items-center mb-4">
                    <div class="w-10 h-10 bg-blue-500/20 rounded-lg flex items-center justify-center mr-3">
                        <i class="fas fa-users text-blue-400"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-white">Team & Organization</h3>
                </div>
                <p class="text-gray-400 mb-4">View team and organization details</p>
                <div class="space-y-2">
                    @can('view-teams')
                    <a href="{{ route('teams.index') }}" class="block w-full bg-gray-700 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition-colors duration-200 text-center">
                        <i class="fas fa-building mr-2"></i>View Teams
                    </a>
                    @endcan
                    @can('view-users')
                    <a href="{{ route('users.index') }}" class="block w-full bg-gray-700 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition-colors duration-200 text-center">
                        <i class="fas fa-user-friends mr-2"></i>View Users
                    </a>
                    @endcan
                </div>
            </div>
        </div>

        <!-- Business Operations -->
        <div class="bg-gray-800 rounded-xl border border-gray-700 hover:border-gray-600 transition-all duration-300 group">
            <div class="p-6">
                <div class="flex items-center mb-4">
                    <div class="w-10 h-10 bg-green-500/20 rounded-lg flex items-center justify-center mr-3">
                        <i class="fas fa-briefcase text-green-400"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-white">Business Operations</h3>
                </div>
                <p class="text-gray-400 mb-4">View business operations</p>
                <div class="space-y-2">
                    <a href="{{ route('sales.index') }}" class="block w-full bg-gray-700 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition-colors duration-200 text-center">
                        <i class="fas fa-chart-line mr-2"></i>View Sales
                    </a>
                    <a href="{{ route('fulfillment.index') }}" class="block w-full bg-gray-700 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition-colors duration-200 text-center">
                        <i class="fas fa-box mr-2"></i>View Fulfillment
                    </a>
                </div>
            </div>
        </div>

        <!-- Reports & Analytics -->
        <div class="bg-gray-800 rounded-xl border border-gray-700 hover:border-gray-600 transition-all duration-300 group">
            <div class="p-6">
                <div class="flex items-center mb-4">
                    <div class="w-10 h-10 bg-purple-500/20 rounded-lg flex items-center justify-center mr-3">
                        <i class="fas fa-chart-bar text-purple-400"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-white">Reports & Analytics</h3>
                </div>
                <p class="text-gray-400 mb-4">View reports and analytics</p>
                <div class="space-y-2">
                    <a href="{{ route('financial.reports') }}" class="block w-full bg-gray-700 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition-colors duration-200 text-center">
                        <i class="fas fa-dollar-sign mr-2"></i>Financial Reports
                    </a>
                    <a href="{{ route('tiktok.analytics.index') }}" class="block w-full bg-gray-700 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition-colors duration-200 text-center">
                        <i class="fas fa-chart-pie mr-2"></i>TikTok Analytics
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Personal Section -->
<div class="mb-8">
    <h2 class="text-xl font-semibold text-white mb-6 flex items-center">
        <i class="fas fa-user mr-2 text-indigo-400"></i>
        Personal
    </h2>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <!-- My Profile -->
        <div class="bg-gray-800 rounded-xl border border-gray-700 hover:border-gray-600 transition-all duration-300 group">
            <div class="p-6">
                <div class="flex items-center mb-4">
                    <div class="w-10 h-10 bg-indigo-500/20 rounded-lg flex items-center justify-center mr-3">
                        <i class="fas fa-user-circle text-indigo-400"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-white">My Profile</h3>
                </div>
                <p class="text-gray-400 mb-4">Manage personal information</p>
                <a href="{{ route('profile.edit') }}" class="block w-full bg-gray-700 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition-colors duration-200 text-center">
                    <i class="fas fa-edit mr-2"></i>Edit Profile
                </a>
            </div>
        </div>

        <!-- Product Management -->
        @include('components.product-management')
    </div>
</div>

<!-- User Statistics -->
<div class="bg-gray-800 rounded-xl border border-gray-700 p-6 mb-8">
    <h2 class="text-xl font-semibold text-white mb-6 flex items-center">
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
        </svg>
        User Overview
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
