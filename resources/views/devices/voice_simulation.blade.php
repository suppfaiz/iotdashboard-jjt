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
                <div class="relative w-full">
                    <input type="text" id="text-command-input" placeholder="Contoh: nyalakan lampu lobby..." 
                        class="block w-full pl-4 pr-20 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-700 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all"
                        onkeydown="if(event.key === 'Enter') sendTextCommand()">
                    <button onclick="sendTextCommand()" class="absolute right-2 top-1/2 -translate-y-1/2 px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-[10px] font-bold transition-colors cursor-pointer focus:outline-none">
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

    // Intent Parser Logic — Smart Indonesian Voice Command Engine
    function normalizeText(text) {
        let t = text.toLowerCase().trim();
        // Normalize Indonesian number words to digits
        const numberWords = {
            'nol': '0', 'satu': '1', 'dua': '2', 'tiga': '3', 'empat': '4',
            'lima': '5', 'enam': '6', 'tujuh': '7', 'delapan': '8', 'sembilan': '9',
            'sepuluh': '10', 'pertama': '1', 'kedua': '2', 'ketiga': '3'
        };
        for (const [word, digit] of Object.entries(numberWords)) {
            t = t.replace(new RegExp(`\\b${word}\\b`, 'g'), digit);
        }
        // Common speech recognition mistakes / synonyms
        t = t.replace(/\broom\b/g, 'room')
             .replace(/\bruangan?\b/g, 'room')
             .replace(/\brungan\b/g, 'room')
             .replace(/\bkamar\b/g, 'room')
             .replace(/\bworkspace\b/g, 'workspace')
             .replace(/\bruang\s*kerja\b/g, 'workspace')
             .replace(/\bmiting\b/g, 'meeting')
             .replace(/\bmeeting\b/g, 'meeting')
             .replace(/\brapat\b/g, 'meeting')
             .replace(/\blobby\b/g, 'lobby')
             .replace(/\blobi\b/g, 'lobby')
             .replace(/\bserver\b/g, 'server')
             .replace(/\bserfer\b/g, 'server')
             .replace(/\bsurfer\b/g, 'server')
             .replace(/\bleft\b/g, 'left')
             .replace(/\bkiri\b/g, 'left')
             .replace(/\bright\b/g, 'right')
             .replace(/\bkanan\b/g, 'right')
             .replace(/\bbackup\b/g, 'backup')
             .replace(/\bcadangan\b/g, 'backup')
             .replace(/\butama\b/g, 'utama')
             .replace(/\bseluruh\b/g, 'semua')
             .replace(/\bsemuanya\b/g, 'semua');
        return t;
    }

    function processCommand(text) {
        const rawText = normalizeText(text);
        let state = null;
        let speakText = "";
        let isStatusCheck = false;

        // 1. Identify command action (ON, OFF, or STATUS)
        if (/(nyalakan|nyalain|hidupkan|hidupin|aktifkan|aktifin|buka|on\b|pasang|jalankan)/.test(rawText)) {
            state = 1;
        } else if (/(matikan|matiin|padamkan|nonaktifkan|tutup|off\b|cabut|hentikan|stop)/.test(rawText)) {
            state = 0;
        } else if (/(status|cek|periksa|kondisi|keadaan|info|gimana|bagaimana)/.test(rawText)) {
            isStatusCheck = true;
        }

        if (state === null && !isStatusCheck) {
            speakText = "Maaf, saya tidak mengerti perintah Anda. Silakan katakan nyalakan atau matikan diikuti nama alat. Contoh: nyalakan AC server room 1.";
            logToConsole('[JASMIN]', speakText, 'text-rose-400');
            speakResponse(speakText);
            return;
        }

        // 2. Build device alias map — sorted by longest phrase first to prevent greedy short matches
        const deviceMap = [];

        // Static aliases for simulated fallback devices
        const staticAliases = {
            'ac_server_1': [
                'ac server room 1', 'ac server 1', 'ac server room', 'ac ruang server 1',
                'ac ruang server', 'pendingin server 1', 'pendingin server room 1'
            ],
            'ac_server_2': [
                'ac server room 2', 'ac server 2', 'ac server backup', 'ac server room backup',
                'ac ruang server 2', 'ac cadangan server', 'pendingin server 2'
            ],
            'ac_workspace_1': [
                'ac workspace left', 'ac workspace', 'ac workspace kiri', 'ac ruang kerja',
                'ac workspace left', 'ac kantor', 'pendingin workspace'
            ],
            'ac_meeting_1': [
                'ac meeting room a', 'ac meeting room', 'ac meeting', 'ac ruang meeting',
                'ac ruang rapat', 'ac rapat', 'pendingin meeting', 'pendingin rapat'
            ],
            'lights_lobby': [
                'lampu lobby', 'lampu lobi', 'lampu lobby reception', 'lampu reception',
                'lampu utama lobby', 'lampu utama', 'lampu resepsionis', 'penerangan lobby'
            ],
            'lights_workspace': [
                'lampu workspace', 'lampu ruang kerja', 'lampu kantor', 'lampu kerja',
                'lampu workspace utama', 'penerangan workspace', 'penerangan ruang kerja'
            ],
            'lights_meeting': [
                'lampu meeting room', 'lampu meeting', 'lampu ruang meeting', 'lampu ruang rapat',
                'lampu rapat', 'penerangan meeting', 'penerangan rapat'
            ],
            'exhaust_server': [
                'kipas server', 'exhaust server', 'kipas angin server', 'exhaust fan server',
                'exhaust fan', 'kipas exhaust', 'ventilasi server', 'kipas angin', 'exhaust', 'kipas'
            ]
        };

        // Register all static aliases
        for (const [id, aliases] of Object.entries(staticAliases)) {
            if (appliancesRegistry.hasOwnProperty(id)) {
                for (const alias of aliases) {
                    deviceMap.push({ phrase: alias, id: id });
                }
            }
        }

        // Register dynamic relay device names from controller injection
        @foreach($appliances as $app)
            deviceMap.push({ phrase: '{!! strtolower($app['name']) !!}', id: '{{ $app['id'] }}' });
            @php
                // Also add channel number shorthand alias
                $chMatch = [];
                preg_match('/\(CH(\d+)\)/', $app['name'], $chMatch);
                $baseName = strtolower(preg_replace('/\s*\(CH\d+\)/', '', $app['name']));
            @endphp
            @if(!empty($chMatch))
                deviceMap.push({ phrase: '{{ $baseName }} channel {{ $chMatch[1] }}', id: '{{ $app['id'] }}' });
                deviceMap.push({ phrase: '{{ $baseName }} ch {{ $chMatch[1] }}', id: '{{ $app['id'] }}' });
                deviceMap.push({ phrase: '{{ $baseName }} {{ $chMatch[1] }}', id: '{{ $app['id'] }}' });
            @endif
        @endforeach

        // Sort by longest phrase first to prevent greedy short matches
        deviceMap.sort((a, b) => b.phrase.length - a.phrase.length);

        // 3. Match device in user's command text
        let targetId = null;
        let matchedPhrase = "";
        for (const entry of deviceMap) {
            if (rawText.includes(entry.phrase)) {
                targetId = entry.id;
                matchedPhrase = entry.phrase;
                break;
            }
        }

        // 4. Try fuzzy matching if exact match failed — check if any 2+ word overlap exists
        if (!targetId) {
            let bestScore = 0;
            const inputWords = rawText.split(/\s+/);
            for (const entry of deviceMap) {
                const phraseWords = entry.phrase.split(/\s+/);
                let matchCount = 0;
                for (const pw of phraseWords) {
                    if (inputWords.includes(pw)) matchCount++;
                }
                const score = matchCount / phraseWords.length;
                if (matchCount >= 2 && score > bestScore) {
                    bestScore = score;
                    targetId = entry.id;
                    matchedPhrase = entry.phrase + ' (fuzzy)';
                }
            }
        }

        if (!targetId) {
            // Suggest available devices
            const availableNames = [];
            const seen = new Set();
            for (const entry of deviceMap) {
                if (!seen.has(entry.id)) {
                    seen.add(entry.id);
                    const cardEl = document.querySelector(`#appliance-card-${entry.id} h4`);
                    if (cardEl) availableNames.push(cardEl.innerText);
                }
            }
            speakText = `Maaf, saya tidak menemukan peralatan tersebut. Peralatan yang tersedia: ${availableNames.join(', ')}.`;
            logToConsole('[JASMIN]', speakText, 'text-rose-400');
            speakResponse(speakText);
            return;
        }

        const cardEl = document.querySelector(`#appliance-card-${targetId} h4`);
        const applianceName = cardEl ? cardEl.innerText : targetId;

        // 5. Handle status check
        if (isStatusCheck) {
            const currentState = appliancesRegistry[targetId];
            const statusWord = currentState === 1 ? 'menyala' : 'mati';
            speakText = `Status ${applianceName} saat ini sedang ${statusWord}.`;
            logToConsole('[JASMIN]', speakText, 'text-sky-400');
            speakResponse(speakText);
            return;
        }

        const actionWord = state === 1 ? 'dinyalakan' : 'dimatikan';
        logToConsole('[PARSER]', `Kecocokan: "${matchedPhrase}" → Target: ${targetId} (${actionWord})`, 'text-emerald-400');

        // 6. Fire the request
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
