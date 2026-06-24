<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Device;
use App\Models\Group;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RbacTest extends TestCase
{
    use RefreshDatabase;

    private $admin;
    private $user;
    private $device;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->user = User::factory()->create(['role' => 'user']);
        
        $group = Group::create(['name' => 'Test Group']);
        $this->device = Device::create([
            'device_id' => 'dev_test123',
            'name' => 'Test Meter',
            'group_id' => $group->id,
            'status' => true,
            'mqtt_topic' => 'telemetry/test-group/dev_test123',
            'provisioning_code' => '// test code'
        ]);
    }

    public function test_standard_user_can_access_devices_list(): void
    {
        $response = $this
            ->actingAs($this->user)
            ->get('/devices');

        $response->assertOk();
    }

    public function test_standard_user_can_access_device_detail(): void
    {
        $response = $this
            ->actingAs($this->user)
            ->get("/devices/{$this->device->id}");

        $response->assertOk();
    }

    public function test_standard_user_is_forbidden_from_device_control_actions(): void
    {
        // 1. Restart
        $response = $this
            ->actingAs($this->user)
            ->post("/devices/{$this->device->id}/restart");
        $response->assertStatus(403);

        // 2. Reset Energy
        $response = $this
            ->actingAs($this->user)
            ->post("/devices/{$this->device->id}/reset-energy");
        $response->assertStatus(403);

        // 3. Trigger OTA
        $response = $this
            ->actingAs($this->user)
            ->post("/devices/{$this->device->id}/trigger-ota");
        $response->assertStatus(403);

        // 4. Provisioning Code
        $response = $this
            ->actingAs($this->user)
            ->get("/devices/{$this->device->id}/provisioning");
        $response->assertStatus(403);

        // 5. Settings
        $response = $this
            ->actingAs($this->user)
            ->get('/settings');
        $response->assertStatus(403);
    }

    public function test_admin_user_can_access_settings_and_device_actions(): void
    {
        // Settings page
        $response = $this
            ->actingAs($this->admin)
            ->get('/settings');
        $response->assertOk();

        // Provisioning page
        $response = $this
            ->actingAs($this->admin)
            ->get("/devices/{$this->device->id}/provisioning");
        $response->assertOk();

        // Control actions (should pass middleware, but may redirect back if MQTT broker is down)
        $response = $this
            ->actingAs($this->admin)
            ->post("/devices/{$this->device->id}/restart");
        
        // Assert it did not return 403 (it should redirect back due to MQTT connection fail in test env)
        $this->assertNotEquals(403, $response->getStatusCode());
    }
}
