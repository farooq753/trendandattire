<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductCustomizationOption extends Model
{
    protected $fillable = [
        'product_id', 'code', 'name', 'step', 'input_type', 'is_required', 'is_active', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_required' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function values(): HasMany
    {
        return $this->hasMany(CustomizationOptionValue::class)->orderBy('sort_order')->orderBy('id');
    }
}
