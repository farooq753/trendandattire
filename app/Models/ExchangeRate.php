<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExchangeRate extends Model
{
    protected $fillable = ['base_code', 'quote_code', 'rate', 'source', 'effective_at'];

    protected function casts(): array
    {
        return [
            'rate' => 'decimal:8',
            'effective_at' => 'datetime',
        ];
    }
}
