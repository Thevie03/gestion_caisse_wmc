/**
 * WMC CAISSE — Couche API offline
 *
 * Appels HTTP vers Laravel. Ne bloque pas sur navigator.onLine :
 * on tente toujours le fetch (utile en local avec Service Worker).
 */

export class WmcOfflineApi {
    constructor(options = {}) {
        this.bootstrapUrl = options.bootstrapUrl ?? '/api/offline/bootstrap';
        this.pingUrl = options.pingUrl ?? '/api/offline/ping';
        this.syncUrl = options.syncUrl ?? '/api/offline/sync';
    }

    getCsrfToken() {
        return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';
    }

    async request(url, options = {}) {
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
                cache: 'no-store',
            });

            return response;
        } catch {
            return null;
        }
    }

    /** Ping — timeout court pour ne pas bloquer l'UI. */
    async ping() {
        const controller = new AbortController();
        const timeout = setTimeout(() => controller.abort(), 4000);

        try {
            const response = await fetch(this.pingUrl, {
                method: 'GET',
                credentials: 'same-origin',
                cache: 'no-store',
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                signal: controller.signal,
            });

            clearTimeout(timeout);

            if (!response.ok) {
                return false;
            }

            const data = await response.json();
            return data?.success === true;
        } catch {
            clearTimeout(timeout);
            return false;
        }
    }

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
