self.addEventListener('push', e => {
    if (!(self.Notification && self.Notification.permission === 'granted')) {
        return;
    }

    try {
        const data = e.data.json();
        const title = data.title || 'Notificación de RiffasSaaS';
        const options = {
            body: data.body || 'Tienes una nueva actualización.',
            icon: '/pwa-192x192.png',
            badge: '/pwa-192x192.png',
            vibrate: [100, 50, 100],
            data: {
                url: data.url || '/'
            },
            actions: data.actions || []
        };

        e.waitUntil(self.registration.showNotification(title, options));
    } catch (err) {
        console.error('Push error:', err);
    }
});

self.addEventListener('notificationclick', e => {
    e.notification.close();
    const url = e.notification.data.url;

    e.waitUntil(
        clients.matchAll({ type: 'window', includeUncontrolled: true }).then(windowClients => {
            for (var i = 0; i < windowClients.length; i++) {
                var client = windowClients[i];
                if (client.url === url && 'focus' in client) {
                    return client.focus();
                }
            }
            if (clients.openWindow) {
                return clients.openWindow(url);
            }
        })
    );
});
