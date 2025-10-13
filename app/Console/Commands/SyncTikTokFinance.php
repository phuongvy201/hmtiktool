<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\TikTokShop;
use App\Models\TikTokPayment;
use App\Services\TikTokFinanceService;
use Illuminate\Support\Facades\Log;

class SyncTikTokFinance extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tiktok:sync-finance 
                            {--shop= : Specific shop ID to sync}
                            {--days=7 : Number of days to sync (default: 7)}
                            {--force : Force sync even if data exists}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync TikTok finance/payment data from API to database';

    private TikTokFinanceService $financeService;

    public function __construct(TikTokFinanceService $financeService)
    {
        parent::__construct();
        $this->financeService = $financeService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🚀 Starting TikTok Finance Sync...');

        $shopId = $this->option('shop');
        $days = (int) $this->option('days');
        $force = $this->option('force');

        // Get shops to sync
        $shops = $this->getShopsToSync($shopId);

        if ($shops->isEmpty()) {
            $this->error('❌ No shops found to sync');
            return 1;
        }

        $this->info("📊 Found {$shops->count()} shop(s) to sync");
        $this->info("📅 Syncing data for the last {$days} days");

        $totalSynced = 0;
        $totalErrors = 0;

        foreach ($shops as $shop) {
            $this->info("\n🏪 Syncing shop: {$shop->shop_name}");

            try {
                $result = $this->syncShopFinance($shop, $days, $force);
                $totalSynced += $result['synced'];
                $totalErrors += $result['errors'];

                $this->info("✅ Synced {$result['synced']} payments, {$result['errors']} errors");
            } catch (\Exception $e) {
                $this->error("❌ Error syncing shop {$shop->shop_name}: " . $e->getMessage());
                $totalErrors++;
                Log::error('TikTok Finance Sync Error', [
                    'shop_id' => $shop->id,
                    'shop_name' => $shop->shop_name,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }
        }

        $this->info("\n🎉 Sync completed!");
        $this->info("📈 Total payments synced: {$totalSynced}");
        $this->info("⚠️  Total errors: {$totalErrors}");

        return 0;
    }

    /**
     * Get shops to sync
     */
    private function getShopsToSync(?string $shopId)
    {
        $query = TikTokShop::with('integration')
            ->whereHas('integration', function ($q) {
                $q->where('status', 'active');
            });

        if ($shopId) {
            $query->where('id', $shopId);
        }

        return $query->get();
    }

    /**
     * Sync finance data for a specific shop
     */
    private function syncShopFinance(TikTokShop $shop, int $days, bool $force): array
    {
        $synced = 0;
        $errors = 0;

        // Calculate date range
        $endDate = date('Y-m-d');
        $startDate = date('Y-m-d', strtotime("-{$days} days"));

        $this->line("   📅 Date range: {$startDate} to {$endDate}");

        // Prepare filters
        $filters = [
            'date_from' => $startDate,
            'date_to' => $endDate,
            'page_size' => 100, // Get more data per request
            'sort_order' => 'DESC'
        ];

        $pageToken = null;
        $hasMore = true;

        while ($hasMore) {
            if ($pageToken) {
                $filters['page_token'] = $pageToken;
            }

            // Get payments from TikTok API
            $result = $this->financeService->getPayments($shop, $filters);

            if (!$result['success']) {
                $this->error("   ❌ API Error: {$result['message']}");
                $errors++;
                break;
            }

            $payments = $result['data']['payments'] ?? [];
            $hasMore = $result['data']['has_more'] ?? false;
            $pageToken = $result['data']['next_page_token'] ?? null;

            $this->line("   📦 Processing " . count($payments) . " payments...");

            // Save payments to database
            foreach ($payments as $paymentData) {
                try {
                    $saved = $this->savePaymentToDatabase($shop, $paymentData, $force);
                    if ($saved) {
                        $synced++;
                    }
                } catch (\Exception $e) {
                    $this->error("   ❌ Error saving payment {$paymentData['id']}: " . $e->getMessage());
                    $errors++;
                    Log::error('Error saving TikTok payment', [
                        'shop_id' => $shop->id,
                        'payment_id' => $paymentData['id'] ?? 'unknown',
                        'error' => $e->getMessage()
                    ]);
                }
            }

            // If no more data, break
            if (!$hasMore || empty($pageToken)) {
                break;
            }
        }

        return [
            'synced' => $synced,
            'errors' => $errors
        ];
    }

    /**
     * Save payment data to database
     */
    private function savePaymentToDatabase(TikTokShop $shop, array $paymentData, bool $force): bool
    {
        $paymentId = $paymentData['id'] ?? null;

        if (!$paymentId) {
            throw new \Exception('Payment ID is missing');
        }

        // Check if payment already exists
        $existingPayment = TikTokPayment::where('payment_id', $paymentId)->first();

        if ($existingPayment && !$force) {
            // Update last_synced_at
            $existingPayment->update(['last_synced_at' => now()]);
            return false; // Not a new sync
        }

        // Prepare data for database
        $dbData = [
            'payment_id' => $paymentId,
            'tiktok_shop_id' => $shop->id,
            'shop_name' => $shop->shop_name,
            'shop_profile' => $shop->shop_profile,
            'create_time' => $paymentData['create_time'] ?? null,
            'paid_time' => $paymentData['paid_time'] ?? null,
            'status' => $paymentData['status'] ?? null,
            'amount_value' => $paymentData['amount']['value'] ?? 0,
            'amount_currency' => $paymentData['amount']['currency'] ?? 'GBP',
            'settlement_amount_value' => $paymentData['settlement_amount']['value'] ?? 0,
            'settlement_amount_currency' => $paymentData['settlement_amount']['currency'] ?? 'GBP',
            'reserve_amount_value' => $paymentData['reserve_amount']['value'] ?? 0,
            'reserve_amount_currency' => $paymentData['reserve_amount']['currency'] ?? 'GBP',
            'payment_amount_before_exchange_value' => $paymentData['payment_amount_before_exchange']['value'] ?? 0,
            'payment_amount_before_exchange_currency' => $paymentData['payment_amount_before_exchange']['currency'] ?? 'GBP',
            'exchange_rate' => $paymentData['exchange_rate'] ?? 1.000000,
            'bank_account' => $paymentData['bank_account'] ?? null,
            'payment_data' => $paymentData,
            'last_synced_at' => now(),
        ];

        if ($existingPayment) {
            $existingPayment->update($dbData);
        } else {
            TikTokPayment::create($dbData);
        }

        return true; // New sync
    }
}
