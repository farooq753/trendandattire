<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailLog extends Model
{
    protected $fillable = [
        'mailable', 'recipient', 'subject', 'status', 'error', 'related_type', 'related_id',
    ];
}
