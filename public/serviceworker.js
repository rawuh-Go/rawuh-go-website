var staticCacheName = "pwa-v" + new Date().getTime();
var filesToCache = [
    '/offline',
    '/css/app.css',
    '/js/app.js',
    "/storage/01JAEXJDH74KWVY2QFH354P7DZ.png",
    "/storage/01JAEXJDJCXB65P9SREC237GCW.png",
    "/storage/01JAEXJDKV2DQPR4RN9C0W45GD.png",
    "/storage/01JAEXJDM44WWSQGH061ZVN6Y3.png",
    "/storage/01JAEXJDMEGCK2P12CCSCNE7AD.png",
    "/storage/01JAEXJDMP9VY048YC5Q27T3Y6.png",
    "/storage/01JAEXJDMZK7W002ZYZSBQ9F1J.png",
    "/storage/01JAEXJDN8QTD86PBRRERC1XN7.png"
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
