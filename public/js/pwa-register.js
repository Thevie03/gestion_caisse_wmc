/**

 * WMC CAISSE — Enregistrement du Service Worker et gestion de l'installation PWA

 *

 * En local (127.0.0.1) : le Service Worker est désactivé pour ne pas bloquer le dev Laravel.

 * En production : PWA + cache offline actifs.

 */

(function () {

    'use strict';



    const SW_URL = '/service-worker.js?v=1.4.4';

    const SW_SCOPE = '/';



    /** @type {BeforeInstallPromptEvent|null} */

    let deferredInstallPrompt = null;



    function isLocalDev() {

        return ['localhost', '127.0.0.1', '[::1]'].includes(window.location.hostname);

    }



    /**

     * Désinstalle le SW et vide les caches WMC (utile en développement local).

     */

    async function disableLocalServiceWorker() {

        if (!('serviceWorker' in navigator)) {

            return;

        }



        const registrations = await navigator.serviceWorker.getRegistrations();

        for (const registration of registrations) {

            await registration.unregister();

        }



        if ('caches' in window) {

            const keys = await caches.keys();

            await Promise.all(

                keys.filter((key) => key.startsWith('wmc-caisse-')).map((key) => caches.delete(key))

            );

        }



        console.info('[PWA] Mode local : Service Worker et caches WMC désactivés.');

    }



    /**

     * Enregistre le Service Worker et gère les mises à jour.

     */

    async function registerServiceWorker() {

        if (!('serviceWorker' in navigator)) {

            console.info('[PWA] Service Worker non supporté par ce navigateur.');

            return null;

        }



        if (isLocalDev()) {

            await disableLocalServiceWorker();

            return null;

        }



        try {

            const registration = await navigator.serviceWorker.register(SW_URL, {

                scope: SW_SCOPE,

                updateViaCache: 'none',

            });



            console.info('[PWA] Service Worker enregistré.', registration.scope);



            await registration.update();



            if (registration.waiting) {

                registration.waiting.postMessage({ type: 'SKIP_WAITING' });

            }



            registration.addEventListener('updatefound', () => {

                const newWorker = registration.installing;

                if (!newWorker) return;



                newWorker.addEventListener('statechange', () => {

                    if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {

                        window.dispatchEvent(new CustomEvent('wmc-pwa-update-available', {

                            detail: { registration },

                        }));

                    }

                });

            });



            setInterval(() => registration.update(), 60 * 60 * 1000);



            return registration;

        } catch (error) {

            console.error('[PWA] Échec enregistrement Service Worker :', error);

            return null;

        }

    }



    /**

     * Active immédiatement le nouveau Service Worker et recharge la page.

     */

    function applyServiceWorkerUpdate(registration) {

        const waiting = registration.waiting;

        if (!waiting) return;



        waiting.postMessage({ type: 'SKIP_WAITING' });



        navigator.serviceWorker.addEventListener('controllerchange', () => {

            window.location.reload();

        }, { once: true });

    }



    window.addEventListener('beforeinstallprompt', (event) => {

        event.preventDefault();

        deferredInstallPrompt = event;

        window.dispatchEvent(new CustomEvent('wmc-pwa-install-available'));

    });



    async function promptInstall() {

        if (!deferredInstallPrompt) {

            return 'unavailable';

        }



        deferredInstallPrompt.prompt();

        const { outcome } = await deferredInstallPrompt.userChoice;

        deferredInstallPrompt = null;



        if (outcome === 'accepted') {

            window.dispatchEvent(new CustomEvent('wmc-pwa-installed'));

        }



        return outcome;

    }



    function isStandalone() {

        return (

            window.matchMedia('(display-mode: standalone)').matches ||

            window.navigator.standalone === true

        );

    }



    function canInstall() {

        return !!deferredInstallPrompt && !isStandalone();

    }



    window.addEventListener('appinstalled', () => {

        deferredInstallPrompt = null;

        window.dispatchEvent(new CustomEvent('wmc-pwa-installed'));

    });



    window.WmcPwa = {

        registerServiceWorker,

        applyServiceWorkerUpdate,

        promptInstall,

        isStandalone,

        canInstall,

        disableLocalServiceWorker,

        isLocalDev,

    };



    if (document.readyState === 'loading') {

        document.addEventListener('DOMContentLoaded', registerServiceWorker);

    } else {

        registerServiceWorker();

    }

})();


