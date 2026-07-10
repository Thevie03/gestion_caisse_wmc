/**
 * WMC CAISSE — Détection réseau
 *
 * Surveille l'état de connexion et émet des événements personnalisés.
 */

export class WmcNetwork {
    /** @type {boolean} */
    #online = navigator.onLine;

    /** @type {boolean} */
    #serverReachable = navigator.onLine;

    /** @type {ReturnType<typeof setInterval>|null} */
    #pingInterval = null;

    constructor() {
        window.addEventListener('online', () => this.#setOnline(true));
        window.addEventListener('offline', () => this.#setOnline(false));
    }

    /**
     * Démarrer la surveillance réseau avec ping serveur périodique.
     * @param {() => Promise<boolean>} pingFn
     * @param {number} [intervalMs=30000]
     */
    startMonitoring(pingFn, intervalMs = 30000) {
        this.#pingFn = pingFn;

        this.checkServerReachability();

        if (this.#pingInterval) {
            clearInterval(this.#pingInterval);
        }

        this.#pingInterval = setInterval(() => this.checkServerReachability(), intervalMs);
    }

    /** @type {(() => Promise<boolean>)|null} */
    #pingFn = null;

    /**
     * Vérifier si le navigateur est en ligne (API navigator.onLine).
     */
    isOnline() {
        return this.#online;
    }

    /**
     * Vérifier si le serveur Laravel répond.
     */
    isServerReachable() {
        return this.#serverReachable;
    }

    /**
     * État combiné : en ligne ET serveur accessible.
     */
    isFullyOnline() {
        return this.#online && this.#serverReachable;
    }

    /**
     * Ping le serveur pour confirmer la disponibilité réelle.
     * @returns {Promise<boolean>}
     */
    async checkServerReachability() {
        if (!navigator.onLine) {
            this.#setServerReachable(false);
            return false;
        }

        if (!this.#pingFn) {
            this.#setServerReachable(true);
            return true;
        }

        try {
            const reachable = await this.#pingFn();
            this.#setServerReachable(reachable);
            return reachable;
        } catch {
            this.#setServerReachable(false);
            return false;
        }
    }

    /**
     * @param {boolean} online
     */
    #setOnline(online) {
        const changed = this.#online !== online;
        this.#online = online;

        if (!online) {
            this.#setServerReachable(false);
        } else if (this.#pingFn) {
            this.checkServerReachability();
        }

        if (changed) {
            this.#emit('wmc-network-change', { online: this.isFullyOnline() });
        }
    }

    /**
     * @param {boolean} reachable
     */
    #setServerReachable(reachable) {
        const wasFullyOnline = this.isFullyOnline();
        this.#serverReachable = reachable;
        const isFullyOnline = this.isFullyOnline();

        if (wasFullyOnline !== isFullyOnline) {
            this.#emit('wmc-network-change', { online: isFullyOnline });
        }

        if (!wasFullyOnline && isFullyOnline) {
            this.#emit('wmc-network-restored', { online: true });
        }
    }

    /**
     * @param {string} name
     * @param {object} detail
     */
    #emit(name, detail) {
        window.dispatchEvent(new CustomEvent(name, { detail }));
    }
}

export const offlineNetwork = new WmcNetwork();
