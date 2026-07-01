<?php

// Include Laravel bootstrap
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\SystemConfig;
use Illuminate\Support\Facades\Http;

$dbKey = SystemConfig::where('key', 'gemini_api_key')->value('value');
$envKey = config('services.gemini.key');

echo "<h1>Gemini API Diagnostics</h1>";
echo "Database key exists: " . (empty($dbKey) ? "NO" : "YES (" . substr($dbKey, 0, 8) . "...)") . "<br>";
echo "Services config key exists: " . (empty($envKey) ? "NO" : "YES (" . substr($envKey, 0, 8) . "...)") . "<br>";

$geminiKey = trim(($dbKey ?: $envKey) ?? '');

if (empty($geminiKey)) {
    echo "<span style='color: red;'>ERROR: No API Key found in database or config!</span><br>";
    exit;
}

try {
    $response = Http::withHeaders([
        'Content-Type' => 'application/json',
        'x-goog-api-key' => $geminiKey,
    ])->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent", [
        'contents' => [
            [
                'parts' => [
                    ['text' => 'Hello, respond with exactly "Gemini is connected successfully!"']
                ]
            ]
        ]
    ]);

    echo "HTTP Status Code: " . $response->status() . "<br>";
    if ($response->successful()) {
        $result = $response->json();
        $text = $result['candidates'][0]['content']['parts'][0]['text'] ?? 'No text in response';
        echo "<span style='color: green;'>Success: " . htmlspecialchars($text) . "</span><br>";
    } else {
        echo "<span style='color: red;'>API Error Response: " . htmlspecialchars($response->body()) . "</span><br>";
    }
} catch (\Exception $e) {
    echo "<span style='color: red;'>Connection Exception: " . htmlspecialchars($e->getMessage()) . "</span><br>";
}
