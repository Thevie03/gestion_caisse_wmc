/**
 * WMC CAISSE — Initialisation mode hors connexion (Offline-First)
 */

import { offlineDb } from './indexeddb.js';
import { offlineNetwork } from './network.js';
import { offlineApi } from './api.js';
import { offlineCache } from './cache.js';
import { offlineSync } from './sync.js';
import { cacheAppShell, isPageCached, PAGES_CACHE_NAME, APP_PAGES_TO_CACHE } from './pages-cache.js';
import { offlineHydrate } from './hydrate.js';

const statusEl = document.getElementById('wmc-offline-status');
const hintEl = document.getElementById('wmc-offline-hint');
const pendingEl = document.getElementById('wmc-offline-pending');
const lastSyncEl = document.getElementById('wmc-offline-last-sync');
const progressEl = document.getElementById('wmc-offline-progress');
const progressBarEl = document.getElementById('wmc-offline-progress-bar');
const progressTextEl = document.getElementById('wmc-offline-progress-text');

function notifySwOffline(force) {
    offlineHydrate.notifyServiceWorkerOffline(force);
}

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
    notifySwOffline(!isOnline);

    if (offlineNetwork.isLocalDev()) {
        updateCacheHint();
    }
}

async function updateCacheHint() {
    if (!hintEl || !offlineNetwork.isLocalDev()) return;

    let cachedCount = 0;
    for (const path of APP_PAGES_TO_CACHE.slice(0, 4)) {
        if (await isPageCached(path)) cachedCount += 1;
    }

    const display = offlineNetwork.getStatusDisplay();
    const cacheNote =
        cachedCount > 0
            ? ` — ${cachedCount}+ pages en cache`
            : ' — visitez l\'app en ligne pour le cache';

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

    if (offlineNetwork.isFullyOnline()) {
        await cacheAppShell();
    }
}

async function onServerOnline() {
    notifySwOffline(false);
    await offlineCache.bootstrapIfNeeded(false);
    await rememberAndCacheCurrentRoute();
    await offlineSync.syncAll();
    await updateLastSyncUI();
    await updatePendingUI();
}

async function init() {
    try {
        await offlineDb.open();
    } catch (error) {
        console.warn('[WMC Offline] IndexedDB indisponible :', error);
        return;
    }

    offlineHydrate.startHydrationListener();

    offlineNetwork.startMonitoring(() => offlineApi.ping(), 8000);

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
        if (!offlineNetwork.isFullyOnline()) {
            await offlineHydrate.hydrateCurrentPage();
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

    if (offlineNetwork.isFullyOnline()) {
        await onServerOnline();
    } else {
        localStorage.setItem('wmc_last_app_route', window.location.pathname);
        await offlineHydrate.hydrateCurrentPage();
    }
}

window.WmcOffline = {
    db: offlineDb,
    network: offlineNetwork,
    api: offlineApi,
    cache: offlineCache,
    sync: offlineSync,
    hydrate: offlineHydrate,
    pagesCache: { cacheAppShell, isPageCached, PAGES_CACHE_NAME, APP_PAGES_TO_CACHE },
    canUseLocalData: () => !offlineNetwork.isFullyOnline(),
};

window.dispatchEvent(new CustomEvent('wmc-offline-ready'));

init().catch((error) => {
    console.error('[WMC Offline] Erreur initialisation :', error);
});
