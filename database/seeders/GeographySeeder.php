<?php

namespace Database\Seeders;

use App\Models\Country;
use App\Models\Currency;
use App\Models\ExchangeRate;
use App\Models\ShippingMethod;
use App\Models\ShippingZone;
use App\Models\TaxRule;
use Illuminate\Database\Seeder;

class GeographySeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            ['code' => 'USD', 'name' => 'US Dollar', 'symbol' => '$', 'decimal_places' => 2, 'is_default' => true],
            ['code' => 'EUR', 'name' => 'Euro', 'symbol' => '€', 'decimal_places' => 2, 'is_default' => false],
            ['code' => 'GBP', 'name' => 'Pound Sterling', 'symbol' => '£', 'decimal_places' => 2, 'is_default' => false],
            ['code' => 'AED', 'name' => 'UAE Dirham', 'symbol' => 'AED ', 'decimal_places' => 2, 'is_default' => false],
            ['code' => 'PKR', 'name' => 'Pakistani Rupee', 'symbol' => 'Rs. ', 'decimal_places' => 0, 'is_default' => false],
        ] as $currency) {
            Currency::query()->updateOrCreate(['code' => $currency['code']], $currency + ['is_active' => true]);
        }

        $quotedAt = now()->subDay();

        foreach ([
            'EUR' => '0.92000000',
            'GBP' => '0.78000000',
            'AED' => '3.67250000',
        ] as $code => $rate) {
            ExchangeRate::query()->updateOrCreate(
                ['base_code' => 'USD', 'quote_code' => $code, 'source' => 'manual'],
                ['rate' => $rate, 'effective_at' => $quotedAt],
            );
        }

        $countries = [
            ['code' => 'US', 'name' => 'United States', 'phone_prefix' => '+1', 'address_requires_region' => true, 'address_requires_postal' => true, 'region_label' => 'State'],
            ['code' => 'GB', 'name' => 'United Kingdom', 'phone_prefix' => '+44', 'address_requires_region' => false, 'address_requires_postal' => true, 'region_label' => 'County'],
            ['code' => 'AE', 'name' => 'United Arab Emirates', 'phone_prefix' => '+971', 'address_requires_region' => true, 'address_requires_postal' => false, 'region_label' => 'Emirate'],
            ['code' => 'PK', 'name' => 'Pakistan', 'phone_prefix' => '+92', 'address_requires_region' => true, 'address_requires_postal' => true, 'region_label' => 'Province'],
            ['code' => 'CA', 'name' => 'Canada', 'phone_prefix' => '+1', 'address_requires_region' => true, 'address_requires_postal' => true, 'region_label' => 'Province'],
            ['code' => 'DE', 'name' => 'Germany', 'phone_prefix' => '+49', 'address_requires_region' => false, 'address_requires_postal' => true, 'region_label' => 'State'],
            ['code' => 'AU', 'name' => 'Australia', 'phone_prefix' => '+61', 'address_requires_region' => true, 'address_requires_postal' => true, 'region_label' => 'State'],
            ['code' => 'SA', 'name' => 'Saudi Arabia', 'phone_prefix' => '+966', 'address_requires_region' => true, 'address_requires_postal' => true, 'region_label' => 'Region'],
        ];

        foreach ($countries as $country) {
            Country::query()->updateOrCreate(['code' => $country['code']], $country + ['is_active' => true]);
        }

        $domestic = ShippingZone::query()->updateOrCreate(
            ['slug' => 'united-states'],
            ['name' => 'United States', 'is_international' => false, 'is_active' => true],
        );
        $domestic->locations()->updateOrCreate(
            ['country_code' => 'US', 'region' => null],
            [],
        );

        ShippingMethod::query()->updateOrCreate(
            ['shipping_zone_id' => $domestic->id, 'code' => 'ground'],
            [
                'name' => 'Ground',
                'rate_type' => 'flat',
                'amount' => '8.0000',
                'allows_cod' => true,
                'processing_min_days' => 2,
                'processing_max_days' => 5,
                'is_active' => true,
                'sort_order' => 1,
            ],
        );
        ShippingMethod::query()->updateOrCreate(
            ['shipping_zone_id' => $domestic->id, 'code' => 'express'],
            [
                'name' => 'Express (weight)',
                'rate_type' => 'weight',
                'amount' => '12.0000',
                'per_kg' => '4.0000',
                'allows_cod' => false,
                'processing_min_days' => 1,
                'processing_max_days' => 3,
                'is_active' => true,
                'sort_order' => 2,
            ],
        );

        $international = ShippingZone::query()->updateOrCreate(
            ['slug' => 'international'],
            ['name' => 'International', 'is_international' => true, 'is_active' => true],
        );

        foreach (['GB', 'AE', 'PK', 'CA', 'DE', 'AU', 'SA'] as $code) {
            $international->locations()->updateOrCreate(
                ['country_code' => $code, 'region' => null],
                [],
            );
        }

        ShippingMethod::query()->updateOrCreate(
            ['shipping_zone_id' => $international->id, 'code' => 'intl-flat'],
            [
                'name' => 'International flat estimate',
                'rate_type' => 'flat',
                'amount' => '25.0000',
                'free_threshold' => '200.0000',
                'allows_cod' => false,
                'processing_min_days' => 5,
                'processing_max_days' => 14,
                'is_active' => true,
                'sort_order' => 1,
            ],
        );

        TaxRule::query()->updateOrCreate(
            ['country_code' => 'US', 'region' => 'CA', 'name' => 'California sample product rate'],
            ['rate_percent' => '7.2500', 'price_mode' => 'exclusive', 'applies_to' => 'products', 'is_active' => true, 'priority' => 10],
        );
        TaxRule::query()->updateOrCreate(
            ['country_code' => 'DE', 'region' => null, 'name' => 'Germany sample rate'],
            ['rate_percent' => '19.0000', 'price_mode' => 'exclusive', 'applies_to' => 'both', 'is_active' => true, 'priority' => 10],
        );
    }
}
