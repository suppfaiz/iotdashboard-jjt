@extends('layouts.app')

@section('content')
<div class="mb-8 flex flex-col md:flex-row md:items-end md:justify-between gap-4">
    <div>
        <h1 class="text-3xl font-bold text-gray-900 mb-2">Executive Summary</h1>
        <p class="text-gray-500">Real-time energy consumption and operational metrics from your IoT devices.</p>
    </div>
    <!-- Real-time Clock (Top Right of Content Area) -->
    <div class="flex items-center text-xs font-semibold text-gray-600 bg-white border border-gray-200 px-4 py-2 rounded-2xl gap-2 font-mono shadow-sm self-start md:self-end">
        <span class="relative flex h-2 w-2">
            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
            <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
        </span>
        <span id="realtime-clock">--:--:--</span>
    </div>
</div>

<!-- Executive Summary Cards -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <!-- PLN Tariff -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 flex items-center justify-between transition-all hover:shadow-md duration-300">
        <div class="space-y-1">
            <p class="text-sm font-medium text-gray-500 uppercase tracking-wider">PLN Tariff</p>
            <p class="text-2xl font-bold text-gray-900">
                Rp {{ number_format($plnTariff, 2, ',', '.') }} <span class="text-sm font-normal text-gray-500">/ kWh</span>
            </p>
        </div>
        <div class="p-3 bg-blue-50 text-blue-600 rounded-xl">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
        </div>
    </div>

    <!-- Total Energy Today -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 flex items-center justify-between transition-all hover:shadow-md duration-300">
        <div class="space-y-1">
            <p class="text-sm font-medium text-gray-500 uppercase tracking-wider">Total Energy Today</p>
            <p class="text-3xl font-extrabold text-teal-600">
                <span id="total-energy-value">{{ number_format($totalVolatileKwh, 3, ',', '.') }}</span> <span class="text-sm font-normal text-gray-500">kWh</span>
            </p>
        </div>
        <div class="p-3 bg-teal-50 text-teal-600 rounded-xl animate-pulse">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
            </svg>
        </div>
    </div>

    <!-- Estimated Cost -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 flex items-center justify-between transition-all hover:shadow-md duration-300">
        <div class="space-y-1">
            <p class="text-sm font-medium text-gray-500 uppercase tracking-wider">Estimated Daily Cost</p>
            <p class="text-3xl font-extrabold text-blue-600">
                Rp <span id="estimated-cost-value">{{ number_format($estimatedCost, 2, ',', '.') }}</span>
            </p>
        </div>
        <div class="p-3 bg-blue-50 text-blue-600 rounded-xl">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
            </svg>
        </div>
    </div>
</div>

<!-- Energy & Consumers Grid -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-10">
    <!-- Energy Consumption Graph -->
    <div class="lg:col-span-2 bg-white/80 backdrop-blur-xl rounded-2xl shadow-sm border border-gray-200 p-6 flex flex-col justify-between">
        <div>
            <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center mb-6 gap-4">
                <div>
                    <h2 class="text-xl font-bold text-gray-900 tracking-tight" id="chart-title">Energy Consumption Trend</h2>
                    <p class="text-xs text-gray-500 font-medium mt-1">Historical analytics of electricity utilization and operational costs</p>
                </div>
                <div class="flex flex-wrap items-center gap-3">
                    <!-- Period Selector -->
                    <div class="flex bg-gray-100 p-1 rounded-lg border border-gray-200 shadow-inner">
                        <button type="button" onclick="changePeriod('daily')" id="btn-chart-daily" class="px-3 py-1.5 text-xs font-semibold rounded-md bg-white text-blue-600 shadow-sm border border-gray-200 transition-all">Daily</button>
                        <button type="button" onclick="changePeriod('monthly')" id="btn-chart-monthly" class="px-3 py-1.5 text-xs font-medium rounded-md text-gray-500 hover:text-gray-900 transition-all border border-transparent">Monthly</button>
                        <button type="button" onclick="changePeriod('yearly')" id="btn-chart-yearly" class="px-3 py-1.5 text-xs font-medium rounded-md text-gray-500 hover:text-gray-900 transition-all border border-transparent">Yearly</button>
                    </div>
                    <!-- Metric Selector -->
                    <div class="flex bg-gray-100 p-1 rounded-lg border border-gray-200 shadow-inner">
                        <button type="button" onclick="changeMetric('energy')" id="btn-metric-energy" class="px-3 py-1.5 text-xs font-semibold rounded-md bg-white text-blue-600 shadow-sm border border-gray-200 transition-all">Energy (kWh)</button>
                        <button type="button" onclick="changeMetric('cost')" id="btn-metric-cost" class="px-3 py-1.5 text-xs font-medium rounded-md text-gray-500 hover:text-gray-900 transition-all border border-transparent">Cost (Rupiah)</button>
                    </div>
                </div>
            </div>
            <div class="relative h-72 w-full">
                <canvas id="energyChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Top 3 Energy Consumers -->
    <div class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-sm border border-gray-200 p-6 flex flex-col justify-between">
        <div class="mb-5">
            <h3 class="text-lg font-bold text-gray-900 tracking-tight">Top 3 Energy Consumers</h3>
            <p class="text-xs text-gray-500 font-medium mt-1">Devices with the highest cumulative energy usage (kWh)</p>
        </div>
        
        <div class="space-y-4 flex-grow flex flex-col justify-center">
            @forelse($topDevices as $index => $topDevice)
                <div class="flex items-center justify-between p-4 rounded-xl bg-gray-50/50 border border-gray-100 transition-all hover:bg-gray-50 hover:shadow-sm duration-200">
                    <div class="flex items-center space-x-3.5">
                        <div class="flex-shrink-0 w-9 h-9 rounded-full flex items-center justify-center font-bold text-sm shadow-sm
                            {{ $index === 0 ? 'bg-amber-100 text-amber-800 border border-amber-200' : ($index === 1 ? 'bg-slate-100 text-slate-800 border border-slate-200' : 'bg-orange-100 text-orange-800 border border-orange-200') }}">
                            #{{ $index + 1 }}
                        </div>
                        <div>
                            <h4 class="text-sm font-bold text-gray-900 leading-snug">{{ $topDevice['name'] }}</h4>
                            <span class="inline-block mt-0.5 px-2 py-0.5 text-[10px] font-semibold bg-blue-50 text-blue-700 rounded-full uppercase tracking-wider">{{ $topDevice['group_name'] }}</span>
                        </div>
                    </div>
                    <div class="text-right">
                        <span class="text-sm font-extrabold text-gray-900">{{ number_format($topDevice['energy'], 3, ',', '.') }}</span>
                        <span class="text-[10px] font-bold text-gray-500 block uppercase tracking-wider mt-0.5">kWh</span>
                    </div>
                </div>
            @empty
                <div class="text-center py-8 text-gray-400 text-sm">
                    No active telemetry devices found.
                </div>
            @endforelse
        </div>
    </div>
</div>

<!-- Group Areas and Devices -->
<div class="space-y-10">
    @forelse($groups as $group)
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between mb-6 pb-4 border-b border-gray-100">
                <div class="flex items-center space-x-3">
                    <div class="p-2 bg-gray-100 rounded-lg text-gray-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-gray-900">{{ $group->name }}</h2>
                        <p class="text-xs text-gray-500 font-medium">Operational Area</p>
                    </div>
                </div>
                <span class="text-xs font-semibold px-2.5 py-1 bg-gray-100 text-gray-600 rounded-full">
                    {{ $group->devices->count() }} Devices
                </span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @forelse($group->devices as $device)
                    @php
                        $isDeviceActive = (now()->timestamp - $device->last_seen) < 15;
                    @endphp
                    <div class="bg-gray-50 rounded-xl border border-gray-200 p-5 flex flex-col justify-between hover:shadow-md hover:border-gray-300 transition-all duration-300" id="device-card-{{ $device->device_id }}" data-last-seen="{{ $device->last_seen }}" data-device-id="{{ $device->device_id }}">
                        
                        <!-- Card Header -->
                        <div class="flex items-start justify-between mb-4 gap-4">
                            <div>
                                <h3 class="text-lg font-bold text-gray-900 tracking-tight">{{ $device->name }}</h3>
                                <p class="text-xs text-gray-500 font-mono mt-0.5">{{ $device->device_id }}</p>
                            </div>
                            
                            <!-- Status Badge -->
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $isDeviceActive ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}" id="status-{{ $device->device_id }}">
                                <span class="w-1.5 h-1.5 mr-1.5 rounded-full {{ $isDeviceActive ? 'bg-green-500 animate-pulse' : 'bg-red-500' }}" id="status-dot-{{ $device->device_id }}"></span>
                                <span id="status-text-{{ $device->device_id }}">{{ $isDeviceActive ? 'Active' : 'Inactive' }}</span>
                            </span>
                        </div>

                        <!-- PZEM Metrics Grid -->
                        <div class="grid grid-cols-2 gap-3 mb-5">
                            <div class="bg-white rounded-lg p-3 border border-gray-100 shadow-sm">
                                <span class="text-[10px] uppercase font-bold text-gray-400 block tracking-wider mb-0.5">Voltage</span>
                                <span class="text-lg font-bold text-gray-800"><span id="voltage-{{ $device->device_id }}">{{ number_format(Cache::get("voltage:{$device->device_id}", 0), 1) }}</span> <span class="text-xs text-gray-500 font-normal">V</span></span>
                            </div>
                            <div class="bg-white rounded-lg p-3 border border-gray-100 shadow-sm">
                                <span class="text-[10px] uppercase font-bold text-gray-400 block tracking-wider mb-0.5">Current</span>
                                <span class="text-lg font-bold text-gray-800"><span id="current-{{ $device->device_id }}">{{ number_format(Cache::get("current:{$device->device_id}", 0), 2) }}</span> <span class="text-xs text-gray-500 font-normal">A</span></span>
                            </div>
                            <div class="bg-white rounded-lg p-3 border border-gray-100 shadow-sm">
                                <span class="text-[10px] uppercase font-bold text-gray-400 block tracking-wider mb-0.5">Power</span>
                                <span class="text-lg font-bold text-gray-800"><span id="power-{{ $device->device_id }}">{{ number_format(Cache::get("power:{$device->device_id}", 0), 1) }}</span> <span class="text-xs text-gray-500 font-normal">W</span></span>
                            </div>
                            <div class="bg-blue-50 rounded-lg p-3 border border-blue-100 shadow-sm">
                                <span class="text-[10px] uppercase font-bold text-blue-400 block tracking-wider mb-0.5">Energy</span>
                                <span class="text-lg font-bold text-blue-700"><span id="energy-{{ $device->device_id }}">{{ number_format(Cache::get("energy:{$device->device_id}", 0), 3) }}</span> <span class="text-xs text-blue-500 font-normal">kWh</span></span>
                            </div>
                            <div class="bg-emerald-50 rounded-lg p-3 border border-emerald-100 shadow-sm col-span-2 flex items-center justify-between">
                                <div>
                                    <span class="text-[10px] uppercase font-bold text-emerald-500 block tracking-wider mb-0.5">Estimated Cost</span>
                                    <span class="text-lg font-extrabold text-emerald-700">
                                        Rp <span id="cost-{{ $device->device_id }}">{{ number_format(Cache::get("energy:{$device->device_id}", 0) * $plnTariff, 0, ',', '.') }}</span>
                                    </span>
                                </div>
                                <div class="p-1.5 bg-emerald-100/50 rounded-lg text-emerald-600">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                            </div>
                        </div>

                        <!-- Card Actions -->
                        @if(auth()->user()->role === 'admin')
                            <div class="flex items-center justify-between border-t border-gray-100 pt-3 mt-auto">
                                <a href="{{ route('devices.show', $device->id) }}" class="text-sm font-semibold text-blue-600 hover:text-blue-700 flex items-center gap-1 transition-colors">
                                    View Details
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                                </a>
                                
                                <form action="{{ route('devices.destroy', $device->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this device?');" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-500 hover:text-red-700 p-1.5 hover:bg-red-50 rounded-lg transition-all" title="Delete Device">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        @else
                            <div class="text-[11px] text-gray-400 font-medium text-center border-t border-gray-100 pt-3 mt-auto uppercase tracking-wide">
                                Read Only Access
                            </div>
                        @endif
                    </div>
                @empty
                    <div class="col-span-full py-8 text-center text-gray-500 font-medium border border-dashed border-gray-200 rounded-xl">
                        No active devices in this area.
                    </div>
                @endforelse
            </div>
        </div>
    @empty
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-12 text-center">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
            </svg>
            <h3 class="mt-4 text-lg font-bold text-gray-900">No Operational Areas</h3>
            <p class="mt-2 text-sm text-gray-500 max-w-sm mx-auto">Create a group and add devices to start monitoring real-time power consumption.</p>
        </div>
    @endforelse
</div>

@if(auth()->user()->role === 'admin')
<!-- Add Device Modal -->
<div id="add-device-modal" class="hidden fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <!-- Background backdrop -->
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" onclick="document.getElementById('add-device-modal').classList.add('hidden')"></div>

        <!-- Spacer to center modal -->
        <span class="hidden sm:inline-block sm:align-middle sm:min-h-screen" aria-hidden="true">&#8203;</span>

        <!-- Modal Panel -->
        <div class="inline-block align-middle bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-gray-100">
            <form action="{{ route('devices.store') }}" method="POST">
                @csrf
                <div class="bg-white px-6 pt-6 pb-4 sm:p-6 sm:pb-4">
                    <div class="flex items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-blue-50 text-blue-600 sm:mx-0 sm:h-10 sm:w-10">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                            <h3 class="text-lg font-bold text-gray-900" id="modal-title">Register New Device</h3>
                            <p class="text-sm text-gray-500 mb-6">Enter details below to provision a new energy monitoring node.</p>
                            
                            <div class="space-y-4">
                                <div>
                                    <label for="name" class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Device Name</label>
                                    <input type="text" name="name" id="name" required placeholder="e.g. Compressor Pump A" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                                </div>

                                <div>
                                    <label for="group_id" class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Group Area</label>
                                    <select name="group_id" id="group_id" required class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                                        @foreach($groups as $g)
                                            <option value="{{ $g->id }}">{{ $g->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <label for="wifi_ssid" class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">SSID Wifi</label>
                                    <input type="text" name="wifi_ssid" id="wifi_ssid" required placeholder="SSID Wifi Name" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                                </div>

                                <div>
                                    <label for="wifi_password" class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Password Wifi</label>
                                    <input type="password" name="wifi_password" id="wifi_password" required placeholder="••••••••" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse gap-2">
                    <button type="submit" class="w-full inline-flex justify-center rounded-lg border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-semibold text-white hover:bg-blue-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm transition-colors">
                        Register & Get Code
                    </button>
                    <button type="button" onclick="document.getElementById('add-device-modal').classList.add('hidden')" class="mt-3 w-full inline-flex justify-center rounded-lg border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-semibold text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm transition-colors">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

<!-- Real-time WebSocket Listeners and Timeout Checks -->
<script>
    const plnTariff = {{ $plnTariff }};
    
    // Dynamic registry of device energy metrics used to recalculate executive summary
    const energyRegistry = {
        @foreach($groups as $group)
            @foreach($group->devices as $device)
                "{{ $device->device_id }}": {{ Cache::get("energy:{$device->device_id}", 0) }},
            @endforeach
        @endforeach
    };

    function recalculateTotalEnergy() {
        let total = 0;
        for (const deviceId in energyRegistry) {
            total += energyRegistry[deviceId];
        }
        
        // Update DOM elements
        document.getElementById('total-energy-value').innerText = total.toLocaleString('id-ID', { minimumFractionDigits: 3, maximumFractionDigits: 3 });
        
        const estimatedCost = total * plnTariff;
        document.getElementById('estimated-cost-value').innerText = estimatedCost.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    // Function to update UI dynamically when telemetries are received
    function updateDeviceUI(deviceId, data) {
        // Update PZEM values
        if(data.voltage !== undefined) {
            const vElem = document.getElementById('voltage-' + deviceId);
            if(vElem) vElem.innerText = parseFloat(data.voltage).toFixed(1);
        }
        if(data.current !== undefined) {
            const cElem = document.getElementById('current-' + deviceId);
            if(cElem) cElem.innerText = parseFloat(data.current).toFixed(2);
        }
        if(data.power !== undefined) {
            const pElem = document.getElementById('power-' + deviceId);
            if(pElem) pElem.innerText = parseFloat(data.power).toFixed(1);
        }
        if(data.energy !== undefined) {
            const eElem = document.getElementById('energy-' + deviceId);
            if(eElem) eElem.innerText = parseFloat(data.energy).toFixed(3);
            
            const costElem = document.getElementById('cost-' + deviceId);
            if(costElem) {
                const cost = parseFloat(data.energy) * plnTariff;
                costElem.innerText = cost.toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
            }
            
            // Keep track of latest energy for this device
            energyRegistry[deviceId] = parseFloat(data.energy);
            recalculateTotalEnergy();
        }

        // Toggle Status Badge to Active
        const cardElem = document.getElementById('device-card-' + deviceId);
        if(cardElem) {
            cardElem.setAttribute('data-last-seen', Math.floor(Date.now() / 1000));
        }

        const badgeElem = document.getElementById('status-' + deviceId);
        const dotElem = document.getElementById('status-dot-' + deviceId);
        const textElem = document.getElementById('status-text-' + deviceId);

        if(badgeElem && dotElem && textElem) {
            badgeElem.className = "inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-green-100 text-green-800";
            dotElem.className = "w-1.5 h-1.5 mr-1.5 rounded-full bg-green-500 animate-pulse";
            textElem.innerText = "Active";
        }
    }

    // Initialize Laravel Echo to listen to all channels
    window.addEventListener('DOMContentLoaded', () => {
        if (window.Echo) {
            console.log('Echo initialized, subscribing to channels...');
            @foreach($groups as $group)
                @foreach($group->devices as $device)
                    window.Echo.channel('telemetry.{{ $device->device_id }}')
                        .listen('TelemetryUpdated', (e) => {
                            console.log('Received telemetry for {{ $device->device_id }}:', e.data);
                            updateDeviceUI("{{ $device->device_id }}", e.data);
                        });
                @endforeach
            @endforeach
        } else {
            console.error('Laravel Echo is not available.');
        }

        // Periodically check if devices are active/inactive (offline status check)
        setInterval(() => {
            const currentTimestamp = Math.floor(Date.now() / 1000);
            
            @foreach($groups as $group)
                @foreach($group->devices as $device)
                    (function() {
                        const cardElem = document.getElementById('device-card-{{ $device->device_id }}');
                        if (cardElem) {
                            const lastSeen = parseInt(cardElem.getAttribute('data-last-seen')) || 0;
                            const diff = currentTimestamp - lastSeen;
                            
                            // If last seen is older than 15 seconds, mark as Inactive
                            if (lastSeen === 0 || diff >= 15) {
                                const badgeElem = document.getElementById('status-{{ $device->device_id }}');
                                const dotElem = document.getElementById('status-dot-{{ $device->device_id }}');
                                const textElem = document.getElementById('status-text-{{ $device->device_id }}');

                                if (badgeElem && dotElem && textElem && textElem.innerText === "Active") {
                                    badgeElem.className = "inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-red-100 text-red-800";
                                    dotElem.className = "w-1.5 h-1.5 mr-1.5 rounded-full bg-red-500";
                                    textElem.innerText = "Inactive";
                                }
                            }
                        }
                    })();
                @endforeach
            @endforeach
        }, 5000); // Check every 5 seconds
    });
</script>

<script>
    const chartDataRaw = {!! json_encode($chartData) !!};
    let energyChartInstance = null;
    let currentPeriod = 'daily';
    let currentMetric = 'energy';

    function initChart() {
        const ctx = document.getElementById('energyChart').getContext('2d');
        
        energyChartInstance = new Chart(ctx, {
            type: 'line',
            data: {
                labels: [],
                datasets: [{
                    label: 'Energy Usage',
                    data: [],
                    borderColor: '#3b82f6',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    borderWidth: 2.5,
                    pointBackgroundColor: '#ffffff',
                    pointBorderColor: '#3b82f6',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: 'rgba(17, 24, 39, 0.9)',
                        titleFont: { size: 13, family: "'Inter', sans-serif" },
                        bodyFont: { size: 13, family: "'Inter', sans-serif" },
                        padding: 12,
                        displayColors: false,
                        callbacks: {
                            label: function(context) {
                                const val = context.parsed.y;
                                if (currentMetric === 'energy') {
                                    return val.toLocaleString('id-ID', {minimumFractionDigits:2, maximumFractionDigits:2}) + ' kWh';
                                } else {
                                    return 'Rp ' + val.toLocaleString('id-ID', {minimumFractionDigits:0, maximumFractionDigits:0});
                                }
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { display: false, drawBorder: false },
                        ticks: { font: { family: "'Inter', sans-serif", size: 11 }, color: '#6b7280' }
                    },
                    y: {
                        border: { display: false },
                        grid: { color: '#f3f4f6', drawBorder: false },
                        ticks: {
                            font: { family: "'Inter', sans-serif", size: 11 },
                            color: '#6b7280',
                            callback: function(value) {
                                if (currentMetric === 'energy') {
                                    return value + ' kWh';
                                } else {
                                    return 'Rp ' + value.toLocaleString('id-ID', {maximumFractionDigits:0});
                                }
                            }
                        },
                        beginAtZero: true
                    }
                },
                interaction: {
                    intersect: false,
                    mode: 'index',
                },
            }
        });
        
        setTimeout(window.updateChart, 100);
    }

    window.changePeriod = function(period) {
        currentPeriod = period;
        window.updateChart();
    }

    window.changeMetric = function(metric) {
        currentMetric = metric;
        window.updateChart();
    }

    window.updateChart = function() {
        const ctx = document.getElementById('energyChart').getContext('2d');
        
        // Update Period Buttons UI
        ['daily', 'monthly', 'yearly'].forEach(p => {
            const btn = document.getElementById('btn-chart-' + p);
            if(btn) {
                if (p === currentPeriod) {
                    btn.className = 'px-3 py-1.5 text-xs font-semibold rounded-md bg-white text-blue-600 shadow-sm border border-gray-200 transition-all';
                } else {
                    btn.className = 'px-3 py-1.5 text-xs font-medium rounded-md text-gray-500 hover:text-gray-900 transition-all border border-transparent';
                }
            }
        });

        // Update Metric Buttons UI
        ['energy', 'cost'].forEach(m => {
            const btn = document.getElementById('btn-metric-' + m);
            if(btn) {
                if (m === currentMetric) {
                    btn.className = 'px-3 py-1.5 text-xs font-semibold rounded-md bg-white text-blue-600 shadow-sm border border-gray-200 transition-all';
                } else {
                    btn.className = 'px-3 py-1.5 text-xs font-medium rounded-md text-gray-500 hover:text-gray-900 transition-all border border-transparent';
                }
            }
        });

        // Update title and styling based on metric
        const titleEl = document.getElementById('chart-title');
        let borderColor = '#3b82f6'; // blue
        let gradientColor = 'rgba(59, 130, 246, 0.4)';
        
        if (currentMetric === 'energy') {
            if (titleEl) titleEl.innerText = "Energy Consumption Trend";
            borderColor = '#3b82f6';
            gradientColor = 'rgba(59, 130, 246, 0.4)';
        } else {
            if (titleEl) titleEl.innerText = "Estimated Cost Analytics";
            borderColor = '#10b981'; // emerald
            gradientColor = 'rgba(16, 185, 129, 0.4)';
        }

        const gradient = ctx.createLinearGradient(0, 0, 0, 300);
        gradient.addColorStop(0, gradientColor);
        gradient.addColorStop(1, 'rgba(255, 255, 255, 0)');

        const dataset = chartDataRaw[currentPeriod] || [];
        const labels = dataset.map(item => item.label);
        const data = dataset.map(item => {
            const val = parseFloat(item.total);
            return currentMetric === 'energy' ? val : (val * plnTariff);
        });

        if (energyChartInstance) {
            energyChartInstance.data.labels = labels;
            energyChartInstance.data.datasets[0].data = data;
            energyChartInstance.data.datasets[0].label = currentMetric === 'energy' ? 'Energy (kWh)' : 'Cost (Rp)';
            energyChartInstance.data.datasets[0].borderColor = borderColor;
            energyChartInstance.data.datasets[0].backgroundColor = gradient;
            energyChartInstance.data.datasets[0].pointBorderColor = borderColor;
            energyChartInstance.update();
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        initChart();
    });
</script>
@endsection
