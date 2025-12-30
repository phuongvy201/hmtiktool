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

        <!-- Order Statistics -->
        @if(isset($shopStats) && !empty($shopStats['shops']))
        <div class="mb-8">
            <div class="bg-gray-800 rounded-xl border border-gray-700 p-6">
                <h2 class="text-2xl font-bold text-white mb-6 flex items-center">
                    <i class="fas fa-shopping-bag mr-3 text-blue-400"></i>
                    Order Statistics
                </h2>
                
                <!-- Filters -->
                <div class="mb-6 grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">Start Date</label>
                        <input type="date" id="start-date" value="{{ request('start_date') }}" class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-2 text-white focus:outline-none focus:border-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">End Date</label>
                        <input type="date" id="end-date" value="{{ request('end_date') }}" class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-2 text-white focus:outline-none focus:border-blue-500">
                    </div>
                    <div class="flex items-end">
                        <button id="filter-btn" class="w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors duration-200">
                            <i class="fas fa-filter mr-2"></i>Filter
                        </button>
                    </div>
                </div>

                <!-- Loading Indicator -->
                <div id="loading-indicator" class="hidden mb-4 text-center">
                    <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-400"></div>
                    <p class="text-gray-400 mt-2">Loading...</p>
                </div>

                <!-- Statistics Table -->
                <div class="overflow-x-auto" id="stats-table-container">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="border-b border-gray-700">
                                <th class="pb-3 text-sm font-semibold text-gray-300">Name</th>
                                <th class="pb-3 text-sm font-semibold text-gray-300">Profile</th>
                                <th class="pb-3 text-sm font-semibold text-gray-300 text-right">Total Orders</th>
                                <th class="pb-3 text-sm font-semibold text-gray-300 text-right">Success Orders</th>
                                <th class="pb-3 text-sm font-semibold text-gray-300 text-right">Cancel Orders</th>
                                <th class="pb-3 text-sm font-semibold text-gray-300 text-center">Staffs</th>
                            </tr>
                        </thead>
                        <tbody id="stats-table-body">
                            @foreach($shopStats['shops'] as $stat)
                            <tr class="border-b border-gray-700 hover:bg-gray-700/50 transition-colors">
                                <td class="py-3">
                                    <a href="{{ route('tiktok.orders.index', ['shop_id' => $stat['shop_id'] ?? $stat['shop']->id]) }}" class="text-blue-400 hover:text-blue-300 underline">
                                        {{ $stat['shop_name'] }}
                                    </a>
                                </td>
                                <td class="py-3 text-gray-300">{{ $stat['profile'] }}</td>
                                <td class="py-3 text-right text-white font-semibold">{{ number_format($stat['total_orders'] ?? 0) }}</td>
                                <td class="py-3 text-right text-white font-semibold">{{ number_format($stat['success_orders']) }}</td>
                                <td class="py-3 text-right text-white font-semibold">{{ number_format($stat['cancel_orders']) }}</td>
                                <td class="py-3">
                                    @if(isset($stat['staffs_names']) && count($stat['staffs_names']) > 0)
                                        <div class="flex flex-wrap gap-1 justify-center">
                                            @foreach($stat['staffs_names'] as $staffName)
                                                <span class="bg-pink-500/20 text-pink-300 px-2 py-1 rounded text-xs border border-pink-500/30">
                                                    {{ $staffName }}
                                                </span>
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="text-gray-500 text-sm">-</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                            <!-- Total Row -->
                            <tr class="border-t-2 border-gray-600 bg-gray-700/30">
                                <td class="py-3 font-bold text-white">Total</td>
                                <td class="py-3"></td>
                                <td class="py-3 text-right font-bold text-white">{{ number_format($shopStats['total']['total_orders'] ?? 0) }}</td>
                                <td class="py-3 text-right font-bold text-white">{{ number_format($shopStats['total']['success_orders']) }}</td>
                                <td class="py-3 text-right font-bold text-white">{{ number_format($shopStats['total']['cancel_orders']) }}</td>
                                <td class="py-3"></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif

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
        @elseif(auth()->user()->hasRole('seller'))
            @include('dashboard.partials.seller')
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
                    <p class="text-gray-400 mb-4">Manage your personal information</p>
                    
                    <!-- TikTok Shop Integrations -->
                    @if(isset($integrations) && $integrations->count() > 0)
                    <div class="mb-4">
                        <h4 class="text-sm font-medium text-gray-300 mb-2 flex items-center">
                            <i class="fab fa-tiktok mr-2 text-pink-400"></i>
                            TikTok Shop Integrations:
                        </h4>
                        <div class="space-y-2">
                            @foreach($integrations as $integration)
                            <div class="bg-gray-700/50 rounded-lg p-3 border border-gray-600/50">
                                <div class="flex items-center justify-between">
                                    <div class="flex-1">
                                        <p class="text-white font-medium">{{ $integration->name ?? 'Unnamed' }}</p>
                                        <div class="flex items-center space-x-3 mt-1 text-xs text-gray-400">
                                            <span class="flex items-center">
                                                <span class="w-2 h-2 rounded-full mr-1 {{ $integration->status === 'active' ? 'bg-green-400' : ($integration->status === 'pending' ? 'bg-yellow-400' : 'bg-gray-400') }}"></span>
                                                {{ $integration->status_text }}
                                            </span>
                                            @if($integration->market)
                                            <span class="flex items-center">
                                                <i class="fas fa-globe mr-1"></i>
                                                {{ $integration->market }}
                                            </span>
                                            @endif
                                        </div>
                                    </div>
                                    <span class="text-xs px-2 py-1 rounded {{ $integration->status_badge_class }}">
                                        {{ $integration->status_text }}
                                    </span>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @else
                    <div class="mb-4 bg-blue-500/10 border border-blue-500/20 rounded-lg p-3">
                        <p class="text-sm text-gray-300">No TikTok Shop integration yet</p>
                    </div>
                    @endif

                    <a href="{{ route('profile.edit') }}" class="block w-full bg-gray-700 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition-colors duration-200 text-center">Edit Profile</a>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const filterBtn = document.getElementById('filter-btn');
    const startDateInput = document.getElementById('start-date');
    const endDateInput = document.getElementById('end-date');
    const loadingIndicator = document.getElementById('loading-indicator');
    const statsTableBody = document.getElementById('stats-table-body');

    if (filterBtn) {
        filterBtn.addEventListener('click', function() {
            const startDate = startDateInput.value;
            const endDate = endDateInput.value;

            // Show loading
            loadingIndicator.classList.remove('hidden');
            statsTableBody.innerHTML = '';

            // Build URL with query parameters
            const url = new URL(window.location.href);
            if (startDate) {
                url.searchParams.set('start_date', startDate);
            } else {
                url.searchParams.delete('start_date');
            }
            if (endDate) {
                url.searchParams.set('end_date', endDate);
            } else {
                url.searchParams.delete('end_date');
            }

            // Make AJAX request
            fetch(url.toString(), {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                }
            })
            .then(response => response.json())
            .then(data => {
                // Hide loading
                loadingIndicator.classList.add('hidden');

                // Update table
                if (data.shops && data.shops.length > 0) {
                    let html = '';
                    data.shops.forEach(stat => {
                        html += `
                            <tr class="border-b border-gray-700 hover:bg-gray-700/50 transition-colors">
                                <td class="py-3">
                                    <a href="/tiktok/orders?shop_id=${stat.shop_id}" class="text-blue-400 hover:text-blue-300 underline">
                                        ${stat.shop_name}
                                    </a>
                                </td>
                                <td class="py-3 text-gray-300">${stat.profile}</td>
                                <td class="py-3 text-right text-white font-semibold">${parseInt(stat.total_orders || 0).toLocaleString()}</td>
                                <td class="py-3 text-right text-white font-semibold">${parseInt(stat.success_orders).toLocaleString()}</td>
                                <td class="py-3 text-right text-white font-semibold">${parseInt(stat.cancel_orders).toLocaleString()}</td>
                                <td class="py-3">
                                    ${stat.staffs_names && stat.staffs_names.length > 0 
                                        ? '<div class="flex flex-wrap gap-1 justify-center">' + 
                                          stat.staffs_names.map(name => 
                                              `<span class="bg-pink-500/20 text-pink-300 px-2 py-1 rounded text-xs border border-pink-500/30">${name}</span>`
                                          ).join('') + 
                                          '</div>'
                                        : '<span class="text-gray-500 text-sm">-</span>'
                                    }
                                </td>
                            </tr>
                        `;
                    });

                    // Add total row
                    html += `
                        <tr class="border-t-2 border-gray-600 bg-gray-700/30">
                            <td class="py-3 font-bold text-white">Total</td>
                            <td class="py-3"></td>
                            <td class="py-3 text-right font-bold text-white">${parseInt(data.total.total_orders || 0).toLocaleString()}</td>
                            <td class="py-3 text-right font-bold text-white">${parseInt(data.total.success_orders).toLocaleString()}</td>
                            <td class="py-3 text-right font-bold text-white">${parseInt(data.total.cancel_orders).toLocaleString()}</td>
                            <td class="py-3"></td>
                        </tr>
                    `;

                    statsTableBody.innerHTML = html;
                } else {
                    statsTableBody.innerHTML = '<tr><td colspan="6" class="py-4 text-center text-gray-400">No data found</td></tr>';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                loadingIndicator.classList.add('hidden');
                statsTableBody.innerHTML = '<tr><td colspan="6" class="py-4 text-center text-red-400">Error loading data</td></tr>';
            });
        });
    }
});
</script>
@endpush
@endsection