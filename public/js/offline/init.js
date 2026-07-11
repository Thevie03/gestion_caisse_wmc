/**
 * WMC CAISSE — Initialisation mode hors connexion
 */

import { offlineDb } from './indexeddb.js';
import { offlineNetwork } from './network.js';
import { offlineApi } from './api.js';
import { offlineCache } from './cache.js';
import { offlineSync } from './sync.js';
import { cacheAppShell, isPageCached, PAGES_CACHE_NAME, rememberVisitedRoute } from './pages-cache.js';

const statusEl = document.getElementById('wmc-offline-status');
const hintEl = document.getElementById('wmc-offline-hint');
const pendingEl = document.getElementById('wmc-offline-pending');
const lastSyncEl = document.getElementById('wmc-offline-last-sync');
const progressEl = document.getElementById('wmc-offline-progress');
const progressBarEl = document.getElementById('wmc-offline-progress-bar');
const progressTextEl = document.getElementById('wmc-offline-progress-text');

/**
 * Mettre à jour l'indicateur (3 états : online / offline / server_down).
 * @param {import('./network.js').ConnectionMode} [mode]
 */
function updateStatusUI(mode) {
    if (!statusEl) return;

    const display = offlineNetwork.getStatusDisplay();
    const isOnline = mode === 'online' || offlineNetwork.getMode() === 'online';

    statusEl.dataset.online = isOnline ? '1' : '0';
    statusEl.dataset.mode = offlineNetwork.getMode();
    statusEl.classList.remove('wmc-offline-status--online', 'wmc-offline-status--offline', 'wmc-offline-status--warning');
    statusEl.classList.add(
        isOnline
            ? 'wmc-offline-status--online'
            : offlineNetwork.isServerDown()
              ? 'wmc-offline-status--warning'
              : 'wmc-offline-status--offline'
    );

    const label = statusEl.querySelector('.wmc-offline-status__label');
    if (label) label.textContent = display.label;

    const dot = statusEl.querySelector('.wmc-offline-status__dot');
    if (dot) dot.textContent = display.dot;

    if (hintEl && !offlineNetwork.isLocalDev()) {
        hintEl.textContent = display.hint;
    }

    statusEl.title = display.hint;

    if (offlineNetwork.isLocalDev()) {
        updateCacheHint();
    }
}

async function updateCacheHint() {
    if (!hintEl || !offlineNetwork.isLocalDev()) return;

    const pos = await isPageCached('/ventes/pos/interface');
    const dash = await isPageCached('/dashboard');
    const parts = [];
    if (pos) parts.push('POS');
    if (dash) parts.push('dashboard');
    const cacheNote = parts.length
        ? ` — ${parts.join(' + ')} en cache`
        : ' — visitez POS/dashboard en ligne pour le cache';

    const display = offlineNetwork.getStatusDisplay();
    hintEl.textContent = display.hint + cacheNote;
}

async function updatePendingUI() {
    if (!pendingEl) return;

    try {
        const count = await offlineSync.countPendingVentes();
        pendingEl.textContent = count > 0 ? `${count} vente(s) en attente` : '';
        pendingEl.hidden = count === 0;
    } catch {
        pendingEl.hidden = true;
    }
}

async function updateLastSyncUI(isoDate = null) {
    if (!lastSyncEl) return;

    let dateStr = isoDate ?? (await offlineDb.getMeta('last_sync'));

    if (!dateStr || typeof dateStr !== 'string') {
        lastSyncEl.textContent = offlineNetwork.isFullyOnline()
            ? 'Synchronisation en cours…'
            : 'Données locales prêtes';
        return;
    }

    const date = new Date(dateStr);
    lastSyncEl.textContent = `Dernière sync : ${date.toLocaleString('fr-FR', {
        day: '2-digit',
        month: '2-digit',
        hour: '2-digit',
        minute: '2-digit',
    })}`;
}

function updateProgressUI(percent, message) {
    if (!progressEl) return;

    if (percent <= 0 || percent >= 100) {
        if (percent >= 100) {
            progressEl.hidden = false;
            if (progressBarEl) progressBarEl.style.width = '100%';
            if (progressTextEl) progressTextEl.textContent = message;
            setTimeout(() => {
                if (progressEl) progressEl.hidden = true;
            }, 2000);
        } else {
            progressEl.hidden = true;
        }
        return;
    }

    progressEl.hidden = false;
    if (progressBarEl) progressBarEl.style.width = `${percent}%`;
    if (progressTextEl) progressTextEl.textContent = message;
}

async function rememberAndCacheCurrentRoute() {
    const path = window.location.pathname;
    if (!path || path.startsWith('/api') || path === '/offline.html') {
        return;
    }

    localStorage.setItem('wmc_last_app_route', path);
    rememberVisitedRoute(path);

    if (offlineNetwork.isFullyOnline()) {
        await cacheAppShell();
    }
}

async function onServerOnline() {
    await offlineCache.bootstrapIfNeeded(false);
    await rememberAndCacheCurrentRoute();
    await offlineSync.syncAll();
    await updateLastSyncUI();
    await updatePendingUI();
}

function setupOfflineNavigation() {
    document.addEventListener('click', async (event) => {
        if (offlineNetwork.isFullyOnline()) {
            return;
        }

        const link = event.target.closest('a[href]');
        if (!link || link.target === '_blank' || link.hasAttribute('download')) {
            return;
        }

        let url;
        try {
            url = new URL(link.href, window.location.origin);
        } catch {
            return;
        }

        if (url.origin !== window.location.origin || url.pathname.startsWith('/api')) {
            return;
        }

        const cached = await isPageCached(url.pathname);
        if (!cached) {
            event.preventDefault();
            window.alert(
                'Cette page n\'est pas disponible hors connexion.\n\n' +
                    'Ouvrez-la une fois en ligne pour la mettre en cache, ou utilisez le POS / le dashboard.'
            );
        }
    });
}

async function init() {
    try {
        await offlineDb.open();
    } catch (error) {
        console.warn('[WMC Offline] IndexedDB indisponible :', error);
        return;
    }

    offlineNetwork.startMonitoring(() => offlineApi.ping(), 15000);

    await offlineNetwork.checkServerReachability();
    updateStatusUI(offlineNetwork.getMode());
    await updateLastSyncUI();
    await updatePendingUI();

    window.addEventListener('wmc-network-status', () => {
        updateStatusUI(offlineNetwork.getMode());
    });

    window.addEventListener('wmc-network-change', (event) => {
        updateStatusUI(/** @type {CustomEvent} */ (event).detail?.mode);
    });

    window.addEventListener('wmc-network-restored', async () => {
        updateStatusUI('online');
        await onServerOnline();
    });

    window.addEventListener('wmc-offline-sync-progress', (event) => {
        const { percent, message } = /** @type {CustomEvent} */ (event).detail ?? {};
        updateProgressUI(percent ?? 0, message ?? '');
    });

    window.addEventListener('wmc-offline-bootstrap-complete', async (event) => {
        const { syncedAt } = /** @type {CustomEvent} */ (event).detail ?? {};
        await updateLastSyncUI(syncedAt ?? null);
        updateProgressUI(100, 'Synchronisation terminée.');
        if (offlineNetwork.isFullyOnline()) {
            await cacheAppShell();
        }
    });

    window.addEventListener('wmc-offline-pending-change', () => updatePendingUI());

    window.addEventListener('wmc-pages-cached', () => updateCacheHint());

    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.addEventListener('message', (event) => {
            if (event.data?.type === 'WMC_OFFLINE_SYNC') {
                offlineSync.syncAll();
            }
        });
    }

    setupOfflineNavigation();

    if (offlineNetwork.isFullyOnline()) {
        await onServerOnline();
    } else {
        localStorage.setItem('wmc_last_app_route', window.location.pathname);
    }
}

window.WmcOffline = {
    db: offlineDb,
    network: offlineNetwork,
    api: offlineApi,
    cache: offlineCache,
    sync: offlineSync,
    pagesCache: { cacheAppShell, isPageCached, PAGES_CACHE_NAME },
    /** Utiliser pour savoir si on doit travailler en mode local (POS, etc.) */
    canUseLocalData: () => !offlineNetwork.isFullyOnline(),
};

window.dispatchEvent(new CustomEvent('wmc-offline-ready'));

init().catch((error) => {
    console.error('[WMC Offline] Erreur initialisation :', error);
});
