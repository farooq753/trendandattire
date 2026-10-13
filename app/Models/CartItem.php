<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CartItem extends Model
{
    protected $fillable = [
        'cart_id', 'product_id', 'product_variant_id', 'fulfillment_type', 'quantity',
        'unit_price', 'currency_code', 'configuration', 'measurements', 'personalization', 'line_signature',
    ];

    protected function casts(): array
    {
        return [
            'unit_price' => 'decimal:4',
            'configuration' => 'array',
            'measurements' => 'array',
            'personalization' => 'array',
        ];
    }

    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }
}
