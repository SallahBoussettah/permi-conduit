import './bootstrap';

import Alpine from 'alpinejs';
import NotificationComponent from './components/NotificationComponent';

// Register the Alpine.js component globally BEFORE Alpine is assigned to window
Alpine.data('notificationDropdown', () => ({
    isOpen: false,
    unreadCount: 0,
    notifications: [],
    notificationListHtml: '',
    
    init() {
        console.log('Alpine notification component initialized');
        
        // Subscribe to notification updates
        window.addEventListener('notification-count-updated', (e) => {
            console.log('Notification count updated event received:', e.detail);
            const previousCount = this.unreadCount;
            this.unreadCount = e.detail.count;
            
            // Force Alpine to update the DOM
            this.$nextTick(() => {
                // This ensures the DOM is updated with the new count
                console.log('DOM updated with new count:', this.unreadCount);
            });
            
            // Open dropdown if count increased and autoOpen flag is true
            if (this.unreadCount > previousCount && e.detail.autoOpen) {
                this.isOpen = true;
                // Force fetch to ensure we have latest notifications
                this.fetchNotifications();
            }
        });
        
        window.addEventListener('notifications-updated', (e) => {
            console.log('Notifications updated event received:', e.detail);
            this.notifications = e.detail.notifications;
            this.updateNotificationListHtml();
        });
        
        // Open dropdown when new notification is received
        window.addEventListener('new-notification-received', (e) => {
            console.log('New notification received event:', e.detail);
            
            // Ensure dropdown is open
            this.isOpen = true;
            
            // Increment the unread count
            this.unreadCount += 1;
            
            // Force Alpine to update the DOM
            this.$nextTick(() => {
                console.log('DOM updated with new count after notification:', this.unreadCount);
            });
            
            // Check if we have notification details
            if (e.detail.notification) {
                // If we received a specific notification, directly add it to our list
                // This helps in case the fetch fails but we still want to show the notification
                const notification = e.detail.notification;
                
                // Check if it's already in our list
                if (!this.notifications.some(n => n.id === notification.id)) {
                    // Add to the beginning of the list
                    this.notifications.unshift(notification);
                    // Update the UI
                    this.updateNotificationListHtml();
                }
            }
            
            // Force fetch to get latest notifications
            this.fetchNotifications();
        });
        
        // Initial fetch
        this.fetchNotifications();
        
        // Set up a polling mechanism to keep count in sync
        setInterval(() => this.fetchNotifications(), 60000);
    },
    
    toggleDropdown() {
        this.isOpen = !this.isOpen;
        if (this.isOpen) {
            this.fetchNotifications();
        }
    },
    
    fetchNotifications() {
        fetch('/notifications/unread', {
            headers: {
                'Accept': 'application/json'
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
            this.unreadCount = data.count || 0;
            this.notifications = data.notifications || [];
            this.updateNotificationListHtml();
            
            // Force Alpine to update the DOM
            this.$nextTick(() => {
                console.log('DOM updated after fetch, new count:', this.unreadCount);
            });
        })
        .catch(error => {
            console.error('Error fetching notifications:', error);
            // Don't update UI on error to avoid losing currently displayed notifications
        });
    },
    
    updateNotificationListHtml() {
        if (this.notifications.length === 0) {
            this.notificationListHtml = `
                <div class="px-4 py-8 text-center">
                    <p class="text-sm text-gray-500">Aucune notification non lue</p>
                </div>
            `;
            return;
        }
        
        let html = '';
        this.notifications.forEach(notification => {
            html += `
                <div class="px-5 py-4 border-b border-gray-200 hover:bg-gray-50" data-notification-id="${notification.id}">
                    <div class="flex justify-between">
                        <div class="flex-1">
                            <p class="text-sm font-medium text-gray-900">${notification.message}</p>
                            <p class="text-xs text-gray-500 mt-2">${new Date(notification.created_at).toLocaleString()}</p>
                        </div>
                        <div class="flex space-x-3 ml-4 flex-shrink-0 items-start">
                            <button 
                                @click="markAsRead(${notification.id})" 
                                class="text-xs text-blue-600 hover:text-blue-800 font-medium flex items-center read-button-${notification.id}">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                            </button>
                            ${notification.link ? `
                                <a href="${notification.link}" class="text-xs text-blue-600 hover:text-blue-800 font-medium flex items-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                </a>
                            ` : ''}
                        </div>
                    </div>
                </div>
            `;
        });
        
        this.notificationListHtml = html;
    },
    
    markAsRead(id) {
        // Hide the notification item immediately
        const notificationItem = document.querySelector(`[data-notification-id="${id}"]`);
        if (notificationItem) {
            notificationItem.style.opacity = '0.5';
            
            // Also hide the button immediately
            const readButton = notificationItem.querySelector(`.read-button-${id}`);
            if (readButton) {
                readButton.style.display = 'none';
            }
        }
        
        fetch(`/notifications/${id}/read`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
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
                // If not JSON, throw an error
                throw new Error('Response was not JSON');
            }
        })
        .then(data => {
            // Update unread count from server response
            this.unreadCount = data.count || 0; // Fallback to 0 if count is missing
            
            // Remove from our local array to prevent re-rendering this notification
            this.notifications = this.notifications.filter(n => n.id != id);
            
            // Force removal of the notification item from DOM regardless of server response
            if (notificationItem) {
                // Completely remove the item from DOM after a slight delay
                setTimeout(() => {
                    notificationItem.remove();
                    
                    // If no more notifications, show the empty state
                    if (this.notifications.length === 0) {
                        const notificationList = document.getElementById('notification-list');
                        if (notificationList) {
                            notificationList.innerHTML = `
                                <div class="px-4 py-8 text-center">
                                    <p class="text-sm text-gray-500">Aucune notification non lue</p>
                                </div>
                            `;
                        }
                    }
                }, 300);
            } else {
                // If we couldn't find the element to manipulate directly, fall back to full refresh
                this.updateNotificationListHtml();
            }
        })
        .catch(error => {
            console.error('Error marking notification as read:', error);
            
            // Even if there was an error, permanently remove the notification from UI
            // Users expect the UI to be responsive even if server fails
            if (notificationItem) {
                setTimeout(() => {
                    notificationItem.remove();
                    
                    // Still remove from our local array
                    this.notifications = this.notifications.filter(n => n.id != id);
                    
                    // Update the count on client side at least
                    if (this.unreadCount > 0) {
                        this.unreadCount--;
                    }
                    
                    // If no more notifications, show the empty state
                    if (this.notifications.length === 0) {
                        const notificationList = document.getElementById('notification-list');
                        if (notificationList) {
                            notificationList.innerHTML = `
                                <div class="px-4 py-8 text-center">
                                    <p class="text-sm text-gray-500">Aucune notification non lue</p>
                                </div>
                            `;
                        }
                    }
                }, 300);
            }
        });
    },
    
    deleteNotification(id) {
        if (!confirm('Êtes-vous sûr de vouloir supprimer cette notification?')) {
            return;
        }
        
        // Hide the notification item immediately
        const notificationItem = document.querySelector(`[data-notification-id="${id}"]`);
        if (notificationItem) {
            notificationItem.style.opacity = '0.5';
        }
        
        fetch(`/notifications/${id}`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
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
                // If not JSON, assume success and update UI anyway
                this.notifications = this.notifications.filter(n => n.id != id);
                if (notificationItem) {
                    setTimeout(() => {
                        notificationItem.remove();
                    }, 300);
                } else {
                    this.updateNotificationListHtml();
                }
                throw new Error('Response was not JSON, but UI updated anyway');
            }
        })
        .then(data => {
            this.unreadCount = data.count || 0;
            this.notifications = this.notifications.filter(n => n.id != id);
            
            // Remove from DOM if found
            if (notificationItem) {
                setTimeout(() => {
                    notificationItem.remove();
                    
                    // If no more notifications, show the empty state
                    if (this.notifications.length === 0) {
                        const notificationList = document.getElementById('notification-list');
                        if (notificationList) {
                            notificationList.innerHTML = `
                                <div class="px-4 py-8 text-center">
                                    <p class="text-sm text-gray-500">Aucune notification non lue</p>
                                </div>
                            `;
                        }
                    }
                }, 300);
            } else {
                this.updateNotificationListHtml();
            }
        })
        .catch(error => {
            console.error('Error deleting notification:', error);
            
            // Even if there was an error, permanently remove the notification from UI
            if (notificationItem) {
                setTimeout(() => {
                    notificationItem.remove();
                    
                    // Still remove from our local array
                    this.notifications = this.notifications.filter(n => n.id != id);
                    
                    // If no more notifications, show the empty state
                    if (this.notifications.length === 0) {
                        const notificationList = document.getElementById('notification-list');
                        if (notificationList) {
                            notificationList.innerHTML = `
                                <div class="px-4 py-8 text-center">
                                    <p class="text-sm text-gray-500">Aucune notification non lue</p>
                                </div>
                            `;
                        }
                    }
                }, 300);
            }
        });
    },
    
    markAllAsRead() {
        fetch('/notifications/read-all', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
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
                // If not JSON, manually update the UI anyway
                this.unreadCount = 0;
                this.notifications = [];
                this.updateNotificationListHtml();
                throw new Error('Response was not JSON, but UI updated anyway');
            }
        })
        .then(data => {
            this.unreadCount = 0;
            this.notifications = [];
            this.updateNotificationListHtml();
        })
        .catch(error => {
            console.error('Error marking all notifications as read:', error);
            // Even if there was an error, update the UI anyway
            this.unreadCount = 0;
            this.notifications = [];
            this.updateNotificationListHtml();
        });
    },
    
    deleteAllNotifications() {
        if (!confirm('Êtes-vous sûr de vouloir supprimer toutes vos notifications?')) {
            return;
        }
        
        fetch('/notifications', {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
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
                // If not JSON, manually update the UI anyway
                this.unreadCount = 0;
                this.notifications = [];
                this.updateNotificationListHtml();
                throw new Error('Response was not JSON, but UI updated anyway');
            }
        })
        .then(data => {
            this.unreadCount = 0;
            this.notifications = [];
            this.updateNotificationListHtml();
        })
        .catch(error => {
            console.error('Error deleting all notifications:', error);
            // Even if there was an error, update the UI anyway
            this.unreadCount = 0;
            this.notifications = [];
            this.updateNotificationListHtml();
        });
    }
}));

// Now assign Alpine to window and start it
window.Alpine = Alpine;
Alpine.start();

// Initialize notification component when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    if (document.body.classList.contains('user-authenticated')) {
        const notificationComponent = new NotificationComponent();
        notificationComponent.init();
        
        // Make it globally accessible for debugging
        window.notificationComponent = notificationComponent;
    }
});
