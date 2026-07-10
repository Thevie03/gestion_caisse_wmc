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
    @endphp
    <div class="container-fluid">
        <div class="row align-items-center">
            <!-- Mobile Menu Toggle -->
            <div class="col-auto d-md-none">
                <button id="sidebarToggle" class="btn btn-link text-dark">
                    <i class="fas fa-bars"></i>
                </button>
            </div>

            <!-- Logo Mobile -->
            <div class="col-auto d-md-none">
                <div class="d-inline-block">
                    @if ($resolvedLogo)
                        <img src="{{ $resolvedLogo }}" alt="Logo {{ $resolvedBoutiqueName }}"
                            style="max-height: 60px; width: auto; object-fit: contain;">
                    @else
                        <img src="{{ asset('images/logos/logo_wmc_orange.png') }}" alt="Logo WMC"
                            style="max-height: 60px; width: auto;">
                    @endif
                </div>
            </div>

            <!-- Statut connexion mobile -->
            <div class="col-auto d-md-none ms-auto" id="wmc-offline-status-mobile" title="État de la connexion">
                <span class="wmc-offline-status__dot" aria-hidden="true">🟢</span>
            </div>

            <!-- Logo Desktop -->
            <div class="col-auto d-none d-md-block">
                <div class="d-inline-block">
                    @if ($resolvedLogo)
                        <img src="{{ $resolvedLogo }}" alt="Logo {{ $resolvedBoutiqueName }}"
                            style="max-height: 60px; width: auto; object-fit: contain;">
                    @else
                        <img src="{{ asset('images/logos/logo_wmc_orange.png') }}" alt="Logo WMC"
                            style="max-height: 60px; width: auto;">
                    @endif
                </div>
            </div>

            <!-- Page Title + Search -->
            <div class="col topbar-center-col">
                <div class="d-flex flex-column flex-md-row align-items-md-center gap-2 topbar-center">
                    <h1 class="h4 mb-0 text-dark text-truncate">
                        @yield('page-title', $resolvedBoutiqueName ?? config('app.name', 'GestionCaisse'))
                    </h1>
                    <!-- Search (UI uniquement) -->
                    <div class="topbar-search d-none d-md-inline-flex ms-md-3">
                        <i class="fas fa-search"></i>
                        <input type="text" placeholder="Rechercher (produits, clients, ventes)…">
                    </div>
                    <!-- Right Side (desktop) -->
                    <div class="topbar-inline-tools d-none d-md-flex align-items-center">
                        <!-- Boutique Selector (Propriétaires avec plusieurs boutiques et Super Admin) -->
                        @auth
                            @php
                                $user = auth()->user();
                                $boutiques = collect();

                                if ($user->isSuperAdmin()) {
                                    // Super admin voit toutes les boutiques (cache service)
                                    $boutiques = \App\Services\CacheService::getActiveBoutiques();
                                } elseif ($user->isOwner()) {
                                    // Propriétaire voit ses boutiques (cache court)
                                    $boutiques = \Illuminate\Support\Facades\Cache::remember(
                                        'topbar_owner_boutiques_' . $user->id,
                                        now()->addSeconds(60),
                                        fn() => $user->ownedBoutiques()
                                            ->select('boutiques.id', 'boutiques.nom')
                                            ->where('actif', true)
                                            ->orderBy('nom')
                                            ->get(),
                                    );
                                    // Rétrocompatibilité : si aucune via many-to-many, utiliser boutique_id
                                    if ($boutiques->isEmpty() && $user->boutique_id) {
                                        $boutiques = \App\Models\Boutique::select('id', 'nom')
                                            ->where('id', $user->boutique_id)
                                            ->where('actif', true)
                                            ->get();
                                    }
                                }

                                $showSelector = $boutiques->count() > 1;
                            @endphp
                            @if ($showSelector)
                                <div class="dropdown me-2 d-none d-lg-block">
                                    <button class="btn btn-outline-primary btn-sm dropdown-toggle" type="button"
                                        data-bs-toggle="dropdown">
                                        <i class="fas fa-store me-1"></i>
                                        <span id="selectedBoutique" class="d-none d-xl-inline">
                                            @if (session('boutique_active'))
                                                @php
                                                    $activeBoutique = $boutiques->firstWhere(
                                                        'id',
                                                        session('boutique_active'),
                                                    );
                                                @endphp
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
                                                    <i class="fas fa-store me-2"></i>
                                                    {{ $boutique->nom }}
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

                        {{-- Indicateur connexion PWA / mode offline --}}
                        @include('layouts.partials.offline-status')

                        <!-- Notifications -->
                        <div class="dropdown me-2 topbar-notification">
                            <button class="btn btn-link text-dark position-relative topbar-notification-btn" type="button"
                                data-bs-toggle="dropdown" id="notificationDropdown">
                                <i class="fas fa-bell"></i>
                                @php
                                    $notificationCount = \Illuminate\Support\Facades\Cache::remember(
                                        'topbar_notifications_count_' . auth()->id() . '_' . session('boutique_active', 'all'),
                                        now()->addSeconds(20),
                                        fn() => \App\Services\NotificationService::compterNonLues(auth()->id()),
                                    );
                                @endphp
                                @if ($notificationCount > 0)
                                    <span
                                        class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                        {{ $notificationCount }}
                                    </span>
                                @endif
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end"
                                style="min-width: min(300px, calc(100vw - 40px)); max-width: calc(100vw - 40px); max-height: 400px; overflow-y: auto;">
                                <li>
                                    <h6 class="dropdown-header d-flex justify-content-between align-items-center">
                                        <span>Notifications</span>
                                        @if ($notificationCount > 0)
                                            <small class="text-muted">{{ $notificationCount }} non lue(s)</small>
                                        @endif
                                    </h6>
                                </li>

                                @php
                                    $notifications = \Illuminate\Support\Facades\Cache::remember(
                                        'topbar_notifications_list_' . auth()->id() . '_' . session('boutique_active', 'all'),
                                        now()->addSeconds(20),
                                        fn() => \App\Services\NotificationService::getNotifications(auth()->id(), 5),
                                    );
                                @endphp

                                @forelse($notifications as $notification)
                                    @php
                                        $isSupport =
                                            $notification->module === 'support' && isset($notification->data['ticket_id']);
                                        $notificationUrl = null;
                                        if ($isSupport) {
                                            if (auth()->user()->isAdmin() && !auth()->user()->isOwner()) {
                                                $notificationUrl = route(
                                                    'admin.support.show',
                                                    $notification->data['ticket_id'],
                                                );
                                            } elseif (auth()->user()->isOwner()) {
                                                $notificationUrl = route('tickets.show', $notification->data['ticket_id']);
                                            }
                                        } else {
                                            $notificationUrl = auth()->user()->isAdmin()
                                                ? route('admin.notifications.show', $notification)
                                                : route('notifications.show', $notification);
                                        }
                                    @endphp
                                    <li>
                                        <a class="dropdown-item {{ !$notification->lue ? 'fw-bold' : '' }}"
                                            href="{{ $notificationUrl }}"
                                            onclick="markAsRead({{ $notification->id }}); return true;">
                                            <div class="d-flex align-items-start">
                                                <div class="me-2">
                                                    <i
                                                        class="{{ $notification->icone }} text-{{ $notification->couleur }}"></i>
                                                </div>
                                                <div class="flex-grow-1">
                                                    <div class="fw-bold small">{{ $notification->titre }}</div>
                                                    <div class="text-muted small">
                                                        <x-notification-message
                                                            :message="$notification->message"
                                                            :module="$notification->module"
                                                            :limit="50"
                                                        />
                                                    </div>
                                                    <div class="text-muted" style="font-size: 0.75rem;">
                                                        {{ $notification->created_at->diffForHumans() }}
                                                    </div>
                                                </div>
                                                @if (!$notification->lue)
                                                    <div class="ms-2">
                                                        <span class="badge bg-primary rounded-pill"
                                                            style="width: 8px; height: 8px;"></span>
                                                    </div>
                                                @endif
                                            </div>
                                        </a>
                                    </li>
                                @empty
                                    <li>
                                        <div class="dropdown-item text-center text-muted">
                                            <i class="fas fa-bell-slash me-2"></i>
                                            Aucune notification
                                        </div>
                                    </li>
                                @endforelse

                                @if ($notifications->count() > 0)
                                    <li>
                                        <hr class="dropdown-divider">
                                    </li>
                                    <li>
                                        <a class="dropdown-item text-center"
                                            href="{{ auth()->user()->isAdmin() ? route('admin.notifications.index') : route('notifications.index') }}">
                                            <i class="fas fa-list me-2"></i>
                                            Voir toutes les notifications
                                        </a>
                                    </li>
                                @endif
                            </ul>
                        </div>

                        <!-- User Menu -->
                        <div class="dropdown topbar-user-menu">
                            <button class="btn btn-link text-dark d-flex align-items-center topbar-user-trigger" type="button"
                                data-bs-toggle="dropdown">
                                <div class="me-1 me-md-2">
                                    <div class="topbar-avatar rounded-circle d-flex align-items-center justify-content-center"
                                        style="width: 32px; height: 32px;">
                                        <i class="fas fa-user text-white"></i>
                                    </div>
                                </div>
                                <div class="text-start d-none d-xl-block">
                                    <div class="fw-bold">{{ auth()->user()->name }}</div>
                                    <small class="text-muted d-none">
                                        @if (auth()->user()->isAdmin())
                                            Administrateur
                                        @elseif (auth()->user()->isOwner())
                                            Administrateur
                                        @else
                                            Employé
                                        @endif
                                    </small>
                                </div>
                                <i class="fas fa-chevron-down ms-2 d-none d-md-block"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end" style="min-width: 200px; z-index: 1050;">
                                <li>
                                    <a class="dropdown-item" href="{{ route('profile.edit') }}">
                                        <i class="fas fa-user me-2"></i>
                                        Mon profil
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="#">
                                        <i class="fas fa-cog me-2"></i>
                                        Paramètres
                                    </a>
                                </li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li>
                                    <form method="POST" action="{{ route('logout') }}" class="d-inline">
                                        @csrf
                                        <button type="submit" class="dropdown-item text-danger w-100 text-start border-0 bg-transparent" style="cursor: pointer;">
                                            <i class="fas fa-sign-out-alt me-2"></i>
                                            Déconnexion
                                        </button>
                                    </form>
                                </li>
                            </ul>
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
            console.warn('Web Audio API non supportée, son de notification désactivé');
            return null;
        }
        notificationAudioContext = new AudioContext();
        return notificationAudioContext;
    }

    function playNotificationSound() {
        const audioCtx = getAudioContext();
        if (!audioCtx) {
            return;
        }

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
            audioCtx.resume()
                .then(startOscillator)
                .catch(() => {});
            return;
        }

        startOscillator();
    }

    function ensureAudioUnlocked() {
        const audioCtx = getAudioContext();
        if (!audioCtx) {
            return;
        }
        if (audioCtx.state === 'suspended') {
            audioCtx.resume().catch(() => {});
        }
    }

    document.addEventListener('click', ensureAudioUnlocked, {
        passive: true
    });
    document.addEventListener('keydown', ensureAudioUnlocked, {
        passive: true
    });

    function markAsRead(notificationId) {
        @if (auth()->user()->isAdmin())
            const url = `{{ route('admin.notifications.mark-read', ':id') }}`.replace(':id', notificationId);
        @else
            const url = `{{ route('notifications.mark-read', ':id') }}`.replace(':id', notificationId);
        @endif

        const csrfToken = document.querySelector('meta[name="csrf-token"]');
        if (!csrfToken) {
            console.error('Token CSRF non trouvé');
            return;
        }

        fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken.getAttribute('content'),
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin'
            })
            .then(response => {
                if (!response.ok) {
                    // Si erreur 419, ne pas bloquer la navigation
                    if (response.status === 419) {
                        console.warn('Token CSRF expiré, mais navigation autorisée');
                        return;
                    }
                    return response.json().then(data => Promise.reject(data));
                }
                return response.json();
            })
            .then(data => {
                if (data && data.success) {
                    // Mettre à jour le compteur de notifications
                    updateNotificationCount();
                }
            })
            .catch(error => {
                // Ne pas bloquer la navigation en cas d'erreur
                console.warn('Erreur lors du marquage de la notification:', error);
            });
    }

    function updateNotificationCount() {
        @if (auth()->user()->isAdmin())
            const url = `{{ route('admin.notifications.count') }}`;
        @else
            const url = `{{ route('notifications.count') }}`;
        @endif
        fetch(url)
            .then(response => response.json())
            .then(data => {
                const badge = document.querySelector('#notificationDropdown .badge.bg-danger');
                const newCount = data.count ?? 0;
                if (newCount > previousNotificationCount) {
                    playNotificationSound();
                }
                previousNotificationCount = newCount;
                if (newCount > 0) {
                    if (badge) {
                        badge.textContent = data.count;
                    } else {
                        // Créer le badge s'il n'existe pas
                        const button = document.getElementById('notificationDropdown');
                        const span = document.createElement('span');
                        span.className =
                            'position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger';
                        span.textContent = data.count;
                        button.appendChild(span);
                    }
                } else {
                    if (badge) {
                        badge.remove();
                    }
                }
            })
            .catch(error => console.error('Erreur:', error));
    }

    let notificationPollingDelay = 30000;
    let notificationPollingTimer = null;

    function scheduleNotificationPolling(delay = notificationPollingDelay) {
        if (notificationPollingTimer) {
            clearTimeout(notificationPollingTimer);
        }
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

    document.addEventListener('visibilitychange', function() {
        if (document.visibilityState === 'visible') {
            notificationPollingDelay = 30000;
            updateNotificationCount();
            scheduleNotificationPolling(notificationPollingDelay);
        }
    });

    scheduleNotificationPolling();
</script>
