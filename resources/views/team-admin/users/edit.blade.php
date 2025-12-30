@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-slate-900 text-gray-100">
    <div class="container mx-auto px-4 py-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h1 class="text-3xl font-bold text-white mb-2">Edit Member</h1>
                    <p class="text-gray-400">Edit member information: <span class="text-blue-400 font-medium">{{ $user->name }}</span></p>
                </div>
                <a href="{{ route('team-admin.users.index') }}" class="bg-gray-600 hover:bg-gray-500 text-white px-6 py-3 rounded-lg font-medium transition-colors duration-200 flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Back
                </a>
            </div>
        </div>

        <!-- Form -->
        <div class="max-w-2xl mx-auto">
            <div class="bg-gray-800 rounded-xl border border-gray-700 p-6">
                <form method="POST" action="{{ route('team-admin.users.update', $user) }}">
                    @csrf
                    @method('PUT')

                    <!-- Name -->
                    <div class="mb-6">
                        <label for="name" class="block text-sm font-medium text-gray-300 mb-2">Full name</label>
                        <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}" required
                               class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-3 text-white placeholder-gray-400 focus:outline-none focus:border-blue-500 @error('name') border-red-500 @enderror"
                               placeholder="Enter full name">
                        @error('name')
                            <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Email -->
                    <div class="mb-6">
                        <label for="email" class="block text-sm font-medium text-gray-300 mb-2">Email</label>
                        <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}" required
                               class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-3 text-white placeholder-gray-400 focus:outline-none focus:border-blue-500 @error('email') border-red-500 @enderror"
                               placeholder="Enter email">
                        @error('email')
                            <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Password (Optional) -->
                    <div class="mb-6">
                        <label for="password" class="block text-sm font-medium text-gray-300 mb-2">New password (leave blank if unchanged)</label>
                        <input type="password" id="password" name="password"
                               class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-3 text-white placeholder-gray-400 focus:outline-none focus:border-blue-500 @error('password') border-red-500 @enderror"
                               placeholder="Enter new password (minimum 8 characters)">
                        @error('password')
                            <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Password Confirmation -->
                    <div class="mb-6">
                        <label for="password_confirmation" class="block text-sm font-medium text-gray-300 mb-2">Confirm new password</label>
                        <input type="password" id="password_confirmation" name="password_confirmation"
                               class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-3 text-white placeholder-gray-400 focus:outline-none focus:border-blue-500"
                               placeholder="Re-enter new password">
                    </div>

                    <!-- Role -->
                    <div class="mb-6">
                        <label for="role_id" class="block text-sm font-medium text-gray-300 mb-2">Role</label>
                        <select id="role_id" name="role_id" required
                                class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-3 text-white focus:outline-none focus:border-blue-500 @error('role_id') border-red-500 @enderror">
                            <option value="">Select a role</option>
                            @foreach($roles as $role)
                                <option value="{{ $role->id }}" 
                                    {{ old('role_id', $user->roles->first()->id ?? '') == $role->id ? 'selected' : '' }}>
                                    {{ $role->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('role_id')
                            <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- TikTok Market -->
                    <div class="mb-6">
                        <label for="market" class="block text-sm font-medium text-gray-300 mb-2">
                            Default TikTok market for this member
                        </label>
                        <select id="market" name="market"
                                class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-3 text-white focus:outline-none focus:border-blue-500">
                            <option value="">Select market</option>
                            <option value="US" {{ old('market', $user->getPrimaryTikTokMarket() ?? '') === 'US' ? 'selected' : '' }}>🇺🇸 United States (US)</option>
                            <option value="UK" {{ old('market', $user->getPrimaryTikTokMarket() ?? '') === 'UK' ? 'selected' : '' }}>🇬🇧 United Kingdom (UK)</option>
                        </select>
                        <p class="mt-1 text-sm text-gray-400">Choose the market the member will work on.</p>
                    </div>

                    <!-- Team Info (Read-only) -->
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-300 mb-2">Team</label>
                        <div class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-3 text-gray-300">
                            {{ $user->team->name ?? 'N/A' }}
                        </div>
                        <p class="mt-1 text-sm text-gray-400">Member will always belong to this team</p>
                    </div>

                    <!-- Current Status -->
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-300 mb-2">Current status</label>
                        <div class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-3">
                            @if($user->email_verified_at)
                                <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-green-500/20 text-green-400">
                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                    </svg>
                                    Email verified
                                </span>
                            @else
                                <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-yellow-500/20 text-yellow-400">
                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                    </svg>
                                    Email not verified
                                </span>
                            @endif
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="flex justify-end space-x-4">
                        <a href="{{ route('team-admin.users.index') }}" 
                           class="bg-gray-600 hover:bg-gray-500 text-white px-6 py-3 rounded-lg font-medium transition-colors duration-200">
                            Cancel
                        </a>
                        <button type="submit" 
                                class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-medium transition-colors duration-200 flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            Update Member
                        </button>
                    </div>
                </form>
            </div>

            <!-- Help Information -->
            <div class="mt-6 bg-blue-500/10 border border-blue-500/20 rounded-lg p-4">
                <div class="flex items-start">
                    <svg class="w-5 h-5 text-blue-400 mt-0.5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <div>
                        <h3 class="text-blue-400 font-medium mb-1">Important notes</h3>
                        <ul class="text-sm text-gray-300 space-y-1">
                            <li>• Fill password only if you want to change it</li>
                            <li>• Email must be unique in the system</li>
                            <li>• Member will always belong to the current team</li>
                            <li>• Email verification status cannot be changed</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
