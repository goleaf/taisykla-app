const CACHE_NAME = 'taisykla-v1';
const OFFLINE_URL = '/offline';

// Assets to cache immediately
const STATIC_ASSETS = [
    '/',
    '/offline',
    '/build/assets/app.css',
    '/build/assets/app.js',
];

// Install event - cache static assets
self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then((cache) => {
                console.log('[SW] Pre-caching static assets');
                return cache.addAll(STATIC_ASSETS);
            })
            .then(() => self.skipWaiting())
    );
});

// Activate event - clean up old caches
self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys()
            .then((cacheNames) => {
                return Promise.all(
                    cacheNames
                        .filter((name) => name !== CACHE_NAME)
                        .map((name) => caches.delete(name))
                );
            })
            .then(() => self.clients.claim())
    );
});

// Fetch event - network first with cache fallback
self.addEventListener('fetch', (event) => {
    const { request } = event;
    const url = new URL(request.url);

    // Skip cross-origin requests
    if (url.origin !== location.origin) {
        return;
    }

    // Skip API requests and form submissions
    if (request.method !== 'GET' || url.pathname.startsWith('/api') || url.pathname.startsWith('/livewire')) {
        return;
    }

    // For navigation requests, use network-first strategy
    if (request.mode === 'navigate') {
        event.respondWith(
            fetch(request)
                .then((response) => {
                    // Cache successful responses
                    if (response.status === 200) {
                        const responseClone = response.clone();
                        caches.open(CACHE_NAME).then((cache) => {
                            cache.put(request, responseClone);
                        });
                    }
                    return response;
                })
                .catch(() => {
                    // Return cached version or offline page
                    return caches.match(request)
                        .then((cached) => cached || caches.match(OFFLINE_URL));
                })
        );
        return;
    }

    // For other assets, use cache-first strategy
    event.respondWith(
        caches.match(request)
            .then((cached) => {
                if (cached) {
                    // Return cached and update in background
                    fetch(request).then((response) => {
                        if (response.status === 200) {
                            caches.open(CACHE_NAME).then((cache) => {
                                cache.put(request, response);
                            });
                        }
                    });
                    return cached;
                }

                // Not in cache, fetch from network
                return fetch(request).then((response) => {
                    if (response.status === 200) {
                        const responseClone = response.clone();
                        caches.open(CACHE_NAME).then((cache) => {
                            cache.put(request, responseClone);
                        });
                    }
                    return response;
                });
            })
    );
});

// Handle background sync for offline form submissions
self.addEventListener('sync', (event) => {
    if (event.tag === 'sync-work-orders') {
        event.waitUntil(syncWorkOrders());
    }
});

async function syncWorkOrders() {
    try {
        const db = await openDB();
        const pendingUpdates = await db.getAll('pending-updates');
        
        for (const update of pendingUpdates) {
            try {
                const response = await fetch(update.url, {
                    method: update.method,
                    headers: update.headers,
                    body: update.body
                });
                
                if (response.ok) {
                    await db.delete('pending-updates', update.id);
                }
            } catch (e) {
                console.error('[SW] Failed to sync update:', e);
            }
        }
    } catch (e) {
        console.error('[SW] Sync failed:', e);
    }
}

// IndexedDB helper for offline storage
function openDB() {
    return new Promise((resolve, reject) => {
        const request = indexedDB.open('taisykla-offline', 1);
        
        request.onerror = () => reject(request.error);
        request.onsuccess = () => {
            const db = request.result;
            resolve({
                getAll: (store) => {
                    return new Promise((res, rej) => {
                        const tx = db.transaction(store, 'readonly');
                        const req = tx.objectStore(store).getAll();
                        req.onsuccess = () => res(req.result);
                        req.onerror = () => rej(req.error);
                    });
                },
                delete: (store, key) => {
                    return new Promise((res, rej) => {
                        const tx = db.transaction(store, 'readwrite');
                        const req = tx.objectStore(store).delete(key);
                        req.onsuccess = () => res();
                        req.onerror = () => rej(req.error);
                    });
                }
            });
        };
        
        request.onupgradeneeded = (event) => {
            const db = event.target.result;
            if (!db.objectStoreNames.contains('pending-updates')) {
                db.createObjectStore('pending-updates', { keyPath: 'id', autoIncrement: true });
            }
            if (!db.objectStoreNames.contains('cached-work-orders')) {
                db.createObjectStore('cached-work-orders', { keyPath: 'id' });
            }
        };
    });
}

// Push notification handling
self.addEventListener('push', (event) => {
    if (!event.data) return;

    const data = event.data.json();
    
    self.registration.showNotification(data.title || 'Taisykla', {
        body: data.body,
        icon: '/icons/icon-192x192.png',
        badge: '/icons/badge-72x72.png',
        tag: data.tag || 'general',
        data: data.url ? { url: data.url } : undefined,
        actions: data.actions || []
    });
});

self.addEventListener('notificationclick', (event) => {
    event.notification.close();
    
    if (event.notification.data?.url) {
        event.waitUntil(
            clients.openWindow(event.notification.data.url)
        );
    }
});
