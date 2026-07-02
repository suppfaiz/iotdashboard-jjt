@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between border-b border-slate-200 pb-5 mb-8 gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-800 tracking-tight flex items-center gap-2">
                🎮 OFFICE IOT CONTROL HUB
            </h1>
            <p class="text-xs text-slate-500 font-medium tracking-wide mt-1 uppercase">Monitor Office Environment & Control Auxiliary Smart Devices</p>
        </div>
        
        <div class="flex items-center gap-3">
            <a href="{{ route('dashboard') }}" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-white rounded-xl text-xs font-bold transition-colors shadow-sm">
                ← Dashboard Home
            </a>
        </div>
    </div>

    <!-- Main Grid Content -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
        
        <!-- Left: Environment Monitoring (Col 7) -->
        <div class="lg:col-span-7 flex flex-col gap-6">
            <div class="bg-white/70 backdrop-blur-md border border-slate-200/80 rounded-3xl p-6 shadow-sm">
                <div class="flex justify-between items-center border-b border-slate-100 pb-4 mb-6">
                    <div>
                        <h3 class="text-base font-extrabold text-slate-800">🌡️ Climate & Environment Monitor</h3>
                        <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest mt-0.5">Real-time room temperature and humidity metrics</p>
                    </div>
                    <span class="flex items-center gap-1.5 text-[9px] font-black text-emerald-600 bg-emerald-50 border border-emerald-100 px-2.5 py-1 rounded-full uppercase tracking-wider">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span> Live Sensors
                    </span>
                </div>

                <!-- Climate Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    @foreach($rooms as $room)
                        <div id="room-card-{{ $room['id'] }}" class="bg-slate-50/50 border border-slate-200/60 rounded-2xl p-5 relative overflow-hidden transition-all duration-300 hover:shadow-md hover:bg-white">
                            <!-- Background blueprint indicator -->
                            <div class="absolute -right-6 -bottom-6 text-7xl text-slate-100/50 font-black tracking-tighter select-none pointer-events-none uppercase">
                                {{ substr($room['name'], 0, 3) }}
                            </div>

                            <div class="flex justify-between items-start relative z-10">
                                <div class="flex flex-col">
                                    <span class="text-sm font-extrabold text-slate-800">{{ $room['name'] }}</span>
                                    <span class="text-[9px] font-bold {{ $room['status_color'] }} border px-2 py-0.5 rounded-md uppercase tracking-wider mt-1 w-max">
                                        {{ $room['comfort'] }}
                                    </span>
                                </div>
                                <span class="text-xl">🏢</span>
                            </div>

                            <!-- Metrics display -->
                            <div class="grid grid-cols-2 gap-4 mt-6 relative z-10">
                                <!-- Temp -->
                                <div class="bg-white/80 border border-slate-100 rounded-xl p-3 shadow-sm">
                                    <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest block">Temperature</span>
                                    <span class="text-2xl font-black text-slate-800 tracking-tight mt-1 block">
                                        <span id="temp-val-{{ $room['id'] }}">{{ number_format($room['temp'], 1) }}</span>°C
                                    </span>
                                </div>
                                <!-- Humi -->
                                <div class="bg-white/80 border border-slate-100 rounded-xl p-3 shadow-sm">
                                    <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest block">Humidity</span>
                                    <span class="text-2xl font-black text-slate-800 tracking-tight mt-1 block">
                                        <span id="humi-val-{{ $room['id'] }}">{{ number_format($room['humi'], 1) }}</span>%
                                    </span>
                                </div>
                            </div>

                            <!-- Sparkline Chart visualization -->
                            <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-[9px] text-slate-400 font-bold uppercase tracking-wider relative z-10">
                                <span>Trend (24h)</span>
                                <span class="flex items-center gap-1 text-emerald-650">
                                    <span>Stable</span>
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                </span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Right: Appliance Relay Control (Col 5) -->
        <div class="lg:col-span-5 flex flex-col gap-6">
            <div class="bg-white/70 backdrop-blur-md border border-slate-200/80 rounded-3xl p-6 shadow-sm">
                <div class="border-b border-slate-100 pb-4 mb-6">
                    <h3 class="text-base font-extrabold text-slate-800">🔌 Relay & Appliance Control</h3>
                    <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest mt-0.5">Toggle office power relays and devices remotely</p>
                </div>

                <!-- Appliances Switches list -->
                <div class="space-y-4">
                    @foreach($appliances as $appliance)
                        <div class="flex items-center justify-between bg-slate-50/50 border border-slate-200/50 rounded-2xl p-4 transition-all hover:bg-white hover:shadow-sm">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-white border border-slate-200/70 shadow-sm flex items-center justify-center text-xl">
                                    {{ $appliance['icon'] }}
                                </div>
                                <div class="flex flex-col">
                                    <span class="text-xs font-black text-slate-800">{{ $appliance['name'] }}</span>
                                    <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">{{ $appliance['category'] }}</span>
                                </div>
                            </div>

                            <!-- Toggle Switch control -->
                            <div class="flex items-center gap-2">
                                <span id="label-state-{{ $appliance['id'] }}" class="text-[9px] font-black uppercase tracking-wider {{ $appliance['state'] ? 'text-blue-600' : 'text-slate-400' }} mr-1">
                                    {{ $appliance['state'] ? 'ON' : 'OFF' }}
                                </span>
                                
                                <label class="inline-flex items-center cursor-pointer relative">
                                    <input type="checkbox" 
                                           id="switch-{{ $appliance['id'] }}"
                                           {{ $appliance['state'] ? 'checked' : '' }}
                                           onchange="toggleApplianceState('{{ $appliance['id'] }}')" 
                                           class="sr-only peer">
                                    <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                                </label>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

    </div>
</div>

<!-- Toast notifications wrapper -->
<div id="toast-wrapper" class="fixed bottom-6 right-6 z-[10000] flex flex-col gap-2 pointer-events-none"></div>

<script>
    // CSRF Token
    const csrfToken = '{{ csrf_token() }}';
    
    // Toggle Relay action
    function toggleApplianceState(applianceId) {
        const checkbox = document.getElementById(`switch-${applianceId}`);
        const label = document.getElementById(`label-state-${applianceId}`);
        const targetState = checkbox.checked ? 1 : 0;
        
        // Temporarily disable click until action completes
        checkbox.disabled = true;
        
        label.textContent = 'WAIT...';
        label.className = 'text-[9px] font-black uppercase tracking-wider text-slate-400 animate-pulse';

        // POST request to target toggle endpoint
        fetch('{{ route("office.control.toggle") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({
                appliance_id: applianceId,
                state: targetState
            })
        })
        .then(res => res.json())
        .then(data => {
            checkbox.disabled = false;
            if (data.success) {
                // Success updates
                label.textContent = targetState ? 'ON' : 'OFF';
                label.className = `text-[9px] font-black uppercase tracking-wider ${targetState ? 'text-blue-600' : 'text-slate-400'}`;
                showToast(`Peralatan "${applianceId}" berhasil diubah menjadi ${targetState ? 'ON' : 'OFF'}.`, 'success');
            } else {
                throw new Error();
            }
        })
        .catch(() => {
            // Revert state on error
            checkbox.disabled = false;
            checkbox.checked = !checkbox.checked;
            const currentState = checkbox.checked ? 1 : 0;
            label.textContent = currentState ? 'ON' : 'OFF';
            label.className = `text-[9px] font-black uppercase tracking-wider ${currentState ? 'text-blue-600' : 'text-slate-400'}`;
            showToast(`Gagal mengubah status peralatan. Periksa koneksi broker MQTT Anda.`, 'danger');
        });
    }

    // Helper to display clean premium slide-in toasts
    function showToast(message, type = 'success') {
        const wrapper = document.getElementById('toast-wrapper');
        const toast = document.createElement('div');
        toast.className = `p-4 rounded-xl border shadow-lg text-xs font-bold transition-all duration-500 transform translate-y-10 opacity-0 pointer-events-auto max-w-sm flex items-center gap-2.5 ${
            type === 'success' ? 'bg-emerald-50 text-emerald-800 border-emerald-200' : 'bg-red-50 text-red-800 border-red-200'
        }`;
        
        toast.innerHTML = `
            <span>${type === 'success' ? '✅' : '❌'}</span>
            <span class="flex-grow">${message}</span>
        `;
        
        wrapper.appendChild(toast);
        
        // Slide in
        setTimeout(() => {
            toast.classList.remove('translate-y-10', 'opacity-0');
        }, 50);

        // Slide out & remove
        setTimeout(() => {
            toast.classList.add('translate-y-[-10px]', 'opacity-0');
            setTimeout(() => {
                toast.remove();
            }, 500);
        }, 4000);
    }

    // Live climate updates simulation (±0.1 fluctuation for visual lifelike animations)
    const mockRoomFluctuations = {
        server_room: { baseTemp: 19.8, baseHumi: 42.5, minT: 18.5, maxT: 21.0 },
        main_workspace: { baseTemp: 24.2, baseHumi: 53.0, minT: 23.0, maxT: 25.5 },
        meeting_room_a: { baseTemp: 22.5, baseHumi: 48.0, minT: 21.5, maxT: 23.8 },
        lobby_reception: { baseTemp: 25.1, baseHumi: 58.5, minT: 24.0, maxT: 26.5 }
    };

    setInterval(() => {
        Object.keys(mockRoomFluctuations).forEach(roomId => {
            const data = mockRoomFluctuations[roomId];
            
            // Fluctuating offset
            let tempDelta = (Math.random() - 0.5) * 0.2;
            let humiDelta = (Math.random() - 0.5) * 0.4;
            
            data.baseTemp = Math.min(data.maxT, Math.max(data.minT, data.baseTemp + tempDelta));
            data.baseHumi = Math.min(90, Math.max(20, data.baseHumi + humiDelta));

            // Only update DOM if they are not overridden by real sensor messages
            const tempEl = document.getElementById(`temp-val-${roomId}`);
            const humiEl = document.getElementById(`humi-val-${roomId}`);
            
            if (tempEl && !tempEl.dataset.realOverride) {
                tempEl.textContent = data.baseTemp.toFixed(1);
            }
            if (humiEl && !humiEl.dataset.realOverride) {
                humiEl.textContent = data.baseHumi.toFixed(1);
            }
        });
    }, 4000);

    // Pusher/Reverb WebSockets actual channel listener for real sensors integration
    document.addEventListener('DOMContentLoaded', () => {
        if (window.Echo) {
            window.Echo.channel('telemetry')
                .listen('TelemetryUpdated', (e) => {
                    if (e.data && e.data.type === 'office-env') {
                        const roomId = e.deviceId; // match deviceId directly to roomId
                        const tempEl = document.getElementById(`temp-val-${roomId}`);
                        const humiEl = document.getElementById(`humi-val-${roomId}`);
                        const card = document.getElementById(`room-card-${roomId}`);

                        if (tempEl) {
                            tempEl.textContent = parseFloat(e.data.temperature).toFixed(1);
                            tempEl.dataset.realOverride = "true"; // mark to pause local fluctuations simulation
                        }
                        if (humiEl) {
                            humiEl.textContent = parseFloat(e.data.humidity).toFixed(1);
                            humiEl.dataset.realOverride = "true";
                        }

                        // Flash card visually to indicate live sync trigger
                        if (card) {
                            card.classList.add('border-blue-500', 'bg-blue-50/20');
                            setTimeout(() => {
                                card.classList.remove('border-blue-500', 'bg-blue-50/20');
                            }, 1000);
                        }
                    }
                });
        }
    });
</script>
@endsection
