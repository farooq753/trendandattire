<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ShippingZone extends Model
{
    protected $fillable = ['name', 'slug', 'is_international', 'is_active'];

    protected function casts(): array
    {
        return [
            'is_international' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function locations(): HasMany
    {
        return $this->hasMany(ShippingZoneLocation::class);
    }

    public function methods(): HasMany
    {
        return $this->hasMany(ShippingMethod::class)->orderBy('sort_order')->orderBy('id');
    }
}
