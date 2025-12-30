@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-slate-900 text-gray-100">
    <div class="container mx-auto px-4 py-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center justify-between mb-6">
                <div class="flex items-center">
                    <a href="{{ route('team-subscriptions.index') }}" class="text-blue-400 hover:text-blue-300 mr-4">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                    </a>
                    <div>
                        <h1 class="text-3xl font-bold text-white mb-2">Team Service Package Details</h1>
                        <p class="text-gray-400">Detailed information about this team subscription</p>
                    </div>
                </div>
                <div class="flex items-center space-x-3">
                    <a href="{{ route('team-subscriptions.edit', $teamSubscription) }}" 
                       class="bg-yellow-600 hover:bg-yellow-700 text-white px-4 py-2 rounded-lg font-medium transition-colors duration-200 flex items-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                        Edit
                    </a>
                    <form action="{{ route('team-subscriptions.destroy', $teamSubscription) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this subscription?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg font-medium transition-colors duration-200 flex items-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                            Delete
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Subscription Details -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Team Information -->
            <div class="lg:col-span-1">
                <div class="bg-gray-800 rounded-xl border border-gray-700 p-6">
                    <h3 class="text-lg font-semibold text-white mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                        Team Information
                    </h3>
                    
                    <div class="space-y-4">
                        <div class="flex items-center">
                            <div class="w-12 h-12 bg-green-500/20 rounded-lg flex items-center justify-center mr-4">
                                <svg class="w-6 h-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                </svg>
                            </div>
                            <div>
                                <h4 class="text-white font-semibold">{{ $teamSubscription->team->name }}</h4>
                                <p class="text-gray-400 text-sm">{{ $teamSubscription->team->description ?: 'No description' }}</p>
                            </div>
                        </div>
                        
                        <div class="space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="text-gray-400">Members:</span>
                                <span class="text-white font-medium">{{ $teamSubscription->team->users->count() }}</span>
                            </div>
                            
                            <div class="flex items-center justify-between">
                                <span class="text-gray-400">Team status:</span>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $teamSubscription->team->status === 'active' ? 'bg-green-600 text-green-100' : 'bg-red-600 text-red-100' }}">
                                    {{ $teamSubscription->team->status === 'active' ? 'Active' : 'Inactive' }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Package Information -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Package Details -->
                <div class="bg-gray-800 rounded-xl border border-gray-700 p-6">
                    <h3 class="text-lg font-semibold text-white mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                        </svg>
                        Service Package Information
                    </h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-400 mb-1">Package name</label>
                                <p class="text-white font-semibold">{{ $teamSubscription->servicePackage->name }}</p>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-400 mb-1">Description</label>
                                <p class="text-gray-300">{{ $teamSubscription->servicePackage->description ?: 'No description' }}</p>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-400 mb-1">Package price</label>
                                <p class="text-white font-semibold text-lg">{{ $teamSubscription->servicePackage->formatted_price }}</p>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-400 mb-1">Package duration</label>
                                <p class="text-gray-300">{{ $teamSubscription->servicePackage->duration_days }} days</p>
                            </div>
                        </div>
                        
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-400 mb-1">User limit</label>
                                <p class="text-white">{{ $teamSubscription->servicePackage->max_users }} users</p>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-400 mb-1">Project limit</label>
                                <p class="text-white">{{ $teamSubscription->servicePackage->max_projects }} projects</p>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-400 mb-1">Storage limit</label>
                                <p class="text-white">{{ $teamSubscription->servicePackage->max_storage_gb }}GB</p>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-400 mb-1">Features</label>
                                <div class="flex flex-wrap gap-1">
                                    @if($teamSubscription->servicePackage->features)
                                        @foreach($teamSubscription->servicePackage->features as $feature)
                                            <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-blue-600 text-blue-100">
                                                {{ $feature }}
                                            </span>
                                        @endforeach
                                    @else
                                        <span class="text-gray-500 text-sm">No special features</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Subscription Details -->
                <div class="bg-gray-800 rounded-xl border border-gray-700 p-6">
                    <h3 class="text-lg font-semibold text-white mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Subscription Details
                    </h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-400 mb-1">Status</label>
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $teamSubscription->status_badge_class }}">
                                    {{ $teamSubscription->status_text }}
                                </span>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-400 mb-1">Start date</label>
                                <p class="text-white">{{ $teamSubscription->start_date->format('d/m/Y') }}</p>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-400 mb-1">End date</label>
                                <p class="text-white">{{ $teamSubscription->end_date->format('d/m/Y') }}</p>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-400 mb-1">Remaining days</label>
                                <p class="text-white font-semibold">{{ $teamSubscription->remaining_days }} days</p>
                            </div>
                        </div>
                        
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-400 mb-1">Paid amount</label>
                                <p class="text-white font-semibold">{{ $teamSubscription->formatted_paid_amount }}</p>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-400 mb-1">Payment method</label>
                                <p class="text-white">{{ $teamSubscription->payment_method ?: 'Not specified' }}</p>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-400 mb-1">Transaction ID</label>
                                <p class="text-white">{{ $teamSubscription->transaction_id ?: 'Not available' }}</p>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-400 mb-1">Auto renew</label>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $teamSubscription->auto_renew ? 'bg-green-600 text-green-100' : 'bg-gray-600 text-gray-100' }}">
                                    {{ $teamSubscription->auto_renew ? 'Yes' : 'No' }}
                                </span>
                            </div>
                        </div>
                    </div>
                    
                    @if($teamSubscription->notes)
                        <div class="mt-6 pt-6 border-t border-gray-700">
                            <label class="block text-sm font-medium text-gray-400 mb-2">Notes</label>
                            <div class="bg-gray-700 rounded-lg p-4">
                                <p class="text-gray-300">{{ $teamSubscription->notes }}</p>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Assignment Information -->
                <div class="bg-gray-800 rounded-xl border border-gray-700 p-6">
                    <h3 class="text-lg font-semibold text-white mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                        Assignment Information
                    </h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-400 mb-1">Assigned by</label>
                            <div class="flex items-center">
                                <div class="w-8 h-8 bg-blue-500/20 rounded-full flex items-center justify-center mr-3">
                                    <svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                    </svg>
                                </div>
                                <span class="text-white">{{ $teamSubscription->assignedBy ? $teamSubscription->assignedBy->name : 'System' }}</span>
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-400 mb-1">Assigned at</label>
                            <p class="text-white">{{ $teamSubscription->created_at->format('d/m/Y H:i') }}</p>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-400 mb-1">Last updated</label>
                            <p class="text-white">{{ $teamSubscription->updated_at->format('d/m/Y H:i') }}</p>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-400 mb-1">Subscription ID</label>
                            <p class="text-white font-mono text-sm">{{ $teamSubscription->id }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
