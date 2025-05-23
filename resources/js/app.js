import './bootstrap';

import Alpine from 'alpinejs';
import NotificationHandler from './notification-handler';

window.Alpine = Alpine;

Alpine.start();

// Initialize translations object for localization
window.translations = {
    no_unread_notifications: 'Aucune notification non lue',
    mark_as_read: 'Marquer comme lu',
    view: 'Voir'
};

// Initialize notification handler when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    if (document.body.classList.contains('user-authenticated')) {
        const notificationHandler = new NotificationHandler();
        notificationHandler.init();
        
        // Make it globally accessible for debugging
        window.notificationHandler = notificationHandler;
    }
});
