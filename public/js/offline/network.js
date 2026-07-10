/**
 * WMC CAISSE — Détection de connexion (local + production)
 *
 * En local (127.0.0.1 / localhost), seul compte : est-ce que Laravel répond ?
 * - online        → le serveur répond (php artisan serve OK)
 * - offline       → mode hors connexion simulé (DevTools Offline)
 * - server_down   → réseau OK mais le serveur local ne répond pas
 */

/** @typedef {'online'|'offline'|'server_down'} ConnectionMode */

export class WmcNetwork {
    /** @type {boolean} */
    #browserOnline = navigator.onLine;

    /** @type {boolean} */
    #serverReachable = false;

    /** @type {ConnectionMode} */
    #mode = 'offline';

    /** @type {ReturnType<typeof setInterval>|null} */
    #pingInterval = null;

    /** @type {(() => Promise<boolean>)|null} */
    #pingFn = null;

    constructor() {
        window.addEventListener('online', () => {
            this.#browserOnline = true;
            this.checkServerReachability();
        });
        window.addEventListener('offline', () => {
            this.#browserOnline = false;
            this.#applyMode(this.#serverReachable && false ? 'server_down' : 'offline');
        });
    }

    /** En développement local ? */
    isLocalDev() {
        const host = window.location.hostname;
        return host === 'localhost' || host === '127.0.0.1' || host === '[::1]';
    }

    /**
     * @returns {ConnectionMode}
     */
    getMode() {
        return this.#mode;
    }

    isBrowserOnline() {
        return this.#browserOnline;
    }

    isServerReachable() {
        return this.#serverReachable;
    }

    /** L'application peut parler au serveur Laravel (sync, ventes en ligne). */
    isFullyOnline() {
        return this.#mode === 'online';
    }

    /** Mode hors connexion simulé ou réel — utiliser le cache local. */
    isOfflineMode() {
        return this.#mode === 'offline';
    }

    /** Serveur local arrêté alors que le navigateur est « en ligne ». */
    isServerDown() {
        return this.#mode === 'server_down';
    }

    /**
     * Libellé lisible pour l'utilisateur.
     * @returns {{ dot: string, label: string, hint: string }}
     */
    getStatusDisplay() {
        if (this.isLocalDev()) {
            switch (this.#mode) {
                case 'online':
                    return {
                        dot: '🟢',
                        label: 'Serveur local OK',
                        hint: 'Connecté à Laravel (php artisan serve)',
                    };
                case 'server_down':
                    return {
                        dot: '🟡',
                        label: 'Serveur local arrêté',
                        hint: 'Lancez : php artisan serve',
                    };
                default:
                    return {
                        dot: '🔴',
                        label: 'Mode hors connexion',
                        hint: 'Données locales — sync à la reconnexion',
                    };
            }
        }

        switch (this.#mode) {
            case 'online':
                return {
                    dot: '🟢',
                    label: 'En ligne',
                    hint: 'Connecté au serveur',
                };
            case 'server_down':
                return {
                    dot: '🟡',
                    label: 'Serveur inaccessible',
                    hint: 'Vérifiez la connexion Internet',
                };
            default:
                return {
                    dot: '🔴',
                    label: 'Hors connexion',
                    hint: 'Mode local actif',
                };
        }
    }

    startMonitoring(pingFn, intervalMs = 15000) {
        this.#pingFn = pingFn;
        this.checkServerReachability();

        if (this.#pingInterval) {
            clearInterval(this.#pingInterval);
        }

        this.#pingInterval = setInterval(() => this.checkServerReachability(), intervalMs);
    }

    /**
     * Ping le serveur — même si DevTools Offline (pour détecter le mode simulé).
     * @returns {Promise<boolean>}
     */
    async checkServerReachability() {
        if (!this.#pingFn) {
            this.#updateFromPing(this.#browserOnline);
            return this.#serverReachable;
        }

        try {
            const reachable = await this.#pingFn();
            this.#updateFromPing(reachable);
            return reachable;
        } catch {
            this.#updateFromPing(false);
            return false;
        }
    }

    /**
     * @param {boolean} serverOk
     */
    #updateFromPing(serverOk) {
        this.#serverReachable = serverOk;

        let mode;
        if (serverOk) {
            mode = 'online';
        } else if (!this.#browserOnline) {
            mode = 'offline';
        } else {
            mode = 'server_down';
        }

        this.#applyMode(mode);
    }

    /**
     * @param {ConnectionMode} mode
     */
    #applyMode(mode) {
        const previous = this.#mode;
        this.#mode = mode;

        window.dispatchEvent(
            new CustomEvent('wmc-network-status', {
                detail: {
                    mode,
                    serverReachable: this.#serverReachable,
                    browserOnline: this.#browserOnline,
                    display: this.getStatusDisplay(),
                },
            })
        );

        if (previous !== mode) {
            window.dispatchEvent(
                new CustomEvent('wmc-network-change', {
                    detail: {
                        mode,
                        online: mode === 'online',
                        display: this.getStatusDisplay(),
                    },
                })
            );
        }

        if (previous !== 'online' && mode === 'online') {
            window.dispatchEvent(new CustomEvent('wmc-network-restored', { detail: { mode: 'online' } }));
        }
    }
}

export const offlineNetwork = new WmcNetwork();
