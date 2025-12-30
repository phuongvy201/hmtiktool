@props(['user'])

@php
    $canDelete = auth()->user()->can('delete', $user);
    $isSelf = $user->id === auth()->id();
    $isLastSystemAdmin = $user->hasRole('system-admin') && \App\Models\User::role('system-admin')->count() <= 1;
    $isTeamAdmin = auth()->user()->hasRole('team-admin');
    $isHigherRole = $isTeamAdmin && $user->hasRole(['system-admin', 'manager']);
    $isDifferentTeam = $isTeamAdmin && $user->team_id !== auth()->user()->team_id;
@endphp

@if($canDelete)
    <form action="{{ route('users.destroy', $user) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this user?')">
        @csrf
        @method('DELETE')
        <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded-lg text-sm font-medium transition-colors duration-200 flex items-center">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
            </svg>
            Delete
        </button>
    </form>
@else
    <div class="relative group">
        <button disabled class="bg-gray-500 text-gray-300 px-3 py-1 rounded-lg text-sm font-medium cursor-not-allowed flex items-center">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
            </svg>
            Delete
        </button>
        
        <!-- Tooltip -->
        <div class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 px-3 py-2 bg-gray-900 text-white text-xs rounded-lg opacity-0 group-hover:opacity-100 transition-opacity duration-200 whitespace-nowrap z-10">
            @if($isSelf)
                Cannot delete yourself
            @elseif($isLastSystemAdmin)
                Cannot delete the last system admin
            @elseif($isHigherRole)
                Cannot delete a user with higher privileges
            @elseif($isDifferentTeam)
                Can only delete users in your team
            @else
                No permission to delete this user
            @endif
            
            <!-- Arrow -->
            <div class="absolute top-full left-1/2 transform -translate-x-1/2 w-0 h-0 border-l-4 border-r-4 border-t-4 border-transparent border-t-gray-900"></div>
        </div>
    </div>
@endif
