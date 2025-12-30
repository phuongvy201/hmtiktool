@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-slate-900 text-gray-100">
    <div class="container mx-auto px-4 py-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center mb-6">
                <a href="{{ route('teams.index') }}" class="text-blue-400 hover:text-blue-300 mr-4">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                </a>
                <div>
                    <h1 class="text-3xl font-bold text-white mb-2">Edit Team</h1>
                    <p class="text-gray-400">Update team information: {{ $team->name }}</p>
                </div>
            </div>
        </div>

        <!-- Form -->
        <div class="max-w-4xl mx-auto">
            <div class="bg-gray-800 rounded-xl border border-gray-700 p-6">
                <form action="{{ route('teams.update', $team) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <!-- Basic Information -->
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold text-white mb-4 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                            Team Information
                        </h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-300 mb-2">Team name *</label>
                                <input type="text" id="name" name="name" value="{{ old('name', $team->name) }}" required
                                       placeholder="Example: Team Marketing, Team Sales..."
                                       class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-2 text-white placeholder-gray-400 focus:outline-none focus:border-blue-500 @error('name') border-red-500 @enderror">
                                @error('name')
                                    <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <div>
                                <label for="status" class="block text-sm font-medium text-gray-300 mb-2">Status *</label>
                                <select id="status" name="status" required
                                        class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-2 text-white focus:outline-none focus:border-blue-500 @error('status') border-red-500 @enderror">
                                    <option value="">Select status</option>
                                    <option value="active" {{ old('status', $team->status) == 'active' ? 'selected' : '' }}>Hoạt động</option>
                                        <option value="inactive" {{ old('status', $team->status) == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                    <option value="suspended" {{ old('status', $team->status) == 'suspended' ? 'selected' : '' }}>Suspended</option>
                                </select>
                                @error('status')
                                    <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="mt-6">
                            <label for="description" class="block text-sm font-medium text-gray-300 mb-2">Description</label>
                            <textarea id="description" name="description" rows="4" 
                                      placeholder="Description of the team, objectives, functions..."
                                      class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-2 text-white placeholder-gray-400 focus:outline-none focus:border-blue-500 @error('description') border-red-500 @enderror">{{ old('description', $team->description) }}</textarea>
                            @error('description')
                                <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- TikTok Markets -->
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold text-white mb-3 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m-4 4h12M9 9v2m-4 4h12M9 15v2m7-6h5m-2-2v4"></path>
                            </svg>
                            TikTok Markets (select one or both)
                        </h3>
                        @php
                            $selectedMarkets = old('markets', $team->tiktokMarkets->pluck('market')->toArray());
                        @endphp
                        <div class="bg-gray-700 rounded-lg p-4 space-y-2">
                            <label class="flex items-center text-sm text-gray-200">
                                <input type="checkbox" name="markets[]" value="US"
                                       class="w-4 h-4 text-blue-600 bg-gray-600 border-gray-500 rounded focus:ring-blue-500"
                                       {{ in_array('US', $selectedMarkets) ? 'checked' : '' }}>
                                <span class="ml-2">🇺🇸 United States (US)</span>
                            </label>
                            <label class="flex items-center text-sm text-gray-200">
                                <input type="checkbox" name="markets[]" value="UK"
                                       class="w-4 h-4 text-blue-600 bg-gray-600 border-gray-500 rounded focus:ring-blue-500"
                                       {{ in_array('UK', $selectedMarkets) ? 'checked' : '' }}>
                                <span class="ml-2">🇬🇧 United Kingdom (UK)</span>
                            </label>
                            <p class="text-xs text-gray-400 mt-1">Market access for this team when connecting TikTok Shop.</p>
                        </div>
                        @error('markets')
                            <p class="text-red-400 text-sm mt-2">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Team Members -->
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold text-white mb-4 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                            </svg>
                            Team Members
                        </h3>
                        
                        @if($users->count() > 0)
                            <div class="bg-gray-700 rounded-lg p-4">
                                <div class="flex items-center justify-between mb-4">
                                    <span class="text-gray-300 text-sm">Select members for the team:</span>
                                    <button type="button" id="select-all" class="text-blue-400 hover:text-blue-300 text-sm">
                                        Select all
                                    </button>
                                </div>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3 max-h-64 overflow-y-auto">
                                    @foreach($users as $user)
                                        <label class="flex items-center p-3 bg-gray-600 rounded-lg hover:bg-gray-500 transition-colors duration-200 cursor-pointer">
                                            <input type="checkbox" name="members[]" value="{{ $user->id }}" 
                                                   {{ in_array($user->id, old('members', $team->users->pluck('id')->toArray())) ? 'checked' : '' }}
                                                   class="member-checkbox w-4 h-4 text-blue-600 bg-gray-600 border-gray-500 rounded focus:ring-blue-500 focus:ring-2">
                                            <div class="ml-3">
                                                <div class="flex items-center">
                                                    <div class="w-6 h-6 bg-blue-500/20 rounded-full flex items-center justify-center mr-2">
                                                        <span class="text-blue-400 font-semibold text-xs">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                                                    </div>
                                                    <span class="text-gray-300 text-sm font-medium">{{ $user->name }}</span>
                                                    @if($user->team_id == $team->id)
                                                        <span class="ml-2 inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-green-500/20 text-green-400">
                                                            Current
                                                        </span>
                                                    @endif
                                                </div>
                                                <div class="text-gray-400 text-xs">{{ $user->email }}</div>
                                            </div>
                                        </label>
                                    @endforeach
                                </div>
                                
                                <div class="mt-4 text-sm text-gray-400">
                                    <p>• Only display Team Level users (not System Level)</p>
                                    <p>• Users can only belong to one team</p>
                                    <p>• The "Current" tag indicates the user belongs to this team</p>
                                </div>
                            </div>
                        @else
                            <div class="bg-gray-700 rounded-lg p-6 text-center">
                                <svg class="w-12 h-12 mx-auto text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                                </svg>
                                <p class="text-gray-400">No Team Level users to add to the team</p>
                                <p class="text-gray-500 text-sm mt-2">Please create Team Level users first</p>
                            </div>
                        @endif
                        @error('members')
                            <p class="text-red-400 text-sm mt-2">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Current Team Info -->
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold text-white mb-4 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            Current Team Information
                        </h3>
                        
                        <div class="bg-gray-700 rounded-lg p-4">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                                <div>
                                    <span class="text-gray-400">Current members:</span>
                                    <span class="text-white font-medium ml-2">{{ $team->users->count() }}</span>
                                </div>
                                <div>
                                    <span class="text-gray-400">Created date:</span>
                                    <span class="text-gray-300 ml-2">{{ $team->created_at->format('d/m/Y') }}</span>
                                </div>
                                <div>
                                    <span class="text-gray-400">Last updated:</span>
                                    <span class="text-gray-300 ml-2">{{ $team->updated_at->format('d/m/Y') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Team Statistics Preview -->
                    <div class="mb-6">
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
                                        <p class="text-white font-semibold" id="total-members">{{ $team->users->count() }}</p>
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
                                        <p class="text-gray-400 text-sm">Available users</p>
                                        <p class="text-white font-semibold">{{ $users->count() }}</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="bg-gray-700 rounded-lg p-4">
                                <div class="flex items-center">
                                    <div class="w-8 h-8 bg-purple-500/20 rounded-lg flex items-center justify-center mr-3">
                                        <svg class="w-5 h-5 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="text-gray-400 text-sm">Current teams</p>
                                        <p class="text-white font-semibold">{{ \App\Models\Team::count() }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center justify-end space-x-4 pt-6 border-t border-gray-700">
                        <a href="{{ route('teams.index') }}" 
                           class="px-6 py-2 border border-gray-600 text-gray-300 rounded-lg hover:bg-gray-700 transition-colors duration-200">
                            Cancel
                        </a>
                        <button type="submit" 
                                class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition-colors duration-200">
                            Update Team
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const memberCheckboxes = document.querySelectorAll('.member-checkbox');
    const selectAllBtn = document.getElementById('select-all');
    const totalMembersSpan = document.getElementById('total-members');
    
    // Update total members count
    function updateMemberCount() {
        const checkedCount = document.querySelectorAll('.member-checkbox:checked').length;
        totalMembersSpan.textContent = checkedCount;
    }
    
    // Select all functionality
    selectAllBtn.addEventListener('click', function() {
        const allChecked = Array.from(memberCheckboxes).every(cb => cb.checked);
        
        memberCheckboxes.forEach(checkbox => {
            checkbox.checked = !allChecked;
        });
        
        updateMemberCount();
        updateSelectAllText();
    });
    
    // Update select all text
    function updateSelectAllText() {
        const allChecked = Array.from(memberCheckboxes).every(cb => cb.checked);
        const someChecked = Array.from(memberCheckboxes).some(cb => cb.checked);
        
        if (allChecked) {
                        selectAllBtn.textContent = 'Unselect all';
        } else if (someChecked) {
            selectAllBtn.textContent = 'Select all';
        } else {
            selectAllBtn.textContent = 'Select all';
        }
    }
    
    // Listen for checkbox changes
    memberCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            updateMemberCount();
            updateSelectAllText();
        });
    });
    
    // Initialize
    updateMemberCount();
    updateSelectAllText();
});
</script>
@endsection
