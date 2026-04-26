@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <!-- En-tête -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h2 class="mb-0">
                            <i class="fas fa-tags me-2 text-primary"></i>
                            Gestion des catégories
                        </h2>
                        <p class="text-muted mb-0">Organisez vos produits par catégories</p>
                    </div>
                    <a href="{{ route('categories.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>
                        Ajouter une catégorie
                    </a>
                </div>
            </div>
        </div>

        <!-- Filtres -->
        @if (isset($boutiques) && $boutiques->isNotEmpty())
            <div class="row mb-4">
                <div class="col-md-4">
                    <form method="GET" action="{{ route('categories.index') }}" class="card shadow-sm">
                        <div class="card-header py-2">
                            <h6 class="m-0 font-weight-bold text-primary">
                                <i class="fas fa-store me-2"></i>
                                Boutique affichée
                            </h6>
                        </div>
                        <div class="card-body">
                            <select class="form-select" name="boutique_id" onchange="this.form.submit()">
                                <option value="">Toutes les boutiques</option>
                                @foreach ($boutiques as $boutique)
                                    <option value="{{ $boutique->id }}"
                                        {{ request('boutique_id', $boutiqueId) == $boutique->id ? 'selected' : '' }}>
                                        {{ $boutique->nom }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </form>
                </div>
            </div>
        @endif

        <!-- Statistiques -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card border-left-primary">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <h6 class="text-primary text-uppercase mb-1">Total</h6>
                                <h3 class="mb-0">{{ $stats['total'] }}</h3>
                            </div>
                            <div class="text-primary">
                                <i class="fas fa-tags fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-left-success">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <h6 class="text-success text-uppercase mb-1">Actives</h6>
                                <h3 class="mb-0">{{ $stats['actives'] }}</h3>
                            </div>
                            <div class="text-success">
                                <i class="fas fa-check-circle fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-left-info">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <h6 class="text-info text-uppercase mb-1">Avec produits</h6>
                                <h3 class="mb-0">{{ $stats['avec_produits'] }}</h3>
                            </div>
                            <div class="text-info">
                                <i class="fas fa-box fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Liste des catégories -->
        <div class="card">
            <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
                <h5 class="card-title mb-0">
                    <i class="fas fa-list me-2"></i>
                    Liste des catégories ({{ $categories->total() }})
                </h5>
                <form method="GET" action="{{ route('categories.index') }}" class="d-flex gap-2 align-items-center">
                    @if (request()->filled('boutique_id') || $boutiqueId)
                        <input type="hidden" name="boutique_id" value="{{ request('boutique_id', $boutiqueId) }}">
                    @endif
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white">
                            <i class="fas fa-search text-muted"></i>
                        </span>
                        <input type="text" name="search" class="form-control" placeholder="Rechercher une catégorie..."
                            value="{{ request('search') }}">
                    </div>
                    <button type="submit" class="btn btn-sm btn-primary">
                        <i class="fas fa-search"></i>
                    </button>
                    @if (request('search'))
                        <a href="{{ route('categories.index', array_filter(['boutique_id' => request('boutique_id', $boutiqueId)])) }}"
                            class="btn btn-sm btn-outline-secondary" title="Réinitialiser">
                            <i class="fas fa-times"></i>
                        </a>
                    @endif
                </form>
            </div>
            <div class="card-body p-0">
                @if ($categories->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Catégorie</th>
                                    @if (isset($boutiques) && $boutiques->isNotEmpty() && !request('boutique_id') && !$boutiqueId)
                                        <th>Boutique</th>
                                    @endif
                                    <th>Description</th>
                                    <th>Produits</th>
                                    <th>Statut</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($categories as $category)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="me-3">
                                                    <i
                                                        class="{{ $category->icone_formatee }} text-{{ $category->couleur_formatee }} fa-lg"></i>
                                                </div>
                                                <div>
                                                    <strong>{{ $category->nom }}</strong>
                                                    <br>
                                                    <small class="text-muted">Créée le
                                                        {{ $category->created_at->format('d/m/Y') }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        @if (isset($boutiques) && $boutiques->isNotEmpty() && !request('boutique_id') && !$boutiqueId)
                                            <td>
                                                <span class="badge bg-info text-dark">
                                                    {{ $category->boutique->nom ?? 'Non affectée' }}
                                                </span>
                                            </td>
                                        @endif
                                        <td>
                                            @if ($category->description)
                                                <span
                                                    class="text-muted">{{ Str::limit($category->description, 50) }}</span>
                                            @else
                                                <span class="text-muted">Aucune description</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-primary">{{ $category->produits_count }} produits</span>
                                        </td>
                                        <td>
                                            @if ($category->active)
                                                <span class="badge bg-success">Active</span>
                                            @else
                                                <span class="badge bg-secondary">Inactive</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('categories.show', $category) }}"
                                                    class="btn btn-sm btn-outline-info" title="Voir">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('categories.edit', $category) }}"
                                                    class="btn btn-sm btn-outline-warning" title="Modifier">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <form method="POST" action="{{ route('categories.toggle', $category) }}"
                                                    class="d-inline">
                                                    @csrf
                                                    <button type="submit"
                                                        class="btn btn-sm btn-outline-{{ $category->active ? 'secondary' : 'success' }}"
                                                        title="{{ $category->active ? 'Désactiver' : 'Activer' }}">
                                                        <i class="fas fa-{{ $category->active ? 'pause' : 'play' }}"></i>
                                                    </button>
                                                </form>
                                                <button type="button" class="btn btn-sm btn-outline-danger"
                                                    title="Supprimer"
                                                    onclick="confirmerSuppression('{{ $category->nom }}', '{{ route('categories.destroy', $category) }}')">
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
                        {{ $categories->links() }}
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="fas fa-tags fa-3x text-muted mb-3"></i>
                        <h4>Aucune catégorie trouvée</h4>
                        <p class="text-muted">Commencez par créer votre première catégorie.</p>
                        <a href="{{ route('categories.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i>
                            Ajouter une catégorie
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Script pour la confirmation de suppression -->
    <script>
        function confirmerSuppression(nomCategorie, urlSuppression) {
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
                                <h4>Êtes-vous sûr de vouloir supprimer cette catégorie ?</h4>
                            </div>
                            <div class="alert alert-warning">
                                <strong>Catégorie :</strong> ${nomCategorie}
                            </div>
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <strong>Attention :</strong> Cette action est irréversible ! La catégorie sera définitivement supprimée.
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

            // Supprimer l'ancien modal s'il existe
            const existingModal = document.getElementById('modalSuppression');
            if (existingModal) {
                existingModal.remove();
            }

            // Ajouter le nouveau modal au DOM
            document.body.insertAdjacentHTML('beforeend', modalHtml);

            // Afficher le modal
            const modal = new bootstrap.Modal(document.getElementById('modalSuppression'));
            modal.show();
        }
    </script>
@endsection
