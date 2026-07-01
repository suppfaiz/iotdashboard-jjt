<!-- Open Source Chatbot Widget -->
<link href="https://cdn.jsdelivr.net/npm/@luccaallen/chatbot-widget/dist/style.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/@luccaallen/chatbot-widget/dist/chatbot-widget.bundle.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        ChatbotWidget.init({
            webhookUrl: '/chatbot/chat',
            title: 'YukAnalisaListrikmu',
            welcomeMessage: 'Halo! Saya YukAnalisaListrikmu, asisten energi pintar Jamkrida Jateng. Ada yang bisa saya bantu?',
            theme: {
                primaryColor: '#2563eb', // Blue-600 to match the dashboard
            }
        });
    });
</script>
