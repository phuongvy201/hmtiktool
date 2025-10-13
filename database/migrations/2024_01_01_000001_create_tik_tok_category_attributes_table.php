<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tik_tok_category_attributes', function (Blueprint $table) {
            $table->id();
            $table->string('category_id'); // TikTok category ID
            $table->string('attribute_id'); // TikTok attribute ID
            $table->string('name'); // Attribute name (e.g., "Occasion")
            $table->enum('type', ['PRODUCT_PROPERTY', 'SALES_PROPERTY']); // Attribute type
            $table->boolean('is_required')->default(false); // Is required for this category
            $table->boolean('is_multiple_selection')->default(false); // Can select multiple values
            $table->boolean('is_customizable')->default(false); // Can add custom values
            $table->string('value_data_format')->nullable(); // Data format (e.g., "POSITIVE_INT_OR_DECIMAL")
            $table->json('values')->nullable(); // Available values for this attribute
            $table->json('requirement_conditions')->nullable(); // Conditional requirements
            $table->json('attribute_data')->nullable(); // Full attribute data from API
            $table->timestamp('last_synced_at')->nullable(); // When this attribute was last synced
            $table->timestamps();

            // Indexes
            $table->index(['category_id', 'attribute_id']);
            $table->index('type');
            $table->index('is_required');
            $table->index('last_synced_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tik_tok_category_attributes');
    }
};
