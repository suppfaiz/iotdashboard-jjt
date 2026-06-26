<?php

namespace App\Jobs;

use App\Models\Device;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use PhpMqtt\Client\MqttClient;
use PhpMqtt\Client\ConnectionSettings;
use Illuminate\Support\Facades\Log;

class SendMqttCommand implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $device;
    public $payload;

    public function __construct(Device $device, string $payload)
    {
        $this->device = $device;
        $this->payload = $payload;
    }

    public function handle(): void
    {
        $server   = \App\Models\SystemConfig::where('key', 'mqtt_host')->value('value') ?? env('MQTT_HOST', 'broker.emqx.io');
        $port     = \App\Models\SystemConfig::where('key', 'mqtt_port')->value('value') ?? env('MQTT_PORT', 1883);
        $username = \App\Models\SystemConfig::where('key', 'mqtt_user')->value('value') ?? env('MQTT_USERNAME');
        $password = \App\Models\SystemConfig::where('key', 'mqtt_password')->value('value') ?? env('MQTT_PASSWORD');
        $clientId = env('MQTT_CLIENT_ID', 'laravel_job_' . rand(1000, 9999));

        try {
            $mqtt = new MqttClient($server, (int)$port, $clientId);
            $connectionSettings = (new ConnectionSettings)
                ->setKeepAliveInterval(60)
                ->setUseTls(false);

            if (!empty($username)) {
                $connectionSettings->setUsername($username);
            }
            if (!empty($password)) {
                $connectionSettings->setPassword($password);
            }

            $mqtt->connect($connectionSettings, true);
            $mqtt->publish("cmd/{$this->device->device_id}", $this->payload, 0);
            $mqtt->disconnect();
        } catch (\Exception $e) {
            Log::error("Queue MQTT command failed for device {$this->device->device_id}: " . $e->getMessage());
        }
    }
}
