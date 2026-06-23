<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\Device;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        try {
            // Update all devices to use the new C++ template with MQTT authentication
            $devices = Device::all();
            foreach ($devices as $device) {
                $oldCode = $device->provisioning_code;
                if ($oldCode && strpos($oldCode, 'mqtt_user') === false) {
                    // Extract SSID
                    $wifi_ssid = 'YOUR_WIFI_SSID';
                    if (preg_match('/const char\* ssid = "(.*?)";/', $oldCode, $matchesSsid)) {
                        $wifi_ssid = $matchesSsid[1];
                    }
                    
                    // Extract Password
                    $wifi_password = 'YOUR_WIFI_PASSWORD';
                    if (preg_match('/const char\* password = "(.*?)";/', $oldCode, $matchesPassword)) {
                        $wifi_password = $matchesPassword[1];
                    }

                    $mqtt_host = \App\Models\SystemConfig::where('key', 'mqtt_host')->value('value') ?? env('MQTT_HOST', 'broker.emqx.io');
                    $mqtt_port = \App\Models\SystemConfig::where('key', 'mqtt_port')->value('value') ?? env('MQTT_PORT', 1883);
                    $mqtt_user = \App\Models\SystemConfig::where('key', 'mqtt_user')->value('value') ?? env('MQTT_USERNAME', '');
                    $mqtt_password = \App\Models\SystemConfig::where('key', 'mqtt_password')->value('value') ?? env('MQTT_PASSWORD', '');

                    // Render with new template
                    $code = view('devices.code_template', compact('device', 'wifi_ssid', 'wifi_password', 'mqtt_host', 'mqtt_port', 'mqtt_user', 'mqtt_password'))->render();
                    $device->provisioning_code = $code;
                    $device->save();
                }
            }
        } catch (\Exception $e) {
            // Prevent migration failure if database table or class is missing
            Log::error('Failed to upgrade provisioning codes: ' . $e->getMessage());
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Content upgrade is non-destructive, no rollback needed
    }
};
