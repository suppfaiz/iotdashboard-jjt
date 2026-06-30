import Echo from 'laravel-echo';

import Pusher from 'pusher-js';
window.Pusher = Pusher;

const reverbHost = import.meta.env.VITE_REVERB_HOST;
const isLocalHostServe = (window.location.port === '8000'); // php artisan serve default

const wsHost = isLocalHostServe ? (reverbHost || '127.0.0.1') : window.location.hostname;
const wsPort = isLocalHostServe ? (import.meta.env.VITE_REVERB_PORT ?? 8085) : (window.location.port || 80);
const wssPort = isLocalHostServe ? (import.meta.env.VITE_REVERB_PORT ?? 8085) : (window.location.port || 443);
const forceTLS = isLocalHostServe ? ((import.meta.env.VITE_REVERB_SCHEME ?? 'http') === 'https') : (window.location.protocol === 'https:');

window.Echo = new Echo({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: wsHost,
    wsPort: wsPort,
    wssPort: wssPort,
    forceTLS: forceTLS,
    enabledTransports: ['ws', 'wss'],
});
