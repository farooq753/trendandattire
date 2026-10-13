<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductionJob extends Model
{
    protected $fillable = [
        'order_item_id', 'status', 'lead_time_days', 'due_on', 'staff_notes', 'updated_by',
    ];

    protected function casts(): array
    {
        return ['due_on' => 'date'];
    }

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }

    public function editor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
