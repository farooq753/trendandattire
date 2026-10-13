<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductFabric extends Model
{
    protected $fillable = [
        'product_id', 'name', 'composition', 'price_delta', 'extra_lead_days', 'is_active', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'price_delta' => 'decimal:4',
            'is_active' => 'boolean',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
