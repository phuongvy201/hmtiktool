<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('tiktok_shop_integrations', 'market')) {
            return;
        }

        DB::transaction(function () {
            $integrations = DB::table('tiktok_shop_integrations')
                ->select('id', 'market', 'additional_data')
                ->whereNotNull('market')
                ->get();

            foreach ($integrations as $integration) {
                $additionalData = $integration->additional_data
                    ? json_decode($integration->additional_data, true)
                    : [];

                if (!is_array($additionalData)) {
                    $additionalData = [];
                }

                $additionalData['market'] = strtoupper($integration->market);

                DB::table('tiktok_shop_integrations')
                    ->where('id', $integration->id)
                    ->update([
                        'additional_data' => json_encode($additionalData),
                    ]);
            }
        });

        Schema::table('tiktok_shop_integrations', function (Blueprint $table) {
            $table->dropColumn('market');
        });
    }

    public function down(): void
    {
        if (Schema::hasColumn('tiktok_shop_integrations', 'market')) {
            return;
        }

        Schema::table('tiktok_shop_integrations', function (Blueprint $table) {
            $table->string('market', 10)->default('UK')->after('team_id');
        });

        DB::transaction(function () {
            $integrations = DB::table('tiktok_shop_integrations')
                ->select('id', 'additional_data')
                ->get();

            foreach ($integrations as $integration) {
                $additionalData = $integration->additional_data
                    ? json_decode($integration->additional_data, true)
                    : [];

                if (!is_array($additionalData)) {
                    $additionalData = [];
                }

                $market = strtoupper($additionalData['market'] ?? 'UK');

                DB::table('tiktok_shop_integrations')
                    ->where('id', $integration->id)
                    ->update([
                        'market' => $market,
                    ]);
            }
        });
    }
};
