<?php

namespace App\Models;

use App\Support\Money;
use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    /** @use HasFactory<ProductFactory> */
    use HasFactory;

    protected $fillable = [
        'category_id', 'name', 'sku', 'slug', 'short_description', 'description',
        'gender', 'garment_type', 'fulfillment_type', 'regular_price', 'sale_price',
        'cost_price', 'currency_code', 'fabric_composition', 'gsm', 'stretch', 'fit',
        'care', 'origin_country', 'weight_grams', 'length_mm', 'width_mm', 'height_mm',
        'meta_title', 'meta_description', 'keywords', 'is_featured', 'is_published',
        'published_at', 'production_lead_days', 'low_stock_threshold', 'track_inventory',
        'allows_personalization',
    ];

    protected function casts(): array
    {
        return [
            'regular_price' => 'decimal:4',
            'sale_price' => 'decimal:4',
            'cost_price' => 'decimal:4',
            'is_featured' => 'boolean',
            'is_published' => 'boolean',
            'published_at' => 'datetime',
            'track_inventory' => 'boolean',
            'allows_personalization' => 'boolean',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order')->orderBy('id');
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function fabrics(): HasMany
    {
        return $this->hasMany(ProductFabric::class)->orderBy('sort_order')->orderBy('id');
    }

    public function customizationOptions(): HasMany
    {
        return $this->hasMany(ProductCustomizationOption::class)->orderBy('step')->orderBy('sort_order');
    }

    public function measurementSchemas(): HasMany
    {
        return $this->hasMany(ProductMeasurementSchema::class)->orderBy('sort_order')->orderBy('id');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(ProductReview::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * @param  Builder<Product>  $query
     * @return Builder<Product>
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }

    public function effectivePrice(): string
    {
        return Money::normalize($this->sale_price ?? $this->regular_price);
    }

    public function isOnSale(): bool
    {
        return $this->sale_price !== null
            && Money::compare($this->sale_price, $this->regular_price) < 0;
    }

    public function acceptsReadyMade(): bool
    {
        return in_array($this->fulfillment_type, ['ready', 'both'], true);
    }

    public function acceptsCustom(): bool
    {
        return in_array($this->fulfillment_type, ['custom', 'both'], true);
    }

    public function primaryImagePath(): string
    {
        $image = $this->images->firstWhere('is_primary', true) ?? $this->images->first();

        return $image?->path ?? '/images/placeholders/scrub.svg';
    }
}
