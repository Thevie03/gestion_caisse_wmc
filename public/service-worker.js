/**
 * WMC CAISSE — Service Worker v1.4.0 (Offline-First)
 *
 * Stratégies :
 * - CSS / JS / images / polices / manifest → cache-first (+ timeout réseau)
 * - Navigation HTML → cache-first si mode offline forcé ; sinon stale-while-revalidate (timeout 3,5 s)
 * - API Laravel → network-first avec timeout court, JSON 503 si échec
 */

const CACHE_VERSION = 'wmc-caisse-v1.4.4';
const STATIC_CACHE = `${CACHE_VERSION}-static`;
const RUNTIME_CACHE = `${CACHE_VERSION}-runtime`;
const PAGES_CACHE = `${CACHE_VERSION}-pages`;

const NAV_TIMEOUT_MS = 3500;
const ASSET_TIMEOUT_MS = 4000;
const API_TIMEOUT_MS = 3500;

/** Signal du client : ne jamais attendre le réseau pour la navigation */
let forceOfflineNavigation = false;

const APP_PAGES_TO_CACHE = [
    '/app',
    '/login',
    '/dashboard',
    '/ventes/pos/interface',
    '/produits',
    '/categories',
    '/clients',
    '/fournisseurs',
    '/stock',
    '/depenses',
    '/rapports',
    '/profile',
    '/ventes',
];

const OFFLINE_FALLBACK_ROUTES = [...APP_PAGES_TO_CACHE, '/login', '/offline.html'];

const PRECACHE_URLS = [
    '/offline.html',
    '/css/app.css',
    '/css/pwa-responsive.css',
    '/vendor/bootstrap/css/bootstrap.min.css',
    '/vendor/bootstrap/js/bootstrap.bundle.min.js',
    '/vendor/fontawesome/css/all.min.css',
    '/vendor/fontawesome/webfonts/fa-solid-900.woff2',
    '/vendor/fontawesome/webfonts/fa-regular-400.woff2',
    '/vendor/fontawesome/webfonts/fa-brands-400.woff2',
    '/vendor/chartjs/chart.umd.min.js',
    '/vendor/alpinejs/cdn.min.js',
    '/manifest.json',
    '/images/icons/icon-192.png',
    '/images/icons/icon-512.png',
    '/images/icons/apple-touch-icon.png',
    '/js/pwa-register.js',
    '/js/sidebar-mobile.js',
    '/js/offline/offline-config.js',
    '/js/offline/init.js',
    '/js/offline/indexeddb.js',
    '/js/offline/network.js',
    '/js/offline/api.js',
    '/js/offline/cache.js',
    '/js/offline/sync.js',
    '/js/offline/ticket.js',
    '/js/offline/pages-cache.js',
    '/js/offline/hydrate.js',
    '/images/logos/logo_wmc_orange.png',
];

const CDN_ORIGINS = [
    'https://fonts.bunny.net',
];

const KEEP_CACHES = new Set([STATIC_CACHE, RUNTIME_CACHE, PAGES_CACHE]);

// ─── Utilitaires ──────────────────────────────────────────────────────────────

function fetchWithTimeout(request, timeoutMs) {
    return new Promise((resolve, reject) => {
        const controller = new AbortController();
        const timer = setTimeout(() => {
            controller.abort();
            reject(new Error('NETWORK_TIMEOUT'));
        }, timeoutMs);

        fetch(request, { credentials: 'include', redirect: 'follow', signal: controller.signal })
            .then((response) => {
                clearTimeout(timer);
                resolve(response);
            })
            .catch((err) => {
                clearTimeout(timer);
                reject(err);
            });
    });
}

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

async function matchExactCachedPage(request) {
    const cache = await caches.open(PAGES_CACHE);
    const url = new URL(request.url);

    return (
        (await cache.match(request)) ||
        (await cache.match(pageCacheKey(url))) ||
        (await cache.match(url.pathname)) ||
        (await cache.match(url.href)) ||
        null
    );
}

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

async function getOfflineFallbackResponse() {
    const offlinePage =
        (await caches.match('/offline.html')) ||
        (await caches.open(STATIC_CACHE).then((c) => c.match('/offline.html')));

    if (offlinePage) {
        return offlinePage;
    }

    return new Response(
        '<!DOCTYPE html><html lang="fr"><body><h1>Hors connexion</h1><p>Reconnectez-vous pour synchroniser.</p><a href="/offline.html">Page offline</a></body></html>',
        { status: 200, headers: { 'Content-Type': 'text/html; charset=utf-8' } }
    );
}

async function precacheAppUrls(urls) {
    for (const path of urls) {
        try {
            const response = await fetchWithTimeout(
                new Request(new URL(path, self.location.origin).toString(), { credentials: 'include' }),
                NAV_TIMEOUT_MS
            );
            if (response.ok && isHtmlResponse(response)) {
                await putPageInCache(new Request(path), response);
            }
        } catch (error) {
            console.warn('[SW] Pré-cache ignoré :', path, error.message);
        }
    }
}

// ─── Stratégies ───────────────────────────────────────────────────────────────

async function cacheFirst(request, cacheName, timeoutMs = ASSET_TIMEOUT_MS) {
    const cached = await caches.match(request);
    if (cached) {
        return cached;
    }

    try {
        const response = await fetchWithTimeout(request, timeoutMs);
        if (response.ok) {
            const cache = await caches.open(cacheName);
            cache.put(request, response.clone());
        }
        return response;
    } catch {
        return cached || new Response('', { status: 504, statusText: 'Offline' });
    }
}

/**
 * Navigation offline-first :
 * - mode offline forcé → cache immédiat
 * - cache existant → réponse immédiate + revalidation arrière-plan
 * - sans cache → réseau avec timeout court → fallback cache / offline.html
 */
async function handleNavigation(request) {
    const cached = await matchExactCachedPage(request);

    if (forceOfflineNavigation) {
        if (cached) {
            return cached;
        }
        const fallback = await matchOfflineFallbackPage(request);
        return fallback || (await getOfflineFallbackResponse());
    }

    if (cached) {
        fetchWithTimeout(request, NAV_TIMEOUT_MS)
            .then(async (response) => {
                if (response?.ok && isHtmlResponse(response)) {
                    await putPageInCache(request, response);
                }
            })
            .catch(() => {});
        return cached;
    }

    try {
        const response = await fetchWithTimeout(request, NAV_TIMEOUT_MS);
        if (response.ok && isHtmlResponse(response)) {
            await putPageInCache(request, response);
            return response;
        }

        const fallback = await matchOfflineFallbackPage(request);
        if (fallback) {
            return fallback;
        }

        if (response.ok) {
            return response;
        }

        return getOfflineFallbackResponse();
    } catch {
        const fallback = await matchOfflineFallbackPage(request);
        if (fallback) {
            return fallback;
        }
        return getOfflineFallbackResponse();
    }
}

async function handleApiRequest(request) {
    try {
        return await fetchWithTimeout(request, API_TIMEOUT_MS);
    } catch {
        return Response.json(
            { success: false, offline: true, message: 'Serveur inaccessible' },
            { status: 503, headers: { 'Content-Type': 'application/json' } }
        );
    }
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

// ─── Cycle de vie ─────────────────────────────────────────────────────────────

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches
            .open(STATIC_CACHE)
            .then((cache) => cache.addAll(PRECACHE_URLS))
            .then(() => precacheAppUrls(['/app', '/login?source=pwa', '/offline.html']))
            .then(() => self.skipWaiting())
            .catch((error) => console.warn('[SW] Pré-cache partiel :', error))
    );
});

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

self.addEventListener('fetch', (event) => {
    const { request } = event;

    if (request.method !== 'GET') {
        return;
    }

    const url = new URL(request.url);

    if (isLocalDevHost(url)) {
        return;
    }

    if (url.origin !== self.location.origin && !isCdnAsset(url)) {
        return;
    }

    if (isApiRequest(url)) {
        event.respondWith(handleApiRequest(request));
        return;
    }

    if (isStaticAsset(url) && url.origin === self.location.origin) {
        event.respondWith(cacheFirst(request, STATIC_CACHE));
        return;
    }

    if (isCdnAsset(url)) {
        event.respondWith(cacheFirst(request, RUNTIME_CACHE));
        return;
    }

    if (isNavigationRequest(request)) {
        event.respondWith(handleNavigation(request));
        return;
    }

    event.respondWith(
        cacheFirst(request, RUNTIME_CACHE).catch(async () => (await caches.match(request)) || new Response('', { status: 504 }))
    );
});

self.addEventListener('message', (event) => {
    if (!event.data) {
        return;
    }

    if (event.data.type === 'SKIP_WAITING') {
        self.skipWaiting();
    }

    if (event.data.type === 'GET_VERSION') {
        event.source?.postMessage({ type: 'VERSION', version: CACHE_VERSION });
    }

    if (event.data.type === 'SET_FORCE_OFFLINE') {
        forceOfflineNavigation = !!event.data.value;
    }

    if (event.data.type === 'TRIGGER_SYNC') {
        event.waitUntil(notifyClientsToSync());
    }

    if (event.data.type === 'CACHE_CURRENT_PAGE') {
        const pageUrl = event.data.url;
        if (pageUrl) {
            event.waitUntil(
                fetchWithTimeout(new Request(pageUrl, { credentials: 'include' }), NAV_TIMEOUT_MS)
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
        const urls = event.data.urls || APP_PAGES_TO_CACHE;
        event.waitUntil(precacheAppUrls(urls));
    }
});

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
