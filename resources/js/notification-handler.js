// Notification Handler
export default class NotificationHandler {
    constructor() {
        this.unreadNotifications = [];
        this.unreadCount = 0;
        this.notificationContainer = document.getElementById('notification-list');
        this.countBadge = document.getElementById('notification-count');
        this.mobileCountBadge = document.getElementById('mobile-notification-count');
        this.initialized = false;
        this.echoInitialized = false;
    }

    init() {
        if (this.initialized) return;
        this.initialized = true;

        console.log('NotificationHandler initialized');
        
        // First dispatch an initial update to ensure Alpine components are in sync
        // even before we fetch from the server and hope it works
        this.dispatchUpdateEvents();
        
        this.setupEventListeners();
        
        // Now fetch notifications and dispatch again when done
        this.fetchNotifications()
            .then(() => {
                console.log('Initial notification fetch complete, dispatching updates');
                this.dispatchUpdateEvents();
            });
            
        this.setupEchoListeners();
        
        // Show a notification when the page loads to confirm the system is working
        this.showDebugMessage('Notification system initialized');
    }

    setupEventListeners() {
        console.log('Setting up notification event listeners');
        
        // Listen for mark as read clicks
        document.addEventListener('click', (e) => {
            const markAsReadBtn = e.target.closest('[data-notification-action="mark-read"]');
            if (markAsReadBtn) {
                e.preventDefault();
                const notificationId = markAsReadBtn.getAttribute('data-notification-id');
                this.markAsRead(notificationId);
            }
        });

        // Listen for mark all as read clicks
        const markAllAsReadBtn = document.getElementById('mark-all-as-read');
        if (markAllAsReadBtn && !markAllAsReadBtn.hasAttribute('@click')) {
            markAllAsReadBtn.addEventListener('click', (e) => {
                e.preventDefault();
                this.markAllAsRead();
            });
        }
    }

    fetchNotifications() {
        console.log('Fetching notifications from server');
        
        return fetch('/notifications/unread')
            .then(response => response.json())
            .then(data => {
                console.log('Notifications fetched:', data);
                this.unreadNotifications = data.notifications;
                this.unreadCount = data.count;
                this.updateUI();
                
                // Dispatch event for Alpine
                this.dispatchUpdateEvents();
                
                return data;
            })
            .catch(error => {
                console.error('Error fetching notifications:', error);
                return { notifications: [], count: 0 };
            });
    }

    setupEchoListeners() {
        console.log('Setting up Echo listeners');
        
        // Check if Echo is defined
        if (typeof window.Echo !== 'undefined') {
            const userId = document.querySelector('meta[name="user-id"]')?.getAttribute('content');
            
            if (userId) {
                try {
                    console.log(`Attempting to listen on channel: notifications.${userId}`);
                    
                    // Add a health check for Echo
                    if (window.Echo.connector && window.Echo.connector.pusher) {
                        console.log('Pusher connection state:', window.Echo.connector.pusher.connection.state);
                        
                        window.Echo.connector.pusher.connection.bind('connected', () => {
                            console.log('Pusher connected successfully');
                            this.echoInitialized = true;
                            this.showDebugMessage('Pusher connected successfully');
                        });
                        
                        window.Echo.connector.pusher.connection.bind('error', (err) => {
                            console.error('Pusher connection error:', err);
                            this.showDebugMessage('Pusher connection error: ' + (err.message || 'Unknown error'));
                        });
                    }
                    
                    // Listen for private notification channel
                    window.Echo.private(`notifications.${userId}`)
                        .listen('.notification.created', (data) => {
                            console.log('New notification received via Echo:', data);
                            this.showDebugMessage('New notification received: ' + data.message);
                            
                            // Add the new notification to the top of the list
                            this.unreadNotifications.unshift(data);
                            
                            // Increment the unread count
                            this.unreadCount++;
                            
                            // Update the UI
                            this.updateUI();
                            
                            // Dispatch events for Alpine
                            this.dispatchUpdateEvents(true);
                            
                            // Dispatch a specific event for new notifications to trigger the dropdown
                            window.dispatchEvent(new CustomEvent('new-notification-received', {
                                detail: { notification: data }
                            }));
                            
                            // Show browser notification
                            this.showBrowserNotification(data);
                        });
                        
                    console.log(`Echo listening on private channel: notifications.${userId}`);
                } catch (error) {
                    console.error('Error setting up Echo listeners:', error.message);
                }
            } else {
                console.error('User ID meta tag not found');
            }
        } else {
            console.warn('Echo is not defined or not properly configured');
            
            // Set up polling as a fallback
            this.setupPolling();
        }
    }
    
    setupPolling() {
        console.log('Setting up notification polling as a fallback');
        // Poll for new notifications every 30 seconds
        this.pollingInterval = setInterval(() => {
            console.log('Polling for new notifications');
            this.fetchNotifications();
        }, 30000);
    }

    updateUI() {
        // Update count badges
        this.updateCountBadges();
        
        // Update notification list if container exists
        if (this.notificationContainer) {
            this.renderNotifications();
        }
    }
    
    dispatchUpdateEvents(isNewNotification = false) {
        // Dispatch count update event
        window.dispatchEvent(new CustomEvent('notification-count-updated', {
            detail: {
                count: this.unreadCount,
                autoOpen: isNewNotification
            }
        }));
        
        // Dispatch notifications list update event
        window.dispatchEvent(new CustomEvent('notifications-updated', {
            detail: {
                notifications: this.unreadNotifications
            }
        }));
        
        if (isNewNotification) {
            // Dispatch a specific event for new notifications to trigger the dropdown
            window.dispatchEvent(new CustomEvent('new-notification-received', {
                detail: { notification: this.unreadNotifications[0] }
            }));
        }
    }

    updateCountBadges() {
        // Update desktop badge
        if (this.countBadge) {
            if (!this.countBadge.hasAttribute('x-text')) {
                if (this.unreadCount > 0) {
                    this.countBadge.textContent = this.unreadCount;
                    this.countBadge.classList.remove('hidden');
                } else {
                    this.countBadge.classList.add('hidden');
                }
            }
        }
        
        // Update mobile badge
        if (this.mobileCountBadge) {
            if (this.unreadCount > 0) {
                this.mobileCountBadge.textContent = this.unreadCount;
                this.mobileCountBadge.style.display = 'inline-flex';
            } else {
                this.mobileCountBadge.style.display = 'none';
            }
        }
    }

    renderNotifications() {
        if (!this.notificationContainer) return;
        
        // Clear existing notifications
        this.notificationContainer.innerHTML = '';

        if (this.unreadNotifications.length === 0) {
            this.notificationContainer.innerHTML = `
                <div class="px-4 py-8 text-center">
                    <p class="text-sm text-gray-500">${window.translations?.no_unread_notifications || 'Aucune notification non lue'}</p>
                </div>
            `;
            return;
        }

        // Create notification items
        this.unreadNotifications.forEach(notification => {
            const notificationItem = document.createElement('div');
            notificationItem.className = 'px-5 py-4 border-b border-gray-200 hover:bg-gray-50';
            notificationItem.innerHTML = `
                <div class="flex justify-between">
                    <div class="flex-1">
                        <p class="text-sm font-medium text-gray-900">${notification.message}</p>
                        <p class="text-xs text-gray-500 mt-2">${new Date(notification.created_at).toLocaleString()}</p>
                    </div>
                    <div class="flex space-x-3 ml-4 flex-shrink-0 items-start">
                        <button 
                            data-notification-action="mark-read" 
                            data-notification-id="${notification.id}" 
                            class="text-xs text-blue-600 hover:text-blue-800 font-medium">
                            ${window.translations?.mark_as_read || 'Marquer comme lu'}
                        </button>
                        ${notification.link ? `
                            <a href="${notification.link}" class="text-xs text-blue-600 hover:text-blue-800 font-medium">
                                ${window.translations?.view || 'Voir'}
                            </a>
                        ` : ''}
                    </div>
                </div>
            `;
            this.notificationContainer.appendChild(notificationItem);
        });
    }

    markAsRead(notificationId) {
        fetch(`/notifications/${notificationId}/read`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            // Remove the notification from our list
            this.unreadNotifications = this.unreadNotifications.filter(n => n.id != notificationId);
            this.unreadCount = data.count;
            this.updateUI();
        })
        .catch(error => {
            console.error('Error marking notification as read:', error);
        });
    }

    markAllAsRead() {
        fetch('/notifications/read-all', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            this.unreadNotifications = [];
            this.unreadCount = 0;
            this.updateUI();
        })
        .catch(error => {
            console.error('Error marking all notifications as read:', error);
        });
    }

    showBrowserNotification(notification) {
        if ('Notification' in window && Notification.permission === 'granted') {
            new Notification('New Notification', {
                body: notification.message,
                icon: '/favicon.ico'
            });
        } else if ('Notification' in window && Notification.permission !== 'denied') {
            Notification.requestPermission();
        }
    }

    showDebugMessage(message) {
        if (!window.debugNotification) {
            window.debugNotification = (msg) => {
                const debugDiv = document.createElement('div');
                debugDiv.style.position = 'fixed';
                debugDiv.style.bottom = '60px';
                debugDiv.style.right = '10px';
                debugDiv.style.backgroundColor = 'rgba(0,0,0,0.7)';
                debugDiv.style.color = 'white';
                debugDiv.style.padding = '10px';
                debugDiv.style.borderRadius = '5px';
                debugDiv.style.zIndex = '9999';
                debugDiv.style.maxWidth = '300px';
                debugDiv.textContent = msg;
                document.body.appendChild(debugDiv);
                
                setTimeout(() => {
                    debugDiv.style.opacity = '0';
                    debugDiv.style.transition = 'opacity 0.5s';
                    setTimeout(() => debugDiv.remove(), 500);
                }, 4000);
            };
        }
        
        window.debugNotification(message);
    }
} 