<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use App\Models\Device;
use App\Models\HourlyEnergyLog;
use Illuminate\Support\Facades\Cache;

#[Signature('energy:log-hourly')]
#[Description('Log hourly energy consumption snapshots for all active devices')]
class LogHourlyEnergyCommand extends Command
{
    public function handle()
    {
        $devices = Device::where('status', true)->get();
        $hourStart = now()->startOfHour();
        $hourStartString = $hourStart->format('Y-m-d H:00:00');
        
        $count = 0;
        foreach ($devices as $device) {
            $voltage = Cache::get("voltage:{$device->device_id}", 0);
            $current = Cache::get("current:{$device->device_id}", 0);
            $power = Cache::get("power:{$device->device_id}", 0);
            $energy = Cache::get("daily_energy:{$device->device_id}", 0);
            
            $hourlyCacheKey = "hourly_logged:{$device->device_id}:{$hourStartString}";
            
            HourlyEnergyLog::updateOrCreate(
                [
                    'device_id' => $device->id,
                    'logged_at' => $hourStart,
                ],
                [
                    'voltage' => $voltage,
                    'current' => $current,
                    'power' => $power,
                    'energy' => $energy,
                ]
            );
            
            Cache::put($hourlyCacheKey, true, now()->addDays(2));
            $count++;
        }
        
        $this->info("Successfully logged hourly energy snapshot for {$count} devices.");
    }
}
