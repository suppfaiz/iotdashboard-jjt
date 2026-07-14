<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OutageLog extends Model
{
    protected $guarded = [];

    protected $casts = [
        'outage_start' => 'datetime',
        'outage_end' => 'datetime',
    ];
}
