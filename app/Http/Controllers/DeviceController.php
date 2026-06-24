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
        $mqtt_host = \App\Models\SystemConfig::where('key', 'mqtt_host')->value('value');
        if (empty($mqtt_host)) {
            $envHost = env('MQTT_HOST');
            if (empty($envHost) || $envHost === 'broker.emqx.io' || $envHost === 'mqtt') {
                $mqtt_host = app()->runningInConsole() ? '127.0.0.1' : request()->getHost();
            } else {
                $mqtt_host = $envHost;
            }
        }
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
        if (!$oldCode) {
            return;
        }

        $mqtt_host = \App\Models\SystemConfig::where('key', 'mqtt_host')->value('value');
        if (empty($mqtt_host)) {
            $envHost = env('MQTT_HOST');
            if (empty($envHost) || $envHost === 'broker.emqx.io' || $envHost === 'mqtt') {
                $mqtt_host = app()->runningInConsole() ? '127.0.0.1' : request()->getHost();
            } else {
                $mqtt_host = $envHost;
            }
        }
        $mqtt_port = \App\Models\SystemConfig::where('key', 'mqtt_port')->value('value') ?? env('MQTT_PORT', 1883);
        $mqtt_user = \App\Models\SystemConfig::where('key', 'mqtt_user')->value('value') ?? env('MQTT_USERNAME', '');
        $mqtt_password = \App\Models\SystemConfig::where('key', 'mqtt_password')->value('value') ?? env('MQTT_PASSWORD', '');

        $oldMqttHost = '';
        if (preg_match('/const char\* mqtt_server = "(.*?)";/', $oldCode, $matchesHost)) {
            $oldMqttHost = $matchesHost[1];
        }

        $oldMqttPort = 1883;
        if (preg_match('/const int mqtt_port = (\d+);/', $oldCode, $matchesPort)) {
            $oldMqttPort = intval($matchesPort[1]);
        }

        if (strpos($oldCode, 'LittleFS') === false || $oldMqttHost !== $mqtt_host || $oldMqttPort !== $mqtt_port) {
            $wifi_ssid = 'YOUR_WIFI_SSID';
            if (preg_match('/const char\* ssid = "(.*?)";/', $oldCode, $matchesSsid)) {
                $wifi_ssid = $matchesSsid[1];
            }
            
            $wifi_password = 'YOUR_WIFI_PASSWORD';
            if (preg_match('/const char\* password = "(.*?)";/', $oldCode, $matchesPassword)) {
                $wifi_password = $matchesPassword[1];
            }

            if (preg_match('/const char\* mqtt_user = "(.*?)";/', $oldCode, $matchesUser) && !empty($matchesUser[1])) {
                $mqtt_user = $matchesUser[1];
            }
            if (preg_match('/const char\* mqtt_password = "(.*?)";/', $oldCode, $matchesPass) && !empty($matchesPass[1])) {
                $mqtt_password = $matchesPass[1];
            }

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

    public function uploadFirmware(Request $request, Device $device)
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

            dispatch(function() use ($server, $port, $clientId, $username, $password, $device, $url) {
                try {
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
                } catch (\Exception $e) {
                    \Log::error("MQTT OTA command failed: " . $e->getMessage());
                }
            })->afterResponse();
            
            return redirect()->back()->with('success', 'OTA Update command sent via MQTT!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to schedule MQTT command: ' . $e->getMessage());
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

            dispatch(function() use ($server, $port, $clientId, $username, $password, $device) {
                try {
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
                } catch (\Exception $e) {
                    \Log::error("MQTT Reset Energy command failed: " . $e->getMessage());
                }
            })->afterResponse();

            // Reset the cache for this device so it immediately goes to 0 on the dashboard
            \Illuminate\Support\Facades\Cache::put("energy:{$device->device_id}", 0.0, now()->addDays(2));
            \Illuminate\Support\Facades\Cache::put("daily_energy:{$device->device_id}", 0.0, now()->addDays(2));
            \Illuminate\Support\Facades\Cache::put("last_energy:{$device->device_id}", 0.0, now()->addDays(2));
            \Illuminate\Support\Facades\Cache::put("last_historical_energy:{$device->device_id}", 0.0, now()->addDays(2));
            
            $todayDate = now()->toDateString();
            \Illuminate\Support\Facades\Cache::put("daily_energy:{$device->device_id}:{$todayDate}", 0.0, now()->addDays(2));
            \Illuminate\Support\Facades\Cache::put("daily_cost:{$device->device_id}", 0.0, now()->addDays(2));
            \Illuminate\Support\Facades\Cache::put("daily_cost:{$device->device_id}:{$todayDate}", 0.0, now()->addDays(2));
            \Illuminate\Support\Facades\Cache::put("daily_energy_date:{$device->device_id}", $todayDate, now()->addDays(2));

            \App\Models\DailyEnergyLog::updateOrCreate(
                ['device_id' => $device->id, 'date' => $todayDate],
                ['total_kwh_harian' => 0.0]
            );
            
            // Also broadcast the update so the UI updates instantly
            broadcast(new \App\Events\TelemetryUpdated($device->device_id, [
                'energy' => 0.000,
                'cost' => 0.00
            ]));
            
            return redirect()->back()->with('success', 'Reset energy command sent to device!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to schedule reset command: ' . $e->getMessage());
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

            dispatch(function() use ($server, $port, $clientId, $username, $password, $device) {
                try {
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
                } catch (\Exception $e) {
                    \Log::error("MQTT Restart command failed: " . $e->getMessage());
                }
            })->afterResponse();
            
            return redirect()->back()->with('success', 'Restart command sent to device!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to schedule restart command: ' . $e->getMessage());
        }
    }

    public function update(Request $request, Device $device)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'voltage_multiplier' => 'required|numeric|min:0.1|max:10.0',
            'current_multiplier' => 'required|numeric|min:0.1|max:10.0',
            'monthly_budget_kwh' => 'nullable|numeric|min:0',
            'monthly_budget_cost' => 'nullable|numeric|min:0',
        ]);

        $device->update([
            'name' => $request->name,
            'voltage_multiplier' => floatval($request->voltage_multiplier),
            'current_multiplier' => floatval($request->current_multiplier),
            'monthly_budget_kwh' => $request->filled('monthly_budget_kwh') ? floatval($request->monthly_budget_kwh) : null,
            'monthly_budget_cost' => $request->filled('monthly_budget_cost') ? floatval($request->monthly_budget_cost) : null,
        ]);

        return redirect()->back()->with('success', 'Device settings updated successfully!');
    }

    public function sendCustomCommand(Request $request, Device $device)
    {
        $request->validate([
            'payload' => 'required|string',
        ]);

        $payload = $request->payload;

        // Ensure payload is valid JSON
        json_decode($payload);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return response()->json(['status' => 'error', 'message' => 'Invalid JSON payload.'], 400);
        }

        try {
            $server   = \App\Models\SystemConfig::where('key', 'mqtt_host')->value('value') ?? env('MQTT_HOST', 'broker.emqx.io');
            $port     = \App\Models\SystemConfig::where('key', 'mqtt_port')->value('value') ?? env('MQTT_PORT', 1883);
            $username = \App\Models\SystemConfig::where('key', 'mqtt_user')->value('value') ?? env('MQTT_USERNAME');
            $password = \App\Models\SystemConfig::where('key', 'mqtt_password')->value('value') ?? env('MQTT_PASSWORD');
            $clientId = env('MQTT_CLIENT_ID', 'laravel_console_' . rand(1000, 9999));

            dispatch(function() use ($server, $port, $clientId, $username, $password, $device, $payload) {
                try {
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
                    $mqtt->publish("cmd/{$device->device_id}", $payload, 0);
                    $mqtt->disconnect();
                } catch (\Exception $e) {
                    \Log::error("MQTT Console command failed: " . $e->getMessage());
                }
            })->afterResponse();

            return response()->json(['status' => 'success', 'message' => 'Command sent successfully via MQTT.']);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Failed to schedule MQTT command: ' . $e->getMessage()], 500);
        }
    }
}
