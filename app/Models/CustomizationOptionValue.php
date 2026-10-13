<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomizationOptionValue extends Model
{
    protected $fillable = [
        'product_customization_option_id', 'label', 'value', 'price_delta', 'extra_lead_days', 'is_active', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'price_delta' => 'decimal:4',
            'is_active' => 'boolean',
        ];
    }

    public function option(): BelongsTo
    {
        return $this->belongsTo(ProductCustomizationOption::class, 'product_customization_option_id');
    }
}
