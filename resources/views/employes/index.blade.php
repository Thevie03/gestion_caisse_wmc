@extends('layouts.app')

@section('content')

    <!-- En-tête avec bouton d'ajout -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="mb-0">
                        <i class="fas fa-users me-2 text-primary"></i>
                        Gestion des employés
                    </h2>
                    <p class="text-muted mb-0">Gérez vos employés et leurs permissions</p>
                </div>
                <a href="{{ route('employes.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>
                    Ajouter un employé
                </a>
            </div>
        </div>
    </div>

    <!-- Statistiques -->
    <div class="row mb-4">
        <div class="col-md-4 mb-3">
            <div class="card stat-card">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <div class="stat-value">{{ $stats['total_employes'] }}</div>
                        <div class="stat-label">Total employés</div>
                    </div>
                    <div class="ms-3">
                        <i class="fas fa-users fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-3">
            <div class="card stat-card bg-success text-white">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <div class="stat-value">{{ $stats['employes_actifs'] }}</div>
                        <div class="stat-label">Employés actifs</div>
                    </div>
                    <div class="ms-3">
                        <i class="fas fa-user-check fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-3">
            <div class="card stat-card bg-warning text-white">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <div class="stat-value">{{ $stats['employes_inactifs'] }}</div>
                        <div class="stat-label">Employés inactifs</div>
                    </div>
                    <div class="ms-3">
                        <i class="fas fa-user-times fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtres -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('employes.index') }}" class="row g-3">
                <div class="col-md-4">
                    <label for="search" class="form-label">Rechercher</label>
                    <input type="text" class="form-control" id="search" name="search" value="{{ request('search') }}"
                        placeholder="Nom, email ou téléphone">
                </div>

                <div class="col-md-3">
                    <label for="boutique_id" class="form-label">Boutique</label>
                    <select class="form-select" id="boutique_id" name="boutique_id">
                        <option value="">Toutes les boutiques</option>
                        @foreach ($boutiques as $boutique)
                            <option value="{{ $boutique->id }}"
                                {{ request('boutique_id') == $boutique->id ? 'selected' : '' }}>
                                {{ $boutique->nom }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <label for="statut" class="form-label">Statut</label>
                    <select class="form-select" id="statut" name="statut">
                        <option value="">Tous les statuts</option>
                        <option value="actif" {{ request('statut') == 'actif' ? 'selected' : '' }}>Actif</option>
                        <option value="inactif" {{ request('statut') == 'inactif' ? 'selected' : '' }}>Inactif</option>
                    </select>
                </div>

                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="fas fa-search"></i>
                    </button>
                    <a href="{{ route('employes.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-times"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Liste des employés -->
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">
                <i class="fas fa-list me-2"></i>
                Employés ({{ $employes->total() }})
            </h5>
        </div>
        <div class="card-body p-0">
            @if ($employes->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Employé</th>
                                <th>Contact</th>
                                <th>Boutique</th>
                                <th>Statut</th>
                                <th>Créé le</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($employes as $employe)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3"
                                                style="width: 40px; height: 40px;">
                                                <i class="fas fa-user"></i>
                                            </div>
                                            <div>
                                                <strong>{{ $employe->name }}</strong>
                                                <br>
                                                <small class="text-muted">ID: {{ $employe->id }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div>
                                            <strong>{{ $employe->email }}</strong>
                                            <br>
                                            <small class="text-muted">
                                                <i class="fas fa-phone me-1"></i>
                                                {{ $employe->telephone }}
                                            </small>
                                        </div>
                                    </td>
                                    <td>
                                        @if (method_exists($employe, 'isSuperAdmin') && $employe->isSuperAdmin())
                                            <span class="badge bg-danger text-white">Super Admin</span>
                                        @elseif ($employe->boutique)
                                            <span class="badge bg-primary">{{ $employe->boutique->nom }}</span>
                                        @else
                                            <span class="badge bg-warning text-dark">Aucune boutique</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($employe->actif)
                                            <span class="badge bg-success">
                                                <i class="fas fa-check me-1"></i>
                                                Actif
                                            </span>
                                        @else
                                            <span class="badge bg-danger">
                                                <i class="fas fa-times me-1"></i>
                                                Inactif
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        <div>
                                            <strong>{{ $employe->created_at->format('d/m/Y') }}</strong>
                                            <br>
                                            <small class="text-muted">{{ $employe->created_at->format('H:i') }}</small>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('employes.show', $employe) }}"
                                                class="btn btn-sm btn-outline-info" title="Voir le profil">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('employes.edit', $employe) }}"
                                                class="btn btn-sm btn-outline-primary" title="Modifier">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="{{ route('employes.ventes', $employe) }}"
                                                class="btn btn-sm btn-outline-success" title="Voir les ventes">
                                                <i class="fas fa-shopping-cart"></i>
                                            </a>
                                            <a href="{{ route('employes.permissions', $employe) }}"
                                                class="btn btn-sm btn-outline-warning" title="Gérer les permissions">
                                                <i class="fas fa-user-shield"></i>
                                            </a>
                                            <form method="POST" action="{{ route('employes.toggle-status', $employe) }}"
                                                style="display: inline;">
                                                @csrf
                                                <button type="submit"
                                                    class="btn btn-sm {{ $employe->actif ? 'btn-outline-warning' : 'btn-outline-success' }}"
                                                    title="{{ $employe->actif ? 'Désactiver' : 'Activer' }}">
                                                    <i class="fas fa-{{ $employe->actif ? 'pause' : 'play' }}"></i>
                                                </button>
                                            </form>
                                            <button type="button" class="btn btn-sm btn-outline-danger"
                                                title="Supprimer"
                                                onclick="confirmerSuppression('{{ $employe->name }}', '{{ route('employes.destroy', $employe) }}')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="card-footer">
                    {{ $employes->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-users fa-3x text-muted mb-3"></i>
                    <h4>Aucun employé trouvé</h4>
                    <p class="text-muted">Commencez par créer vos premiers employés.</p>
                    <a href="{{ route('employes.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>
                        Ajouter un employé
                    </a>
                </div>
            @endif
        </div>
    </div>

    <!-- Script pour la confirmation de suppression -->
    <script>
        function confirmerSuppression(nomEmploye, urlSuppression) {
            const modalHtml = `
                <div class="modal fade" id="modalSuppression" tabindex="-1" aria-labelledby="modalSuppressionLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header bg-danger text-white">
                                <h5 class="modal-title" id="modalSuppressionLabel">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    Confirmation de suppression
                                </h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="text-center mb-3">
                                    <i class="fas fa-trash fa-3x text-danger mb-3"></i>
                                    <h4>Êtes-vous sûr de vouloir supprimer cet employé ?</h4>
                                </div>
                                <div class="alert alert-warning">
                                    <strong>Employé :</strong> ${nomEmploye}
                                </div>
                                <div class="alert alert-danger">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    <strong>Attention :</strong> Cette action est irréversible ! L'employé sera définitivement supprimé.
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                    <i class="fas fa-times me-2"></i>
                                    Annuler
                                </button>
                                <form method="POST" action="${urlSuppression}" style="display: inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger">
                                        <i class="fas fa-trash me-2"></i>
                                        Supprimer définitivement
                                    </button>
                                </form>
                </div>
            </div>
        </div>
    </div>
            `;

            const existingModal = document.getElementById('modalSuppression');
            if (existingModal) {
                existingModal.remove();
            }

            document.body.insertAdjacentHTML('beforeend', modalHtml);

            const modal = new bootstrap.Modal(document.getElementById('modalSuppression'));
            modal.show();
        }
    </script>
@endsection
