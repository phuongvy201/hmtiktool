<?php

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== CHECKING TEAM-ADMIN UPLOADS ===\n";

$user = App\Models\User::where('name', 'like', '%team-admin%')->first();
if ($user) {
    echo "Team-admin user: {$user->name} (ID: {$user->id})\n";
    $histories = App\Models\TikTokProductUploadHistory::where('user_id', $user->id)->get();
    echo "Histories by team-admin: " . $histories->count() . "\n";
    
    foreach ($histories as $h) {
        echo "History ID: {$h->id}, Product ID: {$h->product_id}, Status: {$h->status}\n";
    }
} else {
    echo "No team-admin user found\n";
}

// Check all users
echo "\n=== ALL USERS ===\n";
$users = App\Models\User::all();
foreach ($users as $u) {
    $histories = App\Models\TikTokProductUploadHistory::where('user_id', $u->id)->count();
    echo "User: {$u->name} (ID: {$u->id}) - Upload histories: {$histories}\n";
}

