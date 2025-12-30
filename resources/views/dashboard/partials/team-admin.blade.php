<!-- Team Admin Dashboard -->
<!-- Team Management Section -->
<div class="mb-8">
    <h2 class="text-xl font-semibold text-white mb-6 flex items-center">
        <i class="fas fa-users-cog mr-2 text-blue-400"></i>
        Team Management
    </h2>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <!-- Team Management -->
        <div class="bg-gray-800 rounded-xl border border-gray-700 hover:border-gray-600 transition-all duration-300 group">
            <div class="p-6">
                <div class="flex items-center mb-4">
                    <div class="w-10 h-10 bg-blue-500/20 rounded-lg flex items-center justify-center mr-3">
                        <i class="fas fa-users text-blue-400"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-white">Team Management</h3>
                </div>
                <p class="text-gray-400 mb-4">Manage members and team information</p>
                <div class="space-y-2">
                    <a href="{{ route('team-admin.users.index') }}" class="block w-full bg-gray-700 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition-colors duration-200 text-center">
                        <i class="fas fa-user-friends mr-2"></i>Manage Members
                    </a>
                    <a href="{{ route('teams.show', auth()->user()->team) }}" class="block w-full bg-gray-700 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition-colors duration-200 text-center">
                        <i class="fas fa-info-circle mr-2"></i>Team Details
                    </a>
                </div>
            </div>
        </div>

        <!-- TikTok Shop Integration -->
        <div class="bg-gray-800 rounded-xl border border-gray-700 hover:border-gray-600 transition-all duration-300 group">
            <div class="p-6">
                <div class="flex items-center mb-4">
                    <div class="w-10 h-10 bg-pink-500/20 rounded-lg flex items-center justify-center mr-3">
                        <i class="fab fa-tiktok text-pink-400"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-white">TikTok Shop</h3>
                </div>
                <p class="text-gray-400 mb-4">Connect and manage TikTok Shop</p>
                <div class="space-y-2">
                    <a href="{{ route('team.tiktok-shop.index') }}" class="block w-full bg-gray-700 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition-colors duration-200 text-center">
                        <i class="fas fa-link mr-2"></i>Connect TikTok
                    </a>
                    <a href="{{ route('tiktok.orders.index') }}" class="block w-full bg-gray-700 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition-colors duration-200 text-center">
                        <i class="fas fa-shopping-bag mr-2"></i>Orders
                    </a>
                </div>
            </div>
        </div>

        <!-- Analytics & Reports -->
        <div class="bg-gray-800 rounded-xl border border-gray-700 hover:border-gray-600 transition-all duration-300 group">
            <div class="p-6">
                <div class="flex items-center mb-4">
                    <div class="w-10 h-10 bg-purple-500/20 rounded-lg flex items-center justify-center mr-3">
                        <i class="fas fa-chart-bar text-purple-400"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-white">Analytics & Reports</h3>
                </div>
                <p class="text-gray-400 mb-4">Analyze and report for the team</p>
                <div class="space-y-2">
                    <a href="{{ route('tiktok.analytics.index') }}" class="block w-full bg-gray-700 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition-colors duration-200 text-center">
                        <i class="fas fa-chart-line mr-2"></i>Shop Analytics
                    </a>
                    <a href="{{ route('tiktok.finance.index') }}" class="block w-full bg-gray-700 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition-colors duration-200 text-center">
                        <i class="fas fa-dollar-sign mr-2"></i>Finance Reports
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Business Operations Section -->
<div class="mb-8">
    <h2 class="text-xl font-semibold text-white mb-6 flex items-center">
        <i class="fas fa-briefcase mr-2 text-green-400"></i>
        Business Operations
    </h2>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <!-- Sales & Fulfillment -->
        <div class="bg-gray-800 rounded-xl border border-gray-700 hover:border-gray-600 transition-all duration-300 group">
            <div class="p-6">
                <div class="flex items-center mb-4">
                    <div class="w-10 h-10 bg-green-500/20 rounded-lg flex items-center justify-center mr-3">
                        <i class="fas fa-chart-line text-green-400"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-white">Sales & Fulfillment</h3>
                </div>
                <p class="text-gray-400 mb-4">Manage sales and fulfillment</p>
                <div class="space-y-2">
                    <a href="{{ route('sales.index') }}" class="block w-full bg-gray-700 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition-colors duration-200 text-center">
                        <i class="fas fa-trending-up mr-2"></i>Manage Sales
                    </a>
                    <a href="{{ route('fulfillment.index') }}" class="block w-full bg-gray-700 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition-colors duration-200 text-center">
                        <i class="fas fa-box mr-2"></i>Manage Fulfillment
                    </a>
                </div>
            </div>
        </div>

        <!-- Product Management -->
        @include('components.product-management')

        <!-- Performance & Monitoring -->
        <div class="bg-gray-800 rounded-xl border border-gray-700 hover:border-gray-600 transition-all duration-300 group">
            <div class="p-6">
                <div class="flex items-center mb-4">
                    <div class="w-10 h-10 bg-orange-500/20 rounded-lg flex items-center justify-center mr-3">
                        <i class="fas fa-tachometer-alt text-orange-400"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-white">Performance</h3>
                </div>
                <p class="text-gray-400 mb-4">Monitor team performance</p>
                <div class="space-y-2">
                    <a href="{{ route('tiktok.performance.index') }}" class="block w-full bg-gray-700 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition-colors duration-200 text-center">
                        <i class="fas fa-chart-area mr-2"></i>GMV Performance
                    </a>
                    <a href="{{ route('team-admin.roles.index') }}" class="block w-full bg-gray-700 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition-colors duration-200 text-center">
                        <i class="fas fa-shield-alt mr-2"></i>Team Roles
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Team Statistics -->
<div class="bg-gray-800 rounded-xl border border-gray-700 p-6 mb-8">
    <h2 class="text-xl font-semibold text-white mb-6 flex items-center">
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
        </svg>
        Team Overview - {{ auth()->user()->team->name }}
    </h2>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-gray-700 rounded-lg p-4 border border-gray-600">
            <div class="flex items-center justify-between">
                <div>
                    <h4 class="text-sm font-medium text-gray-400">Team Members</h4>
                    <p class="text-2xl font-bold text-white">{{ auth()->user()->team->users()->count() }}</p>
                </div>
                <div class="w-8 h-8 bg-blue-500/20 rounded-lg flex items-center justify-center">
                    <svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                    </svg>
                </div>
            </div>
        </div>
        <div class="bg-gray-700 rounded-lg p-4 border border-gray-600">
            <div class="flex items-center justify-between">
                <div>
                    <h4 class="text-sm font-medium text-gray-400">Active Members</h4>
                    <p class="text-2xl font-bold text-white">{{ auth()->user()->team->users()->where('email_verified_at', '!=', null)->count() }}</p>
                </div>
                <div class="w-8 h-8 bg-green-500/20 rounded-lg flex items-center justify-center">
                    <svg class="w-4 h-4 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
        </div>
        <div class="bg-gray-700 rounded-lg p-4 border border-gray-600">
            <div class="flex items-center justify-between">
                <div>
                    <h4 class="text-sm font-medium text-gray-400">Team Status</h4>
                    <p class="text-2xl font-bold text-white">{{ ucfirst(auth()->user()->team->status) }}</p>
                </div>
                <div class="w-8 h-8 bg-purple-500/20 rounded-lg flex items-center justify-center">
                    <svg class="w-4 h-4 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                </div>
            </div>
        </div>
    </div>
</div>
