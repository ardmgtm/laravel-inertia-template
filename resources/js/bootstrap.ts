import axios from 'axios';
import Echo, { Broadcaster } from 'laravel-echo';
import Pusher from 'pusher-js';

declare global {
    interface Window {
        Echo: Echo<keyof Broadcaster>;
        Pusher: typeof Pusher;
    }
}

window.axios = axios;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

window.Pusher = Pusher;
window.Echo = new Echo({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: import.meta.env.VITE_REVERB_HOST ?? 'localhost',
    wsPort: import.meta.env.VITE_REVERB_PORT ?? 80,
    wssPort: import.meta.env.VITE_REVERB_PORT ?? 443,
    forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'https') === 'https',
    enabledTransports: ['ws', 'wss'],
    authorizer: (channel: any) => {
        return {
            authorize: (socketId: string, callback: Function) => {
                console.log('🔐 Authorizing channel:', channel.name, 'with socket:', socketId);
                axios.post('/broadcasting/auth', {
                    socket_id: socketId,
                    channel_name: channel.name
                })
                .then(response => {
                    console.log('✅ Channel authorized:', channel.name);
                    callback(null, response.data);
                })
                .catch(error => {
                    console.error('❌ Broadcasting auth error:', error.response?.data || error.message);
                    callback(error);
                });
            }
        };
    },
});
