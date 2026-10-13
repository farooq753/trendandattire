<?php

namespace App\Models;

use App\Support\Money;
use Database\Factories\ProductVariantFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductVariant extends Model
{
    /** @use HasFactory<ProductVariantFactory> */
    use HasFactory;

    protected $fillable = [
        'product_id', 'sku', 'barcode', 'combination_key', 'regular_price', 'sale_price',
        'stock_on_hand', 'reserved', 'weight_grams', 'is_available',
    ];

    protected function casts(): array
    {
        return [
            'regular_price' => 'decimal:4',
            'sale_price' => 'decimal:4',
            'is_available' => 'boolean',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function attributeValues(): BelongsToMany
    {
        return $this->belongsToMany(AttributeValue::class, 'variant_attribute_values');
    }

    public function movements(): HasMany
    {
        return $this->hasMany(InventoryMovement::class);
    }

    public function availableQuantity(): int
    {
        return max(0, (int) $this->stock_on_hand - (int) $this->reserved);
    }

    public function unitPrice(): string
    {
        if ($this->sale_price !== null) {
            return Money::normalize($this->sale_price);
        }

        if ($this->regular_price !== null) {
            return Money::normalize($this->regular_price);
        }

        return $this->product->effectivePrice();
    }

    public function label(): string
    {
        $this->loadMissing('attributeValues.attribute');

        return $this->attributeValues
            ->sortBy(fn (AttributeValue $value) => $value->attribute?->sort_order ?? 0)
            ->map(fn (AttributeValue $value) => $value->value)
            ->implode(' / ');
    }
}
