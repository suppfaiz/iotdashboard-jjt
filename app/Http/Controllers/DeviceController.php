<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Device;
use App\Models\Group;
use Illuminate\Support\Str;

class DeviceController extends Controller
{
    public function index()
    {
        $devices = Device::with('group')->get();
        $groups = Group::all();
        
        $metrics = [];
        foreach ($devices as $device) {
            $lastSeen = \Illuminate\Support\Facades\Cache::get("last_seen:{$device->device_id}", 0);
            $isOnline = ($lastSeen > 0 && (now()->timestamp - $lastSeen) < 15);
            
            $metrics[$device->id] = [
                'voltage' => \Illuminate\Support\Facades\Cache::get("voltage:{$device->device_id}", 0),
                'current' => \Illuminate\Support\Facades\Cache::get("current:{$device->device_id}", 0),
                'power' => \Illuminate\Support\Facades\Cache::get("power:{$device->device_id}", 0),
                'energy' => \Illuminate\Support\Facades\Cache::get("daily_energy:{$device->device_id}", 0),
                'status' => $isOnline ? 'Online' : 'Offline',
                'last_seen' => $lastSeen,
            ];
        }

        return view('devices.index', compact('devices', 'groups', 'metrics'));
    }

    public function create()
    {
        $groups = Group::all();
        return view('devices.create', compact('groups'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'group_id' => 'required|exists:groups,id',
            'wifi_ssid' => 'required|string|max:255',
            'wifi_password' => 'required|string|max:255',
        ]);

        $group = Group::findOrFail($request->group_id);
        $deviceId = 'dev_' . Str::random(8);
        $mqttTopic = 'telemetry/' . Str::slug($group->name) . '/' . $deviceId;

        $device = new Device([
            'device_id' => $deviceId,
            'name' => $request->name,
            'group_id' => $group->id,
            'status' => true,
            'mqtt_topic' => $mqttTopic,
        ]);

        $wifi_ssid = $request->wifi_ssid;
        $wifi_password = $request->wifi_password;
        $mqtt_host = \App\Models\SystemConfig::where('key', 'mqtt_host')->value('value') ?? env('MQTT_HOST', 'broker.emqx.io');
        $mqtt_port = \App\Models\SystemConfig::where('key', 'mqtt_port')->value('value') ?? env('MQTT_PORT', 1883);
        $mqtt_user = \App\Models\SystemConfig::where('key', 'mqtt_user')->value('value') ?? env('MQTT_USERNAME', '');
        $mqtt_password = \App\Models\SystemConfig::where('key', 'mqtt_password')->value('value') ?? env('MQTT_PASSWORD', '');

        $code = view('devices.code_template', compact('device', 'wifi_ssid', 'wifi_password', 'mqtt_host', 'mqtt_port', 'mqtt_user', 'mqtt_password'))->render();
        $device->provisioning_code = $code;
        $device->save();

        return redirect()->route('devices.provisioning', $device->id);
    }

    public function provisioning(Device $device)
    {
        $this->ensureProvisioningCodeUpToDate($device);
        return view('devices.provisioning', compact('device'));
    }

    public function show(Device $device)
    {
        // Load the device details
        $device->load('group');
        $this->ensureProvisioningCodeUpToDate($device);
        
        // Fetch latest metrics from cache
        $metrics = [
            'voltage' => \Illuminate\Support\Facades\Cache::get("voltage:{$device->device_id}", 0),
            'current' => \Illuminate\Support\Facades\Cache::get("current:{$device->device_id}", 0),
            'power' => \Illuminate\Support\Facades\Cache::get("power:{$device->device_id}", 0),
            'energy' => \Illuminate\Support\Facades\Cache::get("daily_energy:{$device->device_id}", 0),
        ];

        $plnTariff = \App\Models\SystemConfig::where('key', 'pln_tariff')->value('value') ?? 1444.70;

        return view('devices.show', compact('device', 'metrics', 'plnTariff'));
    }

    protected function ensureProvisioningCodeUpToDate(Device $device)
    {
        $oldCode = $device->provisioning_code;
        if ($oldCode && strpos($oldCode, 'mqtt_user') === false) {
            $wifi_ssid = 'YOUR_WIFI_SSID';
            if (preg_match('/const char\* ssid = "(.*?)";/', $oldCode, $matchesSsid)) {
                $wifi_ssid = $matchesSsid[1];
            }
            
            $wifi_password = 'YOUR_WIFI_PASSWORD';
            if (preg_match('/const char\* password = "(.*?)";/', $oldCode, $matchesPassword)) {
                $wifi_password = $matchesPassword[1];
            }

            $mqtt_host = \App\Models\SystemConfig::where('key', 'mqtt_host')->value('value') ?? env('MQTT_HOST', 'broker.emqx.io');
            $mqtt_port = \App\Models\SystemConfig::where('key', 'mqtt_port')->value('value') ?? env('MQTT_PORT', 1883);
            $mqtt_user = \App\Models\SystemConfig::where('key', 'mqtt_user')->value('value') ?? env('MQTT_USERNAME', '');
            $mqtt_password = \App\Models\SystemConfig::where('key', 'mqtt_password')->value('value') ?? env('MQTT_PASSWORD', '');

            $code = view('devices.code_template', compact('device', 'wifi_ssid', 'wifi_password', 'mqtt_host', 'mqtt_port', 'mqtt_user', 'mqtt_password'))->render();
            $device->provisioning_code = $code;
            $device->save();
        }
    }


    public function ping(Device $device)
    {
        $lastSeen = \Illuminate\Support\Facades\Cache::get("last_seen:{$device->device_id}", 0);
        $diff = now()->timestamp - $lastSeen;
        
        if ($lastSeen === 0) {
            return response()->json(['status' => 'offline', 'message' => 'No data received yet.', 'diff' => null]);
        } elseif ($diff < 15) {
            return response()->json(['status' => 'online', 'message' => "Device is active. Last data received {$diff} seconds ago.", 'diff' => $diff]);
        } else {
            return response()->json(['status' => 'offline', 'message' => "Device is inactive. Last data received {$diff} seconds ago.", 'diff' => $diff]);
        }
    }

    public function destroy(Device $device)
    {
        $device->delete();
        return redirect()->route('dashboard')->with('success', 'Device deleted successfully.');
    }

    public function uploadFirmware(\Illuminate\Http\Request $request, Device $device)
    {
        $request->validate([
            'firmware' => 'required|file' // usually bins might not have mime check working well
        ]);

        if ($request->hasFile('firmware')) {
            $path = $request->file('firmware')->storeAs('firmwares', "firmware_{$device->device_id}_" . time() . ".bin", 'public');
            $device->firmware_path = $path;
            $device->save();
        }

        return redirect()->back()->with('success', 'Firmware uploaded successfully. Ready to push OTA.');
    }

    public function triggerOta(Device $device)
    {
        if (!$device->firmware_path) {
            return redirect()->back()->with('error', 'No firmware uploaded yet.');
        }

        $url = asset('storage/' . $device->firmware_path);
        
        try {
            $server   = \App\Models\SystemConfig::where('key', 'mqtt_host')->value('value') ?? env('MQTT_HOST', 'broker.emqx.io');
            $port     = \App\Models\SystemConfig::where('key', 'mqtt_port')->value('value') ?? env('MQTT_PORT', 1883);
            $username = \App\Models\SystemConfig::where('key', 'mqtt_user')->value('value') ?? env('MQTT_USERNAME');
            $password = \App\Models\SystemConfig::where('key', 'mqtt_password')->value('value') ?? env('MQTT_PASSWORD');
            $clientId = env('MQTT_CLIENT_ID', 'laravel_ota_' . rand(1000, 9999));

            $mqtt = new \PhpMqtt\Client\MqttClient($server, $port, $clientId);
            $connectionSettings = (new \PhpMqtt\Client\ConnectionSettings)
                ->setKeepAliveInterval(60)
                ->setUseTls(false);

            if (!empty($username)) {
                $connectionSettings->setUsername($username);
            }
            if (!empty($password)) {
                $connectionSettings->setPassword($password);
            }
                
            $mqtt->connect($connectionSettings, true);
            
            $payload = json_encode([
                'cmd' => 'update_firmware',
                'url' => $url
            ]);
            
            $mqtt->publish("cmd/{$device->device_id}", $payload, 0);
            $mqtt->disconnect();
            
            return redirect()->back()->with('success', 'OTA Update command sent via MQTT!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to send MQTT command: ' . $e->getMessage());
        }
    }

    public function resetEnergy(Device $device)
    {
        try {
            $server   = \App\Models\SystemConfig::where('key', 'mqtt_host')->value('value') ?? env('MQTT_HOST', 'broker.emqx.io');
            $port     = \App\Models\SystemConfig::where('key', 'mqtt_port')->value('value') ?? env('MQTT_PORT', 1883);
            $username = \App\Models\SystemConfig::where('key', 'mqtt_user')->value('value') ?? env('MQTT_USERNAME');
            $password = \App\Models\SystemConfig::where('key', 'mqtt_password')->value('value') ?? env('MQTT_PASSWORD');
            $clientId = env('MQTT_CLIENT_ID', 'laravel_reset_' . rand(1000, 9999));

            $mqtt = new \PhpMqtt\Client\MqttClient($server, $port, $clientId);
            $connectionSettings = (new \PhpMqtt\Client\ConnectionSettings)
                ->setKeepAliveInterval(60)
                ->setUseTls(false);

            if (!empty($username)) {
                $connectionSettings->setUsername($username);
            }
            if (!empty($password)) {
                $connectionSettings->setPassword($password);
            }
                
            $mqtt->connect($connectionSettings, true);
            
            $payload = json_encode([
                'cmd' => 'reset_energy'
            ]);
            
            $mqtt->publish("cmd/{$device->device_id}", $payload, 0);
            $mqtt->disconnect();

            // Reset the cache for this device so it immediately goes to 0 on the dashboard
            \Illuminate\Support\Facades\Cache::put("energy:{$device->device_id}", 0, now()->addDays(2));
            
            // Also broadcast the update so the UI updates instantly
            broadcast(new \App\Events\TelemetryUpdated($device->device_id, [
                'energy' => 0.000
            ]));
            
            return redirect()->back()->with('success', 'Reset energy command sent to device!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to send reset command: ' . $e->getMessage());
        }
    }

    public function restart(Device $device)
    {
        try {
            $server   = \App\Models\SystemConfig::where('key', 'mqtt_host')->value('value') ?? env('MQTT_HOST', 'broker.emqx.io');
            $port     = \App\Models\SystemConfig::where('key', 'mqtt_port')->value('value') ?? env('MQTT_PORT', 1883);
            $username = \App\Models\SystemConfig::where('key', 'mqtt_user')->value('value') ?? env('MQTT_USERNAME');
            $password = \App\Models\SystemConfig::where('key', 'mqtt_password')->value('value') ?? env('MQTT_PASSWORD');
            $clientId = env('MQTT_CLIENT_ID', 'laravel_restart_' . rand(1000, 9999));

            $mqtt = new \PhpMqtt\Client\MqttClient($server, $port, $clientId);
            $connectionSettings = (new \PhpMqtt\Client\ConnectionSettings)
                ->setKeepAliveInterval(60)
                ->setUseTls(false);

            if (!empty($username)) {
                $connectionSettings->setUsername($username);
            }
            if (!empty($password)) {
                $connectionSettings->setPassword($password);
            }
                
            $mqtt->connect($connectionSettings, true);
            
            $payload = json_encode([
                'cmd' => 'restart'
            ]);
            
            $mqtt->publish("cmd/{$device->device_id}", $payload, 0);
            $mqtt->disconnect();
            
            return redirect()->back()->with('success', 'Restart command sent to device!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to send restart command: ' . $e->getMessage());
        }
    }
}
