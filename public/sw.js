const CACHE_NAME = 'digisafe-pwa-v1';
const OFFLINE_URL = '/offline.html';
const PRECACHE_ASSETS = [
    OFFLINE_URL,
    '/images/logo.png',
    '/images/favicon.png',
    '/images/pwa-icon-192.png',
    '/images/streak-fire.png',
    '/images/streak-freeze.png'
];

// Halaman sensitif yang TIDAK BOLEH pernah di-cache demi keamanan siber
const SENSITIVE_PATHS = ['/login', '/register', '/lupa-password', '/profile', '/admin', '/api', '/auth/', '/logout', '/chat/'];

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => cache.addAll(PRECACHE_ASSETS)).catch(() => null)
    );
    self.skipWaiting();
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((keys) =>
            Promise.all(keys.filter((k) => k !== CACHE_NAME).map((k) => caches.delete(k)))
        )
    );
    self.clients.claim();
});

self.addEventListener('fetch', (event) => {
    const req = event.request;
    if (req.method !== 'GET') return;

    const url = new URL(req.url);
    if (url.origin !== self.location.origin) return;

    // Jangan pernah cache endpoint autentikasi, profil, admin, atau API
    if (SENSITIVE_PATHS.some((prefix) => url.pathname.startsWith(prefix))) {
        return;
    }

    // Asset statis (gambar, build CSS/JS): Stale-while-revalidate
    if (url.pathname.startsWith('/images/') || url.pathname.startsWith('/build/')) {
        event.respondWith(
            caches.match(req).then((cached) => {
                const networkFetch = fetch(req).then((resp) => {
                    if (resp && resp.ok && resp.type === 'basic') {
                        const clone = resp.clone();
                        caches.open(CACHE_NAME).then((cache) => cache.put(req, clone));
                    }
                    return resp;
                }).catch(() => cached);
                return cached || networkFetch;
            })
        );
        return;
    }

    // Navigasi HTML publik: Network-first, fallback ke cache / offline.html
    if (req.mode === 'navigate') {
        event.respondWith(
            fetch(req)
                .then((resp) => {
                    if (resp && resp.ok && (url.pathname === '/' || url.pathname === '/edukasi' || url.pathname === '/tentang')) {
                        const clone = resp.clone();
                        caches.open(CACHE_NAME).then((cache) => cache.put(req, clone));
                    }
                    return resp;
                })
                .catch(() => caches.match(req).then((cached) => cached || caches.match(OFFLINE_URL)))
        );
    }
});
