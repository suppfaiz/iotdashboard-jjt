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
    <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center mb-6 gap-4">
        <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 flex flex-wrap items-center gap-2">
            {{ $device->name }} 
            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800" id="status-badge">
                <span class="w-2 h-2 mr-2 bg-green-500 rounded-full animate-pulse" id="status-dot"></span> <span id="status-text">Active</span>
            </span>
        </h1>
        <div class="flex flex-wrap items-center gap-2 w-full lg:w-auto">
            @if(auth()->user()->role === 'admin')
                <button onclick="printBarcode()" class="text-sm bg-indigo-600 hover:bg-indigo-700 text-white px-3 py-1.5 rounded transition-colors flex items-center shadow-sm">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 00-2 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                    Print Label
                </button>
            @endif
            
            <button onclick="pingDevice()" class="text-sm bg-blue-600 hover:bg-blue-700 text-white px-3 py-1.5 rounded transition-colors flex items-center shadow-sm">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                Ping Device
            </button>
            
            <a href="{{ route('devices.export_csv', $device->id) }}" class="text-sm bg-emerald-600 hover:bg-emerald-700 text-white px-3 py-1.5 rounded transition-colors flex items-center shadow-sm">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                Export CSV
            </a>
            
            @if(auth()->user()->role === 'admin')
                <form action="{{ route('devices.reset_energy', $device->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to reset the accumulated energy for this device?');" class="inline">
                    @csrf
                    <button type="submit" class="text-sm bg-amber-500 hover:bg-amber-600 text-white px-3 py-1.5 rounded transition-colors flex items-center shadow-sm border border-amber-500">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                        Reset Energy
                    </button>
                </form>
                <form action="{{ route('devices.restart', $device->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to restart this device remotely?');" class="inline">
                    @csrf
                    <button type="submit" class="text-sm bg-rose-600 hover:bg-rose-700 text-white px-3 py-1.5 rounded transition-colors flex items-center shadow-sm border border-rose-600">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                        Restart Device
                    </button>
                </form>
                <a href="{{ route('devices.provisioning', $device->id) }}" class="text-sm bg-gray-100 hover:bg-gray-200 text-gray-800 border border-gray-300 px-3 py-1.5 rounded transition-colors flex items-center shadow-sm">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"></path></svg>
                    Provisioning
                </a>
                <form action="{{ route('devices.destroy', $device->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this device?');" class="inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="text-sm text-red-600 hover:text-red-700 transition-colors bg-red-50 hover:bg-red-100 border border-red-200 px-3 py-1.5 rounded">Delete</button>
                </form>
            @endif
            <a href="{{ route('dashboard') }}" class="text-sm text-gray-500 hover:text-gray-900 transition-colors ml-auto lg:ml-2">← Back</a>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-6 bg-green-50 border-l-4 border-green-500 p-4 rounded shadow-sm">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
                </div>
            </div>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        <!-- Left Column -->
        <div class="space-y-6">
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
                    <div class="flex justify-between">
                        <dt class="text-gray-500">IP Address</dt>
                        <dd class="text-gray-900 font-mono bg-blue-50 text-blue-700 px-2 py-0.5 rounded border border-blue-100" id="device-ip">{{ Cache::get("ip:{$device->device_id}", 'N/A') }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-500">WiFi Signal (RSSI)</dt>
                        <dd class="text-gray-900 font-semibold" id="device-rssi">
                            @php
                                $rssi = Cache::get("rssi:{$device->device_id}", null);
                            @endphp
                            @if($rssi !== null)
                                <span class="font-mono">{{ $rssi }} dBm</span>
                                @if($rssi >= -60)
                                    <span class="text-emerald-600 font-bold ml-1" title="Excellent">🟢 Excellent</span>
                                @elseif($rssi >= -70)
                                    <span class="text-emerald-500 font-bold ml-1" title="Good">🟢 Good</span>
                                @elseif($rssi >= -80)
                                    <span class="text-amber-500 font-bold ml-1" title="Fair">🟡 Fair</span>
                                @else
                                    <span class="text-red-500 font-bold ml-1" title="Poor">🔴 Poor</span>
                                @endif
                            @else
                                <span class="text-slate-400">N/A</span>
                            @endif
                        </dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Free Heap Memory</dt>
                        <dd class="text-gray-900 font-mono" id="device-heap">
                            @php
                                $heap = Cache::get("heap:{$device->device_id}", null);
                            @endphp
                            @if($heap !== null)
                                {{ number_format($heap / 1024, 1) }} KB
                            @else
                                <span class="text-slate-400">N/A</span>
                            @endif
                        </dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Voltage Multiplier</dt>
                        <dd class="text-gray-900 font-semibold">{{ number_format($device->voltage_multiplier, 2) }}x</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Current Multiplier</dt>
                        <dd class="text-gray-900 font-semibold">{{ number_format($device->current_multiplier, 2) }}x</dd>
                    </div>
                    @if($device->monthly_budget_kwh)
                        <div class="flex justify-between">
                            <dt class="text-gray-500">Monthly Budget (kWh)</dt>
                            <dd class="text-blue-600 font-semibold">{{ number_format($device->monthly_budget_kwh, 2) }} kWh</dd>
                        </div>
                    @endif
                    @if($device->monthly_budget_cost)
                        <div class="flex justify-between">
                            <dt class="text-gray-500">Monthly Budget (IDR)</dt>
                            <dd class="text-emerald-600 font-semibold">Rp {{ number_format($device->monthly_budget_cost, 0, ',', '.') }}</dd>
                        </div>
                    @endif
                </dl>
            </div>

            <!-- Edit Device Settings (Admin Only) -->
            @if(auth()->user()->role === 'admin')
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4 border-b border-gray-200 pb-2">Edit Device Settings</h3>
                <form action="{{ route('devices.update', $device->id) }}" method="POST" class="space-y-4">
                    @csrf
                    @method('PATCH')
                    
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase">Device Name</label>
                        <input type="text" name="name" value="{{ $device->name }}" required class="mt-1 block w-full text-sm border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 uppercase">Volt Multiplier</label>
                            <input type="number" name="voltage_multiplier" step="0.01" min="0.1" max="10.0" value="{{ $device->voltage_multiplier }}" required class="mt-1 block w-full text-sm border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 uppercase">Current Multiplier</label>
                            <input type="number" name="current_multiplier" step="0.01" min="0.1" max="10.0" value="{{ $device->current_multiplier }}" required class="mt-1 block w-full text-sm border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 uppercase">Budget kWh (Month)</label>
                            <input type="number" name="monthly_budget_kwh" step="0.01" min="0" value="{{ $device->monthly_budget_kwh }}" placeholder="None" class="mt-1 block w-full text-sm border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 uppercase">Budget IDR (Month)</label>
                            <input type="number" name="monthly_budget_cost" step="1" min="0" value="{{ $device->monthly_budget_cost }}" placeholder="None" class="mt-1 block w-full text-sm border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    </div>

                    <button type="submit" class="w-full text-center text-sm bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded transition-colors shadow-sm">
                        Save Settings
                    </button>
                </form>
            </div>
            @endif
        </div>

        <!-- Right Column (2/3 width) -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Real-Time Metrics -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5" id="device-metrics-container">
                <div class="flex justify-between items-center mb-4 pb-3 border-b border-gray-100">
                    <h3 class="text-base font-semibold text-gray-900">Real-Time Metrics</h3>
                    <span class="inline-flex items-center gap-1.5 text-xs text-emerald-600 bg-emerald-50 border border-emerald-200 px-2.5 py-1 rounded-full font-medium">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                        Live via WebSockets
                    </span>
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-5 gap-3">

                    <!-- Voltage -->
                    <div class="rounded-xl border border-amber-100 bg-amber-50 p-3.5 flex items-center gap-3 hover:shadow-sm transition-shadow">
                        <div class="flex-shrink-0 w-8.5 h-8.5 rounded-lg bg-amber-100 flex items-center justify-center">
                            <svg class="w-4 h-4 text-amber-600 w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                        </div>
                        <div>
                            <p class="text-[10px] font-semibold text-amber-700 uppercase tracking-wide leading-none mb-1">Voltage</p>
                            <p class="text-lg font-extrabold text-gray-900 leading-none"><span id="metric-voltage">{{ $metrics['voltage'] }}</span> <span class="text-xs font-semibold text-gray-400">V</span></p>
                        </div>
                    </div>

                    <!-- Current -->
                    <div class="rounded-xl border border-blue-100 bg-blue-50 p-3.5 flex items-center gap-3 hover:shadow-sm transition-shadow">
                        <div class="flex-shrink-0 w-8.5 h-8.5 rounded-lg bg-blue-100 flex items-center justify-center">
                            <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 002 2h2a2 2 0 002-2z"/></svg>
                        </div>
                        <div>
                            <p class="text-[10px] font-semibold text-blue-700 uppercase tracking-wide leading-none mb-1">Current</p>
                            <p class="text-lg font-extrabold text-gray-900 leading-none"><span id="metric-current">{{ $metrics['current'] }}</span> <span class="text-xs font-semibold text-gray-400">A</span></p>
                        </div>
                    </div>

                    <!-- Power -->
                    <div class="rounded-xl border border-purple-100 bg-purple-50 p-3.5 flex items-center gap-3 hover:shadow-sm transition-shadow">
                        <div class="flex-shrink-0 w-8.5 h-8.5 rounded-lg bg-purple-100 flex items-center justify-center">
                            <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                        </div>
                        <div>
                            <p class="text-[10px] font-semibold text-purple-700 uppercase tracking-wide leading-none mb-1">Power</p>
                            <p class="text-lg font-extrabold text-gray-900 leading-none"><span id="metric-power">{{ $metrics['power'] }}</span> <span class="text-xs font-semibold text-gray-400">W</span></p>
                        </div>
                    </div>

                    <!-- Energy -->
                    <div class="rounded-xl border border-indigo-100 bg-indigo-50 p-3.5 flex items-center gap-3 hover:shadow-sm transition-shadow">
                        <div class="flex-shrink-0 w-8.5 h-8.5 rounded-lg bg-indigo-100 flex items-center justify-center">
                            <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364-6.364l-.707.707M6.343 17.657l-.707.707m12.728 0l-.707-.707M6.343 6.364l-.707-.707M12 5a7 7 0 100 14 7 7 0 000-14z"/></svg>
                        </div>
                        <div>
                            <p class="text-[10px] font-semibold text-indigo-700 uppercase tracking-wide leading-none mb-1">Energy</p>
                            <p class="text-lg font-extrabold text-indigo-700 leading-none"><span id="metric-energy">{{ $metrics['energy'] }}</span> <span class="text-xs font-semibold text-indigo-400">kWh</span></p>
                        </div>
                    </div>

                    <!-- Estimated Cost -->
                    <div class="rounded-xl border border-emerald-100 bg-emerald-50 p-3.5 flex items-center gap-3 hover:shadow-sm transition-shadow col-span-2 sm:col-span-1">
                        <div class="flex-shrink-0 w-8.5 h-8.5 rounded-lg bg-emerald-100 flex items-center justify-center">
                            <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <div>
                            <p class="text-[10px] font-semibold text-emerald-700 uppercase tracking-wide leading-none mb-1">Est. Cost</p>
                            <p class="text-lg font-extrabold text-emerald-700 leading-none">Rp <span id="metric-cost">{{ number_format($metrics['energy'] * $plnTariff, 0, ',', '.') }}</span></p>
                        </div>
                    </div>

                </div>
            </div>

            @if(auth()->user()->role === 'admin')
                <!-- OTA Firmware Management -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
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
                            <div class="border-t md:border-t-0 md:border-l border-gray-200 pt-6 md:pt-0 pl-0 md:pl-6">
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

                        <!-- OTA Progress Bar -->
                        <div id="ota-progress-container" class="mt-6 border-t border-gray-100 pt-6 hidden">
                            <h4 class="text-sm font-semibold text-gray-700 mb-2 flex items-center justify-between">
                                <span id="ota-status-label" class="flex items-center gap-2">
                                    <svg class="animate-spin h-4 w-4 text-blue-600" id="ota-spinner" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    <span id="ota-status-text" class="font-bold text-gray-800">OTA Update Progress</span>
                                </span>
                                <span id="ota-progress-percent" class="text-blue-600 font-bold">0%</span>
                            </h4>
                            <div class="w-full bg-gray-200 rounded-full h-4 overflow-hidden relative shadow-inner">
                                <div id="ota-progress-bar" class="bg-gradient-to-r from-blue-500 to-indigo-600 h-full rounded-full transition-all duration-300 ease-out" style="width: 0%;"></div>
                            </div>
                            <div class="flex justify-between items-center mt-2 text-xs text-gray-500">
                                <span id="ota-status-message" class="italic">Initiating firmware transfer...</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Interactive Remote Console -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center bg-gray-50">
                        <h3 class="text-lg font-medium text-gray-900 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l3 3-3 3m5 0h3M4 17h16a2 2 0 002-2V5a2 2 0 00-2-2H4a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                            Interactive Remote Console
                        </h3>
                        <span class="text-xs text-gray-500 font-mono">cmd/{{ $device->device_id }}</span>
                    </div>
                    <div class="p-6">
                        <div class="mb-4">
                            <label class="block text-xs font-semibold text-gray-700 uppercase mb-2">Send Custom JSON Command</label>
                            <div class="flex gap-3">
                                <input type="text" id="console-payload" placeholder='{"cmd": "restart"}' value='{"cmd": "restart"}' class="flex-1 text-sm font-mono border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 bg-gray-50">
                                <button onclick="sendConsoleCommand()" class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-4 py-2 rounded transition-colors shadow-sm">
                                    Send Command
                                </button>
                            </div>
                            <p class="text-xs text-gray-500 mt-1">Make sure the payload is valid JSON (e.g., <code>{"cmd": "restart"}</code>, <code>{"cmd": "reset_energy"}</code>).</p>
                        </div>
                        
                        <div class="mt-4">
                            <span class="block text-[10px] font-extrabold text-slate-450 uppercase tracking-widest mb-2">Preset Commands</span>
                            <div class="flex flex-wrap gap-2">
                                <button onclick="applyConsolePreset('{\&quot;cmd\&quot;: \&quot;restart\&quot;}')" class="px-3 py-1.5 bg-slate-50 border border-slate-200 text-xs font-semibold text-slate-700 rounded-xl hover:bg-slate-100 hover:border-slate-350 transition shadow-sm flex items-center gap-1.5">
                                    🔄 Reboot ESP32
                                </button>
                                <button onclick="applyConsolePreset('{\&quot;cmd\&quot;: \&quot;reset_energy\&quot;}')" class="px-3 py-1.5 bg-slate-50 border border-slate-200 text-xs font-semibold text-slate-700 rounded-xl hover:bg-slate-100 hover:border-slate-350 transition shadow-sm flex items-center gap-1.5">
                                    ⚡ Reset Energy
                                </button>
                                <button onclick="applyConsolePreset('{\&quot;cmd\&quot;: \&quot;relay\&quot;, \&quot;state\&quot;: 1}')" class="px-3 py-1.5 bg-slate-50 border border-slate-200 text-xs font-semibold text-slate-700 rounded-xl hover:bg-slate-100 hover:border-slate-350 transition shadow-sm flex items-center gap-1.5">
                                    💡 Relay ON
                                </button>
                                <button onclick="applyConsolePreset('{\&quot;cmd\&quot;: \&quot;relay\&quot;, \&quot;state\&quot;: 0}')" class="px-3 py-1.5 bg-slate-50 border border-slate-200 text-xs font-semibold text-slate-700 rounded-xl hover:bg-slate-100 hover:border-slate-350 transition shadow-sm flex items-center gap-1.5">
                                    🔌 Relay OFF
                                </button>
                                <button onclick="applyConsolePreset('{\&quot;cmd\&quot;: \&quot;get_status\&quot;}')" class="px-3 py-1.5 bg-slate-50 border border-slate-200 text-xs font-semibold text-slate-700 rounded-xl hover:bg-slate-100 hover:border-slate-350 transition shadow-sm flex items-center gap-1.5">
                                    📡 Get Status
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Connection Debugger -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden text-slate-800">
                    <div class="px-6 py-3 border-b border-gray-200 flex justify-between items-center bg-gray-50">
                        <h3 class="text-sm font-semibold text-gray-700 flex items-center">
                            <svg class="w-4 h-4 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l3 3-3 3m5 0h3M4 17h16a2 2 0 002-2V5a2 2 0 00-2-2H4a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                            Connection Debugger
                        </h3>
                        <button onclick="document.getElementById('debug-log').innerHTML = ''" class="text-xs text-gray-550 hover:text-gray-900 transition-colors">Clear Log</button>
                    </div>
                    <div class="p-4 bg-gray-900 font-mono text-xs text-green-400 overflow-y-auto" style="height: 320px;" id="debug-log">
                        <div class="mb-1"><span class="text-gray-500">[{{ now()->format('H:i:s') }}]</span> System ready. Waiting for telemetry or ping...</div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    @if(auth()->user()->role === 'admin')
        <!-- Provisioning Code -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-8">
            <div class="px-6 py-4 border-b border-gray-200 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
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
    @endif
    
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

    const initEcho = () => {
        if(window.Echo) {
            logDebug('WebSocket connected. Subscribed to telemetry channel.');
            window.Echo.channel('telemetry')
                .listen('TelemetryUpdated', (e) => {
                    if (e.deviceId !== '{{ $device->device_id }}') return;
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
                    
                    if(data.ip !== undefined) {
                        const ipElem = document.getElementById('device-ip');
                        if (ipElem) ipElem.innerText = data.ip;
                    }
                    
                    if(data.rssi !== undefined) {
                        const rssiElem = document.getElementById('device-rssi');
                        if (rssiElem) {
                            const val = parseInt(data.rssi);
                            let statusText = '🔴 Poor';
                            if (val >= -60) statusText = '🟢 Excellent';
                            else if (val >= -70) statusText = '🟢 Good';
                            else if (val >= -80) statusText = '🟡 Fair';
                            
                            rssiElem.innerHTML = `<span class="font-mono">${val} dBm</span> <span class="font-bold ml-1">${statusText}</span>`;
                        }
                    }
                    
                    if(data.heap !== undefined) {
                        const heapElem = document.getElementById('device-heap');
                        if (heapElem) {
                            const val = parseInt(data.heap);
                            heapElem.innerText = (val / 1024).toFixed(1) + ' KB';
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
                })
                .listen('OtaProgressUpdated', (e) => {
                    logDebug(`OTA Progress: ${e.progress}% [${e.status}]`);
                    const container = document.getElementById('ota-progress-container');
                    const bar = document.getElementById('ota-progress-bar');
                    const percent = document.getElementById('ota-progress-percent');
                    const msg = document.getElementById('ota-status-message');
                    const spinner = document.getElementById('ota-spinner');
                    const statusText = document.getElementById('ota-status-text');

                    if (container) container.classList.remove('hidden');

                    if (e.progress !== undefined && bar && percent) {
                        bar.style.width = e.progress + '%';
                        percent.innerText = e.progress + '%';
                    }

                    if (e.status === 'started') {
                        if (spinner) spinner.classList.remove('hidden');
                        if (statusText) statusText.innerText = 'OTA Firmware Update Started';
                        if (msg) msg.innerText = e.message || 'Starting HTTP firmware download on ESP32...';
                        if (bar) bar.className = "bg-gradient-to-r from-blue-500 to-indigo-600 h-full rounded-full transition-all duration-300 ease-out";
                    } else if (e.status === 'downloading') {
                        if (spinner) spinner.classList.remove('hidden');
                        if (statusText) statusText.innerText = 'Downloading Firmware';
                        if (msg) msg.innerText = e.message || 'Downloading OTA binary...';
                        if (bar) bar.className = "bg-gradient-to-r from-blue-500 to-indigo-600 h-full rounded-full transition-all duration-300 ease-out";
                    } else if (e.status === 'completed') {
                        if (spinner) spinner.classList.add('hidden');
                        if (statusText) statusText.innerText = 'Update Completed';
                        if (msg) msg.innerHTML = '<span class="text-green-600 font-bold">✨ Firmware updated successfully! Device is rebooting now.</span>';
                        if (bar) bar.className = "bg-emerald-500 h-full rounded-full transition-all duration-300 ease-out";
                        setTimeout(() => {
                            if (container) container.classList.add('hidden');
                        }, 10000);
                    } else if (e.status === 'failed') {
                        if (spinner) spinner.classList.add('hidden');
                        if (statusText) statusText.innerText = 'Update Failed';
                        if (msg) msg.innerHTML = `<span class="text-rose-600 font-bold">❌ Update failed: ${e.message || 'Unknown error'}</span>`;
                        if (bar) bar.className = "bg-rose-500 h-full rounded-full transition-all duration-300 ease-out";
                    }
                });
        }
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initEcho);
    } else {
        initEcho();
    }

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

    window.sendConsoleCommand = function() {
        const payloadInput = document.getElementById('console-payload');
        const payload = payloadInput.value.trim();
        
        if (!payload) {
            alert('Payload cannot be empty.');
            return;
        }

        try {
            JSON.parse(payload);
        } catch (e) {
            alert('Payload must be a valid JSON string.');
            return;
        }

        logDebug(`<span class="text-indigo-400">Sending console command payload: ${payload}</span>`);

        fetch(`{{ route('devices.console', $device->id) }}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ payload: payload })
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                logDebug(`<span class="text-green-400">Console Success: ${data.message}</span>`);
            } else {
                logDebug(`<span class="text-red-400">Console Error: ${data.message}</span>`);
            }
        })
        .catch(err => {
            logDebug(`<span class="text-red-500">Network Error sending console command: ${err.message}</span>`);
        });
    }

    window.applyConsolePreset = function(jsonStr) {
        document.getElementById('console-payload').value = jsonStr;
        logDebug(`<span class="text-indigo-400">Loaded preset payload: ${jsonStr}</span>`);
    }
</script>
@endsection
