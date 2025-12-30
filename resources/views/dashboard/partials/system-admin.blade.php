<!-- System Admin Dashboard -->
<!-- System Management Section -->
<div class="mb-8">
    <h2 class="text-xl font-semibold text-white mb-6 flex items-center">
        <i class="fas fa-cogs mr-2 text-blue-400"></i>
        System Management
    </h2>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <!-- User & Role Management -->
        <div class="bg-gray-800 rounded-xl border border-gray-700 hover:border-gray-600 transition-all duration-300 group">
            <div class="p-6">
                <div class="flex items-center mb-4">
                    <div class="w-10 h-10 bg-blue-500/20 rounded-lg flex items-center justify-center mr-3">
                        <i class="fas fa-users text-blue-400"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-white">User & Role Management</h3>
                </div>
                <p class="text-gray-400 mb-4">Manage users and permissions</p>
                <div class="space-y-2">
                    <a href="{{ route('users.index') }}" class="block w-full bg-gray-700 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition-colors duration-200 text-center">
                        <i class="fas fa-user-friends mr-2"></i>Manage Users
                    </a>
                    <a href="{{ route('roles.index') }}" class="block w-full bg-gray-700 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition-colors duration-200 text-center">
                        <i class="fas fa-shield-alt mr-2"></i>Manage Roles
                    </a>
                </div>
            </div>
        </div>

        <!-- Team Management -->
        <div class="bg-gray-800 rounded-xl border border-gray-700 hover:border-gray-600 transition-all duration-300 group">
            <div class="p-6">
                <div class="flex items-center mb-4">
                    <div class="w-10 h-10 bg-purple-500/20 rounded-lg flex items-center justify-center mr-3">
                        <i class="fas fa-users-cog text-purple-400"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-white">Team Management</h3>
                </div>
                <p class="text-gray-400 mb-4">Manage teams and organization</p>
                <div class="space-y-2">
                    <a href="{{ route('teams.index') }}" class="block w-full bg-gray-700 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition-colors duration-200 text-center">
                        <i class="fas fa-building mr-2"></i>Manage Teams
                    </a>
                    <a href="{{ route('team-subscriptions.index') }}" class="block w-full bg-gray-700 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition-colors duration-200 text-center">
                        <i class="fas fa-box mr-2"></i>Service Packages
                    </a>
                </div>
            </div>
        </div>

        <!-- System Configuration -->
        <div class="bg-gray-800 rounded-xl border border-gray-700 hover:border-gray-600 transition-all duration-300 group">
            <div class="p-6">
                <div class="flex items-center mb-4">
                    <div class="w-10 h-10 bg-yellow-500/20 rounded-lg flex items-center justify-center mr-3">
                        <i class="fas fa-cog text-yellow-400"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-white">System Configuration</h3>
                </div>
                <p class="text-gray-400 mb-4">Configure and maintain the system</p>
                <div class="space-y-2">
                    <a href="{{ route('system.settings') }}" class="block w-full bg-gray-700 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition-colors duration-200 text-center">
                        <i class="fas fa-sliders-h mr-2"></i>System Settings
                    </a>
                    <a href="{{ route('backups.index') }}" class="block w-full bg-gray-700 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition-colors duration-200 text-center">
                        <i class="fas fa-database mr-2"></i>Backup & Restore
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- TikTok Shop Management Section -->
<div class="mb-8">
    <h2 class="text-xl font-semibold text-white mb-6 flex items-center">
        <i class="fab fa-tiktok mr-2 text-pink-400"></i>
        TikTok Shop Management
    </h2>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <!-- TikTok Shop Integration -->
        <div class="bg-gray-800 rounded-xl border border-gray-700 hover:border-gray-600 transition-all duration-300 group">
            <div class="p-6">
                <div class="flex items-center mb-4">
                    <div class="w-10 h-10 bg-pink-500/20 rounded-lg flex items-center justify-center mr-3">
                        <i class="fab fa-tiktok text-pink-400"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-white">TikTok Shop Integration</h3>
                </div>
                <p class="text-gray-400 mb-4">Manage TikTok Shop connections</p>
                <div class="space-y-2">
                    <a href="{{ route('tiktok-shop.index') }}" class="block w-full bg-gray-700 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition-colors duration-200 text-center">
                        <i class="fas fa-link mr-2"></i>Manage Connections
                    </a>
                    <a href="{{ route('tiktok.orders.index') }}" class="block w-full bg-gray-700 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition-colors duration-200 text-center">
                        <i class="fas fa-shopping-bag mr-2"></i>Orders
                    </a>
                </div>
            </div>
        </div>

        <!-- Analytics & Monitoring -->
        <div class="bg-gray-800 rounded-xl border border-gray-700 hover:border-gray-600 transition-all duration-300 group">
            <div class="p-6">
                <div class="flex items-center mb-4">
                    <div class="w-10 h-10 bg-purple-500/20 rounded-lg flex items-center justify-center mr-3">
                        <i class="fas fa-chart-bar text-purple-400"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-white">Analytics & Monitoring</h3>
                </div>
                <p class="text-gray-400 mb-4">Analyze and monitor the system</p>
                <div class="space-y-2">
                    <a href="{{ route('tiktok.analytics.index') }}" class="block w-full bg-gray-700 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition-colors duration-200 text-center">
                        <i class="fas fa-chart-line mr-2"></i>Shop Analytics
                    </a>
                    <a href="{{ route('tiktok.performance.index') }}" class="block w-full bg-gray-700 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition-colors duration-200 text-center">
                        <i class="fas fa-chart-area mr-2"></i>Performance
                    </a>
                </div>
            </div>
        </div>

        <!-- Financial Management -->
        <div class="bg-gray-800 rounded-xl border border-gray-700 hover:border-gray-600 transition-all duration-300 group">
            <div class="p-6">
                <div class="flex items-center mb-4">
                    <div class="w-10 h-10 bg-green-500/20 rounded-lg flex items-center justify-center mr-3">
                        <i class="fas fa-dollar-sign text-green-400"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-white">Financial Management</h3>
                </div>
                <p class="text-gray-400 mb-4">Manage finances and reports</p>
                <div class="space-y-2">
                    <a href="{{ route('tiktok.finance.index') }}" class="block w-full bg-gray-700 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition-colors duration-200 text-center">
                        <i class="fas fa-chart-pie mr-2"></i>TikTok Finance
                    </a>
                    <a href="{{ route('service-packages.index') }}" class="block w-full bg-gray-700 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition-colors duration-200 text-center">
                        <i class="fas fa-credit-card mr-2"></i>Service Packages
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- System Statistics -->
<div class="bg-gray-800 rounded-xl border border-gray-700 p-6 mb-8">
    <h2 class="text-xl font-semibold text-white mb-6 flex items-center">
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
        </svg>
        System Overview
    </h2>
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-gray-700 rounded-lg p-4 border border-gray-600">
            <div class="flex items-center justify-between">
                <div>
                    <h4 class="text-sm font-medium text-gray-400">Total Users</h4>
                    <p class="text-2xl font-bold text-white">{{ \App\Models\User::count() }}</p>
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
                    <h4 class="text-sm font-medium text-gray-400">Active Teams</h4>
                    <p class="text-2xl font-bold text-white">{{ \App\Models\Team::where('status', 'active')->count() }}</p>
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
                    <h4 class="text-sm font-medium text-gray-400">System Users</h4>
                    <p class="text-2xl font-bold text-white">{{ \App\Models\User::where('is_system_user', true)->count() }}</p>
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
                    <h4 class="text-sm font-medium text-gray-400">Team Users</h4>
                    <p class="text-2xl font-bold text-white">{{ \App\Models\User::where('is_system_user', false)->count() }}</p>
                </div>
                <div class="w-8 h-8 bg-orange-500/20 rounded-lg flex items-center justify-center">
                    <svg class="w-4 h-4 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                </div>
            </div>
        </div>
        @can('view-service-packages')
        <div class="bg-gray-700 rounded-lg p-4 border border-gray-600">
            <div class="flex items-center justify-between">
                <div>
                    <h4 class="text-sm font-medium text-gray-400">Active Packages</h4>
                    <p class="text-2xl font-bold text-white">{{ \App\Models\ServicePackage::where('is_active', true)->count() }}</p>
                </div>
                <div class="w-8 h-8 bg-indigo-500/20 rounded-lg flex items-center justify-center">
                    <svg class="w-4 h-4 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                    </svg>
                </div>
            </div>
        </div>
        <div class="bg-gray-700 rounded-lg p-4 border border-gray-600">
            <div class="flex items-center justify-between">
                <div>
                    <h4 class="text-sm font-medium text-gray-400">Active Subscriptions</h4>
                    <p class="text-2xl font-bold text-white">{{ \App\Models\UserSubscription::where('status', 'active')->count() }}</p>
                </div>
                <div class="w-8 h-8 bg-green-500/20 rounded-lg flex items-center justify-center">
                    <svg class="w-4 h-4 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
        </div>
        @endcan
    </div>
</div>

<!-- Quick Actions -->
@can('view-service-packages')
<div class="bg-gray-800 rounded-xl border border-gray-700 p-6 mb-8">
    <h2 class="text-xl font-semibold text-white mb-6 flex items-center">
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
        </svg>
        Quick Actions
    </h2>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <a href="{{ route('service-packages.index') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-3 rounded-lg transition-colors duration-200 flex items-center justify-center">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
            </svg>
            Manage Service Packages
        </a>
        @can('create-service-packages')
        <a href="{{ route('service-packages.create') }}" class="bg-green-600 hover:bg-green-700 text-white px-4 py-3 rounded-lg transition-colors duration-200 flex items-center justify-center">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
            </svg>
            Create New Package
        </a>
        @endcan
        <a href="{{ route('users.index') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-3 rounded-lg transition-colors duration-200 flex items-center justify-center">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
            </svg>
            Manage Users
        </a>
        <a href="{{ route('products.index') }}" class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-3 rounded-lg transition-colors duration-200 flex items-center justify-center">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
            </svg>
            Manage Products
        </a>
    </div>
</div>
@endcan
