/**
 * WMC CAISSE — Couche API offline
 *
 * Appels HTTP vers le backend Laravel (session + CSRF).
 * En cas d'échec réseau, retourne null sans lever d'erreur bloquante.
 */

export class WmcOfflineApi {
    /**
     * @param {object} [options]
     * @param {string} [options.bootstrapUrl='/api/offline/bootstrap']
     * @param {string} [options.pingUrl='/api/offline/ping']
     */
    constructor(options = {}) {
        this.bootstrapUrl = options.bootstrapUrl ?? '/api/offline/bootstrap';
        this.pingUrl = options.pingUrl ?? '/api/offline/ping';
        this.syncUrl = options.syncUrl ?? '/api/offline/sync';
    }

    /**
     * Obtenir le token CSRF depuis le meta tag.
     * @returns {string}
     */
    getCsrfToken() {
        return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';
    }

    /**
     * Requête fetch avec credentials et CSRF.
     * @param {string} url
     * @param {RequestInit} [options]
     * @returns {Promise<Response|null>}
     */
    async request(url, options = {}) {
        if (!navigator.onLine) {
            return null;
        }

        const headers = new Headers(options.headers ?? {});
        headers.set('Accept', 'application/json');
        headers.set('X-Requested-With', 'XMLHttpRequest');

        const csrf = this.getCsrfToken();
        if (csrf) {
            headers.set('X-CSRF-TOKEN', csrf);
        }

        try {
            const response = await fetch(url, {
                ...options,
                headers,
                credentials: 'same-origin',
            });

            return response;
        } catch {
            return null;
        }
    }

    /**
     * Ping léger pour vérifier que le serveur répond.
     * @returns {Promise<boolean>}
     */
    async ping() {
        const response = await this.request(this.pingUrl, { method: 'GET' });

        if (!response || !response.ok) {
            return false;
        }

        try {
            const data = await response.json();
            return data?.success === true;
        } catch {
            return false;
        }
    }

    /**
     * Télécharger toutes les données pour le bootstrap offline.
     * @returns {Promise<object|null>}
     */
    async fetchBootstrap() {
        const response = await this.request(this.bootstrapUrl, { method: 'GET' });

        if (!response) {
            return null;
        }

        if (!response.ok) {
            let message = `Erreur bootstrap (${response.status})`;
            try {
                const err = await response.json();
                if (err?.message) message = err.message;
            } catch {
                // ignore
            }
            throw new Error(message);
        }

        const data = await response.json();

        if (!data?.success) {
            throw new Error(data?.message ?? 'Bootstrap échoué.');
        }

        return data;
    }

    /**
     * Envoyer un lot d'opérations à synchroniser.
     * @param {Array<{uuid: string, type: string, payload: object}>} operations
     * @returns {Promise<object|null>}
     */
    async postSync(operations) {
        const response = await this.request(this.syncUrl, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ operations }),
        });

        if (!response) {
            return null;
        }

        const data = await response.json();

        if (!response.ok && response.status !== 207) {
            throw new Error(data?.message ?? `Erreur sync (${response.status})`);
        }

        return data;
    }
}

export const offlineApi = new WmcOfflineApi();
