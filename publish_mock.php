<?php
require __DIR__ . '/vendor/autoload.php';

use PhpMqtt\Client\MqttClient;
use PhpMqtt\Client\ConnectionSettings;

$server   = 'broker.emqx.io';
$port     = 1883;
$clientId = 'laravel_mock_publisher_' . rand(1000, 9999);

$mqtt = new MqttClient($server, $port, $clientId);
$connectionSettings = (new ConnectionSettings)
    ->setKeepAliveInterval(60)
    ->setUseTls(false)
    ->setTlsSelfSignedAllowed(false);

$mqtt->connect($connectionSettings, true);

// Fetch device from DB
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$devices = \App\Models\Device::all();
while($devices->isEmpty()) {
    echo "Waiting for device...\n";
    sleep(2);
    $devices = \App\Models\Device::all();
}
$device = $devices->first();

echo "Publishing to {$device->mqtt_topic}...\n";

$accumulatedEnergy = 0.000;

while (true) {
    $voltage = rand(2150, 2250) / 10; // 215.0 to 225.0 V
    $current = rand(5, 25) / 10;       // 0.5 to 2.5 A
    $power = $voltage * $current;      // W
    
    // Accumulate energy: kWh = (Power (W) * time (2s)) / 3,600,000
    $accumulatedEnergy += ($power * 2) / 3600000;
    
    $payload = json_encode([
        'voltage' => round($voltage, 1),
        'current' => round($current, 2),
        'power' => round($power, 1),
        'energy' => round($accumulatedEnergy, 4),
        'ip' => '192.168.1.184'
    ]);
    
    $mqtt->publish($device->mqtt_topic, $payload, 0);
    echo "Published: $payload\n";
    sleep(2);
}
