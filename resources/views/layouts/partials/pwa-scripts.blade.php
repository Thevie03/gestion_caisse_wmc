{{--
    WMC CAISSE — Bannière d'installation PWA + notification de mise à jour
--}}
<div id="wmc-pwa-install-banner" class="wmc-pwa-install-banner" hidden aria-live="polite">
    <div class="wmc-pwa-install-banner__content">
        <img src="{{ asset('images/icons/icon-192.png') }}" alt="" width="40" height="40" class="wmc-pwa-install-banner__icon">
        <div class="wmc-pwa-install-banner__text">
            <strong>Installer WMC Caisse</strong>
            <span id="wmc-pwa-install-hint">Accédez à l'application depuis votre écran d'accueil, même hors connexion.</span>
        </div>
        <div class="wmc-pwa-install-banner__actions">
            <button type="button" class="btn btn-sm btn-warning" id="wmc-pwa-install-btn">
                <i class="fas fa-download me-1"></i> Installer
            </button>
            <button type="button" class="btn btn-sm btn-link text-muted" id="wmc-pwa-install-dismiss" aria-label="Fermer">
                <i class="fas fa-times"></i>
            </button>
        </div>
    </div>
</div>

<div id="wmc-pwa-update-banner" class="wmc-pwa-update-banner" hidden aria-live="polite">
    <div class="wmc-pwa-update-banner__content">
        <i class="fas fa-sync-alt me-2"></i>
        <span>Une nouvelle version est disponible.</span>
        <button type="button" class="btn btn-sm btn-warning ms-2" id="wmc-pwa-update-btn">Mettre à jour</button>
        <button type="button" class="btn btn-sm btn-link text-muted ms-1" id="wmc-pwa-update-dismiss" aria-label="Fermer">
            <i class="fas fa-times"></i>
        </button>
    </div>
</div>

<style>
    .wmc-pwa-install-banner,
    .wmc-pwa-update-banner {
        position: fixed;
        bottom: max(1rem, env(safe-area-inset-bottom));
        left: 50%;
        transform: translateX(-50%);
        z-index: 9999;
        width: calc(100% - 2rem);
        max-width: 520px;
    }

    .wmc-pwa-install-banner__content,
    .wmc-pwa-update-banner__content {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        background: var(--bg-secondary, #1f2937);
        border: 1px solid var(--border-light, #374151);
        border-radius: 0.75rem;
        padding: 0.875rem 1rem;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.35);
        color: var(--text-primary, #f9fafb);
        font-size: 0.875rem;
    }

    .wmc-pwa-install-banner__icon {
        border-radius: 0.5rem;
        flex-shrink: 0;
    }

    .wmc-pwa-install-banner__text {
        flex: 1;
        display: flex;
        flex-direction: column;
        gap: 0.15rem;
        min-width: 0;
    }

    .wmc-pwa-install-banner__text span {
        color: var(--text-secondary, #9ca3af);
        font-size: 0.8rem;
        line-height: 1.35;
    }

    .wmc-pwa-install-banner__actions {
        display: flex;
        align-items: center;
        gap: 0.25rem;
        flex-shrink: 0;
    }

    .wmc-pwa-update-banner__content {
        justify-content: center;
        flex-wrap: wrap;
    }

    @media (max-width: 480px) {
        .wmc-pwa-install-banner__content {
            flex-wrap: wrap;
        }

        .wmc-pwa-install-banner__actions {
            width: 100%;
            justify-content: flex-end;
        }
    }
</style>

<script>
    (function () {
        'use strict';

        const INSTALL_DISMISS_KEY = 'wmc_pwa_install_dismissed';
        const installBanner = document.getElementById('wmc-pwa-install-banner');
        const installHint = document.getElementById('wmc-pwa-install-hint');
        const installBtn = document.getElementById('wmc-pwa-install-btn');
        const updateBanner = document.getElementById('wmc-pwa-update-banner');
        let pendingRegistration = null;

        const ua = navigator.userAgent.toLowerCase();
        const isIos = /iphone|ipad|ipod/.test(ua);
        const isAndroid = /android/.test(ua);
        const isHttps = location.protocol === 'https:' || location.hostname === 'localhost';

        function isDismissed() {
            return !!localStorage.getItem(INSTALL_DISMISS_KEY);
        }

        function showInstallBanner(mode) {
            if (!installBanner || !window.WmcPwa) return;
            if (window.WmcPwa.isStandalone()) return;
            if (isDismissed()) return;

            if (mode === 'prompt' && window.WmcPwa.canInstall()) {
                if (installHint) {
                    installHint.textContent = 'Accédez à l\'application depuis votre écran d\'accueil, même hors connexion.';
                }
                if (installBtn) installBtn.hidden = false;
                installBanner.hidden = false;
                return;
            }

            if (mode === 'manual') {
                if (!isHttps && !window.WmcPwa.isLocalDev()) {
                    if (installHint) {
                        installHint.textContent = 'L\'installation PWA nécessite HTTPS (https://gestioncaisse.wmcci.com).';
                    }
                } else if (isAndroid) {
                    if (installHint) {
                        installHint.textContent = 'Ouvrez d\'abord la page de connexion, puis Chrome ⋮ → « Installer l\'application ».';
                    }
                } else if (isIos) {
                    if (installHint) {
                        installHint.textContent = 'Ouvrez https://gestioncaisse.wmcci.com/app dans Safari, puis Partager → « Sur l\'écran d\'accueil ».';
                    }
                } else {
                    if (installHint) {
                        installHint.textContent = 'Utilisez le menu du navigateur pour installer l\'application.';
                    }
                }
                if (installBtn) installBtn.hidden = true;
                installBanner.hidden = false;
            }
        }

        function hideInstallBanner() {
            if (installBanner) installBanner.hidden = true;
        }

        installBtn?.addEventListener('click', async () => {
            if (window.WmcPwa) {
                const result = await window.WmcPwa.promptInstall();
                if (result === 'unavailable') {
                    showInstallBanner('manual');
                } else {
                    hideInstallBanner();
                }
            }
        });

        document.getElementById('wmc-pwa-install-dismiss')?.addEventListener('click', () => {
            localStorage.setItem(INSTALL_DISMISS_KEY, '1');
            hideInstallBanner();
        });

        window.addEventListener('wmc-pwa-install-available', () => showInstallBanner('prompt'));
        window.addEventListener('wmc-pwa-installed', hideInstallBanner);

        window.addEventListener('wmc-pwa-update-available', (event) => {
            pendingRegistration = event.detail?.registration ?? null;
            if (updateBanner) updateBanner.hidden = false;
        });

        document.getElementById('wmc-pwa-update-btn')?.addEventListener('click', () => {
            if (window.WmcPwa && pendingRegistration) {
                window.WmcPwa.applyServiceWorkerUpdate(pendingRegistration);
            }
        });

        document.getElementById('wmc-pwa-update-dismiss')?.addEventListener('click', () => {
            if (updateBanner) updateBanner.hidden = true;
        });

        // Android / iOS : instructions manuelles si pas de prompt natif
        window.addEventListener('load', () => {
            setTimeout(() => {
                if (!window.WmcPwa || window.WmcPwa.isStandalone() || isDismissed()) return;
                var onPublicEntry = /^\/(login|app)?(\?|$)/.test(window.location.pathname + window.location.search)
                    || window.location.pathname === '/';
                if (window.WmcPwa.canInstall() && onPublicEntry) {
                    showInstallBanner('prompt');
                } else if ((isAndroid || isIos) && onPublicEntry) {
                    showInstallBanner('manual');
                }
            }, 2500);
        });
    })();
</script>
