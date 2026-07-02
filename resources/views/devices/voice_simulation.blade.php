@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between border-b border-slate-200 pb-5 mb-8 gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-800 tracking-tight flex items-center gap-2">
                🎙️ JASMIN VOICE ASSISTANT PLAYGROUND
            </h1>
            <p class="text-xs text-slate-500 font-medium tracking-wide mt-1 uppercase">Simulasi & Uji Coba Kendali Suara Lokal Jamkrida IoT</p>
        </div>
        <div>
            <a href="{{ route('office.control') }}" class="px-4 py-2 bg-white hover:bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-600 transition-colors shadow-sm">
                ← Panel Kontrol Fisik
            </a>
        </div>
    </div>

    <!-- Main Content Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
        
        <!-- Left: Voice Orb Interface (5 Cols) -->
        <div class="lg:col-span-5 bg-white/70 backdrop-blur-md border border-slate-200/80 rounded-3xl p-8 shadow-sm flex flex-col items-center text-center relative overflow-hidden" style="min-height: 560px;">
            <!-- Ambient glows -->
            <div class="absolute -top-12 -right-12 w-48 h-48 bg-blue-400/10 rounded-full blur-3xl pointer-events-none"></div>
            <div class="absolute -bottom-12 -left-12 w-48 h-48 bg-emerald-400/10 rounded-full blur-3xl pointer-events-none"></div>

            <span class="text-[10px] font-bold text-blue-500 tracking-widest uppercase mb-4 flex-shrink-0">Voice Assistant Module</span>

            <!-- JASMIN Assistant Holographic Orb -->
            <div class="relative flex items-center justify-center my-6 w-52 h-52 flex-shrink-0">
                <!-- Outer Pulse Rings -->
                <div id="orb-pulse-1" class="absolute inset-0 rounded-full border border-blue-500/20 animate-ping" style="animation-duration: 3s; display: none;"></div>
                <div id="orb-pulse-2" class="absolute inset-4 rounded-full border border-emerald-500/20 animate-ping" style="animation-duration: 2s; display: none;"></div>

                <!-- Main Glassmorphic Orb -->
                <button id="jasmin-orb" onclick="toggleListening()" class="w-36 h-36 min-w-[9rem] min-h-[9rem] rounded-full flex flex-col items-center justify-center shadow-2xl transition-all duration-500 focus:outline-none border-2 border-white/60 active:scale-95 cursor-pointer relative z-10 flex-shrink-0 aspect-square" 
                    style="background: radial-gradient(circle at 30% 30%, rgba(255, 255, 255, 0.98) 0%, rgba(239, 246, 255, 0.9) 50%, rgba(191, 219, 254, 0.65) 100%); box-shadow: 0 15px 35px rgba(59, 130, 246, 0.25), inset 0 0 15px rgba(255, 255, 255, 0.9);">
                    
                    <!-- Icon inside Orb -->
                    <div id="orb-icon" class="text-4xl transition-all duration-500">🎙️</div>
                    <span id="orb-label" class="text-[10px] font-black text-blue-600 uppercase tracking-widest mt-2">TAP TO TALK</span>
                </button>
            </div>

            <!-- Waveform Animation -->
            <div class="w-full h-10 flex items-center justify-center gap-1.5 my-3 flex-shrink-0">
                <div class="wave-bar w-1.5 h-2 bg-blue-500 rounded-full transition-all duration-150"></div>
                <div class="wave-bar w-1.5 h-3 bg-blue-500 rounded-full transition-all duration-150"></div>
                <div class="wave-bar w-1.5 h-5 bg-blue-500 rounded-full transition-all duration-150"></div>
                <div class="wave-bar w-1.5 h-2 bg-blue-500 rounded-full transition-all duration-150"></div>
                <div class="wave-bar w-1.5 h-4 bg-blue-500 rounded-full transition-all duration-150"></div>
            </div>

            <!-- Status Message -->
            <div class="mt-4 max-w-sm flex-shrink-0">
                <h3 id="assistant-status" class="text-lg font-bold text-slate-800">Hi! Saya JASMIN</h3>
                <p id="assistant-sub" class="text-xs text-slate-500 mt-1 font-medium leading-relaxed">Ketuk bola di atas untuk memberikan perintah suara. Coba ucapkan: <br><strong class="text-blue-600">"Jasmin, nyalakan lampu lobby"</strong> atau <br><strong class="text-blue-600">"Matikan AC server room satu"</strong></p>
            </div>

            <!-- Speech Support Check -->
            <div id="speech-warning" class="hidden mt-4 w-full p-3 bg-amber-50 border border-amber-200 rounded-xl text-amber-800 text-[11px] font-semibold flex items-center justify-center gap-2 flex-shrink-0">
                ⚠️ Mikrofon tidak aktif/didukung. Ketuk bola atau ketik di bawah untuk simulasi!
            </div>

            <!-- Fallback Text Input Simulator -->
            <div class="w-full mt-6 pt-5 border-t border-slate-100 flex flex-col gap-2 flex-shrink-0">
                <span class="text-[9px] font-bold text-slate-400 uppercase tracking-wider text-left">Text Command Simulator (Ketik Manual)</span>
                <div class="relative flex items-center">
                    <input type="text" id="text-command-input" placeholder="Contoh: nyalakan lampu lobby..." 
                        class="w-full pl-4 pr-20 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-700 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all"
                        onkeydown="if(event.key === 'Enter') sendTextCommand()">
                    <button onclick="sendTextCommand()" class="absolute right-2 px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-[10px] font-bold transition-colors cursor-pointer focus:outline-none">
                        Kirim
                    </button>
                </div>
            </div>
        </div>

        <!-- Right: Simulator Grid & Console (7 Cols) -->
        <div class="lg:col-span-7 flex flex-col gap-6">
            
            <!-- Simulated Devices Control Panel -->
            <div class="bg-white/70 backdrop-blur-md border border-slate-200/80 rounded-3xl p-6 shadow-sm">
                <h2 class="text-sm font-black text-slate-800 uppercase tracking-wider mb-5 flex items-center gap-2">
                    🖥️ Simulated Device Console
                </h2>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    @foreach($appliances as $appliance)
                        <div id="appliance-card-{{ $appliance['id'] }}" class="p-4 rounded-2xl border transition-all duration-300 flex items-center justify-between {{ $appliance['state'] ? 'bg-emerald-50/50 border-emerald-200/70 shadow-sm shadow-emerald-500/5' : 'bg-slate-50/55 border-slate-200/50' }}">
                            <div class="flex items-center gap-3">
                                <div id="appliance-icon-{{ $appliance['id'] }}" class="w-10 h-10 rounded-xl flex items-center justify-center text-lg {{ $appliance['state'] ? 'bg-emerald-500 text-white shadow-md' : 'bg-slate-200 text-slate-500' }}">
                                    {{ $appliance['icon'] }}
                                </div>
                                <div>
                                    <h4 class="text-xs font-bold text-slate-800">{{ $appliance['name'] }}</h4>
                                    <span class="text-[9px] text-slate-400 font-bold uppercase tracking-wider">{{ $appliance['category'] }}</span>
                                </div>
                            </div>

                            <!-- Sim Switch -->
                            <button onclick="manualToggle('{{ $appliance['id'] }}')" 
                                class="w-12 h-6 rounded-full p-1 transition-colors duration-300 focus:outline-none flex-shrink-0 {{ $appliance['state'] ? 'bg-emerald-500' : 'bg-slate-300' }}">
                                <div class="w-4 h-4 bg-white rounded-full shadow-md transition-transform duration-300 transform {{ $appliance['state'] ? 'translate-x-6' : 'translate-x-0' }}"></div>
                            </button>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Speech Recognition Logs -->
            <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-sm text-slate-300 font-mono text-xs flex flex-col justify-between" style="min-height: 220px;">
                <div>
                    <div class="flex items-center justify-between border-b border-slate-800 pb-3 mb-4">
                        <span class="text-slate-400 font-bold tracking-wider uppercase text-[10px]">Real-Time Transcription Logs</span>
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    </div>
                    <div id="console-logs" class="space-y-2.5 max-h-36 overflow-y-auto pr-2">
                        <div class="text-slate-500 font-bold">[SYSTEM] JASMIN Playground initialized. Waiting for activation...</div>
                    </div>
                </div>
                <div class="mt-4 pt-3 border-t border-slate-800 flex justify-between items-center text-[10px] text-slate-500 font-bold">
                    <span>API ROUTE: /office-control/toggle</span>
                    <button onclick="clearLogs()" class="hover:text-slate-350 transition-colors uppercase cursor-pointer">Clear Console</button>
                </div>
            </div>

        </div>

    </div>
</div>

<style>
    /* Styling for glassmorphic voice assistant page */
    .wave-bar {
        animation: none;
    }
    
    @keyframes wave-bounce {
        0%, 100% {
            transform: scaleY(1);
        }
        50% {
            transform: scaleY(3);
        }
    }
</style>

<script>
    // System registry for appliances state (injected from Controller)
    const appliancesRegistry = {!! json_encode(collect($appliances)->mapWithKeys(fn($item) => [$item['id'] => (int)$item['state']])) !!};
    
    // Web Speech API references
    const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
    let recognition = null;
    let isListening = false;
    let waveInterval = null;

    document.addEventListener('DOMContentLoaded', () => {
        // 1. Check browser compatibility
        if (!SpeechRecognition) {
            document.getElementById('speech-warning').classList.remove('hidden');
            // Do not disable the orb, just change label to indicate simulation fallback
            document.getElementById('orb-label').innerText = 'TAP TO SIMULATE';
            logToConsole('[WARN]', 'Mikrofon/WebSpeech API dinonaktifkan browser. Anda tetap bisa mengeklik bola untuk simulasi ketik!', 'text-amber-500');
            return;
        }

        // 2. Initialize recognition engine
        recognition = new SpeechRecognition();
        recognition.lang = 'id-ID'; // Indonesian Language Recognition
        recognition.interimResults = false;
        recognition.maxAlternatives = 1;

        // 3. Define event handlers
        recognition.onstart = () => {
            isListening = true;
            updateOrbUI('listening');
            logToConsole('[JASMIN]', 'Mendengarkan suara Anda...', 'text-sky-400');
            startWaveAnimation();
        };

        recognition.onresult = (event) => {
            const transcript = event.results[0][0].transcript;
            const confidence = event.results[0][0].confidence;
            logToConsole('[TRANSKRIP]', `"${transcript}" (Akurasi: ${(confidence * 100).toFixed(0)}%)`, 'text-yellow-300');
            processCommand(transcript);
        };

        recognition.onerror = (e) => {
            logToConsole('[ERROR]', `Speech recognition error: ${e.error}`, 'text-rose-500');
            stopListeningState();
        };

        recognition.onend = () => {
            stopListeningState();
        };
    });

    function toggleListening() {
        if (!recognition) {
            // Text simulation prompt fallback
            const typedCommand = prompt("Browser Anda memblokir Mikrofon / Web Speech API.\nMasukkan teks perintah suara Anda di bawah untuk simulasi (Bahasa Indonesia):");
            if (typedCommand && typedCommand.trim() !== "") {
                logToConsole('[SIMULASI]', `"${typedCommand}" (Manual via prompt)`, 'text-yellow-300');
                processCommand(typedCommand);
            }
            return;
        }
        
        if (isListening) {
            recognition.stop();
        } else {
            try {
                recognition.start();
            } catch (err) {
                console.error(err);
            }
        }
    }

    function sendTextCommand() {
        const input = document.getElementById('text-command-input');
        const text = input.value.trim();
        if (text === "") return;
        
        logToConsole('[TEXT SIM]', `"${text}" (Diketik)`, 'text-yellow-300');
        input.value = ""; // Clear input
        processCommand(text);
    }

    function stopListeningState() {
        isListening = false;
        updateOrbUI('standby');
        stopWaveAnimation();
    }

    function updateOrbUI(state) {
        const orb = document.getElementById('jasmin-orb');
        const icon = document.getElementById('orb-icon');
        const label = document.getElementById('orb-label');
        const p1 = document.getElementById('orb-pulse-1');
        const p2 = document.getElementById('orb-pulse-2');
        const statusTitle = document.getElementById('assistant-status');

        if (state === 'listening') {
            orb.style.background = 'radial-gradient(circle at 30% 30%, #ecfdf5 0%, #d1fae5 50%, #a7f3d0 100%)';
            orb.style.borderColor = 'rgba(16, 185, 129, 0.4)';
            orb.style.boxShadow = '0 15px 35px rgba(16, 185, 129, 0.35), inset 0 0 15px rgba(255, 255, 255, 0.9)';
            icon.innerText = '🟢';
            label.innerText = 'LISTENING...';
            label.className = 'text-[10px] font-black text-emerald-650 uppercase tracking-widest mt-2';
            p1.style.display = 'block';
            p2.style.display = 'block';
            statusTitle.innerText = 'JASMIN Mendengarkan...';
        } else {
            orb.style.background = 'radial-gradient(circle at 30% 30%, rgba(255, 255, 255, 0.95) 0%, rgba(239, 246, 255, 0.8) 50%, rgba(191, 219, 254, 0.5) 100%)';
            orb.style.borderColor = 'rgba(255, 255, 255, 0.5)';
            orb.style.boxShadow = '0 15px 35px rgba(59, 130, 246, 0.15), inset 0 0 15px rgba(255, 255, 255, 0.8)';
            icon.innerText = '🎙️';
            label.innerText = 'TAP TO TALK';
            label.className = 'text-[10px] font-black text-blue-600 uppercase tracking-widest mt-2';
            p1.style.display = 'none';
            p2.style.display = 'none';
            statusTitle.innerText = 'Hi! Saya JASMIN';
        }
    }

    function startWaveAnimation() {
        const bars = document.querySelectorAll('.wave-bar');
        bars.forEach((bar, idx) => {
            bar.style.animation = `wave-bounce 0.8s ease-in-out infinite`;
            bar.style.animationDelay = `${idx * 0.15}s`;
        });
    }

    function stopWaveAnimation() {
        const bars = document.querySelectorAll('.wave-bar');
        bars.forEach(bar => {
            bar.style.animation = 'none';
        });
    }

    // Intent Parser Logic
    function processCommand(text) {
        const rawText = text.toLowerCase().trim();
        let state = null;
        let speakText = "";

        // 1. Identify command action (ON or OFF)
        if (/(nyalakan|hidupkan|aktifkan|buka|on)/.test(rawText)) {
            state = 1;
        } else if (/(matikan|padamkan|nonaktifkan|tutup|off)/.test(rawText)) {
            state = 0;
        }

        if (state === null) {
            speakText = "Maaf, saya tidak mengerti maksud perintah Anda. Silakan katakan nyalakan atau matikan diikuti nama alat.";
            logToConsole('[JASMIN]', speakText, 'text-rose-450');
            speakResponse(speakText);
            return;
        }

        // 2. Map spoken device names to actual IDs
        // Build regex keywords based on active appliances in view
        const deviceMap = {
            // Air conditioners
            'ac server room 1': 'ac_server_1',
            'ac server room satu': 'ac_server_1',
            'ac server satu': 'ac_server_1',
            'ac server room 2': 'ac_server_2',
            'ac server room dua': 'ac_server_2',
            'ac server dua': 'ac_server_2',
            'ac server': 'ac_server_1',
            'ac workspace left': 'ac_workspace_1',
            'ac workspace': 'ac_workspace_1',
            'ac meeting room a': 'ac_meeting_1',
            'ac meeting': 'ac_meeting_1',
            'ac': 'ac_server_1',

            // Lights
            'lampu lobby': 'lights_lobby',
            'lampu utama': 'lights_lobby',
            'lampu lobby reception': 'lights_lobby',
            'lampu reception': 'lights_lobby',
            'lampu workspace': 'lights_workspace',
            'lampu ruang kerja': 'lights_workspace',
            'lampu meeting': 'lights_meeting',
            'lampu ruang meeting': 'lights_meeting',
            'lampu': 'lights_lobby',

            // Ventilation
            'kipas angin': 'exhaust_server',
            'kipas server': 'exhaust_server',
            'exhaust server': 'exhaust_server',
            'kipas': 'exhaust_server',
            'exhaust': 'exhaust_server'
        };

        // Support dynamic channel triggers if user registers actual relay controllers
        @foreach($appliances as $app)
            deviceMap['{!! strtolower($app['name']) !!}'] = '{{ $app['id'] }}';
        @endforeach

        let targetId = null;
        let matchedPhrase = "";
        for (const [phrase, id] of Object.entries(deviceMap)) {
            if (rawText.includes(phrase)) {
                targetId = id;
                matchedPhrase = phrase;
                break;
            }
        }

        if (!targetId) {
            speakText = "Maaf, saya tidak menemukan nama peralatan tersebut di daftar kontrol kantor.";
            logToConsole('[JASMIN]', speakText, 'text-rose-450');
            speakResponse(speakText);
            return;
        }

        const actionWord = state === 1 ? 'dinyalakan' : 'dimatikan';
        const applianceName = document.querySelector(`#appliance-card-${targetId} h4`).innerText;

        logToConsole('[PARSER]', `Kecocokan kata kunci "${matchedPhrase}" -> Target: ${targetId} (${actionWord})`, 'text-emerald-450');

        // 3. Fire the request
        sendToggleRequest(targetId, state, applianceName, actionWord);
    }

    function sendToggleRequest(applianceId, state, applianceName, actionWord) {
        logToConsole('[POST]', `Sending command to /office-control/toggle...`, 'text-slate-500');
        
        fetch("{{ route('office.control.toggle') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                appliance_id: applianceId,
                state: state
            })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                // Update local UI
                updateLocalDeviceUI(applianceId, state);
                
                // Speak confirmation back
                const responseMessage = `Baik, ${applianceName} telah berhasil ${actionWord}.`;
                logToConsole('[JASMIN]', responseMessage, 'text-emerald-450');
                speakResponse(responseMessage);
            } else {
                const failMsg = `Gagal mengubah status ${applianceName}.`;
                logToConsole('[SYSTEM]', failMsg, 'text-rose-500');
                speakResponse(failMsg);
            }
        })
        .catch(err => {
            console.error(err);
            const errorMsg = "Koneksi terputus. Gagal mengirimkan perintah ke server.";
            logToConsole('[SYSTEM]', errorMsg, 'text-rose-500');
            speakResponse(errorMsg);
        });
    }

    function updateLocalDeviceUI(applianceId, state) {
        appliancesRegistry[applianceId] = state;
        
        const card = document.getElementById(`appliance-card-${applianceId}`);
        const iconDiv = document.getElementById(`appliance-icon-${applianceId}`);
        const button = document.querySelector(`#appliance-card-${applianceId} button`);
        const switchCircle = button.querySelector('div');

        if (state === 1) {
            card.className = "p-4 rounded-2xl border transition-all duration-300 flex items-center justify-between bg-emerald-50/50 border-emerald-200/70 shadow-sm shadow-emerald-500/5";
            iconDiv.className = "w-10 h-10 rounded-xl flex items-center justify-center text-lg bg-emerald-500 text-white shadow-md";
            button.className = "w-12 h-6 rounded-full p-1 transition-colors duration-300 focus:outline-none bg-emerald-500";
            switchCircle.className = "w-4 h-4 bg-white rounded-full shadow-md transition-transform duration-300 transform translate-x-6";
        } else {
            card.className = "p-4 rounded-2xl border transition-all duration-300 flex items-center justify-between bg-slate-50/55 border-slate-200/50";
            iconDiv.className = "w-10 h-10 rounded-xl flex items-center justify-center text-lg bg-slate-200 text-slate-500";
            button.className = "w-12 h-6 rounded-full p-1 transition-colors duration-300 focus:outline-none bg-slate-300";
            switchCircle.className = "w-4 h-4 bg-white rounded-full shadow-md transition-transform duration-300 transform translate-x-0";
        }
    }

    function manualToggle(applianceId) {
        const currentState = appliancesRegistry[applianceId];
        const newState = currentState === 1 ? 0 : 1;
        const actionWord = newState === 1 ? 'dinyalakan' : 'dimatikan';
        const applianceName = document.querySelector(`#appliance-card-${applianceId} h4`).innerText;

        logToConsole('[MANUAL]', `User toggled ${applianceId} manually on screen.`, 'text-slate-400');
        sendToggleRequest(applianceId, newState, applianceName, actionWord);
    }

    // Text to Speech
    function speakResponse(text) {
        if ('speechSynthesis' in window) {
            window.speechSynthesis.cancel(); // Stop any currently playing audio
            
            const utterance = new SpeechSynthesisUtterance(text);
            utterance.lang = 'id-ID';
            utterance.rate = 1.0;
            utterance.pitch = 1.05;

            // Load and match Indonesian voice profile
            const voices = window.speechSynthesis.getVoices();
            const indonesianVoice = voices.find(voice => voice.lang.includes('id-ID'));
            if (indonesianVoice) {
                utterance.voice = indonesianVoice;
            }

            window.speechSynthesis.speak(utterance);
        }
    }

    // Console Logging Utilities
    function logToConsole(prefix, message, colorClass = 'text-slate-300') {
        const consoleLogs = document.getElementById('console-logs');
        const timestamp = new Date().toLocaleTimeString('id-ID', { hour12: false });
        
        const logLine = document.createElement('div');
        logLine.className = 'leading-relaxed';
        logLine.innerHTML = `<span class="text-slate-600 font-bold">[${timestamp}]</span> <span class="${colorClass} font-bold">${prefix}</span> ${message}`;
        
        consoleLogs.appendChild(logLine);
        consoleLogs.scrollTop = consoleLogs.scrollHeight;
    }

    function clearLogs() {
        const consoleLogs = document.getElementById('console-logs');
        consoleLogs.innerHTML = `<div class="text-slate-500 font-bold">[SYSTEM] Logs cleared. Waiting for commands...</div>`;
    }

    // Warm up the speech synthesis voices (required for chrome to load voices)
    if ('speechSynthesis' in window) {
        window.speechSynthesis.getVoices();
        window.speechSynthesis.onvoiceschanged = () => {
            window.speechSynthesis.getVoices();
        };
    }
</script>
@endsection
