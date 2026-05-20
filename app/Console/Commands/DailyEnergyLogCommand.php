<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use App\Models\Device;
use App\Models\DailyEnergyLog;
use Illuminate\Support\Facades\Cache;

#[Signature('energy:log-daily')]
#[Description('Log daily energy consumption for all active devices')]
class DailyEnergyLogCommand extends Command
{
    public function handle()
    {
        $devices = Device::where('status', true)->get();
        $date = now()->toDateString();
        
        $count = 0;
        foreach ($devices as $device) {
            $energy = Cache::get("energy:{$device->device_id}");
            
            if ($energy !== null) {
                DailyEnergyLog::updateOrCreate(
                    ['device_id' => $device->id, 'date' => $date],
                    ['total_kwh_harian' => $energy]
                );
                $count++;
            }
        }
        
        $this->info("Successfully logged daily energy for {$count} devices.");
    }
}
