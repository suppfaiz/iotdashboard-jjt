@extends('layouts.app')

@section('content')
<div class="max-w-5xl mx-auto flex gap-8">
    <!-- Sidebar Navigation -->
    <aside class="hidden lg:block w-64 shrink-0 sticky top-24 self-start">
        <nav class="space-y-1">
            <p class="text-xs font-bold text-gray-400 uppercase tracking-wider px-3 mb-2">Documentation</p>
            <a href="#architecture" class="flex items-center px-3 py-2 text-sm font-medium text-gray-700 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition-colors">
                1. System Architecture
            </a>
            <a href="#provisioning" class="flex items-center px-3 py-2 text-sm font-medium text-gray-700 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition-colors">
                2. Device Provisioning
            </a>
            <a href="#mqtt-websocket" class="flex items-center px-3 py-2 text-sm font-medium text-gray-700 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition-colors">
                3. MQTT & WebSockets
            </a>
            <a href="#ota-update" class="flex items-center px-3 py-2 text-sm font-medium text-gray-700 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition-colors">
                4. OTA Firmware Updates
            </a>
            <a href="#scheduler" class="flex items-center px-3 py-2 text-sm font-medium text-gray-700 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition-colors">
                5. Scheduler & Logging
            </a>
        </nav>
    </aside>

    <!-- Main Content Area -->
    <div class="flex-1 min-w-0 bg-white rounded-2xl border border-gray-200 shadow-sm p-8">
        <div class="border-b border-gray-100 pb-6 mb-8">
            <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight">Admin & Developer Documentation</h1>
            <p class="text-gray-500 mt-2">Technical manual for managing, provisioning, and updating Jamkrida Energy IoT infrastructure.</p>
        </div>

        <div class="space-y-12">
            <!-- Section 1: System Architecture -->
            <section id="architecture" class="scroll-mt-24">
                <h2 class="text-2xl font-bold text-gray-900 mb-4 flex items-center gap-2">
                    <span class="flex items-center justify-center w-8 h-8 rounded-lg bg-blue-50 text-blue-600 text-sm font-bold">1</span>
                    System Architecture
                </h2>
                <p class="text-gray-600 mb-4 leading-relaxed">
                    Jamkrida Energy Platform uses a high-performance, real-time distributed architecture to fetch, store, and stream electricity usage metrics from physical microcontroller nodes (ESP32/ESP8266 + PZEM-004T).
                </p>
                <div class="bg-gray-50 border border-gray-200 rounded-xl p-5 mb-4">
                    <h4 class="text-sm font-bold text-gray-800 mb-3">Core Technical Stack:</h4>
                    <ul class="list-disc pl-5 space-y-2 text-sm text-gray-600">
                        <li><strong>Firmware:</strong> Arduino C++ listening to PZEM-004T v3 sensor over UART.</li>
                        <li><strong>Ingress Protocol:</strong> MQTT over TCP (broker.emqx.io or customized instances).</li>
                        <li><strong>Backend Listener:</strong> Continuous PHP Artisan command daemon daemonized on server.</li>
                        <li><strong>Reactive Layer:</strong> Laravel Reverb (WebSocket server) with Pusher Protocol wrapper.</li>
                        <li><strong>Frontend State:</strong> Vanilla Javascript Echo client with Alpine.js reactivity wrappers.</li>
                    </ul>
                </div>
            </section>

            <!-- Section 2: Device Provisioning -->
            <section id="provisioning" class="scroll-mt-24">
                <h2 class="text-2xl font-bold text-gray-900 mb-4 flex items-center gap-2">
                    <span class="flex items-center justify-center w-8 h-8 rounded-lg bg-blue-50 text-blue-600 text-sm font-bold">2</span>
                    Device Provisioning & C++ Template
                </h2>
                <p class="text-gray-600 mb-4 leading-relaxed">
                    When you add a new device through the Admin Dashboard, the system dynamically calculates a unique ID (prefixed with <code>dev_</code>) and generates a tailor-made C++ script.
                </p>
                <div class="space-y-4">
                    <div class="border-l-4 border-amber-500 bg-amber-50 p-4 rounded-r-xl">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-bold text-amber-800">C++ Hardware Requirements</h3>
                                <p class="text-xs text-amber-700 mt-1">Make sure PZEM-004T v3 Rx and Tx pins are properly cross-wired to the microcontroller’s configured UART Serial Pins (typically GPIO 16/17 on ESP32 or SoftwareSerial on ESP8266).</p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 border border-gray-200 rounded-xl p-5">
                        <h4 class="text-sm font-bold text-gray-800 mb-2">Step-by-Step Provisioning Flow:</h4>
                        <ol class="list-decimal pl-5 space-y-2 text-sm text-gray-600">
                            <li>Go to the main <strong>Dashboard</strong> and click <strong>Add Device</strong>.</li>
                            <li>Fill in name, area group, SSID, and Wi-Fi password.</li>
                            <li>The dashboard will generate and display a ready-to-copy code containing unique telemetry MQTT topics.</li>
                            <li>Upload the code to your microchip. As soon as the chip connects, the browser registers telemetry, displaying a success banner.</li>
                        </ol>
                    </div>
                </div>
            </section>

            <!-- Section 3: MQTT & WebSockets -->
            <section id="mqtt-websocket" class="scroll-mt-24">
                <h2 class="text-2xl font-bold text-gray-900 mb-4 flex items-center gap-2">
                    <span class="flex items-center justify-center w-8 h-8 rounded-lg bg-blue-50 text-blue-600 text-sm font-bold">3</span>
                    Real-time Pipeline (MQTT & WebSockets)
                </h2>
                <p class="text-gray-600 mb-4 leading-relaxed">
                    Data sent by the devices flows through a pipeline before reaching the user's dashboard screen in real-time.
                </p>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="bg-gray-50 border border-gray-200 rounded-xl p-5">
                        <h4 class="text-sm font-bold text-gray-800 mb-2">1. Inbound MQTT Payload</h4>
                        <p class="text-xs text-gray-500 mb-2 font-mono">Topic: telemetry/operational-area/device_id</p>
                        <pre class="bg-gray-800 text-white rounded p-3 text-xs overflow-x-auto"><code>{
  "voltage": 224.5,
  "current": 1.45,
  "power": 325.5,
  "energy": 12.435
}</code></pre>
                    </div>
                    <div class="bg-gray-50 border border-gray-200 rounded-xl p-5">
                        <h4 class="text-sm font-bold text-gray-800 mb-2">2. Processing & WebSocket Dispatch</h4>
                        <p class="text-sm text-gray-600 leading-relaxed mb-2">
                            The background command daemon <code>php artisan mqtt:listen</code> receives the JSON data, updates Redis/Database caches, and fires the <code>TelemetryUpdated</code> Laravel Event.
                        </p>
                        <p class="text-sm text-gray-600 leading-relaxed">
                            Laravel Reverb immediately broadcasts it over the <code>telemetry.{device_id}</code> WebSocket channel.
                        </p>
                    </div>
                </div>
            </section>

            <!-- Section 4: OTA Firmware Updates -->
            <section id="ota-update" class="scroll-mt-24">
                <h2 class="text-2xl font-bold text-gray-900 mb-4 flex items-center gap-2">
                    <span class="flex items-center justify-center w-8 h-8 rounded-lg bg-blue-50 text-blue-600 text-sm font-bold">4</span>
                    OTA Firmware Updates
                </h2>
                <p class="text-gray-600 mb-4 leading-relaxed">
                    You can flash new binary firmware directly to the devices over Wi-Fi, removing the need to connect physical type-C or serial cables to microchips.
                </p>
                <div class="bg-gray-50 border border-gray-200 rounded-xl p-5 space-y-4">
                    <div>
                        <h4 class="text-sm font-bold text-gray-800 mb-1">To Perform an OTA Update:</h4>
                        <ol class="list-decimal pl-5 space-y-2 text-sm text-gray-600">
                            <li>Compile your updated firmware in Arduino IDE or VSCode PlatformIO to get a <code>.bin</code> file.</li>
                            <li>Navigate to the device's <strong>Detail View</strong> and look for the <strong>OTA Firmware Update</strong> section.</li>
                            <li>Upload the <code>.bin</code> file. The system stores this file securely in the public storage directory.</li>
                            <li>Click the <strong>Trigger OTA</strong> button. This publishes an MQTT command to the device.</li>
                        </ol>
                    </div>
                    <div class="bg-gray-800 rounded p-4 text-xs text-white">
                        <span class="text-gray-400 block font-mono mb-1">// Outgoing MQTT trigger payload published to cmd/{device_id}</span>
                        <pre class="font-mono overflow-x-auto"><code>{
  "cmd": "update_firmware",
  "url": "http://127.0.0.1:8000/storage/firmwares/firmware_dev_xxxxx.bin"
}</code></pre>
                    </div>
                    <p class="text-xs text-gray-500 italic">Note: The target ESP32/ESP8266 will parse this command, start an HTTP client connection to the URL, and execute the standard Arduino OTA Update sequence before self-rebooting.</p>
                </div>
            </section>

            <!-- Section 5: Scheduler -->
            <section id="scheduler" class="scroll-mt-24">
                <h2 class="text-2xl font-bold text-gray-900 mb-4 flex items-center gap-2">
                    <span class="flex items-center justify-center w-8 h-8 rounded-lg bg-blue-50 text-blue-600 text-sm font-bold">5</span>
                    Scheduler & Daily Historical Logging
                </h2>
                <p class="text-gray-600 mb-4 leading-relaxed">
                    Rather than writing database telemetry records every second (which would overload standard SQL engines), the system tracks real-time volatile metrics in Cache memory.
                </p>
                <div class="bg-gray-50 border border-gray-200 rounded-xl p-5">
                    <h4 class="text-sm font-bold text-gray-800 mb-2">Automated Cron Job:</h4>
                    <p class="text-sm text-gray-600 leading-relaxed mb-3">
                        Every day, a scheduled command runs to capture the accumulated energy (kWh) reading from the cache memory, writing a single row for each active device into the persistent database table <code>daily_energy_logs</code>.
                    </p>
                    <div class="bg-gray-800 rounded p-4 text-xs font-mono text-white">
                        <span class="text-gray-400 block mb-1"># Command executed by the system scheduler</span>
                        <code>php artisan daily-energy-log:run</code>
                    </div>
                </div>
            </section>
        </div>
    </div>
</div>
@endsection
