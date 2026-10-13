<?php

namespace App\Models;

use Database\Factories\ShippingMethodFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShippingMethod extends Model
{
    /** @use HasFactory<ShippingMethodFactory> */
    use HasFactory;

    protected $fillable = [
        'shipping_zone_id', 'name', 'code', 'rate_type', 'amount', 'per_kg', 'free_threshold',
        'min_order', 'max_order', 'min_weight_grams', 'max_weight_grams', 'processing_min_days',
        'processing_max_days', 'allows_cod', 'is_active', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:4',
            'per_kg' => 'decimal:4',
            'free_threshold' => 'decimal:4',
            'min_order' => 'decimal:4',
            'max_order' => 'decimal:4',
            'allows_cod' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function zone(): BelongsTo
    {
        return $this->belongsTo(ShippingZone::class, 'shipping_zone_id');
    }
}
