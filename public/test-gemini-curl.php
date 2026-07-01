<?php

// Include Laravel bootstrap to get the key from database
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\SystemConfig;

$dbKey = SystemConfig::where('key', 'gemini_api_key')->value('value');
$envKey = config('services.gemini.key');
$geminiKey = trim(($dbKey ?: $envKey) ?? '');

echo "<h1>Gemini API Curl Diagnostics</h1>";
echo "Using Key: " . (empty($geminiKey) ? "EMPTY" : substr($geminiKey, 0, 8) . "...") . "<br><br>";

if (empty($geminiKey)) {
    echo "<span style='color: red;'>ERROR: API Key is empty!</span>";
    exit;
}

$testData = [
    'contents' => [
        [
            'parts' => [
                ['text' => 'Hello, respond with exactly "Success"']
            ]
        ]
    ]
];

// Test cases
$tests = [
    'Test 1: v1beta with query param' => [
        'url' => 'https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=' . $geminiKey,
        'headers' => ['Content-Type: application/json']
    ],
    'Test 2: v1 with query param' => [
        'url' => 'https://generativelanguage.googleapis.com/v1/models/gemini-1.5-flash:generateContent?key=' . $geminiKey,
        'headers' => ['Content-Type: application/json']
    ],
    'Test 3: v1beta with header x-goog-api-key' => [
        'url' => 'https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent',
        'headers' => [
            'Content-Type: application/json',
            'x-goog-api-key: ' . $geminiKey
        ]
    ],
    'Test 4: v1 with header x-goog-api-key' => [
        'url' => 'https://generativelanguage.googleapis.com/v1/models/gemini-1.5-flash:generateContent',
        'headers' => [
            'Content-Type: application/json',
            'x-goog-api-key: ' . $geminiKey
        ]
    ],
];

foreach ($tests as $name => $test) {
    echo "<h3>$name</h3>";
    echo "URL: " . htmlspecialchars($test['url']) . "<br>";
    echo "Headers: " . htmlspecialchars(implode(', ', $test['headers'])) . "<br>";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $test['url']);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($testData));
    curl_setopt($ch, CURLOPT_HTTPHEADER, $test['headers']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    echo "HTTP Status: <strong>$httpCode</strong><br>";
    if ($curlError) {
        echo "<span style='color: red;'>Curl Error: " . htmlspecialchars($curlError) . "</span><br>";
    } else {
        echo "Response: <pre>" . htmlspecialchars($response) . "</pre><br>";
    }
    echo "<hr>";
}
