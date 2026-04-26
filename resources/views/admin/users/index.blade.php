@extends('layouts.app')

@section('content')
    <div class="container-fluid px-4 py-4">
        <!-- En-tête -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-1 fw-bold">Gestion des Commerçants</h1>
                <p class="text-muted mb-0">Gérez les comptes clients et leurs abonnements</p>
            </div>
            <a href="{{ route('admin.users.create') }}" class="btn btn-primary">
                <i class="fas fa-user-plus me-2"></i>Nouveau commerçant
            </a>
        </div>

        <!-- Statistiques -->
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <p class="text-muted small mb-1">Total Commerçants</p>
                                <h3 class="mb-0 fw-bold">{{ $stats['total'] }}</h3>
                            </div>
                            <div class="ms-3">
                                <div class="bg-primary bg-opacity-10 rounded-circle p-3">
                                    <i class="fas fa-users fa-2x text-primary"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <p class="text-muted small mb-1">Commerçants Actifs</p>
                                <h3 class="mb-0 fw-bold text-success">{{ $stats['actifs'] }}</h3>
                            </div>
                            <div class="ms-3">
                                <div class="bg-success bg-opacity-10 rounded-circle p-3">
                                    <i class="fas fa-check-circle fa-2x text-success"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <p class="text-muted small mb-1">Commerçants Suspendus</p>
                                <h3 class="mb-0 fw-bold text-danger">{{ $stats['suspendus'] }}</h3>
                            </div>
                            <div class="ms-3">
                                <div class="bg-danger bg-opacity-10 rounded-circle p-3">
                                    <i class="fas fa-ban fa-2x text-danger"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filtres et recherche -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <form method="GET" action="{{ route('admin.users.index') }}" class="row g-3">
                    <div class="col-md-4">
                        <label for="search" class="form-label small fw-semibold">Rechercher</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="fas fa-search text-muted"></i></span>
                            <input type="text" name="search" id="search" value="{{ request('search') }}"
                                placeholder="Nom, email, téléphone..." class="form-control">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label for="statut" class="form-label small fw-semibold">Statut</label>
                        <select name="statut" id="statut" class="form-select">
                            <option value="">Tous les statuts</option>
                            <option value="actif" @selected(request('statut') === 'actif')>Actifs</option>
                            <option value="inactif" @selected(request('statut') === 'inactif')>Suspendus</option>
                        </select>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-filter me-2"></i>Filtrer
                        </button>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        @if (request('search') || request('statut'))
                            <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary w-100">
                                <i class="fas fa-times me-2"></i>Réinitialiser
                            </a>
                        @endif
                    </div>
                </form>
            </div>
        </div>

        <!-- Tableau des commerçants -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom">
                <h5 class="mb-0 fw-semibold">
                    <i class="fas fa-list me-2 text-primary"></i>
                    Liste des Commerçants
                </h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="px-4 py-3 text-nowrap fw-semibold">Commerçant</th>
                                <th class="px-4 py-3 text-nowrap fw-semibold">Contact</th>
                                <th class="px-4 py-3 text-nowrap fw-semibold">Boutique</th>
                                <th class="px-4 py-3 text-nowrap fw-semibold">Statut Compte</th>
                                <th class="px-4 py-3 text-nowrap fw-semibold">Abonnement</th>
                                <th class="px-4 py-3 text-nowrap fw-semibold">Dernière Connexion</th>
                                <th class="px-4 py-3 text-center fw-semibold">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($users as $user)
                                <tr>
                                    <td class="px-4 py-3">
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-sm me-3">
                                                <div
                                                    class="avatar-title bg-primary bg-opacity-10 text-primary rounded-circle">
                                                    <i class="fas fa-user"></i>
                                                </div>
                                            </div>
                                            <div>
                                                <p class="mb-0 fw-semibold text-dark">{{ $user->name }}</p>
                                                <p class="mb-0 text-muted small">
                                                    <i class="fas fa-phone-alt me-1"></i>{{ $user->telephone ?? 'N/A' }}
                                                </p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <p class="mb-0 text-dark">
                                            <i class="fas fa-envelope me-2 text-muted"></i>{{ $user->email }}
                                        </p>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div>
                                            <p class="mb-0 fw-semibold text-dark">
                                                <i class="fas fa-store me-2 text-success"></i>
                                                {{ $user->boutique?->nom ?? 'Non défini' }}
                                            </p>
                                            <span class="badge bg-light text-dark small">
                                                {{ $user->boutique?->devise ?? 'FCFA' }}
                                            </span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="badge {{ $user->actif ? 'bg-success' : 'bg-danger' }} px-3 py-2">
                                            <i class="fas fa-{{ $user->actif ? 'check-circle' : 'ban' }} me-1"></i>
                                            {{ $user->actif ? 'Actif' : 'Suspendu' }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3">
                                        @php $abo = $user->abonnementActif; @endphp
                                        @if ($abo)
                                            <div>
                                                <span class="badge bg-info px-3 py-2 mb-1">
                                                    <i class="fas fa-credit-card me-1"></i>
                                                    {{ $abo->type_label }}
                                                </span>
                                                <p class="mb-0 text-muted small">
                                                    <i class="fas fa-calendar-alt me-1"></i>
                                                    Expire le {{ $abo->date_expiration->format('d/m/Y') }}
                                                </p>
                                            </div>
                                        @else
                                            <span class="badge bg-danger px-3 py-2">
                                                <i class="fas fa-exclamation-triangle me-1"></i>Aucun
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">
                                        @if ($user->last_login_at)
                                            <p class="mb-0 text-dark">
                                                <i class="fas fa-clock me-2 text-muted"></i>
                                                {{ \Carbon\Carbon::parse($user->last_login_at)->diffForHumans() }}
                                            </p>
                                            <small class="text-muted">
                                                {{ \Carbon\Carbon::parse($user->last_login_at)->format('d/m/Y H:i') }}
                                            </small>
                                        @else
                                            <span class="badge bg-secondary">
                                                <i class="fas fa-times-circle me-1"></i>Jamais connecté
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('admin.users.show', $user) }}"
                                                class="btn btn-sm btn-outline-primary" data-bs-toggle="tooltip"
                                                title="Voir détails">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('admin.users.edit', $user) }}"
                                                class="btn btn-sm btn-outline-warning" data-bs-toggle="tooltip"
                                                title="Modifier">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button type="button" class="btn btn-sm btn-outline-danger"
                                                data-bs-toggle="tooltip" title="Supprimer"
                                                onclick="confirmDeleteUser({{ $user->id }}, '{{ $user->name }}', '{{ $user->email }}')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                            <div class="btn-group" role="group">
                                                <button type="button"
                                                    class="btn btn-sm btn-outline-secondary dropdown-toggle"
                                                    data-bs-toggle="dropdown" aria-expanded="false">
                                                    <i class="fas fa-ellipsis-v"></i>
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-end">
                                                    <li>
                                                        <a class="dropdown-item"
                                                            href="{{ route('admin.users.show', $user) }}">
                                                            <i class="fas fa-eye me-2 text-primary"></i>Voir détails
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item"
                                                            href="{{ route('admin.boutiques.supervision', $user->boutique_id ?? 0) }}">
                                                            <i class="fas fa-store me-2 text-success"></i>Superviser
                                                            boutique
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item"
                                                            href="{{ route('admin.users.edit', $user) }}">
                                                            <i class="fas fa-edit me-2 text-warning"></i>Modifier
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item" href="#"
                                                            onclick="resetPasswordModal({{ $user->id }}, '{{ $user->email }}')">
                                                            <i class="fas fa-key me-2 text-info"></i>Réinitialiser mot de
                                                            passe
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item"
                                                            href="{{ route('admin.users.show', $user) }}#abonnements">
                                                            <i class="fas fa-credit-card me-2 text-primary"></i>Gérer
                                                            abonnement
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <hr class="dropdown-divider">
                                                    </li>
                                                    <li>
                                                        <form action="{{ route('admin.users.suspend', $user) }}"
                                                            method="POST" class="d-inline">
                                                            @csrf
                                                            <button type="submit"
                                                                class="dropdown-item {{ $user->actif ? 'text-warning' : 'text-success' }}">
                                                                <i
                                                                    class="fas fa-{{ $user->actif ? 'ban' : 'check' }} me-2"></i>
                                                                {{ $user->actif ? 'Suspendre' : 'Réactiver' }}
                                                            </button>
                                                        </form>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-4 py-5 text-center">
                                        <div class="py-4">
                                            <i class="fas fa-users fa-3x text-muted mb-3"></i>
                                            <p class="text-muted mb-0">Aucun commerçant trouvé.</p>
                                            @if (request('search') || request('statut'))
                                                <a href="{{ route('admin.users.index') }}"
                                                    class="btn btn-sm btn-outline-primary mt-2">
                                                    Réinitialiser les filtres
                                                </a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if ($users->hasPages())
                <div class="card-footer bg-white border-top">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="text-muted small">
                            Affichage de {{ $users->firstItem() }} à {{ $users->lastItem() }} sur {{ $users->total() }}
                            résultats
                        </div>
                        <div>
                            {{ $users->withQueryString()->links() }}
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Modal pour réinitialiser le mot de passe -->
    <div class="modal fade" id="resetPasswordModal" tabindex="-1" aria-labelledby="resetPasswordModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-warning text-white">
                    <h5 class="modal-title" id="resetPasswordModalLabel">
                        <i class="fas fa-key me-2"></i>Réinitialiser le mot de passe
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <form id="reset-password-form" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            Réinitialisation du mot de passe pour : <strong id="user-email-display"></strong>
                        </div>
                        <div class="mb-3">
                            <label for="new-password" class="form-label fw-semibold">Nouveau mot de passe</label>
                            <div class="input-group">
                                <input type="password" name="password" id="new-password" class="form-control" required
                                    minlength="8">
                                <button type="button" class="btn btn-outline-secondary" onclick="generatePassword()">
                                    <i class="fas fa-dice"></i> Générer
                                </button>
                                <button type="button" class="btn btn-outline-secondary"
                                    onclick="togglePasswordVisibility('new-password')">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <small class="form-text text-muted">Minimum 8 caractères.</small>
                        </div>
                        <div class="mb-3">
                            <label for="password-confirmation" class="form-label fw-semibold">Confirmer le mot de
                                passe</label>
                            <div class="input-group">
                                <input type="password" name="password_confirmation" id="password-confirmation"
                                    class="form-control" required>
                                <button type="button" class="btn btn-outline-secondary"
                                    onclick="togglePasswordVisibility('password-confirmation')">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-warning">
                            <i class="fas fa-save me-2"></i>Réinitialiser
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <style>
        .avatar-sm {
            width: 40px;
            height: 40px;
        }

        .avatar-title {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
        }

        .table th {
            font-size: 0.875rem;
            color: #495057;
        }

        .table td {
            vertical-align: middle;
        }

        .card {
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .card:hover {
            transform: translateY(-2px);
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
        }
    </style>

    <!-- Modal de confirmation de suppression -->
    <div class="modal fade" id="deleteUserModal" tabindex="-1" aria-labelledby="deleteUserModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="deleteUserModalLabel">
                        <i class="fas fa-exclamation-triangle me-2"></i>Confirmer la suppression
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Attention !</strong> Cette action est irréversible.
                    </div>
                    <p class="mb-2">Vous êtes sur le point de supprimer définitivement :</p>
                    <ul class="list-unstyled mb-3">
                        <li><strong>Nom :</strong> <span id="delete-user-name"></span></li>
                        <li><strong>Email :</strong> <span id="delete-user-email"></span></li>
                    </ul>
                    <p class="text-danger mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        Cette action supprimera également la boutique associée et toutes les données liées (abonnements,
                        paramètres).
                        <strong>Les ventes, produits et dépenses doivent être supprimés ou transférés avant la
                            suppression.</strong>
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-2"></i>Annuler
                    </button>
                    <form id="delete-user-form" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-trash me-2"></i>Supprimer définitivement
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function resetPasswordModal(userId, userEmail) {
            const form = document.getElementById('reset-password-form');
            form.action = `/admin/users/${userId}/reset-password`;
            document.getElementById('user-email-display').textContent = userEmail;
            const modal = new bootstrap.Modal(document.getElementById('resetPasswordModal'));
            modal.show();
        }

        function confirmDeleteUser(userId, userName, userEmail) {
            const form = document.getElementById('delete-user-form');
            form.action = `/admin/users/${userId}`;
            document.getElementById('delete-user-name').textContent = userName;
            document.getElementById('delete-user-email').textContent = userEmail;
            const modal = new bootstrap.Modal(document.getElementById('deleteUserModal'));
            modal.show();
        }

        function togglePasswordVisibility(id) {
            const input = document.getElementById(id);
            const icon = event.currentTarget.querySelector('i');
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }

        function generatePassword() {
            const length = 12;
            const charset = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*";
            let password = "";
            for (let i = 0; i < length; i++) {
                password += charset.charAt(Math.floor(Math.random() * charset.length));
            }
            document.getElementById('new-password').value = password;
            document.getElementById('password-confirmation').value = password;
        }

        // Initialiser les tooltips Bootstrap
        document.addEventListener('DOMContentLoaded', function() {
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        });
    </script>
@endsection
