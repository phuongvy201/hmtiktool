@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-slate-900 text-gray-100">
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-md mx-auto">
            <div class="bg-gray-800 rounded-xl border border-gray-700 p-8">
                <!-- Header -->
                <div class="text-center mb-8">
                    <div class="w-16 h-16 bg-yellow-500/20 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                        </svg>
                    </div>
                    <h1 class="text-2xl font-bold text-white mb-2">Resend Verification Email</h1>
                    <p class="text-gray-400">Verification link expired? Send a new one</p>
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

                @if(session('info'))
                    <div class="bg-blue-500/20 border border-blue-500/50 text-blue-400 px-4 py-3 rounded-lg mb-6">
                        {{ session('info') }}
                    </div>
                @endif

                <!-- Form -->
                <form method="POST" action="{{ route('verification.resend') }}">
                    @csrf

                    <div class="mb-6">
                        <label for="email" class="block text-sm font-medium text-gray-300 mb-2">Email</label>
                        <input type="email" id="email" name="email" value="{{ old('email') }}" required
                               class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-3 text-white placeholder-gray-400 focus:outline-none focus:border-blue-500 @error('email') border-red-500 @enderror"
                               placeholder="Enter your email">
                        @error('email')
                            <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <button type="submit" 
                            class="w-full bg-yellow-600 hover:bg-yellow-700 text-white px-6 py-3 rounded-lg font-medium transition-colors duration-200 flex items-center justify-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Resend Verification Email
                    </button>
                </form>

                <!-- Links -->
                <div class="mt-6 text-center">
                    <p class="text-gray-400 text-sm">
                        <a href="{{ route('verification.notice') }}" class="text-blue-400 hover:text-blue-300 transition-colors duration-200">
                            ← Back to verification page
                        </a>
                    </p>
                </div>

                <!-- Help Information -->
                <div class="mt-6 bg-yellow-500/10 border border-yellow-500/20 rounded-lg p-4">
                    <div class="flex items-start">
                        <svg class="w-5 h-5 text-yellow-400 mt-0.5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <div>
                            <h3 class="text-yellow-400 font-medium mb-1">Notes</h3>
                            <ul class="text-sm text-gray-300 space-y-1">
                                <li>• You can resend after 5 minutes</li>
                                <li>• New verification link is valid for 60 minutes</li>
                                <li>• Check the spam folder if you do not see the email</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
