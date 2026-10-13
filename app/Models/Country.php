<?php

namespace App\Models;

use Database\Factories\CountryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    /** @use HasFactory<CountryFactory> */
    use HasFactory;

    protected $primaryKey = 'code';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'code', 'name', 'phone_prefix', 'is_active', 'address_requires_region',
        'address_requires_postal', 'region_label',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'address_requires_region' => 'boolean',
            'address_requires_postal' => 'boolean',
        ];
    }
}
