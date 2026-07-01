<!-- Self-Contained AI Chatbot Widget (YukAnalisaListrikmu) -->
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
        <div class="p-4 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-t-2xl flex justify-between items-center shadow-md flex-shrink-0">
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
                    <p class="text-xs text-slate-700 leading-relaxed">Halo! Saya <b>YukAnalisaListrikmu</b>, asisten pintar pemantauan energi listrik Jamkrida Jateng. Ada yang bisa saya bantu hari ini?</p>
                    
                    <!-- Quick suggestions -->
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
        <div class="p-3 bg-white border-t border-slate-100 flex gap-2 rounded-b-2xl flex-shrink-0">
            <input type="text" id="chatbot-input" placeholder="Tanya seputar listrik..." class="flex-1 px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:outline-none focus:border-blue-500 focus:bg-white transition-all" />
            <button id="chatbot-send-btn" class="px-3 py-2 bg-blue-600 hover:bg-blue-500 text-white rounded-xl text-xs font-bold transition-colors shadow-sm">Kirim</button>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const toggleBtn = document.getElementById('chatbot-toggle-btn');
        const closeBtn = document.getElementById('chatbot-close-btn');
        const chatWindow = document.getElementById('chatbot-window');
        const chatInput = document.getElementById('chatbot-input');
        const sendBtn = document.getElementById('chatbot-send-btn');
        const messagesArea = document.getElementById('chatbot-messages');

        // Toggle open/close
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

        // Send messages
        sendBtn.addEventListener('click', handleSend);
        chatInput.addEventListener('keypress', function (e) {
            if (e.key === 'Enter') handleSend();
        });

        function handleSend() {
            const text = chatInput.value.trim();
            if (!text) return;

            appendMessage('user', text);
            chatInput.value = '';

            const typingId = showTypingIndicator();

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
                appendMessage('bot', data.text || data.reply || 'Maaf, saya tidak dapat memproses pesan Anda.');
            })
            .catch(err => {
                removeTypingIndicator(typingId);
                appendMessage('bot', 'Terjadi kesalahan koneksi. Silakan coba kembali.');
            });
        }

        window.sendQuickReply = function (text) {
            appendMessage('user', text);
            const typingId = showTypingIndicator();
            
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
                appendMessage('bot', data.text || data.reply || 'Maaf, saya tidak dapat memproses pesan Anda.');
            })
            .catch(err => {
                removeTypingIndicator(typingId);
                appendMessage('bot', 'Terjadi kesalahan koneksi. Silakan coba kembali.');
            });
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
    });
</script>
