<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Device;
use App\Models\DailyEnergyLog;
use Carbon\Carbon;

class MockDailyEnergyLogsSeeder extends Seeder
{
    public function run(): void
    {
        $devices = Device::all();
        
        if ($devices->isEmpty()) {
            // Seed a dummy device if none exists
            $group = \App\Models\Group::first() ?? \App\Models\Group::create(['name' => 'FAI (Finance and Accounting)']);
            $device = Device::create([
                'device_id' => 'PZEM_TEST_01',
                'name' => 'Demo Device FAI',
                'group_id' => $group->id,
                'mqtt_topic' => 'telemetry/pzem_test_01',
                'status' => true,
            ]);
            $devices = collect([$device]);
        }

        // Generate data for the last 30 days
        for ($i = 30; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i)->toDateString();
            foreach ($devices as $device) {
                // Generate a random daily consumption between 5 kWh and 25 kWh
                $kwh = rand(500, 2500) / 100;
                
                DailyEnergyLog::updateOrCreate(
                    ['device_id' => $device->id, 'date' => $date],
                    ['total_kwh_harian' => $kwh]
                );
            }
        }
        
        echo "Mock daily energy logs successfully seeded for " . $devices->count() . " devices!\n";
    }
}
