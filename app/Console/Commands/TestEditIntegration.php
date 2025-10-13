<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\TikTokShopIntegration;
use App\Models\Team;

class TestEditIntegration extends Command
{
    protected $signature = 'tiktok:test-edit-integration {integration_id}';
    protected $description = 'Test edit integration functionality';

    public function handle()
    {
        $integrationId = $this->argument('integration_id');

        $this->info("🧪 Testing edit integration functionality for ID: {$integrationId}");

        // Find integration
        $integration = TikTokShopIntegration::find($integrationId);
        if (!$integration) {
            $this->error("❌ Integration not found with ID: {$integrationId}");
            return 1;
        }

        $this->info("✅ Found integration:");
        $this->table(
            ['Field', 'Value'],
            [
                ['ID', $integration->id],
                ['Team ID', $integration->team_id],
                ['Name', $integration->name ?? 'NULL'],
                ['Description', $integration->description ?? 'NULL'],
                ['Status', $integration->status],
                ['Created At', $integration->created_at],
                ['Updated At', $integration->updated_at],
            ]
        );

        // Test update
        $this->info("🔄 Testing update functionality...");

        $updateData = [
            'name' => 'Test Integration ' . date('Y-m-d H:i:s'),
            'description' => 'This is a test integration updated at ' . now()->format('Y-m-d H:i:s'),
        ];

        try {
            $integration->update($updateData);
            $this->info("✅ Update successful!");

            // Reload and show updated data
            $integration->refresh();
            $this->info("📊 Updated integration data:");
            $this->table(
                ['Field', 'Value'],
                [
                    ['ID', $integration->id],
                    ['Name', $integration->name],
                    ['Description', $integration->description],
                    ['Updated At', $integration->updated_at],
                ]
            );
        } catch (\Exception $e) {
            $this->error("❌ Update failed: " . $e->getMessage());
            return 1;
        }

        // Test delete
        $this->info("🗑️ Testing delete functionality...");

        if ($this->confirm('Do you want to delete this integration? This action cannot be undone.')) {
            try {
                // Delete related shops first
                $shopCount = $integration->shops()->count();
                if ($shopCount > 0) {
                    $this->info("📦 Deleting {$shopCount} related shops...");
                    $integration->shops()->delete();
                }

                // Delete integration
                $integration->delete();
                $this->info("✅ Integration deleted successfully!");
            } catch (\Exception $e) {
                $this->error("❌ Delete failed: " . $e->getMessage());
                return 1;
            }
        } else {
            $this->info("ℹ️ Delete cancelled by user.");
        }

        $this->info("🎉 Test completed successfully!");
        return 0;
    }
}
