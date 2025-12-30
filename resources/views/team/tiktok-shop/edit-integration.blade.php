@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-slate-900 text-gray-100">
    <div class="container mx-auto px-4 py-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h1 class="text-3xl font-bold text-white mb-2">Edit TikTok Shop Integration</h1>
                    <p class="text-gray-400">Edit integration details for team: <span class="text-blue-400 font-medium">{{ $team->name }}</span></p>
                </div>
                <a href="{{ route('team.tiktok-shop.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg font-medium transition-colors duration-200">
                    Back
                </a>
            </div>
        </div>

        <!-- Success/Error Messages -->
        @if(session('success'))
            <div class="bg-green-500/20 border border-green-500/50 text-green-400 px-4 py-3 rounded-lg mb-6">
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="bg-red-500/20 border border-red-500/50 text-red-400 px-4 py-3 rounded-lg mb-6">
                <ul class="list-disc list-inside">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Edit Integration Form -->
        <div class="bg-gray-800 rounded-xl border border-gray-700 p-6 mb-8">
            <h3 class="text-lg font-semibold text-white mb-4">Integration information</h3>
            
            <form action="{{ route('team.tiktok-shop.update-integration', $integration->id) }}" method="POST">
                @csrf
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-300 mb-2">Integration name</label>
                        <input type="text" 
                               id="name" 
                               name="name" 
                               value="{{ old('name', $integration->name) }}"
                               class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                               placeholder="Enter integration name (optional)">
                        <p class="text-xs text-gray-400 mt-1">Name to distinguish integrations</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">Status</label>
                        <div class="flex items-center space-x-2">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium border {{ $integration->status_badge_class }}">
                                {{ $integration->status_text }}
                            </span>
                            @if($integration->access_token)
                                <span class="text-xs text-gray-400">
                                    Token: {{ $integration->isAccessTokenExpired() ? 'Expired' : 'Active' }}
                                </span>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="mt-6">
                    <label for="description" class="block text-sm font-medium text-gray-300 mb-2">Description</label>
                    <textarea id="description" 
                              name="description" 
                              rows="4"
                              class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                              placeholder="Enter integration description (optional)">{{ old('description', $integration->description) }}</textarea>
                    <p class="text-xs text-gray-400 mt-1">Describe the purpose of this integration</p>
                </div>

                <div class="mt-6">
                    <h4 class="text-md font-semibold text-white mb-3">Connection info</h4>
                    <div class="bg-gray-700 rounded-lg p-4 space-y-3">
                        <div class="flex justify-between items-center">
                            <span class="text-gray-400">App Key:</span>
                            <span class="text-white font-mono text-sm">{{ config('tiktok-shop.app_key') ?? 'Chưa cấu hình' }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-400">Access Token:</span>
                            <span class="text-sm {{ $integration->access_token ? 'text-green-400' : 'text-red-400' }}">
                                {{ $integration->access_token ? 'Connected' : 'Not connected' }}
                            </span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-400">Shops:</span>
                            <span class="text-blue-400">{{ $integration->shops->count() }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-400">Created at:</span>
                            <span class="text-gray-300">{{ $integration->created_at->format('d/m/Y H:i') }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-400">Last updated:</span>
                            <span class="text-gray-300">{{ $integration->updated_at->format('d/m/Y H:i') }}</span>
                        </div>
                    </div>
                </div>

                <div class="flex justify-between items-center mt-8">
                    <div class="flex space-x-3">
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-medium transition-colors duration-200">
                            <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            Update
                        </button>
                        
                        <a href="{{ route('team.tiktok-shop.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-6 py-2 rounded-lg font-medium transition-colors duration-200">
                            Cancel
                        </a>
                    </div>

                    <div class="flex space-x-3">
                        @if($integration->status === 'pending')
                            <a href="{{ route('team.tiktok-shop.connect', ['integration_id' => $integration->id]) }}" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-medium transition-colors duration-200">
                                <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path>
                                </svg>
                                Connect
                            </a>
                        @endif

                        <button type="button" 
                                onclick="confirmDelete({{ $integration->id }})" 
                                class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg font-medium transition-colors duration-200">
                            <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                            Delete
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Shops List -->
        @if($integration->shops->count() > 0)
        <div class="bg-gray-800 rounded-xl border border-gray-700 p-6 mb-8">
            <h3 class="text-lg font-semibold text-white mb-4">Connected shops ({{ $integration->shops->count() }})</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($integration->shops as $shop)
                    <div class="bg-gray-700 rounded-lg p-4 border border-gray-600">
                        <div class="flex justify-between items-start mb-3">
                            <h4 class="font-semibold text-white">{{ $shop->shop_name }}</h4>
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium border {{ $shop->status_badge_class }}">
                                {{ $shop->status_text }}
                            </span>
                        </div>
                        
                        <div class="space-y-2 text-sm">
                            <div>
                                <span class="text-gray-400">ID:</span>
                                <span class="text-white">{{ $shop->shop_id }}</span>
                            </div>
                            <div>
                                <span class="text-gray-400">Region:</span>
                                <span class="text-blue-400">{{ $shop->seller_region }}</span>
                            </div>
                            <div>
                                <span class="text-gray-400">Sellers:</span>
                                <span class="text-green-400">{{ $shop->activeSellers->count() }}</span>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Integration Actions -->
        <div class="bg-gray-800 rounded-xl border border-gray-700 p-6">
            <h3 class="text-lg font-semibold text-white mb-4">Integration actions</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @if($integration->status === 'pending')
                    <div class="bg-blue-500/10 border border-blue-500/20 rounded-lg p-4">
                        <h4 class="text-md font-semibold text-blue-400 mb-2">Connect TikTok Shop</h4>
                        <p class="text-sm text-gray-300 mb-3">Connect this integration to TikTok Shop to start using.</p>
                        <a href="{{ route('team.tiktok-shop.connect', ['integration_id' => $integration->id]) }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors duration-200">
                            Connect now
                        </a>
                    </div>
                @elseif($integration->status === 'active')
                    <div class="bg-green-500/10 border border-green-500/20 rounded-lg p-4">
                        <h4 class="text-md font-semibold text-green-400 mb-2">Integration active</h4>
                        <p class="text-sm text-gray-300 mb-3">Integration is connected and running normally.</p>
                        <div class="flex space-x-2">
                            <a href="{{ route('team.tiktok-shop.index') }}" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors duration-200">
                                View details
                            </a>
                            <form action="{{ route('team.tiktok-shop.disconnect', ['integration_id' => $integration->id]) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" onclick="return confirm('Are you sure you want to disconnect?')" class="bg-red-600 hover-bg-red-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors duration-200">
                                    Disconnect
                                </button>
                            </form>
                        </div>
                    </div>
                @else
                    <div class="bg-red-500/10 border border-red-500/20 rounded-lg p-4">
                        <h4 class="text-md font-semibold text-red-400 mb-2">Integration error</h4>
                        <p class="text-sm text-gray-300 mb-3">Integration has issues and needs to be fixed.</p>
                        <a href="{{ route('team.tiktok-shop.connect', ['integration_id' => $integration->id]) }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors duration-200">
                            Reconnect
                        </a>
                    </div>
                @endif

                <div class="bg-yellow-500/10 border border-yellow-500/20 rounded-lg p-4">
                    <h4 class="text-md font-semibold text-yellow-400 mb-2">Delete integration</h4>
                    <p class="text-sm text-gray-300 mb-3">Permanently delete this integration and all related data.</p>
                    <button onclick="confirmDelete({{ $integration->id }})" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors duration-200">
                        Delete integration
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-gray-800 rounded-xl p-6 max-w-md w-full mx-4">
        <h3 class="text-lg font-semibold text-white mb-4">Confirm delete integration</h3>
        <p class="text-gray-300 mb-6">Are you sure you want to delete this integration? This action cannot be undone and will remove all related data.</p>
        
        <div class="flex justify-end space-x-3">
            <button onclick="closeDeleteModal()" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg font-medium transition-colors duration-200">
                Cancel
            </button>
            <form id="deleteForm" method="POST" class="inline">
                @csrf
                @method('DELETE')
                <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg font-medium transition-colors duration-200">
                    Delete
                </button>
            </form>
        </div>
    </div>
</div>

<script>
function confirmDelete(integrationId) {
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
