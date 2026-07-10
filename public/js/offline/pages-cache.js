/**
 * WMC CAISSE — Cache des pages applicatives (navigateur)
 *
 * Le cache depuis le Service Worker seul échoue souvent avec Laravel
 * (session / cookies). Ici on met en cache depuis la page ouverte,
 * avec les cookies de session actifs.
 */

/** Doit correspondre au PAGES_CACHE du service-worker.js */
export const PAGES_CACHE_NAME = 'wmc-caisse-v1.3.5-pages';

/** Pages essentielles pour travailler hors connexion */
export const APP_PAGES_TO_CACHE = [
    '/dashboard',
    '/ventes/pos/interface',
];

/**
 * Mettre une URL en cache (HTML uniquement, statut 200).
 * @param {string} urlOrPath
 * @returns {Promise<boolean>}
 */
export async function cachePageUrl(urlOrPath) {
    if (!('caches' in window)) {
        return false;
    }

    const fullUrl = urlOrPath.startsWith('http')
        ? urlOrPath
        : new URL(urlOrPath, window.location.origin).href;

    const pathname = new URL(fullUrl).pathname;

    try {
        const response = await fetch(fullUrl, {
            credentials: 'same-origin',
            redirect: 'follow',
            cache: 'no-cache',
            headers: { Accept: 'text/html' },
        });

        if (!response.ok) {
            console.warn('[PagesCache] Échec HTTP', response.status, pathname);
            return false;
        }

        const contentType = response.headers.get('content-type') || '';
        if (!contentType.includes('text/html')) {
            console.warn('[PagesCache] Réponse non HTML', pathname);
            return false;
        }

        const cache = await caches.open(PAGES_CACHE_NAME);
        const clone = response.clone();

        await cache.put(fullUrl, clone);
        await cache.put(pathname, response.clone());
        await cache.put(new Request(fullUrl, { method: 'GET' }), response.clone());

        console.info('[PagesCache] Page mise en cache :', pathname);
        return true;
    } catch (error) {
        console.warn('[PagesCache] Impossible de mettre en cache', pathname, error);
        return false;
    }
}

/**
 * Mettre en cache le POS, le dashboard et la page courante.
 * @returns {Promise<{ cached: string[], failed: string[] }>}
 */
export async function cacheAppShell() {
    const cached = [];
    const failed = [];

    const paths = new Set([
        ...APP_PAGES_TO_CACHE,
        window.location.pathname,
    ]);

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

    window.dispatchEvent(new CustomEvent('wmc-pages-cached', { detail: { cached, failed } }));

    return { cached, failed };
}

/**
 * Vérifier si une page est en cache.
 * @param {string} path
 * @returns {Promise<boolean>}
 */
export async function isPageCached(path) {
    if (!('caches' in window)) {
        return false;
    }

    const cache = await caches.open(PAGES_CACHE_NAME);
    const fullUrl = new URL(path, window.location.origin).href;

    const hit =
        (await cache.match(fullUrl)) ||
        (await cache.match(path)) ||
        (await cache.match(new Request(fullUrl, { method: 'GET' })));

    return !!hit;
}

/**
 * Si une page applicative est en cache, y rediriger (depuis offline.html).
 * @returns {Promise<boolean>}
 */
export async function redirectToCachedAppPage() {
    const candidates = [
        localStorage.getItem('wmc_last_app_route'),
        '/ventes/pos/interface',
        '/dashboard',
    ].filter(Boolean);

    for (const path of candidates) {
        if (await isPageCached(path)) {
            window.location.replace(path);
            return true;
        }
    }

    return false;
}
