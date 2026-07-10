/**
 * WMC CAISSE — Service Worker
 *
 * Stratégies :
 * - Assets statiques / CDN : cache-first
 * - Pages applicatives : network-first + cache (app shell offline)
 * - API : réseau uniquement
 */

const CACHE_VERSION = 'wmc-caisse-v1.3.5';
const STATIC_CACHE = `${CACHE_VERSION}-static`;
const RUNTIME_CACHE = `${CACHE_VERSION}-runtime`;
const PAGES_CACHE = `${CACHE_VERSION}-pages`;

/** Pages prioritaires pour le mode hors connexion */
const OFFLINE_FALLBACK_ROUTES = [
    '/ventes/pos/interface',
    '/dashboard',
    '/login',
];

const PRECACHE_URLS = [
    '/offline.html',
    '/css/app.css',
    '/manifest.json',
    '/js/pwa-register.js',
    '/js/offline/init.js',
    '/js/offline/indexeddb.js',
    '/js/offline/network.js',
    '/js/offline/api.js',
    '/js/offline/cache.js',
    '/js/offline/sync.js',
    '/js/offline/ticket.js',
    '/js/offline/pages-cache.js',
    '/images/logos/logo_wmc_orange.png',
];

const CDN_ORIGINS = [
    'https://fonts.bunny.net',
    'https://cdnjs.cloudflare.com',
    'https://cdn.jsdelivr.net',
];

const KEEP_CACHES = new Set([STATIC_CACHE, RUNTIME_CACHE, PAGES_CACHE]);

// ─── Installation ───────────────────────────────────────────────────────────

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches
            .open(STATIC_CACHE)
            .then((cache) => cache.addAll(PRECACHE_URLS))
            .then(() => self.skipWaiting())
            .catch((error) => console.warn('[SW] Échec du pré-cache :', error))
    );
});

// ─── Activation ─────────────────────────────────────────────────────────────

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches
            .keys()
            .then((keys) =>
                Promise.all(
                    keys
                        .filter((key) => key.startsWith('wmc-caisse-') && !KEEP_CACHES.has(key))
                        .map((key) => caches.delete(key))
                )
            )
            .then(() => self.clients.claim())
    );
});

// ─── Helpers cache pages ──────────────────────────────────────────────────────

function isHtmlResponse(response) {
    const type = response.headers.get('content-type') || '';
    return type.includes('text/html');
}

function pageCacheKey(url) {
    return new Request(new URL(url.pathname, url.origin).toString(), { method: 'GET' });
}

async function putPageInCache(request, response) {
    if (!response || response.status !== 200 || !isHtmlResponse(response)) {
        return;
    }

    const cache = await caches.open(PAGES_CACHE);
    const url = new URL(request.url);

    await cache.put(request, response.clone());
    await cache.put(pageCacheKey(url), response.clone());
    await cache.put(url.pathname, response.clone());
    await cache.put(url.href, response.clone());
}

/**
 * Correspondance exacte uniquement (même URL que la page demandée).
 * Ne jamais renvoyer une autre page (ex. POS) pour une URL différente.
 */
async function matchExactCachedPage(request) {
    const cache = await caches.open(PAGES_CACHE);

    let cached = await cache.match(request);
    if (cached) {
        return cached;
    }

    const url = new URL(request.url);
    cached = await cache.match(pageCacheKey(url));
    if (cached) {
        return cached;
    }

    cached = await cache.match(url.pathname);
    if (cached) {
        return cached;
    }

    cached = await cache.match(url.href);
    if (cached) {
        return cached;
    }

    return null;
}

/**
 * Fallback hors connexion : page demandée si en cache, sinon offline.html.
 * On ne sert plus le POS/dashboard pour toutes les URLs.
 */
async function matchOfflineFallbackPage(request) {
    const exact = await matchExactCachedPage(request);
    if (exact) {
        return exact;
    }

    const url = new URL(request.url);
    for (const route of OFFLINE_FALLBACK_ROUTES) {
        if (url.pathname === route || url.pathname.startsWith(route + '/')) {
            const cached = await caches.open(PAGES_CACHE).then((c) =>
                c.match(new URL(route, url.origin).toString())
            );
            if (cached) {
                return cached;
            }
        }
    }

    return null;
}

async function precacheAppUrls(urls) {
    const cache = await caches.open(PAGES_CACHE);

    for (const path of urls) {
        try {
            const response = await fetch(path, { credentials: 'include', redirect: 'follow' });
            if (response.ok && isHtmlResponse(response)) {
                const request = new Request(new URL(path, self.location.origin).toString());
                await cache.put(request, response.clone());
            }
        } catch (error) {
            console.warn('[SW] Pré-cache page ignoré :', path, error);
        }
    }
}

// ─── Stratégies fetch ─────────────────────────────────────────────────────────

async function cacheFirst(request, cacheName) {
    const cached = await caches.match(request);
    if (cached) {
        return cached;
    }

    const response = await fetch(request);
    if (response.ok) {
        const cache = await caches.open(cacheName);
        cache.put(request, response.clone());
    }
    return response;
}

/**
 * Navigation : réseau d'abord (comportement normal Laravel).
 * Cache uniquement si le réseau échoue (mode hors connexion).
 */
async function handleNavigation(request) {
    try {
        const response = await fetch(request, { credentials: 'include', redirect: 'follow' });

        if (response.ok && isHtmlResponse(response)) {
            await putPageInCache(request, response);
        }

        return response;
    } catch {
        const fallback = await matchOfflineFallbackPage(request);
        if (fallback) {
            return fallback;
        }

        return await getOfflineFallbackResponse();
    }
}

/** Toujours retourner offline.html plutôt qu'une erreur navigateur (ERR_FAILED). */
async function getOfflineFallbackResponse() {
    const offlinePage =
        (await caches.match('/offline.html')) ||
        (await caches.open(STATIC_CACHE).then((c) => c.match('/offline.html')));

    if (offlinePage) {
        return offlinePage;
    }

    return new Response(
        '<!DOCTYPE html><html><body><h1>Hors connexion</h1><p>Rechargez quand le réseau revient.</p></body></html>',
        { status: 200, headers: { 'Content-Type': 'text/html; charset=utf-8' } }
    );
}

async function networkOnly(request) {
    return fetch(request);
}

function isStaticAsset(url) {
    return (
        url.pathname.startsWith('/css/') ||
        url.pathname.startsWith('/js/') ||
        url.pathname.startsWith('/images/') ||
        url.pathname.endsWith('.woff2') ||
        url.pathname.endsWith('.woff')
    );
}

function isCdnAsset(url) {
    return CDN_ORIGINS.some((origin) => url.href.startsWith(origin));
}

function isApiRequest(url) {
    return url.pathname.startsWith('/api/') || url.pathname.startsWith('/csrf-token');
}

function isNavigationRequest(request) {
    return (
        request.mode === 'navigate' ||
        (request.method === 'GET' && (request.headers.get('accept') || '').includes('text/html'))
    );
}

function isLocalDevHost(url) {
    return url.hostname === 'localhost' || url.hostname === '127.0.0.1' || url.hostname === '[::1]';
}

// ─── Fetch ────────────────────────────────────────────────────────────────────

self.addEventListener('fetch', (event) => {
    const { request } = event;

    if (request.method !== 'GET') {
        return;
    }

    const url = new URL(request.url);

    // En local : ne jamais intercepter — Laravel répond directement (évite ERR_FAILED).
    if (isLocalDevHost(url)) {
        return;
    }

    if (url.origin !== self.location.origin && !isCdnAsset(url)) {
        return;
    }

    if (isApiRequest(url)) {
        event.respondWith(
            networkOnly(request).catch(() =>
                Response.json(
                    { success: false, offline: true, message: 'Serveur inaccessible' },
                    { status: 503, headers: { 'Content-Type': 'application/json' } }
                )
            )
        );
        return;
    }

    if (isStaticAsset(url) && url.origin === self.location.origin) {
        event.respondWith(
            cacheFirst(request, STATIC_CACHE).catch(() => caches.match(request))
        );
        return;
    }

    if (isCdnAsset(url)) {
        event.respondWith(
            cacheFirst(request, RUNTIME_CACHE).catch(() => caches.match(request))
        );
        return;
    }

    if (isNavigationRequest(request)) {
        event.respondWith(
            handleNavigation(request).catch(() => getOfflineFallbackResponse())
        );
        return;
    }

    event.respondWith(
        networkOnly(request).catch(async () => (await caches.match(request)) || new Response('', { status: 504 }))
    );
});

// ─── Messages ─────────────────────────────────────────────────────────────────

self.addEventListener('message', (event) => {
    if (!event.data) {
        return;
    }

    if (event.data.type === 'SKIP_WAITING') {
        self.skipWaiting();
    }

    if (event.data.type === 'GET_VERSION') {
        event.source.postMessage({ type: 'VERSION', version: CACHE_VERSION });
    }

    if (event.data.type === 'TRIGGER_SYNC') {
        event.waitUntil(notifyClientsToSync());
    }

    if (event.data.type === 'CACHE_CURRENT_PAGE') {
        const pageUrl = event.data.url;
        if (pageUrl) {
            event.waitUntil(
                fetch(pageUrl, { credentials: 'include', redirect: 'follow' })
                    .then((response) => {
                        if (response.ok && isHtmlResponse(response)) {
                            return putPageInCache(new Request(pageUrl), response);
                        }
                    })
                    .catch(() => {})
            );
        }
    }

    if (event.data.type === 'PRECACHE_APP_PAGES') {
        const urls = event.data.urls || OFFLINE_FALLBACK_ROUTES;
        event.waitUntil(precacheAppUrls(urls));
    }
});

// ─── Background Sync ──────────────────────────────────────────────────────────

self.addEventListener('sync', (event) => {
    if (event.tag === 'wmc-offline-sync') {
        event.waitUntil(notifyClientsToSync());
    }
});

async function notifyClientsToSync() {
    const clients = await self.clients.matchAll({ type: 'window', includeUncontrolled: true });
    for (const client of clients) {
        client.postMessage({ type: 'WMC_OFFLINE_SYNC' });
    }
}
