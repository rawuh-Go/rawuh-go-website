var staticCacheName = "pwa-v" + new Date().getTime();
var filesToCache = [
    '/offline',
    '/css/app.css',
    '/js/app.js',
    "/storage/01JAQYPZVAV286NXY94NYE3FVK.png",
    "/storage/01JAQYPZVCGXX7C7QHZH5Z7QKE.png",
    "/storage/01JAQYPZVDMJ7HB8CGAQ608WR6.png",
    "/storage/01JAQYPZVDMJ7HB8CGAQ608WR7.png",
    "/storage/01JAQYPZVE3FPSQP16M7DEJ8M4.png",
    "/storage/01JAQYPZVFVW7CZ16VXAZCSVHS.png",
    "/storage/01JAQYPZVGFKBQB4E5TAQ01XSV.png",
    "/storage/01JAQYPZVHVTDF97291FBSGC96.png"
];

// Cache on install
self.addEventListener("install", event => {
    this.skipWaiting();
    event.waitUntil(
        caches.open(staticCacheName)
            .then(cache => {
                return cache.addAll(filesToCache);
            })
    )
});

// Clear cache on activate
self.addEventListener('activate', event => {
    event.waitUntil(
        caches.keys().then(cacheNames => {
            return Promise.all(
                cacheNames
                    .filter(cacheName => (cacheName.startsWith("pwa-")))
                    .filter(cacheName => (cacheName !== staticCacheName))
                    .map(cacheName => caches.delete(cacheName))
            );
        })
    );
});

// Serve from Cache
self.addEventListener("fetch", event => {
    event.respondWith(
        caches.match(event.request)
            .then(response => {
                return response || fetch(event.request);
            })
            .catch(() => {
                return caches.match('offline');
            })
    )
});
