<?php

namespace App\Models;

use Database\Factories\OrderFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    /** @use HasFactory<OrderFactory> */
    use HasFactory;

    protected $fillable = [
        'number', 'user_id', 'tracking_token', 'checkout_token', 'currency_code',
        'fx_base_code', 'fx_rate', 'fx_quoted_at', 'subtotal', 'discount_total',
        'shipping_total', 'tax_total', 'cod_fee', 'grand_total', 'coupon_code',
        'payment_method', 'payment_status', 'fulfillment_status', 'customer_name',
        'customer_email', 'customer_phone', 'notes', 'shipping_method_name',
        'shipping_snapshot', 'tax_snapshot', 'placed_at', 'cancelled_at',
    ];

    public function getRouteKeyName(): string
    {
        return 'number';
    }

    protected function casts(): array
    {
        return [
            'fx_rate' => 'decimal:8',
            'subtotal' => 'decimal:4',
            'discount_total' => 'decimal:4',
            'shipping_total' => 'decimal:4',
            'tax_total' => 'decimal:4',
            'cod_fee' => 'decimal:4',
            'grand_total' => 'decimal:4',
            'shipping_snapshot' => 'array',
            'tax_snapshot' => 'array',
            'placed_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'fx_quoted_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(OrderAddress::class);
    }

    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class)->latestOfMany();
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function shipments(): HasMany
    {
        return $this->hasMany(Shipment::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(OrderEvent::class)->orderBy('id');
    }

    public function returns(): HasMany
    {
        return $this->hasMany(ProductReturn::class);
    }

    public function refunds(): HasMany
    {
        return $this->hasMany(Refund::class);
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(StockReservation::class);
    }

    public function shippingAddress(): ?OrderAddress
    {
        return $this->addresses->firstWhere('type', 'shipping');
    }
}
