@extends('layouts.app')

@section('content')

    <!-- Statistiques -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card stat-card">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <div class="stat-value">{{ $stats['total_depenses'] }}</div>
                        <div class="stat-label">Total dépenses</div>
                    </div>
                    <div class="ms-3">
                        <i class="fas fa-receipt fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card stat-card bg-danger text-white">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <div class="stat-value">{{ number_format($stats['montant_total'], 0, ',', ' ') }} FCFA</div>
                        <div class="stat-label">Montant total</div>
                    </div>
                    <div class="ms-3">
                        <i class="fas fa-money-bill-wave fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card stat-card bg-warning text-white">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <div class="stat-value">{{ number_format($stats['moyenne_mensuelle'], 0, ',', ' ') }} FCFA</div>
                        <div class="stat-label">Ce mois</div>
                    </div>
                    <div class="ms-3">
                        <i class="fas fa-calendar-alt fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card stat-card bg-info text-white">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <div class="stat-value">{{ $stats['categorie_principale']->categorie ?? 'N/A' }}</div>
                        <div class="stat-label">Catégorie principale</div>
                    </div>
                    <div class="ms-3">
                        <i class="fas fa-tags fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtres -->
    <div class="card saas-surface-card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('depenses.index') }}" class="row g-3">
                <div class="col-md-3">
                    <label for="search" class="form-label">Rechercher</label>
                    <input type="text" class="form-control" id="search" name="search" value="{{ request('search') }}"
                        placeholder="Description ou catégorie">
                </div>

                <div class="col-md-2">
                    <label for="categorie" class="form-label">Catégorie</label>
                    <select class="form-select" id="categorie" name="categorie">
                        <option value="">Toutes les catégories</option>
                        @foreach ($categories as $categorie)
                            <option value="{{ $categorie }}" {{ request('categorie') == $categorie ? 'selected' : '' }}>
                                {{ $categorie }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2">
                    <label for="date_debut" class="form-label">Date début</label>
                    <input type="date" class="form-control" id="date_debut" name="date_debut"
                        value="{{ request('date_debut') }}">
                </div>

                <div class="col-md-2">
                    <label for="date_fin" class="form-label">Date fin</label>
                    <input type="date" class="form-control" id="date_fin" name="date_fin"
                        value="{{ request('date_fin') }}">
                </div>

                <div class="col-md-2">
                    <label for="montant_min" class="form-label">Montant min</label>
                    <input type="number" step="0.01" class="form-control" id="montant_min" name="montant_min"
                        value="{{ request('montant_min') }}" placeholder="0">
                </div>

                <div class="col-md-1 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="fas fa-search"></i>
                    </button>
                    <a href="{{ route('depenses.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-times"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Liste des dépenses -->
    <div class="card saas-surface-card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">
                <i class="fas fa-list me-2"></i>
                Dépenses ({{ $depenses->total() }})
            </h5>
            <a href="{{ route('depenses.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i>
                Ajouter une dépense
            </a>
        </div>
        <div class="card-body p-0">
            @if ($depenses->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover mb-0 saas-table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Description</th>
                                <th>Catégorie</th>
                                <th>Montant</th>
                                <th>Boutique</th>
                                <th>Utilisateur</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($depenses as $depense)
                                <tr>
                                    <td>
                                        <div>
                                            <strong>{{ $depense->date_depense->format('d/m/Y') }}</strong>
                                            <br>
                                            <small class="text-muted">{{ $depense->created_at->format('H:i') }}</small>
                                        </div>
                                    </td>
                                    <td>
                                        <div>
                                            <strong>{{ $depense->description }}</strong>
                                            @if ($depense->notes)
                                                <br>
                                                <small class="text-muted">{{ Str::limit($depense->notes, 50) }}</small>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary">{{ $depense->categorie }}</span>
                                    </td>
                                    <td>
                                        <strong class="text-danger">{{ number_format($depense->montant, 0, ',', ' ') }}
                                            FCFA</strong>
                                    </td>
                                    <td>
                                        <span class="badge bg-primary">{{ $depense->boutique->nom }}</span>
                                    </td>
                                    <td>
                                        <div>
                                            <strong>{{ $depense->user->name }}</strong>
                                            <br>
                                            <small class="text-muted">{{ $depense->user->email }}</small>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('depenses.show', $depense) }}"
                                                class="btn btn-sm btn-outline-info" title="Voir">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('depenses.edit', $depense) }}"
                                                class="btn btn-sm btn-outline-primary" title="Modifier">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button type="button" class="btn btn-sm btn-outline-danger"
                                                title="Supprimer"
                                                onclick="confirmerSuppression('{{ $depense->description }}', '{{ route('depenses.destroy', $depense) }}')">
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
                    {{ $depenses->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-receipt fa-3x text-muted mb-3"></i>
                    <h4>Aucune dépense trouvée</h4>
                    <p class="text-muted">Commencez par enregistrer vos premières dépenses.</p>
                    <a href="{{ route('depenses.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>
                        Ajouter une dépense
                    </a>
                </div>
            @endif
        </div>
    </div>

    <!-- Script pour la confirmation de suppression -->
    <script>
        function confirmerSuppression(description, urlSuppression) {
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
                                    <h4>Êtes-vous sûr de vouloir supprimer cette dépense ?</h4>
                                </div>
                                <div class="alert alert-warning">
                                    <strong>Dépense :</strong> ${description}
                                </div>
                                <div class="alert alert-danger">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    <strong>Attention :</strong> Cette action est irréversible ! La dépense sera définitivement supprimée.
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
