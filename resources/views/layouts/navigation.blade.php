<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container-fluid">
        <!-- Logo -->
        <a class="navbar-brand d-flex align-items-center" href="{{ route('dashboard') }}">
            @if (isset($logo) && $logo)
                <img src="{{ $logo }}" alt="Logo" height="32" class="me-2">
            @else
                <i class="fas fa-cash-register me-2"></i>
            @endif
            <span>GestionCaisse</span>
            @if (isset($boutiqueActive) && $boutiqueActive)
                <small class="ms-2 opacity-75">- {{ $boutiqueActive->nom }}</small>
            @endif
        </a>

        <!-- Mobile toggle -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">
            <!-- Navigation Links -->
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('dashboard') }}">
                        <i class="fas fa-tachometer-alt me-1"></i>
                        Dashboard
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="{{ route('produits.index') }}">
                        <i class="fas fa-box me-1"></i>
                        Produits
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="{{ route('ventes.pos') }}">
                        <i class="fas fa-cash-register me-1"></i>
                        Point de vente
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="{{ route('ventes.index') }}">
                        <i class="fas fa-shopping-cart me-1"></i>
                        Historique ventes
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="{{ route('stock.index') }}">
                        <i class="fas fa-warehouse me-1"></i>
                        Gestion du stock
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="{{ route('depenses.index') }}">
                        <i class="fas fa-receipt me-1"></i>
                        Gestion des dépenses
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="{{ route('factures.index') }}">
                        <i class="fas fa-file-invoice me-1"></i>
                        Factures
                    </a>
                </li>

                @if (auth()->user()->isAdmin())
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('employes.index') }}">
                            <i class="fas fa-users me-1"></i>
                            Gestion des employés
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('rapports.index') }}">
                            <i class="fas fa-chart-bar me-1"></i>
                            Rapports
                        </a>
                    </li>
                @endif
            </ul>

            <!-- User Menu -->
            <ul class="navbar-nav">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button"
                        data-bs-toggle="dropdown">
                        <i class="fas fa-user me-1"></i>
                        {{ Auth::user()->name }}
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="{{ route('profile.edit') }}">
                                <i class="fas fa-user-edit me-1"></i>
                                Profil
                            </a></li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="dropdown-item">
                                    <i class="fas fa-sign-out-alt me-1"></i>
                                    Déconnexion
                                </button>
                            </form>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>
