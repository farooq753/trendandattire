<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('countries', function (Blueprint $table) {
            $table->char('code', 2)->primary();
            $table->string('name');
            $table->string('phone_prefix', 8)->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('address_requires_region')->default(false);
            $table->boolean('address_requires_postal')->default(true);
            $table->string('region_label', 64)->default('Region');
            $table->timestamps();
        });

        Schema::create('currencies', function (Blueprint $table) {
            $table->char('code', 3)->primary();
            $table->string('name');
            $table->string('symbol', 8);
            $table->unsignedTinyInteger('decimal_places')->default(2);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });

        Schema::create('exchange_rates', function (Blueprint $table) {
            $table->id();
            $table->char('base_code', 3);
            $table->char('quote_code', 3);
            $table->decimal('rate', 18, 8);
            $table->string('source', 32)->default('manual');
            $table->timestamp('effective_at')->useCurrent();
            $table->timestamps();
            $table->index(['base_code', 'quote_code', 'effective_at']);
            $table->foreign('base_code')->references('code')->on('currencies');
            $table->foreign('quote_code')->references('code')->on('currencies');
        });

        Schema::create('shipping_zones', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->boolean('is_international')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('shipping_zone_locations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shipping_zone_id')->constrained()->cascadeOnDelete();
            $table->char('country_code', 2);
            $table->string('region')->nullable();
            $table->timestamps();
            $table->foreign('country_code')->references('code')->on('countries');
            $table->index(['country_code', 'region']);
        });

        Schema::create('shipping_methods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shipping_zone_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('code');
            $table->string('rate_type', 32);
            $table->decimal('amount', 14, 4)->default(0);
            $table->decimal('per_kg', 14, 4)->default(0);
            $table->decimal('free_threshold', 14, 4)->nullable();
            $table->decimal('min_order', 14, 4)->nullable();
            $table->decimal('max_order', 14, 4)->nullable();
            $table->unsignedInteger('min_weight_grams')->nullable();
            $table->unsignedInteger('max_weight_grams')->nullable();
            $table->unsignedSmallInteger('processing_min_days')->default(1);
            $table->unsignedSmallInteger('processing_max_days')->default(5);
            $table->boolean('allows_cod')->default(false);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->unique(['shipping_zone_id', 'code']);
        });

        Schema::create('tax_rules', function (Blueprint $table) {
            $table->id();
            $table->char('country_code', 2);
            $table->string('region')->nullable();
            $table->string('name');
            $table->decimal('rate_percent', 8, 4);
            $table->string('price_mode', 16)->default('exclusive');
            $table->string('applies_to', 16)->default('products');
            $table->string('product_fulfillment', 16)->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('priority')->default(0);
            $table->timestamps();
            $table->foreign('country_code')->references('code')->on('countries');
            $table->index(['country_code', 'region', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tax_rules');
        Schema::dropIfExists('shipping_methods');
        Schema::dropIfExists('shipping_zone_locations');
        Schema::dropIfExists('shipping_zones');
        Schema::dropIfExists('exchange_rates');
        Schema::dropIfExists('currencies');
        Schema::dropIfExists('countries');
    }
};
