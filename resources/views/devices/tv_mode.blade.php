<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TV Monitoring Mode - Jamkrida IoT</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;600;800;900&family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Share+Tech+Mono&display=swap" rel="stylesheet">
    
    <!-- Scripts & Styles (Vite) -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: #f8fafc radial-gradient(circle, rgba(148, 163, 184, 0.15) 1.5px, transparent 1.5px) !important;
            background-size: 24px 24px !important;
            color: #1e293b; /* slate-800 */
            overflow-x: hidden;
            min-height: 100vh;
        }
        .digital-mono {
            font-family: 'Share Tech Mono', monospace;
        }
        .glowing-value {
            font-family: 'Orbitron', sans-serif;
        }
        .glow-card {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(226, 232, 240, 0.8);
            box-shadow: 0 10px 30px -10px rgba(148, 163, 184, 0.12);
        }
        .glow-card-active {
            border: 1px solid rgba(59, 130, 246, 0.3);
            box-shadow: 0 10px 30px -5px rgba(59, 130, 246, 0.08);
        }
        /* Custom scrollbar for TV view */
        ::-webkit-scrollbar {
            width: 8px;
        }
        ::-webkit-scrollbar-track {
            background: #f1f5f9;
        }
        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 4px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
    </style>
</head>
<body class="h-full flex flex-col p-6">

    <!-- Top Navigation Header -->
    <header class="flex flex-col sm:flex-row items-center justify-between gap-4 pb-6 border-b border-slate-200 flex-shrink-0">
        <div class="flex items-center gap-4">
            <a href="{{ route('dashboard') }}" class="p-2.5 rounded-xl bg-white hover:bg-slate-50 border border-slate-200 text-slate-500 hover:text-slate-900 transition-colors shadow-sm" title="Back to Dashboard">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
            </a>
            <div>
                <h1 class="text-xl sm:text-2xl font-extrabold tracking-wider text-slate-800 flex items-center gap-2">
                    <span class="text-blue-600 animate-pulse">⚡</span> JAMKRIDA MONITORING KIOSK
                </h1>
                <p class="text-[10px] sm:text-xs font-bold text-slate-400 uppercase tracking-widest mt-0.5">Real-time IoT Telemetry Display System</p>
            </div>
        </div>

        <!-- Realtime Digital Clock -->
        <div class="flex items-center gap-6">
            <div class="text-right">
                <div id="tv-clock" class="text-2xl sm:text-3xl font-black tracking-wider text-blue-600 glowing-value leading-none">00:00:00</div>
                <div id="tv-date" class="text-[10px] sm:text-xs font-bold tracking-widest text-slate-500 uppercase mt-1">Kamis, 01 Juli 2026</div>
            </div>
            
            <!-- Fullscreen Button -->
            <button onclick="toggleFullscreen()" class="px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-500 text-white font-bold text-xs tracking-wider transition-all shadow-md hover:shadow-blue-500/20 flex items-center gap-2 border border-blue-400/20">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4" />
                </svg>
                FULLSCREEN
            </button>
        </div>
    </header>

    <!-- Header Stats Cards Summary -->
    <section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 py-6 flex-shrink-0">
        <!-- Card 1: Total Power -->
        <div class="glow-card rounded-2xl p-5 flex items-center justify-between">
            <div>
                <p class="text-xs font-bold tracking-widest text-slate-400 uppercase mb-1">Total Active Load</p>
                <h3 id="stat-total-power" class="text-3xl font-black text-emerald-600 glowing-value">{{ number_format($totalPower, 1) }} <span class="text-lg text-slate-400">W</span></h3>
            </div>
            <div class="w-12 h-12 rounded-xl bg-emerald-50 flex items-center justify-center text-emerald-600 text-xl border border-emerald-100">⚡</div>
        </div>

        <!-- Card 2: Active Devices -->
        <div class="glow-card rounded-2xl p-5 flex items-center justify-between">
            <div>
                <p class="text-xs font-bold tracking-widest text-slate-400 uppercase mb-1">Active Devices</p>
                <h3 class="text-3xl font-black text-blue-600 glowing-value"><span id="stat-active-count">{{ $totalActiveCount }}</span><span class="text-lg text-slate-400 font-bold">/{{ $devices->count() }}</span></h3>
            </div>
            <div class="w-12 h-12 rounded-xl bg-blue-50 flex items-center justify-center text-blue-600 text-xl border border-blue-100">🖥️</div>
        </div>

        <!-- Card 3: Est Cost Today -->
        <div class="glow-card rounded-2xl p-5 flex items-center justify-between">
            <div>
                <p class="text-xs font-bold tracking-widest text-slate-400 uppercase mb-1">Estimated Cost Today</p>
                <h3 id="stat-total-cost" class="text-2xl font-black text-amber-600 leading-none mt-1">Rp {{ number_format($totalTodayCost, 0, ',', '.') }}</h3>
            </div>
            <div class="w-12 h-12 rounded-xl bg-amber-50 flex items-center justify-center text-amber-600 text-xl border border-amber-100">💰</div>
        </div>

        <!-- Card 4: System Safety Status -->
        <div class="glow-card rounded-2xl p-5 flex items-center justify-between">
            <div>
                <p class="text-xs font-bold tracking-widest text-slate-400 uppercase mb-1">System Health</p>
                <h3 id="stat-health-status" class="text-lg font-extrabold text-emerald-600 mt-1 flex items-center gap-1.5">
                    <span class="inline-block w-2.5 h-2.5 rounded-full bg-emerald-500 animate-ping"></span>
                    ALL STABLE
                </h3>
            </div>
            <div id="stat-health-icon" class="w-12 h-12 rounded-xl bg-emerald-50 flex items-center justify-center text-emerald-600 text-xl border border-emerald-100">🛡️</div>
        </div>
    </section>

    <!-- Main Grid Content: Devices Cards -->
    <main class="flex-1 grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-6 items-start pb-6">
        @foreach($devices as $device)
            <!-- Device Card Wrapper -->
            <div id="card-{{ $device->device_id }}" class="glow-card {{ $device->is_online ? 'glow-card-active' : '' }} rounded-2xl flex flex-col overflow-hidden transition-all duration-300" 
                 data-device-id="{{ $device->device_id }}" 
                 data-last-seen="{{ $device->last_seen }}"
                 data-is-online="{{ $device->is_online ? '1' : '0' }}">
                
                <!-- Card Header -->
                <div class="px-5 py-4 border-b border-slate-100 bg-slate-50 flex justify-between items-center flex-shrink-0">
                    <div>
                        <h4 class="text-sm font-extrabold tracking-wide text-slate-800">{{ $device->name }}</h4>
                        <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">{{ $device->group->name ?? 'Default Group' }} | {{ $device->device_id }}</p>
                    </div>
                    
                    <!-- Online Status Badge -->
                    <div id="status-badge-{{ $device->device_id }}" class="flex items-center gap-2">
                        @if($device->is_online)
                            <span class="flex h-2 w-2 relative">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                            </span>
                            <span class="text-[10px] font-extrabold tracking-wider text-emerald-600 uppercase">ONLINE</span>
                        @else
                            <span class="h-2 w-2 rounded-full bg-slate-400"></span>
                            <span class="text-[10px] font-extrabold tracking-wider text-slate-400 uppercase">OFFLINE</span>
                        @endif
                    </div>
                </div>

                <!-- Card Body (Large Gauges/Metrics) -->
                <div class="p-5 grid grid-cols-3 gap-4 flex-1">
                    <!-- Metric: Voltage -->
                    <div class="bg-slate-50 p-4 rounded-xl border border-slate-100 flex flex-col items-center justify-center text-center">
                        <span class="text-[9px] font-bold tracking-widest text-slate-400 uppercase mb-2">VOLTAGE</span>
                        <div id="val-v-{{ $device->device_id }}" class="text-xl sm:text-2xl font-black text-blue-600 glowing-value flex items-baseline gap-0.5">
                            {{ number_format($device->voltage, 1) }}<span class="text-xs text-slate-400 font-bold">V</span>
                        </div>
                        <div id="status-v-{{ $device->device_id }}" class="text-[8px] font-bold text-blue-500 uppercase tracking-widest mt-2">Normal</div>
                    </div>

                    <!-- Metric: Current -->
                    <div class="bg-slate-50 p-4 rounded-xl border border-slate-100 flex flex-col items-center justify-center text-center">
                        <span class="text-[9px] font-bold tracking-widest text-slate-400 uppercase mb-2">CURRENT</span>
                        <div id="val-a-{{ $device->device_id }}" class="text-xl sm:text-2xl font-black text-purple-600 glowing-value flex items-baseline gap-0.5">
                            {{ number_format($device->current, 3) }}<span class="text-xs text-slate-400 font-bold">A</span>
                        </div>
                        <div class="text-[8px] font-bold text-purple-500 uppercase tracking-widest mt-2">Load</div>
                    </div>

                    <!-- Metric: Power -->
                    <div class="bg-slate-50 p-4 rounded-xl border border-slate-100 flex flex-col items-center justify-center text-center">
                        <span class="text-[9px] font-bold tracking-widest text-slate-400 uppercase mb-2">POWER</span>
                        <div id="val-w-{{ $device->device_id }}" class="text-xl sm:text-2xl font-black text-emerald-600 glowing-value flex items-baseline gap-0.5">
                            {{ number_format($device->power, 1) }}<span class="text-xs text-slate-400 font-bold">W</span>
                        </div>
                        <div id="status-w-{{ $device->device_id }}" class="text-[8px] font-bold text-emerald-500 uppercase tracking-widest mt-2">Active</div>
                    </div>
                </div>

                <!-- Card Footer (Energy and Cost Counters) -->
                <div class="px-5 py-4 border-t border-slate-100 bg-slate-50 flex justify-between items-center text-xs tracking-wider flex-shrink-0">
                    <div class="flex items-center gap-1.5 text-slate-500 font-medium">
                        <span>🔋</span> Energy: 
                        <strong id="val-kwh-{{ $device->device_id }}" class="text-slate-700 font-bold digital-mono">{{ number_format($device->energy, 4) }} kWh</strong>
                    </div>
                    <div class="flex items-center gap-1.5 text-slate-500 font-medium">
                        <span>💰</span> Cost: 
                        <strong id="val-cost-{{ $device->device_id }}" class="text-amber-600 font-black digital-mono">Rp {{ number_format($device->energy * $plnTariff, 0, ',', '.') }}</strong>
                    </div>
                </div>
            </div>
        @endforeach
    </main>

    <!-- TV Mode Controller Script -->
    <script>
        const plnTariff = {{ $plnTariff }};
        const alertConfig = @json($alertConfig);

        document.addEventListener('DOMContentLoaded', () => {
            // Live Real-Time Clock
            function updateClock() {
                const clockEl = document.getElementById('tv-clock');
                const dateEl = document.getElementById('tv-date');
                if (!clockEl || !dateEl) return;

                const days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
                const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
                
                const now = new Date();
                
                // Clock format: HH:MM:SS
                const hrs = String(now.getHours()).padStart(2, '0');
                const mins = String(now.getMinutes()).padStart(2, '0');
                const secs = String(now.getSeconds()).padStart(2, '0');
                clockEl.textContent = `${hrs}:${mins}:${secs}`;
                
                // Date format: Day, DD Month YYYY
                const dayName = days[now.getDay()];
                const dateNum = now.getDate();
                const monthName = months[now.getMonth()];
                const year = now.getFullYear();
                dateEl.textContent = `${dayName}, ${dateNum} ${monthName} ${year}`;
            }
            updateClock();
            setInterval(updateClock, 1000);

            // Fullscreen Toggle controller
            window.toggleFullscreen = function() {
                if (!document.fullscreenElement) {
                    document.documentElement.requestFullscreen().catch(err => {
                        console.error(`Error attempting to enable full-screen mode: ${err.message}`);
                    });
                } else {
                    document.exitFullscreen();
                }
            }

            // Realtime WebSocket Channel listener
            if (window.Echo) {
                window.Echo.channel('telemetry')
                    .listen('TelemetryUpdated', (e) => {
                        const deviceId = e.deviceId;
                        const data = e.data; // { voltage, current, power, energy, cost }
                        
                        const card = document.getElementById(`card-${deviceId}`);
                        if (!card) return;

                        // Mark card data
                        const nowTimestamp = Math.floor(Date.now() / 1000);
                        card.setAttribute('data-last-seen', nowTimestamp);
                        
                        // Set online state if not already
                        if (card.getAttribute('data-is-online') === '0') {
                            card.setAttribute('data-is-online', '1');
                            card.classList.add('glow-card-active');
                            
                            const badge = document.getElementById(`status-badge-${deviceId}`);
                            if (badge) {
                                badge.innerHTML = `
                                    <span class="flex h-2 w-2 relative">
                                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                                        <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                                    </span>
                                    <span class="text-[10px] font-extrabold tracking-wider text-emerald-600 uppercase">ONLINE</span>
                                `;
                            }
                        }

                        // Update raw metric numbers
                        const valV = document.getElementById(`val-v-${deviceId}`);
                        const valA = document.getElementById(`val-a-${deviceId}`);
                        const valW = document.getElementById(`val-w-${deviceId}`);
                        const valKwh = document.getElementById(`val-kwh-${deviceId}`);
                        const valCost = document.getElementById(`val-cost-${deviceId}`);

                        if (valV) {
                            valV.innerHTML = `${parseFloat(data.voltage).toFixed(1)}<span class="text-xs text-slate-400 font-bold">V</span>`;
                            const statusV = document.getElementById(`status-v-${deviceId}`);
                            
                            // Check voltage anomalies
                            if (data.voltage < alertConfig.voltage_min) {
                                valV.className = "text-xl sm:text-2xl font-black text-red-600 glowing-value flex items-baseline gap-0.5 animate-pulse";
                                if (statusV) statusV.innerHTML = `<span class="text-red-500">Under voltage</span>`;
                            } else if (data.voltage > alertConfig.voltage_max) {
                                valV.className = "text-xl sm:text-2xl font-black text-red-600 glowing-value flex items-baseline gap-0.5 animate-pulse";
                                if (statusV) statusV.innerHTML = `<span class="text-red-500">Over voltage</span>`;
                            } else {
                                valV.className = "text-xl sm:text-2xl font-black text-blue-600 glowing-value flex items-baseline gap-0.5";
                                if (statusV) statusV.innerHTML = "Normal";
                            }
                        }

                        if (valA) {
                            valA.innerHTML = `${parseFloat(data.current).toFixed(3)}<span class="text-xs text-slate-400 font-bold">A</span>`;
                        }

                        if (valW) {
                            valW.innerHTML = `${parseFloat(data.power).toFixed(1)}<span class="text-xs text-slate-400 font-bold">W</span>`;
                            const statusW = document.getElementById(`status-w-${deviceId}`);
                            
                            // Check power overload anomalies
                            if (data.power > alertConfig.power_max) {
                                valW.className = "text-xl sm:text-2xl font-black text-red-600 glowing-value flex items-baseline gap-0.5 animate-bounce";
                                if (statusW) statusW.innerHTML = `<span class="text-red-500 animate-pulse">⚠️ OVERLOAD</span>`;
                            } else {
                                valW.className = "text-xl sm:text-2xl font-black text-emerald-600 glowing-value flex items-baseline gap-0.5";
                                if (statusW) statusW.innerHTML = "Active";
                            }
                        }

                        if (valKwh) {
                            valKwh.textContent = `${parseFloat(data.energy).toFixed(4)} kWh`;
                        }

                        if (valCost) {
                            const cost = data.cost || (data.energy * plnTariff);
                            valCost.textContent = `Rp ${new Intl.NumberFormat('id-ID').format(Math.round(cost))}`;
                        }

                        // Recalculate summary stats immediately
                        updateSummaryCounters();
                    });
            }

            // Client-side heartbeat/offline timeout checker (15 seconds timeout)
            function checkDeviceTimeouts() {
                const nowTimestamp = Math.floor(Date.now() / 1000);
                const cards = document.querySelectorAll('[data-device-id]');
                
                cards.forEach(card => {
                    const deviceId = card.getAttribute('data-device-id');
                    const lastSeen = parseInt(card.getAttribute('data-last-seen') || 0);
                    const isOnline = card.getAttribute('data-is-online') === '1';

                    if (isOnline && (nowTimestamp - lastSeen >= 15)) {
                        // Mark card offline
                        card.setAttribute('data-is-online', '0');
                        card.classList.remove('glow-card-active');
                        
                        const badge = document.getElementById(`status-badge-${deviceId}`);
                        if (badge) {
                            badge.innerHTML = `
                                <span class="h-2 w-2 rounded-full bg-slate-400"></span>
                                <span class="text-[10px] font-extrabold tracking-wider text-slate-400 uppercase">OFFLINE</span>
                            `;
                        }
                        
                        // Set metrics to 0 or dim
                        const valW = document.getElementById(`val-w-${deviceId}`);
                        const valA = document.getElementById(`val-a-${deviceId}`);
                        const valV = document.getElementById(`val-v-${deviceId}`);
                        if (valW) valW.innerHTML = `0.0<span class="text-xs text-slate-400 font-bold">W</span>`;
                        if (valA) valA.innerHTML = `0.000<span class="text-xs text-slate-400 font-bold">A</span>`;
                        if (valV) valV.innerHTML = `0.0<span class="text-xs text-slate-400 font-bold">V</span>`;
                    }
                });

                // Update summary counters
                updateSummaryCounters();
            }
            setInterval(checkDeviceTimeouts, 1000);

            // Re-calculate the sum of all metrics for top widgets
            function updateSummaryCounters() {
                let totalPower = 0.0;
                let totalCost = 0.0;
                let activeCount = 0;
                let hasAlerts = false;

                const cards = document.querySelectorAll('[data-device-id]');
                cards.forEach(card => {
                    const deviceId = card.getAttribute('data-device-id');
                    const isOnline = card.getAttribute('data-is-online') === '1';

                    // Extrapolate values from UI elements
                    const valW = document.getElementById(`val-w-${deviceId}`);
                    const valV = document.getElementById(`val-v-${deviceId}`);
                    const valCost = document.getElementById(`val-cost-${deviceId}`);

                    if (isOnline) {
                        activeCount++;
                        
                        if (valW) {
                            const powerText = valW.textContent.replace(/[^\d.]/g, '');
                            totalPower += parseFloat(powerText || 0.0);
                        }

                        // Check voltage alerts for health status
                        if (valV) {
                            const voltageText = valV.textContent.replace(/[^\d.]/g, '');
                            const voltage = parseFloat(voltageText || 0.0);
                            if (voltage < alertConfig.voltage_min || voltage > alertConfig.voltage_max) {
                                hasAlerts = true;
                            }
                        }
                    }

                    if (valCost) {
                        const costText = valCost.textContent.replace(/[^\d]/g, '');
                        totalCost += parseInt(costText || 0);
                    }
                });

                // Update DOM elements
                const statPower = document.getElementById('stat-total-power');
                const statCount = document.getElementById('stat-active-count');
                const statCost = document.getElementById('stat-total-cost');
                const statHealth = document.getElementById('stat-health-status');
                const statHealthIcon = document.getElementById('stat-health-icon');

                if (statPower) statPower.innerHTML = `${totalPower.toFixed(1)} <span class="text-lg text-slate-400">W</span>`;
                if (statCount) statCount.textContent = activeCount;
                if (statCost) statCost.textContent = `Rp ${new Intl.NumberFormat('id-ID').format(Math.round(totalCost))}`;

                if (statHealth && statHealthIcon) {
                    if (hasAlerts) {
                        statHealth.className = "text-lg font-extrabold text-red-600 mt-1 flex items-center gap-1.5 animate-pulse";
                        statHealth.innerHTML = `
                            <span class="inline-block w-2.5 h-2.5 rounded-full bg-red-500 animate-ping"></span>
                            VOLTAGE ALERT
                        `;
                        statHealthIcon.className = "w-12 h-12 rounded-xl bg-red-50 flex items-center justify-center text-red-600 text-xl border border-red-100";
                        statHealthIcon.innerHTML = "⚠️";
                    } else {
                        statHealth.className = "text-lg font-extrabold text-emerald-600 mt-1 flex items-center gap-1.5";
                        statHealth.innerHTML = `
                            <span class="inline-block w-2.5 h-2.5 rounded-full bg-emerald-500 animate-ping"></span>
                            ALL STABLE
                        `;
                        statHealthIcon.className = "w-12 h-12 rounded-xl bg-emerald-50 flex items-center justify-center text-emerald-600 text-xl border border-emerald-100";
                        statHealthIcon.innerHTML = "🛡️";
                    }
                }
            }
        });
    </script>
</body>
</html>
