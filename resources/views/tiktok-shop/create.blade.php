@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-slate-900 text-gray-100">
    <div class="container mx-auto px-4 py-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center mb-6">
                <a href="{{ route('tiktok-shop.index') }}" class="text-blue-400 hover:text-blue-300 mr-4">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                </a>
                <div>
                    <h1 class="text-3xl font-bold text-white mb-2">Create TikTok Shop Integration</h1>
                    <p class="text-gray-400">Create a new TikTok Shop integration with a profile name for easy identification</p>
                </div>
            </div>
        </div>

        <!-- Form -->
        <div class="max-w-2xl mx-auto">
            <div class="bg-gray-800 rounded-xl border border-gray-700 p-6">
                <form action="{{ route('tiktok-shop.store') }}" method="POST">
                    @csrf
                    
                    <!-- Integration Information -->
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold text-white mb-4 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                            </svg>
                            Integration Information
                        </h3>
                        
                        <div class="space-y-4">
                            <div>
                                <label for="team_id" class="block text-sm font-medium text-gray-300 mb-2">Select Team *</label>
                                <select id="team_id" name="team_id" required
                                        class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-2 text-white focus:outline-none focus:border-blue-500 @error('team_id') border-red-500 @enderror">
                                    <option value="">Select team...</option>
                                    @foreach($teams as $team)
                                        <option value="{{ $team->id }}" {{ old('team_id') == $team->id ? 'selected' : '' }}>
                                            {{ $team->name }} (ID: {{ $team->id }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('team_id')
                                    <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-300 mb-2">Profile Name *</label>
                                <input type="text" id="name" name="name" value="{{ old('name') }}" required
                                       class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-2 text-white placeholder-gray-400 focus:outline-none focus:border-blue-500 @error('name') border-red-500 @enderror"
                                       placeholder="e.g., My TikTok Account, Shop Profile 1, etc.">
                                <p class="text-gray-400 text-xs mt-1">Enter a name to easily identify this integration</p>
                                @error('name')
                                    <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <div>
                                <label for="market" class="block text-sm font-medium text-gray-300 mb-2">Market *</label>
                                <select id="market" name="market" required
                                        class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-2 text-white focus:outline-none focus:border-blue-500 @error('market') border-red-500 @enderror">
                                    <option value="">Select market...</option>
                                    <option value="US" {{ old('market') == 'US' ? 'selected' : '' }}>United States (US)</option>
                                    <option value="UK" {{ old('market') == 'UK' ? 'selected' : '' }}>United Kingdom (UK)</option>
                                </select>
                                @error('market')
                                    <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <div>
                                <label for="description" class="block text-sm font-medium text-gray-300 mb-2">Description (Optional)</label>
                                <textarea id="description" name="description" rows="3"
                                          class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-2 text-white placeholder-gray-400 focus:outline-none focus:border-blue-500 @error('description') border-red-500 @enderror"
                                          placeholder="Add any additional notes about this integration...">{{ old('description') }}</textarea>
                                @error('description')
                                    <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Important Notes -->
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold text-white mb-4 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                            </svg>
                            Important Notes
                        </h3>
                        
                        <div class="bg-yellow-500/10 border border-yellow-500/20 rounded-lg p-4">
                            <ul class="list-disc list-inside space-y-2 text-gray-300 text-sm">
                                <li>After creating the integration, you will need to complete the authorization process</li>
                                <li>The profile name will be used to identify this integration in the dashboard</li>
                                <li>You can create multiple integrations with different profile names for the same team</li>
                                <li>Make sure to select the correct market (US or UK) for your TikTok Shop account</li>
                            </ul>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center justify-end space-x-4 pt-6 border-t border-gray-700">
                        <a href="{{ route('tiktok-shop.index') }}" 
                           class="px-6 py-2 border border-gray-600 text-gray-300 rounded-lg hover:bg-gray-700 transition-colors duration-200">
                            Cancel
                        </a>
                        <button type="submit" 
                                class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition-colors duration-200">
                            Create Integration
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
