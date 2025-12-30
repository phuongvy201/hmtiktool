@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-slate-900 text-gray-100">
    <div class="container mx-auto px-4 py-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center justify-between mb-6">
                <div class="flex items-center">
                    <a href="{{ route('service-packages.index') }}" class="text-blue-400 hover:text-blue-300 mr-4">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                    </a>
                    <div>
                        <h1 class="text-3xl font-bold text-white mb-2">{{ $servicePackage->name }}</h1>
                        <p class="text-gray-400">Service package details</p>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    @can('edit-service-packages')
                    <a href="{{ route('service-packages.edit', $servicePackage) }}" 
                       class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors duration-200 flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                        Edit
                    </a>
                    @endcan
                </div>
            </div>
        </div>

        <!-- Success/Error Messages -->
        @if(session('success'))
        <div class="bg-green-600 border border-green-500 text-white px-4 py-3 rounded-lg mb-6">
            {{ session('success') }}
        </div>
        @endif

        @if(session('error'))
        <div class="bg-red-600 border border-red-500 text-white px-4 py-3 rounded-lg mb-6">
            {{ session('error') }}
        </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main Content -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Package Information -->
                <div class="bg-gray-800 rounded-xl border border-gray-700 p-6">
                    <h2 class="text-xl font-bold text-white mb-4">Service Package Information</h2>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-400 mb-1">Package name</label>
                            <p class="text-white text-lg font-semibold">{{ $servicePackage->name }}</p>
                        </div>

                        @if($servicePackage->description)
                        <div>
                            <label class="block text-sm font-medium text-gray-400 mb-1">Description</label>
                            <p class="text-gray-300">{{ $servicePackage->description }}</p>
                        </div>
                        @endif

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-400 mb-1">Price</label>
                                <p class="text-white text-xl font-bold">{{ $servicePackage->formatted_price }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-400 mb-1">Duration</label>
                                <p class="text-white">{{ $servicePackage->duration_days }} days ({{ $servicePackage->duration_months }} months)</p>
                            </div>
                        </div>

                        @if(isset($servicePackage->max_users) || isset($servicePackage->max_projects) || isset($servicePackage->max_storage_gb))
                        <div class="grid grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-400 mb-1">Maximum users</label>
                                <p class="text-white text-lg font-semibold">{{ isset($servicePackage->max_users) ? $servicePackage->max_users : 'Unlimited' }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-400 mb-1">Maximum projects</label>
                                <p class="text-white text-lg font-semibold">{{ isset($servicePackage->max_projects) ? $servicePackage->max_projects : 'Unlimited' }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-400 mb-1">Storage</label>
                                <p class="text-white text-lg font-semibold">{{ isset($servicePackage->max_storage_gb) ? $servicePackage->max_storage_gb . ' GB' : 'Unlimited' }}</p>
                            </div>
                        </div>
                        @endif

                        <div class="flex items-center gap-4 pt-4 border-t border-gray-700">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $servicePackage->is_active ? 'bg-green-600 text-green-100' : 'bg-red-600 text-red-100' }}">
                                {{ $servicePackage->is_active ? 'Active' : 'Inactive' }}
                            </span>
                            @if(isset($servicePackage->is_featured) && $servicePackage->is_featured)
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-yellow-600 text-yellow-100">
                                Featured
                            </span>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Features -->
                @php
                    $features = $servicePackage->features;
                    // Ensure features is an array
                    if (is_string($features)) {
                        $features = json_decode($features, true) ?? [];
                    }
                    if (!is_array($features)) {
                        $features = [];
                    }
                @endphp
                @if(!empty($features) && count($features) > 0)
                <div class="bg-gray-800 rounded-xl border border-gray-700 p-6">
                    <h2 class="text-xl font-bold text-white mb-4">Features</h2>
                    <ul class="space-y-2">
                        @foreach($features as $feature)
                        <li class="flex items-start">
                            <svg class="w-5 h-5 text-green-400 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span class="text-gray-300">{{ $feature }}</span>
                        </li>
                        @endforeach
                    </ul>
                </div>
                @endif

                <!-- Subscriptions -->
                <div class="bg-gray-800 rounded-xl border border-gray-700 p-6">
                    <h2 class="text-xl font-bold text-white mb-4">Subscriptions</h2>
                    
                    @if($servicePackage->subscriptions->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-700">
                            <thead class="bg-gray-700">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-300 uppercase">User</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-300 uppercase">Status</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-300 uppercase">Start date</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-300 uppercase">End date</th>
                                </tr>
                            </thead>
                            <tbody class="bg-gray-800 divide-y divide-gray-700">
                                @foreach($servicePackage->subscriptions as $subscription)
                                <tr class="hover:bg-gray-700">
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <div class="text-sm font-medium text-white">
                                            {{ $subscription->user->name ?? 'N/A' }}
                                        </div>
                                        <div class="text-sm text-gray-400">
                                            {{ $subscription->user->email ?? 'N/A' }}
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $subscription->status === 'active' ? 'bg-green-600 text-green-100' : 'bg-gray-600 text-gray-100' }}">
                                            {{ $subscription->status === 'active' ? 'Active' : ucfirst($subscription->status) }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-300">
                                        {{ $subscription->started_at ? $subscription->started_at->format('d/m/Y') : 'N/A' }}
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-300">
                                        {{ $subscription->expires_at ? $subscription->expires_at->format('d/m/Y') : 'N/A' }}
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <p class="text-gray-400 text-center py-8">No users have subscribed to this package yet.</p>
                    @endif
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Statistics -->
                <div class="bg-gray-800 rounded-xl border border-gray-700 p-6">
                    <h2 class="text-xl font-bold text-white mb-4">Statistics</h2>
                    <div class="space-y-4">
                        <div class="flex justify-between items-center">
                            <span class="text-gray-400">Total subscriptions</span>
                            <span class="text-white font-semibold text-lg">{{ $servicePackage->subscriptions->count() }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-400">Active</span>
                            <span class="text-green-400 font-semibold text-lg">{{ $servicePackage->activeSubscriptions->count() }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-400">Expired</span>
                            <span class="text-red-400 font-semibold text-lg">{{ $servicePackage->subscriptions->where('status', '!=', 'active')->count() }}</span>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                @can('edit-service-packages')
                <div class="bg-gray-800 rounded-xl border border-gray-700 p-6">
                    <h2 class="text-xl font-bold text-white mb-4">Actions</h2>
                    <div class="space-y-3">
                        <form method="POST" action="{{ route('service-packages.toggle-active', $servicePackage) }}" class="inline w-full">
                            @csrf
                            @method('PATCH')
                            <button type="submit" 
                                    class="w-full {{ $servicePackage->is_active ? 'bg-red-600 hover:bg-red-700' : 'bg-green-600 hover:bg-green-700' }} text-white px-4 py-2 rounded-lg transition-colors duration-200">
                                {{ $servicePackage->is_active ? 'Deactivate' : 'Activate' }}
                            </button>
                        </form>

                        @if(isset($servicePackage->is_featured))
                        <form method="POST" action="{{ route('service-packages.toggle-featured', $servicePackage) }}" class="inline w-full">
                            @csrf
                            @method('PATCH')
                            <button type="submit" 
                                    class="w-full bg-yellow-600 hover:bg-yellow-700 text-white px-4 py-2 rounded-lg transition-colors duration-200">
                                {{ $servicePackage->is_featured ? 'Remove featured' : 'Mark as featured' }}
                            </button>
                        </form>
                        @endif

                        @can('delete-service-packages')
                        @if($servicePackage->activeSubscriptions->count() === 0)
                        <form method="POST" action="{{ route('service-packages.destroy', $servicePackage) }}" 
                              onsubmit="return confirm('Are you sure you want to delete this package?');" 
                              class="inline w-full">
                            @csrf
                            @method('DELETE')
                            <button type="submit" 
                                    class="w-full bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg transition-colors duration-200">
                                Delete package
                            </button>
                        </form>
                        @else
                        <button disabled 
                                class="w-full bg-gray-600 text-gray-400 px-4 py-2 rounded-lg cursor-not-allowed"
                                title="Cannot delete a package with active subscribers">
                            Delete package
                        </button>
                        @endif
                        @endcan
                    </div>
                </div>
                @endcan

                <!-- Package Details -->
                <div class="bg-gray-800 rounded-xl border border-gray-700 p-6">
                    <h2 class="text-xl font-bold text-white mb-4">Details</h2>
                    <div class="space-y-3 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-400">Slug:</span>
                            <span class="text-white font-mono">{{ $servicePackage->slug }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-400">Monthly price:</span>
                            <span class="text-white">{{ number_format($servicePackage->monthly_price, 0, ',', '.') }} {{ $servicePackage->currency }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-400">Sort order:</span>
                            <span class="text-white">{{ $servicePackage->sort_order ?? 0 }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-400">Created at:</span>
                            <span class="text-white">{{ $servicePackage->created_at->format('d/m/Y H:i') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-400">Last updated:</span>
                            <span class="text-white">{{ $servicePackage->updated_at->format('d/m/Y H:i') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

