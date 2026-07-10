/**
 * WMC CAISSE — Synchronisation automatique (Étape 3)
 *
 * Gère la file d'attente, l'enregistrement offline des ventes/clients
 * et l'envoi automatique au retour de la connexion.
 */

import { offlineDb } from './indexeddb.js';
import { offlineApi } from './api.js';
import { printOfflineTicket } from './ticket.js';

/** @typedef {'vente'|'client'|'paiement'|'stock'} SyncType */
/** @typedef {'pending'|'syncing'|'synced'|'failed'} SyncStatus */

export class WmcOfflineSync {
    constructor(db, api) {
        this.db = db;
        this.api = api;
        this.#isSyncing = false;
    }

    #isSyncing;

    static generateUuid() {
        if (crypto.randomUUID) {
            return crypto.randomUUID();
        }

        return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, (c) => {
            const r = (Math.random() * 16) | 0;
            const v = c === 'x' ? r : (r & 0x3) | 0x8;
            return v.toString(16);
        });
    }

    async enqueue(type, payload) {
        const uuid = WmcOfflineSync.generateUuid();
        const record = {
            uuid,
            type,
            payload,
            status: /** @type {SyncStatus} */ ('pending'),
            created_at: new Date().toISOString(),
            synced_at: null,
            error: null,
        };

        await this.db.put('pending_sync', record);

        window.dispatchEvent(new CustomEvent('wmc-offline-pending-change', { detail: { uuid, type } }));

        this.registerBackgroundSync();

        return uuid;
    }

    async countPending() {
        return this.db.countPendingSync();
    }

    async countPendingVentes() {
        const items = await this.db.getAll('pending_sync');
        return items.filter(
            (item) => item.type === 'vente' && (item.status === 'pending' || item.status === 'syncing' || item.status === 'failed')
        ).length;
    }

    /**
     * Enregistrer une vente créée hors connexion.
     * @param {object} venteData
     * @returns {Promise<{ uuid: string, numero_local: string }>}
     */
    async saveVenteOffline(venteData) {
        const uuid = WmcOfflineSync.generateUuid();
        const boutiqueId = await this.db.getMeta('boutique_id');
        const boutique = await this.db.getMeta('boutique');
        const numeroLocal = `OFF-${Date.now().toString().slice(-8)}`;

        const payload = {
            ...venteData,
            boutique_id: boutiqueId,
            created_at: new Date().toISOString(),
        };

        for (const ligne of payload.produits ?? []) {
            await this.db.decrementProduitStock(ligne.id, ligne.quantite);
        }

        await this.db.put('pending_sync', {
            uuid,
            type: 'vente',
            payload,
            status: 'pending',
            created_at: payload.created_at,
            synced_at: null,
            error: null,
        });

        const venteLocale = {
            uuid,
            numero_local: numeroLocal,
            ...payload,
            boutique,
            synced: false,
        };

        await this.db.put('ventes_local', venteLocale);

        window.dispatchEvent(new CustomEvent('wmc-offline-pending-change', { detail: { uuid, type: 'vente' } }));
        this.registerBackgroundSync();

        printOfflineTicket(venteLocale);

        return { uuid, numero_local: numeroLocal };
    }

    /**
     * Enregistrer un client créé hors connexion.
     * @param {object} clientData
     * @returns {Promise<{ uuid: string, local_id: string }>}
     */
    async saveClientOffline(clientData) {
        const uuid = WmcOfflineSync.generateUuid();
        const boutiqueId = await this.db.getMeta('boutique_id');
        const localId = `local-${uuid}`;

        const payload = {
            ...clientData,
            boutique_id: boutiqueId,
            created_at: new Date().toISOString(),
        };

        await this.db.put('pending_sync', {
            uuid,
            type: 'client',
            payload,
            status: 'pending',
            created_at: payload.created_at,
            synced_at: null,
            error: null,
        });

        await this.db.put('clients', {
            id: localId,
            offline_uuid: uuid,
            nom: clientData.nom ?? '',
            prenom: clientData.prenom ?? 'Client',
            nom_complet: `${clientData.prenom ?? 'Client'} ${clientData.nom ?? ''}`.trim(),
            telephone: clientData.telephone ?? '',
            email: clientData.email ?? null,
            actif: true,
            local: true,
        });

        window.dispatchEvent(new CustomEvent('wmc-offline-pending-change', { detail: { uuid, type: 'client' } }));
        this.registerBackgroundSync();

        return { uuid, local_id: localId };
    }

    /**
     * Synchroniser toutes les opérations en attente.
     * @returns {Promise<{ synced: number, failed: number }>}
     */
    async syncAll() {
        if (this.#isSyncing) {
            return { synced: 0, failed: 0 };
        }

        const reachable = await this.api.ping();
        if (!reachable) {
            return { synced: 0, failed: 0 };
        }

        const pending = await this.db.getPendingOperations();
        if (!pending.length) {
            return { synced: 0, failed: 0 };
        }

        this.#isSyncing = true;
        let synced = 0;
        let failed = 0;
        const total = pending.length;

        window.dispatchEvent(
            new CustomEvent('wmc-offline-sync-progress', {
                detail: { percent: 5, message: `Synchronisation de ${total} opération(s)…` },
            })
        );

        try {
            const batchSize = 20;

            for (let i = 0; i < pending.length; i += batchSize) {
                const batch = pending.slice(i, i + batchSize);

                for (const item of batch) {
                    await this.db.updatePendingSync(item.uuid, { status: 'syncing', error: null });
                }

                const operations = batch.map((item) => ({
                    uuid: item.uuid,
                    type: item.type,
                    payload: item.payload,
                }));

                const percent = Math.round(((i + batch.length) / total) * 90) + 5;

                window.dispatchEvent(
                    new CustomEvent('wmc-offline-sync-progress', {
                        detail: { percent, message: `Envoi ${Math.min(i + batch.length, total)}/${total}…` },
                    })
                );

                try {
                    const response = await this.api.postSync(operations);

                    if (!response) {
                        for (const item of batch) {
                            await this.db.updatePendingSync(item.uuid, {
                                status: 'pending',
                                error: 'Serveur inaccessible',
                            });
                        }
                        failed += batch.length;
                        continue;
                    }

                    const successUuids = new Set((response.results ?? []).map((r) => r.uuid));

                    for (const item of batch) {
                        if (successUuids.has(item.uuid)) {
                            const result = response.results.find((r) => r.uuid === item.uuid);
                            await this.db.updatePendingSync(item.uuid, {
                                status: 'synced',
                                synced_at: new Date().toISOString(),
                                server_result: result ?? null,
                                error: null,
                            });

                            if (item.type === 'vente') {
                                await this.#markVenteLocaleSynced(item.uuid, result);
                            }

                            synced += 1;
                        } else {
                            const err = (response.errors ?? []).find((e) => e.uuid === item.uuid);
                            await this.db.updatePendingSync(item.uuid, {
                                status: 'failed',
                                error: err?.message ?? 'Échec de synchronisation',
                            });
                            failed += 1;
                        }
                    }
                } catch (error) {
                    for (const item of batch) {
                        await this.db.updatePendingSync(item.uuid, {
                            status: 'failed',
                            error: error instanceof Error ? error.message : 'Erreur inconnue',
                        });
                    }
                    failed += batch.length;
                }
            }

            if (synced > 0) {
                const { offlineCache } = await import('./cache.js');
                await offlineCache.bootstrapIfNeeded(true);
                await this.db.setMeta({ last_sync: new Date().toISOString() });
            }

            window.dispatchEvent(
                new CustomEvent('wmc-offline-sync-progress', {
                    detail: {
                        percent: 100,
                        message: synced > 0
                            ? `${synced} opération(s) synchronisée(s)${failed ? `, ${failed} échec(s)` : ''}`
                            : 'Aucune synchronisation effectuée',
                    },
                })
            );

            window.dispatchEvent(
                new CustomEvent('wmc-offline-bootstrap-complete', {
                    detail: { syncedAt: new Date().toISOString() },
                })
            );
        } finally {
            this.#isSyncing = false;
            window.dispatchEvent(new CustomEvent('wmc-offline-pending-change'));
        }

        return { synced, failed };
    }

    async #markVenteLocaleSynced(uuid, result) {
        await this.db.transaction('ventes_local', 'readwrite', (store) => {
            return new Promise((resolve, reject) => {
                const req = store.get(uuid);
                req.onsuccess = () => {
                    const vente = req.result;
                    if (!vente) {
                        resolve(undefined);
                        return;
                    }

                    vente.synced = true;
                    vente.server_id = result?.id ?? null;
                    vente.numero_vente = result?.numero_vente ?? vente.numero_local;

                    const putReq = store.put(vente);
                    putReq.onsuccess = () => resolve(undefined);
                    putReq.onerror = () => reject(putReq.error);
                };
                req.onerror = () => reject(req.error);
            });
        });
    }

    /**
     * Enregistrer Background Sync si disponible.
     */
    registerBackgroundSync() {
        if (!('serviceWorker' in navigator) || !('SyncManager' in window)) {
            return;
        }

        navigator.serviceWorker.ready
            .then((registration) => registration.sync.register('wmc-offline-sync'))
            .catch(() => {
                // Background Sync non disponible — sync manuelle via network-restored
            });
    }
}

export const offlineSync = new WmcOfflineSync(offlineDb, offlineApi);
