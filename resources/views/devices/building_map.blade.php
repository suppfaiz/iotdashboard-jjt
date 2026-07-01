@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between border-b border-slate-200 pb-5 mb-8 gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-800 tracking-tight flex items-center gap-2">
                🏢 3D BUILDING SENSOR MAP
            </h1>
            <p class="text-xs text-slate-500 font-medium tracking-wide mt-1 uppercase">Holographic 3D Building Floor & Real-time Sensor Locations</p>
        </div>
        
        <!-- Controls -->
        <div class="flex items-center gap-3">
            <button onclick="resetBuildingView()" class="px-4 py-2 bg-white hover:bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-600 transition-colors shadow-sm flex items-center gap-1.5">
                🔄 Reset View
            </button>
            <a href="{{ route('dashboard') }}" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-white rounded-xl text-xs font-bold transition-colors shadow-sm">
                ← Dashboard Home
            </a>
        </div>
    </div>

    <!-- Main Content Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
        
        <!-- Left Column: 3D Scene Viewport (Col 7) -->
        <div class="lg:col-span-7 bg-white/70 backdrop-blur-md border border-slate-200/80 rounded-3xl p-6 shadow-sm flex flex-col items-center justify-center relative overflow-hidden" style="min-height: 620px;">
            <!-- Subtle backdrop info -->
            <div class="absolute top-6 left-6 z-20 text-slate-400 font-bold text-[10px] uppercase tracking-widest pointer-events-none">
                Interactive 3D Hologram Viewport
            </div>

            <div class="absolute bottom-6 left-6 z-20 text-slate-400/90 text-[10px] font-bold tracking-wider uppercase pointer-events-none flex flex-col gap-2">
                <div class="flex items-center gap-2"><span class="w-2.5 h-2.5 rounded-full bg-blue-500 shadow-sm shadow-blue-500/30"></span> Click a floor to explode & inspect</div>
                <div class="flex items-center gap-2"><span class="w-2.5 h-2.5 rounded-full bg-red-500 animate-ping"></span> Flashing red indicates voltage/load anomaly</div>
            </div>

            <!-- The 3D Scene Container -->
            <div class="scene">
                @php
                    $maxFloor = max(3, $floors->keys()->max() ?? 1);
                @endphp
                <div id="building-model" class="building auto-rotate">
                    <!-- Floor Slabs stacked from bottom (1) to top (max) -->
                    @for($f = 1; $f <= $maxFloor; $f++)
                        @php
                            $hasGroups = $floors->has($f);
                            $floorGroups = $hasGroups ? $floors->get($f) : collect();
                            $onlineCount = 0;
                            $offlineCount = 0;
                            $hasAnomalies = false;

                            foreach($floorGroups as $group) {
                                foreach($group->devices as $dev) {
                                    if ($dev->is_online) {
                                        $onlineCount++;
                                        if ($dev->voltage < $alertConfig['voltage_min'] || $dev->voltage > $alertConfig['voltage_max'] || $dev->power > $alertConfig['power_max']) {
                                            $hasAnomalies = true;
                                        }
                                    } else {
                                        $offlineCount++;
                                    }
                                }
                            }
                        @endphp

                        <!-- Isometric Floor Plate -->
                        <div class="floor-slab" 
                             id="slab-{{ $f }}" 
                             data-floor-index="{{ $f }}"
                             onclick="clickFloor({{ $f }})"
                             style="--floor-index: {{ $f }}; transform: translateZ({{ ($f - 1) * 75 }}px);">
                            
                            <!-- 3D Walls for the house/building structure -->
                            <div class="wall wall-back"></div>
                            <div class="wall wall-front"></div>
                            <div class="wall wall-left"></div>
                            <div class="wall wall-right"></div>

                            <!-- Internal 3D details styling -->
                            <div class="flex justify-between items-center w-full z-10 relative">
                                <div class="flex flex-col">
                                    <span class="text-xs font-black text-slate-800 tracking-wide">LANTAI {{ $f }}</span>
                                    <span class="text-[8px] text-slate-400 font-bold uppercase tracking-widest mt-0.5">
                                        {{ $floorGroups->count() }} Zones
                                    </span>
                                </div>

                                <!-- Flashing indicator badge -->
                                <div class="flex items-center gap-1.5">
                                    @if($hasAnomalies)
                                        <span id="slab-alert-{{ $f }}" class="w-3.5 h-3.5 rounded-full bg-red-500 animate-ping flex items-center justify-center"></span>
                                        <span class="text-[8px] font-black text-red-600 uppercase tracking-widest">ALERT</span>
                                    @elseif($onlineCount > 0)
                                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 shadow-sm shadow-emerald-500/20"></span>
                                    @else
                                        <span class="w-2.5 h-2.5 rounded-full bg-slate-300"></span>
                                    @endif
                                </div>
                            </div>

                            <!-- Simplified Clean 2-Letter Abbreviation Zones (Blue-print Grid style) -->
                            @if($hasGroups)
                                <div class="mt-4 flex flex-wrap items-center gap-2 pointer-events-none z-10 relative">
                                    @foreach($floorGroups as $group)
                                        @php
                                            $anyOnline = $group->devices->where('is_online', true)->count() > 0;
                                            
                                            // Extract 2-letter abbreviation
                                            $words = preg_split("/[\s\-_&]+/", $group->name);
                                            if (count($words) >= 2) {
                                                $abbr = strtoupper(substr($words[0], 0, 1) . substr($words[1], 0, 1));
                                            } else {
                                                $abbr = strtoupper(substr($group->name, 0, 2));
                                            }
                                        @endphp
                                        <div class="w-7 h-7 rounded-lg flex items-center justify-center text-[9px] font-black tracking-tighter {{ $anyOnline ? 'bg-emerald-500/10 text-emerald-600 border border-emerald-250/70 shadow-sm' : 'bg-slate-100/90 text-slate-450 border border-slate-200/60' }}" title="{{ $group->name }}">
                                            {{ $abbr }}
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="mt-4 text-[9px] text-slate-400 italic font-semibold pointer-events-none text-center py-2.5 bg-slate-50/50 rounded-xl border border-dashed border-slate-200/40 w-full z-10 relative">
                                    Empty Floor
                                </div>
                            @endif
                        </div>
                    @endfor

                    <!-- Pitched Roof on top floor slab -->
                    <div id="roof-model" class="roof" style="transform: translateZ({{ $maxFloor * 75 }}px);">
                        <div class="roof-panel roof-front"></div>
                        <div class="roof-panel roof-back"></div>
                        <div class="roof-gable roof-gable-left"></div>
                        <div class="roof-gable roof-gable-right"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column: Inspector Details Panel (Col 5) -->
        <div class="lg:col-span-5 flex flex-col gap-6">
            
            <!-- Instructions / Placeholder Panel -->
            <div id="inspector-placeholder" class="bg-white/70 backdrop-blur-md border border-slate-200/80 rounded-3xl p-8 shadow-sm flex flex-col items-center justify-center text-center transition-all duration-300" style="min-height: 620px;">
                <span class="text-5xl mb-4 block animate-bounce">🏢</span>
                <h3 class="text-lg font-black text-slate-800">Select a Building Floor</h3>
                <p class="text-xs text-slate-500 max-w-sm mt-2 leading-relaxed">
                    Click on any of the 3D floor slabs in the holographic building blueprint to inspect active groups, check sensor locations, and view real-time telemetry details.
                </p>
            </div>

            <!-- Active Floor Panel (Hidden by default) -->
            <div id="inspector-panel" class="hidden bg-white/70 backdrop-blur-md border border-slate-200/80 rounded-3xl p-6 shadow-sm flex flex-col justify-between transition-all duration-300" style="min-height: 620px;">
                <div class="flex-grow">
                    <!-- Floor Title -->
                    <div class="border-b border-slate-100 pb-4 mb-6 flex justify-between items-center">
                        <div>
                            <h2 id="inspect-title" class="text-xl font-black text-slate-800">Lantai 2 Details</h2>
                            <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest mt-0.5">Floor Operational Inspector</p>
                        </div>
                        <button onclick="resetBuildingView()" class="text-xs font-bold text-blue-600 hover:text-blue-700 transition-colors">
                            Close ×
                        </button>
                    </div>

                    <!-- Groups & Sensors List container -->
                    <div id="inspect-groups-container" class="space-y-6 max-h-[460px] overflow-y-auto pr-1">
                        <!-- Loaded dynamically via JS -->
                    </div>
                </div>

                <div class="border-t border-slate-100 pt-4 mt-6 flex justify-between items-center text-[10px] text-slate-400 font-bold uppercase tracking-widest flex-shrink-0">
                    <span>Target PLN Tariff: Rp {{ number_format($plnTariff, 2, ',', '.') }}/kWh</span>
                    <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span> Live Syncing</span>
                </div>
            </div>

        </div>

    </div>
</div>

<style>
    /* Scene Settings */
    .scene {
        width: 100%;
        height: 580px;
        perspective: 1600px;
        perspective-origin: 50% 25%;
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
        background: radial-gradient(circle, rgba(99, 102, 241, 0.05) 1.5px, transparent 1.5px);
        background-size: 24px 24px;
        border-radius: 24px;
        overflow: hidden;
    }
    
    /* 3D Isometric building box */
    .building {
        position: relative;
        width: 320px;
        height: 220px;
        transform-style: preserve-3d;
        transform: rotateX(60deg) rotateZ(-30deg) translateY(20px);
        transition: transform 1.2s cubic-bezier(0.16, 1, 0.3, 1);
    }

    /* Auto-spin animation for holographic presentation */
    .building.auto-rotate {
        animation: auto-spin 25s linear infinite;
    }

    @keyframes auto-spin {
        0% { transform: rotateX(60deg) rotateZ(0deg) translateY(20px); }
        100% { transform: rotateX(60deg) rotateZ(360deg) translateY(20px); }
    }

    /* Floating Floor slabs */
    .floor-slab {
        position: absolute;
        width: 100%;
        height: 100%;
        background: linear-gradient(135deg, rgba(255, 255, 255, 0.88) 0%, rgba(248, 250, 252, 0.78) 100%);
        backdrop-filter: blur(8px);
        border: 2px solid rgba(148, 163, 184, 0.4);
        box-shadow: 0 8px 25px -8px rgba(148, 163, 184, 0.15), inset 0 0 15px rgba(255, 255, 255, 0.7);
        border-radius: 12px;
        transition: all 0.8s cubic-bezier(0.16, 1, 0.3, 1);
        transform-style: preserve-3d;
        cursor: pointer;
        display: flex;
        flex-direction: column;
        padding: 16px;
        justify-content: flex-start;
    }

    .floor-slab:hover {
        background: rgba(255, 255, 255, 0.96);
        border-color: rgba(59, 130, 246, 0.6);
        box-shadow: 0 15px 30px -5px rgba(59, 130, 246, 0.15), inset 0 0 20px rgba(255, 255, 255, 0.9);
    }

    .floor-slab.active {
        background: rgba(255, 255, 255, 0.98);
        border-color: #2563eb;
        box-shadow: 0 20px 45px -5px rgba(37, 99, 235, 0.2), inset 0 0 20px rgba(255, 255, 255, 1);
    }

    /* Red flashing glowing alert style for floor slab */
    .floor-slab.alert-active {
        border-color: #ef4444 !important;
        box-shadow: 0 15px 30px -5px rgba(239, 68, 68, 0.2), inset 0 0 20px rgba(239, 68, 68, 0.05) !important;
    }

    /* --- 3D Walls for House Shape --- */
    .wall {
        position: absolute;
        background: rgba(59, 130, 246, 0.05);
        border: 1.5px solid rgba(148, 163, 184, 0.3);
        transition: transform 0.8s cubic-bezier(0.16, 1, 0.3, 1), background-color 0.5s ease;
        pointer-events: none;
    }

    .wall-back {
        width: 320px;
        height: 60px;
        left: 0;
        top: 0;
        transform-origin: top center;
        transform: rotateX(-90deg);
        border-bottom: none;
    }

    .wall-front {
        width: 320px;
        height: 60px;
        left: 0;
        bottom: 0;
        transform-origin: bottom center;
        transform: rotateX(90deg);
        border-top: none;
        /* Draw a small main entrance gate look */
        background: radial-gradient(circle at 50% 100%, rgba(59, 130, 246, 0.1) 20px, rgba(59, 130, 246, 0.03) 21px);
    }

    .wall-left {
        width: 220px;
        height: 60px;
        left: 0;
        top: 0;
        transform-origin: left top;
        transform: rotateY(90deg) rotateZ(-90deg);
        border-right: none;
    }

    .wall-right {
        width: 220px;
        height: 60px;
        right: 0;
        top: 0;
        transform-origin: right top;
        transform: rotateY(-90deg) rotateZ(90deg);
        border-left: none;
    }

    /* Wall folding animation when floor is clicked */
    .floor-slab.active .wall-back {
        transform: rotateX(-180deg);
        background: rgba(59, 130, 246, 0.01);
    }
    .floor-slab.active .wall-front {
        transform: rotateX(180deg);
        background: rgba(59, 130, 246, 0.01);
    }
    .floor-slab.active .wall-left {
        transform: rotateY(180deg) rotateZ(-90deg);
        background: rgba(59, 130, 246, 0.01);
    }
    .floor-slab.active .wall-right {
        transform: rotateY(-180deg) rotateZ(90deg);
        background: rgba(59, 130, 246, 0.01);
    }

    /* Red flashing anomaly walls */
    .floor-slab.alert-active .wall {
        border-color: rgba(239, 68, 68, 0.4) !important;
        background-color: rgba(239, 68, 68, 0.03) !important;
    }

    /* --- Pitched Roof on Top --- */
    .roof {
        position: absolute;
        width: 320px;
        height: 220px;
        transform-style: preserve-3d;
        transition: transform 0.8s cubic-bezier(0.16, 1, 0.3, 1), opacity 0.6s ease;
        pointer-events: none;
    }

    .roof-panel {
        position: absolute;
        width: 320px;
        height: 140px;
        background: linear-gradient(135deg, rgba(59, 130, 246, 0.15) 0%, rgba(37, 99, 235, 0.05) 100%);
        border: 2px solid rgba(148, 163, 184, 0.5);
        box-shadow: inset 0 0 15px rgba(255, 255, 255, 0.4);
    }

    .roof-front {
        left: 0;
        top: 0;
        transform-origin: bottom center;
        transform: translate3d(0, -30px, 60px) rotateX(38deg);
    }

    .roof-back {
        left: 0;
        bottom: 0;
        transform-origin: top center;
        transform: translate3d(0, 30px, 60px) rotateX(-38deg);
    }

    /* Gable walls for roof side coverage */
    .roof-gable {
        position: absolute;
        width: 220px;
        height: 86px;
        background: rgba(59, 130, 246, 0.08);
        border: 1.5px solid rgba(148, 163, 184, 0.45);
        clip-path: polygon(50% 0%, 0% 100%, 100% 100%);
    }

    .roof-gable-left {
        left: 0;
        top: 0;
        transform-origin: left top;
        transform: rotateY(90deg) rotateZ(-90deg) translate3d(0, 0, 60px);
    }

    .roof-gable-right {
        right: 0;
        top: 0;
        transform-origin: right top;
        transform: rotateY(-90deg) rotateZ(90deg) translate3d(0, 0, 60px);
    }
</style>

<script>
    // Building structure passed from Laravel PHP
    const floorsData = @json($floors);
    const alertConfig = @json($alertConfig);
    const plnTariff = {{ $plnTariff }};
    const maxFloor = {{ $maxFloor }};
    
    let activeFloor = null;

    // Explode/Adjust building slabs view
    function clickFloor(floorNum) {
        activeFloor = floorNum;

        // Toggle building exploded class
        const building = document.getElementById('building-model');
        building.classList.remove('auto-rotate'); // Pause auto-rotation on click

        // Adjust rotations based on select
        building.style.transform = `rotateX(55deg) rotateZ(-20deg) translateY(50px)`;

        const slabs = document.querySelectorAll('.floor-slab');
        slabs.forEach(slab => {
            const idx = parseInt(slab.getAttribute('data-floor-index'));
            slab.classList.remove('active');

            let spacing = 95;
            let lift = 0;

            if (idx > floorNum) {
                lift = 90; // lift upper floors high
            } else if (idx === floorNum) {
                lift = 25;  // hover select slab
                slab.classList.add('active');
            } else {
                lift = -30; // slide lower floors down
            }

            // Using (idx - 1) shifts Floor 1 to baseline Z=0, preventing top floor cut-off
            slab.style.transform = `translateZ(${(idx - 1) * spacing + lift}px)`;
        });

        // Explode roof model upward and make it semi-transparent
        const roof = document.getElementById('roof-model');
        if (roof) {
            roof.style.transform = `translateZ(${maxFloor * 95 + 90}px)`;
            roof.style.opacity = '0.3';
        }

        // Show Inspector Details Panel
        showInspector(floorNum);
    }

    // Reset layout view back to compact stacked setting
    function resetBuildingView() {
        activeFloor = null;
        
        const building = document.getElementById('building-model');
        building.style.transform = `rotateX(60deg) rotateZ(-30deg) translateY(20px)`;
        building.classList.add('auto-rotate'); // Resume auto-rotation
        
        const slabs = document.querySelectorAll('.floor-slab');
        slabs.forEach(slab => {
            const idx = parseInt(slab.getAttribute('data-floor-index'));
            slab.classList.remove('active');
            slab.style.transform = `translateZ(${(idx - 1) * 75}px)`;
        });

        // Reset roof
        const roof = document.getElementById('roof-model');
        if (roof) {
            roof.style.transform = `translateZ(${maxFloor * 75}px)`;
            roof.style.opacity = '1';
        }

        // Hide Inspector
        document.getElementById('inspector-panel').classList.add('hidden');
        document.getElementById('inspector-placeholder').classList.remove('hidden');
    }

    // Load and build Inspector Sidebar details
    function showInspector(floorNum) {
        document.getElementById('inspector-placeholder').classList.add('hidden');
        
        const panel = document.getElementById('inspector-panel');
        panel.classList.remove('hidden');

        document.getElementById('inspect-title').textContent = `Lantai ${floorNum} Details`;
        
        const container = document.getElementById('inspect-groups-container');
        container.innerHTML = '';

        // Get groups for this floor
        const floorGroups = floorsData[floorNum] || [];

        if (floorGroups.length === 0) {
            container.innerHTML = `
                <div class="text-center py-12 text-slate-400 font-medium text-xs">
                    No active monitoring groups registered on this floor.
                </div>
            `;
            return;
        }

        // Loop and render groups
        floorGroups.forEach(group => {
            const groupEl = document.createElement('div');
            groupEl.className = "bg-white border border-slate-200/80 rounded-2xl p-4 shadow-sm";
            
            let devicesHtml = '';
            
            if (group.devices.length === 0) {
                devicesHtml = `
                    <div class="text-[10px] text-slate-400 italic font-medium py-1.5 pl-2">
                        No telemetry sensors paired with this group yet.
                    </div>
                `;
            } else {
                group.devices.forEach(device => {
                    const isOnline = device.is_online;
                    const statusText = isOnline ? 'ONLINE' : 'OFFLINE';
                    const statusClass = isOnline ? 'text-emerald-600 bg-emerald-50 border-emerald-100' : 'text-slate-400 bg-slate-50 border-slate-100';
                    
                    // Check warning anomalies
                    let cardBorderClass = 'border-slate-100';
                    let alertWarningBadge = '';

                    if (isOnline) {
                        const hasVoltAlert = device.voltage < alertConfig.voltage_min || device.voltage > alertConfig.voltage_max;
                        const hasPowerAlert = device.power > alertConfig.power_max;
                        
                        if (hasVoltAlert || hasPowerAlert) {
                            cardBorderClass = 'border-red-300 bg-red-50/20';
                            alertWarningBadge = `
                                <span class="text-[8px] font-black text-red-600 bg-red-100 px-2 py-0.5 rounded border border-red-200 animate-pulse">
                                    ⚠️ ANOMALY
                                </span>
                            `;
                        }
                    }

                    devicesHtml += `
                        <div id="sidebar-card-${device.device_id}" class="p-3 border ${cardBorderClass} rounded-xl bg-slate-50/40 flex items-center justify-between text-xs transition-all">
                            <div>
                                <div class="flex items-center gap-1.5">
                                    <span class="font-extrabold text-slate-800">${device.name}</span>
                                    <span class="text-[9px] font-semibold text-slate-400">(${device.device_id})</span>
                                </div>
                                <div class="flex items-center gap-3 mt-1.5 text-slate-500 font-medium text-[10px] tracking-wide uppercase">
                                    <span id="side-v-${device.device_id}">V: <strong>${device.voltage.toFixed(1)}V</strong></span>
                                    <span id="side-a-${device.device_id}">A: <strong>${device.current.toFixed(3)}A</strong></span>
                                    <span id="side-w-${device.device_id}">W: <strong>${device.power.toFixed(1)}W</strong></span>
                                </div>
                            </div>
                            
                            <div class="flex flex-col items-end gap-1 flex-shrink-0">
                                <span id="side-status-${device.device_id}" class="text-[9px] font-extrabold border rounded-md px-1.5 py-0.5 ${statusClass}">
                                    ${statusText}
                                </span>
                                <div id="side-alert-badge-${device.device_id}">
                                    ${alertWarningBadge}
                                </div>
                            </div>
                        </div>
                    `;
                });
            }

            groupEl.innerHTML = `
                <div class="flex items-center justify-between border-b border-slate-100 pb-2.5 mb-3">
                    <div class="flex items-center gap-1.5">
                        <span class="text-sm font-extrabold text-slate-800">🏢 ${group.name}</span>
                    </div>
                    <span class="text-[9px] font-bold text-slate-400 bg-slate-100 border border-slate-200 px-2 py-0.5 rounded-full uppercase">
                        Area
                    </span>
                </div>
                <div class="space-y-2.5">
                    ${devicesHtml}
                </div>
            `;
            
            container.appendChild(groupEl);
        });
    }

    // Realtime Echo integration to update numbers dynamically on the Map and Sidebar
    document.addEventListener('DOMContentLoaded', () => {
        if (window.Echo) {
            window.Echo.channel('telemetry')
                .listen('TelemetryUpdated', (e) => {
                    const deviceId = e.deviceId;
                    const data = e.data; // { voltage, current, power, energy, cost }

                    // 1. Locate and update sidebar visual elements
                    const sideV = document.getElementById(`side-v-${deviceId}`);
                    const sideA = document.getElementById(`side-a-${deviceId}`);
                    const sideW = document.getElementById(`side-w-${deviceId}`);
                    const sideStatus = document.getElementById(`side-status-${deviceId}`);
                    const sideCard = document.getElementById(`sidebar-card-${deviceId}`);
                    const sideAlert = document.getElementById(`side-alert-badge-${deviceId}`);

                    // Sync online state
                    if (sideStatus) {
                        sideStatus.className = "text-[9px] font-extrabold border rounded-md px-1.5 py-0.5 text-emerald-600 bg-emerald-50 border-emerald-100";
                        sideStatus.textContent = 'ONLINE';
                    }

                    // Update values
                    if (sideV) sideV.innerHTML = `V: <strong>${parseFloat(data.voltage).toFixed(1)}V</strong>`;
                    if (sideA) sideA.innerHTML = `A: <strong>${parseFloat(data.current).toFixed(3)}A</strong>`;
                    if (sideW) sideW.innerHTML = `W: <strong>${parseFloat(data.power).toFixed(1)}W</strong>`;

                    // Anomaly checks
                    const hasVoltAlert = data.voltage < alertConfig.voltage_min || data.voltage > alertConfig.voltage_max;
                    const hasPowerAlert = data.power > alertConfig.power_max;

                    if (sideCard) {
                        if (hasVoltAlert || hasPowerAlert) {
                            sideCard.className = "p-3 border border-red-300 bg-red-50/20 rounded-xl flex items-center justify-between text-xs transition-all";
                            if (sideAlert) {
                                sideAlert.innerHTML = `
                                    <span class="text-[8px] font-black text-red-600 bg-red-100 px-2 py-0.5 rounded border border-red-200 animate-pulse">
                                        ⚠️ ANOMALY
                                    </span>
                                `;
                            }
                        } else {
                            sideCard.className = "p-3 border border-slate-100 rounded-xl bg-slate-50/40 flex items-center justify-between text-xs transition-all";
                            if (sideAlert) sideAlert.innerHTML = '';
                        }
                    }

                    // Update local javascript data store
                    updateLocalDataStore(deviceId, data);

                    // Re-calculate the floor slab anomaly glow
                    recheckFloorSlabStates();
                });
        }

        // Periodically verify device timeouts and offline states (15s timeout)
        setInterval(() => {
            const now = Math.floor(Date.now() / 1000);
            let stateChanged = false;

            Object.keys(floorsData).forEach(floorNum => {
                const groups = floorsData[floorNum];
                groups.forEach(group => {
                    group.devices.forEach(device => {
                        if (device.is_online && device.last_seen && (now - device.last_seen >= 15)) {
                            // Turn device offline
                            device.is_online = false;
                            stateChanged = true;

                            // Update active sidebar elements if displaying current floor
                            if (activeFloor == floorNum) {
                                const sideStatus = document.getElementById(`side-status-${device.device_id}`);
                                const sideCard = document.getElementById(`sidebar-card-${device.device_id}`);
                                const sideAlert = document.getElementById(`side-alert-badge-${device.device_id}`);
                                
                                if (sideStatus) {
                                    sideStatus.className = "text-[9px] font-extrabold border rounded-md px-1.5 py-0.5 text-slate-400 bg-slate-50 border-slate-100";
                                    sideStatus.textContent = 'OFFLINE';
                                }
                                if (sideCard) {
                                    sideCard.className = "p-3 border border-slate-100 rounded-xl bg-slate-50/40 flex items-center justify-between text-xs transition-all";
                                }
                                if (sideAlert) sideAlert.innerHTML = '';
                            }
                        }
                    });
                });
            });

            if (stateChanged) {
                recheckFloorSlabStates();
            }
        }, 2000);

        // Update local object states
        function updateLocalDataStore(deviceId, data) {
            Object.keys(floorsData).forEach(floorNum => {
                const groups = floorsData[floorNum];
                groups.forEach(group => {
                    group.devices.forEach(device => {
                        if (device.device_id == deviceId) {
                            device.voltage = parseFloat(data.voltage);
                            device.current = parseFloat(data.current);
                            device.power = parseFloat(data.power);
                            device.energy = parseFloat(data.energy);
                            device.is_online = true;
                            device.last_seen = Math.floor(Date.now() / 1000);
                        }
                    });
                });
            });
        }

        // Re-render slab anomalies glows red/green
        function recheckFloorSlabStates() {
            Object.keys(floorsData).forEach(floorNum => {
                const groups = floorsData[floorNum];
                let hasFloorAnomalies = false;
                let onlineCount = 0;

                groups.forEach(group => {
                    group.devices.forEach(device => {
                        if (device.is_online) {
                            onlineCount++;
                            const hasVoltAlert = device.voltage < alertConfig.voltage_min || device.voltage > alertConfig.voltage_max;
                            const hasPowerAlert = device.power > alertConfig.power_max;
                            if (hasVoltAlert || hasPowerAlert) {
                                hasFloorAnomalies = true;
                            }
                        }
                    });
                });

                const slab = document.getElementById(`slab-${floorNum}`);
                const alertBadge = document.getElementById(`slab-alert-${floorNum}`);

                if (slab) {
                    if (hasFloorAnomalies) {
                        slab.classList.add('alert-active');
                    } else {
                        slab.classList.remove('alert-active');
                    }
                }

                if (alertBadge) {
                    if (hasFloorAnomalies) {
                        alertBadge.className = "w-3.5 h-3.5 rounded-full bg-red-500 animate-ping flex items-center justify-center";
                    } else if (onlineCount > 0) {
                        alertBadge.className = "w-2.5 h-2.5 rounded-full bg-emerald-500 shadow-sm shadow-emerald-500/20";
                    } else {
                        alertBadge.className = "w-2.5 h-2.5 rounded-full bg-slate-300";
                    }
                }
            });
        }
    });
</script>
@endsection
