<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends Model{
    protected $table = 'notifications';
    protected $guarded = ['id'];

    protected $casts = [
        'read_at' => 'datetime',
    ];
}