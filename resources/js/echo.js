import Echo from 'laravel-echo';

import Pusher from 'pusher-js';
window.Pusher = Pusher;

const reverbHost = import.meta.env.VITE_REVERB_HOST;
const useConfiguredEnv = (reverbHost && reverbHost !== 'localhost');

const wsHost = useConfiguredEnv ? reverbHost : window.location.hostname;
const wsPort = useConfiguredEnv ? (import.meta.env.VITE_REVERB_PORT ?? 8085) : (window.location.port || 80);
const wssPort = useConfiguredEnv ? (import.meta.env.VITE_REVERB_PORT ?? 8085) : (window.location.port || 443);
const forceTLS = useConfiguredEnv ? ((import.meta.env.VITE_REVERB_SCHEME ?? 'http') === 'https') : (window.location.protocol === 'https:');

window.Echo = new Echo({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: wsHost,
    wsPort: wsPort,
    wssPort: wssPort,
    forceTLS: forceTLS,
    enabledTransports: ['ws', 'wss'],
});
