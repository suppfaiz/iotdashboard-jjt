<?php
require __DIR__ . '/vendor/autoload.php';

use PhpMqtt\Client\MqttClient;
use PhpMqtt\Client\ConnectionSettings;

$server   = 'broker.hivemq.com';
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

while (true) {
    $payload = json_encode([
        'voltage' => rand(215, 225),
        'current' => rand(10, 50) / 10,
        'power' => rand(200, 1100),
        'energy' => rand(100, 105) + mt_rand(0, 99) / 100
    ]);
    
    $mqtt->publish($device->mqtt_topic, $payload, 0);
    echo "Published: $payload\n";
    sleep(2);
}
