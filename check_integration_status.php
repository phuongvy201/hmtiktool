<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== INTEGRATION STATUS CHECK ===\n";

$integrations = \App\Models\TikTokShopIntegration::with('team')->get();

foreach ($integrations as $integration) {
    echo "\n--- Integration ID: {$integration->id} ---\n";
    echo "Team: {$integration->team->name} (ID: {$integration->team_id})\n";
    echo "App Name: {$integration->app_name}\n";
    echo "App Key: {$integration->getAppKey()}\n";
    echo "Status: {$integration->status}\n";
    echo "Access Token Length: " . strlen($integration->access_token ?? '') . "\n";
    echo "Refresh Token Length: " . strlen($integration->refresh_token ?? '') . "\n";
    echo "Access Expires At: " . ($integration->access_expires_at ?? 'NULL') . "\n";
    echo "Refresh Expires At: " . ($integration->refresh_expires_at ?? 'NULL') . "\n";
    
    if ($integration->access_expires_at) {
        $hoursUntilExpiry = $integration->getHoursUntilExpiry();
        echo "Hours Until Expiry: {$hoursUntilExpiry}\n";
        echo "Needs Refresh: " . ($integration->needsTokenRefresh() ? 'YES' : 'NO') . "\n";
    }
    
    echo "Is Active: " . ($integration->isActive() ? 'YES' : 'NO') . "\n";
}
