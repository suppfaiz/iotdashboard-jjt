@extends('layouts.app')

@section('content')
<!-- Ambient Glow Style Overrides for Light Glassmorphic Dashboard Widgets -->
<style>
    .glass-card {
        background: rgba(255, 255, 255, 0.45);
        backdrop-filter: blur(16px);
        -webkit-backdrop-filter: blur(16px);
        border: 1px solid rgba(0, 0, 0, 0.06);
        transition: border-color 0.4s cubic-bezier(0.4, 0, 0.2, 1), box-shadow 0.4s cubic-bezier(0.4, 0, 0.2, 1), transform 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        color: #0f172a;
        transform: translateZ(0);
        backface-visibility: hidden;
        will-change: transform, box-shadow;
    }
    .glass-card:hover {
        background: rgba(255, 255, 255, 0.7);
        border-color: rgba(0, 0, 0, 0.1);
        transform: translateY(-2px) translateZ(0);
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

<!-- System Alerts & Status Banner -->
<div id="active-alerts-container" class="mb-10 {{ count($activeWarnings) > 0 ? '' : 'hidden' }} space-y-3">
    <div class="bg-rose-50/60 border border-rose-150 rounded-3xl p-5 shadow-sm backdrop-blur-md">
        <div class="flex items-center justify-between mb-3">
            <div class="flex items-center gap-2 text-rose-800 font-extrabold text-sm tracking-tight">
                <svg class="w-5 h-5 text-rose-500 animate-bounce" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                Real-time System Alerts & Warnings
            </div>
            <span id="alerts-count-badge" class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-rose-200 text-rose-800">
                {{ count($activeWarnings) }} Warnings
            </span>
        </div>
        <div id="alerts-list" class="space-y-2">
            @foreach($activeWarnings as $warning)
                <div class="flex items-center justify-between p-3 rounded-2xl bg-white/70 border border-rose-100 text-slate-800 text-xs font-semibold shadow-sm gap-4" id="alert-item-{{ $warning['device_id'] }}-{{ $warning['type'] }}">
                    <div class="flex items-center gap-2">
                        <span class="flex-shrink-0 w-2 h-2 rounded-full {{ $warning['severity'] === 'danger' ? 'bg-rose-500 animate-ping' : 'bg-amber-500' }}"></span>
                        <span><strong>{{ $warning['device_name'] }}</strong>: {{ $warning['message'] }}</span>
                    </div>
                    @if(!empty($electricianWhatsapp))
                        <a href="https://wa.me/{{ $electricianWhatsapp }}?text=Halo%20Bapak%2FIbu%2C%20kami%20ingin%20melaporkan%20adanya%20masalah%20kelistrikan%20pada%20sistem%20pemantauan%20daya%20IoT%20Jamkrida%20Jateng.%0A%0APerangkat%3A%20{{ urlencode($warning['device_name']) }}%0AMasalah%3A%20{{ urlencode($warning['message']) }}%0A%0AMohon%20bantuannya%20untuk%20memeriksa.%20Terima%20kasih." target="_blank" class="flex-shrink-0 px-2.5 py-1 bg-emerald-50 hover:bg-emerald-100 text-emerald-600 border border-emerald-100 rounded-lg transition-colors flex items-center gap-1 font-bold text-[10px]">
                            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946C.06 5.348 5.397.01 12.008.01c3.202.001 6.212 1.246 8.477 3.514 2.266 2.268 3.507 5.28 3.505 8.484-.004 6.657-5.34 11.997-11.953 11.997-2.005-.001-3.973-.5-5.739-1.453L0 24zm6.59-4.846c1.6.95 2.72 1.488 4.74 1.489 5.625 0 10.201-4.576 10.204-10.203.002-2.727-1.062-5.29-3.003-7.23C16.65 1.3 14.09.233 11.36.235c-5.632.001-10.21 4.582-10.213 10.211-.001 2.04.536 3.54 1.49 5.16L1.57 22.03l6.398-1.68c.002-.001.002-.001.002-.001z"/>
                            </svg>
                            Hubungi
                        </a>
                    @endif
                </div>
            @endforeach
        </div>
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
                        <button type="button" onclick="changePeriod('weekly')" id="btn-chart-weekly" class="px-3.5 py-1.5 text-xs font-semibold rounded-lg text-slate-500 hover:text-slate-800 transition-all border border-transparent">Weekly</button>
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
            <div class="relative h-60 sm:h-80 w-full">
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

<!-- Live Electrical Grid Flow Diagram -->
<div class="glass-card rounded-3xl p-6 shadow-sm border border-slate-200/80 mb-10 overflow-hidden relative">
    <div class="absolute inset-0 opacity-[0.015] bg-[radial-gradient(#000_1px,transparent_1px)] [background-size:16px_16px] pointer-events-none"></div>
    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-xl font-bold text-slate-900 tracking-tight">Live Electrical Grid Flow</h2>
            <p class="text-xs text-slate-500 font-medium mt-1">Real-time vector flow visualization of electricity distribution from PLN to area panels</p>
        </div>
        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-green-200">
            <span class="w-1.5 h-1.5 mr-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
            Live Monitoring
        </span>
    </div>

    <!-- SVG Container -->
    <div class="w-full overflow-x-auto">
        <div class="min-w-[800px] p-2">
            <svg viewBox="0 0 800 300" class="w-full h-auto select-none" style="background: rgba(248, 250, 252, 0.35); border-radius: 20px; border: 1px solid rgba(226, 232, 240, 0.8);">
                <style>
                    .grid-flow-line {
                        stroke-dasharray: 8, 8;
                        stroke-dashoffset: 0;
                        animation: grid-flow-anim 1.5s linear infinite;
                    }
                    @keyframes grid-flow-anim {
                        to {
                            stroke-dashoffset: -16;
                        }
                    }
                    .grid-pulse {
                        transform-origin: center;
                        animation: grid-pulse-anim 2.5s cubic-bezier(0.4, 0, 0.6, 1) infinite;
                    }
                    @keyframes grid-pulse-anim {
                        0%, 100% {
                            transform: scale(1);
                            opacity: 0.6;
                        }
                        50% {
                            transform: scale(1.15);
                            opacity: 0.15;
                        }
                    }
                    .grid-node circle {
                        transition: stroke-width 0.3s ease, stroke 0.3s ease, fill 0.3s ease;
                    }
                </style>
                <!-- Definitions for markers and glow filters -->
                <defs>
                    <!-- Grid pattern inside the SVG for engineering style -->
                    <pattern id="grid-pattern" width="20" height="20" patternUnits="userSpaceOnUse">
                        <path d="M 20 0 L 0 0 0 20" fill="none" stroke="rgba(226, 232, 240, 0.6)" stroke-width="1"/>
                    </pattern>
                </defs>

                <!-- Apply grid background pattern -->
                <rect width="100%" height="100%" fill="url(#grid-pattern)" />

                <!-- Connecting Flow Line: PLN to MDP -->
                <path d="M 120,150 L 260,150" fill="none" stroke="#e2e8f0" stroke-width="3.5" stroke-linecap="round" />
                <path id="flow-line-pln-mdp-active" d="M 120,150 L 260,150" fill="none" stroke="#10b981" stroke-width="3.5" stroke-linecap="round" class="grid-flow-line" />

                <!-- Dynamic Flow Lines: MDP to Groups -->
                @php
                    $numGroups = count($groups);
                    $groupFlowData = [];
                @endphp
                @foreach($groups as $index => $group)
                    @php
                        // Calculate Y coordinate based on group count
                        if ($numGroups <= 1) {
                            $y = 150;
                        } else {
                            $y = 50 + ($index * 200 / ($numGroups - 1));
                        }
                        $groupFlowData[$group->id] = [
                            'name' => $group->name,
                            'y' => $y
                        ];
                    @endphp
                    <!-- Backing line (inactive grey) -->
                    <path d="M 280,150 C 400,150 400,{{ $y }} 560,{{ $y }}" fill="none" stroke="#e2e8f0" stroke-width="3" stroke-linecap="round" />
                    <!-- Animated overlay line (active green) -->
                    <path id="flow-line-{{ $group->id }}" d="M 280,150 C 400,150 400,{{ $y }} 560,{{ $y }}" fill="none" stroke="#10b981" stroke-width="3.5" stroke-linecap="round" class="grid-flow-line" style="animation-duration: 2s;" />
                @endforeach

                <!-- 1. PLN Main Supply Node -->
                <g class="grid-node" transform="translate(80, 150)">
                    <circle r="36" fill="#f8fafc" stroke="#cbd5e1" stroke-width="2" />
                    <circle r="30" fill="rgba(59, 130, 246, 0.08)" stroke="#3b82f6" stroke-width="2.5" />
                    <!-- Lightning Bolt Icon -->
                    <path d="M -6,-12 L 8,-3 L -2,2 L 8,13 L -8,3 L 1,-2 Z" fill="#3b82f6" />
                    <text y="50" text-anchor="middle" class="text-[11px] font-black fill-slate-800">PLN SUPPLY</text>
                    <text y="64" text-anchor="middle" class="text-[9px] font-bold fill-blue-600 tracking-wider">220V SOURCE</text>
                </g>

                <!-- 2. MDP (Main Distribution Panel) Node -->
                <g class="grid-node" transform="translate(280, 150)">
                    <circle r="34" fill="#f8fafc" stroke="#cbd5e1" stroke-width="2" />
                    <circle r="28" fill="rgba(79, 70, 229, 0.06)" stroke="#4f46e5" stroke-width="2" />
                    <!-- Box/Grid Icon -->
                    <rect x="-10" y="-10" width="20" height="20" rx="3" fill="none" stroke="#4f46e5" stroke-width="2" />
                    <line x1="-10" y1="0" x2="10" y2="0" stroke="#4f46e5" stroke-width="1.5" />
                    <line x1="0" y1="-10" x2="0" y2="10" stroke="#4f46e5" stroke-width="1.5" />
                    <text y="48" text-anchor="middle" class="text-[10px] font-extrabold fill-slate-850">MAIN MDP</text>
                    <text y="60" text-anchor="middle" id="total-grid-power" class="text-[9px] font-bold fill-slate-500">0.0 W</text>
                </g>

                <!-- 3. Dynamic Group Nodes -->
                @foreach($groups as $index => $group)
                    @php
                        $y = $groupFlowData[$group->id]['y'];
                    @endphp
                    <g id="flow-node-{{ $group->id }}" class="grid-node" transform="translate(560, {{ $y }})">
                        <!-- Background glow effect ring -->
                        <circle r="30" fill="rgba(16, 185, 129, 0.04)" stroke="rgba(16, 185, 129, 0.2)" stroke-width="3" class="grid-pulse" />
                        <circle r="24" fill="#f8fafc" stroke="#cbd5e1" stroke-width="1.5" />
                        <circle r="20" fill="rgba(16, 185, 129, 0.08)" stroke="#10b981" stroke-width="2" id="node-circle-{{ $group->id }}" />
                        
                        <!-- Panel Icon inside node -->
                        <path d="M -6,-6 L 6,-6 L 6,6 L -6,6 Z M -6,0 L 6,0 M 0,-6 L 0,6" fill="none" stroke="#10b981" stroke-width="1.5" id="node-icon-{{ $group->id }}" />
                        
                        <!-- Group details on the right -->
                        <text x="35" y="-5" class="text-[12px] font-black fill-slate-900" text-anchor="start">{{ $group->name }}</text>
                        <text x="35" y="10" id="flow-group-power-{{ $group->id }}" class="text-[11px] font-extrabold fill-emerald-600" text-anchor="start">0.0 W</text>
                        <text x="35" y="22" id="flow-group-status-{{ $group->id }}" class="text-[8px] font-bold uppercase tracking-wider fill-emerald-500" text-anchor="start">Active</text>
                    </g>
                @endforeach
            </svg>
        </div>
    </div>
</div>

<!-- Group Areas and Devices -->
<div class="flex items-center justify-between mb-6 pb-2 border-b border-slate-200/50">
    <h2 class="text-xl font-bold text-slate-900 tracking-tight">Group Areas & Devices</h2>
    @if(auth()->user()->role === 'admin')
        <button type="button" onclick="document.getElementById('add-group-modal-dashboard').classList.remove('hidden')" class="relative inline-flex items-center gap-x-1.5 rounded-xl bg-indigo-600 px-5 py-2.5 text-xs font-bold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 transition-colors">
            <svg class="-ml-0.5 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            Add New Group
        </button>
    @endif
</div>
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
                    <div class="device-card bg-white/40 rounded-2xl border border-slate-200/60 p-5 flex flex-col justify-between hover:bg-white hover:-translate-y-0.5 hover:border-slate-350 transition-all duration-300 shadow-sm {{ $isDeviceActive ? 'device-active' : '' }}" id="device-card-{{ $device->device_id }}" data-last-seen="{{ $device->last_seen }}" data-device-id="{{ $device->device_id }}">
                        
                        <!-- Card Header -->
                        <div class="flex items-start justify-between mb-5 gap-4">
                            <div>
                                <h3 class="text-base font-bold text-slate-800 tracking-tight">{{ $device->name }}</h3>
                                <p class="text-[10px] text-slate-400 font-mono mt-0.5 tracking-wider">{{ $device->device_id }}</p>
                            </div>
                            
                            <!-- Status & WiFi Badge -->
                            <div class="flex flex-col items-end gap-1.5">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $isDeviceActive ? 'bg-green-50 text-green-700 border border-green-200' : 'bg-red-50 text-red-700 border border-red-200' }}" id="status-{{ $device->device_id }}">
                                    <span class="w-1.5 h-1.5 mr-1.5 rounded-full {{ $isDeviceActive ? 'bg-green-500 animate-pulse' : 'bg-red-500' }}" id="status-dot-{{ $device->device_id }}"></span>
                                    <span id="status-text-{{ $device->device_id }}">{{ $isDeviceActive ? 'Active' : 'Inactive' }}</span>
                                </span>
                                @php
                                    $rssi = Cache::get("rssi:{$device->device_id}", null);
                                @endphp
                                <span class="text-[10px] font-mono text-slate-550 flex items-center gap-1 {{ $isDeviceActive && $rssi !== null ? '' : 'hidden' }}" id="wifi-container-{{ $device->device_id }}">
                                    📶 <span id="wifi-rssi-{{ $device->device_id }}">{{ $rssi }} dBm</span>
                                </span>
                            </div>
                        </div>

                        <!-- PZEM Metrics Grid -->
                        <div class="grid grid-cols-2 gap-3 mb-5">
                            <div class="bg-white rounded-xl p-3 border border-slate-100 shadow-sm">
                                <span class="text-[9px] uppercase font-extrabold text-slate-450 block tracking-widest mb-0.5">Voltage</span>
                                <span class="text-base font-bold text-slate-800"><span id="voltage-{{ $device->device_id }}">{{ number_format($device->voltage, 1) }}</span> <span class="text-xs text-slate-400 font-normal">V</span></span>
                            </div>
                            <div class="bg-white rounded-xl p-3 border border-slate-100 shadow-sm">
                                <span class="text-[9px] uppercase font-extrabold text-slate-450 block tracking-widest mb-0.5">Current</span>
                                <span class="text-base font-bold text-slate-800"><span id="current-{{ $device->device_id }}">{{ number_format($device->current, 2) }}</span> <span class="text-xs text-slate-400 font-normal">A</span></span>
                            </div>
                            <div class="bg-white rounded-xl p-3 border border-slate-100 shadow-sm">
                                <span class="text-[9px] uppercase font-extrabold text-slate-450 block tracking-widest mb-0.5">Power</span>
                                <span class="text-base font-bold text-slate-800"><span id="power-{{ $device->device_id }}">{{ number_format($device->power, 1) }}</span> <span class="text-xs text-slate-400 font-normal">W</span></span>
                            </div>
                            <div class="bg-blue-50 rounded-xl p-3 border border-blue-100 shadow-sm">
                                <span class="text-[9px] uppercase font-extrabold text-blue-500 block tracking-widest mb-0.5">Energy</span>
                                <span class="text-base font-bold text-blue-700"><span id="energy-{{ $device->device_id }}">{{ number_format($device->energy, 3) }}</span> <span class="text-xs text-blue-500 font-normal">kWh</span></span>
                            </div>
                            <div class="bg-emerald-50 rounded-xl p-3 border border-emerald-100 shadow-sm col-span-2 flex items-center justify-between">
                                <div>
                                    <span class="text-[9px] uppercase font-extrabold text-emerald-600 block tracking-widest mb-0.5">Estimated Cost</span>
                                    <span class="text-base font-extrabold text-emerald-700">
                                        Rp <span id="cost-{{ $device->device_id }}">{{ number_format($device->energy * $plnTariff, 0, ',', '.') }}</span>
                                    </span>
                                </div>
                                <div class="p-2 bg-emerald-100 border border-emerald-200 rounded-lg text-emerald-600">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                            </div>
                        </div>

                        @if($device->monthly_budget_kwh || $device->monthly_budget_cost)
                            <div class="border-t border-slate-100 pt-3.5 mt-2.5 mb-4">
                                <span class="text-[9px] uppercase font-extrabold text-slate-450 block tracking-widest mb-2">Monthly Budget Forecast</span>
                                
                                @if($device->monthly_budget_kwh)
                                    @php
                                        $kwhPercent = $device->monthly_budget_kwh > 0 ? ($device->current_month_kwh / $device->monthly_budget_kwh) * 100 : 0;
                                        $projectedKwhPercent = $device->monthly_budget_kwh > 0 ? ($device->projected_kwh / $device->monthly_budget_kwh) * 100 : 0;
                                        $kwhExceeded = $projectedKwhPercent > 100;
                                        $kwhWarning = $projectedKwhPercent > 80 && $projectedKwhPercent <= 100;
                                    @endphp
                                    <div class="mb-3">
                                        <div class="flex justify-between text-[10px] font-bold text-slate-650 mb-1">
                                            <span>Consumption (kWh)</span>
                                            <span>{{ number_format($device->current_month_kwh, 1, ',', '.') }} / {{ number_format($device->monthly_budget_kwh, 0, ',', '.') }} kWh</span>
                                        </div>
                                        <div class="w-full bg-slate-100/80 rounded-full h-1.5 overflow-hidden">
                                            <div class="h-full rounded-full {{ $kwhExceeded ? 'bg-red-500' : ($kwhWarning ? 'bg-amber-500' : 'bg-emerald-500') }}" style="width: {{ min(100, $kwhPercent) }}%"></div>
                                        </div>
                                        <div class="flex justify-between text-[9px] mt-1 font-semibold">
                                            <span class="text-slate-400">Projection: {{ number_format($device->projected_kwh, 1, ',', '.') }} kWh</span>
                                            @if($kwhExceeded)
                                                <span class="text-red-500 font-bold">⚠️ Over Limit (+{{ number_format($projectedKwhPercent - 100, 0) }}%)</span>
                                            @elseif($kwhWarning)
                                                <span class="text-amber-500 font-bold">⚠️ Warning ({{ number_format($projectedKwhPercent, 0) }}%)</span>
                                            @else
                                                <span class="text-emerald-500 font-bold">On Track ({{ number_format($projectedKwhPercent, 0) }}%)</span>
                                            @endif
                                        </div>
                                    </div>
                                @endif

                                @if($device->monthly_budget_cost)
                                    @php
                                        $costPercent = $device->monthly_budget_cost > 0 ? ($device->current_month_cost / $device->monthly_budget_cost) * 100 : 0;
                                        $projectedCostPercent = $device->monthly_budget_cost > 0 ? ($device->projected_cost / $device->monthly_budget_cost) * 100 : 0;
                                        $costExceeded = $projectedCostPercent > 100;
                                        $costWarning = $projectedCostPercent > 80 && $projectedCostPercent <= 100;
                                    @endphp
                                    <div>
                                        <div class="flex justify-between text-[10px] font-bold text-slate-650 mb-1">
                                            <span>Billing Cost (IDR)</span>
                                            <span>Rp {{ number_format($device->current_month_cost, 0, ',', '.') }} / Rp {{ number_format($device->monthly_budget_cost, 0, ',', '.') }}</span>
                                        </div>
                                        <div class="w-full bg-slate-100/80 rounded-full h-1.5 overflow-hidden">
                                            <div class="h-full rounded-full {{ $costExceeded ? 'bg-red-500' : ($costWarning ? 'bg-amber-500' : 'bg-emerald-500') }}" style="width: {{ min(100, $costPercent) }}%"></div>
                                        </div>
                                        <div class="flex justify-between text-[9px] mt-1 font-semibold">
                                            <span class="text-slate-400">Projection: Rp {{ number_format($device->projected_cost, 0, ',', '.') }}</span>
                                            @if($costExceeded)
                                                <span class="text-red-500 font-bold">⚠️ Over Limit (+{{ number_format($projectedCostPercent - 100, 0) }}%)</span>
                                            @elseif($costWarning)
                                                <span class="text-amber-500 font-bold">⚠️ Warning ({{ number_format($projectedCostPercent, 0) }}%)</span>
                                            @else
                                                <span class="text-emerald-500 font-bold">On Track ({{ number_format($projectedCostPercent, 0) }}%)</span>
                                            @endif
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @endif

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
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" aria-hidden="true" onclick="document.getElementById('add-device-modal').classList.add('hidden')"></div>

        <!-- Spacer to center modal -->
        <span class="hidden sm:inline-block sm:align-middle sm:min-h-screen" aria-hidden="true">&#8203;</span>

        <!-- Modal Panel -->
        <div class="inline-block align-middle bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full border border-slate-200/60">
            <form action="{{ route('devices.store') }}" method="POST">
                @csrf
                
                <!-- Modal Header -->
                <div class="px-6 py-5 border-b border-slate-100 flex items-start gap-3.5 bg-slate-50/50">
                    <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center border border-blue-100 flex-shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v6m3-3H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-900" id="modal-title">Register New Device</h3>
                        <p class="text-xs text-slate-500 mt-0.5 font-medium">Enter details below to provision a new monitoring node.</p>
                    </div>
                </div>

                <!-- Form Fields -->
                <div class="px-6 py-5 space-y-4">
                    <!-- Device Type -->
                    <div class="relative group">
                        <label for="dash_device_type" class="block text-xs font-semibold text-slate-600 mb-1.5 group-focus-within:text-blue-600 transition-colors">Device Type</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400 group-focus-within:text-blue-500 transition-colors">
                                <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                                </svg>
                            </div>
                            <select name="device_type" id="dash_device_type" required 
                                onchange="toggleDashGroupField(this.value)"
                                class="w-full pl-9 pr-10 py-2.5 bg-white border border-slate-250 rounded-xl text-slate-900 text-sm font-semibold focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 hover:border-slate-300 transition-all duration-200 appearance-none">
                                <option value="pzem">⚡ Energy Monitor (PZEM-004T)</option>
                                <option value="env_sensor">🌡️ Environment Sensor (DHT22)</option>
                                <option value="relay_controller">🔌 Relay Controller (4CH)</option>
                            </select>
                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-slate-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Device Name -->
                    <div class="relative group">
                        <label for="dash_name" class="block text-xs font-semibold text-slate-600 mb-1.5 group-focus-within:text-blue-600 transition-colors">Device Name</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400 group-focus-within:text-blue-500 transition-colors">
                                <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z" />
                                </svg>
                            </div>
                            <input type="text" name="name" id="dash_name" required placeholder="e.g. Compressor Pump A" 
                                class="w-full pl-9 pr-4 py-2.5 bg-white border border-slate-250 rounded-xl text-slate-900 text-sm font-medium placeholder-slate-405 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 hover:border-slate-300 transition-all duration-200">
                        </div>
                    </div>

                    <!-- Group Area (only for pzem) -->
                    <div class="relative group" id="dash_group_field">
                        <label for="dash_group_id" class="block text-xs font-semibold text-slate-600 mb-1.5 group-focus-within:text-blue-600 transition-colors">Group Area</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400 group-focus-within:text-blue-500 transition-colors">
                                <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
                                </svg>
                            </div>
                            <select name="group_id" id="dash_group_id" 
                                class="w-full pl-9 pr-10 py-2.5 bg-white border border-slate-250 rounded-xl text-slate-900 text-sm font-semibold focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 hover:border-slate-300 transition-all duration-200 appearance-none">
                                @foreach($groups as $g)
                                    <option value="{{ $g->id }}">{{ $g->name }}</option>
                                @endforeach
                            </select>
                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-slate-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Wifi SSID -->
                    <div class="relative group">
                        <label for="dash_wifi_ssid" class="block text-xs font-semibold text-slate-600 mb-1.5 group-focus-within:text-blue-600 transition-colors">Wifi SSID</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400 group-focus-within:text-blue-500 transition-colors">
                                <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071a9.9 9.9 0 0114.14 0M2.006 8.502a15 15 0 0121.988 0" />
                                </svg>
                            </div>
                            <input type="text" name="wifi_ssid" id="dash_wifi_ssid" required placeholder="SSID Wifi Name" 
                                class="w-full pl-9 pr-4 py-2.5 bg-white border border-slate-250 rounded-xl text-slate-900 text-sm font-medium placeholder-slate-405 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 hover:border-slate-300 transition-all duration-200">
                        </div>
                    </div>

                    <!-- Wifi Password -->
                    <div class="relative group">
                        <label for="dash_wifi_password" class="block text-xs font-semibold text-slate-600 mb-1.5 group-focus-within:text-blue-600 transition-colors">Wifi Password</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400 group-focus-within:text-blue-500 transition-colors">
                                <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                </svg>
                            </div>
                            <input type="password" name="wifi_password" id="dash_wifi_password" required placeholder="••••••••" 
                                class="w-full pl-9 pr-4 py-2.5 bg-white border border-slate-250 rounded-xl text-slate-900 text-sm font-medium placeholder-slate-405 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 hover:border-slate-300 transition-all duration-200">
                        </div>
                    </div>
                </div>

                <!-- Footer Actions -->
                <div class="bg-slate-50/60 px-6 py-4 border-t border-slate-100 flex items-center justify-end gap-2.5">
                    <button type="button" onclick="document.getElementById('add-device-modal').classList.add('hidden')" 
                        class="px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-xs font-bold text-slate-600 hover:bg-slate-50 hover:text-slate-800 transition-colors duration-200">
                        Cancel
                    </button>
                    <button type="submit" 
                        class="px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-xs font-bold text-white shadow-md shadow-blue-500/10 transition-colors duration-200">
                        Register & Get Code
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
function toggleDashGroupField(type) {
    const groupField = document.getElementById('dash_group_field');
    const groupSelect = document.getElementById('dash_group_id');
    if (type === 'pzem') {
        groupField.style.display = '';
        groupSelect.required = true;
    } else {
        groupField.style.display = 'none';
        groupSelect.required = false;
    }
}
</script>
@endif

<!-- Real-time WebSocket Listeners and Timeout Checks -->
<script>
    const plnTariff = {{ $plnTariff }};
    const vMin = {{ $vMin }};
    const vMax = {{ $vMax }};
    const pMax = {{ $pMax }};

    const deviceToGroupMap = {!! json_encode($groups->flatMap(function($g) {
        return $g->devices->pluck('device_id')->mapWithKeys(fn($id) => [$id => $g->id]);
    })) !!};

    const devicePowerRegistry = {!! json_encode($groups->flatMap->devices->pluck('device_id')->mapWithKeys(fn($id) => [$id => (float)Cache::get("power:{$id}", 0)])) !!};

    const groupDevicesMap = {!! json_encode($groups->mapWithKeys(fn($g) => [$g->id => $g->devices->pluck('device_id')->all()])) !!};

    function updateElectricalFlowUI() {
        let totalSystemPower = 0;
        
        for (const groupId in groupDevicesMap) {
            const deviceIds = groupDevicesMap[groupId];
            let groupPowerSum = 0;
            
            deviceIds.forEach(id => {
                groupPowerSum += (devicePowerRegistry[id] || 0);
            });
            
            totalSystemPower += groupPowerSum;
            
            // Update power label
            const powerText = document.getElementById('flow-group-power-' + groupId);
            const statusText = document.getElementById('flow-group-status-' + groupId);
            const flowLine = document.getElementById('flow-line-' + groupId);
            const nodeCircle = document.getElementById('node-circle-' + groupId);
            const nodePulseRing = document.querySelector('#flow-node-' + groupId + ' .grid-pulse');
            
            if (powerText) {
                powerText.textContent = groupPowerSum.toFixed(1) + ' W';
            }
            
            if (groupPowerSum > 0) {
                if (statusText) {
                    statusText.textContent = 'Active';
                    statusText.setAttribute('fill', '#10b981'); // emerald-500
                }
                if (flowLine) {
                    flowLine.setAttribute('stroke', '#10b981');
                    flowLine.style.animationPlayState = 'running';
                    // Speed up flow animation based on consumption!
                    // 2200W corresponds to very fast (0.35s), 0W corresponds to slow (2s)
                    const speed = Math.max(0.35, 2 - (groupPowerSum / 2200) * 1.65);
                    flowLine.style.animationDuration = speed + 's';
                }
                if (nodeCircle) {
                    nodeCircle.setAttribute('stroke', '#10b981');
                    nodeCircle.setAttribute('fill', 'rgba(16, 185, 129, 0.08)');
                }
                if (nodePulseRing) {
                    nodePulseRing.style.display = 'block';
                }
            } else {
                if (statusText) {
                    statusText.textContent = 'Inactive';
                    statusText.setAttribute('fill', '#94a3b8'); // slate-400
                }
                if (flowLine) {
                    flowLine.setAttribute('stroke', '#cbd5e1'); // slate-300
                    flowLine.style.animationPlayState = 'paused';
                }
                if (nodeCircle) {
                    nodeCircle.setAttribute('stroke', '#cbd5e1');
                    nodeCircle.setAttribute('fill', 'rgba(148, 163, 184, 0.05)');
                }
                if (nodePulseRing) {
                    nodePulseRing.style.display = 'none';
                }
            }
        }
        
        // Update main MDP power text
        const mdpPowerText = document.getElementById('total-grid-power');
        if (mdpPowerText) {
            mdpPowerText.textContent = totalSystemPower.toFixed(1) + ' W';
        }
        
        // Update PLN line
        const plnLineActive = document.getElementById('flow-line-pln-mdp-active');
        if (plnLineActive) {
            if (totalSystemPower > 0) {
                plnLineActive.style.display = 'block';
                const plnSpeed = Math.max(0.35, 2 - (totalSystemPower / 4400) * 1.65);
                plnLineActive.style.animationDuration = plnSpeed + 's';
            } else {
                plnLineActive.style.display = 'none';
            }
        }
    }

    const energyRegistry = {!! json_encode($groups->flatMap->devices->pluck('device_id')->mapWithKeys(fn($id) => [$id => (float)Cache::get("daily_energy:{$id}", 0)])) !!};
    const costRegistry = {!! json_encode($groups->flatMap->devices->pluck('device_id')->mapWithKeys(fn($id) => [$id => (float)Cache::get("daily_cost:{$id}", Cache::get("daily_energy:{$id}", 0) * $plnTariff)])) !!};

    // Warnings state management
    const activeWarningsState = {!! json_encode(collect($activeWarnings)->mapWithKeys(fn($w) => [
        ($w['device_id'] . '_' . $w['type']) => $w
    ])->all()) !!};

    function renderWarningsUI() {
        const container = document.getElementById('active-alerts-container');
        const list = document.getElementById('alerts-list');
        const badge = document.getElementById('alerts-count-badge');
        
        if (!container || !list || !badge) return;

        const warningKeys = Object.keys(activeWarningsState);
        const count = warningKeys.length;

        if (count === 0) {
            container.classList.add('hidden');
            return;
        }

        container.classList.remove('hidden');
        badge.innerText = count + ' Warnings';

        // Clear and rebuild list
        list.innerHTML = '';
        warningKeys.forEach(key => {
            const warning = activeWarningsState[key];
            const pingBg = warning.severity === 'danger' ? 'bg-rose-500 animate-ping' : 'bg-amber-500';
            const html = `
                <div class="flex items-start justify-between p-3 rounded-2xl bg-white/70 border border-rose-100 text-slate-800 text-xs font-semibold shadow-sm gap-4" id="alert-item-${warning.device_id}-${warning.type}">
                    <div class="flex items-center gap-2">
                        <span class="flex-shrink-0 w-2.5 h-2.5 rounded-full ${pingBg}"></span>
                        <span><strong>${warning.device_name}</strong>: ${warning.message}</span>
                    </div>
                </div>
            `;
            list.insertAdjacentHTML('beforeend', html);
        });
    }

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
        const cardName = cardElem ? cardElem.querySelector('h3').innerText : deviceId;
        
        // Apply flash animation to indicate live WebSocket push in light theme
        if(cardElem) {
            cardElem.classList.remove('flash-active');
            void cardElem.offsetWidth; // Trigger reflow
            cardElem.classList.add('flash-active');
            cardElem.classList.add('device-active');
            cardElem.setAttribute('data-last-seen', Math.floor(Date.now() / 1000));

            // Show and update WiFi RSSI if available
            if(data.rssi !== undefined) {
                const wifiRssiElem = document.getElementById('wifi-rssi-' + deviceId);
                const wifiContainer = document.getElementById('wifi-container-' + deviceId);
                if(wifiRssiElem) wifiRssiElem.innerText = parseInt(data.rssi) + ' dBm';
                if(wifiContainer) wifiContainer.classList.remove('hidden');
            }
        }

        // Live connection means offline alert is removed
        if (activeWarningsState[deviceId + '_offline']) {
            delete activeWarningsState[deviceId + '_offline'];
            renderWarningsUI();
        }

        // Update PZEM values
        if(data.voltage !== undefined) {
            const vElem = document.getElementById('voltage-' + deviceId);
            if(vElem) vElem.innerText = parseFloat(data.voltage).toFixed(1);

            // Voltage threshold check
            const vVal = parseFloat(data.voltage);
            if(vVal > 0 && (vVal < vMin || vVal > vMax)) {
                activeWarningsState[deviceId + '_voltage'] = {
                    device_id: deviceId,
                    device_name: cardName,
                    type: 'voltage',
                    message: `Voltase tidak stabil: ${vVal} V (Batas aman: ${vMin} - ${vMax} V)`,
                    severity: 'warning'
                };
            } else {
                delete activeWarningsState[deviceId + '_voltage'];
            }
            renderWarningsUI();
        }
        if(data.current !== undefined) {
            const cElem = document.getElementById('current-' + deviceId);
            if(cElem) cElem.innerText = parseFloat(data.current).toFixed(2);
        }
        if(data.power !== undefined) {
            const pElem = document.getElementById('power-' + deviceId);
            if(pElem) pElem.innerText = parseFloat(data.power).toFixed(1);

            devicePowerRegistry[deviceId] = parseFloat(data.power);
            updateElectricalFlowUI();

            // Power threshold check
            const pVal = parseFloat(data.power);
            if(pVal > pMax) {
                activeWarningsState[deviceId + '_power'] = {
                    device_id: deviceId,
                    device_name: cardName,
                    type: 'power',
                    message: `Konsumsi daya melebihi batas beban maksimum: ${pVal} W (Batas aman: maks ${pMax} W)`,
                    severity: 'warning'
                };
            } else {
                delete activeWarningsState[deviceId + '_power'];
            }
            renderWarningsUI();
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

    const initDashboardEcho = () => {
        // Initial warnings render
        renderWarningsUI();
        updateElectricalFlowUI();

        if (window.Echo) {
            console.log('Echo initialized, subscribing to global channel...');
            window.Echo.channel('telemetry')
                .listen('TelemetryUpdated', (e) => {
                    console.log('Received telemetry for ' + e.deviceId + ':', e.data);
                    updateDeviceUI(e.deviceId, e.data);
                });
        } else {
            console.error('Laravel Echo is not available.');
        }

        // Periodically check if devices are active/inactive (offline status check)
        setInterval(() => {
            const currentTimestamp = Math.floor(Date.now() / 1000);
            let stateChanged = false;

            document.querySelectorAll('.device-card').forEach(cardElem => {
                const deviceId = cardElem.getAttribute('data-device-id');
                const lastSeen = parseInt(cardElem.getAttribute('data-last-seen')) || 0;
                const diff = currentTimestamp - lastSeen;
                
                // 1. Check card status state (Active/Inactive)
                if (lastSeen === 0 || diff >= 15) {
                    cardElem.classList.remove('device-active');
                    const badgeElem = document.getElementById('status-' + deviceId);
                    const dotElem = document.getElementById('status-dot-' + deviceId);
                    const textElem = document.getElementById('status-text-' + deviceId);
                    const wifiContainer = document.getElementById('wifi-container-' + deviceId);

                    if (badgeElem && dotElem && textElem && textElem.innerText === "Active") {
                        badgeElem.className = "inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-red-50 text-red-700 border border-red-200";
                        dotElem.className = "w-1.5 h-1.5 mr-1.5 rounded-full bg-red-500";
                        textElem.innerText = "Inactive";
                    }
                    if (wifiContainer) {
                        wifiContainer.classList.add('hidden');
                    }

                    if (devicePowerRegistry[deviceId] !== 0) {
                        devicePowerRegistry[deviceId] = 0;
                        updateElectricalFlowUI();
                    }
                }

                // 2. Check 5 minutes offline threshold for Alerts Banner
                if (lastSeen === 0 || diff >= 300) {
                    const cardName = cardElem.querySelector('h3').innerText;
                    const key = deviceId + '_offline';
                    const message = `Perangkat offline. Terakhir terlihat: ${lastSeen > 0 ? Math.floor(diff / 60) + ' menit yang lalu' : 'Belum pernah online'}`;
                    
                    if (!activeWarningsState[key] || activeWarningsState[key].message !== message) {
                        activeWarningsState[key] = {
                            device_id: deviceId,
                            device_name: cardName,
                            type: 'offline',
                            message: message,
                            severity: 'danger'
                        };
                        // Clear voltage/power alerts since device is offline
                        delete activeWarningsState[deviceId + '_voltage'];
                        delete activeWarningsState[deviceId + '_power'];
                        stateChanged = true;
                    }
                } else {
                    if (activeWarningsState[deviceId + '_offline']) {
                        delete activeWarningsState[deviceId + '_offline'];
                        stateChanged = true;
                    }
                }
            });

            if (stateChanged) {
                renderWarningsUI();
            }
        }, 5000); // Check every 5 seconds
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initDashboardEcho);
    } else {
        initDashboardEcho();
    }
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
                datasets: [
                    {
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
                    },
                    {
                        label: 'Last Week Usage',
                        data: [],
                        borderColor: '#94a3b8',
                        backgroundColor: 'rgba(148, 163, 184, 0.02)',
                        borderWidth: 3,
                        borderDash: [5, 5],
                        pointBackgroundColor: '#ffffff',
                        pointBorderColor: '#94a3b8',
                        pointBorderWidth: 2,
                        pointRadius: 4,
                        pointHoverRadius: 6,
                        fill: true,
                        tension: 0.35,
                        hidden: true
                    }
                ]
            },
            options: {
                animation: false, // disable animations to make it extremely lightweight for lower-end CPUs
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false,
                        labels: {
                            font: { family: "'Inter', sans-serif", size: 11, weight: 'bold' },
                            color: '#475569'
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(15, 23, 42, 0.95)',
                        borderColor: 'rgba(0, 0, 0, 0.05)',
                        borderWidth: 1,
                        titleFont: { size: 12, family: "'Inter', sans-serif", weight: 'bold' },
                        bodyFont: { size: 12, family: "'Inter', sans-serif" },
                        padding: 12,
                        cornerRadius: 12,
                        displayColors: true,
                        callbacks: {
                            label: function(context) {
                                const val = context.parsed.y;
                                const label = context.dataset.label || '';
                                if (currentMetric === 'energy') {
                                    return label + ': ' + val.toLocaleString('id-ID', {minimumFractionDigits:2, maximumFractionDigits:2}) + ' kWh';
                                } else {
                                    return label + ': Rp ' + val.toLocaleString('id-ID', {minimumFractionDigits:0, maximumFractionDigits:0});
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
        ['daily', 'weekly', 'monthly', 'yearly'].forEach(p => {
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
            if (titleEl) {
                titleEl.innerText = currentPeriod === 'weekly' ? "Weekly Energy Comparison" : "Energy Consumption Trend";
            }
            borderColor = '#3b82f6';
            gradientColor = 'rgba(59, 130, 246, 0.15)';
        } else {
            if (titleEl) {
                titleEl.innerText = currentPeriod === 'weekly' ? "Weekly Cost Comparison" : "Estimated Cost Analytics";
            }
            borderColor = '#10b981'; // emerald
            gradientColor = 'rgba(16, 185, 129, 0.15)';
        }

        const gradient = ctx.createLinearGradient(0, 0, 0, 300);
        gradient.addColorStop(0, gradientColor);
        gradient.addColorStop(1, 'rgba(255, 255, 255, 0)');

        if (energyChartInstance) {
            if (currentPeriod === 'weekly') {
                const comparison = chartDataRaw.comparison || { labels: [], this_week: [], last_week: [] };
                const labels = comparison.labels;
                const thisWeekData = comparison.this_week.map(val => currentMetric === 'energy' ? val : (val * plnTariff));
                const lastWeekData = comparison.last_week.map(val => currentMetric === 'energy' ? val : (val * plnTariff));

                energyChartInstance.data.labels = labels;
                
                // This Week dataset
                energyChartInstance.data.datasets[0].data = thisWeekData;
                energyChartInstance.data.datasets[0].label = currentMetric === 'energy' ? 'This Week (kWh)' : 'This Week (Rp)';
                energyChartInstance.data.datasets[0].borderColor = borderColor;
                energyChartInstance.data.datasets[0].backgroundColor = gradient;
                energyChartInstance.data.datasets[0].pointBorderColor = borderColor;
                
                // Last Week dataset
                const lastWeekGradient = ctx.createLinearGradient(0, 0, 0, 300);
                lastWeekGradient.addColorStop(0, 'rgba(148, 163, 184, 0.1)');
                lastWeekGradient.addColorStop(1, 'rgba(255, 255, 255, 0)');
                
                energyChartInstance.data.datasets[1].data = lastWeekData;
                energyChartInstance.data.datasets[1].label = currentMetric === 'energy' ? 'Last Week (kWh)' : 'Last Week (Rp)';
                energyChartInstance.data.datasets[1].hidden = false;
                energyChartInstance.data.datasets[1].backgroundColor = lastWeekGradient;
                
                energyChartInstance.options.plugins.legend.display = true;
            } else {
                const dataset = chartDataRaw[currentPeriod] || [];
                const labels = dataset.map(item => item.label);
                const data = dataset.map(item => {
                    const val = parseFloat(item.total);
                    return currentMetric === 'energy' ? val : (val * plnTariff);
                });

                energyChartInstance.data.labels = labels;
                energyChartInstance.data.datasets[0].data = data;
                energyChartInstance.data.datasets[0].label = currentMetric === 'energy' ? 'Energy (kWh)' : 'Cost (Rp)';
                energyChartInstance.data.datasets[0].borderColor = borderColor;
                energyChartInstance.data.datasets[0].backgroundColor = gradient;
                energyChartInstance.data.datasets[0].pointBorderColor = borderColor;
                
                // Hide Last Week dataset
                energyChartInstance.data.datasets[1].data = [];
                energyChartInstance.data.datasets[1].hidden = true;
                
                energyChartInstance.options.plugins.legend.display = false;
            }
            energyChartInstance.update();
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        initChart();
    });
</script>

<!-- Add Group Modal -->
@if(auth()->user()->role === 'admin')
<div id="add-group-modal-dashboard" class="hidden fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title-group" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <!-- Background backdrop -->
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" aria-hidden="true" onclick="document.getElementById('add-group-modal-dashboard').classList.add('hidden')"></div>

        <!-- Spacer to center modal -->
        <span class="hidden sm:inline-block sm:align-middle sm:min-h-screen" aria-hidden="true">&#8203;</span>

        <!-- Modal Panel -->
        <div class="inline-block align-middle bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full border border-slate-200/60">
            <form action="{{ route('groups.store') }}" method="POST">
                @csrf
                
                <!-- Modal Header -->
                <div class="px-6 py-5 border-b border-slate-100 flex items-start gap-3.5 bg-slate-50/50">
                    <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center border border-indigo-100 flex-shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-900" id="modal-title-group">Add New Operational Group</h3>
                        <p class="text-xs text-slate-500 mt-0.5 font-medium">Create a new organizational area for IoT monitoring nodes.</p>
                    </div>
                </div>

                <!-- Form Fields -->
                <div class="px-6 py-5 space-y-4">
                    <!-- Group Name -->
                    <div class="relative group">
                        <label for="group_name_dash" class="block text-xs font-semibold text-slate-600 mb-1.5 group-focus-within:text-indigo-600 transition-colors">Group Name</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400 group-focus-within:text-indigo-500 transition-colors">
                                <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                </svg>
                            </div>
                            <input type="text" name="name" id="group_name_dash" required placeholder="e.g. Server Room B" 
                                class="w-full pl-9 pr-4 py-2.5 bg-white border border-slate-250 rounded-xl text-slate-900 text-sm font-medium placeholder-slate-405 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 hover:border-slate-300 transition-all duration-200">
                        </div>
                    </div>

                    <!-- Floor Number -->
                    <div class="relative group">
                        <label for="group_floor_dash" class="block text-xs font-semibold text-slate-600 mb-1.5 group-focus-within:text-indigo-600 transition-colors">Floor / Lantai</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 group-focus-within:text-indigo-500 transition-colors">
                                <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5" />
                                </svg>
                            </div>
                            <select name="floor" id="group_floor_dash" required 
                                class="w-full pl-10 pr-4 py-2.5 bg-white border border-slate-250 rounded-xl text-slate-900 text-sm font-medium focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 hover:border-slate-300 transition-all duration-200">
                                @for($i = 1; $i <= 10; $i++)
                                    <option value="{{ $i }}">Lantai {{ $i }}</option>
                                @endfor
                            </select>
                        </div>
                    </div>

                    <!-- Description -->
                    <div class="relative group">
                        <label for="group_description_dash" class="block text-xs font-semibold text-slate-600 mb-1.5 group-focus-within:text-indigo-600 transition-colors">Description (Optional)</label>
                        <div class="relative">
                            <textarea name="description" id="group_description_dash" rows="3" placeholder="Describe the operational area..." 
                                class="w-full px-4 py-2.5 bg-white border border-slate-250 rounded-xl text-slate-900 text-sm font-medium placeholder-slate-405 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 hover:border-slate-300 transition-all duration-200"></textarea>
                        </div>
                    </div>
                </div>

                <!-- Footer Actions -->
                <div class="bg-slate-50/60 px-6 py-4 border-t border-slate-100 flex items-center justify-end gap-2.5">
                    <button type="button" onclick="document.getElementById('add-group-modal-dashboard').classList.add('hidden')" 
                        class="px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-xs font-bold text-slate-600 hover:bg-slate-50 hover:text-slate-800 transition-colors duration-200">
                        Cancel
                    </button>
                    <button type="submit" 
                        class="px-5 py-2.5 rounded-xl bg-indigo-650 hover:bg-indigo-600 active:bg-indigo-750 text-xs font-bold text-white shadow-md shadow-indigo-500/10 transition-colors duration-200">
                        Create Group
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endsection
