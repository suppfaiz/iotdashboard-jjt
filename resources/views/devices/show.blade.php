@extends('layouts.app')

@section('content')
<style>
@media print {
    body * {
        visibility: hidden;
    }
    #print-label-container, #print-label-container * {
        visibility: visible;
    }
    #print-label-container {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
        display: flex !important;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding-top: 40px;
    }
}
</style>
<div class="max-w-5xl mx-auto">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-900 flex items-center">
            {{ $device->name }} 
            <span class="ml-4 inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800" id="status-badge">
                <span class="w-2 h-2 mr-2 bg-green-500 rounded-full animate-pulse" id="status-dot"></span> <span id="status-text">Active</span>
            </span>
        </h1>
        <div class="flex items-center gap-3">
            <button onclick="printBarcode()" class="text-sm bg-indigo-600 hover:bg-indigo-700 text-white px-3 py-1.5 rounded transition-colors flex items-center shadow-sm">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                Print Label
            </button>
            <button onclick="pingDevice()" class="text-sm bg-blue-600 hover:bg-blue-700 text-white px-3 py-1.5 rounded transition-colors flex items-center shadow-sm">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                Ping Device
            </button>
            <a href="{{ route('devices.provisioning', $device->id) }}" class="text-sm bg-gray-100 hover:bg-gray-200 text-gray-800 border border-gray-300 px-3 py-1.5 rounded transition-colors flex items-center shadow-sm">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"></path></svg>
                Provisioning
            </a>
            <form action="{{ route('devices.destroy', $device->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this device?');" class="inline">
                @csrf
                @method('DELETE')
                <button type="submit" class="text-sm text-red-600 hover:text-red-700 transition-colors bg-red-50 hover:bg-red-100 border border-red-200 px-3 py-1.5 rounded">Delete</button>
            </form>
            <a href="{{ route('dashboard') }}" class="text-sm text-gray-500 hover:text-gray-900 transition-colors ml-2">← Back</a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        <!-- Device Info -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4 border-b border-gray-200 pb-2">Device Information</h3>
            <dl class="space-y-3 text-sm">
                <div class="flex justify-between">
                    <dt class="text-gray-500">Device ID</dt>
                    <dd class="text-gray-900 font-mono bg-gray-50 px-2 py-0.5 rounded border border-gray-200">{{ $device->device_id }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-500">Group Area</dt>
                    <dd class="text-gray-900">{{ $device->group->name }}</dd>
                </div>
                <div class="flex justify-between items-start gap-4">
                    <dt class="text-gray-500 whitespace-nowrap">MQTT Topic</dt>
                    <dd class="text-gray-900 font-mono text-right break-all">{{ $device->mqtt_topic }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-500">Registered</dt>
                    <dd class="text-gray-900">{{ $device->created_at->format('M d, Y') }}</dd>
                </div>
            </dl>
        </div>

        <!-- Latest Metrics -->
        <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex justify-between items-center mb-4 border-b border-gray-200 pb-2">
                <h3 class="text-lg font-medium text-gray-900">Real-Time Metrics</h3>
                <span class="text-xs text-gray-500">Auto-updating via WebSockets</span>
            </div>
            
            <div class="grid grid-cols-2 md:grid-cols-5 gap-4" id="device-metrics-container">
                <div class="bg-gray-50 rounded-lg p-4 border border-gray-100">
                    <p class="text-sm text-gray-500 mb-1">Voltage</p>
                    <p class="text-2xl font-semibold text-gray-900"><span id="metric-voltage">{{ $metrics['voltage'] }}</span> <span class="text-sm text-gray-500">V</span></p>
                </div>
                <div class="bg-gray-50 rounded-lg p-4 border border-gray-100">
                    <p class="text-sm text-gray-500 mb-1">Current</p>
                    <p class="text-2xl font-semibold text-gray-900"><span id="metric-current">{{ $metrics['current'] }}</span> <span class="text-sm text-gray-500">A</span></p>
                </div>
                <div class="bg-gray-50 rounded-lg p-4 border border-gray-100">
                    <p class="text-sm text-gray-500 mb-1">Power</p>
                    <p class="text-2xl font-semibold text-gray-900"><span id="metric-power">{{ $metrics['power'] }}</span> <span class="text-sm text-gray-500">W</span></p>
                </div>
                <div class="bg-blue-50 rounded-lg p-4 border border-blue-200 relative overflow-hidden">
                    <p class="text-sm text-blue-600 mb-1">Energy</p>
                    <p class="text-2xl font-semibold text-blue-700"><span id="metric-energy">{{ $metrics['energy'] }}</span> <span class="text-sm text-blue-500">kWh</span></p>
                </div>
                <div class="bg-emerald-50 rounded-lg p-4 border border-emerald-200 col-span-2 md:col-span-1 relative overflow-hidden">
                    <p class="text-sm text-emerald-600 mb-1">Estimated Cost</p>
                    <p class="text-2xl font-semibold text-emerald-700">Rp <span id="metric-cost">{{ number_format($metrics['energy'] * $plnTariff, 0, ',', '.') }}</span></p>
                </div>
            </div>
        </div>
    </div>

    <!-- OTA Firmware Management -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-8">
        <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center bg-gray-50">
            <h3 class="text-lg font-medium text-gray-900 flex items-center">
                <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                OTA Firmware Update
            </h3>
        </div>
        <div class="p-6">
            @if(session('success') && str_contains(session('success'), 'Firmware'))
                <div class="mb-4 bg-green-50 border-l-4 border-green-500 p-3 rounded">
                    <p class="text-sm text-green-800">{{ session('success') }}</p>
                </div>
            @endif
            @if(session('error'))
                <div class="mb-4 bg-red-50 border-l-4 border-red-500 p-3 rounded">
                    <p class="text-sm text-red-800">{{ session('error') }}</p>
                </div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Upload Form -->
                <div>
                    <h4 class="text-sm font-medium text-gray-700 mb-3">1. Upload Firmware (.bin)</h4>
                    <form action="{{ route('devices.upload_firmware', $device->id) }}" method="POST" enctype="multipart/form-data" class="flex flex-col gap-3">
                        @csrf
                        <input type="file" name="firmware" accept=".bin" required class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 cursor-pointer">
                        <button type="submit" class="self-start text-sm bg-gray-800 hover:bg-gray-900 text-white px-4 py-2 rounded transition-colors shadow-sm border border-gray-700">
                            Upload File
                        </button>
                    </form>
                </div>
                
                <!-- Trigger OTA Form -->
                <div class="border-l border-gray-200 pl-6">
                    <h4 class="text-sm font-medium text-gray-700 mb-3">2. Push Update to Device</h4>
                    @if($device->firmware_path)
                        <div class="mb-3 text-xs text-green-600 flex items-center">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            Latest firmware ready: {{ basename($device->firmware_path) }}
                        </div>
                        <form action="{{ route('devices.trigger_ota', $device->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="text-sm bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded transition-colors shadow-sm flex items-center">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                                Push OTA Update
                            </button>
                        </form>
                    @else
                        <div class="mb-3 text-xs text-yellow-600 flex items-center">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                            No firmware uploaded yet. Please upload a .bin file first.
                        </div>
                        <button disabled class="text-sm bg-gray-100 text-gray-400 border border-gray-200 px-4 py-2 rounded cursor-not-allowed flex items-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                            Push OTA Update
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Connection Debugger -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-8">
        <div class="px-6 py-3 border-b border-gray-200 flex justify-between items-center bg-gray-50">
            <h3 class="text-sm font-medium text-gray-700 flex items-center">
                <svg class="w-4 h-4 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l3 3-3 3m5 0h3M4 17h16a2 2 0 002-2V5a2 2 0 00-2-2H4a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                Connection Debugger
            </h3>
            <button onclick="document.getElementById('debug-log').innerHTML = ''" class="text-xs text-gray-500 hover:text-gray-900 transition-colors">Clear Log</button>
        </div>
        <div class="p-4 bg-gray-900 font-mono text-xs text-green-400 h-48 overflow-y-auto" id="debug-log">
            <div class="mb-1"><span class="text-gray-500">[{{ now()->format('H:i:s') }}]</span> System ready. Waiting for telemetry or ping...</div>
        </div>
    </div>

    <!-- Provisioning Code -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
            <h3 class="text-lg font-medium text-gray-900">Provisioning Code (Arduino C++)</h3>
            <button onclick="copyCode()" class="inline-flex items-center text-xs text-gray-700 hover:text-gray-900 bg-white border border-gray-300 hover:bg-gray-50 px-3 py-1.5 rounded transition-colors shadow-sm">
                <svg class="w-4 h-4 mr-1 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                Copy to Clipboard
            </button>
        </div>
        <div class="p-0 bg-gray-50 border-t border-gray-200">
            <pre class="p-6 text-sm text-gray-800 font-mono overflow-x-auto"><code id="cpp-code">{{ $device->provisioning_code ?? '// Code not found. Please re-provision the device.' }}</code></pre>
        </div>
    </div>
    
    <!-- Printable Barcode Container -->
    <div id="print-label-container" style="display: none;" class="bg-white text-black text-center font-sans">
        <img src="{{ asset('logo.png') }}" class="h-16 mx-auto mb-4" alt="Jamkrida Energy">
        <h2 class="text-2xl font-bold mb-1">{{ $device->name }}</h2>
        <p class="text-sm text-gray-600 mb-6 uppercase tracking-widest">{{ $device->group->name }}</p>
        <div id="device-qrcode" class="mx-auto flex justify-center mb-2"></div>
        <p class="text-xs text-gray-500 font-mono mt-2">{{ $device->device_id }}</p>
    </div>
</div>

<script src="{{ asset('js/qrcode.min.js') }}"></script>

<script type="module">
    window.logDebug = function(message) {
        const log = document.getElementById('debug-log');
        const time = new Date().toLocaleTimeString('en-US', {hour12:false});
        const div = document.createElement('div');
        div.className = 'mb-1';
        div.innerHTML = `<span class="text-gray-500">[${time}]</span> ${message}`;
        log.appendChild(div);
        log.scrollTop = log.scrollHeight;
    };

    window.pingDevice = function() {
        logDebug('<span class="text-blue-400">Pinging backend for device status...</span>');
        fetch(`{{ route('devices.ping', $device->id) }}`)
            .then(res => res.json())
            .then(data => {
                if(data.status === 'online') {
                    logDebug(`<span class="text-green-400">PONG: ${data.message}</span>`);
                } else {
                    logDebug(`<span class="text-red-400">PONG: ${data.message}</span>`);
                }
            })
            .catch(err => {
                logDebug(`<span class="text-red-500">Error pinging device: ${err.message}</span>`);
            });
    };

    document.addEventListener('DOMContentLoaded', () => {
        if(window.Echo) {
            logDebug('WebSocket connected. Subscribed to telemetry channel.');
            window.Echo.channel('telemetry.{{ $device->device_id }}')
                .listen('TelemetryUpdated', (e) => {
                    const data = e.data;
                    logDebug(`Received Telemetry: ${JSON.stringify(data)}`);
                    
                    if(data.voltage !== undefined) document.getElementById('metric-voltage').innerText = parseFloat(data.voltage).toFixed(1);
                    if(data.current !== undefined) document.getElementById('metric-current').innerText = parseFloat(data.current).toFixed(2);
                    if(data.power !== undefined) document.getElementById('metric-power').innerText = parseFloat(data.power).toFixed(1);
                    if(data.energy !== undefined) {
                        const energyVal = parseFloat(data.energy);
                        document.getElementById('metric-energy').innerText = energyVal.toFixed(3);
                        
                        const costElem = document.getElementById('metric-cost');
                        if (costElem) {
                            const cost = energyVal * {{ $plnTariff }};
                            costElem.innerText = cost.toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
                        }
                    }
                    
                    // Update active status
                    const statusBadge = document.getElementById('status-badge');
                    const statusDot = document.getElementById('status-dot');
                    const statusText = document.getElementById('status-text');
                    
                    if (statusBadge) {
                        statusBadge.classList.remove('bg-red-100', 'text-red-800');
                        statusBadge.classList.add('bg-green-100', 'text-green-800');
                        statusDot.classList.remove('bg-red-500');
                        statusDot.classList.add('bg-green-500', 'animate-pulse');
                        statusText.innerText = 'Active';
                        
                        if (window.statusTimeout) clearTimeout(window.statusTimeout);
                        window.statusTimeout = setTimeout(() => {
                            statusBadge.classList.add('bg-red-100', 'text-red-800');
                            statusBadge.classList.remove('bg-green-100', 'text-green-800');
                            statusDot.classList.add('bg-red-500');
                            statusDot.classList.remove('bg-green-500', 'animate-pulse');
                            statusText.innerText = 'Inactive';
                        }, 15000);
                    }
                    
                    const container = document.getElementById('device-metrics-container');
                    container.classList.add('ring-2', 'ring-blue-500', 'rounded-lg');
                    setTimeout(() => {
                        container.classList.remove('ring-2', 'ring-blue-500', 'rounded-lg');
                    }, 300);
                });
        }
    });

    window.copyCode = function() {
        const codeElement = document.getElementById('cpp-code');
        const text = codeElement.innerText || codeElement.textContent;
        navigator.clipboard.writeText(text).then(() => {
            alert('Code copied to clipboard!');
        }).catch(err => {
            console.error('Failed to copy text: ', err);
            // Fallback
            const textArea = document.createElement("textarea");
            textArea.value = text;
            document.body.appendChild(textArea);
            textArea.select();
            document.execCommand("Copy");
            textArea.remove();
            alert('Code copied to clipboard!');
        });
    }

    let qrcodeRendered = false;
    window.printBarcode = function() {
        if (!qrcodeRendered) {
            new QRCode(document.getElementById("device-qrcode"), {
                text: "{{ $device->device_id }}",
                width: 140,
                height: 140,
                colorDark : "#000000",
                colorLight : "#ffffff",
                correctLevel : QRCode.CorrectLevel.H
            });
            qrcodeRendered = true;
        }
        
        // Timeout to allow QR Code canvas to render before printing
        setTimeout(() => {
            window.print();
        }, 100);
    }
</script>
@endsection
