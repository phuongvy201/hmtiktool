@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-slate-900 text-gray-100">
    <div class="container mx-auto px-4 py-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center justify-between mb-6">
                <div class="flex items-center">
                    <a href="{{ route('teams.index') }}" class="text-blue-400 hover:text-blue-300 mr-4">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                    </a>
                    <div>
                        <h1 class="text-3xl font-bold text-white mb-2">Team Details</h1>
                        <p class="text-gray-400">Detailed information about the team: {{ $team->name }}</p>
                    </div>
                </div>
                <div class="flex items-center space-x-3">
                    @can('edit-teams')
                    <a href="{{ route('teams.edit', $team) }}" class="bg-yellow-600 hover:bg-yellow-700 text-white px-4 py-2 rounded-lg font-medium transition-colors duration-200 flex items-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                        Edit
                    </a>
                    @endcan
                    @can('delete-teams')
                    <form action="{{ route('teams.destroy', $team) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this team?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg font-medium transition-colors duration-200 flex items-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                            Delete
                        </button>
                    </form>
                    @endcan
                </div>
            </div>
        </div>

        <!-- Team Information -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Team Card -->
            <div class="lg:col-span-1">
                <div class="bg-gray-800 rounded-xl border border-gray-700 p-6">
                    <div class="text-center mb-6">
                        <div class="w-24 h-24 bg-purple-500/20 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg class="w-12 h-12 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                        </div>
                        <h2 class="text-xl font-bold text-white">{{ $team->name }}</h2>
                        <p class="text-gray-400">Team in the system</p>
                    </div>
                    
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <span class="text-gray-400">Number of members:</span>
                            <span class="text-white font-medium">{{ $team->users->count() }}</span>
                        </div>
                        
                        <div class="flex items-center justify-between">
                            <span class="text-gray-400">Status:</span>
                            @if($team->status === 'active')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-500/20 text-green-400">
                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                    </svg>
                                    Active
                                </span>
                            @elseif($team->status === 'inactive')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-500/20 text-gray-400">
                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                                    </svg>
                                    Inactive
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-500/20 text-red-400">
                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                    </svg>
                                    Suspended
                                </span>
                            @endif
                        </div>
                        
                        <div class="flex items-center justify-between">
                                <span class="text-gray-400">Created date:</span>
                            <span class="text-gray-300">{{ $team->created_at->format('d/m/Y H:i') }}</span>
                        </div>
                        
                        <div class="flex items-center justify-between">
                            <span class="text-gray-400">Last updated:</span>
                            <span class="text-gray-300">{{ $team->updated_at->format('d/m/Y H:i') }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Details -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Description -->
                @if($team->description)
                <div class="bg-gray-800 rounded-xl border border-gray-700 p-6">
                    <h3 class="text-lg font-semibold text-white mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        Team Description
                    </h3>
                    <p class="text-gray-300 leading-relaxed">{{ $team->description }}</p>
                </div>
                @endif

                <!-- Team Members -->
                <div class="bg-gray-800 rounded-xl border border-gray-700 p-6">
                    <h3 class="text-lg font-semibold text-white mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                        </svg>
                        Team Members
                    </h3>
                    
                    @if($team->users->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead class="bg-gray-700">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Member</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Email</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Role</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Status</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Joined date</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-700">
                                    @foreach($team->users as $user)
                                    <tr class="hover:bg-gray-700 transition-colors duration-200">
                                        <td class="px-4 py-3">
                                            <div class="flex items-center">
                                                <div class="w-8 h-8 bg-blue-500/20 rounded-full flex items-center justify-center mr-3">
                                                    <span class="text-blue-400 font-semibold text-sm">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                                                </div>
                                                <span class="text-white font-medium">{{ $user->name }}</span>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3 text-gray-300">{{ $user->email }}</td>
                                        <td class="px-4 py-3">
                                            @if($user->roles->count() > 0)
                                                <div class="flex flex-wrap gap-1">
                                                    @foreach($user->roles->take(2) as $role)
                                                        <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-gray-700 text-gray-300">
                                                            {{ $role->name }}
                                                        </span>
                                                    @endforeach
                                                    @if($user->roles->count() > 2)
                                                        <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-gray-600 text-gray-400">
                                                            +{{ $user->roles->count() - 2 }}
                                                        </span>
                                                    @endif
                                                </div>
                                            @else
                                                <span class="text-gray-500 text-sm">No role</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3">
                                            @if($user->email_verified_at)
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-500/20 text-green-400">
                                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                                    </svg>
                                                    Verified
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-500/20 text-yellow-400">
                                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                                    </svg>
                                                    Not verified
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-gray-300">{{ $user->created_at->format('d/m/Y') }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-8">
                            <svg class="w-12 h-12 mx-auto text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                            </svg>
                            <p class="text-gray-400">This team has no members</p>
                        </div>
                    @endif
                </div>

                                 @if(auth()->user()->hasRole('admin'))
                     <!-- Team Service Package -->
                     <div class="bg-gray-800 rounded-xl border border-gray-700 p-6">
                         <div class="flex items-center justify-between mb-4">
                             <h3 class="text-lg font-semibold text-white flex items-center">
                                 <svg class="w-5 h-5 mr-2 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                     <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                 </svg>
                                 Team Service Package
                             </h3>
                             <a href="{{ route('team-subscriptions.create') }}" 
                                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors duration-200 flex items-center">
                                 <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                     <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                 </svg>
                                 Assign new package
                             </a>
                         </div>
                         
                         @if($team->hasActiveSubscription())
                             @php
                                 $currentSubscription = $team->currentSubscription();
                             @endphp
                             <div class="bg-green-500/10 border border-green-500/20 rounded-lg p-4">
                                 <div class="flex items-center justify-between">
                                     <div class="flex items-center">
                                         <div class="w-12 h-12 bg-green-500/20 rounded-lg flex items-center justify-center mr-4">
                                             <svg class="w-6 h-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                             </svg>
                                         </div>
                                         <div>
                                             <h4 class="text-white font-semibold">{{ $currentSubscription->servicePackage->name }}</h4>
                                             <p class="text-green-400 text-sm">{{ $currentSubscription->formatted_paid_amount }}</p>
                                         </div>
                                     </div>
                                     <div class="text-right">
                                            <div class="text-white font-medium">{{ $currentSubscription->remaining_days }} days remaining</div>
                                         <div class="text-gray-400 text-sm">Expired: {{ $currentSubscription->end_date->format('d/m/Y') }}</div>
                                     </div>
                                 </div>
                             </div>
                         @else
                             <div class="bg-gray-700/50 border border-gray-600 rounded-lg p-6 text-center">
                                 <svg class="w-12 h-12 mx-auto text-gray-500 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                     <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                 </svg>
                                 <h4 class="text-gray-300 font-medium mb-2">No service package</h4>
                                 <p class="text-gray-400 text-sm mb-4">This team has no service package assigned</p>
                                 <a href="{{ route('team-subscriptions.create') }}" 
                                    class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-medium transition-colors duration-200">
                                     <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                         <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                     </svg>
                                     Assign service package
                                 </a>
                             </div>
                         @endif

                         <!-- Subscription History -->
                         @if($team->subscriptions->count() > 0)
                             <div class="mt-6">
                                 <h4 class="text-md font-medium text-white mb-3">Service package history</h4>
                                 <div class="space-y-2">
                                     @foreach($team->subscriptions->take(3) as $subscription)
                                         <div class="flex items-center justify-between p-3 bg-gray-700/50 rounded-lg">
                                             <div class="flex items-center">
                                                 <div class="w-8 h-8 bg-blue-500/20 rounded-lg flex items-center justify-center mr-3">
                                                     <svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                         <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                                     </svg>
                                                 </div>
                                                 <div>
                                                     <div class="text-white text-sm">{{ $subscription->servicePackage->name }}</div>
                                                     <div class="text-gray-400 text-xs">{{ $subscription->start_date->format('d/m/Y') }} - {{ $subscription->end_date->format('d/m/Y') }}</div>
                                                 </div>
                                             </div>
                                             <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium {{ $subscription->status_badge_class }}">
                                                 {{ $subscription->status_text }}
                                             </span>
                                         </div>
                                     @endforeach
                                 </div>
                                 @if($team->subscriptions->count() > 3)
                                     <div class="mt-3 text-center">
                                         <a href="{{ route('team-subscriptions.team-subscriptions', $team) }}" 
                                            class="text-blue-400 hover:text-blue-300 text-sm">
                                             View all ({{ $team->subscriptions->count() }} packages)
                                         </a>
                                     </div>
                                 @endif
                             </div>
                         @endif
                     </div>
                 @endif

                <!-- Team Statistics -->
                <div class="bg-gray-800 rounded-xl border border-gray-700 p-6">
                    <h3 class="text-lg font-semibold text-white mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                            Team Statistics
                    </h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="bg-gray-700 rounded-lg p-4">
                            <div class="flex items-center">
                                <div class="w-8 h-8 bg-blue-500/20 rounded-lg flex items-center justify-center mr-3">
                                    <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-gray-400 text-sm">Total members</p>
                                    <p class="text-white font-semibold">{{ $team->users->count() }}</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="bg-gray-700 rounded-lg p-4">
                            <div class="flex items-center">
                                <div class="w-8 h-8 bg-green-500/20 rounded-lg flex items-center justify-center mr-3">
                                    <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-gray-400 text-sm">Verified</p>
                                    <p class="text-white font-semibold">{{ $team->users->where('email_verified_at', '!=', null)->count() }}</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="bg-gray-700 rounded-lg p-4">
                            <div class="flex items-center">
                                <div class="w-8 h-8 bg-yellow-500/20 rounded-lg flex items-center justify-center mr-3">
                                    <svg class="w-5 h-5 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                                <div>
                                        <p class="text-gray-400 text-sm">Not verified</p>
                                    <p class="text-white font-semibold">{{ $team->users->where('email_verified_at', null)->count() }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
