@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-slate-900 text-gray-100">
    <div class="container mx-auto px-4 py-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h1 class="text-3xl font-bold text-white mb-2">Manage TikTok Shop integrations</h1>
                    <p class="text-gray-400">Manage all TikTok Shop integrations for teams</p>
                </div>
                <a href="{{ route('tiktok-shop.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-medium transition-colors duration-200">
                    Create new integration
                </a>
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

        @if($integrations->count() > 0)
            <!-- Filter and Search Section -->
            <div class="bg-gray-800 rounded-xl border border-gray-700 p-6 mb-6">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <!-- Search -->
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-300 mb-2">
                            <i class="fas fa-search mr-2"></i>Search
                        </label>
                        <input 
                            type="text" 
                            id="search-input" 
                            placeholder="Search by team name, shop name, shop ID..." 
                            class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-2 text-white placeholder-gray-400 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                        >
                    </div>

                    <!-- Market Filter -->
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">
                            <i class="fas fa-globe mr-2"></i>Market
                        </label>
                        <select 
                            id="market-filter" 
                            class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-2 text-white focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                        >
                            <option value="">All Markets</option>
                            <option value="UK">🇬🇧 UK</option>
                            <option value="US">🇺🇸 US</option>
                        </select>
                    </div>

                    <!-- Status Filter -->
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">
                            <i class="fas fa-filter mr-2"></i>Status
                        </label>
                        <select 
                            id="status-filter" 
                            class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-2 text-white focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                        >
                            <option value="">All Status</option>
                            <option value="active">Active</option>
                            <option value="pending">Pending</option>
                            <option value="error">Error</option>
                            <option value="expired">Expired</option>
                        </select>
                    </div>
                </div>

                <!-- Results Count -->
                <div class="mt-4 flex items-center justify-between">
                    <div class="text-sm text-gray-400">
                        Showing <span id="results-count" class="font-semibold text-white">{{ $integrations->count() }}</span> of {{ $integrations->count() }} integrations
                    </div>
                    <button 
                        id="clear-filters" 
                        class="text-sm text-blue-400 hover:text-blue-300 transition-colors"
                        style="display: none;"
                    >
                        <i class="fas fa-times mr-1"></i>Clear filters
                    </button>
                </div>
            </div>

            <!-- Integrations List -->
            <div class="bg-gray-800 rounded-xl border border-gray-700 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-700">
                    <h3 class="text-lg font-semibold text-white">List of TikTok Shop integrations</h3>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-700">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Team</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Market</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Shop</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Token</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Created at</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-700" id="integrations-table-body">
                            @foreach($integrations as $integration)
                                <tr 
                                    class="hover:bg-gray-700/50 integration-row"
                                    data-team-name="{{ strtolower($integration->team->name) }}"
                                    data-market="{{ $integration->market ?? '' }}"
                                    data-status="{{ $integration->status }}"
                                    data-shop-name="{{ strtolower($integration->activeShops->pluck('shop_name')->join(' ')) }}"
                                    data-shop-id="{{ strtolower($integration->activeShops->pluck('shop_id')->join(' ')) }}"
                                >
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div>
                                            <div class="text-sm font-medium text-white">{{ $integration->team->name }}</div>
                                            <div class="text-sm text-gray-400">ID: {{ $integration->team->id }}</div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @php
                                            $market = $integration->market ?? 'N/A';
                                            $categoryVersion = $integration->getCategoryVersion() ?? '';
                                            $marketColors = [
                                                'UK' => 'bg-blue-500/20 text-blue-400 border-blue-500/50',
                                                'US' => 'bg-red-500/20 text-red-400 border-red-500/50',
                                                'default' => 'bg-gray-500/20 text-gray-400 border-gray-500/50'
                                            ];
                                            $marketFlags = [
                                                'UK' => '🇬🇧',
                                                'US' => '🇺🇸',
                                            ];
                                        @endphp
                                        <div class="flex flex-col items-start gap-1">
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium border {{ $marketColors[$market] ?? $marketColors['default'] }}">
                                                {{ $marketFlags[$market] ?? '🌐' }} {{ $market }}
                                            </span>
                                            @if($categoryVersion)
                                            <span class="text-xs text-gray-500">{{ $categoryVersion }}</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border {{ $integration->status_badge_class }}">
                                            {{ $integration->status_text }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div>
                                            @if($integration->activeShops->count() > 0)
                                                @foreach($integration->activeShops as $shop)
                                                    <div class="text-sm text-white">{{ $shop->shop_name }}</div>
                                                    <div class="text-sm text-gray-400">{{ $shop->shop_id }}</div>
                                                @endforeach
                                            @else
                                                <div class="text-sm text-white">Not connected</div>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm">
                                            <div class="flex items-center">
                                                <span class="text-gray-400">Access:</span>
                                                <span class="ml-2 {{ $integration->isAccessTokenExpired() ? 'text-red-400' : 'text-green-400' }}">
                                                    {{ $integration->isAccessTokenExpired() ? 'Expired' : 'Valid' }}
                                                </span>
                                            </div>
                                            @if($integration->access_token_remaining_days > 0)
                                                <div class="text-xs text-yellow-400 mt-1">
                                                    Remaining {{ $integration->access_token_remaining_days }} days
                                                </div>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300">
                                        {{ $integration->created_at->format('d/m/Y H:i') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <div class="flex items-center space-x-2">
                                            <a href="{{ route('tiktok-shop.edit', $integration) }}" class="text-blue-400 hover:text-blue-300" title="Edit">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                </svg>
                                            </a>
                                                                                         <a href="{{ route('tiktok-shop.debug', $integration) }}" class="text-purple-400 hover:text-purple-300" title="Debug OAuth">
                                                 <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                     <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                                                 </svg>
                                             </a>
                                             <form action="{{ route('tiktok-shop.test-credentials', $integration) }}" method="POST" class="inline">
                                                 @csrf
                                                 <button type="submit" class="text-orange-400 hover:text-orange-300" title="Test Credentials">
                                                     <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                         <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                                                     </svg>
                                                 </button>
                                             </form>
                                            @if($integration->status === 'active')
                                                <form action="{{ route('tiktok-shop.test-connection', $integration) }}" method="POST" class="inline">
                                                    @csrf
                                                    <button type="submit" class="text-green-400 hover:text-green-300">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                        </svg>
                                                    </button>
                                                </form>
                                            @endif
                                                <form action="{{ route('tiktok-shop.destroy', $integration) }}" method="POST" class="inline" onsubmit="return confirm('Are
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-400 hover:text-red-300">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                    </svg>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Statistics -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mt-8">
                <div class="bg-gray-800 rounded-xl border border-gray-700 p-6">
                    <div class="flex items-center">
                        <div class="w-12 h-12 bg-blue-500/20 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-400">Total integrations</p>
                            <p class="text-2xl font-bold text-white">{{ $integrations->count() }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-gray-800 rounded-xl border border-gray-700 p-6">
                    <div class="flex items-center">
                        <div class="w-12 h-12 bg-green-500/20 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-400">Active</p>
                            <p class="text-2xl font-bold text-white">{{ $integrations->where('status', 'active')->count() }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-gray-800 rounded-xl border border-gray-700 p-6">
                    <div class="flex items-center">
                        <div class="w-12 h-12 bg-yellow-500/20 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-400">Waiting for verification</p>
                            <p class="text-2xl font-bold text-white">{{ $integrations->where('status', 'pending')->count() }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-gray-800 rounded-xl border border-gray-700 p-6">
                    <div class="flex items-center">
                        <div class="w-12 h-12 bg-red-500/20 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-400">Error</p>
                            <p class="text-2xl font-bold text-white">{{ $integrations->whereIn('status', ['error', 'expired'])->count() }}</p>
                        </div>
                    </div>
                </div>
            </div>

        @else
            <!-- No Integrations -->
            <div class="bg-gray-800 rounded-xl border border-gray-700 p-8 text-center">
                <div class="w-24 h-24 bg-blue-500/20 rounded-full flex items-center justify-center mx-auto mb-6">
                    <svg class="w-12 h-12 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-white mb-2">No TikTok Shop integrations found</h3>
                <p class="text-gray-400 mb-6">Start creating TikTok Shop integrations for teams.</p>
                <a href="{{ route('tiktok-shop.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-medium transition-colors duration-200">
                    Create first integration
                </a>
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('search-input');
    const marketFilter = document.getElementById('market-filter');
    const statusFilter = document.getElementById('status-filter');
    const clearFiltersBtn = document.getElementById('clear-filters');
    const resultsCount = document.getElementById('results-count');
    const rows = document.querySelectorAll('.integration-row');
    const totalCount = rows.length;

    function filterTable() {
        const searchTerm = searchInput.value.toLowerCase().trim();
        const selectedMarket = marketFilter.value.toLowerCase();
        const selectedStatus = statusFilter.value.toLowerCase();

        let visibleCount = 0;

        rows.forEach(row => {
            const teamName = row.getAttribute('data-team-name') || '';
            const market = (row.getAttribute('data-market') || '').toLowerCase();
            const status = (row.getAttribute('data-status') || '').toLowerCase();
            const shopName = row.getAttribute('data-shop-name') || '';
            const shopId = row.getAttribute('data-shop-id') || '';

            // Search filter
            const matchesSearch = !searchTerm || 
                teamName.includes(searchTerm) || 
                shopName.includes(searchTerm) || 
                shopId.includes(searchTerm);

            // Market filter
            const matchesMarket = !selectedMarket || market === selectedMarket;

            // Status filter
            const matchesStatus = !selectedStatus || status === selectedStatus;

            // Show/hide row
            if (matchesSearch && matchesMarket && matchesStatus) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });

        // Update results count
        resultsCount.textContent = visibleCount;

        // Show/hide clear filters button
        if (searchTerm || selectedMarket || selectedStatus) {
            clearFiltersBtn.style.display = 'block';
        } else {
            clearFiltersBtn.style.display = 'none';
        }
    }

    // Event listeners
    searchInput.addEventListener('input', filterTable);
    marketFilter.addEventListener('change', filterTable);
    statusFilter.addEventListener('change', filterTable);

    // Clear filters
    clearFiltersBtn.addEventListener('click', function() {
        searchInput.value = '';
        marketFilter.value = '';
        statusFilter.value = '';
        filterTable();
    });

    // Preserve filter values from URL params (if needed)
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('market')) {
        marketFilter.value = urlParams.get('market');
    }
    if (urlParams.get('status')) {
        statusFilter.value = urlParams.get('status');
    }
    if (urlParams.get('search')) {
        searchInput.value = urlParams.get('search');
    }
    
    // Apply filters on page load if URL params exist
    if (urlParams.get('market') || urlParams.get('status') || urlParams.get('search')) {
        filterTable();
    }
});
</script>
@endpush
@endsection
