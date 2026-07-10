/**
 * WMC CAISSE — Initialisation du mode hors connexion (Étape 2)
 *
 * Point d'entrée : démarre la surveillance réseau, le bootstrap
 * et met à jour l'indicateur de connexion dans la topbar.
 */

import { offlineDb } from './indexeddb.js';
import { offlineNetwork } from './network.js';
import { offlineApi } from './api.js';
import { offlineCache } from './cache.js';
import { offlineSync } from './sync.js';

/** @type {HTMLElement|null} */
const statusEl = document.getElementById('wmc-offline-status');
/** @type {HTMLElement|null} */
const pendingEl = document.getElementById('wmc-offline-pending');
/** @type {HTMLElement|null} */
const lastSyncEl = document.getElementById('wmc-offline-last-sync');
/** @type {HTMLElement|null} */
const progressEl = document.getElementById('wmc-offline-progress');
/** @type {HTMLElement|null} */
const progressBarEl = document.getElementById('wmc-offline-progress-bar');
/** @type {HTMLElement|null} */
const progressTextEl = document.getElementById('wmc-offline-progress-text');

/**
 * Mettre à jour l'indicateur visuel de connexion.
 * @param {boolean} online
 */
function updateStatusUI(online) {
    if (!statusEl) return;

    statusEl.dataset.online = online ? '1' : '0';
    statusEl.classList.toggle('wmc-offline-status--online', online);
    statusEl.classList.toggle('wmc-offline-status--offline', !online);

    const label = statusEl.querySelector('.wmc-offline-status__label');
    if (label) {
        label.textContent = online ? 'En ligne' : 'Hors connexion';
    }

    const dot = statusEl.querySelector('.wmc-offline-status__dot');
    if (dot) {
        dot.textContent = online ? '🟢' : '🔴';
    }
}

/**
 * Mettre à jour le compteur de ventes en attente.
 */
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

/**
 * Mettre à jour la date de dernière synchronisation.
 * @param {string|null} [isoDate]
 */
async function updateLastSyncUI(isoDate = null) {
    if (!lastSyncEl) return;

    let dateStr = isoDate;

    if (!dateStr) {
        dateStr = await offlineDb.getMeta('last_sync');
    }

    if (!dateStr || typeof dateStr !== 'string') {
        lastSyncEl.textContent = 'Jamais synchronisé';
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

/**
 * Afficher / masquer la barre de progression.
 * @param {number} percent
 * @param {string} message
 */
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

/**
 * Initialiser le module offline.
 */
async function init() {
    try {
        await offlineDb.open();
    } catch (error) {
        console.warn('[WMC Offline] IndexedDB indisponible :', error);
        return;
    }

    offlineNetwork.startMonitoring(() => offlineApi.ping(), 30000);

    updateStatusUI(offlineNetwork.isFullyOnline());
    await updateLastSyncUI();
    await updatePendingUI();

    window.addEventListener('wmc-network-change', (event) => {
        const online = /** @type {CustomEvent} */ (event).detail?.online ?? false;
        updateStatusUI(online);
    });

    window.addEventListener('wmc-network-restored', async () => {
        updateStatusUI(true);
        await offlineSync.syncAll();
        await offlineCache.bootstrapIfNeeded(true);
        await updateLastSyncUI();
        await updatePendingUI();
    });

    window.addEventListener('wmc-offline-sync-progress', (event) => {
        const { percent, message } = /** @type {CustomEvent} */ (event).detail ?? {};
        updateProgressUI(percent ?? 0, message ?? '');
    });

    window.addEventListener('wmc-offline-bootstrap-complete', async (event) => {
        const { syncedAt } = /** @type {CustomEvent} */ (event).detail ?? {};
        await updateLastSyncUI(syncedAt ?? null);
        updateProgressUI(100, 'Synchronisation terminée.');
    });

    window.addEventListener('wmc-offline-pending-change', () => updatePendingUI());

    // Background Sync via Service Worker
    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.addEventListener('message', (event) => {
            if (event.data?.type === 'WMC_OFFLINE_SYNC') {
                offlineSync.syncAll();
            }
        });
    }

    if (offlineNetwork.isFullyOnline()) {
        await offlineCache.bootstrapIfNeeded(false);
        await updateLastSyncUI();
    }
}

window.WmcOffline = {
    db: offlineDb,
    network: offlineNetwork,
    api: offlineApi,
    cache: offlineCache,
    sync: offlineSync,
};

window.dispatchEvent(new CustomEvent('wmc-offline-ready'));

init().catch((error) => {
    console.error('[WMC Offline] Erreur initialisation :', error);
});
