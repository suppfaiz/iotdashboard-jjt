<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Device;
use App\Models\Group;
use App\Models\SystemConfig;
use App\Models\DailyEnergyLog;
use App\Console\Commands\MqttWorker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AdvancedExtensionsTest extends TestCase
{
    use RefreshDatabase;

    private $admin;
    private $user;
    private $device;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
        Http::fake();

        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->user = User::factory()->create(['role' => 'user']);
        
        $group = Group::create(['name' => 'Factory Room']);
        $this->device = Device::create([
            'device_id' => 'dev_test123',
            'name' => 'Calibrated Meter',
            'group_id' => $group->id,
            'status' => true,
            'mqtt_topic' => 'telemetry/factory/dev_test123',
            'provisioning_code' => '// code',
            'voltage_multiplier' => 1.10,
            'current_multiplier' => 0.90,
            'monthly_budget_kwh' => 100.00,
            'monthly_budget_cost' => 150000.00,
        ]);

        // Default configurations
        SystemConfig::updateOrCreate(['key' => 'pln_tariff_wbp'], ['value' => '2000.00']);
        SystemConfig::updateOrCreate(['key' => 'pln_tariff_lwbp'], ['value' => '1000.00']);
        SystemConfig::updateOrCreate(['key' => 'wbp_start'], ['value' => '17:00']);
        SystemConfig::updateOrCreate(['key' => 'wbp_end'], ['value' => '22:00']);
        SystemConfig::updateOrCreate(['key' => 'pln_tariff'], ['value' => '1500.00']);
        SystemConfig::updateOrCreate(['key' => 'telegram_bot_token'], ['value' => 'fake_token']);
        SystemConfig::updateOrCreate(['key' => 'telegram_chat_id'], ['value' => 'fake_chat']);
    }

    public function test_telemetry_calibration_and_multiplier_accuracy(): void
    {
        $this->travelTo(now()->setTime(10, 0)); // LWBP
        $worker = new MqttWorker();

        // 1. Initial message (calibration cache init)
        $worker->processMessage("telemetry/factory/dev_test123", json_encode([
            'voltage' => 200.0,
            'current' => 2.0,
            'power' => 400.0,
            'energy' => 10.0
        ]));

        // 2. Second message (energy increases by 0.5 kWh raw)
        $worker->processMessage("telemetry/factory/dev_test123", json_encode([
            'voltage' => 200.0,
            'current' => 2.0,
            'power' => 400.0,
            'energy' => 10.5
        ]));

        // Calibration logic:
        // voltage: 200.0 * 1.10 = 220.0
        // current: 2.0 * 0.90 = 1.8
        // power: 220.0 * 1.8 = 396.0
        // energy delta: 0.5 * 1.10 * 0.90 = 0.495
        $this->assertEquals(220.0, Cache::get("voltage:dev_test123"));
        $this->assertEquals(1.8, Cache::get("current:dev_test123"));
        $this->assertEquals(396.0, Cache::get("power:dev_test123"));
        $this->assertEquals(0.495, Cache::get("daily_energy:dev_test123"));

        // Cost is 0.495 * 1000 = 495.0
        $this->assertEquals(495.0, Cache::get("daily_cost:dev_test123"));

        $this->assertDatabaseHas('daily_energy_logs', [
            'device_id' => $this->device->id,
            'total_kwh_harian' => 0.495
        ]);
    }

    public function test_device_offline_heartbeats_and_recovery_telegram_alerts(): void
    {
        // 1. Mark device as offline (last seen 6 minutes ago)
        Cache::put("last_seen:dev_test123", now()->subMinutes(6)->timestamp);

        // Run monitor
        $this->artisan('devices:monitor')->assertExitCode(0);

        // Assert Telegram API was called for offline alert
        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'fake_token/sendMessage') &&
                   $request['chat_id'] === 'fake_chat' &&
                   str_contains($request['text'], 'DEVICE OFFLINE ALERT') &&
                   str_contains($request['text'], 'Calibrated Meter');
        });

        // 2. Clear sent requests list, now mark device as online (last seen 10 seconds ago)
        Http::fake();
        Cache::put("last_seen:dev_test123", now()->subSeconds(10)->timestamp);

        // Run monitor again
        $this->artisan('devices:monitor')->assertExitCode(0);

        // Assert Telegram API was called for recovery alert
        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'fake_token/sendMessage') &&
                   $request['chat_id'] === 'fake_chat' &&
                   str_contains($request['text'], 'DEVICE ONLINE RECOVERY');
        });
    }

    public function test_monthly_energy_and_cost_budgeting_telegram_alerts(): void
    {
        // Mark device as online to avoid offline alert interference
        Cache::put("last_seen:dev_test123", now()->timestamp);

        // 1. Set daily energy log to 81 kWh (exceeds 80% of 100 kWh budget)
        DailyEnergyLog::create([
            'device_id' => $this->device->id,
            'date' => now()->toDateString(),
            'total_kwh_harian' => 81.00
        ]);

        $this->artisan('devices:monitor')->assertExitCode(0);

        Http::assertSent(function ($request) {
            return str_contains($request['text'], 'BATAS ANGGARAN ENERGI 80%') &&
                   str_contains($request['text'], 'Calibrated Meter');
        });

        // 2. Clear sent requests, now set daily energy log to 101 kWh (exceeds 100% of 100 kWh budget)
        Http::fake();
        DailyEnergyLog::query()->delete();
        DailyEnergyLog::create([
            'device_id' => $this->device->id,
            'date' => now()->toDateString(),
            'total_kwh_harian' => 101.00
        ]);

        $this->artisan('devices:monitor')->assertExitCode(0);

        Http::assertSent(function ($request) {
            return str_contains($request['text'], 'BATAS ANGGARAN ENERGI 100%') &&
                   str_contains($request['text'], 'Calibrated Meter');
        });
    }

    public function test_csv_report_download_security_and_streaming(): void
    {
        // 1. Guest access should redirect to login
        $response = $this->get("/devices/{$this->device->id}/export-csv");
        $response->assertRedirect('/login');

        // 2. Auth user access should succeed and return CSV headers
        $response = $this->actingAs($this->user)->get("/devices/{$this->device->id}/export-csv");
        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        
        $expectedFilename = 'attachment; filename="energy_log_dev_test123_' . now()->format('Ymd') . '.csv"';
        $response->assertHeader('Content-Disposition', $expectedFilename);
    }

    public function test_monthly_pdf_and_csv_reports_generation(): void
    {
        // Create a log in June 2026
        DailyEnergyLog::create([
            'device_id' => $this->device->id,
            'date' => '2026-06-15',
            'total_kwh_harian' => 12.34
        ]);

        // 1. Admin can access monthly PDF download
        $response = $this->actingAs($this->admin)->get('/reports/monthly/2026-06');
        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/pdf');

        // 2. Admin can access monthly CSV export
        $response = $this->actingAs($this->admin)->get('/reports/monthly/2026-06/export-csv');
        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        $response->assertHeader('Content-Disposition', 'attachment; filename="monthly_energy_report_2026-06.csv"');
    }

    public function test_role_based_access_control_and_validation(): void
    {
        // 1. Standard user cannot update device configuration
        $response = $this->actingAs($this->user)->patch("/devices/{$this->device->id}", [
            'name' => 'New Name',
            'group_id' => $this->device->group_id,
            'voltage_multiplier' => 1.20,
            'current_multiplier' => 1.20,
        ]);
        $response->assertStatus(403);

        // 2. Admin user can update device configuration
        $response = $this->actingAs($this->admin)->patch("/devices/{$this->device->id}", [
            'name' => 'New Name',
            'group_id' => $this->device->group_id,
            'voltage_multiplier' => 1.20,
            'current_multiplier' => 1.20,
            'monthly_budget_kwh' => 200,
            'monthly_budget_cost' => 300000,
        ]);
        $response->assertRedirect();
        $this->device->refresh();
        $this->assertEquals(1.20, $this->device->voltage_multiplier);

        // 3. Standard user cannot send custom commands to device console
        $response = $this->actingAs($this->user)->post("/devices/{$this->device->id}/console", [
            'payload' => '{"restart":true}'
        ]);
        $response->assertStatus(403);

        // 4. Admin user cannot send invalid JSON command
        $response = $this->actingAs($this->admin)->post("/devices/{$this->device->id}/console", [
            'payload' => 'invalid-json'
        ]);
        $response->assertStatus(400);

        // 5. Admin user can send valid JSON command
        $response = $this->actingAs($this->admin)->post("/devices/{$this->device->id}/console", [
            'payload' => '{"restart":true}'
        ]);
        $this->assertNotEquals(403, $response->getStatusCode());
        $this->assertNotEquals(400, $response->getStatusCode());
    }

    public function test_changelog_route_is_accessible_to_any_auth_user(): void
    {
        // Guest gets redirected to login
        $this->get('/changelog')->assertRedirect('/login');

        // Authenticated user can access changelog page
        $this->actingAs($this->user)->get('/changelog')->assertOk();
    }

    public function test_dashboard_per_device_budget_forecasting(): void
    {
        // 1. Create a historical log for the device in the current month
        DailyEnergyLog::create([
            'device_id' => $this->device->id,
            'date' => now()->startOfMonth()->toDateString(),
            'total_kwh_harian' => 15.00
        ]);

        // 2. Put some real-time volatile energy in cache to verify real-time delta summation
        Cache::put("daily_energy:dev_test123", 5.00);

        // 3. Access dashboard
        $response = $this->actingAs($this->admin)->get(route('dashboard'));
        $response->assertOk();

        // 4. Assert that the device object in the view has the calculated budget projection attributes
        $viewGroups = $response->original->getData()['groups'];
        $deviceObj = $viewGroups->first()->devices->first();

        // Current month energy should be historical (15.00) + volatile today (5.00) = 20.00 kWh
        $this->assertEquals(20.00, $deviceObj->current_month_kwh);
        $this->assertEquals(20.00 * 1500.00, $deviceObj->current_month_cost); // pln_tariff is 1500.00

        // Check if projected_kwh and projected_cost are populated and greater than 0
        $this->assertGreaterThan(0.0, $deviceObj->projected_kwh);
        $this->assertGreaterThan(0.0, $deviceObj->projected_cost);
    }

    public function test_dashboard_active_warnings_detection(): void
    {
        // 1. Set a device as online and set an unstable voltage alert in cache
        Cache::put("last_seen:dev_test123", now()->timestamp);
        Cache::put("voltage:dev_test123", 190.00); // lower than 200V limit

        // 2. Access dashboard
        $response = $this->actingAs($this->admin)->get(route('dashboard'));
        $response->assertOk();

        // 3. Assert warnings are passed to the view
        $warnings = $response->original->getData()['activeWarnings'];
        $this->assertNotEmpty($warnings);
        
        $voltageWarning = collect($warnings)->firstWhere('type', 'voltage');
        $this->assertNotNull($voltageWarning);
        $this->assertStringContainsString('Voltase tidak stabil', $voltageWarning['message']);
    }

    public function test_mqtt_tls_and_credentials_settings_and_provisioning_regeneration(): void
    {
        // 1. Update settings to enable TLS and configure credentials
        $response = $this->actingAs($this->admin)->put('/settings', [
            'pln_tariff' => 1500,
            'pln_tariff_wbp' => 2000,
            'pln_tariff_lwbp' => 1000,
            'wbp_start' => '17:00',
            'wbp_end' => '22:00',
            'mqtt_host' => '25b7768b96d642e28fab356da906f103.s1.eu.hivemq.cloud',
            'mqtt_port' => 8883,
            'mqtt_user' => 'hivemq_user',
            'mqtt_password' => 'hivemq_pass',
            'mqtt_use_tls' => '1',
            'alert_voltage_min' => 200,
            'alert_voltage_max' => 240,
            'alert_power_max' => 2200,
        ]);
        $response->assertRedirect();
        
        $this->assertDatabaseHas('system_configs', [
            'key' => 'mqtt_use_tls',
            'value' => '1'
        ]);
        $this->assertDatabaseHas('system_configs', [
            'key' => 'mqtt_user',
            'value' => 'hivemq_user'
        ]);

        // 2. View provisioning page for a device to verify WiFiClientSecure is compiled in code
        $response = $this->actingAs($this->admin)->get("/devices/{$this->device->id}/provisioning");
        $response->assertOk();
        
        $this->device->refresh();
        $this->assertStringContainsString('WiFiClientSecure espClient;', $this->device->provisioning_code);
        $this->assertStringContainsString('espClient.setInsecure();', $this->device->provisioning_code);
        $this->assertStringContainsString('hivemq_user', $this->device->provisioning_code);
    }
}
