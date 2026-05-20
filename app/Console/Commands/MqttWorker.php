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
        $server   = \App\Models\SystemConfig::where('key', 'mqtt_host')->value('value') ?? env('MQTT_HOST', 'broker.hivemq.com');
        $port     = \App\Models\SystemConfig::where('key', 'mqtt_port')->value('value') ?? env('MQTT_PORT', 1883);
        $clientId = env('MQTT_CLIENT_ID', 'laravel_worker_' . rand(1000, 9999));

        $mqtt = new MqttClient($server, $port, $clientId);
        
        $connectionSettings = (new ConnectionSettings)
            ->setKeepAliveInterval(60)
            ->setUseTls(false)
            ->setTlsSelfSignedAllowed(false);

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
                        if (isset($data['energy'])) Cache::put("energy:{$deviceId}", $data['energy'], now()->addDays(2));
                        
                        Cache::put("last_seen:{$deviceId}", now()->timestamp, now()->addDays(2));

                        // Broadcast to WebSockets
                        broadcast(new TelemetryUpdated($deviceId, $data));
                    }
                }
            }, 0);

            $mqtt->loop(true);
            $mqtt->disconnect();
        } catch (\Exception $e) {
            $this->error("MQTT Error: " . $e->getMessage());
        }
    }
}
