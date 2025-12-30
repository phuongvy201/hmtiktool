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
                    <h1 class="text-3xl font-bold text-white mb-2">Debug OAuth Flow</h1>
                    <p class="text-gray-400">Detailed information about the OAuth flow for team: <span class="text-blue-400 font-medium">{{ $integration->team->name }}</span></p>
                </div>
            </div>
        </div>

        <!-- OAuth Flow Information -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            <!-- Authorization URL -->
            <div class="bg-gray-800 rounded-xl border border-gray-700 p-6">
                <h3 class="text-lg font-semibold text-white mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path>
                    </svg>
                    Authorization URL
                </h3>
                <div class="bg-gray-700 rounded-lg p-4">
                    <p class="text-sm text-gray-300 mb-2">URL for customer authorization:</p>
                    <div class="flex items-center">
                        <input type="text" value="{{ $oauthInfo['authorization_url'] }}" readonly 
                               class="flex-1 bg-gray-600 border border-gray-500 rounded-lg px-3 py-2 text-white text-sm">
                        <button onclick="copyToClipboard('{{ $oauthInfo['authorization_url'] }}')" 
                                class="ml-2 bg-blue-600 hover:bg-blue-700 text-white px-3 py-2 rounded-lg text-sm transition-colors duration-200">
                            Copy
                        </button>
                    </div>
                </div>
            </div>

            <!-- Callback URL -->
            <div class="bg-gray-800 rounded-xl border border-gray-700 p-6">
                <h3 class="text-lg font-semibold text-white mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    Callback URL
                </h3>
                <div class="bg-gray-700 rounded-lg p-4">
                    <p class="text-sm text-gray-300 mb-2">URL TikTok will redirect to:</p>
                    <div class="flex items-center">
                        <input type="text" value="{{ $oauthInfo['callback_url'] }}" readonly 
                               class="flex-1 bg-gray-600 border border-gray-500 rounded-lg px-3 py-2 text-white text-sm">
                        <button onclick="copyToClipboard('{{ $oauthInfo['callback_url'] }}')" 
                                class="ml-2 bg-green-600 hover:bg-green-700 text-white px-3 py-2 rounded-lg text-sm transition-colors duration-200">
                            Copy
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- API Endpoint Information -->
        <div class="bg-gray-800 rounded-xl border border-gray-700 p-6 mb-8">
            <h3 class="text-lg font-semibold text-white mb-4 flex items-center">
                <svg class="w-5 h-5 mr-2 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
                API endpoint & parameters
            </h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <h4 class="text-md font-semibold text-purple-400 mb-3">API Endpoint:</h4>
                    <div class="bg-gray-700 rounded-lg p-4">
                        <code class="text-green-400 text-sm">{{ $oauthInfo['api_endpoint'] }}</code>
                    </div>
                </div>
                
                <div>
                    <h4 class="text-md font-semibold text-purple-400 mb-3">Required Parameters:</h4>
                    <div class="bg-gray-700 rounded-lg p-4">
                        <ul class="space-y-2 text-sm">
                            @foreach($oauthInfo['required_params'] as $key => $value)
                                <li class="flex justify-between">
                                    <span class="text-gray-300">{{ $key }}:</span>
                                    <span class="text-blue-400 font-mono">{{ $value }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>

            <div class="mt-6">
                <h4 class="text-md font-semibold text-purple-400 mb-3">Example Request:</h4>
                <div class="bg-gray-700 rounded-lg p-4">
                    <code class="text-yellow-400 text-sm break-all">{{ $oauthInfo['example_request'] }}</code>
                </div>
            </div>
        </div>

        <!-- OAuth Flow Steps -->
        <div class="bg-gray-800 rounded-xl border border-gray-700 p-6 mb-8">
            <h3 class="text-lg font-semibold text-white mb-4 flex items-center">
                <svg class="w-5 h-5 mr-2 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                </svg>
                OAuth flow
            </h3>
            
            <div class="space-y-4">
                @foreach($oauthInfo['flow_steps'] as $index => $step)
                    <div class="flex items-start">
                        <div class="w-8 h-8 bg-blue-500/20 rounded-full flex items-center justify-center mr-4 mt-1">
                            <span class="text-blue-400 text-sm font-medium">{{ $index + 1 }}</span>
                        </div>
                        <div class="flex-1">
                            <p class="text-gray-300">{{ $step }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

                 <!-- Validation Information -->
         <div class="bg-gray-800 rounded-xl border border-gray-700 p-6 mb-8">
             <h3 class="text-lg font-semibold text-white mb-4 flex items-center">
                 <svg class="w-5 h-5 mr-2 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                     <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                 </svg>
                 Validation information
             </h3>
             
             <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                 <div>
                     <h4 class="text-md font-semibold text-blue-400 mb-3">Simple Validation:</h4>
                     <div class="bg-blue-500/10 border border-blue-500/20 rounded-lg p-4">
                         <ul class="list-disc list-inside space-y-1 text-gray-300 text-sm">
                             <li>Check App Key and App Secret are not empty</li>
                             <li>Check minimum length of 10 characters</li>
                             <li>Check format App Key (letters, numbers, hyphens, underscores)</li>
                             <li>Do not call API, only check format</li>
                         </ul>
                     </div>
                 </div>
                 
                 <div>
                     <h4 class="text-md font-semibold text-orange-400 mb-3">API Validation:</h4>
                     <div class="bg-orange-500/10 border border-orange-500/20 rounded-lg p-4">
                         <ul class="list-disc list-inside space-y-1 text-gray-300 text-sm">
                                <li>Call TikTok Shop API to test</li>
                             <li>Send request with auth_code test</li>
                             <li>Analyze response to determine validity</li>
                             <li>May encounter network errors or API rate limit</li>    
                         </ul>
                     </div>
                 </div>
             </div>
         </div>

         <!-- Error Handling -->
         <div class="bg-gray-800 rounded-xl border border-gray-700 p-6">
             <h3 class="text-lg font-semibold text-white mb-4 flex items-center">
                 <svg class="w-5 h-5 mr-2 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                     <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                 </svg>
                 Error handling
             </h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <h4 class="text-md font-semibold text-red-400 mb-3">Error 36004004 - Invalid Auth Code:</h4>
                    <div class="bg-red-500/10 border border-red-500/20 rounded-lg p-4">
                        <p class="text-gray-300 text-sm mb-2"><strong>Reason:</strong></p>
                        <ul class="list-disc list-inside space-y-1 text-gray-300 text-sm">
                            <li>Auth code has been used or expired</li>
                            <li>Auth code is not in the correct format</li>
                            <li>App key/secret does not match the auth code</li>
                        </ul>
                        <p class="text-gray-300 text-sm mt-2"><strong>Solution:</strong> Request the customer to re-perform the authorization process</p>
                    </div>
                </div>
                
                <div>
                    <h4 class="text-md font-semibold text-red-400 mb-3">Other errors:</h4>
                    <div class="bg-red-500/10 border border-red-500/20 rounded-lg p-4">
                        <ul class="list-disc list-inside space-y-1 text-gray-300 text-sm">
                            <li><strong>10004:</strong> App key/secret is not valid</li>
                            <li><strong>10008:</strong> Access token expired</li>
                            <li><strong>10010:</strong> Refresh token expired</li>
                            <li><strong>10012:</strong> No access permission</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="flex items-center justify-end space-x-4 pt-6">
            <a href="{{ route('tiktok-shop.index') }}" 
               class="px-6 py-2 border border-gray-600 text-gray-300 rounded-lg hover:bg-gray-700 transition-colors duration-200">
                Back
            </a>
            <a href="{{ route('tiktok-shop.edit', $integration) }}" 
               class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition-colors duration-200">
                        Edit integration
            </a>
        </div>
    </div>
</div>

<script>
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(function() {
        // Show success message
        const button = event.target;
        const originalText = button.textContent;
        button.textContent = 'Copied!';
        button.classList.add('bg-green-600');
        button.classList.remove('bg-blue-600', 'hover:bg-blue-700');
        
        setTimeout(function() {
            button.textContent = originalText;
            button.classList.remove('bg-green-600');
            button.classList.add('bg-blue-600', 'hover:bg-blue-700');
        }, 2000);
    });
}
</script>
@endsection
