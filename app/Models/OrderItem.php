<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id', 'product_id', 'product_variant_id', 'name', 'sku', 'fulfillment_type',
        'quantity', 'unit_price', 'discount_total', 'tax_total', 'line_total',
        'production_status', 'snapshot',
    ];

    protected function casts(): array
    {
        return [
            'unit_price' => 'decimal:4',
            'discount_total' => 'decimal:4',
            'tax_total' => 'decimal:4',
            'line_total' => 'decimal:4',
            'snapshot' => 'array',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    public function configurations(): HasMany
    {
        return $this->hasMany(OrderItemConfiguration::class);
    }

    public function measurements(): HasMany
    {
        return $this->hasMany(OrderMeasurement::class);
    }

    public function productionJob(): HasOne
    {
        return $this->hasOne(ProductionJob::class);
    }

    public function shipmentItems(): HasMany
    {
        return $this->hasMany(ShipmentItem::class);
    }

    public function shippedQuantity(): int
    {
        return (int) $this->shipmentItems()->sum('quantity');
    }
}
