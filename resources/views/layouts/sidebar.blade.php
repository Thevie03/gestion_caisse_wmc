<!-- Sidebar -->
<nav id="sidebar" class="sidebar">
    @php
        $resolvedBoutiqueName = $boutiqueActive->nom ?? auth()->user()?->boutique?->nom ?? 'Boutique';
        $resolvedLogo = $logo ?? ($boutiqueActive->logo ?? auth()->user()?->boutique?->logo ?? null);

        if (!$resolvedLogo && auth()->check() && session('boutique_active')) {
            $selectedBoutique = \App\Models\Boutique::select('nom', 'logo')->find(session('boutique_active'));
            if ($selectedBoutique) {
                $resolvedLogo = $selectedBoutique->logo;
                $resolvedBoutiqueName = $selectedBoutique->nom ?: $resolvedBoutiqueName;
            }
        }
    @endphp
    <div class="position-sticky pt-3">
        <!-- Navigation Menu -->
        <ul class="nav flex-column">
            @auth
                @php
                    // Charger la relation boutique si nécessaire pour vérifier isOwner()
                    $user = auth()->user();
                    if ($user->boutique_id && !$user->relationLoaded('boutique')) {
                        $user->load([
                            'boutique' => function ($query) {
                                $query->select('id', 'nom', 'owner_id', 'actif', 'devise', 'theme_color', 'logo');
                            },
                        ]);
                    }
                @endphp
                @if ($user->isSuperAdmin())
                    <!-- Séparateur ADMINISTRATION -->
                    <hr class="text-white-50">
                    <li class="nav-item">
                        <div class="nav-link text-white-50 small fw-bold text-uppercase">
                            <i class="fas fa-shield-alt me-2"></i>
                            ADMINISTRATION
                        </div>
                    </li>

                    <!-- Gestion Boutiques -->
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('admin.boutiques.*') ? 'active' : '' }}"
                            href="{{ route('admin.boutiques.index') }}">
                            <i class="fas fa-store"></i>
                            Boutiques
                        </a>
                    </li>

                    <!-- Dashboard Admin -->
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}"
                            href="{{ route('admin.dashboard') }}">
                            <i class="fas fa-tachometer-alt"></i>
                            Dashboard Admin
                        </a>
                    </li>

                    <!-- Gestion Paiements -->
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('admin.paiements.*') ? 'active' : '' }}"
                            href="{{ route('admin.paiements.index') }}">
                            <i class="fas fa-money-check-alt"></i>
                            Paiements
                        </a>
                    </li>

                    <!-- Gestion Contrats -->
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('admin.contrats.*') ? 'active' : '' }}"
                            href="{{ route('admin.contrats.index') }}">
                            <i class="fas fa-file-contract"></i>
                            Contrats
                        </a>
                    </li>

                    <!-- Statistiques -->
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('admin.statistiques.*') ? 'active' : '' }}"
                            href="{{ route('admin.statistiques.index') }}">
                            <i class="fas fa-chart-bar"></i>
                            Statistiques
                        </a>
                    </li>

                    <!-- Notifications -->
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('admin.notifications.*') ? 'active' : '' }}"
                            href="{{ route('admin.notifications.index') }}">
                            <i class="fas fa-bell"></i>
                            Notifications
                            @php
                                $notificationsNonLues = \Illuminate\Support\Facades\Cache::remember(
                                    'sidebar_notifications_count_' . auth()->id() . '_' . session('boutique_active', 'all'),
                                    now()->addSeconds(20),
                                    fn() => \App\Services\NotificationService::compterNonLues(auth()->id()),
                                );
                            @endphp
                            @if ($notificationsNonLues > 0)
                                <span class="badge bg-danger ms-2">{{ $notificationsNonLues }}</span>
                            @endif
                        </a>
                    </li>

                    <!-- Support & Assistance -->
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('admin.support.*') ? 'active' : '' }}"
                            href="{{ route('admin.support.index') }}">
                            <i class="fas fa-life-ring"></i>
                            Support & Assistance
                        </a>
                    </li>

                    <!-- Paramètres Système -->
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('admin.parametres.*') ? 'active' : '' }}"
                            href="{{ route('admin.parametres.index') }}">
                            <i class="fas fa-cogs"></i>
                            Paramètres Système
                        </a>
                    </li>

                    <!-- Archivage -->
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('archives.*') ? 'active' : '' }}"
                            href="{{ route('archives.index') }}">
                            <i class="fas fa-archive"></i>
                            Archivage
                        </a>
                    </li>
                @else
                    <!-- Section pour les boutiques/clients (propriétaires et employés) -->

                    <!-- Notifications -->
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('notifications.*') ? 'active' : '' }}"
                            href="{{ route('notifications.index') }}">
                            <i class="fas fa-bell"></i>
                            Notifications
                            @php
                                $notificationsNonLues = \Illuminate\Support\Facades\Cache::remember(
                                    'sidebar_notifications_count_' . auth()->id() . '_' . session('boutique_active', 'all'),
                                    now()->addSeconds(20),
                                    fn() => \App\Services\NotificationService::compterNonLues(auth()->id()),
                                );
                            @endphp
                            @if ($notificationsNonLues > 0)
                                <span class="badge bg-danger ms-2">{{ $notificationsNonLues }}</span>
                            @endif
                        </a>
                    </li>

                    <!-- Tableau de bord -->
                    @if (auth()->user()->canAccessSidebarItem('dashboard'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}"
                                href="{{ route('dashboard') }}">
                                <i class="fas fa-tachometer-alt"></i>
                                Tableau de bord
                            </a>
                        </li>
                    @endif

                    <!-- Séparateur VENTES -->
                    <hr class="text-white-50">
                    <li class="nav-item">
                        <div class="nav-link text-white-50 small fw-bold text-uppercase">
                            <i class="fas fa-shopping-cart me-2"></i>
                            VENTES
                        </div>
                    </li>

                    <!-- Point de vente -->
                    @if (auth()->user()->canAccessSidebarItem('point_vente'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('ventes.pos') ? 'active' : '' }}"
                                href="{{ route('ventes.pos') }}">
                                <i class="fas fa-cash-register"></i>
                                Point de vente
                            </a>
                        </li>
                    @endif

                    <!-- Historique ventes -->
                    @if (auth()->user()->canAccessSidebarItem('historique_ventes'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('ventes.index') || request()->routeIs('ventes.show') ? 'active' : '' }}"
                                href="{{ route('ventes.index') }}">
                                <i class="fas fa-history"></i>
                                Historique ventes
                            </a>
                        </li>
                    @endif

                    <!-- Séparateur GESTION -->
                    <hr class="text-white-50">
                    <li class="nav-item">
                        <div class="nav-link text-white-50 small fw-bold text-uppercase">
                            <i class="fas fa-cogs me-2"></i>
                            GESTION
                        </div>
                    </li>

                    <!-- Produits -->
                    @if (auth()->user()->canAccessSidebarItem('produits'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('produits.*') ? 'active' : '' }}"
                                href="{{ route('produits.index') }}">
                                <i class="fas fa-box"></i>
                                produits/articles
                            </a>
                        </li>
                    @endif

                    <!-- Catégories -->
                    @if (auth()->user()->canAccessSidebarItem('categories'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('categories.*') ? 'active' : '' }}"
                                href="{{ route('categories.index') }}">
                                <i class="fas fa-tags"></i>
                                Catégories
                            </a>
                        </li>
                    @endif

                    <!-- Stock -->
                    @if (auth()->user()->canAccessSidebarItem('stock'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('stock.*') ? 'active' : '' }}"
                                href="{{ route('stock.index') }}">
                                <i class="fas fa-warehouse"></i>
                                Stock
                            </a>
                        </li>
                    @endif

                    <!-- Clients -->
                    @if (auth()->user()->canAccessSidebarItem('clients'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('clients.*') ? 'active' : '' }}"
                                href="{{ route('clients.index') }}">
                                <i class="fas fa-users"></i>
                                Clients
                            </a>
                        </li>
                    @endif

                    <!-- Fournisseurs -->
                    @if (auth()->user()->canAccessSidebarItem('fournisseurs'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('fournisseurs.*') ? 'active' : '' }}"
                                href="{{ route('fournisseurs.index') }}">
                                <i class="fas fa-truck"></i>
                                Fournisseurs
                            </a>
                        </li>
                    @endif

                    <!-- Séparateur FINANCIER -->
                    <hr class="text-white-50">
                    <li class="nav-item">
                        <div class="nav-link text-white-50 small fw-bold text-uppercase">
                            <i class="fas fa-money-bill-wave me-2"></i>
                            FINANCIER
                        </div>
                    </li>

                    <!-- Récapitulatif Ventes -->
                    @if (auth()->user()->canAccessSidebarItem('recapitulatif_ventes'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('factures.index') ? 'active' : '' }}"
                                href="{{ route('factures.index') }}">
                                <i class="fas fa-chart-line"></i>
                                Récapitulatif Ventes
                            </a>
                        </li>
                    @endif

                    <!-- Dépenses -->
                    @if (auth()->user()->canAccessSidebarItem('depenses'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('depenses.*') ? 'active' : '' }}"
                                href="{{ route('depenses.index') }}">
                                <i class="fas fa-receipt"></i>
                                Dépenses
                            </a>
                        </li>
                    @endif

                    <!-- Rapports -->
                    @if (auth()->user()->canAccessSidebarItem('rapports'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('rapports.*') ? 'active' : '' }}"
                                href="{{ route('rapports.index') }}">
                                <i class="fas fa-chart-bar"></i>
                                Rapports
                            </a>
                        </li>
                    @endif

                    <!-- Séparateur ADMINISTRATION (pour les propriétaires uniquement) -->
                    @if (auth()->user()->isOwner())
                        <hr class="text-white-50">
                        <li class="nav-item">
                            <div class="nav-link text-white-50 small fw-bold text-uppercase">
                                <i class="fas fa-shield-alt me-2"></i>
                                ADMINISTRATION
                            </div>
                        </li>

                        <!-- Boutiques (pour voir sa boutique) -->
                        @if (auth()->user()->canAccessSidebarItem('boutiques'))
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('boutiques.*') ? 'active' : '' }}"
                                    href="{{ route('boutiques.index') }}">
                                    <i class="fas fa-store"></i>
                                    Boutiques
                                </a>
                            </li>
                        @endif

                        <!-- Archivage -->
                        @if (auth()->user()->canAccessSidebarItem('archivage'))
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('archives.*') ? 'active' : '' }}"
                                    href="{{ route('archives.index') }}">
                                    <i class="fas fa-archive"></i>
                                    Archivage
                                </a>
                            </li>
                        @endif

                        <!-- Employés -->
                        @if (auth()->user()->canAccessSidebarItem('employes'))
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('employes.*') ? 'active' : '' }}"
                                    href="{{ route('employes.index') }}">
                                    <i class="fas fa-users-cog"></i>
                                    Employés
                                </a>
                            </li>
                        @endif

                        <!-- Historique des modifications -->
                        @if (auth()->user()->isOwner() || auth()->user()->isSuperAdmin())
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('historique.*') ? 'active' : '' }}"
                                    href="{{ route('historique.ventes.index') }}">
                                    <i class="fas fa-history"></i>
                                    Historique des modifications
                                </a>
                            </li>
                        @endif

                        <!-- Bugs ou Assistance -->
                        @if (auth()->user()->isOwner())
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('tickets.*') ? 'active' : '' }}"
                                    href="{{ route('tickets.index') }}">
                                    <i class="fas fa-life-ring"></i>
                                    Bugs ou Assistance
                                </a>
                            </li>
                        @endif

                        <!-- Conditions générales d'utilisation -->
                        @if (auth()->user()->isOwner())
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('conditions-generales.*') ? 'active' : '' }}"
                                    href="{{ route('conditions-generales.index') }}">
                                    <i class="fas fa-file-contract"></i>
                                    Conditions générales
                                </a>
                            </li>
                        @endif
                    @endif
                @endif
            @endauth
        </ul>

        <!-- User Info -->
        <div class="mt-auto pt-3">
            <div class="text-center">
                <div class="text-white-50 small">
                    Connecté en tant que
                </div>
                <div class="text-white fw-bold">
                    {{ auth()->user()->name }}
                </div>
                <div class="text-white-50 small">
                    @if (auth()->user()->isSuperAdmin())
                        Super Administrateur
                    @elseif (auth()->user()->isOwner())
                        Administrateur
                    @else
                        Employé
                    @endif
                    @if (auth()->user()->boutique)
                        - {{ auth()->user()->boutique->nom }}
                    @endif
                </div>
            </div>
        </div>
    </div>
</nav>
