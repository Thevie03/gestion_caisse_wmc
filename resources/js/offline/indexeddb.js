/**
 * WMC CAISSE — IndexedDB
 *
 * Gestion du stockage local pour le mode hors connexion.
 * Stocke : produits, catégories, clients, fournisseurs, paramètres, utilisateurs, file d'attente sync.
 */

export const DB_NAME = 'wmc-caisse-offline';
export const DB_VERSION = 2;

/** @type {Object<string, { keyPath: string, indexes?: Array<{ name: string, keyPath: string, unique?: boolean }> }>} */
export const STORES = {
    meta: { keyPath: 'key' },
    produits: {
        keyPath: 'id',
        indexes: [
            { name: 'nom', keyPath: 'nom', unique: false },
            { name: 'barcode', keyPath: 'barcode', unique: false },
            { name: 'categorie', keyPath: 'categorie', unique: false },
        ],
    },
    categories: { keyPath: 'id', indexes: [{ name: 'nom', keyPath: 'nom', unique: false }] },
    clients: {
        keyPath: 'id',
        indexes: [
            { name: 'nom_complet', keyPath: 'nom_complet', unique: false },
            { name: 'telephone', keyPath: 'telephone', unique: false },
            { name: 'offline_uuid', keyPath: 'offline_uuid', unique: false },
        ],
    },
    fournisseurs: { keyPath: 'id', indexes: [{ name: 'nom', keyPath: 'nom', unique: false }] },
    parametres: { keyPath: 'cle' },
    utilisateurs: { keyPath: 'id' },
    pending_sync: {
        keyPath: 'uuid',
        indexes: [
            { name: 'type', keyPath: 'type', unique: false },
            { name: 'status', keyPath: 'status', unique: false },
            { name: 'created_at', keyPath: 'created_at', unique: false },
        ],
    },
    ventes_local: {
        keyPath: 'uuid',
        indexes: [{ name: 'created_at', keyPath: 'created_at', unique: false }],
    },
};

export class WmcOfflineDb {
    /** @type {IDBDatabase|null} */
    #db = null;

    /** @type {Promise<IDBDatabase>|null} */
    #openPromise = null;

    /**
     * Ouvrir (ou rouvrir) la base IndexedDB.
     * @returns {Promise<IDBDatabase>}
     */
    open() {
        if (this.#db) {
            return Promise.resolve(this.#db);
        }

        if (this.#openPromise) {
            return this.#openPromise;
        }

        this.#openPromise = new Promise((resolve, reject) => {
            if (!('indexedDB' in window)) {
                reject(new Error('IndexedDB non supporté par ce navigateur.'));
                return;
            }

            const request = indexedDB.open(DB_NAME, DB_VERSION);

            request.onupgradeneeded = (event) => {
                const db = /** @type {IDBOpenDBRequest} */ (event.target).result;

                Object.entries(STORES).forEach(([storeName, config]) => {
                    let store;
                    if (!db.objectStoreNames.contains(storeName)) {
                        store = db.createObjectStore(storeName, { keyPath: config.keyPath });
                    } else {
                        store = request.transaction.objectStore(storeName);
                    }

                    config.indexes?.forEach((index) => {
                        if (!store.indexNames.contains(index.name)) {
                            store.createIndex(index.name, index.keyPath, { unique: !!index.unique });
                        }
                    });
                });
            };

            request.onsuccess = () => {
                this.#db = request.result;
                resolve(this.#db);
            };

            request.onerror = () => {
                this.#openPromise = null;
                reject(request.error ?? new Error('Impossible d\'ouvrir IndexedDB.'));
            };
        });

        return this.#openPromise;
    }

    /**
     * Exécuter une transaction en lecture/écriture.
     * @template T
     * @param {string|string[]} storeNames
     * @param {'readonly'|'readwrite'} mode
     * @param {(stores: IDBObjectStore|IDBObjectStore[]) => Promise<T>|T} callback
     * @returns {Promise<T>}
     */
    async transaction(storeNames, mode, callback) {
        const db = await this.open();
        const names = Array.isArray(storeNames) ? storeNames : [storeNames];

        return new Promise((resolve, reject) => {
            const tx = db.transaction(names, mode);
            const stores = names.length === 1 ? tx.objectStore(names[0]) : names.map((n) => tx.objectStore(n));

            Promise.resolve(callback(stores))
                .then(resolve)
                .catch(reject);

            tx.onerror = () => reject(tx.error ?? new Error('Erreur transaction IndexedDB.'));
        });
    }

    /**
     * Remplacer entièrement le contenu d'un store.
     * @param {string} storeName
     * @param {Array<Record<string, unknown>>} records
     */
    async replaceAll(storeName, records) {
        await this.transaction(storeName, 'readwrite', (store) => {
            return new Promise((resolve, reject) => {
                const clearReq = store.clear();
                clearReq.onsuccess = () => {
                    if (!records.length) {
                        resolve(undefined);
                        return;
                    }

                    let pending = records.length;
                    records.forEach((record) => {
                        const putReq = store.put(record);
                        putReq.onsuccess = () => {
                            pending -= 1;
                            if (pending === 0) resolve(undefined);
                        };
                        putReq.onerror = () => reject(putReq.error);
                    });
                };
                clearReq.onerror = () => reject(clearReq.error);
            });
        });
    }

    /**
     * Enregistrer des paramètres clé/valeur dans le store meta.
     * @param {Record<string, unknown>} entries
     */
    async setMeta(entries) {
        await this.transaction('meta', 'readwrite', (store) => {
            return Promise.all(
                Object.entries(entries).map(
                    ([key, value]) =>
                        new Promise((resolve, reject) => {
                            const req = store.put({ key, value, updated_at: new Date().toISOString() });
                            req.onsuccess = () => resolve(undefined);
                            req.onerror = () => reject(req.error);
                        })
                )
            );
        });
    }

    /**
     * Lire une entrée meta.
     * @param {string} key
     * @returns {Promise<unknown|null>}
     */
    async getMeta(key) {
        return this.transaction('meta', 'readonly', (store) => {
            return new Promise((resolve, reject) => {
                const req = store.get(key);
                req.onsuccess = () => resolve(req.result?.value ?? null);
                req.onerror = () => reject(req.error);
            });
        });
    }

    /**
     * Lire tous les enregistrements d'un store.
     * @param {string} storeName
     * @returns {Promise<Array<Record<string, unknown>>>}
     */
    async getAll(storeName) {
        return this.transaction(storeName, 'readonly', (store) => {
            return new Promise((resolve, reject) => {
                const req = store.getAll();
                req.onsuccess = () => resolve(req.result ?? []);
                req.onerror = () => reject(req.error);
            });
        });
    }

    /**
     * Rechercher des produits par nom ou code-barres (insensible à la casse).
     * @param {string} term
     * @param {number} [limit=50]
     * @returns {Promise<Array<Record<string, unknown>>>}
     */
    async searchProduits(term, limit = 50) {
        const normalized = term.trim().toLowerCase();
        if (!normalized) return [];

        const produits = await this.getAll('produits');

        return produits
            .filter((p) => {
                const nom = String(p.nom ?? '').toLowerCase();
                const barcode = String(p.barcode ?? '').toLowerCase();
                const code = String(p.code_produit ?? '').toLowerCase();
                return nom.includes(normalized) || barcode.includes(normalized) || code.includes(normalized);
            })
            .slice(0, limit);
    }

    /**
     * Trouver un produit par code-barres exact.
     * @param {string} barcode
     * @returns {Promise<Record<string, unknown>|null>}
     */
    async findProduitByBarcode(barcode) {
        const normalized = barcode.trim();
        if (!normalized) return null;

        return this.transaction('produits', 'readonly', (store) => {
            return new Promise((resolve, reject) => {
                if (store.indexNames.contains('barcode')) {
                    const req = store.index('barcode').get(normalized);
                    req.onsuccess = () => resolve(req.result ?? null);
                    req.onerror = () => reject(req.error);
                } else {
                    const req = store.getAll();
                    req.onsuccess = () => {
                        const found = (req.result ?? []).find((p) => String(p.barcode) === normalized);
                        resolve(found ?? null);
                    };
                    req.onerror = () => reject(req.error);
                }
            });
        });
    }

    /**
     * Compter les éléments en attente de synchronisation.
     * @returns {Promise<number>}
     */
    async countPendingSync() {
        const items = await this.getAll('pending_sync');
        return items.filter((item) => item.status === 'pending' || item.status === 'syncing').length;
    }

    /**
     * Enregistrer ou mettre à jour un enregistrement.
     * @param {string} storeName
     * @param {Record<string, unknown>} record
     */
    async put(storeName, record) {
        await this.transaction(storeName, 'readwrite', (store) => {
            return new Promise((resolve, reject) => {
                const req = store.put(record);
                req.onsuccess = () => resolve(undefined);
                req.onerror = () => reject(req.error);
            });
        });
    }

    /**
     * Mettre à jour le stock local d'un produit après une vente offline.
     * @param {number} produitId
     * @param {number} quantite
     */
    async decrementProduitStock(produitId, quantite) {
        return this.transaction('produits', 'readwrite', (store) => {
            return new Promise((resolve, reject) => {
                const req = store.get(produitId);
                req.onsuccess = () => {
                    const produit = req.result;
                    if (!produit) {
                        resolve(null);
                        return;
                    }

                    const stockActuel = Number(produit.quantite_stock ?? 0);
                    if (stockActuel > 0) {
                        produit.quantite_stock = Math.max(0, stockActuel - quantite);
                    }

                    const putReq = store.put(produit);
                    putReq.onsuccess = () => resolve(produit);
                    putReq.onerror = () => reject(putReq.error);
                };
                req.onerror = () => reject(req.error);
            });
        });
    }

    /**
     * Mettre à jour une opération dans la file d'attente.
     * @param {string} uuid
     * @param {Record<string, unknown>} updates
     */
    async updatePendingSync(uuid, updates) {
        await this.transaction('pending_sync', 'readwrite', (store) => {
            return new Promise((resolve, reject) => {
                const req = store.get(uuid);
                req.onsuccess = () => {
                    const existing = req.result;
                    if (!existing) {
                        resolve(undefined);
                        return;
                    }

                    const putReq = store.put({ ...existing, ...updates });
                    putReq.onsuccess = () => resolve(undefined);
                    putReq.onerror = () => reject(putReq.error);
                };
                req.onerror = () => reject(req.error);
            });
        });
    }

    /**
     * Récupérer les opérations en attente triées par date.
     * @returns {Promise<Array<Record<string, unknown>>>}
     */
    async getPendingOperations() {
        const items = await this.getAll('pending_sync');
        return items
            .filter((item) => item.status === 'pending' || item.status === 'failed')
            .sort((a, b) => String(a.created_at).localeCompare(String(b.created_at)));
    }
}

/** Instance singleton */
export const offlineDb = new WmcOfflineDb();
