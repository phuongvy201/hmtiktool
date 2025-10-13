<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ProductTemplateVariant;

class CheckVariantDataCommand extends Command
{
    protected $signature = 'check:variant-data {limit=5}';
    protected $description = 'Check variant_data structure';

    public function handle()
    {
        $limit = $this->argument('limit');
        $variants = ProductTemplateVariant::take($limit)->get();

        foreach ($variants as $variant) {
            $this->info("Variant ID: {$variant->id}");
            $this->info("SKU: {$variant->sku}");
            $this->info("Variant Data: " . json_encode($variant->variant_data, JSON_PRETTY_PRINT));
            $this->line('---');
        }
    }
}
