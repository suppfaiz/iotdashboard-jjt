<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use PhpMqtt\Client\MqttClient;
use PhpMqtt\Client\ConnectionSettings;
use App\Events\TelemetryUpdated;
use Illuminate\Support\Facades\Cache;

#[Signature('mqtt:listen')]
#[Description('Listen to MQTT broker for telemetry data')]
class MqttWorker extends Command
{
    public function handle()
    {
        $server   = \App\Models\SystemConfig::where('key', 'mqtt_host')->value('value') ?? env('MQTT_HOST', 'broker.emqx.io');
        $port     = \App\Models\SystemConfig::where('key', 'mqtt_port')->value('value') ?? env('MQTT_PORT', 1883);
        $username = \App\Models\SystemConfig::where('key', 'mqtt_user')->value('value') ?? env('MQTT_USERNAME');
        $password = \App\Models\SystemConfig::where('key', 'mqtt_password')->value('value') ?? env('MQTT_PASSWORD');
        
        $connectionSettings = (new ConnectionSettings)
            ->setKeepAliveInterval(60)
            ->setUseTls(false)
            ->setTlsSelfSignedAllowed(false);

        if (!empty($username)) {
            $connectionSettings->setUsername($username);
        }
        if (!empty($password)) {
            $connectionSettings->setPassword($password);
        }

        $this->info("Starting MQTT listener daemon...");

        while (true) {
            $clientId = env('MQTT_CLIENT_ID', 'laravel_worker_' . rand(1000, 9999));
            $mqtt = new MqttClient($server, $port, $clientId);

            try {
                $mqtt->connect($connectionSettings, true);
                $this->info("Connected to MQTT broker {$server}:{$port}");

                $mqtt->subscribe('telemetry/+/+', function (string $topic, string $message, bool $retained) {
                    $parts = explode('/', $topic);
                    if (count($parts) === 3) {
                        $groupName = $parts[1];
                        $deviceId = $parts[2];

                        $data = json_decode($message, true);
                        if (is_array($data)) {
                            // Update Cache for metrics
                            if (isset($data['voltage'])) Cache::put("voltage:{$deviceId}", $data['voltage'], now()->addDays(2));
                            if (isset($data['current'])) Cache::put("current:{$deviceId}", $data['current'], now()->addDays(2));
                            if (isset($data['power'])) Cache::put("power:{$deviceId}", $data['power'], now()->addDays(2));
                            if (isset($data['ip'])) Cache::put("ip:{$deviceId}", $data['ip'], now()->addDays(2));
                            if (isset($data['energy'])) {
                                $currentEnergy = floatval($data['energy']);
                                Cache::put("energy:{$deviceId}", $currentEnergy, now()->addDays(2));

                                // Calculate daily energy consumption
                                $startOfDayCacheKey = "energy_start_of_day:{$deviceId}:" . now()->toDateString();
                                $energyStart = Cache::get($startOfDayCacheKey);

                                if ($energyStart === null || $currentEnergy < $energyStart) {
                                    Cache::put($startOfDayCacheKey, $currentEnergy, now()->addDays(2));
                                    $energyStart = $currentEnergy;
                                }

                                $dailyEnergy = $currentEnergy - $energyStart;
                                Cache::put("daily_energy:{$deviceId}", $dailyEnergy, now()->addDays(2));
                                
                                // Override the energy value sent to the frontend/broadcast with daily energy
                                $data['energy'] = $dailyEnergy;
                            }
                            
                            Cache::put("last_seen:{$deviceId}", now()->timestamp, now()->addDays(2));

                            // Database persistence: Cache the device database ID to prevent querying the database every 2 seconds
                            $deviceDbId = Cache::remember("device_db_id:{$deviceId}", now()->addHours(1), function() use ($deviceId) {
                                return \App\Models\Device::where('device_id', $deviceId)->value('id');
                            });

                            if ($deviceDbId) {
                                // 1. Hourly log: Log once per hour
                                $hourStart = now()->startOfHour();
                                $hourStartString = $hourStart->format('Y-m-d H:00:00');
                                $hourlyCacheKey = "hourly_logged:{$deviceId}:{$hourStartString}";

                                if (!Cache::has($hourlyCacheKey)) {
                                    $voltage = floatval($data['voltage'] ?? Cache::get("voltage:{$deviceId}", 0.00));
                                    $current = floatval($data['current'] ?? Cache::get("current:{$deviceId}", 0.0000));
                                    $power = floatval($data['power'] ?? Cache::get("power:{$deviceId}", 0.00));
                                    $energyVal = floatval($dailyEnergy ?? Cache::get("daily_energy:{$deviceId}", 0.0000));

                                    \App\Models\HourlyEnergyLog::updateOrCreate(
                                        [
                                            'device_id' => $deviceDbId,
                                            'logged_at' => $hourStart
                                        ],
                                        [
                                            'voltage' => $voltage,
                                            'current' => $current,
                                            'power' => $power,
                                            'energy' => $energyVal,
                                        ]
                                    );
                                    Cache::put($hourlyCacheKey, true, now()->addDays(2));
                                }

                                // 2. Daily log: Update or create daily total (throttled to limit DB writes)
                                $dailyEnergyVal = isset($dailyEnergy) ? floatval($dailyEnergy) : floatval(Cache::get("daily_energy:{$deviceId}", 0.0000));
                                $lastLoggedDailyEnergyKey = "last_logged_daily_energy:{$deviceId}";
                                $lastLoggedDailyEnergy = Cache::get($lastLoggedDailyEnergyKey);
                                
                                if ($lastLoggedDailyEnergy === null || abs($dailyEnergyVal - $lastLoggedDailyEnergy) > 0.0001) {
                                    \App\Models\DailyEnergyLog::updateOrCreate(
                                        ['device_id' => $deviceDbId, 'date' => now()->toDateString()],
                                        ['total_kwh_harian' => $dailyEnergyVal]
                                    );
                                    Cache::put($lastLoggedDailyEnergyKey, $dailyEnergyVal, now()->addDays(2));
                                }
                            }

                            // Broadcast to WebSockets
                            broadcast(new TelemetryUpdated($deviceId, $data));
                        }
                    }
                }, 0);

                $mqtt->loop(true);
                $mqtt->disconnect();
            } catch (\Exception $e) {
                $this->error("MQTT Error: " . $e->getMessage() . ". Reconnecting in 5 seconds...");
                sleep(5);
            }
        }
    }
}
