<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderMeasurement extends Model
{
    protected $fillable = [
        'order_item_id', 'field_key', 'label', 'original_value', 'original_unit', 'normalized_cm',
    ];

    protected function casts(): array
    {
        return [
            'original_value' => 'decimal:2',
            'normalized_cm' => 'decimal:2',
        ];
    }

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }
}
