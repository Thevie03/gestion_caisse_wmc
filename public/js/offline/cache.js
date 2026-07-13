/**
 * WMC CAISSE — Cache / Bootstrap
 *
 * Télécharge les données serveur et les enregistre dans IndexedDB.
 * Émet des événements de progression pour l'interface utilisateur.
 */

import { offlineApi } from './api.js';
import { offlineDb } from './indexeddb.js';

export class WmcOfflineCache {
    /**
     * @param {import('./api.js').WmcOfflineApi} api
     * @param {import('./indexeddb.js').WmcOfflineDb} db
     */
    constructor(api, db) {
        this.api = api;
        this.db = db;
    }

    /**
     * @param {number} percent
     * @param {string} message
     */
    #emitProgress(percent, message) {
        window.dispatchEvent(
            new CustomEvent('wmc-offline-sync-progress', {
                detail: { percent, message },
            })
        );
    }

    /**
     * Vérifier si un bootstrap a déjà été effectué pour cette boutique.
     * @returns {Promise<{ bootstrapped: boolean, lastSync: string|null, boutiqueId: number|null }>}
     */
    async getBootstrapStatus() {
        const [lastSync, boutiqueId] = await Promise.all([
            this.db.getMeta('last_sync'),
            this.db.getMeta('boutique_id'),
        ]);

        return {
            bootstrapped: !!lastSync,
            lastSync: typeof lastSync === 'string' ? lastSync : null,
            boutiqueId: typeof boutiqueId === 'number' ? boutiqueId : null,
        };
    }

    /**
     * Télécharger et stocker toutes les données offline.
     * @returns {Promise<object>} Données bootstrap
     */
    async bootstrap() {
        this.#emitProgress(5, 'Connexion au serveur…');

        const data = await this.api.fetchBootstrap();

        if (!data) {
            throw new Error('Impossible de contacter le serveur.');
        }

        this.#emitProgress(15, 'Enregistrement des produits…');
        await this.db.replaceAll('produits', data.produits ?? []);

        this.#emitProgress(35, 'Enregistrement des catégories…');
        await this.db.replaceAll('categories', data.categories ?? []);

        this.#emitProgress(50, 'Enregistrement des clients…');
        await this.db.replaceAll('clients', data.clients ?? []);

        this.#emitProgress(65, 'Enregistrement des fournisseurs…');
        await this.db.replaceAll('fournisseurs', data.fournisseurs ?? []);

        this.#emitProgress(75, 'Enregistrement des utilisateurs…');
        await this.db.replaceAll('utilisateurs', data.utilisateurs ?? []);

        this.#emitProgress(85, 'Enregistrement des paramètres…');
        const parametresRecords = Object.entries(data.parametres ?? {}).map(([cle, valeur]) => ({
            cle,
            valeur,
        }));
        await this.db.replaceAll('parametres', parametresRecords);

        const syncedAt = data.meta?.synced_at ?? new Date().toISOString();

        await this.db.setMeta({
            last_sync: syncedAt,
            boutique_id: data.meta?.boutique_id ?? null,
            bootstrap_version: data.meta?.version ?? 1,
            boutique: data.boutique ?? null,
            user: data.user ?? null,
            counts: data.meta?.counts ?? {},
        });

        this.#emitProgress(100, 'Synchronisation terminée.');

        window.dispatchEvent(
            new CustomEvent('wmc-offline-bootstrap-complete', {
                detail: { syncedAt, counts: data.meta?.counts ?? {} },
            })
        );

        return data;
    }

    /**
     * Bootstrap silencieux si en ligne, sans erreur si hors connexion.
     * @param {boolean} [force=false] Forcer même si déjà bootstrappé
     * @returns {Promise<boolean>} true si bootstrap réussi
     */
    async bootstrapIfNeeded(force = false) {
        const status = await this.getBootstrapStatus();

        if (status.bootstrapped && !force) {
            return true;
        }

        try {
            await this.bootstrap();
            return true;
        } catch (error) {
            console.warn('[WMC Offline] Bootstrap échoué :', error);
            return false;
        }
    }
}

export const offlineCache = new WmcOfflineCache(offlineApi, offlineDb);
