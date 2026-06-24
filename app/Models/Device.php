<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $device_id
 * @property string $name
 * @property int $group_id
 * @property bool $status
 * @property string $mqtt_topic
 * @property string|null $provisioning_code
 * @property string|null $firmware_path
 * @property float $voltage_multiplier
 * @property float $current_multiplier
 * @property float|null $monthly_budget_kwh
 * @property float|null $monthly_budget_cost
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 */
class Device extends Model
{
    protected $guarded = [];
    
    public function group()
    {
        return $this->belongsTo(Group::class);
    }
}
