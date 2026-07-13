/**
 * WMC CAISSE — Hydratation des pages depuis IndexedDB (mode offline)
 *
 * Lit les données locales et met à jour l'interface sans requête réseau.
 */

import { offlineDb } from './indexeddb.js';
import { offlineNetwork } from './network.js';
import { offlineCache } from './cache.js';

const BANNER_ID = 'wmc-offline-page-banner';

/** @type {Record<string, (db: import('./indexeddb.js').WmcOfflineDb) => Promise<void>>} */
const ROUTE_HANDLERS = {
    '/dashboard': hydrateDashboard,
    '/produits': hydrateProduits,
    '/categories': hydrateCategories,
    '/clients': hydrateClients,
    '/fournisseurs': hydrateFournisseurs,
    '/stock': hydrateStock,
    '/depenses': hydrateDepenses,
    '/rapports': hydrateRapports,
};

function normalizePath(path) {
    if (!path || path === '/') {
        return '/dashboard';
    }
    return path.endsWith('/') && path.length > 1 ? path.slice(0, -1) : path;
}

function escapeHtml(value) {
    return String(value ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}

function formatMoney(value) {
    const n = Number(value) || 0;
    return n.toLocaleString('fr-FR', { maximumFractionDigits: 0 }) + ' FCFA';
}

function showBanner(message, variant = 'warning') {
    let banner = document.getElementById(BANNER_ID);
    if (!banner) {
        banner = document.createElement('div');
        banner.id = BANNER_ID;
        banner.className = 'alert alert-warning alert-dismissible fade show mb-3';
        banner.setAttribute('role', 'alert');
        const main = document.querySelector('.main-content');
        if (main) {
            main.prepend(banner);
        } else {
            document.body.prepend(banner);
        }
    }

    banner.className = `alert alert-${variant} alert-dismissible fade show mb-3`;
    banner.innerHTML = `
        <i class="fas fa-wifi-slash me-2"></i>
        <strong>Mode hors connexion</strong> — ${escapeHtml(message)}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
    `;
}

function updateStatValues(values) {
    const cards = document.querySelectorAll('.stat-card .stat-value, .dashboard-stat-card .stat-value');
    const keys = Object.keys(values);
    cards.forEach((el, index) => {
        const key = keys[index];
        if (key && values[key] !== undefined) {
            el.textContent = values[key];
        }
    });
}

async function hydrateDashboard(db) {
    const counts = (await db.getMeta('counts')) || {};
    const produits = await db.getAll('produits');
    const clients = await db.getAll('clients');
    const pending = await db.countPendingSync();

    updateStatValues({
        produits: counts.produits ?? produits.length,
        clients: counts.clients ?? clients.length,
        pending,
    });

    const charts = document.querySelectorAll('canvas');
    charts.forEach((canvas) => {
        const wrap = canvas.closest('.card-body');
        if (wrap && !wrap.querySelector('.wmc-offline-chart-note')) {
            const note = document.createElement('p');
            note.className = 'wmc-offline-chart-note text-muted small mt-2 mb-0';
            note.textContent = 'Graphiques indisponibles hors connexion — données locales affichées.';
            wrap.appendChild(note);
        }
    });
}

async function hydrateProduits(db) {
    const produits = await db.getAll('produits');
    const stockFaible = produits.filter(
        (p) => Number(p.quantite_stock) > 0 && Number(p.quantite_stock) <= Number(p.stock_minimum ?? 0)
    ).length;
    const rupture = produits.filter((p) => Number(p.quantite_stock) <= 0).length;

    updateStatValues({
        total: produits.length,
        stock_faible: stockFaible,
        rupture,
        actifs: produits.filter((p) => p.actif !== false).length,
    });

    const tbody = document.querySelector('.saas-table tbody, table.table tbody');
    if (!tbody || produits.length === 0) {
        return;
    }

    tbody.innerHTML = produits
        .slice(0, 200)
        .map(
            (p) => `
        <tr data-wmc-offline-row="1">
            <td><div class="wmc-table-thumb wmc-table-thumb--placeholder d-flex align-items-center justify-content-center"><i class="fas fa-box text-muted"></i></div></td>
            <td><strong>${escapeHtml(p.nom)}</strong><br><small class="text-muted">${escapeHtml(p.code_produit || p.barcode || '')}</small></td>
            <td><span class="badge ${Number(p.quantite_stock) <= 0 ? 'bg-danger' : 'bg-success'}">${Number(p.quantite_stock)}</span></td>
            <td>—</td>
            <td>${formatMoney(p.prix_vente)}</td>
            <td>—</td>
            <td>Local</td>
            <td><span class="badge bg-secondary">Offline</span></td>
        </tr>`
        )
        .join('');
}

async function hydrateCategories(db) {
    const categories = await db.getAll('categories');
    const tbody = document.querySelector('table tbody');
    if (!tbody || !categories.length) {
        updateStatValues({ total: categories.length });
        return;
    }

    tbody.innerHTML = categories
        .map(
            (c) => `
        <tr>
            <td><strong>${escapeHtml(c.nom)}</strong></td>
            <td>${escapeHtml(c.description || '—')}</td>
            <td><span class="badge ${c.active !== false ? 'bg-success' : 'bg-secondary'}">${c.active !== false ? 'Actif' : 'Inactif'}</span></td>
            <td><span class="badge bg-secondary">Offline</span></td>
        </tr>`
        )
        .join('');
}

async function hydrateClients(db) {
    const clients = await db.getAll('clients');
    const actifs = clients.filter((c) => c.actif !== false).length;

    updateStatValues({
        total_clients: clients.length,
        clients_actifs: actifs,
        clients_inactifs: clients.length - actifs,
    });

    const tbody = document.querySelector('table tbody');
    if (!tbody || !clients.length) {
        return;
    }

    tbody.innerHTML = clients
        .slice(0, 150)
        .map(
            (c) => `
        <tr>
            <td><strong>${escapeHtml(c.nom_complet || c.nom || 'Client')}</strong></td>
            <td>${escapeHtml(c.email || '—')}</td>
            <td>${escapeHtml(c.telephone || '—')}</td>
            <td>${escapeHtml(c.ville || '—')}</td>
            <td><span class="badge bg-info">${Number(c.solde_points || 0)} pts</span></td>
            <td><span class="badge ${c.actif !== false ? 'bg-success' : 'bg-danger'}">${c.actif !== false ? 'Actif' : 'Inactif'}</span></td>
            <td><span class="badge bg-secondary">Offline</span></td>
        </tr>`
        )
        .join('');
}

async function hydrateFournisseurs(db) {
    const fournisseurs = await db.getAll('fournisseurs');
    updateStatValues({ total: fournisseurs.length });

    const tbody = document.querySelector('table tbody');
    if (!tbody || !fournisseurs.length) {
        return;
    }

    tbody.innerHTML = fournisseurs
        .map(
            (f) => `
        <tr>
            <td><strong>${escapeHtml(f.nom)}</strong></td>
            <td>${escapeHtml(f.telephone || '—')}</td>
            <td>${escapeHtml(f.email || '—')}</td>
            <td><span class="badge ${f.actif !== false ? 'bg-success' : 'bg-secondary'}">${f.actif !== false ? 'Actif' : 'Inactif'}</span></td>
            <td><span class="badge bg-secondary">Offline</span></td>
        </tr>`
        )
        .join('');
}

async function hydrateStock(db) {
    const produits = await db.getAll('produits');
    updateStatValues({
        total: produits.length,
        rupture: produits.filter((p) => Number(p.quantite_stock) <= 0).length,
    });

    const tbody = document.querySelector('table tbody');
    if (!tbody) {
        return;
    }

    tbody.innerHTML = produits
        .slice(0, 150)
        .map(
            (p) => `
        <tr>
            <td>${escapeHtml(p.nom)}</td>
            <td>${escapeHtml(p.categorie || '—')}</td>
            <td><span class="badge ${Number(p.quantite_stock) <= Number(p.stock_minimum) ? 'bg-warning' : 'bg-success'}">${Number(p.quantite_stock)}</span></td>
            <td>${Number(p.stock_minimum ?? 0)}</td>
            <td><span class="badge bg-secondary">Offline</span></td>
        </tr>`
        )
        .join('');
}

async function hydrateDepenses() {
    showBanner('Les dépenses nécessitent le serveur pour l\'historique complet. Consultation limitée.', 'info');
}

async function hydrateRapports() {
    showBanner('Les rapports PDF nécessitent le serveur. Statistiques locales disponibles sur le dashboard.', 'info');
}

function notifyServiceWorkerOffline(forceOffline) {
    if ('serviceWorker' in navigator && navigator.serviceWorker.controller) {
        navigator.serviceWorker.controller.postMessage({
            type: 'SET_FORCE_OFFLINE',
            value: forceOffline,
        });
    }
}

/**
 * Hydrater la page courante si hors ligne.
 * @returns {Promise<void>}
 */
export async function hydrateCurrentPage() {
    if (offlineNetwork.isFullyOnline()) {
        notifyServiceWorkerOffline(false);
        return;
    }

    notifyServiceWorkerOffline(true);

    const bootstrapped = await offlineDb.getMeta('last_sync');
    if (!bootstrapped) {
        showBanner(
            'Aucune donnée locale. Connectez-vous une fois en ligne pour télécharger le catalogue.',
            'danger'
        );
        return;
    }

    const lastSync = await offlineDb.getMeta('last_sync');
    const syncLabel = lastSync ? new Date(String(lastSync)).toLocaleString('fr-FR') : '—';
    showBanner(`Données locales (sync : ${syncLabel}). Synchronisation automatique au retour du réseau.`);

    const path = normalizePath(window.location.pathname);
    const handler = ROUTE_HANDLERS[path];

    if (handler) {
        try {
            await handler(offlineDb);
        } catch (error) {
            console.warn('[WMC Hydrate] Erreur', path, error);
        }
    }
}

/**
 * Écouter les changements réseau et hydrater / resync.
 */
export function startHydrationListener() {
    window.addEventListener('wmc-network-change', async (event) => {
        const mode = /** @type {CustomEvent} */ (event).detail?.mode;
        notifyServiceWorkerOffline(mode !== 'online');

        if (mode !== 'online') {
            await hydrateCurrentPage();
        } else {
            const banner = document.getElementById(BANNER_ID);
            if (banner) {
                banner.remove();
            }
            offlineCache.bootstrapIfNeeded(false).catch(() => {});
        }
    });

    window.addEventListener('wmc-offline-bootstrap-complete', () => {
        if (!offlineNetwork.isFullyOnline()) {
            hydrateCurrentPage();
        }
    });
}

export const offlineHydrate = {
    hydrateCurrentPage,
    startHydrationListener,
    notifyServiceWorkerOffline,
};
