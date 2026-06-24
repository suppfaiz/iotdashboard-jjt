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
                    $this->processMessage($topic, $message);
                }, 0);

                $mqtt->loop(true);
                $mqtt->disconnect();
            } catch (\Exception $e) {
                $this->error("MQTT Error: " . $e->getMessage() . ". Reconnecting in 5 seconds...");
                sleep(5);
            }
        }
    }

    public function processMessage(string $topic, string $message)
    {
        $parts = explode('/', $topic);
        if (count($parts) === 3) {
            $actionOrGroup = $parts[1];
            $deviceId = $parts[2];

            $data = json_decode($message, true);
            if (!is_array($data)) {
                return;
            }

            // 1. Handle OTA progress updates
            if ($actionOrGroup === 'ota_status') {
                $progress = $data['progress'] ?? 0;
                $status = $data['status'] ?? 'unknown';
                $msg = $data['message'] ?? '';
                broadcast(new \App\Events\OtaProgressUpdated($deviceId, $progress, $status, $msg));
                return;
            }

            // 2. Handle Historical offline telemetry uploads
            if ($actionOrGroup === 'historical') {
                $timestamp = isset($data['timestamp']) ? intval($data['timestamp']) : time();
                $loggedAt = \Carbon\Carbon::createFromTimestamp($timestamp);
                
                $deviceDbId = Cache::remember("device_db_id:{$deviceId}", now()->addHours(1), function() use ($deviceId) {
                    return \App\Models\Device::where('device_id', $deviceId)->value('id');
                });
                
                if ($deviceDbId) {
                    $voltage = floatval($data['voltage'] ?? 0.0);
                    $current = floatval($data['current'] ?? 0.0);
                    $power = floatval($data['power'] ?? 0.0);
                    $energyVal = floatval($data['energy'] ?? 0.0);
                    
                    // Calculate delta energy using last_historical_energy cache
                    $lastHistEnergyKey = "last_historical_energy:{$deviceId}";
                    $lastHistEnergy = Cache::get($lastHistEnergyKey);
                    if ($lastHistEnergy === null) {
                        $lastHistEnergy = Cache::get("last_energy:{$deviceId}");
                    }

                    $deltaKwh = 0.0;
                    if ($lastHistEnergy !== null) {
                        if ($energyVal >= $lastHistEnergy) {
                            $deltaKwh = $energyVal - $lastHistEnergy;
                        } else {
                            // Reboot occurred during offline period
                            $deltaKwh = $energyVal;
                        }
                    }
                    $deltaKwh = round($deltaKwh, 4);
                    Cache::put($lastHistEnergyKey, $energyVal, now()->addDays(2));
                    Cache::put("last_energy:{$deviceId}", $energyVal, now()->addDays(2));

                    // Daily energy accumulation for the historical date
                    $dateString = $loggedAt->toDateString();
                    $dailyEnergyKeyForDate = "daily_energy:{$deviceId}:{$dateString}";
                    $dailyEnergy = Cache::get($dailyEnergyKeyForDate);
                    if ($dailyEnergy === null) {
                        $dailyEnergy = \App\Models\DailyEnergyLog::where('device_id', $deviceDbId)
                            ->where('date', $dateString)
                            ->value('total_kwh_harian') ?? 0.0;
                    }
                    $dailyEnergy = round(floatval($dailyEnergy) + $deltaKwh, 4);
                    Cache::put($dailyEnergyKeyForDate, $dailyEnergy, now()->addDays(2));

                    if ($dateString === now()->toDateString()) {
                        Cache::put("daily_energy:{$deviceId}", $dailyEnergy, now()->addDays(2));
                        Cache::put("daily_energy_date:{$deviceId}", $dateString, now()->addDays(2));
                    }

                    // Cost calculation for the historical date
                    $tariffWbp = floatval(\App\Models\SystemConfig::where('key', 'pln_tariff_wbp')->value('value') ?? 2000.00);
                    $tariffLwbp = floatval(\App\Models\SystemConfig::where('key', 'pln_tariff_lwbp')->value('value') ?? 1444.70);
                    $wbpStart = \App\Models\SystemConfig::where('key', 'wbp_start')->value('value') ?? '17:00';
                    $wbpEnd = \App\Models\SystemConfig::where('key', 'wbp_end')->value('value') ?? '22:00';
                    
                    $recordTime = $loggedAt->format('H:i');
                    $isWbp = false;
                    if ($wbpStart <= $wbpEnd) {
                        $isWbp = ($recordTime >= $wbpStart && $recordTime <= $wbpEnd);
                    } else {
                        $isWbp = ($recordTime >= $wbpStart || $recordTime <= $wbpEnd);
                    }
                    $activeTariff = $isWbp ? $tariffWbp : $tariffLwbp;
                    
                    $dailyCostKeyForDate = "daily_cost:{$deviceId}:{$dateString}";
                    $dailyCost = Cache::get($dailyCostKeyForDate, 0.0);
                    $dailyCost += $deltaKwh * $activeTariff;
                    $dailyCost = round($dailyCost, 2);
                    Cache::put($dailyCostKeyForDate, $dailyCost, now()->addDays(2));
                    
                    if ($dateString === now()->toDateString()) {
                        Cache::put("daily_cost:{$deviceId}", $dailyCost, now()->addDays(2));
                    }

                    $hourStart = $loggedAt->copy()->startOfHour();
                    \App\Models\HourlyEnergyLog::updateOrCreate(
                        [
                            'device_id' => $deviceDbId,
                            'logged_at' => $hourStart
                        ],
                        [
                            'voltage' => $voltage,
                            'current' => $current,
                            'power' => $power,
                            'energy' => $dailyEnergy,
                        ]
                    );
                    
                    \App\Models\DailyEnergyLog::updateOrCreate(
                        [
                            'device_id' => $deviceDbId,
                            'date' => $dateString
                        ],
                        [
                            'total_kwh_harian' => $dailyEnergy
                        ]
                    );
                }
                return;
            }

            // 3. Handle Standard Telemetry data
            $groupName = $actionOrGroup;
            
            $deviceDbId = Cache::remember("device_db_id:{$deviceId}", now()->addHours(1), function() use ($deviceId) {
                return \App\Models\Device::where('device_id', $deviceId)->value('id');
            });
            
            // Update Cache for metrics
            if (isset($data['voltage'])) Cache::put("voltage:{$deviceId}", $data['voltage'], now()->addDays(2));
            if (isset($data['current'])) Cache::put("current:{$deviceId}", $data['current'], now()->addDays(2));
            if (isset($data['power'])) Cache::put("power:{$deviceId}", $data['power'], now()->addDays(2));
            if (isset($data['ip'])) Cache::put("ip:{$deviceId}", $data['ip'], now()->addDays(2));
            
            if (isset($data['energy'])) {
                $currentEnergy = floatval($data['energy']);
                Cache::put("energy:{$deviceId}", $currentEnergy, now()->addDays(2));

                // Get delta energy since last reading, supporting ESP32 reboots
                $lastEnergyKey = "last_energy:{$deviceId}";
                $lastEnergy = Cache::get($lastEnergyKey);
                $deltaKwh = 0.0;
                if ($lastEnergy !== null) {
                    if ($currentEnergy >= $lastEnergy) {
                        $deltaKwh = $currentEnergy - $lastEnergy;
                    } else {
                        // Reboot detected: currentEnergy starts back from 0,
                        // so the new energy consumed since reboot is currentEnergy.
                        $deltaKwh = $currentEnergy;
                    }
                }
                $deltaKwh = round($deltaKwh, 4);
                Cache::put($lastEnergyKey, $currentEnergy, now()->addDays(2));

                // Retrieve and update daily energy accumulation
                $todayDate = now()->toDateString();
                $dailyEnergyKey = "daily_energy:{$deviceId}";
                $dailyEnergyDateKey = "daily_energy_date:{$deviceId}";
                $lastDailyDate = Cache::get($dailyEnergyDateKey);

                if ($lastDailyDate !== $todayDate) {
                    // Date changed: reset daily energy
                    $dailyEnergy = 0.0;
                    Cache::put($dailyEnergyDateKey, $todayDate, now()->addDays(2));
                } else {
                    $dailyEnergy = Cache::get($dailyEnergyKey);
                    if ($dailyEnergy === null && $deviceDbId) {
                        $dailyEnergy = \App\Models\DailyEnergyLog::where('device_id', $deviceDbId)
                            ->where('date', $todayDate)
                            ->value('total_kwh_harian') ?? 0.0;
                    }
                    $dailyEnergy = floatval($dailyEnergy ?? 0.0);
                }

                $dailyEnergy += $deltaKwh;
                $dailyEnergy = round($dailyEnergy, 4);
                Cache::put($dailyEnergyKey, $dailyEnergy, now()->addDays(2));
                
                // Override the energy value sent to the frontend/broadcast with daily energy
                $data['energy'] = $dailyEnergy;
                
                // Multi-tariff ToU Cost Calculation (Delta Based)
                $tariffWbp = floatval(\App\Models\SystemConfig::where('key', 'pln_tariff_wbp')->value('value') ?? 2000.00);
                $tariffLwbp = floatval(\App\Models\SystemConfig::where('key', 'pln_tariff_lwbp')->value('value') ?? 1444.70);
                $wbpStart = \App\Models\SystemConfig::where('key', 'wbp_start')->value('value') ?? '17:00';
                $wbpEnd = \App\Models\SystemConfig::where('key', 'wbp_end')->value('value') ?? '22:00';
                
                $nowTime = now()->format('H:i');
                $isWbp = false;
                if ($wbpStart <= $wbpEnd) {
                    $isWbp = ($nowTime >= $wbpStart && $nowTime <= $wbpEnd);
                } else {
                    $isWbp = ($nowTime >= $wbpStart || $nowTime <= $wbpEnd);
                }
                $activeTariff = $isWbp ? $tariffWbp : $tariffLwbp;
                
                $dailyCostKey = "daily_cost:{$deviceId}:" . $todayDate;
                if ($lastDailyDate !== $todayDate) {
                    $dailyCost = 0.0;
                } else {
                    $dailyCost = Cache::get($dailyCostKey);
                    $dailyCost = floatval($dailyCost ?? 0.0);
                }
                
                $deltaCost = $deltaKwh * $activeTariff;
                $dailyCost += $deltaCost;
                $dailyCost = round($dailyCost, 2);
                
                Cache::put($dailyCostKey, $dailyCost, now()->addDays(2));
                Cache::put("daily_cost:{$deviceId}", $dailyCost, now()->addDays(2));
                
                $data['cost'] = $dailyCost;
            }
            
            Cache::put("last_seen:{$deviceId}", now()->timestamp, now()->addDays(2));

            // Database persistence
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

                // 2. Daily log
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

            // Telegram Alerts threshold evaluation
            $botToken = \App\Models\SystemConfig::where('key', 'telegram_bot_token')->value('value');
            $chatId = \App\Models\SystemConfig::where('key', 'telegram_chat_id')->value('value');
            
            if (!empty($botToken) && !empty($chatId)) {
                $vMin = floatval(\App\Models\SystemConfig::where('key', 'alert_voltage_min')->value('value') ?? 200.00);
                $vMax = floatval(\App\Models\SystemConfig::where('key', 'alert_voltage_max')->value('value') ?? 240.00);
                $pMax = floatval(\App\Models\SystemConfig::where('key', 'alert_power_max')->value('value') ?? 2200.00);
                
                $voltage = floatval($data['voltage'] ?? 0);
                $power = floatval($data['power'] ?? 0);
                
                $deviceName = Cache::remember("device_name:{$deviceId}", now()->addHours(24), function() use ($deviceId) {
                    return \App\Models\Device::where('device_id', $deviceId)->value('name') ?? $deviceId;
                });
                
                $messageText = '';
                $cacheKey = '';
                
                if ($voltage > 0 && ($voltage < $vMin || $voltage > $vMax)) {
                    $cacheKey = "telegram_alert_voltage:{$deviceId}";
                    if (!Cache::has($cacheKey)) {
                        $messageText = "⚠️ WARNING: <b>{$deviceName}</b>\nVoltase tidak stabil: <b>{$voltage} V</b>\n(Batas aman: {$vMin} - {$vMax} V)";
                    }
                } elseif ($power > $pMax) {
                    $cacheKey = "telegram_alert_power:{$deviceId}";
                    if (!Cache::has($cacheKey)) {
                        $messageText = "⚠️ WARNING: <b>{$deviceName}</b>\nKonsumsi daya melebihi batas: <b>{$power} W</b>\n(Batas aman: maks {$pMax} W)";
                    }
                }
                
                if (!empty($messageText) && !empty($cacheKey)) {
                    Cache::put($cacheKey, true, now()->addHour());
                    $this->sendTelegramNotification($botToken, $chatId, $messageText);
                }
            }

            // Broadcast telemetry updated to WebSockets
            broadcast(new \App\Events\TelemetryUpdated($deviceId, $data));
        }
    }

    protected function sendTelegramNotification($botToken, $chatId, $messageText)
    {
        try {
            $url = "https://api.telegram.org/bot{$botToken}/sendMessage";
            $client = new \GuzzleHttp\Client();
            $client->post($url, [
                'json' => [
                    'chat_id' => $chatId,
                    'text' => $messageText,
                    'parse_mode' => 'HTML'
                ]
            ]);
            $this->info("Telegram Alert Sent: " . $messageText);
        } catch (\Exception $e) {
            $this->error("Failed to send Telegram notification: " . $e->getMessage());
        }
    }
}
