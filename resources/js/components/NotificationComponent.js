// NotificationComponent.js - A standalone component for handling real-time notifications
export default class NotificationComponent {
    constructor() {
        this.initialized = false;
        this.isEchoConnected = false;
    }
    
    init() {
        if (this.initialized) return;
        this.initialized = true;
        
        console.log('NotificationComponent initialized');
        this.setupEchoListeners();
    }
    
    setupEchoListeners() {
        if (typeof window.Echo === 'undefined') {
            console.warn('Echo not available, using polling fallback');
            return;
        }
        
        const userId = document.querySelector('meta[name="user-id"]')?.getAttribute('content');
        if (!userId) {
            console.warn('User ID not found, cannot setup Echo listeners');
            return;
        }
        
        try {
            // Setup Pusher connection monitoring
            if (window.Echo.connector && window.Echo.connector.pusher) {
                window.Echo.connector.pusher.connection.bind('connected', () => {
                    this.isEchoConnected = true;
                    console.log('Pusher connected');
                });
                
                window.Echo.connector.pusher.connection.bind('error', (err) => {
                    this.isEchoConnected = false;
                    console.error('Pusher connection error:', err);
                });
            }
            
            // Listen for notifications
            window.Echo.private(`notifications.${userId}`)
                .listen('.notification.created', (data) => {
                    console.log('New notification received:', data);
                    
                    // Notify the Alpine component
                    this.dispatchNotificationEvents({
                        id: data.id,
                        message: data.message,
                        link: data.link,
                        created_at: data.created_at
                    }, true);
                    
                    // Show browser notification
                    this.showBrowserNotification(data);
                });
                
            console.log('Echo listeners setup complete');
        } catch (error) {
            console.error('Error setting up Echo listeners:', error);
        }
    }
    
    dispatchNotificationEvents(notification, isNew = false) {
        // Fetch latest notifications to ensure we have fresh data
        fetch('/notifications/unread', {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            // Check if the response is actually JSON
            const contentType = response.headers.get('content-type');
            if (contentType && contentType.includes('application/json')) {
                return response.json();
            } else {
                throw new Error('Response was not JSON');
            }
        })
        .then(data => {
            // Update count
            window.dispatchEvent(new CustomEvent('notification-count-updated', {
                detail: {
                    count: data.count || 0,
                    autoOpen: isNew
                }
            }));
            
            // Update notifications list
            window.dispatchEvent(new CustomEvent('notifications-updated', {
                detail: {
                    notifications: data.notifications || []
                }
            }));
            
            // Dispatch specific new notification event
            if (isNew) {
                window.dispatchEvent(new CustomEvent('new-notification-received', {
                    detail: { notification }
                }));
            }
        })
        .catch(error => {
            console.error('Error fetching notifications:', error);
            
            // Even on error, still dispatch the new notification event
            // so UI can show at least this notification
            if (isNew && notification) {
                // First update count - incrementing current count
                const currentCount = parseInt(document.querySelector('.notification-badge')?.textContent || '0');
                window.dispatchEvent(new CustomEvent('notification-count-updated', {
                    detail: {
                        count: currentCount + 1,
                        autoOpen: isNew
                    }
                }));
                
                // Then directly add this notification to the list
                window.dispatchEvent(new CustomEvent('new-notification-received', {
                    detail: { notification }
                }));
            }
        });
    }
    
    showBrowserNotification(notification) {
        if ('Notification' in window && Notification.permission === 'granted') {
            new Notification('Nouvelle notification', {
                body: notification.message,
                icon: '/favicon.ico'
            });
        } else if ('Notification' in window && Notification.permission !== 'denied') {
            Notification.requestPermission();
        }
    }
} 