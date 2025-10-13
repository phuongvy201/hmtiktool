<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\TikTokSignatureService;

class TestHmacComparisonCommand extends Command
{
    protected $signature = 'test:hmac-comparison';
    protected $description = 'Test HMAC generation comparison with Go example';

    public function handle()
    {
        $this->info("=== TESTING HMAC GENERATION COMPARISON ===");

        // Test data
        $input = "test_input_string";
        $secret = "test_secret";

        $this->info("Input: {$input}");
        $this->info("Secret: {$secret}");

        // Method 1: Current PHP method (hash_hmac)
        $this->info("\n=== METHOD 1: hash_hmac ===");
        $signature1 = hash_hmac('sha256', $input, $secret, true);
        $hex1 = bin2hex($signature1);
        $this->info("Binary signature: " . base64_encode($signature1));
        $this->info("Hex signature: {$hex1}");

        // Method 2: Manual HMAC (like Go)
        $this->info("\n=== METHOD 2: Manual HMAC (Go style) ===");
        $signature2 = hash_hmac('sha256', $input, $secret, false);
        $this->info("Hex signature: {$signature2}");

        // Method 3: Using hash_init (more explicit)
        $this->info("\n=== METHOD 3: hash_init (explicit) ===");
        $context = hash_init('sha256', HASH_HMAC, $secret);
        hash_update($context, $input);
        $signature3 = hash_final($context, true);
        $hex3 = bin2hex($signature3);
        $this->info("Binary signature: " . base64_encode($signature3));
        $this->info("Hex signature: {$hex3}");

        // Comparison
        $this->info("\n=== COMPARISON ===");
        $this->info("Method 1 vs Method 2: " . ($hex1 === $signature2 ? 'SAME' : 'DIFFERENT'));
        $this->info("Method 1 vs Method 3: " . ($hex1 === $hex3 ? 'SAME' : 'DIFFERENT'));
        $this->info("Method 2 vs Method 3: " . ($signature2 === $hex3 ? 'SAME' : 'DIFFERENT'));

        // Test with TikTok example
        $this->info("\n=== TIKTOK EXAMPLE TEST ===");

        $tiktokInput = "/authorization/202309/shopsapp_key29a39dtimestamp1623812664";
        $tiktokSecret = "e59af819cc";
        $tiktokWrapped = $tiktokSecret . $tiktokInput . $tiktokSecret;

        $this->info("TikTok Input: {$tiktokInput}");
        $this->info("TikTok Secret: {$tiktokSecret}");
        $this->info("TikTok Wrapped: {$tiktokWrapped}");

        // Generate signature
        $tiktokSignature = hash_hmac('sha256', $tiktokWrapped, $tiktokSecret, false);
        $this->info("TikTok Signature: {$tiktokSignature}");

        $expected = "b596b73e0cc6de07ac26f036364178ab16b0a907af13d43f0a0cd2345f582dc8";
        $this->info("Expected: {$expected}");
        $this->info("Match: " . ($tiktokSignature === $expected ? 'YES' : 'NO'));

        // Test current TikTokSignatureService
        $this->info("\n=== CURRENT SERVICE TEST ===");

        $currentSignature = TikTokSignatureService::generateCustomSignature(
            '29a39d',
            'e59af819cc',
            '/authorization/202309/shops',
            ['app_key' => '29a39d', 'timestamp' => '1623812664'],
            [],
            'application/json'
        );

        $this->info("Current Service Signature: {$currentSignature}");
        $this->info("Expected: {$expected}");
        $this->info("Match: " . ($currentSignature === $expected ? 'YES' : 'NO'));

        $this->info("\n=== COMPLETED ===");
    }
}
