<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HourlyEnergyLog extends Model
{
    protected $guarded = [];

    public function device()
    {
        return $this->belongsTo(Device::class, 'device_id');
    }
}
