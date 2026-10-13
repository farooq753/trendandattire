<?php

namespace App\Models;

use Database\Factories\TaxRuleFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaxRule extends Model
{
    /** @use HasFactory<TaxRuleFactory> */
    use HasFactory;

    protected $fillable = [
        'country_code', 'region', 'name', 'rate_percent', 'price_mode', 'applies_to',
        'product_fulfillment', 'is_active', 'priority',
    ];

    protected function casts(): array
    {
        return [
            'rate_percent' => 'decimal:4',
            'is_active' => 'boolean',
        ];
    }
}
