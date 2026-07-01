<!-- Chatbot YukAnalisaListrikmu -->
<div class="chatbot-container">
    <!-- Floating Chat Button -->
    <button id="chatbot-toggle-btn" class="fixed bottom-6 right-6 z-50 flex items-center gap-2.5 px-4 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 text-white rounded-full shadow-lg hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1 group">
        <span class="relative flex h-3 w-3">
            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
            <span class="relative inline-flex rounded-full h-3 w-3 bg-emerald-500"></span>
        </span>
        <svg class="w-5 h-5 group-hover:rotate-12 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
        </svg>
        <span class="text-xs font-bold tracking-wide">YukAnalisaListrikmu</span>
    </button>

    <!-- Chat Window -->
    <div id="chatbot-window" class="fixed bottom-24 right-6 w-96 h-[520px] z-50 bg-white/95 backdrop-blur-md rounded-2xl shadow-2xl border border-slate-100 flex flex-col hidden transition-all duration-300 opacity-0 translate-y-4">
        <!-- Header -->
        <div class="p-4 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-t-2xl flex justify-between items-center shadow-md">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-white/10 flex items-center justify-center font-bold text-lg text-white">⚡</div>
                <div>
                    <h4 class="text-sm font-extrabold tracking-wide">YukAnalisaListrikmu</h4>
                    <p class="text-[10px] text-blue-100 font-medium">Asisten Energi Pintar Jamkrida</p>
                </div>
            </div>
            <button id="chatbot-close-btn" class="text-white/80 hover:text-white transition-colors p-1 hover:bg-white/10 rounded-lg">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <!-- Messages Area -->
        <div id="chatbot-messages" class="flex-1 p-4 space-y-3.5 overflow-y-auto bg-slate-50/50">
            <!-- Bot Welcome Message -->
            <div class="flex gap-2.5 max-w-[85%]">
                <div class="w-8 h-8 rounded-lg bg-blue-100 flex items-center justify-center flex-shrink-0 text-sm">🤖</div>
                <div class="bg-white px-3.5 py-2.5 rounded-2xl rounded-tl-none shadow-sm border border-slate-100">
                    <p class="text-xs text-slate-700 leading-relaxed">Halo! Saya <b>YukAnalisaListrikmu</b>, asisten pintar untuk menganalisis penggunaan listrik Anda. Ada yang bisa saya bantu hari ini?</p>
                    <!-- Quick Reply suggestions -->
                    <div class="mt-3 flex flex-wrap gap-1.5">
                        <button onclick="triggerAnalysis()" class="px-2.5 py-1.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-600 text-[10px] font-bold rounded-lg border border-indigo-100 transition-colors">🔍 Analisis Real-Time</button>
                        <button onclick="sendQuickReply('💡 Tips Hemat Listrik')" class="px-2.5 py-1.5 bg-blue-50 hover:bg-blue-100 text-blue-600 text-[10px] font-bold rounded-lg border border-blue-100 transition-colors">💡 Tips Hemat</button>
                        <button onclick="sendQuickReply('📊 Cara Baca Grafik')" class="px-2.5 py-1.5 bg-blue-50 hover:bg-blue-100 text-blue-600 text-[10px] font-bold rounded-lg border border-blue-100 transition-colors">📊 Baca Grafik</button>
                        <button onclick="sendQuickReply('🔋 Estimasi Tarif PLN')" class="px-2.5 py-1.5 bg-blue-50 hover:bg-blue-100 text-blue-600 text-[10px] font-bold rounded-lg border border-blue-100 transition-colors">🔋 Tarif PLN</button>
                        <button onclick="sendQuickReply('⚠️ Notifikasi Telegram')" class="px-2.5 py-1.5 bg-blue-50 hover:bg-blue-100 text-blue-600 text-[10px] font-bold rounded-lg border border-blue-100 transition-colors">⚠️ Telegram Alert</button>
                        @php
                            $elWa = \App\Models\SystemConfig::where('key', 'electrician_whatsapp')->value('value');
                        @endphp
                        @if(!empty($elWa))
                            <a href="https://wa.me/{{ $elWa }}?text=Halo%20Bapak%2FIbu%2C%20kami%20ingin%20melaporkan%20adanya%20masalah%20kelistrikan%20pada%20sistem%20pemantauan%20daya%20IoT%20Jamkrida%20Jateng.%20Mohon%20bantuannya%20untuk%20memeriksa.%20Terima%20kasih." target="_blank" class="px-2.5 py-1.5 bg-emerald-50 hover:bg-emerald-100 text-emerald-600 text-[10px] font-bold rounded-lg border border-emerald-100 transition-colors inline-flex items-center gap-1">📞 Hubungi Teknisi</a>
                        @else
                            <button onclick="alert('Nomor WhatsApp tukang listrik belum diatur oleh Administrator di menu Settings.')" class="px-2.5 py-1.5 bg-emerald-50 hover:bg-emerald-100 text-emerald-600 text-[10px] font-bold rounded-lg border border-emerald-100 transition-colors inline-flex items-center gap-1">📞 Hubungi Teknisi</button>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Input Area -->
        <div class="p-3 bg-white border-t border-slate-100 flex gap-2 rounded-b-2xl">
            <input type="text" id="chatbot-input" placeholder="Tanya seputar listrik..." class="flex-1 px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:outline-none focus:border-blue-500 focus:bg-white transition-all" />
            <button id="chatbot-send-btn" class="px-3 py-2 bg-blue-600 hover:bg-blue-500 text-white rounded-xl text-xs font-bold transition-colors shadow-sm">Kirim</button>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const electricianWhatsapp = "{{ \App\Models\SystemConfig::where('key', 'electrician_whatsapp')->value('value') ?? '' }}";
        @php
            $dbKey = \App\Models\SystemConfig::where('key', 'gemini_api_key')->value('value');
            $envKey = config('services.gemini.key');
            $hasKey = (!empty(trim($dbKey ?? '')) || !empty(trim($envKey ?? '')));
        @endphp
        const isAiEnabled = {{ $hasKey ? 'true' : 'false' }};
        const toggleBtn = document.getElementById('chatbot-toggle-btn');
        const closeBtn = document.getElementById('chatbot-close-btn');
        const chatWindow = document.getElementById('chatbot-window');
        const chatInput = document.getElementById('chatbot-input');
        const sendBtn = document.getElementById('chatbot-send-btn');
        const messagesArea = document.getElementById('chatbot-messages');

        // Open/Close toggle
        toggleBtn.addEventListener('click', function () {
            if (chatWindow.classList.contains('hidden')) {
                chatWindow.classList.remove('hidden');
                setTimeout(() => {
                    chatWindow.classList.remove('opacity-0', 'translate-y-4');
                }, 50);
            } else {
                closeChat();
            }
        });

        closeBtn.addEventListener('click', closeChat);

        function closeChat() {
            chatWindow.classList.add('opacity-0', 'translate-y-4');
            setTimeout(() => {
                chatWindow.classList.add('hidden');
            }, 300);
        }

        // Send message
        sendBtn.addEventListener('click', handleSend);
        chatInput.addEventListener('keypress', function (e) {
            if (e.key === 'Enter') handleSend();
        });

        function handleSend() {
            const text = chatInput.value.trim();
            if (!text) return;

            // Append user message
            appendMessage('user', text);
            chatInput.value = '';

            // Show typing indicator
            const typingId = showTypingIndicator();

            if (isAiEnabled) {
                fetch('/chatbot/chat', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ message: text })
                })
                .then(res => res.json())
                .then(data => {
                    removeTypingIndicator(typingId);
                    if (data.status === 'success') {
                        appendMessage('bot', data.reply);
                    } else {
                        // Fallback to static response
                        const response = getBotResponse(text);
                        appendMessage('bot', response);
                    }
                })
                .catch(err => {
                    removeTypingIndicator(typingId);
                    const response = getBotResponse(text);
                    appendMessage('bot', response);
                });
            } else {
                const textLower = text.toLowerCase();
                if (textLower.includes('analis') || textLower.includes('prediksi') || textLower.includes('ramal') || textLower.includes('forecast') || textLower.includes('cek')) {
                    fetch('/chatbot/analysis')
                        .then(res => res.json())
                        .then(data => {
                            removeTypingIndicator(typingId);
                            if (data.status === 'success') {
                                appendMessage('bot', data.analysis);
                            } else {
                                appendMessage('bot', 'Maaf, saya gagal mengambil data analisis saat ini.');
                            }
                        })
                        .catch(err => {
                            removeTypingIndicator(typingId);
                            appendMessage('bot', 'Maaf, terjadi kesalahan koneksi saat mengambil data analisis.');
                        });
                } else {
                    // Get bot response
                    setTimeout(() => {
                        removeTypingIndicator(typingId);
                        const response = getBotResponse(text);
                        appendMessage('bot', response);
                    }, 1000);
                }
            }
        }

        window.sendQuickReply = function (text) {
            appendMessage('user', text);
            const typingId = showTypingIndicator();
            
            if (isAiEnabled) {
                fetch('/chatbot/chat', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ message: text })
                })
                .then(res => res.json())
                .then(data => {
                    removeTypingIndicator(typingId);
                    if (data.status === 'success') {
                        appendMessage('bot', data.reply);
                    } else {
                        const response = getBotResponse(text);
                        appendMessage('bot', response);
                    }
                })
                .catch(err => {
                    removeTypingIndicator(typingId);
                    const response = getBotResponse(text);
                    appendMessage('bot', response);
                });
            } else {
                const textLower = text.toLowerCase();
                if (textLower.includes('analis') || textLower.includes('prediksi') || textLower.includes('ramal') || textLower.includes('forecast') || textLower.includes('cek')) {
                    fetch('/chatbot/analysis')
                        .then(res => res.json())
                        .then(data => {
                            removeTypingIndicator(typingId);
                            if (data.status === 'success') {
                                appendMessage('bot', data.analysis);
                            } else {
                                appendMessage('bot', 'Maaf, saya gagal mengambil data analisis saat ini.');
                            }
                        })
                        .catch(err => {
                            removeTypingIndicator(typingId);
                            appendMessage('bot', 'Maaf, terjadi kesalahan koneksi saat mengambil data analisis.');
                        });
                } else {
                    setTimeout(() => {
                        removeTypingIndicator(typingId);
                        const response = getBotResponse(text);
                        appendMessage('bot', response);
                    }, 1000);
                }
            }
        };

        window.triggerAnalysis = function() {
            window.sendQuickReply('Tolong buatkan analisis penggunaan listrik saat ini');
        };

        function appendMessage(sender, text) {
            const msgDiv = document.createElement('div');
            if (sender === 'user') {
                msgDiv.className = 'flex justify-end max-w-[85%] ml-auto';
                msgDiv.innerHTML = `
                    <div class="bg-gradient-to-r from-blue-600 to-indigo-600 text-white px-3.5 py-2.5 rounded-2xl rounded-tr-none shadow-sm text-xs leading-relaxed">
                        ${text}
                    </div>
                `;
            } else {
                msgDiv.className = 'flex gap-2.5 max-w-[85%]';
                msgDiv.innerHTML = `
                    <div class="w-8 h-8 rounded-lg bg-blue-100 flex items-center justify-center flex-shrink-0 text-sm">🤖</div>
                    <div class="bg-white px-3.5 py-2.5 rounded-2xl rounded-tl-none shadow-sm border border-slate-100 text-xs text-slate-700 leading-relaxed">
                        ${text}
                    </div>
                `;
            }
            messagesArea.appendChild(msgDiv);
            messagesArea.scrollTop = messagesArea.scrollHeight;
        }

        function showTypingIndicator() {
            const typingDiv = document.createElement('div');
            typingDiv.id = 'typing-indicator';
            typingDiv.className = 'flex gap-2.5 max-w-[85%]';
            typingDiv.innerHTML = `
                <div class="w-8 h-8 rounded-lg bg-blue-100 flex items-center justify-center flex-shrink-0 text-sm">🤖</div>
                <div class="bg-white px-3.5 py-2.5 rounded-2xl rounded-tl-none shadow-sm border border-slate-100 text-xs text-slate-400 flex items-center gap-1">
                    <span class="animate-bounce" style="animation-delay: 0ms">.</span>
                    <span class="animate-bounce" style="animation-delay: 150ms">.</span>
                    <span class="animate-bounce" style="animation-delay: 300ms">.</span>
                    <span>mengetik...</span>
                </div>
            `;
            messagesArea.appendChild(typingDiv);
            messagesArea.scrollTop = messagesArea.scrollHeight;
            return typingDiv;
        }

        function removeTypingIndicator(el) {
            if (el && el.parentNode) {
                el.parentNode.removeChild(el);
            }
        }

        function getBotResponse(input) {
            const text = input.toLowerCase();

            if (text.includes('hubungi') || text.includes('tukang') || text.includes('teknisi') || text.includes('listrik')) {
                if (electricianWhatsapp) {
                    return `📞 <b>Hubungi Tukang Listrik:</b><br><br>
                        Terjadi masalah listrik atau alarm menyala? Anda dapat langsung mengirimkan chat WhatsApp ke teknisi listrik resmi:<br><br>
                        👉 <a href="https://wa.me/${electricianWhatsapp}?text=Halo%20Bapak%2FIbu%2C%20kami%20ingin%20melaporkan%20adanya%20masalah%20kelistrikan%20pada%20sistem%20pemantauan%20daya%20IoT%20Jamkrida%20Jateng.%20Mohon%20bantuannya%20untuk%20memeriksa.%20Terima%20kasih." target="_blank" class="inline-block px-3 py-1.5 bg-emerald-600 hover:bg-emerald-500 text-white rounded-lg font-bold text-xs shadow-sm transition-colors">Hubungi via WhatsApp</a>`;
                } else {
                    return `📞 Nomor kontak tukang listrik belum dikonfigurasi oleh Administrator di menu Settings.`;
                }
            }

            if (text.includes('tips') || text.includes('hemat')) {
                return `💡 <b>Berikut Tips Praktis Menghemat Listrik Anda:</b><br><br>
                    1. <b>Matikan Beban Standby</b>: Cabut colokan TV, komputer, atau charger HP yang tidak dipakai. Beban standby berkontribusi hingga 10% tagihan bulanan.<br>
                    2. <b>Gunakan LED Berkualitas</b>: Ganti lampu pijar Anda dengan LED. Lampu LED menggunakan daya 80% lebih sedikit untuk tingkat kecerahan yang sama.<br>
                    3. <b>Atur Limit Alarm Anggaran</b>: Gunakan fitur <b>Monthly Cost/Kwh Budget</b> di menu Settings pada dashboard ini untuk memantau konsumsi agar tidak melebihi anggaran bulanan Anda.`;
            }

            if (text.includes('grafik') || text.includes('baca')) {
                return `📊 <b>Cara Membaca Grafik Sensor Dashboard:</b><br><br>
                    * <b>Grafik Voltase (V)</b>: Memantau kestabilan tegangan listrik. Normalnya berkisar di <b>220V</b>. Jika turun di bawah 200V atau di atas 240V, instalasi Anda berisiko merusak peralatan elektronik.<br>
                    * <b>Grafik Arus (A)</b>: Menampilkan besarnya arus listrik yang mengalir ke beban Anda.<br>
                    * <b>Grafik Daya (W)</b>: Menunjukkan daya aktif nyata yang sedang disedot alat listrik Anda saat ini (V x A).<br>
                    * <b>Grafik Energi (kWh)</b>: Menampilkan akumulasi total pemakaian listrik harian Anda.`;
            }

            if (text.includes('tarif') || text.includes('pln') || text.includes('biaya') || text.includes('wbp')) {
                return `🔋 <b>Estimasi Tarif PLN (Time of Use - ToU):</b><br><br>
                    Sistem di dashboard ini menghitung estimasi biaya harian Anda berdasarkan dua tarif:<br>
                    * <b>WBP (Waktu Beban Puncak)</b>: Berlaku pukul <b>17:00 - 22:00</b> dengan tarif lebih tinggi (misal Rp2.000/kWh) karena beban puncak jaringan listrik.<br>
                    * <b>LWBP (Luar Waktu Beban Puncak)</b>: Berlaku pukul <b>22:00 - 17:00</b> dengan tarif standar (misal Rp1.444,70/kWh).<br><br>
                    Anda bisa mengubah nilai tarif ini kapan saja di menu <b>Settings</b>.`;
            }

            if (text.includes('telegram') || text.includes('notif') || text.includes('mati') || text.includes('offline')) {
                return `⚠️ <b>Fitur Notifikasi Telegram Alert:</b><br><br>
                    * Bot akan otomatis mengirimkan chat ke Telegram Anda jika voltase berada di luar batas aman (di bawah 200V / di atas 240V).<br>
                    * Jika alat sensor ESP32 terputus (mati listrik atau Wi-Fi mati) selama <b>5 menit</b>, Anda akan mendapat chat peringatan <b>DEVICE OFFLINE</b>.<br>
                    * Ketika alat menyala lagi, bot mengirimkan chat pemulihan <b>DEVICE ONLINE RECOVERY</b>.`;
            }

            return `🤖 <b>Halo! Saya YukAnalisaListrikmu.</b><br><br>
                Ada yang bisa saya bantu tentang pemantauan energi Anda?<br>
                Silakan tanyakan hal berikut:<br>
                * 💡 <i>"Tips hemat listrik"</i><br>
                * 📊 <i>"Cara membaca grafik"</i><br>
                * 🔋 <i>"Bagaimana tarif PLN WBP/LWBP dihitung?"</i><br>
                * ⚠️ <i>"Bagaimana cara kerja notifikasi Telegram?"</i>`;
        }
    });
</script>
