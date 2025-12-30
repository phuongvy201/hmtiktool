@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-slate-900 text-gray-100">
    <div class="container mx-auto px-4 py-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h1 class="text-3xl font-bold text-white mb-2">Link Authorization for Customer</h1>
                    <p class="text-gray-400">Create a link for customers to get authorization code from TikTok Shop</p>
                </div>
                <a href="{{ route('team.tiktok-shop.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg font-medium transition-colors duration-200">
                    Back
                </a>
            </div>
        </div>

        <!-- Authorization Link -->
        <div class="bg-gray-800 rounded-xl border border-gray-700 p-6 mb-8">
            <h3 class="text-lg font-semibold text-white mb-4 flex items-center">
                <i class="fas fa-link mr-2 text-green-400"></i>
                Link Authorization
            </h3>
            
            <div class="bg-green-500/10 border border-green-500/20 rounded-lg p-4 mb-6">
                <h4 class="text-sm font-medium text-green-400 mb-2">Usage guide:</h4>
                <ol class="text-sm text-gray-300 space-y-1 list-decimal list-inside">
                    <li>Copy link below and send to customers</li>
                    <li>Customers click on the link and login to TikTok Shop</li>
                    <li>Customers agree to grant permission to the application</li>
                    <li>Customers will see authorization code to copy</li>
                    <li>Customers send authorization code to you</li>
                </ol>
            </div>

            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Authorization Link:</label>
                    <div class="flex items-center space-x-2">
                        <input type="text" 
                               value="{{ $authUrl }}" 
                               readonly 
                               class="flex-1 bg-gray-700 border border-gray-600 rounded-lg px-3 py-2 text-white text-sm"
                               id="authLink">
                        <button onclick="copyToClipboard('authLink')" 
                                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors duration-200">
                            <i class="fas fa-copy mr-1"></i>Copy
                        </button>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Authentication token:</label>
                    <div class="flex items-center space-x-2">
                        <input type="text" 
                               value="{{ $authToken }}" 
                               readonly 
                               class="flex-1 bg-gray-700 border border-gray-600 rounded-lg px-3 py-2 text-white text-sm font-mono"
                               id="authToken">
                        <button onclick="copyToClipboard('authToken')" 
                                class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors duration-200">
                            <i class="fas fa-copy mr-1"></i>Copy
                        </button>
                    </div>
                </div>
            </div>

            <div class="mt-6 p-4 bg-yellow-500/10 border border-yellow-500/20 rounded-lg">
                <h4 class="text-sm font-medium text-yellow-400 mb-2">Important notes:</h4>
                <ul class="text-sm text-gray-300 space-y-1 list-disc list-inside">
                    <li>This link has a 1 hour expiration, after which a new link needs to be created</li>
                    <li>Each link can only be used once</li>
                    <li>Customers need to have a valid TikTok Shop account</li>
                    <li>Authorization code is only valid for a short period</li>
                </ul>
            </div>
        </div>

        <!-- Test Link -->
        <div class="bg-gray-800 rounded-xl border border-gray-700 p-6">
            <h3 class="text-lg font-semibold text-white mb-4 flex items-center">
                <i class="fas fa-flask mr-2 text-blue-400"></i>
                Test Link
            </h3>
            
            <p class="text-gray-400 mb-4">Click on the link below to test the authorization process:</p>
            
            <div class="text-center">
                <a href="{{ $authUrl }}" 
                   target="_blank"
                   class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg font-medium transition-colors duration-200 inline-flex items-center">
                    <i class="fas fa-external-link-alt mr-2"></i>
                    Test Authorization Link
                </a>
            </div>
        </div>
    </div>
</div>

<script>
function copyToClipboard(elementId) {
    const element = document.getElementById(elementId);
    element.select();
    element.setSelectionRange(0, 99999); // For mobile devices
    
    try {
        document.execCommand('copy');
        
        // Show success message
        const button = event.target.closest('button');
        const originalText = button.innerHTML;
        button.innerHTML = '<i class="fas fa-check mr-1"></i>Copied!';
        button.classList.add('bg-green-600');
        button.classList.remove('bg-blue-600', 'hover:bg-blue-700');
        
        setTimeout(() => {
            button.innerHTML = originalText;
            button.classList.remove('bg-green-600');
            button.classList.add('bg-blue-600', 'hover:bg-blue-700');
        }, 2000);
        
    } catch (err) {
        console.error('Failed to copy: ', err);
        alert('Cannot
    }
}
</script>
@endsection
