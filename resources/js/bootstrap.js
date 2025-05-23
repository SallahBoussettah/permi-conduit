import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// Import Echo and Pusher
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

// Add debug logging for environment variables
console.log('Pusher Environment Variables:');
console.log('VITE_PUSHER_APP_KEY:', import.meta.env.VITE_PUSHER_APP_KEY);
console.log('VITE_PUSHER_APP_CLUSTER:', import.meta.env.VITE_PUSHER_APP_CLUSTER);
console.log('VITE_PUSHER_APP_HOST:', import.meta.env.VITE_PUSHER_APP_HOST);
console.log('VITE_PUSHER_APP_PORT:', import.meta.env.VITE_PUSHER_APP_PORT);
console.log('VITE_PUSHER_APP_SCHEME:', import.meta.env.VITE_PUSHER_APP_SCHEME);

// Initialize Laravel Echo for real-time events only if Pusher is properly configured
try {
    // Hard-coded key for testing - this ensures Pusher will be initialized
    const pusherKey = '183f5986f6b077841475';
    
    // Always initialize Echo with the Pusher key
    console.log('Initializing Echo with pusher key:', pusherKey);
    
    window.Echo = new Echo({
        broadcaster: 'pusher',
        key: pusherKey,
        cluster: 'eu',
        wsHost: import.meta.env.VITE_PUSHER_APP_HOST || undefined,
        wsPort: import.meta.env.VITE_PUSHER_APP_PORT || undefined,
        wssPort: import.meta.env.VITE_PUSHER_APP_PORT || undefined,
        forceTLS: true,
        enabledTransports: ['ws', 'wss'],
        disableStats: true,
        authEndpoint: '/broadcasting/auth',
        auth: {
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            }
        }
    });
    
    // Add Pusher connection status logging
    if (window.Echo.connector && window.Echo.connector.pusher) {
        window.Echo.connector.pusher.connection.bind('connected', () => {
            console.log('✅ Pusher connected successfully');
        });
        
        window.Echo.connector.pusher.connection.bind('error', (err) => {
            console.error('❌ Pusher connection error:', err);
        });
    }
    
    // Log that Echo is initialized
    console.log('Laravel Echo initialized with Pusher');
} catch (error) {
    console.error('Failed to initialize Laravel Echo:', error);
    
    // Create a dummy Echo object that doesn't do anything
    window.Echo = {
        private: () => ({
            listen: () => {}
        }),
        channel: () => ({
            listen: () => {}
        })
    };
}
