<!-- Manager Dashboard -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
    <!-- User Management -->
    <div class="bg-gray-800 rounded-xl border border-gray-700 hover:border-gray-600 transition-all duration-300 group">
        <div class="p-6">
            <div class="flex items-center mb-4">
                <div class="w-10 h-10 bg-blue-500/20 rounded-lg flex items-center justify-center mr-3">
                    <svg class="w-6 h-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-white">User Management</h3>
            </div>
            <p class="text-gray-400 mb-4">Manage users and permissions</p>
            <div class="space-y-2">
                <a href="{{ route('users.index') }}" class="block w-full bg-gray-700 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition-colors duration-200 text-center">Manage Users</a>
                <a href="{{ route('roles.index') }}" class="block w-full bg-gray-700 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition-colors duration-200 text-center">Manage Roles</a>
            </div>
        </div>
    </div>

    <!-- Team Management -->
    <div class="bg-gray-800 rounded-xl border border-gray-700 hover:border-gray-600 transition-all duration-300 group">
        <div class="p-6">
            <div class="flex items-center mb-4">
                <div class="w-10 h-10 bg-purple-500/20 rounded-lg flex items-center justify-center mr-3">
                    <svg class="w-6 h-6 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-white">Team Management</h3>
            </div>
            <p class="text-gray-400 mb-4">Manage teams and organization</p>
            <a href="{{ route('teams.index') }}" class="block w-full bg-gray-700 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition-colors duration-200 text-center">Manage Teams</a>
        </div>
    </div>

    <!-- Financial Reports -->
    <div class="bg-gray-800 rounded-xl border border-gray-700 hover:border-gray-600 transition-all duration-300 group">
        <div class="p-6">
            <div class="flex items-center mb-4">
                <div class="w-10 h-10 bg-green-500/20 rounded-lg flex items-center justify-center mr-3">
                    <svg class="w-6 h-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-white">Financial Reports</h3>
            </div>
            <p class="text-gray-400 mb-4">Financial and accounting reports</p>
            <a href="{{ route('financial.reports') }}" class="block w-full bg-gray-700 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition-colors duration-200 text-center">View Reports</a>
        </div>
    </div>

    <!-- Fulfillment -->
    <div class="bg-gray-800 rounded-xl border border-gray-700 hover:border-gray-600 transition-all duration-300 group">
        <div class="p-6">
            <div class="flex items-center mb-4">
                <div class="w-10 h-10 bg-orange-500/20 rounded-lg flex items-center justify-center mr-3">
                    <svg class="w-6 h-6 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-white">Fulfillment</h3>
            </div>
            <p class="text-gray-400 mb-4">Manage fulfillment and orders</p>
            <a href="{{ route('fulfillment.index') }}" class="block w-full bg-gray-700 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition-colors duration-200 text-center">Manage Fulfillment</a>
        </div>
    </div>

    <!-- Sales -->
    <div class="bg-gray-800 rounded-xl border border-gray-700 hover:border-gray-600 transition-all duration-300 group">
        <div class="p-6">
            <div class="flex items-center mb-4">
                <div class="w-10 h-10 bg-red-500/20 rounded-lg flex items-center justify-center mr-3">
                    <svg class="w-6 h-6 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-white">Sales</h3>
            </div>
            <p class="text-gray-400 mb-4">Manage sales and revenue</p>
            <a href="{{ route('sales.index') }}" class="block w-full bg-gray-700 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition-colors duration-200 text-center">Manage Sales</a>
        </div>
    </div>

    <!-- Analytics -->
    <div class="bg-gray-800 rounded-xl border border-gray-700 hover:border-gray-600 transition-all duration-300 group">
        <div class="p-6">
            <div class="flex items-center mb-4">
                <div class="w-10 h-10 bg-indigo-500/20 rounded-lg flex items-center justify-center mr-3">
                    <svg class="w-6 h-6 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-white">Analytics</h3>
            </div>
            <p class="text-gray-400 mb-4">Data analysis and reporting</p>
            <a href="#" class="block w-full bg-gray-700 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition-colors duration-200 text-center">View Analytics</a>
        </div>
    </div>

    <!-- Product Management Component -->
    @include('components.product-management')

    <!-- Product Templates -->
    <div class="bg-gray-800 rounded-xl border border-gray-700 hover:border-gray-600 transition-all duration-300 group">
        <div class="p-6">
            <div class="flex items-center mb-4">
                <div class="w-10 h-10 bg-teal-500/20 rounded-lg flex items-center justify-center mr-3">
                    <svg class="w-6 h-6 text-teal-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-white">Product Templates</h3>
            </div>
            <p class="text-gray-400 mb-4">Manage product templates</p>
            <a href="{{ route('product-templates.index') }}" class="block w-full bg-gray-700 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition-colors duration-200 text-center">Manage Templates</a>
        </div>
    </div>
</div>

<!-- Manager Statistics -->
<div class="bg-gray-800 rounded-xl border border-gray-700 p-6 mb-8">
    <h2 class="text-xl font-semibold text-white mb-6 flex items-center">
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
        </svg>
        Management Overview
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
                    <h4 class="text-sm font-medium text-gray-400">Total Roles</h4>
                    <p class="text-2xl font-bold text-white">{{ \Spatie\Permission\Models\Role::count() }}</p>
                </div>
                <div class="w-8 h-8 bg-green-500/20 rounded-lg flex items-center justify-center">
                    <svg class="w-4 h-4 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                    </svg>
                </div>
            </div>
        </div>
        <div class="bg-gray-700 rounded-lg p-4 border border-gray-600">
            <div class="flex items-center justify-between">
                <div>
                    <h4 class="text-sm font-medium text-gray-400">Total Permissions</h4>
                    <p class="text-2xl font-bold text-white">{{ \Spatie\Permission\Models\Permission::count() }}</p>
                </div>
                <div class="w-8 h-8 bg-orange-500/20 rounded-lg flex items-center justify-center">
                    <svg class="w-4 h-4 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z"></path>
                    </svg>
                </div>
            </div>
        </div>
    </div>
</div>
