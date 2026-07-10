<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
    @php
$htmlClasses = [];
        // Toujours appliquer une classe de thème, même pour 'default'
        // Forcer la récupération depuis la session ou la boutique
        $themeToApply = isset($theme) ? $theme : 'default';

        // Si pas de thème explicite, réutiliser la valeur de session pour éviter une requête SQL à chaque page
        if ($themeToApply === 'default' && session()->has('theme_color')) {
            $themeToApply = session('theme_color', 'default');
        }

        $htmlClasses[] = 'theme-' . $themeToApply;

        // Ajouter le style de thème si défini
        if (isset($themeStyle)) {
            $htmlClasses[] = 'theme-style-' . $themeStyle;
        }
        $htmlClass = 'class="' . implode(' ', $htmlClasses) . '"'; @endphp
    {!! $htmlClass !!} data-theme="{{ $themeToApply }}" style="color-scheme: dark;">

<head>
    <meta charset="utf-8">
    <meta name="viewport"
        content="width=device-width, initial-scale=1, maximum-scale=5, user-scalable=yes, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!-- Forcer le thème clair même si le navigateur est en mode sombre -->
    <meta name="color-scheme" content="dark light">

    <title>{{ config('app.name', 'GestionCaisse') }}</title>

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('images/logos/logo_wmc_orange.png') }}">

    {{-- PWA : manifest, meta tags iOS/Android --}}
    @include('layouts.partials.pwa-head')

    <!-- Optimisation : Preconnect aux CDN pour améliorer les temps de chargement -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link rel="preconnect" href="https://cdnjs.cloudflare.com" crossorigin>
    <link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>

    <!-- Fonts (avec display=swap pour éviter le blocage de rendu) -->
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

    <!-- Font Awesome (avec crossorigin pour la sécurité) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"
        crossorigin="anonymous">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"
        crossorigin="anonymous">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">

    <!-- Chart.js (chargé dans le head pour être disponible tôt) -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js" crossorigin="anonymous"></script>

</head>

<body>
    <!-- Overlay pour fermer la sidebar sur mobile -->
    <div class="sidebar-overlay" onclick="closeSidebar()"></div>

    <div class="d-flex flex-column flex-md-row app-wrapper">
        <!-- Sidebar -->
        @include('layouts.sidebar')

        <!-- Main Content -->
        <div class="flex-grow-1 d-flex flex-column main-wrapper">
            <!-- Topbar -->
            @include('layouts.topbar')

            <!-- Page Content -->
            <main class="main-content container-fluid">
                @php
                    $activeBoutiqueId = session('boutique_active') ?: auth()->user()?->boutique_id;
                    $activeBoutiqueForHero = $activeBoutiqueId
                        ? \App\Services\CacheService::getBoutique($activeBoutiqueId)
                        : null;
                    $sharedHeroImage = !empty($activeBoutiqueForHero?->pos_banner_image)
                        ? asset('images/pos/' . basename($activeBoutiqueForHero->pos_banner_image))
                        : asset('images/pos/pos-banner.jpg');

                    // Afficher le hero uniquement sur les modules demandés.
                    // Le Point de vente (ventes.pos) garde son hero dédié dans sa propre vue.
                    $showContextHero = request()->routeIs('dashboard');

                    $heroConfig = [
                        'title' => 'GestionCaisse WMC',
                        'subtitle' => 'Pilotez votre activité plus vite avec une interface claire et visuelle.',
                        'badge' => 'Espace de gestion',
                        'icon' => 'fas fa-layer-group',
                        'image' => asset('images/pos/pos-banner.jpg'),
                        'thumb1' => asset('images/pos/pos-stock.jpg'),
                        'thumb2' => asset('images/pos/pos-payment.jpg'),
                    ];

                    if (request()->routeIs('dashboard')) {
                        $heroConfig = [
                            'title' => 'Tableau de bord',
                            'subtitle' => 'Suivez vos ventes, stocks et performances en un coup d\'oeil.',
                            'badge' => 'Pilotage',
                            'icon' => 'fas fa-tachometer-alt',
                            'image' => $sharedHeroImage,
                            'thumb1' => $sharedHeroImage,
                            'thumb2' => $sharedHeroImage,
                        ];
                    } elseif (request()->routeIs('ventes.*')) {
                        $heroConfig = [
                            'title' => 'Ventes',
                            'subtitle' => 'Encaissez rapidement et gardez un suivi propre de chaque transaction.',
                            'badge' => 'Commerce',
                            'icon' => 'fas fa-shopping-cart',
                            'image' => asset('images/pos/pos-banner.jpg'),
                            'thumb1' => asset('images/pos/pos-stock.jpg'),
                            'thumb2' => asset('images/pos/pos-payment.jpg'),
                        ];
                    } elseif (
                        request()->routeIs('produits.*') ||
                        request()->routeIs('categories.*') ||
                        request()->routeIs('stock.*')
                    ) {
                        $heroConfig = [
                            'title' => 'Produits & Stock',
                            'subtitle' => 'Gardez un catalogue propre et un stock maitrise en temps reel.',
                            'badge' => 'Inventaire',
                            'icon' => 'fas fa-boxes-stacked',
                            'image' => $sharedHeroImage,
                            'thumb1' => $sharedHeroImage,
                            'thumb2' => $sharedHeroImage,
                        ];
                    } elseif (request()->routeIs('clients.*') || request()->routeIs('fournisseurs.*')) {
                        $heroConfig = [
                            'title' => 'Relations commerciales',
                            'subtitle' => 'Centralisez clients et fournisseurs pour un meilleur suivi quotidien.',
                            'badge' => 'Contacts',
                            'icon' => 'fas fa-users',
                            'image' => asset('images/pos/pos-payment.jpg'),
                            'thumb1' => asset('images/pos/pos-stock.jpg'),
                            'thumb2' => asset('images/pos/pos-banner.jpg'),
                        ];
                    } elseif (
                        request()->routeIs('depenses.*') ||
                        request()->routeIs('rapports.*') ||
                        request()->routeIs('factures.*')
                    ) {
                        $heroConfig = [
                            'title' => 'Finance & Rapports',
                            'subtitle' => 'Controlez les flux financiers et analysez vos resultats facilement.',
                            'badge' => 'Financier',
                            'icon' => 'fas fa-chart-line',
                            'image' => asset('images/pos/pos-payment.jpg'),
                            'thumb1' => asset('images/pos/pos-banner.jpg'),
                            'thumb2' => asset('images/pos/pos-stock.jpg'),
                        ];
                    } elseif (
                        request()->routeIs('admin.*') ||
                        request()->routeIs('boutiques.*') ||
                        request()->routeIs('employes.*') ||
                        request()->routeIs('archives.*') ||
                        request()->routeIs('historique.*') ||
                        request()->routeIs('tickets.*') ||
                        request()->routeIs('conditions-generales.*') ||
                        request()->routeIs('notifications.*')
                    ) {
                        $heroConfig = [
                            'title' => 'Administration',
                            'subtitle' => 'Gerez les parametres, equipes et operations de votre environnement.',
                            'badge' => 'Organisation',
                            'icon' => 'fas fa-shield-alt',
                            'image' => asset('images/pos/pos-banner.jpg'),
                            'thumb1' => asset('images/pos/pos-payment.jpg'),
                            'thumb2' => asset('images/pos/pos-stock.jpg'),
                        ];
                    }
                @endphp

                @if ($showContextHero)
                    <section class="wmc-context-hero mb-4">
                        <div class="row g-0 overflow-hidden rounded-4 border wmc-context-hero-shell">
                            <div
                                class="col-12 col-lg-5 p-4 p-lg-5 d-flex flex-column justify-content-center wmc-context-hero-left">
                                <span class="badge rounded-pill px-3 py-2 mb-3 wmc-context-hero-badge">
                                    <i class="{{ $heroConfig['icon'] }} me-1"></i>{{ $heroConfig['badge'] }}
                                </span>
                                <h2 class="fw-bold mb-2 wmc-context-hero-title">{{ $heroConfig['title'] }}</h2>
                                <p class="mb-4 text-muted wmc-context-hero-subtitle">{{ $heroConfig['subtitle'] }}</p>

                                <div class="row g-2">
                                    <div class="col-6">
                                        <div
                                            class="position-relative overflow-hidden rounded-3 border wmc-context-hero-thumb-wrap">
                                            <img src="{{ $heroConfig['thumb1'] }}" alt=""
                                                class="w-100 wmc-context-hero-thumb" loading="lazy" decoding="async">
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div
                                            class="position-relative overflow-hidden rounded-3 border wmc-context-hero-thumb-wrap">
                                            <img src="{{ $heroConfig['thumb2'] }}" alt=""
                                                class="w-100 wmc-context-hero-thumb" loading="lazy" decoding="async">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12 col-lg-7 p-0 position-relative">
                                <img src="{{ $heroConfig['image'] }}" alt=""
                                    class="w-100 wmc-context-hero-main" fetchpriority="high" decoding="async">
                                <div class="position-absolute bottom-0 start-0 end-0 p-3 wmc-context-hero-overlay">
                                    <small class="text-white fw-semibold">Design visuel adapte au module actuel</small>
                                </div>
                            </div>
                        </div>
                    </section>
                @endif

                <!-- Messages d'alerte -->
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i>
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if (session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if (session('warning'))
                    <div class="alert alert-warning alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        {{ session('warning') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if (session('info'))
                    <div class="alert alert-info alert-dismissible fade show" role="alert">
                        <i class="fas fa-info-circle me-2"></i>
                        {{ session('info') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if (isset($header))
                    <div
                        class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-3">
                        <h1 class="h3 mb-0">{{ $header }}</h1>
                        @if (isset($headerActions))
                            <div class="w-100 w-md-auto">
                                {{ $headerActions }}
                            </div>
                        @endif
                    </div>
                @endif

                @yield('content')
            </main>

            <!-- Footer -->
            <footer class="mt-auto text-center text-muted border-top app-footer">
                <div class="app-footer-content">
                    <img src="{{ asset('images/logos/logo_wmc_orange.png') }}" alt="WMC" class="footer-wmc-logo"
                        loading="lazy" decoding="async">
                    <span class="app-footer-separator" aria-hidden="true"></span>
                    <p class="mb-0 app-footer-text">© 2026 – WMC. Tous droits réservés. Application conçue et développée par WMC.</p>
                </div>
            </footer>
        </div>
    </div>

    <!-- Mobile Sidebar Toggle Script -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const sidebarToggle = document.getElementById('sidebarToggle');
            const sidebar = document.getElementById('sidebar');
            const sidebarOverlay = document.querySelector('.sidebar-overlay');

            if (sidebarToggle && sidebar) {
                sidebarToggle.addEventListener('click', function(e) {
                    e.preventDefault();
                    sidebar.classList.add('show');
                    if (sidebarOverlay) {
                        sidebarOverlay.classList.add('show');
                    }
                });
            }

            // Fermer la sidebar en cliquant sur l'overlay
            if (sidebarOverlay) {
                sidebarOverlay.addEventListener('click', function() {
                    closeSidebar();
                });
            }

            // Fermer la sidebar en cliquant sur un lien
            const sidebarLinks = sidebar.querySelectorAll('.nav-link');
            sidebarLinks.forEach(link => {
                link.addEventListener('click', function() {
                    if (window.innerWidth < 768) {
                        closeSidebar();
                    }
                });
            });
        });

        function closeSidebar() {
            const sidebar = document.getElementById('sidebar');
            const sidebarOverlay = document.querySelector('.sidebar-overlay');
            if (sidebar) {
                sidebar.classList.remove('show');
            }
            if (sidebarOverlay) {
                sidebarOverlay.classList.remove('show');
            }
        }

        // Fermer la sidebar au redimensionnement vers desktop
        window.addEventListener('resize', function() {
            if (window.innerWidth >= 768) {
                closeSidebar();
            }
        });
    </script>

    <!-- Optimisation : Charger les scripts de manière asynchrone pour améliorer les performances -->
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous" defer>
    </script>

    <!-- Alpine.js (déjà en defer, optimisé) -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.13.3/dist/cdn.min.js" crossorigin="anonymous"></script>

    <!-- Fallback pour les CDN qui ne se chargent pas -->
    <script>
        // Vérifier si Chart.js est chargé après un court délai
        setTimeout(function() {
            if (typeof Chart === 'undefined') {
                console.warn('Chart.js n\'a pas pu être chargé depuis jsdelivr, tentative avec cdnjs...');
                // Tentative de chargement alternatif depuis cdnjs
                const chartScript = document.createElement('script');
                chartScript.src = 'https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.0/chart.umd.min.js';
                chartScript.crossOrigin = 'anonymous';
                chartScript.onload = function() {
                    console.log('Chart.js chargé depuis cdnjs');
                };
                chartScript.onerror = function() {
                    console.error('Impossible de charger Chart.js depuis les CDN');
                };
                document.head.appendChild(chartScript);
            }
        }, 500);

        // Vérifier si Bootstrap est chargé (après un délai car il est en defer)
        setTimeout(function() {
            if (typeof bootstrap === 'undefined') {
                console.error('Bootstrap n\'a pas pu être chargé depuis le CDN');
            }
        }, 1000);

        // Vérifier si Alpine.js est chargé (après un court délai car il est en defer)
        setTimeout(function() {
            if (typeof Alpine === 'undefined') {
                console.error('Alpine.js n\'a pas pu être chargé depuis le CDN');
            }
        }, 1000);
    </script>

    <!-- Script pour rafraîchir automatiquement le token CSRF -->
    <script>
        (function() {
            // Rafraîchir le token CSRF toutes les 60 minutes (avant l'expiration de la session)
            const REFRESH_INTERVAL = 60 * 60 * 1000; // 60 minutes en millisecondes

            function refreshCsrfToken() {
                fetch('/csrf-token', {
                        method: 'GET',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute(
                                'content') || ''
                        },
                        credentials: 'same-origin'
                    })
                    .then(response => {
                        if (!response.ok) {
                            // Si la requête échoue silencieusement, ne rien faire
                            return null;
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data && data.csrf_token) {
                            // Mettre à jour le meta tag CSRF
                            const metaTag = document.querySelector('meta[name="csrf-token"]');
                            if (metaTag) {
                                metaTag.setAttribute('content', data.csrf_token);
                            }

                            // Mettre à jour tous les champs cachés _token dans les formulaires
                            const tokenInputs = document.querySelectorAll('input[name="_token"]');
                            tokenInputs.forEach(input => {
                                input.value = data.csrf_token;
                            });
                        }
                    })
                    .catch(error => {
                        // Erreur silencieuse - ne pas perturber l'utilisateur
                        console.debug('Rafraîchissement du token CSRF:', error);
                    });
            }

            // Attendre que le DOM soit chargé
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', function() {
                    // Rafraîchir le token toutes les 60 minutes
                    setInterval(refreshCsrfToken, REFRESH_INTERVAL);
                });
            } else {
                // DOM déjà chargé
                setInterval(refreshCsrfToken, REFRESH_INTERVAL);
            }
        })();
    </script>

    @stack('scripts')

    <style>
        .wmc-context-hero-shell {
            border-color: var(--border-light);
            box-shadow: var(--shadow-md);
        }

        .wmc-context-hero-left {
            background: linear-gradient(135deg, var(--bg-primary) 0%, var(--bg-tertiary) 100%);
        }

        .wmc-context-hero-badge {
            background: rgba(var(--primary-color-rgb), 0.2);
            color: var(--primary-color);
            border: 1px solid rgba(var(--primary-color-rgb), 0.4);
            font-weight: 600;
            width: fit-content;
        }

        .wmc-context-hero-title {
            color: var(--text-primary);
            font-size: clamp(1.35rem, 2.5vw, 1.75rem);
        }

        .wmc-context-hero-subtitle {
            max-width: 38rem;
            font-size: 0.95rem;
        }

        .wmc-context-hero-thumb-wrap {
            border-color: var(--border-light);
        }

        .wmc-context-hero-thumb {
            height: 64px;
            object-fit: cover;
            display: block;
        }

        .wmc-context-hero-main {
            height: 170px;
            object-fit: cover;
            display: block;
        }

        .wmc-context-hero-overlay {
            background: linear-gradient(180deg, rgba(0, 0, 0, 0) 0%, rgba(0, 0, 0, 0.75) 100%);
        }

        .wmc-context-hero-overlay small {
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.5);
        }

        @media (min-width: 992px) {
            .wmc-context-hero-main {
                height: 100%;
                min-height: 200px;
                max-height: 240px;
            }
        }
    </style>

    <!-- Script pour forcer l'application du thème -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Vérifier si le thème est correctement appliqué
            const htmlElement = document.documentElement;
            const currentTheme = htmlElement.getAttribute('data-theme') || htmlElement.className.match(
                /theme-(\w+)/)?.[1] || 'default';

            // Forcer l'application des variables CSS
            const themeColors = {
                'default': {
                    color: '#F59E0B',
                    dark: '#D97706',
                    light: 'rgba(245, 158, 11, 0.18)',
                    lighter: 'rgba(245, 158, 11, 0.1)',
                    rgb: '245, 158, 11'
                },
                'blue': {
                    color: '#2563eb',
                    dark: '#1e40af',
                    light: '#eff6ff',
                    lighter: '#eff6ff',
                    rgb: '37, 99, 235'
                },
                'green': {
                    color: '#059669',
                    dark: '#047857',
                    light: '#ecfdf5',
                    lighter: '#ecfdf5',
                    rgb: '5, 150, 105'
                },
                'purple': {
                    color: '#7c3aed',
                    dark: '#6d28d9',
                    light: '#f5f3ff',
                    lighter: '#f5f3ff',
                    rgb: '124, 58, 237'
                },
                'orange': {
                    color: '#F59E0B',
                    dark: '#D97706',
                    light: 'rgba(245, 158, 11, 0.18)',
                    lighter: 'rgba(245, 158, 11, 0.1)',
                    rgb: '245, 158, 11'
                },
                'abaya': {
                    color: '#dc2626',
                    dark: '#991b1b',
                    light: '#fef2f2',
                    lighter: '#fef2f2',
                    rgb: '220, 38, 38'
                }
            };

            if (themeColors[currentTheme]) {
                const colors = themeColors[currentTheme];
                htmlElement.style.setProperty('--primary-color', colors.color, 'important');
                htmlElement.style.setProperty('--primary-dark', colors.dark, 'important');
                htmlElement.style.setProperty('--primary-light', colors.light, 'important');
                htmlElement.style.setProperty('--primary-lighter', colors.lighter, 'important');
                htmlElement.style.setProperty('--primary-color-rgb', colors.rgb, 'important');
            }

            // Si un paramètre theme_updated est présent, forcer le rechargement
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.has('theme_updated')) {
                // Attendre un peu pour que le CSS se charge, puis recharger
                setTimeout(function() {
                    const newUrl = window.location.pathname + window.location.search.replace(
                        /[?&]theme_updated=[^&]*/, '').replace(/[?&]v=[^&]*/, '');
                    window.location.href = newUrl || window.location.pathname;
                }, 500);
            }
        });
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const rows = document.querySelectorAll('.main-content .row');

            rows.forEach((row) => {
                if (row.classList.contains('wmc-skip-auto-grid')) {
                    return;
                }

                const columns = Array.from(row.children).filter((child) => /(^|\s)col-/.test(child.className));
                if (columns.length < 2) {
                    return;
                }

                const isEligible = columns.every((col) => {
                    const className = col.className || '';
                    const hasCardAsDirectChild = !!col.querySelector(':scope > .card');
                    const hasSupportedWidthClass = /\bcol-(md|lg|xl|xxl)-(3|4|6)\b/.test(className);
                    return hasCardAsDirectChild && hasSupportedWidthClass;
                });

                if (!isEligible) {
                    return;
                }

                // 4 cartes en col-*-3 : Bootstrap = 4 par ligne ; ne pas forcer la grille 3 colonnes.
                const quatreCartesEnTrois = columns.length === 4 && columns.every((col) => {
                    const className = col.className || '';
                    return /\bcol-(?:sm|md|lg|xl|xxl)-3\b/.test(className);
                });

                if (quatreCartesEnTrois) {
                    return;
                }

                row.classList.add('wmc-two-cards-auto');
                columns.forEach((col) => col.classList.add('wmc-two-card-col'));
            });
        });
    </script>

    {{-- PWA : enregistrement Service Worker + bannière d'installation --}}
    <script src="{{ asset('js/pwa-register.js') }}?v=1.3.5" defer></script>
    @include('layouts.partials.pwa-scripts')

    {{-- Mode hors connexion : IndexedDB + bootstrap (Étape 2) --}}
    @include('layouts.partials.offline-scripts')
</body>

</html>
