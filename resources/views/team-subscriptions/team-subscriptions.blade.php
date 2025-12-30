@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-slate-900 text-gray-100">
    <div class="container mx-auto px-4 py-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center justify-between mb-6">
                <div class="flex items-center">
                    <a href="{{ route('teams.show', $team) }}" class="text-blue-400 hover:text-blue-300 mr-4">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                    </a>
                    <div>
                        <h1 class="text-3xl font-bold text-white mb-2">Team Service Packages: {{ $team->name }}</h1>
                        <p class="text-gray-400">History and management of this team's subscriptions</p>
                    </div>
                </div>
                <div class="flex items-center space-x-3">
                    <a href="{{ route('team-subscriptions.create') }}" 
                       class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium transition-colors duration-200 flex items-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        Assign new package
                    </a>
                </div>
            </div>
        </div>

        <!-- Team Info Card -->
        <div class="bg-gray-800 rounded-xl border border-gray-700 p-6 mb-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <div class="w-16 h-16 bg-green-500/20 rounded-lg flex items-center justify-center mr-4">
                        <svg class="w-8 h-8 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-white">{{ $team->name }}</h2>
                        <p class="text-gray-400">{{ $team->description ?: 'No description' }}</p>
                        <div class="flex items-center mt-2 space-x-4 text-sm">
                            <span class="text-gray-300">{{ $team->users->count() }} members</span>
                            <span class="text-gray-300">•</span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $team->status === 'active' ? 'bg-green-600 text-green-100' : 'bg-red-600 text-red-100' }}">
                                {{ $team->status === 'active' ? 'Active' : 'Inactive' }}
                            </span>
                        </div>
                    </div>
                </div>
                
                @if($team->hasActiveSubscription())
                    @php
                        $currentSubscription = $team->currentSubscription();
                    @endphp
                    <div class="text-right">
                        <div class="text-green-400 font-semibold">Current package: {{ $currentSubscription->servicePackage->name }}</div>
                        <div class="text-gray-400 text-sm">{{ $currentSubscription->remaining_days }} days remaining</div>
                    </div>
                @else
                    <div class="text-right">
                        <div class="text-red-400 font-semibold">No service package</div>
                        <div class="text-gray-400 text-sm">Please assign a service package</div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Subscriptions List -->
        <div class="bg-gray-800 rounded-xl border border-gray-700 overflow-hidden">
            <div class="p-6">
                @if($subscriptions->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-700">
                            <thead class="bg-gray-700">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">
                                        Service package
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">
                                        Duration
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">
                                        Status
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">
                                        Payment
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">
                                        Assigned by
                                    </th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-300 uppercase tracking-wider">
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-gray-800 divide-y divide-gray-700">
                                @foreach($subscriptions as $subscription)
                                    <tr class="hover:bg-gray-700 transition-colors duration-200">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0 h-10 w-10">
                                                    <div class="h-10 w-10 rounded-full bg-gradient-to-r from-blue-500 to-purple-600 flex items-center justify-center">
                                                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                                        </svg>
                                                    </div>
                                                </div>
                                                <div class="ml-4">
                                                    <div class="text-sm font-medium text-white">
                                                        {{ $subscription->servicePackage->name }}
                                                        @if($subscription->isActive() && $subscription->id === ($team->currentSubscription()?->id))
                                                            <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-600 text-green-100">
                                                                Current
                                                            </span>
                                                        @endif
                                                    </div>
                                                    <div class="text-sm text-gray-400">
                                                        {{ $subscription->servicePackage->formatted_price }}
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300">
                                                <div class="space-y-1">
                                                    <div>📅 {{ $subscription->start_date->format('d/m/Y') }} - {{ $subscription->end_date->format('d/m/Y') }}</div>
                                                    <div>⏰ {{ $subscription->remaining_days }} days remaining</div>
                                                </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $subscription->status_badge_class }}">
                                                {{ $subscription->status_text }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300">
                                                <div class="space-y-1">
                                                    <div>{{ $subscription->formatted_paid_amount }}</div>
                                                    <div class="text-xs">{{ $subscription->payment_method ?: 'Not specified' }}</div>
                                                </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300">
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0 h-8 w-8">
                                                    <div class="h-8 w-8 rounded-full bg-gray-600 flex items-center justify-center">
                                                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                                        </svg>
                                                    </div>
                                                </div>
                                                <div class="ml-3">
                                                    <div class="text-sm font-medium text-white">
                                                        {{ $subscription->assignedBy ? $subscription->assignedBy->name : 'System' }}
                                                    </div>
                                                    <div class="text-sm text-gray-400">
                                                        {{ $subscription->created_at->format('d/m/Y H:i') }}
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <div class="flex items-center justify-end space-x-2">
                                                <a href="{{ route('team-subscriptions.show', $subscription) }}" 
                                                   class="text-blue-400 hover:text-blue-300 transition-colors duration-200">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                                    </svg>
                                                </a>

                                                <a href="{{ route('team-subscriptions.edit', $subscription) }}" 
                                                   class="text-indigo-400 hover:text-indigo-300 transition-colors duration-200">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                    </svg>
                                                </a>

                                                <form action="{{ route('team-subscriptions.destroy', $subscription) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this subscription?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-red-400 hover:text-red-300 transition-colors duration-200">
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

                    <!-- Pagination -->
                    <div class="mt-6">
                        {{ $subscriptions->links() }}
                    </div>
                @else
                    <div class="text-center py-12">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-300">No service packages</h3>
                        <p class="mt-1 text-sm text-gray-400">This team has no assigned packages yet.</p>
                        <div class="mt-6">
                            <a href="{{ route('team-subscriptions.create') }}" 
                               class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-lg text-white bg-blue-600 hover:bg-blue-700 transition-colors duration-200">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                </svg>
                                Assign the first package
                            </a>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
