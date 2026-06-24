@extends('layouts.app')

@section('content')
<!-- Ambient Glow Style Overrides for Light Glassmorphic Dashboard Widgets -->
<style>
    .glass-card {
        background: rgba(255, 255, 255, 0.45);
        backdrop-filter: blur(16px);
        -webkit-backdrop-filter: blur(16px);
        border: 1px solid rgba(0, 0, 0, 0.06);
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        color: #0f172a;
    }
    .glass-card:hover {
        background: rgba(255, 255, 255, 0.7);
        border-color: rgba(0, 0, 0, 0.1);
    }
    
    /* Neon glow card variations for light theme */
    .glow-cyan:hover {
        border-color: rgba(6, 182, 212, 0.35);
        box-shadow: 0 10px 30px rgba(6, 182, 212, 0.08);
        transform: translateY(-2px);
    }
    .glow-emerald:hover {
        border-color: rgba(16, 185, 129, 0.35);
        box-shadow: 0 10px 30px rgba(16, 185, 129, 0.08);
        transform: translateY(-2px);
    }
    .glow-amber:hover {
        border-color: rgba(245, 158, 11, 0.35);
        box-shadow: 0 10px 30px rgba(245, 158, 11, 0.08);
        transform: translateY(-2px);
    }
    .glow-indigo:hover {
        border-color: rgba(99, 102, 241, 0.35);
        box-shadow: 0 10px 30px rgba(99, 102, 241, 0.08);
        transform: translateY(-2px);
    }

    /* Active Device styling for light theme */
    .device-active {
        border-color: rgba(16, 185, 129, 0.3) !important;
        background: rgba(255, 255, 255, 0.75) !important;
        box-shadow: 0 8px 30px rgba(16, 185, 129, 0.05);
    }
    
    /* Telemetry update flash effect for light theme */
    @keyframes telemetry-flash {
        0% { box-shadow: 0 0 0px rgba(59, 130, 246, 0); border-color: rgba(0, 0, 0, 0.06); }
        30% { box-shadow: 0 0 25px rgba(59, 130, 246, 0.25); border-color: rgba(59, 130, 246, 0.5); }
        100% { box-shadow: 0 8px 30px rgba(16, 185, 129, 0.05); border-color: rgba(16, 185, 129, 0.3); }
    }
    .flash-active {
        animation: telemetry-flash 1s ease-out;
    }
</style>

<div class="mb-10 flex flex-col md:flex-row md:items-end md:justify-between gap-4">
    <div>
        <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight mb-2">Executive Summary</h1>
        <p class="text-slate-500 font-medium">Real-time energy consumption and operational metrics from your IoT devices.</p>
    </div>
    <!-- Real-time Clock (Top Right of Content Area) -->
    <div class="flex items-center text-xs font-bold text-slate-600 bg-white/80 border border-slate-200/80 px-4 py-2.5 rounded-2xl gap-2 font-mono shadow-sm backdrop-blur-md self-start md:self-end">
        <span class="relative flex h-2 w-2">
            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
            <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
        </span>
        <span id="realtime-clock">--:--:--</span>
    </div>
</div>

<!-- Executive Summary Cards -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
    <!-- PLN Tariff -->
    <div class="glass-card glow-cyan rounded-3xl p-6 flex items-center justify-between shadow-sm">
        <div class="space-y-2">
            <p class="text-xs font-bold text-slate-500 uppercase tracking-widest">PLN Tariff</p>
            <p class="text-2xl font-extrabold text-slate-900">
                Rp {{ number_format($plnTariff, 2, ',', '.') }} <span class="text-xs font-medium text-slate-500">/ kWh</span>
            </p>
        </div>
        <div class="p-4 bg-cyan-50 text-cyan-600 rounded-2xl border border-cyan-100">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
        </div>
    </div>

    <!-- Total Energy Today -->
    <div class="glass-card glow-emerald rounded-3xl p-6 flex items-center justify-between shadow-sm">
        <div class="space-y-2">
            <p class="text-xs font-bold text-slate-500 uppercase tracking-widest">Total Energy Today</p>
            <p class="text-3xl font-black text-emerald-600 tracking-tight">
                <span id="total-energy-value">{{ number_format($totalVolatileKwh, 3, ',', '.') }}</span> <span class="text-sm font-semibold text-slate-550">kWh</span>
            </p>
        </div>
        <div class="p-4 bg-emerald-50 text-emerald-600 rounded-2xl border border-emerald-100 animate-pulse">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
            </svg>
        </div>
    </div>

    <!-- Estimated Cost -->
    <div class="glass-card glow-amber rounded-3xl p-6 flex items-center justify-between shadow-sm">
        <div class="space-y-2">
            <p class="text-xs font-bold text-slate-500 uppercase tracking-widest">Estimated Daily Cost</p>
            <p class="text-3xl font-black text-amber-500 tracking-tight">
                Rp <span id="estimated-cost-value">{{ number_format($estimatedCost, 2, ',', '.') }}</span>
            </p>
        </div>
        <div class="p-4 bg-amber-50 text-amber-600 rounded-2xl border border-amber-100">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
            </svg>
        </div>
    </div>

    <!-- Projected Monthly Bill -->
    <div class="glass-card glow-indigo rounded-3xl p-6 flex items-center justify-between shadow-sm">
        <div class="space-y-2">
            <p class="text-xs font-bold text-slate-500 uppercase tracking-widest">Projected Monthly Bill</p>
            <p class="text-3xl font-black text-indigo-600 tracking-tight">
                Rp <span id="projected-cost-value">{{ number_format($projectedBilling, 2, ',', '.') }}</span>
            </p>
        </div>
        <div class="p-4 bg-indigo-50 text-indigo-600 rounded-2xl border border-indigo-100">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 002 2h2a2 2 0 002-2z" />
            </svg>
        </div>
    </div>
</div>

<!-- Energy & Consumers Grid -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-12">
    <!-- Energy Consumption Graph -->
    <div class="lg:col-span-2 glass-card rounded-3xl p-6 flex flex-col justify-between shadow-sm">
        <div>
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
                <div>
                    <h2 class="text-xl font-bold text-slate-900 tracking-tight" id="chart-title">Energy Consumption Trend</h2>
                    <p class="text-xs text-slate-500 font-medium mt-1">Historical analytics of electricity utilization and operational costs</p>
                </div>
                <div class="flex flex-wrap items-center gap-3">
                    <!-- Period Selector -->
                    <div class="flex bg-slate-100 p-1 rounded-xl border border-slate-200/80 shadow-inner">
                        <button type="button" onclick="changePeriod('daily')" id="btn-chart-daily" class="px-3.5 py-1.5 text-xs font-bold rounded-lg bg-white text-blue-600 border border-slate-200 shadow-sm transition-all duration-300">Daily</button>
                        <button type="button" onclick="changePeriod('monthly')" id="btn-chart-monthly" class="px-3.5 py-1.5 text-xs font-semibold rounded-lg text-slate-500 hover:text-slate-800 transition-all border border-transparent">Monthly</button>
                        <button type="button" onclick="changePeriod('yearly')" id="btn-chart-yearly" class="px-3.5 py-1.5 text-xs font-semibold rounded-lg text-slate-500 hover:text-slate-800 transition-all border border-transparent">Yearly</button>
                    </div>
                    <!-- Metric Selector -->
                    <div class="flex bg-slate-100 p-1 rounded-xl border border-slate-200/80 shadow-inner">
                        <button type="button" onclick="changeMetric('energy')" id="btn-metric-energy" class="px-3.5 py-1.5 text-xs font-bold rounded-lg bg-white text-blue-600 border border-slate-200 shadow-sm transition-all duration-300">Energy (kWh)</button>
                        <button type="button" onclick="changeMetric('cost')" id="btn-metric-cost" class="px-3.5 py-1.5 text-xs font-semibold rounded-lg text-slate-500 hover:text-slate-800 transition-all border border-transparent">Cost (Rp)</button>
                    </div>
                </div>
            </div>
            <div class="relative h-80 w-full">
                <canvas id="energyChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Top 3 Energy Consumers -->
    <div class="glass-card rounded-3xl p-6 flex flex-col justify-between shadow-sm">
        <div>
            <div class="mb-5">
                <h3 class="text-lg font-bold text-slate-900 tracking-tight">Top 3 Energy Consumers</h3>
                <p class="text-xs text-slate-500 font-medium mt-1">Devices with the highest cumulative energy usage (kWh)</p>
            </div>
            
            <div class="space-y-3.5">
                @forelse($topDevices as $index => $topDevice)
                    <div class="flex items-center justify-between p-4.5 rounded-2xl bg-white/60 border border-slate-200/60 hover:bg-white hover:border-slate-350 transition-all duration-300 shadow-sm">
                        <div class="flex items-center space-x-3.5">
                            <div class="flex-shrink-0 w-9 h-9 rounded-xl flex items-center justify-center font-bold text-sm shadow-inner
                                {{ $index === 0 ? 'bg-amber-100 text-amber-700 border border-amber-200' : ($index === 1 ? 'bg-slate-100 text-slate-700 border border-slate-200' : 'bg-orange-100 text-orange-700 border border-orange-200') }}">
                                #{{ $index + 1 }}
                            </div>
                            <div>
                                <h4 class="text-sm font-bold text-slate-800 leading-snug">{{ $topDevice['name'] }}</h4>
                                <span class="inline-block mt-1 px-2.5 py-0.5 text-[9px] font-bold bg-blue-50 text-blue-600 rounded-full uppercase tracking-wider border border-blue-100">{{ $topDevice['group_name'] }}</span>
                            </div>
                        </div>
                        <div class="text-right">
                            <span class="text-sm font-extrabold text-slate-900 tracking-wide">{{ number_format($topDevice['energy'], 3, ',', '.') }}</span>
                            <span class="text-[9px] font-bold text-slate-450 block uppercase tracking-widest mt-0.5">kWh</span>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-8 text-slate-400 text-sm">
                        No active telemetry devices found.
                    </div>
                @endforelse
            </div>
        </div>

        <div class="mt-6 pt-4 border-t border-slate-100 flex items-center justify-between">
            <span class="text-[10px] font-semibold text-slate-400">Updated in real-time</span>
            <a href="{{ route('logs.index') }}" class="text-xs font-bold text-blue-600 hover:text-blue-700 transition-colors flex items-center gap-1 group">
                View Detailed Logs
                <svg class="w-3 h-3 transform group-hover:translate-x-0.5 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" />
                </svg>
            </a>
        </div>
    </div>
</div>

<!-- Group Areas and Devices -->
<div class="space-y-10">
    @forelse($groups as $group)
        <div class="glass-card rounded-3xl p-6 shadow-sm border border-slate-200/80">
            <div class="flex items-center justify-between mb-6 pb-4 border-b border-slate-200/85">
                <div class="flex items-center space-x-3">
                    <div class="p-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-500">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-slate-900 tracking-tight">{{ $group->name }}</h2>
                        <p class="text-xs text-slate-500 font-medium">Operational Area</p>
                    </div>
                </div>
                <span class="text-xs font-bold px-3 py-1 bg-slate-100 border border-slate-200 text-slate-650 rounded-full">
                    {{ $group->devices->count() }} Devices
                </span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @forelse($group->devices as $device)
                    @php
                        $isDeviceActive = (now()->timestamp - $device->last_seen) < 15;
                    @endphp
                    <div class="bg-white/40 rounded-2xl border border-slate-200/60 p-5 flex flex-col justify-between hover:bg-white hover:-translate-y-0.5 hover:border-slate-350 transition-all duration-300 shadow-sm {{ $isDeviceActive ? 'device-active' : '' }}" id="device-card-{{ $device->device_id }}" data-last-seen="{{ $device->last_seen }}" data-device-id="{{ $device->device_id }}">
                        
                        <!-- Card Header -->
                        <div class="flex items-start justify-between mb-5 gap-4">
                            <div>
                                <h3 class="text-base font-bold text-slate-800 tracking-tight">{{ $device->name }}</h3>
                                <p class="text-[10px] text-slate-400 font-mono mt-0.5 tracking-wider">{{ $device->device_id }}</p>
                            </div>
                            
                            <!-- Status Badge -->
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $isDeviceActive ? 'bg-green-50 text-green-700 border border-green-200' : 'bg-red-50 text-red-700 border border-red-200' }}" id="status-{{ $device->device_id }}">
                                <span class="w-1.5 h-1.5 mr-1.5 rounded-full {{ $isDeviceActive ? 'bg-green-500 animate-pulse' : 'bg-red-500' }}" id="status-dot-{{ $device->device_id }}"></span>
                                <span id="status-text-{{ $device->device_id }}">{{ $isDeviceActive ? 'Active' : 'Inactive' }}</span>
                            </span>
                        </div>

                        <!-- PZEM Metrics Grid -->
                        <div class="grid grid-cols-2 gap-3 mb-5">
                            <div class="bg-white rounded-xl p-3 border border-slate-100 shadow-sm">
                                <span class="text-[9px] uppercase font-extrabold text-slate-450 block tracking-widest mb-0.5">Voltage</span>
                                <span class="text-base font-bold text-slate-800"><span id="voltage-{{ $device->device_id }}">{{ number_format(Cache::get("voltage:{$device->device_id}", 0), 1) }}</span> <span class="text-xs text-slate-400 font-normal">V</span></span>
                            </div>
                            <div class="bg-white rounded-xl p-3 border border-slate-100 shadow-sm">
                                <span class="text-[9px] uppercase font-extrabold text-slate-450 block tracking-widest mb-0.5">Current</span>
                                <span class="text-base font-bold text-slate-800"><span id="current-{{ $device->device_id }}">{{ number_format(Cache::get("current:{$device->device_id}", 0), 2) }}</span> <span class="text-xs text-slate-400 font-normal">A</span></span>
                            </div>
                            <div class="bg-white rounded-xl p-3 border border-slate-100 shadow-sm">
                                <span class="text-[9px] uppercase font-extrabold text-slate-450 block tracking-widest mb-0.5">Power</span>
                                <span class="text-base font-bold text-slate-800"><span id="power-{{ $device->device_id }}">{{ number_format(Cache::get("power:{$device->device_id}", 0), 1) }}</span> <span class="text-xs text-slate-400 font-normal">W</span></span>
                            </div>
                            <div class="bg-blue-50 rounded-xl p-3 border border-blue-100 shadow-sm">
                                <span class="text-[9px] uppercase font-extrabold text-blue-500 block tracking-widest mb-0.5">Energy</span>
                                <span class="text-base font-bold text-blue-700"><span id="energy-{{ $device->device_id }}">{{ number_format(Cache::get("daily_energy:{$device->device_id}", 0), 3) }}</span> <span class="text-xs text-blue-500 font-normal">kWh</span></span>
                            </div>
                            <div class="bg-emerald-50 rounded-xl p-3 border border-emerald-100 shadow-sm col-span-2 flex items-center justify-between">
                                <div>
                                    <span class="text-[9px] uppercase font-extrabold text-emerald-600 block tracking-widest mb-0.5">Estimated Cost</span>
                                    <span class="text-base font-extrabold text-emerald-700">
                                        Rp <span id="cost-{{ $device->device_id }}">{{ number_format(Cache::get("daily_energy:{$device->device_id}", 0) * $plnTariff, 0, ',', '.') }}</span>
                                    </span>
                                </div>
                                <div class="p-2 bg-emerald-100 border border-emerald-200 rounded-lg text-emerald-600">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                            </div>
                        </div>

                        <!-- Card Actions -->
                        @if(auth()->user()->role === 'admin')
                            <div class="flex items-center justify-between border-t border-slate-100 pt-3 mt-auto">
                                <a href="{{ route('devices.show', $device->id) }}" class="text-xs font-bold text-blue-600 hover:text-blue-500 flex items-center gap-1 transition-colors">
                                    View Details
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" /></svg>
                                </a>
                                
                                <form action="{{ route('devices.destroy', $device->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this device?');" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-500 hover:text-red-600 p-2 hover:bg-red-50 rounded-xl transition-all" title="Delete Device">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        @else
                            <div class="text-[10px] text-slate-450 font-bold text-center border-t border-slate-100 pt-3 mt-auto uppercase tracking-widest">
                                Read Only Access
                            </div>
                        @endif
                    </div>
                @empty
                    <div class="col-span-full py-8 text-center text-slate-400 font-medium border border-dashed border-slate-200 rounded-2xl">
                        No active devices in this area.
                    </div>
                @endforelse
            </div>
        </div>
    @empty
        <div class="glass-card rounded-3xl p-12 text-center shadow-sm border border-slate-200">
            <svg class="mx-auto h-12 w-12 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
            </svg>
            <h3 class="mt-4 text-lg font-bold text-slate-900">No Operational Areas</h3>
            <p class="mt-2 text-sm text-slate-500 max-w-sm mx-auto">Create a group and add devices to start monitoring real-time power consumption.</p>
        </div>
    @endforelse
</div>

@if(auth()->user()->role === 'admin')
<!-- Add Device Modal -->
<div id="add-device-modal" class="hidden fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <!-- Background backdrop -->
        <div class="fixed inset-0 bg-slate-500/75 backdrop-blur-sm transition-opacity" aria-hidden="true" onclick="document.getElementById('add-device-modal').classList.add('hidden')"></div>

        <!-- Spacer to center modal -->
        <span class="hidden sm:inline-block sm:align-middle sm:min-h-screen" aria-hidden="true">&#8203;</span>

        <!-- Modal Panel -->
        <div class="inline-block align-middle bg-white rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-slate-200">
            <form action="{{ route('devices.store') }}" method="POST">
                @csrf
                <div class="bg-white px-6 pt-6 pb-4 sm:p-6 sm:pb-4 border-b border-slate-100">
                    <div class="flex items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-2xl bg-blue-50 text-blue-600 sm:mx-0 sm:h-10 sm:w-10 border border-blue-100">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                            <h3 class="text-lg font-bold text-slate-900 tracking-tight" id="modal-title">Register New Device</h3>
                            <p class="text-xs text-slate-500 mb-6">Enter details below to provision a new energy monitoring node.</p>
                            
                            <div class="space-y-4">
                                <div>
                                    <label for="name" class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">Device Name</label>
                                    <input type="text" name="name" id="name" required placeholder="e.g. Compressor Pump A" class="w-full rounded-xl bg-white border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500/25 text-sm text-slate-900 placeholder-slate-400">
                                </div>

                                <div>
                                    <label for="group_id" class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">Group Area</label>
                                    <select name="group_id" id="group_id" required class="w-full rounded-xl bg-white border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500/25 text-sm text-slate-900">
                                        @foreach($groups as $g)
                                            <option value="{{ $g->id }}">{{ $g->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <label for="wifi_ssid" class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">Wifi SSID</label>
                                    <input type="text" name="wifi_ssid" id="wifi_ssid" required placeholder="SSID Wifi Name" class="w-full rounded-xl bg-white border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500/25 text-sm text-slate-900 placeholder-slate-400">
                                </div>

                                <div>
                                    <label for="wifi_password" class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">Wifi Password</label>
                                    <input type="password" name="wifi_password" id="wifi_password" required placeholder="••••••••" class="w-full rounded-xl bg-white border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500/25 text-sm text-slate-900 placeholder-slate-400">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-slate-50 px-6 py-4 sm:flex sm:flex-row-reverse gap-2">
                    <button type="submit" class="w-full inline-flex justify-center rounded-xl border border-transparent shadow-sm px-4 py-2.5 bg-blue-600 text-sm font-bold text-white hover:bg-blue-500 focus:outline-none sm:ml-3 sm:w-auto transition-colors">
                        Register & Get Code
                    </button>
                    <button type="button" onclick="document.getElementById('add-device-modal').classList.add('hidden')" class="mt-3 w-full inline-flex justify-center rounded-xl border border-slate-300 shadow-sm px-4 py-2.5 bg-white text-sm font-semibold text-slate-700 hover:bg-slate-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto transition-colors">
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
    
    const energyRegistry = {
        @foreach($groups as $group)
            @foreach($group->devices as $device)
                "{{ $device->device_id }}": {{ Cache::get("daily_energy:{$device->device_id}", 0) }},
            @endforeach
        @endforeach
    };

    const costRegistry = {
        @foreach($groups as $group)
            @foreach($group->devices as $device)
                "{{ $device->device_id }}": {{ Cache::get("daily_cost:{$device->device_id}", Cache::get("daily_energy:{$device->device_id}", 0) * $plnTariff) }},
            @endforeach
        @endforeach
    };

    function recalculateTotalEnergy() {
        let totalEnergy = 0;
        let totalCost = 0;
        for (const deviceId in energyRegistry) {
            totalEnergy += energyRegistry[deviceId];
        }
        for (const deviceId in costRegistry) {
            totalCost += costRegistry[deviceId];
        }
        
        // Update DOM elements
        document.getElementById('total-energy-value').innerText = totalEnergy.toLocaleString('id-ID', { minimumFractionDigits: 3, maximumFractionDigits: 3 });
        document.getElementById('estimated-cost-value').innerText = totalCost.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    // Function to update UI dynamically when telemetries are received
    function updateDeviceUI(deviceId, data) {
        // Find card
        const cardElem = document.getElementById('device-card-' + deviceId);
        
        // Apply flash animation to indicate live WebSocket push in light theme
        if(cardElem) {
            cardElem.classList.remove('flash-active');
            void cardElem.offsetWidth; // Trigger reflow
            cardElem.classList.add('flash-active');
            cardElem.classList.add('device-active');
            cardElem.setAttribute('data-last-seen', Math.floor(Date.now() / 1000));
        }

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
            const rawCost = data.cost !== undefined ? parseFloat(data.cost) : (parseFloat(data.energy) * plnTariff);
            if(costElem) {
                costElem.innerText = rawCost.toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
            }
            
            // Keep track of latest energy & cost for this device
            energyRegistry[deviceId] = parseFloat(data.energy);
            costRegistry[deviceId] = rawCost;
            recalculateTotalEnergy();
        }

        // Toggle Status Badge to Active
        const badgeElem = document.getElementById('status-' + deviceId);
        const dotElem = document.getElementById('status-dot-' + deviceId);
        const textElem = document.getElementById('status-text-' + deviceId);

        if(badgeElem && dotElem && textElem) {
            badgeElem.className = "inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-green-50 text-green-700 border border-green-200";
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
                                cardElem.classList.remove('device-active');
                                const badgeElem = document.getElementById('status-{{ $device->device_id }}');
                                const dotElem = document.getElementById('status-dot-{{ $device->device_id }}');
                                const textElem = document.getElementById('status-text-{{ $device->device_id }}');

                                if (badgeElem && dotElem && textElem && textElem.innerText === "Active") {
                                    badgeElem.className = "inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-red-50 text-red-700 border border-red-200";
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
                    backgroundColor: 'rgba(59, 130, 246, 0.04)',
                    borderWidth: 3,
                    pointBackgroundColor: '#ffffff',
                    pointBorderColor: '#3b82f6',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    fill: true,
                    tension: 0.35
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: 'rgba(15, 23, 42, 0.95)',
                        borderColor: 'rgba(0, 0, 0, 0.05)',
                        borderWidth: 1,
                        titleFont: { size: 12, family: "'Inter', sans-serif", weight: 'bold' },
                        bodyFont: { size: 12, family: "'Inter', sans-serif" },
                        padding: 12,
                        cornerRadius: 12,
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
                        grid: { display: false },
                        ticks: { font: { family: "'Inter', sans-serif", size: 10, weight: 'bold' }, color: '#64748b' }
                    },
                    y: {
                        border: { display: false },
                        grid: { color: 'rgba(0, 0, 0, 0.04)' },
                        ticks: {
                            font: { family: "'Inter', sans-serif", size: 10, weight: 'bold' },
                            color: '#64748b',
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
                    btn.className = 'px-3.5 py-1.5 text-xs font-bold rounded-lg bg-white text-blue-600 border border-slate-200 shadow-sm transition-all duration-300';
                } else {
                    btn.className = 'px-3.5 py-1.5 text-xs font-semibold rounded-lg text-slate-500 hover:text-slate-800 transition-all border border-transparent';
                }
            }
        });

        // Update Metric Buttons UI
        ['energy', 'cost'].forEach(m => {
            const btn = document.getElementById('btn-metric-' + m);
            if(btn) {
                if (m === currentMetric) {
                    btn.className = 'px-3.5 py-1.5 text-xs font-bold rounded-lg bg-white text-blue-600 border border-slate-200 shadow-sm transition-all duration-300';
                } else {
                    btn.className = 'px-3.5 py-1.5 text-xs font-semibold rounded-lg text-slate-500 hover:text-slate-800 transition-all border border-transparent';
                }
            }
        });

        // Update title and styling based on metric
        const titleEl = document.getElementById('chart-title');
        let borderColor = '#3b82f6'; // blue
        let gradientColor = 'rgba(59, 130, 246, 0.15)';
        
        if (currentMetric === 'energy') {
            if (titleEl) titleEl.innerText = "Energy Consumption Trend";
            borderColor = '#3b82f6';
            gradientColor = 'rgba(59, 130, 246, 0.15)';
        } else {
            if (titleEl) titleEl.innerText = "Estimated Cost Analytics";
            borderColor = '#10b981'; // emerald
            gradientColor = 'rgba(16, 185, 129, 0.15)';
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
