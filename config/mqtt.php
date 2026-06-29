<?php

return [
    'host' => env('MQTT_HOST', 'broker.emqx.io'),
    'port' => env('MQTT_PORT', 1883),
    'client_id' => env('MQTT_CLIENT_ID', 'laravel_worker'),
    'username' => env('MQTT_USERNAME'),
    'password' => env('MQTT_PASSWORD'),
    'use_tls' => env('MQTT_USE_TLS', false),
];
