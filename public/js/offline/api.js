/**
 * WMC CAISSE — Couche API offline (timeouts courts)
 */

import { NETWORK_TIMEOUT_MS } from './offline-config.js';

export class WmcOfflineApi {
    constructor(options = {}) {
        this.bootstrapUrl = options.bootstrapUrl ?? '/api/offline/bootstrap';
        this.pingUrl = options.pingUrl ?? '/api/offline/ping';
        this.syncUrl = options.syncUrl ?? '/api/offline/sync';
        this.defaultTimeout = options.timeout ?? NETWORK_TIMEOUT_MS;
    }

    getCsrfToken() {
        return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';
    }

    async fetchWithTimeout(url, options = {}, timeoutMs = this.defaultTimeout) {
        const controller = new AbortController();
        const timer = setTimeout(() => controller.abort(), timeoutMs);

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
                signal: controller.signal,
            });
            clearTimeout(timer);
            return response;
        } catch {
            clearTimeout(timer);
            return null;
        }
    }

    async request(url, options = {}, timeoutMs = this.defaultTimeout) {
        return this.fetchWithTimeout(url, options, timeoutMs);
    }

    async ping() {
        const response = await this.fetchWithTimeout(this.pingUrl, { method: 'GET' }, 4000);
        if (!response?.ok) {
            return false;
        }
        try {
            const data = await response.json();
            return data?.success === true;
        } catch {
            return false;
        }
    }

    async fetchBootstrap() {
        const response = await this.request(this.bootstrapUrl, { method: 'GET' }, 12000);

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
        const response = await this.request(
            this.syncUrl,
            {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ operations }),
            },
            15000
        );

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
