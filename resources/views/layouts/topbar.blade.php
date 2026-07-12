<!-- Topbar -->
<header class="topbar">
    @php
        $resolvedBoutiqueName = $boutiqueActive->nom ?? auth()->user()?->boutique?->nom ?? 'Boutique';
        $resolvedLogo = $logo ?? ($boutiqueActive->logo ?? auth()->user()?->boutique?->logo ?? null);

        if (!$resolvedLogo && auth()->check() && session('boutique_active')) {
            $selectedBoutique = \App\Services\CacheService::getBoutique(session('boutique_active'));
            if ($selectedBoutique) {
                $resolvedLogo = $selectedBoutique->logo;
                $resolvedBoutiqueName = $selectedBoutique->nom ?: $resolvedBoutiqueName;
            }
        }

        $pageTitle = $resolvedBoutiqueName ?? config('app.name', 'GestionCaisse');

        $user = auth()->user();
        $boutiques = collect();
        if ($user) {
            if ($user->isSuperAdmin()) {
                $boutiques = \App\Services\CacheService::getActiveBoutiques();
            } elseif ($user->isOwner()) {
                $boutiques = \Illuminate\Support\Facades\Cache::remember(
                    'topbar_owner_boutiques_' . $user->id,
                    now()->addSeconds(60),
                    fn() => $user->ownedBoutiques()
                        ->select('boutiques.id', 'boutiques.nom')
                        ->where('actif', true)
                        ->orderBy('nom')
                        ->get(),
                );
                if ($boutiques->isEmpty() && $user->boutique_id) {
                    $boutiques = \App\Models\Boutique::select('id', 'nom')
                        ->where('id', $user->boutique_id)
                        ->where('actif', true)
                        ->get();
                }
            }
        }
        $showSelector = $boutiques->count() > 1;

        $notificationCount = \Illuminate\Support\Facades\Cache::remember(
            'topbar_notifications_count_' . auth()->id() . '_' . session('boutique_active', 'all'),
            now()->addSeconds(20),
            fn() => \App\Services\NotificationService::compterNonLues(auth()->id()),
        );

        $notifications = \Illuminate\Support\Facades\Cache::remember(
            'topbar_notifications_list_' . auth()->id() . '_' . session('boutique_active', 'all'),
            now()->addSeconds(20),
            fn() => \App\Services\NotificationService::getNotifications(auth()->id(), 5),
        );
    @endphp

    <div class="container-fluid px-0 px-md-3">
        {{-- ── Mobile : une seule ligne compacte ── --}}
        <div class="wmc-topbar-mobile d-flex d-md-none align-items-center gap-1">
            <button id="sidebarToggle" class="btn btn-link text-dark wmc-topbar-icon-btn" type="button"
                aria-label="Ouvrir le menu">
                <i class="fas fa-bars"></i>
            </button>

            <div class="wmc-topbar-mobile__title flex-grow-1 min-w-0">
                <h1 class="h6 mb-0 text-dark text-truncate fw-semibold">@yield('page-title', $pageTitle)</h1>
            </div>

            <div class="wmc-topbar-mobile__actions d-flex align-items-center gap-1 flex-shrink-0">
                <div id="wmc-offline-status-mobile" class="wmc-topbar-icon-btn d-inline-flex align-items-center justify-content-center"
                    title="État de la connexion">
                    <span class="wmc-offline-status__dot" aria-hidden="true">🟢</span>
                </div>

                <div class="dropdown topbar-notification">
                    <button class="btn btn-link text-dark position-relative wmc-topbar-icon-btn" type="button"
                        data-bs-toggle="dropdown" id="notificationDropdownMobile">
                        <i class="fas fa-bell"></i>
                        @if ($notificationCount > 0)
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"
                                style="font-size: 0.6rem;">
                                {{ $notificationCount > 9 ? '9+' : $notificationCount }}
                            </span>
                        @endif
                    </button>
                    @include('layouts.partials.topbar-notifications-menu', ['notifications' => $notifications, 'notificationCount' => $notificationCount])
                </div>

                <div class="dropdown topbar-user-menu">
                    <button class="btn btn-link text-dark wmc-topbar-icon-btn" type="button" data-bs-toggle="dropdown"
                        aria-label="Menu utilisateur">
                        <i class="fas fa-user"></i>
                    </button>
                    @include('layouts.partials.topbar-user-menu')
                </div>
            </div>
        </div>

        {{-- ── Desktop ── --}}
        <div class="row align-items-center d-none d-md-flex topbar-desktop-row">
            <div class="col-auto">
                <div class="d-inline-block">
                    @if ($resolvedLogo)
                        <img src="{{ $resolvedLogo }}" alt="Logo {{ $resolvedBoutiqueName }}" class="topbar-logo-img">
                    @else
                        <img src="{{ asset('images/logos/logo_wmc_orange.png') }}" alt="Logo WMC" class="topbar-logo-img">
                    @endif
                </div>
            </div>

            <div class="col topbar-center-col min-w-0">
                <div class="d-flex flex-row align-items-center gap-2 topbar-center">
                    <h1 class="h4 mb-0 text-dark text-truncate">@yield('page-title', $pageTitle)</h1>

                    <div class="topbar-search d-none d-lg-inline-flex ms-lg-3">
                        <i class="fas fa-search"></i>
                        <input type="text" placeholder="Rechercher (produits, clients, ventes)…">
                    </div>

                    <div class="topbar-inline-tools d-flex align-items-center ms-auto">
                        @auth
                            @if ($showSelector)
                                <div class="dropdown me-2 d-none d-lg-block">
                                    <button class="btn btn-outline-primary btn-sm dropdown-toggle" type="button"
                                        data-bs-toggle="dropdown">
                                        <i class="fas fa-store me-1"></i>
                                        <span id="selectedBoutique" class="d-none d-xl-inline">
                                            @if (session('boutique_active'))
                                                @php $activeBoutique = $boutiques->firstWhere('id', session('boutique_active')); @endphp
                                                {{ $activeBoutique ? $activeBoutique->nom : 'Boutique' }}
                                            @else
                                                {{ $boutiques->first()->nom ?? 'Boutique' }}
                                            @endif
                                        </span>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        @foreach ($boutiques as $boutique)
                                            <li>
                                                <a class="dropdown-item {{ session('boutique_active') == $boutique->id ? 'active' : '' }}"
                                                    href="{{ route('boutiques.select', $boutique->id) }}">
                                                    <i class="fas fa-store me-2"></i>{{ $boutique->nom }}
                                                    @if (session('boutique_active') == $boutique->id)
                                                        <i class="fas fa-check ms-2 text-success"></i>
                                                    @endif
                                                </a>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                        @endauth

                        @include('layouts.partials.offline-status')

                        <div class="dropdown me-2 topbar-notification">
                            <button class="btn btn-link text-dark position-relative topbar-notification-btn" type="button"
                                data-bs-toggle="dropdown" id="notificationDropdown">
                                <i class="fas fa-bell"></i>
                                @if ($notificationCount > 0)
                                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                        {{ $notificationCount }}
                                    </span>
                                @endif
                            </button>
                            @include('layouts.partials.topbar-notifications-menu', ['notifications' => $notifications, 'notificationCount' => $notificationCount])
                        </div>

                        <div class="dropdown topbar-user-menu">
                            <button class="btn btn-link text-dark d-flex align-items-center topbar-user-trigger" type="button"
                                data-bs-toggle="dropdown">
                                <div class="me-2">
                                    <div class="topbar-avatar rounded-circle d-flex align-items-center justify-content-center"
                                        style="width: 32px; height: 32px;">
                                        <i class="fas fa-user text-white"></i>
                                    </div>
                                </div>
                                <div class="text-start d-none d-xl-block">
                                    <div class="fw-bold">{{ auth()->user()->name }}</div>
                                </div>
                                <i class="fas fa-chevron-down ms-2 d-none d-lg-block"></i>
                            </button>
                            @include('layouts.partials.topbar-user-menu')
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>

<script>
    let previousNotificationCount = {{ $notificationCount }};
    let notificationAudioContext = null;

    function getAudioContext() {
        if (notificationAudioContext) {
            return notificationAudioContext;
        }
        const AudioContext = window.AudioContext || window.webkitAudioContext;
        if (!AudioContext) {
            return null;
        }
        notificationAudioContext = new AudioContext();
        return notificationAudioContext;
    }

    function playNotificationSound() {
        const audioCtx = getAudioContext();
        if (!audioCtx) return;

        const startOscillator = () => {
            const oscillator = audioCtx.createOscillator();
            const gainNode = audioCtx.createGain();
            oscillator.type = 'triangle';
            oscillator.frequency.value = 880;
            gainNode.gain.setValueAtTime(0.25, audioCtx.currentTime);
            gainNode.gain.exponentialRampToValueAtTime(0.0001, audioCtx.currentTime + 1);
            oscillator.connect(gainNode);
            gainNode.connect(audioCtx.destination);
            oscillator.start();
            oscillator.stop(audioCtx.currentTime + 1);
        };

        if (audioCtx.state === 'suspended') {
            audioCtx.resume().then(startOscillator).catch(() => {});
            return;
        }
        startOscillator();
    }

    function ensureAudioUnlocked() {
        const audioCtx = getAudioContext();
        if (!audioCtx) return;
        if (audioCtx.state === 'suspended') {
            audioCtx.resume().catch(() => {});
        }
    }

    document.addEventListener('click', ensureAudioUnlocked, { passive: true });
    document.addEventListener('keydown', ensureAudioUnlocked, { passive: true });

    function markAsRead(notificationId) {
        @if (auth()->user()->isAdmin())
            const url = `{{ route('admin.notifications.mark-read', ':id') }}`.replace(':id', notificationId);
        @else
            const url = `{{ route('notifications.mark-read', ':id') }}`.replace(':id', notificationId);
        @endif

        const csrfToken = document.querySelector('meta[name="csrf-token"]');
        if (!csrfToken) return;

        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken.getAttribute('content'),
                'X-Requested-With': 'XMLHttpRequest',
            },
            credentials: 'same-origin',
        })
            .then((response) => {
                if (!response.ok && response.status !== 419) {
                    return response.json().then((data) => Promise.reject(data));
                }
                return response.json();
            })
            .then((data) => {
                if (data && data.success) updateNotificationCount();
            })
            .catch(() => {});
    }

    function updateNotificationBadges(count) {
        document.querySelectorAll('#notificationDropdown .badge.bg-danger, #notificationDropdownMobile .badge.bg-danger')
            .forEach((badge) => badge.remove());

        if (count <= 0) return;

        ['notificationDropdown', 'notificationDropdownMobile'].forEach((id) => {
            const button = document.getElementById(id);
            if (!button) return;
            const span = document.createElement('span');
            span.className = 'position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger';
            span.textContent = id.endsWith('Mobile') && count > 9 ? '9+' : count;
            if (id.endsWith('Mobile')) span.style.fontSize = '0.6rem';
            button.appendChild(span);
        });
    }

    function updateNotificationCount() {
        @if (auth()->user()->isAdmin())
            const url = `{{ route('admin.notifications.count') }}`;
        @else
            const url = `{{ route('notifications.count') }}`;
        @endif
        fetch(url)
            .then((response) => response.json())
            .then((data) => {
                const newCount = data.count ?? 0;
                if (newCount > previousNotificationCount) playNotificationSound();
                previousNotificationCount = newCount;
                updateNotificationBadges(newCount);
            })
            .catch(() => {});
    }

    let notificationPollingDelay = 30000;
    let notificationPollingTimer = null;

    function scheduleNotificationPolling(delay = notificationPollingDelay) {
        if (notificationPollingTimer) clearTimeout(notificationPollingTimer);
        notificationPollingTimer = setTimeout(async () => {
            if (document.visibilityState === 'visible') {
                await updateNotificationCount();
                notificationPollingDelay = 30000;
            } else {
                notificationPollingDelay = Math.min(notificationPollingDelay * 2, 300000);
            }
            scheduleNotificationPolling(notificationPollingDelay);
        }, delay);
    }

    document.addEventListener('visibilitychange', function () {
        if (document.visibilityState === 'visible') {
            notificationPollingDelay = 30000;
            updateNotificationCount();
            scheduleNotificationPolling(notificationPollingDelay);
        }
    });

    scheduleNotificationPolling();
</script>
