<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $fillable = [
        'user_id',
        'tenant_id',
        'type',
        'message',
        'status',
        'attempts',
        'processed_at'
    ];
}
