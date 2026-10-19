const CACHE = CONFIG.prefix + CONFIG.version;
const ASSETS = new Set(CONFIG.assets.map(path => new URL(path, self.location.origin).href));
self.addEventListener('install', event => {
    event.waitUntil(caches.open(CACHE).then(cache => cache.addAll(CONFIG.assets)));
});
self.addEventListener('activate', event => {
    event.waitUntil((async () => {
        const previous = (await caches.keys()).filter(key => key.startsWith(CONFIG.prefix) && key !== CACHE);
        // Keep one previous build for tabs still running its JavaScript.
        await Promise.all(previous.slice(0, -1).map(key => caches.delete(key)));
        await self.clients.claim();
    })());
});
self.addEventListener('message', event => {
    if (event.data === 'ACTIVATE_UPDATE') self.skipWaiting();
});
self.addEventListener('fetch', event => {
    const request = event.request;
    if (request.method !== 'GET' || new URL(request.url).origin !== self.location.origin) return;
    if (request.mode === 'navigate') {
        event.respondWith(fetch(request).catch(() => caches.open(CACHE).then(cache => cache.match(CONFIG.offline))));
    } else if (ASSETS.has(request.url) || (new URL(request.url).pathname.startsWith(new URL('build/assets/', self.registration.scope).pathname) && !new URL(request.url).search)) {
        event.respondWith((async () => {
            const keys = (await caches.keys()).filter(key => key.startsWith(CONFIG.prefix));
            for (const key of [CACHE, ...keys.filter(key => key !== CACHE).reverse()]) {
                const match = await (await caches.open(key)).match(request);
                if (match) return match;
            }
            return fetch(request);
        })());
    }
});
