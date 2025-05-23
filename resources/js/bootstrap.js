import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// Import Echo and Pusher
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

// Initialize Laravel Echo for real-time events only if Pusher is properly configured
try {
    const pusherKey = import.meta.env.VITE_PUSHER_APP_KEY;
    
    // Only initialize Echo if we have a Pusher key
    if (pusherKey && pusherKey !== 'your_pusher_app_key') {
        window.Echo = new Echo({
            broadcaster: 'pusher',
            key: pusherKey,
            cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER,
            wsHost: import.meta.env.VITE_PUSHER_APP_HOST,
            wsPort: import.meta.env.VITE_PUSHER_APP_PORT,
            wssPort: import.meta.env.VITE_PUSHER_APP_PORT,
            forceTLS: import.meta.env.VITE_PUSHER_APP_SCHEME === 'https',
            enabledTransports: ['ws', 'wss'],
            disableStats: true,
            authEndpoint: '/broadcasting/auth',
            auth: {
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                }
            }
        });
        
        // Log that Echo is initialized
        console.log('Laravel Echo initialized with Pusher');
    } else {
        console.log('Pusher key not configured, Echo initialization skipped');
        
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
