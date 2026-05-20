@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Device Provisioning</h1>
            <p class="text-gray-500">Device <span class="font-mono text-blue-600">{{ $device->device_id }}</span> has been registered successfully.</p>
        </div>
        <a href="{{ route('dashboard') }}" class="inline-flex items-center px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-800 text-sm font-medium rounded-md transition-colors">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Back to Dashboard
        </a>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-6">
        <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
            <h2 class="text-xl font-semibold text-gray-900">Arduino Sketch Template</h2>
            <button onclick="copyCode()" class="inline-flex items-center px-3 py-1.5 border border-gray-300 shadow-sm text-xs font-medium rounded text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                <svg class="mr-1.5 h-4 w-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                Copy Code
            </button>
        </div>
        
        <div class="p-6 bg-gray-50 overflow-x-auto">
            <pre class="text-sm font-mono text-gray-800"><code id="code-block">{{ $device->provisioning_code ?? '// Code not found. Please re-provision the device.' }}</code></pre>
        </div>
    </div>

    <div class="bg-blue-50 rounded-xl p-6 border border-blue-200" id="connection-status">
        <h3 class="text-lg font-medium text-blue-900 mb-2 flex items-center">
            <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            Next Steps
        </h3>
        <ol class="list-decimal list-inside text-blue-800 space-y-2 text-sm ml-2">
            <li>Open Arduino IDE.</li>
            <li>Install required libraries: <code>PZEM004Tv30</code>, <code>PubSubClient</code>, <code>ArduinoJson</code>, <code>HTTPClient</code>, <code>HTTPUpdate</code>, <code>Update</code>.</li>
            <li>Copy the generated code above and paste it into a new sketch.</li>
            <li>Connect your ESP32 device to your computer via USB.</li>
            <li>Compile and upload the sketch to the device.</li>
            <li>The device will automatically connect to WiFi and start streaming data to this dashboard.</li>
        </ol>
    </div>
</div>

<script type="module">
    document.addEventListener('DOMContentLoaded', () => {
        if(window.Echo) {
            window.Echo.channel('telemetry.{{ $device->device_id }}')
                .listen('TelemetryUpdated', (e) => {
                    const statusBanner = document.getElementById('connection-status');
                    statusBanner.className = "bg-green-50 rounded-xl p-6 border border-green-200 transition-all duration-500";
                    
                    statusBanner.innerHTML = `
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-green-500 animate-bounce" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-green-800">Connection Successful!</h3>
                                <p class="text-sm text-green-700 mt-1">
                                    Device <strong>{{ $device->device_id }}</strong> is successfully connected and transmitting telemetry data.
                                    <br>Voltage: ${e.data.voltage}V | Current: ${e.data.current}A
                                </p>
                            </div>
                        </div>
                    `;
                });
        }
    });

    window.copyCode = function() {
        const codeElement = document.getElementById('code-block');
        const text = codeElement.innerText || codeElement.textContent;
        navigator.clipboard.writeText(text).then(() => {
            alert('Code copied to clipboard!');
        }).catch(err => {
            console.error('Failed to copy text: ', err);
            // Fallback for older browsers
            const textArea = document.createElement("textarea");
            textArea.value = text;
            document.body.appendChild(textArea);
            textArea.select();
            document.execCommand("Copy");
            textArea.remove();
            alert('Code copied to clipboard!');
        });
    }
</script>
@endsection
