@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-slate-900 text-gray-100">
    <div class="container mx-auto px-4 py-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h1 class="text-3xl font-bold text-white mb-2">Connect TikTok Shop</h1>
                    <p class="text-gray-400">Manage TikTok Shop connections for team: <span class="text-blue-400 font-medium">{{ $team->name }}</span></p>
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

        @if($integrations->count() > 0)
            <!-- Integrations List -->
            <div class="bg-gray-800 rounded-xl border border-gray-700 p-6 mb-8">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-lg font-semibold text-white">TikTok Shop integrations</h3>
                    <span class="text-sm text-gray-400">{{ $integrations->count() }} integrations</span>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($integrations as $integration)
                        <div class="bg-gray-700 rounded-lg p-4 border border-gray-600">
                            <div class="flex justify-between items-start mb-3">
                                <div>
                                    <h4 class="font-semibold text-white">
                                        {{ $integration->name ?: 'Integration #' . $integration->id }}
                                    </h4>
                                    @if($integration->name)
                                        <p class="text-xs text-gray-400">ID: {{ $integration->id }}</p>
                                    @endif
                                </div>
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium border {{ $integration->status_badge_class }}">
                                    {{ $integration->status_text }}
                                </span>
                            </div>
                            
                            @if($integration->description)
                                <div class="mb-3">
                                    <p class="text-sm text-gray-300 line-clamp-2">{{ $integration->description }}</p>
                                </div>
                            @endif
                            
                            <div class="space-y-2 text-sm mb-4">
                                <div>
                                    <span class="text-gray-400">Market:</span>
                                    <span class="text-sm text-yellow-400">
                                        {{ $integration->market === 'US' ? '🇺🇸 US' : ($integration->market === 'UK' ? '🇬🇧 UK' : 'N/A') }}
                                    </span>
                                </div>
                                <div>
                                    <span class="text-gray-400">Access Token:</span>
                                    <span class="text-sm {{ $integration->isAccessTokenExpired() ? 'text-red-400' : 'text-green-400' }}">
                                        {{ $integration->isAccessTokenExpired() ? 'Expired' : 'Active' }}
                                    </span>
                                </div>
                                <div>
                                    <span class="text-gray-400">Shops:</span>
                                    <span class="text-sm text-blue-400">{{ $integration->shops->count() }}</span>
                                </div>
                                <div>
                                    <span class="text-gray-400">Updated:</span>
                                    <span class="text-sm text-gray-300">{{ $integration->updated_at->format('d/m/Y H:i') }}</span>
                                </div>
                            </div>

                            <!-- Integration Actions -->
                            <div class="flex flex-wrap gap-2">
                                @if($integration->status === 'pending')
                                    <a href="{{ route('team.tiktok-shop.connect', ['integration_id' => $integration->id]) }}" class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded text-xs font-medium transition-colors duration-200">
                                        Connect
                                    </a>
                                    <a href="{{ route('team.tiktok-shop.manual-auth', ['integration_id' => $integration->id]) }}" class="bg-green-600 hover:bg-green-700 text-white px-3 py-1 rounded text-xs font-medium transition-colors duration-200">
                                        Enter Code
                                    </a>
                                @endif

                                @if($integration->status === 'active')
                                    <form action="{{ route('team.tiktok-shop.disconnect', ['integration_id' => $integration->id]) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to disconnect?')">
                                        @csrf
                                        <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded text-xs font-medium transition-colors duration-200">
                                            Disconnect
                                        </button>
                                    </form>
                                @endif

                                <!-- Edit and Delete buttons -->
                                <a href="{{ route('team.tiktok-shop.edit-integration', ['integration_id' => $integration->id]) }}" class="bg-yellow-600 hover:bg-yellow-700 text-white px-3 py-1 rounded text-xs font-medium transition-colors duration-200">
                                    Edit
                                </a>
                                
                                <button onclick="confirmDeleteIntegration({{ $integration->id }})" class="bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded text-xs font-medium transition-colors duration-200">
                                    Delete
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Add New Integration -->
        <div class="bg-gray-800 rounded-xl border border-gray-700 p-6 mb-8">
            <h3 class="text-lg font-semibold text-white mb-4">Add new integration</h3>
            <p class="text-gray-400 mb-4">Create a new TikTok Shop integration to manage more shops</p>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <a href="{{ route('team.tiktok-shop.create-integration') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-medium transition-colors duration-200 flex items-center justify-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    Create new integration
                </a>
            </div>
        </div>

        <!-- Shops Management -->
        @if($shops->count() > 0)
            <div class="bg-gray-800 rounded-xl border border-gray-700 p-6 mb-8">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-lg font-semibold text-white">Manage Shops & Assign Sellers</h3>
                    <span class="text-sm text-gray-400">{{ $shops->count() }} shop</span>
                </div>
                
                <div class="space-y-6">
                    @foreach($shops as $shop)
                        <div class="bg-gray-700 rounded-lg p-6 border border-gray-600">
                            <div class="flex justify-between items-start mb-4">
                                <div>
                                    <h4 class="text-lg font-semibold text-white mb-2">{{ $shop->shop_name }}</h4>
                                    <div class="flex items-center space-x-4 text-sm text-gray-400">
                                        <span>ID: {{ $shop->shop_id }}</span>
                                        <span>Region: {{ $shop->seller_region }}</span>
                                        @if($shop->integration)
                                            <span class="text-yellow-400">
                                                Market: {{ $shop->integration->market === 'US' ? '🇺🇸 US' : ($shop->integration->market === 'UK' ? '🇬🇧 UK' : $shop->integration->market) }}
                                            </span>
                                        @endif
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium border {{ $shop->status_badge_class }}">
                                            {{ $shop->status_text }}
                                        </span>
                                    </div>
                                </div>
                                <button onclick="toggleAssignForm({{ $shop->id }})" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium transition-colors duration-200">
                                    <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                    </svg>
                                    Assign
                                </button>
                            </div>

                            <!-- Assign Seller Form (Hidden by default) -->
                            <div id="assignForm{{ $shop->id }}" class="hidden mb-4 p-4 bg-gray-600 rounded-lg">
                                @if($shop->integration)
                                    @php
                                        $shopMarket = $shop->integration->market;
                                        $availableUsers = $teamMembers->filter(function($member) use ($shopMarket) {
                                            return $member->hasTikTokMarket($shopMarket);
                                        });
                                    @endphp
                                    
                                    @if($availableUsers->count() > 0)
                                    <div class="mb-3 p-3 bg-blue-500/10 border border-blue-500/20 rounded-lg">
                                        <p class="text-sm text-blue-300">
                                            <i class="fas fa-info-circle mr-1"></i>
                                            Showing only sellers with access to market <strong>{{ $shopMarket }}</strong>
                                        </p>
                                    </div>
                                    @else
                                        <div class="mb-3 p-3 bg-yellow-500/10 border border-yellow-500/20 rounded-lg">
                                            <p class="text-sm text-yellow-300">
                                                <i class="fas fa-exclamation-triangle mr-1"></i>
                                                No seller has access to market <strong>{{ $shopMarket }}</strong>. Please grant market access first.
                                            </p>
                                        </div>
                                    @endif
                                @endif
                                
                                <form action="{{ route('team.tiktok-shop.assign-seller', $shop) }}" method="POST">
                                    @csrf
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-300 mb-2">Select seller</label>
                                            <select name="user_id" class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" {{ !isset($availableUsers) || $availableUsers->count() == 0 ? 'disabled' : '' }}>
                                                <option value="">Choose seller...</option>
                                                @if($shop->integration)
                                                    @foreach($availableUsers as $member)
                                                        <option value="{{ $member->id }}">
                                                            {{ $member->name }} ({{ $member->email }})
                                                            @if($member->getTikTokMarkets())
                                                                - Markets: {{ implode(', ', $member->getTikTokMarkets()) }}
                                                            @endif
                                                        </option>
                                                    @endforeach
                                                @else
                                                    @foreach($teamMembers as $member)
                                                        <option value="{{ $member->id }}">{{ $member->name }} ({{ $member->email }})</option>
                                                    @endforeach
                                                @endif
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-300 mb-2">Role</label>
                                            <select name="role" class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                                <option value="viewer">Viewer</option>
                                                <option value="manager">Manager</option>
                                                <option value="owner">Owner</option>
                                            </select>
                                        </div>
                                        <div class="flex items-end">
                                            <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-medium transition-colors duration-200">
                                                Assign
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>

                            <!-- Current Sellers -->
                            <div>
                                <h5 class="text-md font-medium text-white mb-3">Assigned sellers:</h5>
                                @if($shop->activeSellers->count() > 0)
                                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                                        @foreach($shop->activeSellers as $seller)
                                            <div class="bg-gray-600 rounded-lg p-3 flex justify-between items-center">
                                                <div>
                                                    <p class="text-sm font-medium text-white">{{ $seller->user->name }}</p>
                                                    <p class="text-xs text-gray-400">{{ $seller->user->email }}</p>
                                                </div>
                                                <div class="flex items-center space-x-2">
                                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium border {{ $seller->role_badge_class }}">
                                                        {{ $seller->role_text }}
                                                    </span>
                                                    <form action="{{ route('team.tiktok-shop.remove-seller', ['shop' => $shop, 'seller' => $seller]) }}" method="POST" class="inline">
                                                        @csrf
                                                        @method('PUT')
                                                        <button type="submit" onclick="return confirm('Are you sure you want to remove this seller permission?')" class="text-red-400 hover:text-red-300">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                            </svg>
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="text-center py-4">
                                        <p class="text-gray-400">No seller assigned to this shop yet.</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @else
            <!-- No Shops Message -->
            <div class="bg-gray-800 rounded-xl border border-gray-700 p-6 mb-8">
                <div class="text-center py-8">
                    <div class="w-16 h-16 bg-gray-600 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-white mb-2">No shops yet</h3>
                    <p class="text-gray-400">Connect a TikTok Shop to start managing.</p>
                </div>
            </div>
        @endif

        <!-- Role Guide -->
        <div class="bg-gray-800 rounded-xl border border-gray-700 p-6 mb-8">
            <h3 class="text-lg font-semibold text-white mb-4">Role guide</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-gray-700 rounded-lg p-4">
                    <div class="flex items-center mb-3">
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium border bg-purple-500/20 text-purple-400 border-purple-500/50 mr-2">
                            Owner
                        </span>
                        <h4 class="font-medium text-white">Owner</h4>
                    </div>
                    <ul class="text-sm text-gray-300 space-y-1">
                        <li>• Full control of shop</li>
                        <li>• Can assign roles to other sellers</li>
                        <li>• View all data and reports</li>
                        <li>• Manage products and orders</li>
                    </ul>
                </div>

                <div class="bg-gray-700 rounded-lg p-4">
                    <div class="flex items-center mb-3">
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium border bg-blue-500/20 text-blue-400 border-blue-500/50 mr-2">
                            Manager
                        </span>
                        <h4 class="font-medium text-white">Manager</h4>
                    </div>
                    <ul class="text-sm text-gray-300 space-y-1">
                        <li>• Manage products and orders</li>
                        <li>• View reports and analytics</li>
                        <li>• Cannot assign roles to others</li>
                        <li>• Cannot change shop settings</li>
                    </ul>
                </div>

                <div class="bg-gray-700 rounded-lg p-4">
                    <div class="flex items-center mb-3">
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium border bg-gray-500/20 text-gray-400 border-gray-500/50 mr-2">
                            Viewer
                        </span>
                        <h4 class="font-medium text-white">Viewer</h4>
                    </div>
                    <ul class="text-sm text-gray-300 space-y-1">
                        <li>• View-only shop info</li>
                        <li>• View products and orders</li>
                        <li>• Cannot edit</li>
                        <li>• Cannot manage</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- OAuth Flow Guide -->
        <div class="bg-gray-800 rounded-xl border border-gray-700 p-6 mb-8">
            <h3 class="text-lg font-semibold text-white mb-4">TikTok Shop connection flow</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <h4 class="text-md font-semibold text-blue-400 mb-3">Method 1: Auto connect</h4>
                    <ol class="list-decimal list-inside space-y-2 text-gray-300 text-sm">
                        <li>Click "Auto connect"</li>
                        <li>System redirects to TikTok authorization page</li>
                        <li>Log in with your TikTok Shop account</li>
                        <li>Review and approve requested permissions</li>
                        <li>TikTok redirects back and completes the connection</li>
                    </ol>
                </div>
                
                <div>
                    <h4 class="text-md font-semibold text-green-400 mb-3">Method 2: Manual code</h4>
                    <ol class="list-decimal list-inside space-y-2 text-gray-300 text-sm">
                        <li>Click "Enter Code"</li>
                        <li>Copy OAuth link and send to seller</li>
                        <li>Seller clicks link and logs into TikTok Shop</li>
                        <li>Seller approves permissions and copies authorization code</li>
                        <li>Enter the code into the form and click "Connect"</li>
                    </ol>
                </div>
            </div>

            <div class="mt-6 p-4 bg-yellow-500/10 border border-yellow-500/20 rounded-lg">
                <h4 class="text-md font-semibold text-yellow-400 mb-2">Required scopes:</h4>
                <ul class="list-disc list-inside space-y-1 text-gray-300 text-sm">
                    <li><strong>read_products:</strong> Read product info</li>
                    <li><strong>write_products:</strong> Update product info</li>
                    <li><strong>read_orders:</strong> Read order info</li>
                    <li><strong>write_orders:</strong> Update order status</li>
                </ul>
            </div>
        </div>

        <!-- Benefits -->
        <div class="bg-gray-800 rounded-xl border border-gray-700 p-6">
            <h3 class="text-lg font-semibold text-white mb-4">Benefits of connecting TikTok Shop</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="text-center">
                    <div class="w-16 h-16 bg-blue-500/20 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                    </div>
                    <h4 class="text-md font-semibold text-white mb-2">Order management</h4>
                    <p class="text-gray-400 text-sm">Auto-sync and manage orders from TikTok Shop</p>
                </div>
                
                <div class="text-center">
                    <div class="w-16 h-16 bg-green-500/20 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                        </svg>
                    </div>
                    <h4 class="text-md font-semibold text-white mb-2">Product management</h4>
                    <p class="text-gray-400 text-sm">Sync and update product info easily</p>
                </div>
                
                <div class="text-center">
                    <div class="w-16 h-16 bg-purple-500/20 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                        </svg>
                    </div>
                    <h4 class="text-md font-semibold text-white mb-2">Seller permissions</h4>
                    <p class="text-gray-400 text-sm">Assign shop management to team sellers</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-gray-800 rounded-xl p-6 max-w-md w-full mx-4">
        <h3 class="text-lg font-semibold text-white mb-4">Xác nhận xóa tích hợp</h3>
        <p class="text-gray-300 mb-6">Bạn có chắc chắn muốn xóa tích hợp này? Hành động này không thể hoàn tác và sẽ xóa tất cả dữ liệu liên quan.</p>
        
        <div class="flex justify-end space-x-3">
            <button onclick="closeDeleteModal()" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg font-medium transition-colors duration-200">
                Hủy
            </button>
            <form id="deleteForm" method="POST" class="inline">
                @csrf
                @method('DELETE')
                <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg font-medium transition-colors duration-200">
                    Xóa
                </button>
            </form>
        </div>
    </div>
</div>

<script>
function toggleAssignForm(shopId) {
    const form = document.getElementById('assignForm' + shopId);
    if (form.classList.contains('hidden')) {
        form.classList.remove('hidden');
    } else {
        form.classList.add('hidden');
    }
}

function confirmDeleteIntegration(integrationId) {
    const modal = document.getElementById('deleteModal');
    const form = document.getElementById('deleteForm');
    form.action = '{{ route("team.tiktok-shop.delete-integration", ":id") }}'.replace(':id', integrationId);
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function closeDeleteModal() {
    const modal = document.getElementById('deleteModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}
</script>
@endsection
