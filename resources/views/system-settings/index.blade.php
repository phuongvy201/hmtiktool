@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-slate-900 text-gray-100">
    <div class="container mx-auto px-4 py-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h1 class="text-3xl font-bold text-white mb-2">System settings</h1>
                    <p class="text-gray-400">Manage system settings</p>
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

        <!-- Tabs -->
        <div class="bg-gray-800 rounded-xl border border-gray-700 mb-6">
            <div class="border-b border-gray-700">
                <nav class="flex -mb-px">
                    @foreach($groups as $groupKey => $groupLabel)
                    <a href="{{ route('system.settings', ['tab' => $groupKey]) }}" 
                       class="px-6 py-4 text-sm font-medium border-b-2 transition-colors duration-200 {{ $activeTab === $groupKey ? 'border-blue-500 text-blue-400' : 'border-transparent text-gray-400 hover:text-gray-300 hover:border-gray-600' }}">
                        {{ $groupLabel }}
                    </a>
                    @endforeach
                </nav>
            </div>

            <!-- Settings Form -->
            <form method="POST" action="{{ route('system.settings.update') }}" class="p-6">
                @csrf
                
                <div class="space-y-6">
                    @forelse($settings as $index => $setting)
                    <div class="bg-gray-700 rounded-lg p-4">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="md:col-span-1">
                                <label class="block text-sm font-medium text-gray-300 mb-2">
                                    {{ $setting->label ?? ucfirst(str_replace('_', ' ', $setting->key)) }}
                                </label>
                                @if($setting->description)
                                <p class="text-xs text-gray-400 mb-2">{{ $setting->description }}</p>
                                @endif
                            </div>
                            <div class="md:col-span-2">
                                <input type="hidden" name="settings[{{ $index }}][key]" value="{{ $setting->key }}">
                                <input type="hidden" name="settings[{{ $index }}][type]" value="{{ $setting->type }}">
                                
                                @if($setting->type === 'boolean')
                                <div class="flex items-center">
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" 
                                               name="settings[{{ $index }}][value]" 
                                               value="1" 
                                               class="sr-only peer"
                                               {{ $setting->typed_value ? 'checked' : '' }}>
                                        <div class="w-11 h-6 bg-gray-600 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-800 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                                        <span class="ml-3 text-sm text-gray-300">
                                            {{ $setting->typed_value ? 'Enable' : 'Disable' }}
                                        </span>
                                    </label>
                                </div>
                                @elseif($setting->type === 'json' || $setting->type === 'array')
                                <textarea 
                                    name="settings[{{ $index }}][value]" 
                                    rows="4"
                                    class="w-full bg-gray-600 border border-gray-500 rounded-lg px-4 py-2 text-white placeholder-gray-400 focus:outline-none focus:border-blue-500"
                                    placeholder="Enter JSON data or comma-separated list">{{ is_array($setting->typed_value) ? json_encode($setting->typed_value, JSON_PRETTY_PRINT) : $setting->value }}</textarea>
                                @else
                                <input 
                                    type="{{ $setting->type === 'integer' ? 'number' : 'text' }}" 
                                    name="settings[{{ $index }}][value]" 
                                    value="{{ $setting->typed_value ?? $setting->value }}"
                                    class="w-full bg-gray-600 border border-gray-500 rounded-lg px-4 py-2 text-white placeholder-gray-400 focus:outline-none focus:border-blue-500"
                                    placeholder="Enter value">
                                @endif
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="bg-gray-700 rounded-lg p-8 text-center">
                        <p class="text-gray-400">No settings in this group.</p>
                        <form method="POST" action="{{ route('system.settings.reset') }}" class="mt-4">
                            @csrf
                            <input type="hidden" name="group" value="{{ $activeTab }}">
                            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors duration-200">
                                Reset default settings
                            </button>
                        </form>
                    </div>
                    @endforelse
                </div>

                @if($settings->count() > 0)
                <div class="flex justify-end gap-4 mt-6">
                    <a href="{{ route('system.settings', ['tab' => $activeTab]) }}" 
                       class="bg-gray-600 hover:bg-gray-500 text-white px-6 py-2 rounded-lg transition-colors duration-200">
                        Cancel
                    </a>
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg transition-colors duration-200">
                        Save settings
                    </button>
                    <form method="POST" action="{{ route('system.settings.reset') }}" class="inline">
                        @csrf
                        <input type="hidden" name="group" value="{{ $activeTab }}">
                        <button type="submit" 
                                onclick="return confirm('Are you sure you want to reset all settings in this group to default?');"
                                class="bg-red-600 hover:bg-red-700 text-white px-6 py-2 rounded-lg transition-colors duration-200">
                            Reset to default
                        </button>
                    </form>
                </div>
                @endif
            </form>
        </div>

        <!-- Export/Import Section -->
        <div class="bg-gray-800 rounded-xl border border-gray-700 p-6">
                <h2 class="text-xl font-bold text-white mb-4">Export/Import Configuration</h2>
            <div class="flex gap-4">
                <form method="GET" action="{{ route('system.settings.export') }}" class="inline">
                    <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-lg transition-colors duration-200">
                        <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        Export configuration
                    </button>
                </form>
                <form method="POST" action="{{ route('system.settings.import') }}" enctype="multipart/form-data" class="inline">
                    @csrf
                    <div class="flex gap-2">
                        <input type="file" name="settings_file" accept=".json" required
                               class="bg-gray-700 border border-gray-600 rounded-lg px-4 py-2 text-white file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-600 file:text-white hover:file:bg-blue-700">
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg transition-colors duration-200">
                            <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                            </svg>
                            Import configuration
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

