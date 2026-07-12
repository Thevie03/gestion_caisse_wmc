<ul class="dropdown-menu dropdown-menu-end wmc-topbar-dropdown" style="min-width: 200px; z-index: 1050;">
    <li>
        <a class="dropdown-item" href="{{ route('profile.edit') }}">
            <i class="fas fa-user me-2"></i>Mon profil
        </a>
    </li>
    <li>
        <a class="dropdown-item" href="#">
            <i class="fas fa-cog me-2"></i>Paramètres
        </a>
    </li>
    <li><hr class="dropdown-divider"></li>
    <li>
        <form method="POST" action="{{ route('logout') }}" class="d-inline">
            @csrf
            <button type="submit"
                class="dropdown-item text-danger w-100 text-start border-0 bg-transparent"
                style="cursor: pointer;">
                <i class="fas fa-sign-out-alt me-2"></i>Déconnexion
            </button>
        </form>
    </li>
</ul>
