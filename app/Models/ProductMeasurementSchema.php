<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductMeasurementSchema extends Model
{
    protected $table = 'product_measurement_schemas';

    protected $fillable = [
        'product_id', 'field_key', 'label', 'min_cm', 'max_cm', 'is_required', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'min_cm' => 'decimal:2',
            'max_cm' => 'decimal:2',
            'is_required' => 'boolean',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
