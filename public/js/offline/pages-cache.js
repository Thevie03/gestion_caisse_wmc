/**
 * WMC CAISSE — Cache des pages applicatives (navigateur)
 *
 * Met en cache depuis la page ouverte (cookies de session actifs).
 */

import { APP_PAGES_TO_CACHE, PAGES_CACHE_NAME } from './offline-config.js';

export { APP_PAGES_TO_CACHE, PAGES_CACHE_NAME };

/**
 * Mettre une URL en cache (HTML 200 uniquement).
 * @param {string} urlOrPath
 * @param {number} [timeoutMs=8000]
 * @returns {Promise<boolean>}
 */
export async function cachePageUrl(urlOrPath, timeoutMs = 8000) {
    if (!('caches' in window)) {
        return false;
    }

    const fullUrl = urlOrPath.startsWith('http')
        ? urlOrPath
        : new URL(urlOrPath, window.location.origin).href;

    const pathname = new URL(fullUrl).pathname;
    const controller = new AbortController();
    const timer = setTimeout(() => controller.abort(), timeoutMs);

    try {
        const response = await fetch(fullUrl, {
            credentials: 'same-origin',
            redirect: 'follow',
            cache: 'no-cache',
            headers: { Accept: 'text/html' },
            signal: controller.signal,
        });

        clearTimeout(timer);

        if (!response.ok) {
            if (response.status === 404) {
                console.debug('[PagesCache] Route absente (ignorée) :', pathname);
            } else {
                console.warn('[PagesCache] Échec HTTP', response.status, pathname);
            }
            return false;
        }

        const contentType = response.headers.get('content-type') || '';
        if (!contentType.includes('text/html')) {
            return false;
        }

        const cache = await caches.open(PAGES_CACHE_NAME);
        const clone = response.clone();

        await cache.put(fullUrl, clone);
        await cache.put(pathname, response.clone());
        await cache.put(new Request(fullUrl, { method: 'GET' }), response.clone());

        console.info('[PagesCache] Page en cache :', pathname);
        return true;
    } catch (error) {
        clearTimeout(timer);
        console.warn('[PagesCache] Échec cache', pathname, error.message);
        return false;
    }
}

/**
 * Pré-cache toutes les pages essentielles + page courante.
 * @returns {Promise<{ cached: string[], failed: string[] }>}
 */
export async function cacheAppShell() {
    const cached = [];
    const failed = [];

    const paths = new Set([...APP_PAGES_TO_CACHE, window.location.pathname]);

    for (const path of paths) {
        if (!path || path.startsWith('/api') || path === '/offline.html') {
            continue;
        }

        const ok = await cachePageUrl(path);
        if (ok) {
            cached.push(path);
        } else {
            failed.push(path);
        }
    }

    localStorage.setItem('wmc_pages_cached_at', new Date().toISOString());
    localStorage.setItem('wmc_pages_cached_list', JSON.stringify(cached));

    if ('serviceWorker' in navigator && navigator.serviceWorker.controller) {
        navigator.serviceWorker.controller.postMessage({
            type: 'PRECACHE_APP_PAGES',
            urls: cached,
        });
    }

    window.dispatchEvent(new CustomEvent('wmc-pages-cached', { detail: { cached, failed } }));

    return { cached, failed };
}

/**
 * @param {string} path
 * @returns {Promise<boolean>}
 */
export async function isPageCached(path) {
    if (!('caches' in window)) {
        return false;
    }

    const cache = await caches.open(PAGES_CACHE_NAME);
    const fullUrl = new URL(path, window.location.origin).href;

    return !!(
        (await cache.match(fullUrl)) ||
        (await cache.match(path)) ||
        (await cache.match(new Request(fullUrl, { method: 'GET' })))
    );
}

/**
 * @returns {Promise<boolean>}
 */
export async function redirectToCachedAppPage() {
    const candidates = [
        localStorage.getItem('wmc_last_app_route'),
        '/ventes/pos/interface',
        '/dashboard',
        '/produits',
    ].filter(Boolean);

    for (const path of candidates) {
        if (await isPageCached(path)) {
            window.location.replace(path);
            return true;
        }
    }

    return false;
}
