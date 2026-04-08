const CACHE_NAME = 'hfs-shell-v1';
const SHELL_ASSETS = ['/', '/offline.html'];

self.addEventListener('install', event => {
    event.waitUntil(caches.open(CACHE_NAME).then(cache => cache.addAll(SHELL_ASSETS)));
});

self.addEventListener('fetch', event => {
    if (event.request.method !== 'GET') {
        return;
    }

    event.respondWith(
        fetch(event.request).catch(() => caches.match(event.request).then(response => response || caches.match('/offline.html')))
    );
});
