/**
 * Menu latéral mobile — toggle fiable (iOS PWA, touch, Bootstrap-safe)
 */
(function () {
    'use strict';

    var MOBILE_MAX = 991.98;
    var OPEN_CLASS = 'is-open';
    var lastToggleAt = 0;

    function getSidebar() {
        return document.getElementById('sidebar');
    }

    function getOverlay() {
        return document.querySelector('.sidebar-overlay');
    }

    function isMobileViewport() {
        return window.matchMedia('(max-width: ' + MOBILE_MAX + 'px)').matches;
    }

    function setAriaExpanded(open) {
        document.querySelectorAll('[data-sidebar-toggle]').forEach(function (btn) {
            btn.setAttribute('aria-expanded', open ? 'true' : 'false');
        });
    }

    function openSidebar() {
        var sidebar = getSidebar();
        var overlay = getOverlay();
        if (!sidebar || !isMobileViewport()) {
            return;
        }

        sidebar.classList.add(OPEN_CLASS);
        if (overlay) {
            overlay.classList.add(OPEN_CLASS);
        }
        document.body.classList.add('sidebar-open');
        setAriaExpanded(true);
    }

    function closeSidebar() {
        var sidebar = getSidebar();
        var overlay = getOverlay();
        if (sidebar) {
            sidebar.classList.remove(OPEN_CLASS);
        }
        if (overlay) {
            overlay.classList.remove(OPEN_CLASS);
        }
        document.body.classList.remove('sidebar-open');
        setAriaExpanded(false);
    }

    function toggleSidebar() {
        var sidebar = getSidebar();
        if (!sidebar || !isMobileViewport()) {
            return;
        }

        if (sidebar.classList.contains(OPEN_CLASS)) {
            closeSidebar();
        } else {
            openSidebar();
        }
    }

    function handleToggleEvent(event) {
        var toggle = event.target.closest('[data-sidebar-toggle]');
        if (!toggle) {
            return;
        }

        event.preventDefault();
        event.stopPropagation();

        var now = Date.now();
        if (now - lastToggleAt < 350) {
            return;
        }
        lastToggleAt = now;

        toggleSidebar();
    }

    function bindSidebarLinks() {
        var sidebar = getSidebar();
        if (!sidebar) {
            return;
        }

        sidebar.querySelectorAll('.nav-link').forEach(function (link) {
            link.addEventListener('click', function () {
                if (isMobileViewport()) {
                    closeSidebar();
                }
            });
        });
    }

    function applyIosSafeAreaInsets() {
        var isApple = /iphone|ipad|ipod/i.test(navigator.userAgent);
        var isStandalone = window.matchMedia('(display-mode: standalone)').matches ||
            window.navigator.standalone === true;

        if (!isApple) {
            return;
        }

        var probe = document.createElement('div');
        probe.style.cssText = 'position:fixed;top:0;left:0;padding:env(safe-area-inset-top);visibility:hidden;pointer-events:none;';
        document.body.appendChild(probe);
        var measured = parseFloat(window.getComputedStyle(probe).paddingTop) || 0;
        document.body.removeChild(probe);

        var fallback = isStandalone ? 59 : 47;
        var effective = Math.max(measured, fallback);

        document.documentElement.style.setProperty('--wmc-safe-top-effective', effective + 'px');
    }

    function init() {
        applyIosSafeAreaInsets();
        window.addEventListener('orientationchange', function () {
            setTimeout(applyIosSafeAreaInsets, 150);
        });

        document.addEventListener('click', handleToggleEvent, true);
        document.addEventListener('touchend', handleToggleEvent, { passive: false, capture: true });

        var overlay = getOverlay();
        if (overlay) {
            overlay.addEventListener('click', function (event) {
                event.preventDefault();
                closeSidebar();
            });
        }

        bindSidebarLinks();

        window.addEventListener('resize', function () {
            if (!isMobileViewport()) {
                closeSidebar();
            }
        });

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') {
                closeSidebar();
            }
        });
    }

    window.closeSidebar = closeSidebar;
    window.toggleSidebar = toggleSidebar;

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
