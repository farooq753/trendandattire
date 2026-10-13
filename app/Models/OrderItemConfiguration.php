<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItemConfiguration extends Model
{
    protected $fillable = ['order_item_id', 'option_key', 'label', 'value', 'price_delta'];

    protected function casts(): array
    {
        return ['price_delta' => 'decimal:4'];
    }

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }
}
