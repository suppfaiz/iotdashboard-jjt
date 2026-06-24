<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Device;
use App\Models\Group;
use App\Models\SystemConfig;
use App\Models\HourlyEnergyLog;
use App\Models\DailyEnergyLog;
use App\Console\Commands\MqttWorker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class MqttWorkerTest extends TestCase
{
    use RefreshDatabase;

    private $device;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();

        $group = Group::create(['name' => 'Test Group']);
        $this->device = Device::create([
            'device_id' => 'dev_test123',
            'name' => 'Test Meter',
            'group_id' => $group->id,
            'status' => true,
            'mqtt_topic' => 'telemetry/test-group/dev_test123',
            'provisioning_code' => '// test code'
        ]);

        // Default configurations
        SystemConfig::updateOrCreate(['key' => 'pln_tariff_wbp'], ['value' => '2000.00']);
        SystemConfig::updateOrCreate(['key' => 'pln_tariff_lwbp'], ['value' => '1000.00']);
        SystemConfig::updateOrCreate(['key' => 'wbp_start'], ['value' => '17:00']);
        SystemConfig::updateOrCreate(['key' => 'wbp_end'], ['value' => '22:00']);
        SystemConfig::updateOrCreate(['key' => 'pln_tariff'], ['value' => '1500.00']);
    }

    public function test_standard_telemetry_accumulation_and_reboot()
    {
        // Set time to standard LWBP hours (e.g. 10:00 AM)
        $this->travelTo(now()->setTime(10, 0));

        $worker = new MqttWorker();

        // 1. Initial Reading: Cumulative Energy starts at 10.0 kWh
        $payload1 = json_encode([
            'voltage' => 220.0,
            'current' => 1.5,
            'power' => 330.0,
            'energy' => 10.0,
            'ip' => '192.168.1.100',
            'rssi' => -65,
            'heap' => 123456
        ]);
        
        $worker->processMessage("telemetry/test-group/dev_test123", $payload1);

        // Daily energy should be 0.0 initially (since last_energy was not set, delta is 0)
        $this->assertEquals(0.0, Cache::get("daily_energy:dev_test123"));
        $this->assertEquals(0.0, Cache::get("daily_cost:dev_test123"));
        $this->assertEquals(-65, Cache::get("rssi:dev_test123"));
        $this->assertEquals(123456, Cache::get("heap:dev_test123"));

        // 2. Second Reading: Cumulative Energy increases to 10.5 kWh (+0.5 kWh consumed)
        $payload2 = json_encode([
            'voltage' => 220.0,
            'current' => 1.5,
            'power' => 330.0,
            'energy' => 10.5,
            'ip' => '192.168.1.100'
        ]);

        $worker->processMessage("telemetry/test-group/dev_test123", $payload2);

        // Delta is 0.5 kWh, cost is 0.5 * 1000.00 (LWBP tariff) = 500
        $this->assertEquals(0.5, Cache::get("daily_energy:dev_test123"));
        $this->assertEquals(500.0, Cache::get("daily_cost:dev_test123"));

        // 3. Third Reading: ESP32 Reboots! Cumulative Energy resets to 0.0 kWh
        $payload3 = json_encode([
            'voltage' => 220.0,
            'current' => 1.5,
            'power' => 330.0,
            'energy' => 0.0,
            'ip' => '192.168.1.100'
        ]);

        $worker->processMessage("telemetry/test-group/dev_test123", $payload3);

        // Daily energy should survive the reboot and stay 0.5 kWh
        $this->assertEquals(0.5, Cache::get("daily_energy:dev_test123"));

        // 4. Fourth Reading: Cumulative Energy increases to 0.2 kWh (+0.2 kWh consumed since reboot)
        $payload4 = json_encode([
            'voltage' => 220.0,
            'current' => 1.5,
            'power' => 330.0,
            'energy' => 0.2,
            'ip' => '192.168.1.100'
        ]);

        $worker->processMessage("telemetry/test-group/dev_test123", $payload4);

        // Daily energy should accumulate the new delta (+0.2 kWh) and become 0.7 kWh
        $this->assertEquals(0.7, Cache::get("daily_energy:dev_test123"));
        $this->assertEquals(700.0, Cache::get("daily_cost:dev_test123"));

        // Assert database persistence
        $this->assertDatabaseHas('daily_energy_logs', [
            'device_id' => $this->device->id,
            'date' => now()->toDateString(),
            'total_kwh_harian' => 0.7
        ]);
    }

    public function test_historical_telemetry_reconstruction()
    {
        // Simulate historical uploads from yesterday
        $yesterday = now()->subDay()->setTime(12, 0);
        $worker = new MqttWorker();

        // 1. Initial historical reading yesterday at 12:00 PM: raw energy = 5.0 kWh
        $payload1 = json_encode([
            'voltage' => 220.0,
            'current' => 1.0,
            'power' => 220.0,
            'energy' => 5.0,
            'timestamp' => $yesterday->timestamp
        ]);

        $worker->processMessage("telemetry/historical/dev_test123", $payload1);

        // Daily energy yesterday should be 0.0 initially (since last_historical_energy was null, delta is 0)
        $yesterdayDate = $yesterday->toDateString();
        $this->assertEquals(0.0, Cache::get("daily_energy:dev_test123:{$yesterdayDate}"));

        // 2. Second historical reading yesterday at 1:00 PM: raw energy = 5.8 kWh (+0.8 kWh)
        $payload2 = json_encode([
            'voltage' => 220.0,
            'current' => 1.0,
            'power' => 220.0,
            'energy' => 5.8,
            'timestamp' => $yesterday->copy()->addHour()->timestamp
        ]);

        $worker->processMessage("telemetry/historical/dev_test123", $payload2);

        // Daily energy yesterday should now be 0.8 kWh
        $this->assertEquals(0.8, Cache::get("daily_energy:dev_test123:{$yesterdayDate}"));

        // Assert database records for yesterday
        $this->assertDatabaseHas('daily_energy_logs', [
            'device_id' => $this->device->id,
            'date' => $yesterdayDate,
            'total_kwh_harian' => 0.8
        ]);

        $this->assertDatabaseHas('hourly_energy_logs', [
            'device_id' => $this->device->id,
            'logged_at' => $yesterday->copy()->addHour()->startOfHour()->format('Y-m-d H:i:s'),
            'energy' => 0.8
        ]);
    }

    public function test_reset_energy_clears_caches_and_logs()
    {
        $this->travelTo(now()->setTime(10, 0));
        $worker = new MqttWorker();

        // 1. Send standard telemetry packet to set cache
        $worker->processMessage("telemetry/test-group/dev_test123", json_encode([
            'voltage' => 220.0,
            'current' => 1.0,
            'power' => 220.0,
            'energy' => 10.0
        ]));
        
        $worker->processMessage("telemetry/test-group/dev_test123", json_encode([
            'voltage' => 220.0,
            'current' => 1.0,
            'power' => 220.0,
            'energy' => 10.5
        ]));

        $this->assertEquals(0.5, Cache::get("daily_energy:dev_test123"));

        // 2. Perform resetEnergy controller action
        $admin = User::factory()->create(['role' => 'admin']);
        $response = $this->actingAs($admin)->post("/devices/{$this->device->id}/reset-energy");
        $response->assertRedirect();

        // Assert caches are cleared
        $this->assertEquals(0.0, Cache::get("energy:dev_test123"));
        $this->assertEquals(0.0, Cache::get("daily_energy:dev_test123"));
        $this->assertEquals(0.0, Cache::get("last_energy:dev_test123"));
        $this->assertEquals(0.0, Cache::get("last_historical_energy:dev_test123"));
        $this->assertEquals(0.0, Cache::get("daily_cost:dev_test123"));

        // Assert DB Daily Log is reset
        $this->assertDatabaseHas('daily_energy_logs', [
            'device_id' => $this->device->id,
            'date' => now()->toDateString(),
            'total_kwh_harian' => 0.0
        ]);
    }
}
