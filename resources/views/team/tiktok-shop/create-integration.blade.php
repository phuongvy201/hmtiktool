@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-slate-900 text-gray-100">
    <div class="container mx-auto px-4 py-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h1 class="text-3xl font-bold text-white mb-2">Create New TikTok Shop Integration</h1>
                    <p class="text-gray-400">Add TikTok Shop integration for team: <span class="text-blue-400 font-medium">{{ $team->name }}</span></p>
                </div>
                <a href="{{ route('team.tiktok-shop.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg font-medium transition-colors duration-200">
                    Back
                </a>
            </div>
        </div>

        <!-- Success/Error Messages -->
        @if(session('success'))
            <div class="bg-green-500/20 border border-green-500/50 text-green-400 px-4 py-3 rounded-lg mb-6">
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="bg-red-500/20 border border-red-500/50 text-red-400 px-4 py-3 rounded-lg mb-6">
                <ul class="list-disc list-inside">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Create Integration Form -->
        <div class="bg-gray-800 rounded-xl border border-gray-700 p-6 mb-8">
            <h3 class="text-lg font-semibold text-white mb-4">Create a new integration</h3>
            
            <div class="text-center mb-6">
                <div class="w-20 h-20 bg-blue-500/20 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-10 h-10 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-white mb-2">Create TikTok Shop Integration</h3>
                <p class="text-gray-400">The system will use shared App Key/Secret configured by system admin</p>
            </div>
            
            <form action="{{ route('team.tiktok-shop.store-integration') }}" method="POST">
                @csrf
                
                <!-- Integration Name -->
                <div class="mb-6">
                    <label for="name" class="block text-sm font-medium text-gray-300 mb-2">
                        Integration name <span class="text-red-400">*</span>
                    </label>
                    <input type="text" 
                           id="name" 
                           name="name" 
                           value="{{ old('name') }}" 
                           required 
                           class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-2 text-white placeholder-gray-400 focus:outline-none focus:border-blue-500 @error('name') border-red-500 @enderror"
                           placeholder="e.g., Main TikTok Shop, UK Account, etc.">
                    <p class="text-gray-400 text-xs mt-1">Enter a name to easily recognize this integration</p>
                    @error('name')
                        <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Market Selection -->
                <div class="mb-6">
                    <label for="market" class="block text-sm font-medium text-gray-300 mb-2">
                        Select market <span class="text-red-400">*</span>
                    </label>
                    <select name="market" id="market" required class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">-- Select market --</option>
                        <option value="US" {{ old('market') == 'US' ? 'selected' : '' }}>🇺🇸 United States (US)</option>
                        <option value="UK" {{ old('market') == 'UK' ? 'selected' : '' }}>🇬🇧 United Kingdom (UK)</option>
                    </select>
                    @error('market')
                        <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Dynamic App Key Display -->
                <div class="bg-blue-500/10 border border-blue-500/20 rounded-lg p-4 mb-6">
                    <h4 class="text-sm font-medium text-blue-400 mb-2">Integration info:</h4>
                    <ul class="text-sm text-gray-300 space-y-1" id="integration-info">
                        <li>• Market: <span class="text-yellow-400">Not selected</span></li>
                        <li>• App Key: <span class="text-green-400" id="app-key-display">Please select market</span></li>
                        <li>• App Secret: <span class="text-green-400">[Configured by system admin]</span></li>
                        <li>• Status: <span class="text-yellow-400">Waiting for connection</span></li>
                    </ul>
                </div>

                <div class="flex justify-center">
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-8 py-3 rounded-lg font-medium transition-colors duration-200 flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        Create integration
                    </button>
                </div>
            </form>

            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const marketSelect = document.getElementById('market');
                    const appKeyDisplay = document.getElementById('app-key-display');
                    const integrationInfo = document.getElementById('integration-info');
                    
                    const marketConfig = {
                        'US': {
                            appKey: @json(config('tiktok-shop.markets.US.app_key') ?? config('tiktok-shop.app_key') ?? env('TIKTOK_SHOP_US_APP_KEY', 'Chưa cấu hình')),
                            name: 'Hoa Kỳ (US)'
                        },
                        'UK': {
                            appKey: @json(config('tiktok-shop.markets.UK.app_key') ?? config('tiktok-shop.app_key') ?? env('TIKTOK_SHOP_UK_APP_KEY', 'Chưa cấu hình')),
                            name: 'Vương quốc Anh (UK)'
                        }
                    };
                    
                    marketSelect.addEventListener('change', function() {
                        const selectedMarket = this.value;
                        
                        if (selectedMarket && marketConfig[selectedMarket]) {
                            const config = marketConfig[selectedMarket];
                            appKeyDisplay.textContent = config.appKey || 'Chưa cấu hình';
                            integrationInfo.querySelector('li:first-child').innerHTML = 
                                '• Thị trường: <span class="text-yellow-400">' + config.name + '</span>';
                        } else {
                            appKeyDisplay.textContent = 'Vui lòng chọn thị trường';
                            integrationInfo.querySelector('li:first-child').innerHTML = 
                                '• Thị trường: <span class="text-yellow-400">Chưa chọn</span>';
                        }
                    });
                });
            </script>
        </div>

        <!-- Authorization Code Generator -->
        <div class="bg-gray-800 rounded-xl border border-gray-700 p-6 mb-8">
            <h3 class="text-lg font-semibold text-white mb-4 flex items-center">
                <i class="fas fa-link mr-2 text-green-400"></i>
                Tạo Link Authorization cho Khách hàng
            </h3>
            
                <div class="bg-green-500/10 border border-green-500/20 rounded-lg p-4 mb-6">
                    <h4 class="text-sm font-medium text-green-400 mb-2">How to use:</h4>
                <ol class="text-sm text-gray-300 space-y-1 list-decimal list-inside">
                        <li>Create the TikTok Shop integration first</li>
                        <li>Click "Generate Authorization Link" to create a link for the customer</li>
                        <li>Send the link to the customer so they can click and get the authorization code</li>
                        <li>The customer will see the authorization code to copy</li>
                </ol>
            </div>

            <div class="text-center">
                <a href="{{ route('team.tiktok-shop.generate-auth-link') }}" class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg font-medium transition-colors duration-200 inline-flex items-center">
                    <i class="fas fa-external-link-alt mr-2"></i>
                    Generate Authorization Link
                </a>
            </div>
        </div>

        <!-- Instructions -->
        <div class="bg-gray-800 rounded-xl border border-gray-700 p-6">
            <h3 class="text-lg font-semibold text-white mb-4">How to get App Key and App Secret</h3>
            
            <div class="space-y-4 text-gray-300">
                <div>
                    <h4 class="text-md font-semibold text-blue-400 mb-2">Step 1: Go to TikTok Partner Center</h4>
                    <ol class="list-decimal list-inside space-y-1 text-sm ml-4">
                        <li>Log in to <a href="https://partner.tiktok.com" target="_blank" class="text-blue-400 hover:underline">TikTok Partner Center</a></li>
                        <li>Select "App & Service" from the left menu</li>
                        <li>Click your TikTok Shop Application</li>
                    </ol>
                </div>
                
                <div>
                    <h4 class="text-md font-semibold text-green-400 mb-2">Step 2: Get app info</h4>
                    <ol class="list-decimal list-inside space-y-1 text-sm ml-4">
                        <li>Scroll down to "Developing"</li>
                        <li>Copy "App Key" and "App Secret"</li>
                        <li>Ensure the App is approved or under review</li>
                    </ol>
                </div>
                
                <div>
                    <h4 class="text-md font-semibold text-yellow-400 mb-2">Step 3: Configure the App</h4>
                    <ol class="list-decimal list-inside space-y-1 text-sm ml-4">
                        <li>Add redirect URI: <code class="bg-gray-700 px-2 py-1 rounded">{{ route('team.tiktok-shop.callback') }}</code></li>
                        <li>Ensure the App has required scopes</li>
                        <li>Save configuration</li>
                    </ol>
                </div>
            </div>

            <div class="mt-6 p-4 bg-yellow-500/10 border border-yellow-500/20 rounded-lg">
                <h4 class="text-md font-semibold text-yellow-400 mb-2">Important notes:</h4>
                <ul class="list-disc list-inside space-y-1 text-gray-300 text-sm">
                    <li>App Key and App Secret are sensitive. Do not share them.</li>
                    <li>Each team can create multiple integrations to manage multiple TikTok accounts.</li>
                    <li>After creating an integration, connect it to start using.</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
