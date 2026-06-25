<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Device;
use App\Models\Group;
use App\Models\SystemConfig;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SecurityHardeningTest extends TestCase
{
    use RefreshDatabase;

    private $admin;
    private $user;
    private $device;
    private $group;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
        Http::fake();

        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->user = User::factory()->create(['role' => 'user']);
        
        $this->group = Group::create(['name' => 'Security Lab']);
        $this->device = Device::create([
            'device_id' => 'dev_sec123',
            'name' => 'Secured Meter',
            'group_id' => $this->group->id,
            'status' => true,
            'mqtt_topic' => 'telemetry/security/dev_sec123',
            'provisioning_code' => '// secure'
        ]);

        SystemConfig::updateOrCreate(['key' => 'pln_tariff'], ['value' => '1500.00']);
        SystemConfig::updateOrCreate(['key' => 'pln_tariff_wbp'], ['value' => '2000.00']);
        SystemConfig::updateOrCreate(['key' => 'pln_tariff_lwbp'], ['value' => '1000.00']);
        SystemConfig::updateOrCreate(['key' => 'wbp_start'], ['value' => '17:00']);
        SystemConfig::updateOrCreate(['key' => 'wbp_end'], ['value' => '22:00']);
        SystemConfig::updateOrCreate(['key' => 'mqtt_host'], ['value' => '127.0.0.1']);
        SystemConfig::updateOrCreate(['key' => 'mqtt_port'], ['value' => '1883']);
    }

    /**
     * Test 1: Unauthenticated guests are redirected to login.
     */
    public function test_guest_users_cannot_access_any_dashboard_and_iot_routes(): void
    {
        $protectedUrls = [
            '/',
            '/devices',
            "/devices/{$this->device->id}",
            "/devices/{$this->device->id}/ping",
            "/devices/{$this->device->id}/export-csv",
            '/logs',
            '/settings',
            '/reports',
        ];

        foreach ($protectedUrls as $url) {
            $response = $this->get($url);
            $response->assertRedirect('/login');
        }
    }

    /**
     * Test 2: RBAC - Standard users cannot access admin-only routes.
     */
    public function test_standard_users_cannot_access_admin_only_pages(): void
    {
        $adminOnlyPages = [
            '/logs',
            '/settings',
            '/reports',
            '/docs',
            "/devices/{$this->device->id}/provisioning"
        ];

        foreach ($adminOnlyPages as $url) {
            $response = $this->actingAs($this->user)->get($url);
            $response->assertStatus(403);
        }
    }

    /**
     * Test 3: RBAC - Standard users cannot perform admin actions (mutate/control).
     */
    public function test_standard_users_cannot_perform_admin_device_actions(): void
    {
        // 1. Create Device (POST /devices)
        $response = $this->actingAs($this->user)->post('/devices', [
            'name' => 'Hacker Meter',
            'group_id' => $this->group->id,
            'wifi_ssid' => 'HackerSSID',
            'wifi_password' => 'HackerPass'
        ]);
        $response->assertStatus(403);

        // 2. Update Device (PATCH /devices/{id})
        $response = $this->actingAs($this->user)->patch("/devices/{$this->device->id}", [
            'name' => 'Updated by User',
            'voltage_multiplier' => 1.5,
            'current_multiplier' => 1.5
        ]);
        $response->assertStatus(403);

        // 3. Delete Device (DELETE /devices/{id})
        $response = $this->actingAs($this->user)->delete("/devices/{$this->device->id}");
        $response->assertStatus(403);

        // 4. Send Custom Command (POST /devices/{id}/console)
        $response = $this->actingAs($this->user)->post("/devices/{$this->device->id}/console", [
            'payload' => '{"restart":true}'
        ]);
        $response->assertStatus(403);

        // 5. Upload Firmware (POST /devices/{id}/firmware)
        $response = $this->actingAs($this->user)->post("/devices/{$this->device->id}/firmware", [
            'firmware' => UploadedFile::fake()->create('firmware.bin', 100)
        ]);
        $response->assertStatus(403);

        // 6. Trigger OTA (POST /devices/{id}/trigger-ota)
        $response = $this->actingAs($this->user)->post("/devices/{$this->device->id}/trigger-ota");
        $response->assertStatus(403);

        // 7. Reset Energy (POST /devices/{id}/reset-energy)
        $response = $this->actingAs($this->user)->post("/devices/{$this->device->id}/reset-energy");
        $response->assertStatus(403);

        // 8. Restart Device (POST /devices/{id}/restart)
        $response = $this->actingAs($this->user)->post("/devices/{$this->device->id}/restart");
        $response->assertStatus(403);
    }

    /**
     * Test 4: Firmware upload size verification (Hardening check).
     */
    public function test_firmware_upload_size_validation_limits_to_10mb(): void
    {
        // 1. Valid size: 2MB (2048 KB) -> should succeed and redirect back (with success or at least not fail validation)
        $validFirmware = UploadedFile::fake()->create('firmware_valid.bin', 2048);
        $response = $this->actingAs($this->admin)->post("/devices/{$this->device->id}/firmware", [
            'firmware' => $validFirmware
        ]);
        $response->assertRedirect();
        $response->assertSessionHasNoErrors();

        // 2. Invalid size: 11MB (11264 KB) -> should fail validation
        $invalidFirmware = UploadedFile::fake()->create('firmware_huge.bin', 11264);
        $response = $this->actingAs($this->admin)->post("/devices/{$this->device->id}/firmware", [
            'firmware' => $invalidFirmware
        ]);
        $response->assertSessionHasErrors('firmware');
    }

    /**
     * Test 5: Validation for custom command payload.
     */
    public function test_send_custom_command_payload_must_be_valid_json(): void
    {
        // 1. Invalid JSON
        $response = $this->actingAs($this->admin)->post("/devices/{$this->device->id}/console", [
            'payload' => 'not-a-json-string'
        ]);
        $response->assertStatus(400);
        $response->assertJsonFragment(['status' => 'error', 'message' => 'Invalid JSON payload.']);

        // 2. Valid JSON
        $response = $this->actingAs($this->admin)->post("/devices/{$this->device->id}/console", [
            'payload' => '{"cmd":"calibrate","voltage":220}'
        ]);
        $response->assertStatus(200);
        $response->assertJsonFragment(['status' => 'success']);
    }

    /**
     * Test 6: Validation check for system configurations input.
     */
    public function test_settings_update_rejects_invalid_values(): void
    {
        // 1. Test invalid negative values or string in numeric fields
        $response = $this->actingAs($this->admin)->put('/settings', [
            'pln_tariff' => -10, // Invalid negative
            'pln_tariff_wbp' => 'abc', // Invalid string
            'pln_tariff_lwbp' => 1000,
            'wbp_start' => '17:00',
            'wbp_end' => '22:00',
            'mqtt_host' => '127.0.0.1',
            'mqtt_port' => 999999, // Invalid port number (>65535)
            'alert_voltage_min' => 180,
            'alert_voltage_max' => 240,
            'alert_power_max' => 2200,
        ]);
        $response->assertSessionHasErrors(['pln_tariff', 'pln_tariff_wbp', 'mqtt_port']);

        // 2. Test invalid time format for WBP
        $response = $this->actingAs($this->admin)->put('/settings', [
            'pln_tariff' => 1500,
            'pln_tariff_wbp' => 2000,
            'pln_tariff_lwbp' => 1000,
            'wbp_start' => '17-00', // Invalid separator
            'wbp_end' => '2200', // Missing colon
            'mqtt_host' => '127.0.0.1',
            'mqtt_port' => 1883,
            'alert_voltage_min' => 180,
            'alert_voltage_max' => 240,
            'alert_power_max' => 2200,
        ]);
        $response->assertSessionHasErrors(['wbp_start', 'wbp_end']);
    }

    /**
     * Test 7: CSRF protection activation.
     */
    public function test_csrf_protection_is_active_for_state_changing_requests(): void
    {
        // Di Laravel Testing, middleware VerifyCsrfToken dinonaktifkan secara default saat pengujian,
        // tetapi kita bisa menguji apakah middleware VerifyCsrfToken terdaftar pada rute web post/patch/delete.
        
        $route = route('devices.store');
        $routeCollection = app('router')->getRoutes();
        $routeObject = $routeCollection->getByAction('App\Http\Controllers\DeviceController@store');

        $this->assertNotNull($routeObject, 'Device store route should exist.');
        $middleware = $routeObject->gatherMiddleware();
        
        // Memastikan middleware 'web' aktif pada route tersebut, karena 'web' group berisi VerifyCsrfToken
        $this->assertTrue(in_array('web', $middleware), 'Web middleware group should be applied to store route.');
    }
}
