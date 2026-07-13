/**
 * WMC CAISSE — Configuration offline partagée (client)
 * Doit rester alignée avec public/service-worker.js (CACHE_VERSION, pages).
 */

export const OFFLINE_CACHE_VERSION = 'wmc-caisse-v1.4.0';
export const PAGES_CACHE_NAME = `${OFFLINE_CACHE_VERSION}-pages`;

/** Pages à mettre en cache lors du bootstrap (session requise) */
export const APP_PAGES_TO_CACHE = [
    '/dashboard',
    '/ventes/pos/interface',
    '/produits',
    '/categories',
    '/clients',
    '/fournisseurs',
    '/stock',
    '/depenses',
    '/rapports',
    '/profile',
    '/ventes',
];

/** Timeout réseau navigation / API (ms) */
export const NETWORK_TIMEOUT_MS = 3500;
