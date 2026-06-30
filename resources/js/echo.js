import Echo from 'laravel-echo';

import Pusher from 'pusher-js';
window.Pusher = Pusher;

const reverbHost = import.meta.env.VITE_REVERB_HOST;
const isLocal = (reverbHost === 'localhost' || !reverbHost);

const wsHost = isLocal ? window.location.hostname : reverbHost;
const wsPort = isLocal ? (window.location.port || 80) : (import.meta.env.VITE_REVERB_PORT ?? 80);
const wssPort = isLocal ? (window.location.port || 443) : (import.meta.env.VITE_REVERB_PORT ?? 443);
const forceTLS = isLocal ? (window.location.protocol === 'https:') : ((import.meta.env.VITE_REVERB_SCHEME ?? 'https') === 'https');

window.Echo = new Echo({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: wsHost,
    wsPort: wsPort,
    wssPort: wssPort,
    forceTLS: forceTLS,
    enabledTransports: ['ws', 'wss'],
});
