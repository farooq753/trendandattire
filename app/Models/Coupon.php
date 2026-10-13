<?php

namespace App\Models;

use Database\Factories\CouponFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Coupon extends Model
{
    /** @use HasFactory<CouponFactory> */
    use HasFactory;

    protected $fillable = [
        'code', 'type', 'value', 'currency_code', 'min_subtotal', 'max_discount',
        'starts_at', 'ends_at', 'usage_limit', 'per_customer_limit', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'value' => 'decimal:4',
            'min_subtotal' => 'decimal:4',
            'max_discount' => 'decimal:4',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    public function usages(): HasMany
    {
        return $this->hasMany(CouponUsage::class);
    }
}
