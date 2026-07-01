<!-- Open Source AI Chatbot Widget (Deep Chat) -->
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

        <!-- Deep Chat Open Source Component Container -->
        <div class="flex-1 overflow-hidden rounded-b-2xl">
            <deep-chat
                id="chat-element"
                request='{"url": "/chatbot/chat", "method": "POST"}'
                introMessage='{"text": "Halo! Saya YukAnalisaListrikmu, asisten energi pintar Jamkrida Jateng. Ada yang bisa saya bantu hari ini?"}'
                style="width: 100%; height: 100%; border: none; border-bottom-left-radius: 1rem; border-bottom-right-radius: 1rem; font-family: Inter, ui-sans-serif, system-ui, sans-serif;"
                textInput='{"placeholder": {"text": "Tanya seputar listrik..."}}'
                messageStyles='{"default": {"shared": {"bubble": {"fontSize": "12px", "lineHeight": "1.6"}}}}'
                suggestions='[{"text": "Tips Hemat Listrik"}, {"text": "Cara Baca Grafik"}, {"text": "Estimasi Tarif PLN"}]'
            ></deep-chat>
        </div>
    </div>
</div>

<!-- Deep Chat Open-Source JavaScript Bundle via CDN -->
<script type="module" src="https://unpkg.com/deep-chat@2.4.2/dist/deepChat.bundle.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const toggleBtn = document.getElementById('chatbot-toggle-btn');
        const closeBtn = document.getElementById('chatbot-close-btn');
        const chatWindow = document.getElementById('chatbot-window');
        const chatElement = document.getElementById('chat-element');

        // Toggle chat window open/close
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

        // Intercept Deep Chat request to match our Laravel Controller format
        if (chatElement) {
            chatElement.requestInterceptor = (requestDetails) => {
                try {
                    const body = JSON.parse(requestDetails.body);
                    const userMessage = body.messages[body.messages.length - 1].text;
                    requestDetails.body = JSON.stringify({
                        message: userMessage
                    });
                } catch (e) {
                    console.error("Chatbot request interceptor error:", e);
                }
                return requestDetails;
            };
        }
    });
</script>
