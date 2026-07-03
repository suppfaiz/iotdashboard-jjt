@extends('layouts.app')

@section('content')
<div class="voice-sim-page">
    <!-- Header -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-6 pb-4">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
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
    </div>

    <!-- Hero Voice Assistant Section -->
    <div class="voice-hero-section">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="flex flex-col items-center text-center relative z-10">
                
                <!-- Floating ambient particles -->
                <div class="voice-particle voice-particle-1"></div>
                <div class="voice-particle voice-particle-2"></div>
                <div class="voice-particle voice-particle-3"></div>

                <span class="voice-module-badge">Voice Assistant Module</span>

                <!-- JASMIN Orb Container -->
                <div class="voice-orb-container">
                    <!-- Outer glow rings -->
                    <div id="orb-pulse-1" class="voice-orb-ring voice-orb-ring-1"></div>
                    <div id="orb-pulse-2" class="voice-orb-ring voice-orb-ring-2"></div>
                    
                    <!-- Static ambient ring -->
                    <div class="voice-orb-ambient"></div>

                    <!-- Main Orb Button -->
                    <button id="jasmin-orb" onclick="toggleListening()" class="voice-orb-button" title="Klik untuk berbicara">
                        <div class="voice-orb-inner-glow"></div>
                        <div id="orb-icon" class="voice-orb-icon">🎙️</div>
                        <span id="orb-label" class="voice-orb-label">TAP TO TALK</span>
                    </button>
                </div>

                <!-- Waveform Animation -->
                <div class="voice-waveform">
                    <div class="wave-bar"></div>
                    <div class="wave-bar"></div>
                    <div class="wave-bar"></div>
                    <div class="wave-bar"></div>
                    <div class="wave-bar"></div>
                    <div class="wave-bar"></div>
                    <div class="wave-bar"></div>
                </div>

                <!-- Status Message -->
                <div class="voice-status-area">
                    <h3 id="assistant-status" class="voice-status-title">Hi! Saya JASMIN</h3>
                    <p id="assistant-sub" class="voice-status-sub">Ketuk bola di atas untuk memberikan perintah suara. Coba ucapkan: <br><strong>"Jasmin, nyalakan lampu lobby"</strong> atau <strong>"Matikan AC server room satu"</strong></p>
                </div>

                <!-- Speech Support Warning -->
                <div id="speech-warning" class="voice-speech-warning hidden">
                    ⚠️ Mikrofon tidak aktif/didukung. Ketuk bola atau ketik di bawah untuk simulasi!
                </div>

                <!-- Fallback Text Input -->
                <div class="voice-text-input-area">
                    <span class="voice-text-input-label">Text Command Simulator</span>
                    <div class="voice-text-input-wrapper">
                        <input type="text" id="text-command-input" placeholder="Ketik perintah... contoh: nyalakan lampu lobby" 
                            class="voice-text-input"
                            onkeydown="if(event.key === 'Enter') sendTextCommand()">
                        <button onclick="sendTextCommand()" class="voice-text-input-btn">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="22" y1="2" x2="11" y2="13"></line><polygon points="22 2 15 22 11 13 2 9 22 2"></polygon></svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Device Console & Logs -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            
            <!-- Left: Simulated Devices Control Panel -->
            <div class="lg:col-span-7">
                <div class="voice-devices-panel">
                    <div class="voice-devices-header">
                        <h2>🖥️ Simulated Device Console</h2>
                        <span class="voice-devices-count">{{ count($appliances) }} Peralatan</span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        @foreach($appliances as $appliance)
                            <div id="appliance-card-{{ $appliance['id'] }}" class="voice-device-card {{ $appliance['state'] ? 'voice-device-card-on' : 'voice-device-card-off' }}">
                                <div class="flex items-center gap-3 min-w-0">
                                    <div id="appliance-icon-{{ $appliance['id'] }}" class="voice-device-icon {{ $appliance['state'] ? 'voice-device-icon-on' : 'voice-device-icon-off' }}">
                                        {{ $appliance['icon'] }}
                                    </div>
                                    <div class="min-w-0">
                                        <h4 class="voice-device-name">{{ $appliance['name'] }}</h4>
                                        <span class="voice-device-category">{{ $appliance['category'] }}</span>
                                    </div>
                                </div>

                                <!-- Toggle Switch -->
                                <button onclick="manualToggle('{{ $appliance['id'] }}')" 
                                    class="voice-toggle-switch flex-shrink-0 {{ $appliance['state'] ? 'voice-toggle-on' : 'voice-toggle-off' }}">
                                    <div class="voice-toggle-knob {{ $appliance['state'] ? 'voice-toggle-knob-on' : 'voice-toggle-knob-off' }}"></div>
                                </button>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Right: Speech Recognition Logs -->
            <div class="lg:col-span-5">
                <div class="voice-console-panel">
                    <div class="voice-console-header">
                        <span>Real-Time Transcription Logs</span>
                        <span class="voice-console-dot"></span>
                    </div>
                    <div id="console-logs" class="voice-console-logs">
                        <div class="text-slate-500 font-bold">[SYSTEM] JASMIN Playground initialized. Waiting for activation...</div>
                    </div>
                    <div class="voice-console-footer">
                        <span>API ROUTE: /office-control/toggle</span>
                        <button onclick="clearLogs()" class="voice-console-clear-btn">Clear Console</button>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<style>
    .voice-sim-page { min-height: 100vh; background: linear-gradient(180deg, #f8fafc 0%, #f1f5f9 100%); }

    /* Hero Section */
    .voice-hero-section {
        background: linear-gradient(135deg, #0f172a 0%, #1e293b 40%, #0f172a 100%);
        position: relative; overflow: hidden;
        border-radius: 0 0 2rem 2rem; margin: 0 1rem;
        box-shadow: 0 20px 60px rgba(15, 23, 42, 0.3);
    }
    .voice-hero-section::before {
        content: ''; position: absolute; top: -50%; left: -50%; width: 200%; height: 200%;
        background: radial-gradient(ellipse at 50% 50%, rgba(59, 130, 246, 0.08) 0%, transparent 60%);
        pointer-events: none;
    }
    .voice-hero-section::after {
        content: ''; position: absolute; bottom: 0; left: 0; right: 0; height: 1px;
        background: linear-gradient(90deg, transparent, rgba(59, 130, 246, 0.3), rgba(16, 185, 129, 0.3), transparent);
    }

    /* Particles */
    .voice-particle { position: absolute; border-radius: 50%; pointer-events: none; animation: particle-float 8s ease-in-out infinite; }
    .voice-particle-1 { width: 6px; height: 6px; background: rgba(59, 130, 246, 0.4); top: 20%; left: 15%; }
    .voice-particle-2 { width: 4px; height: 4px; background: rgba(16, 185, 129, 0.4); top: 60%; right: 20%; animation-delay: 3s; }
    .voice-particle-3 { width: 5px; height: 5px; background: rgba(139, 92, 246, 0.3); bottom: 30%; left: 25%; animation-delay: 5s; }
    @keyframes particle-float {
        0%, 100% { transform: translate(0, 0) scale(1); opacity: 0.4; }
        25% { transform: translate(20px, -30px) scale(1.5); opacity: 0.8; }
        50% { transform: translate(-15px, -20px) scale(1); opacity: 0.3; }
        75% { transform: translate(25px, 10px) scale(1.3); opacity: 0.7; }
    }

    /* Module badge */
    .voice-module-badge {
        display: inline-block; padding: 4px 16px; border-radius: 20px;
        font-size: 10px; font-weight: 800; letter-spacing: 0.15em; text-transform: uppercase;
        color: rgba(147, 197, 253, 0.9); background: rgba(59, 130, 246, 0.1);
        border: 1px solid rgba(59, 130, 246, 0.2); margin-bottom: 2rem;
    }

    /* Orb */
    .voice-orb-container { position: relative; width: 200px; height: 200px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
    .voice-orb-ambient {
        position: absolute; inset: -8px; border-radius: 50%;
        background: conic-gradient(from 0deg, rgba(59, 130, 246, 0.15), rgba(16, 185, 129, 0.1), rgba(139, 92, 246, 0.1), rgba(59, 130, 246, 0.15));
        animation: orb-rotate 10s linear infinite; filter: blur(8px);
    }
    @keyframes orb-rotate { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }

    .voice-orb-ring { position: absolute; border-radius: 50%; border: 1px solid rgba(59, 130, 246, 0.15); display: none; }
    .voice-orb-ring-1 { inset: -16px; animation: orb-ping 3s cubic-bezier(0, 0, 0.2, 1) infinite; }
    .voice-orb-ring-2 { inset: -4px; animation: orb-ping 2s cubic-bezier(0, 0, 0.2, 1) infinite; border-color: rgba(16, 185, 129, 0.15); }
    @keyframes orb-ping { 0% { transform: scale(1); opacity: 1; } 75%, 100% { transform: scale(1.6); opacity: 0; } }

    .voice-orb-button {
        position: relative; z-index: 10; width: 140px; height: 140px; min-width: 140px; min-height: 140px;
        border-radius: 50%; display: flex; flex-direction: column; align-items: center; justify-content: center;
        border: 2px solid rgba(255, 255, 255, 0.15); cursor: pointer;
        background: radial-gradient(circle at 35% 35%, rgba(30, 41, 59, 0.9) 0%, rgba(15, 23, 42, 0.95) 100%);
        box-shadow: 0 0 40px rgba(59, 130, 246, 0.2), 0 0 80px rgba(59, 130, 246, 0.1), inset 0 1px 0 rgba(255, 255, 255, 0.1);
        transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1); outline: none;
    }
    .voice-orb-button:hover {
        transform: scale(1.05);
        box-shadow: 0 0 50px rgba(59, 130, 246, 0.35), 0 0 100px rgba(59, 130, 246, 0.15), inset 0 1px 0 rgba(255, 255, 255, 0.15);
        border-color: rgba(59, 130, 246, 0.3);
    }
    .voice-orb-button:active { transform: scale(0.95); }

    .voice-orb-inner-glow {
        position: absolute; inset: 8px; border-radius: 50%;
        background: radial-gradient(circle at 40% 35%, rgba(59, 130, 246, 0.12) 0%, transparent 70%);
        pointer-events: none;
    }
    .voice-orb-icon { font-size: 2.5rem; line-height: 1; transition: all 0.5s ease; filter: drop-shadow(0 0 8px rgba(59, 130, 246, 0.4)); }
    .voice-orb-label { font-size: 9px; font-weight: 900; letter-spacing: 0.2em; text-transform: uppercase; color: rgba(147, 197, 253, 0.8); margin-top: 6px; transition: all 0.3s ease; }

    /* Waveform */
    .voice-waveform { display: flex; align-items: center; justify-content: center; gap: 4px; margin: 1.5rem 0; height: 32px; }
    .wave-bar { width: 3px; background: linear-gradient(180deg, rgba(59, 130, 246, 0.8), rgba(16, 185, 129, 0.6)); border-radius: 4px; transition: all 0.15s ease; animation: none; }
    .wave-bar:nth-child(1) { height: 6px; } .wave-bar:nth-child(2) { height: 10px; } .wave-bar:nth-child(3) { height: 16px; }
    .wave-bar:nth-child(4) { height: 20px; } .wave-bar:nth-child(5) { height: 16px; } .wave-bar:nth-child(6) { height: 10px; } .wave-bar:nth-child(7) { height: 6px; }
    @keyframes wave-bounce { 0%, 100% { transform: scaleY(1); } 50% { transform: scaleY(2.5); } }

    /* Status */
    .voice-status-area { max-width: 28rem; margin-top: 0.5rem; }
    .voice-status-title { font-size: 1.25rem; font-weight: 800; color: #e2e8f0; letter-spacing: -0.02em; }
    .voice-status-sub { font-size: 12px; color: rgba(148, 163, 184, 0.8); font-weight: 500; line-height: 1.7; margin-top: 6px; }
    .voice-status-sub strong { color: rgba(147, 197, 253, 0.9); }

    /* Speech Warning */
    .voice-speech-warning {
        margin-top: 1rem; padding: 8px 16px;
        background: rgba(245, 158, 11, 0.1); border: 1px solid rgba(245, 158, 11, 0.2);
        border-radius: 12px; color: rgba(253, 224, 71, 0.9); font-size: 11px; font-weight: 600;
        display: flex; align-items: center; justify-content: center; gap: 6px;
    }

    /* Text Input */
    .voice-text-input-area { width: 100%; max-width: 420px; margin-top: 2rem; padding-top: 1.5rem; border-top: 1px solid rgba(255, 255, 255, 0.06); }
    .voice-text-input-label { display: block; font-size: 9px; font-weight: 800; letter-spacing: 0.15em; text-transform: uppercase; color: rgba(148, 163, 184, 0.5); margin-bottom: 8px; text-align: left; }
    .voice-text-input-wrapper { position: relative; width: 100%; }
    .voice-text-input {
        display: block; width: 100%; padding: 12px 52px 12px 16px;
        background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 14px; font-size: 13px; font-weight: 600; color: #e2e8f0; outline: none; transition: all 0.3s ease;
    }
    .voice-text-input::placeholder { color: rgba(148, 163, 184, 0.4); }
    .voice-text-input:focus { border-color: rgba(59, 130, 246, 0.4); box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1); background: rgba(255, 255, 255, 0.08); }
    .voice-text-input-btn {
        position: absolute; right: 6px; top: 50%; transform: translateY(-50%);
        width: 36px; height: 36px; display: flex; align-items: center; justify-content: center;
        background: linear-gradient(135deg, #3b82f6, #2563eb); border: none; border-radius: 10px;
        color: white; cursor: pointer; transition: all 0.2s ease; outline: none;
    }
    .voice-text-input-btn:hover { background: linear-gradient(135deg, #60a5fa, #3b82f6); transform: translateY(-50%) scale(1.05); }

    /* Devices Panel */
    .voice-devices-panel { background: white; border: 1px solid #e2e8f0; border-radius: 1.5rem; padding: 1.5rem; box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04); }
    .voice-devices-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.25rem; padding-bottom: 1rem; border-bottom: 1px solid #f1f5f9; }
    .voice-devices-header h2 { font-size: 13px; font-weight: 900; color: #1e293b; text-transform: uppercase; letter-spacing: 0.05em; }
    .voice-devices-count { font-size: 10px; font-weight: 700; color: #94a3b8; background: #f1f5f9; padding: 3px 10px; border-radius: 20px; }

    .voice-device-card { display: flex; align-items: center; justify-content: space-between; padding: 14px 16px; border-radius: 14px; border: 1px solid transparent; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
    .voice-device-card:hover { transform: translateY(-1px); box-shadow: 0 4px 12px rgba(0, 0, 0, 0.06); }
    .voice-device-card-on { background: linear-gradient(135deg, rgba(16, 185, 129, 0.06), rgba(16, 185, 129, 0.02)); border-color: rgba(16, 185, 129, 0.2); }
    .voice-device-card-off { background: #f8fafc; border-color: #e2e8f0; }

    .voice-device-icon { width: 40px; height: 40px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 18px; flex-shrink: 0; transition: all 0.3s ease; }
    .voice-device-icon-on { background: linear-gradient(135deg, #10b981, #059669); color: white; box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3); }
    .voice-device-icon-off { background: #e2e8f0; color: #94a3b8; }
    .voice-device-name { font-size: 12px; font-weight: 700; color: #1e293b; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .voice-device-category { font-size: 9px; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.1em; }

    /* Toggle Switch */
    .voice-toggle-switch { position: relative; width: 44px; height: 24px; border-radius: 24px; padding: 2px; cursor: pointer; border: none; outline: none; transition: background 0.3s ease; }
    .voice-toggle-on { background: linear-gradient(135deg, #10b981, #059669); }
    .voice-toggle-off { background: #cbd5e1; }
    .voice-toggle-knob { width: 20px; height: 20px; border-radius: 50%; background: white; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.15); transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
    .voice-toggle-knob-on { transform: translateX(20px); }
    .voice-toggle-knob-off { transform: translateX(0); }

    /* Console Panel */
    .voice-console-panel {
        background: #0f172a; border: 1px solid rgba(51, 65, 85, 0.5); border-radius: 1.5rem; padding: 1.5rem;
        font-family: 'JetBrains Mono', 'Fira Code', monospace; font-size: 11px; color: #94a3b8;
        display: flex; flex-direction: column; min-height: 380px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1), inset 0 1px 0 rgba(255, 255, 255, 0.02);
    }
    .voice-console-header { display: flex; align-items: center; justify-content: space-between; padding-bottom: 12px; margin-bottom: 12px; border-bottom: 1px solid rgba(51, 65, 85, 0.4); font-size: 10px; font-weight: 800; letter-spacing: 0.1em; text-transform: uppercase; color: #64748b; }
    .voice-console-dot { width: 6px; height: 6px; border-radius: 50%; background: #10b981; animation: pulse 2s ease-in-out infinite; box-shadow: 0 0 8px rgba(16, 185, 129, 0.4); }
    @keyframes pulse { 0%, 100% { opacity: 1; } 50% { opacity: 0.4; } }
    .voice-console-logs { flex: 1; overflow-y: auto; padding-right: 4px; max-height: 260px; }
    .voice-console-logs > div { padding: 3px 0; line-height: 1.6; }
    .voice-console-footer { display: flex; align-items: center; justify-content: space-between; padding-top: 12px; margin-top: 12px; border-top: 1px solid rgba(51, 65, 85, 0.4); font-size: 10px; font-weight: 700; color: #475569; }
    .voice-console-clear-btn { background: none; border: none; color: #475569; font-size: 10px; font-weight: 700; text-transform: uppercase; cursor: pointer; transition: color 0.2s; outline: none; }
    .voice-console-clear-btn:hover { color: #94a3b8; }

    @media (max-width: 768px) {
        .voice-hero-section { margin: 0 0.5rem; border-radius: 0 0 1.5rem 1.5rem; }
        .voice-orb-container { width: 160px; height: 160px; }
        .voice-orb-button { width: 110px; height: 110px; min-width: 110px; min-height: 110px; }
        .voice-orb-icon { font-size: 2rem; }
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
            orb.style.background = 'radial-gradient(circle at 35% 35%, rgba(6, 78, 59, 0.9) 0%, rgba(4, 47, 46, 0.95) 100%)';
            orb.style.borderColor = 'rgba(16, 185, 129, 0.4)';
            orb.style.boxShadow = '0 0 40px rgba(16, 185, 129, 0.35), 0 0 80px rgba(16, 185, 129, 0.15), inset 0 1px 0 rgba(255, 255, 255, 0.1)';
            icon.innerText = '🟢';
            label.innerText = 'LISTENING...';
            label.className = 'voice-orb-label';
            label.style.color = 'rgba(110, 231, 183, 0.9)';
            p1.style.display = 'block';
            p2.style.display = 'block';
            statusTitle.innerText = 'JASMIN Mendengarkan...';
        } else {
            orb.style.background = 'radial-gradient(circle at 35% 35%, rgba(30, 41, 59, 0.9) 0%, rgba(15, 23, 42, 0.95) 100%)';
            orb.style.borderColor = 'rgba(255, 255, 255, 0.15)';
            orb.style.boxShadow = '0 0 40px rgba(59, 130, 246, 0.2), 0 0 80px rgba(59, 130, 246, 0.1), inset 0 1px 0 rgba(255, 255, 255, 0.1)';
            icon.innerText = '🎙️';
            label.innerText = 'TAP TO TALK';
            label.className = 'voice-orb-label';
            label.style.color = 'rgba(147, 197, 253, 0.8)';
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
            card.className = "voice-device-card voice-device-card-on";
            iconDiv.className = "voice-device-icon voice-device-icon-on";
            button.className = "voice-toggle-switch flex-shrink-0 voice-toggle-on";
            switchCircle.className = "voice-toggle-knob voice-toggle-knob-on";
        } else {
            card.className = "voice-device-card voice-device-card-off";
            iconDiv.className = "voice-device-icon voice-device-icon-off";
            button.className = "voice-toggle-switch flex-shrink-0 voice-toggle-off";
            switchCircle.className = "voice-toggle-knob voice-toggle-knob-off";
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
