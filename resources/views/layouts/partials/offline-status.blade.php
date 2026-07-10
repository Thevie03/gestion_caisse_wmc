{{--
    WMC CAISSE — Indicateur de connexion (local + production)
--}}
<div class="wmc-offline-widget d-none d-md-flex align-items-center me-2" id="wmc-offline-widget">
    <div class="wmc-offline-status wmc-offline-status--online" id="wmc-offline-status" data-online="1" data-mode="online"
        title="État de la connexion">
        <span class="wmc-offline-status__dot" aria-hidden="true">🟢</span>
        <span class="wmc-offline-status__label">Serveur local OK</span>
    </div>

    <div class="wmc-offline-meta ms-2">
        <small class="wmc-offline-hint text-muted d-block" id="wmc-offline-hint">—</small>
        <small class="wmc-offline-pending text-warning" id="wmc-offline-pending" hidden></small>
        <small class="wmc-offline-last-sync text-muted d-block" id="wmc-offline-last-sync">—</small>
    </div>
</div>

<div class="wmc-offline-progress wmc-offline-progress--topbar" id="wmc-offline-progress" hidden>
    <div class="wmc-offline-progress__bar-wrap">
        <div class="wmc-offline-progress__bar" id="wmc-offline-progress-bar" style="width: 0%"></div>
    </div>
    <small class="wmc-offline-progress__text" id="wmc-offline-progress-text"></small>
</div>

<style>
    .wmc-offline-widget {
        font-size: 0.78rem;
        line-height: 1.2;
        max-width: 240px;
    }

    .wmc-offline-status {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.25rem 0.5rem;
        border-radius: 999px;
        border: 1px solid var(--border-light, #374151);
        background: var(--bg-secondary, rgba(31, 41, 55, 0.6));
        white-space: nowrap;
    }

    .wmc-offline-status--online {
        border-color: rgba(34, 197, 94, 0.35);
    }

    .wmc-offline-status--offline {
        border-color: rgba(239, 68, 68, 0.45);
        background: rgba(127, 29, 29, 0.15);
    }

    .wmc-offline-status--warning {
        border-color: rgba(245, 158, 11, 0.55);
        background: rgba(120, 53, 15, 0.2);
    }

    .wmc-offline-status__label {
        font-weight: 600;
        color: var(--text-primary, #f9fafb);
        font-size: 0.75rem;
    }

    .wmc-offline-meta .wmc-offline-hint {
        font-size: 0.68rem;
        line-height: 1.15;
        max-width: 220px;
    }

    .wmc-offline-meta .wmc-offline-last-sync {
        font-size: 0.68rem;
        line-height: 1.1;
    }

    .wmc-offline-meta .wmc-offline-pending {
        font-size: 0.68rem;
        font-weight: 600;
    }

    .wmc-offline-progress--topbar {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        z-index: 10000;
        background: var(--bg-secondary, #1f2937);
        border-bottom: 1px solid var(--border-light, #374151);
        padding: 0.35rem 1rem;
    }

    .wmc-offline-progress__bar-wrap {
        height: 4px;
        background: rgba(255, 255, 255, 0.1);
        border-radius: 2px;
        overflow: hidden;
        margin-bottom: 0.25rem;
    }

    .wmc-offline-progress__bar {
        height: 100%;
        background: #F59E0B;
        border-radius: 2px;
        transition: width 0.3s ease;
    }

    .wmc-offline-progress__text {
        color: var(--text-secondary, #9ca3af);
        font-size: 0.75rem;
    }
</style>

<script>
    (function () {
        const mobileEl = document.getElementById('wmc-offline-status-mobile');
        const desktopEl = document.getElementById('wmc-offline-status');

        function syncMobileIndicator() {
            if (!mobileEl || !desktopEl) return;
            const dot = desktopEl.querySelector('.wmc-offline-status__dot');
            if (dot) {
                mobileEl.querySelector('.wmc-offline-status__dot').textContent = dot.textContent;
            }
            if (desktopEl.title) {
                mobileEl.title = desktopEl.title;
            }
        }

        window.addEventListener('wmc-network-status', syncMobileIndicator);
        window.addEventListener('wmc-network-change', syncMobileIndicator);

        if (desktopEl) {
            new MutationObserver(syncMobileIndicator).observe(desktopEl, {
                attributes: true,
                attributeFilter: ['data-online', 'data-mode', 'class'],
                subtree: true,
            });
        }
    })();
</script>
