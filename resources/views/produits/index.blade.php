@extends('layouts.app')

@section('title', 'Gestion des produits/articles')

@section('content')

    <!-- Statistiques générales -->
    <div class="row mb-4">
        <div class="col-6 col-md-3 mb-3">
            <div class="card stat-card">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <div class="stat-value">{{ $stats['total'] }}</div>
                        <div class="stat-label">Total produits/articles</div>
                    </div>
                    <div class="ms-3">
                        <i class="fas fa-box fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3 mb-3">
            <div class="card stat-card bg-warning text-white">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <div class="stat-value">{{ $stats['stock_faible'] }}</div>
                        <div class="stat-label">Stock faible</div>
                    </div>
                    <div class="ms-3">
                        <i class="fas fa-exclamation-triangle fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3 mb-3">
            <div class="card stat-card bg-danger text-white">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <div class="stat-value">{{ $stats['rupture'] }}</div>
                        <div class="stat-label">En rupture</div>
                    </div>
                    <div class="ms-3">
                        <i class="fas fa-times-circle fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3 mb-3">
            <div class="card stat-card bg-success text-white">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <div class="stat-value">{{ number_format($stats['valeur_stock'], 0, ',', ' ') }} FCFA</div>
                        <div class="stat-label">Valeur stock</div>
                    </div>
                    <div class="ms-3">
                        <i class="fas fa-coins fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistiques par catégorie -->
    @if ($statsParCategorie->count() > 0)
        <div class="row mb-4">
            <div class="col-12">
                <div class="card saas-surface-card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-chart-pie me-2"></i>
                            Statistiques par catégorie
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            @foreach ($statsParCategorie as $stat)
                                <div class="col-6 col-sm-6 col-md-4 col-lg-3 mb-3">
                                    <div class="card border-left-primary">
                                        <div class="card-body">
                                            <div class="d-flex align-items-center">
                                                <div class="flex-grow-1">
                                                    <h6 class="card-title mb-1">{{ $stat->categorie }}</h6>
                                                    <div class="row text-center">
                                                        <div class="col-4">
                                                            <small class="text-muted">produits/articles</small>
                                                            <div class="fw-bold">{{ $stat->total_produits }}</div>
                                                        </div>
                                                        <div class="col-4">
                                                            <small class="text-muted">Stock</small>
                                                            <div class="fw-bold">{{ $stat->total_stock }}</div>
                                                        </div>
                                                        <div class="col-4">
                                                            <small class="text-muted">Valeur</small>
                                                            <div class="fw-bold">
                                                                {{ number_format($stat->valeur_stock, 0, ',', ' ') }}</div>
                                                        </div>
                                                    </div>
                                                    @if ($stat->stock_faible > 0 || $stat->rupture > 0)
                                                        <div class="mt-2">
                                                            @if ($stat->stock_faible > 0)
                                                                <span
                                                                    class="badge bg-warning me-1">{{ $stat->stock_faible }}
                                                                    faible</span>
                                                            @endif
                                                            @if ($stat->rupture > 0)
                                                                <span class="badge bg-danger">{{ $stat->rupture }}
                                                                    rupture</span>
                                                            @endif
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Filtres et recherche -->
    <div class="card saas-surface-card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('produits.index') }}" class="row g-3">
                <div class="col-12 col-md-4">
                    <label for="search" class="form-label">Rechercher</label>
                    <input type="text" class="form-control" id="search" name="search"
                        value="{{ request('search') }}" placeholder="Nom, catégorie ou code produit">
                </div>

                <div class="col-12 col-md-3">
                    <label for="categorie" class="form-label">
                        <i class="fas fa-tags me-1"></i>
                        Catégorie
                    </label>
                    <select class="form-select" id="categorie" name="categorie">
                        <option value="">Toutes les catégories</option>
                        @foreach ($categories as $categorie)
                            <option value="{{ $categorie }}"
                                {{ request('categorie') == $categorie ? 'selected' : '' }}>
                                @php
                                    $icons = [
                                        'Cosmétiques' => 'fas fa-palette',
                                        'Vêtements' => 'fas fa-tshirt',
                                        'Accessoires' => 'fas fa-gem',
                                        'Soins' => 'fas fa-spa',
                                        'Maquillage' => 'fas fa-paint-brush',
                                        'Parfums' => 'fas fa-wind',
                                        'Chaussures' => 'fas fa-shoe-prints',
                                        'Bijoux' => 'fas fa-ring',
                                        'Électronique' => 'fas fa-laptop',
                                        'Maison' => 'fas fa-home',
                                        'Sport' => 'fas fa-dumbbell',
                                        'Livre' => 'fas fa-book',
                                        'Alimentation' => 'fas fa-utensils',
                                        'Santé' => 'fas fa-heart',
                                        'Bébé' => 'fas fa-baby',
                                        'Autres' => 'fas fa-box',
                                    ];
                                    $icon = $icons[$categorie] ?? 'fas fa-tag';
                                @endphp
                                <i class="{{ $icon }} me-2"></i>{{ $categorie }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-12 col-md-3">
                    <label for="stock_status" class="form-label">Statut du stock</label>
                    <select class="form-select" id="stock_status" name="stock_status">
                        <option value="">Tous</option>
                        <option value="disponible" {{ request('stock_status') == 'disponible' ? 'selected' : '' }}>
                            Disponible</option>
                        <option value="faible" {{ request('stock_status') == 'faible' ? 'selected' : '' }}>Stock faible
                        </option>
                        <option value="rupture" {{ request('stock_status') == 'rupture' ? 'selected' : '' }}>En rupture
                        </option>
                    </select>
                </div>

                <div class="col-12 col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="fas fa-search"></i>
                    </button>
                    <a href="{{ route('produits.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-times"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Liste des produits -->
    <div class="card saas-surface-card">
        <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
            <h5 class="card-title mb-0">
                <i class="fas fa-list me-2"></i>
                Liste des produits/articles ({{ $produits->total() }})
            </h5>
            <div class="d-flex flex-wrap gap-2 align-items-center">
                <form method="GET" action="{{ route('produits.index') }}" class="d-flex gap-2">
                    <input type="hidden" name="categorie" value="{{ request('categorie') }}">
                    <input type="hidden" name="stock_status" value="{{ request('stock_status') }}">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text">
                            <i class="fas fa-search text-muted"></i>
                        </span>
                        <input type="text" name="search" class="form-control" placeholder="Rechercher un produit..."
                            value="{{ request('search') }}">
                    </div>
                    <button type="submit" class="btn btn-sm btn-primary">
                        <i class="fas fa-search"></i>
                    </button>
                    @if (request('search'))
                        <a href="{{ route(
                            'produits.index',
                            array_filter([
                                'categorie' => request('categorie'),
                                'stock_status' => request('stock_status'),
                            ]),
                        ) }}"
                            class="btn btn-sm btn-outline-secondary" title="Réinitialiser">
                            <i class="fas fa-times"></i>
                        </a>
                    @endif
                </form>
                <a href="{{ route('produits.import.create') }}" class="btn btn-outline-success">
                    <i class="fas fa-file-excel me-2"></i>
                    Importer Excel
                </a>
                <a href="{{ route('produits.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>
                    Ajouter un produit
                </a>
            </div>
        </div>
        <div class="card-body p-0">
            @if ($produits->count() > 0)
                @if (count($produitsParCategorie) > 0 && !request('categorie') && !request('search'))
                    <!-- Vue organisée par catégorie -->
                    @foreach ($produitsParCategorie as $categorie => $produitsCategorie)
                        <div class="border-bottom">
                            <div class="p-3">
                                <h6 class="mb-0">
                                    @php
                                        $icons = [
                                            'Cosmétiques' => 'fas fa-palette',
                                            'Vêtements' => 'fas fa-tshirt',
                                            'Accessoires' => 'fas fa-gem',
                                            'Soins' => 'fas fa-spa',
                                            'Maquillage' => 'fas fa-paint-brush',
                                            'Parfums' => 'fas fa-wind',
                                            'Chaussures' => 'fas fa-shoe-prints',
                                            'Bijoux' => 'fas fa-ring',
                                            'Électronique' => 'fas fa-laptop',
                                            'Maison' => 'fas fa-home',
                                            'Sport' => 'fas fa-dumbbell',
                                            'Livre' => 'fas fa-book',
                                            'Alimentation' => 'fas fa-utensils',
                                            'Santé' => 'fas fa-heart',
                                            'Bébé' => 'fas fa-baby',
                                            'Autres' => 'fas fa-box',
                                        ];
                                        $icon = $icons[$categorie] ?? 'fas fa-tag';
                                    @endphp
                                    <i class="{{ $icon }} me-2"></i>
                                    {{ $categorie }}
                                    <span class="badge bg-primary ms-2">{{ $produitsCategorie->count() }} produits</span>
                                </h6>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-hover mb-0 saas-table">
                                    <thead>
                                        <tr>
                                            <th>Image</th>
                                            <th>Produit</th>
                                            <th>Stock</th>
                                            <th>Prix d'achat</th>
                                            <th>Prix de vente</th>
                                            <th>Marge</th>
                                            <th>Boutique</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($produitsCategorie as $produit)
                                            <tr>
                                                <td>
                                                    @if ($produit->image)
                                                        <img src="{{ asset($produit->image) }}"
                                                            alt="{{ $produit->nom }}" class="rounded shadow-sm"
                                                            style="width: 60px; height: 60px; object-fit: cover; border: 1px solid #dee2e6;">
                                                    @else
                                                <div class="rounded d-flex align-items-center justify-content-center shadow-sm"
                                                            style="width: 60px; height: 60px; border: 1px solid #dee2e6;">
                                                            <i class="fas fa-image text-muted fa-lg"></i>
                                                        </div>
                                                    @endif
                                                </td>
                                                <td>
                                                    <div>
                                                        <strong>{{ $produit->nom }}</strong>
                                                        <br>
                                                        <small class="text-muted">{{ $produit->code_produit }}</small>
                                                        @if ($produit->barcode)
                                                            <br>
                                                            <small class="text-dark">
                                                                <i class="fas fa-barcode me-1"></i>{{ $produit->barcode }}
                                                            </small>
                                                        @endif
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <span class="me-2">{{ $produit->quantite_stock }}</span>
                                                        @if ($produit->isStockFaible())
                                                            <i class="fas fa-exclamation-triangle text-warning"
                                                                title="Stock faible"></i>
                                                        @elseif($produit->quantite_stock == 0)
                                                            <i class="fas fa-times-circle text-danger"
                                                                title="En rupture"></i>
                                                        @else
                                                            <i class="fas fa-check-circle text-success"
                                                                title="Disponible"></i>
                                                        @endif
                                                    </div>
                                                    <small class="text-muted">Min: {{ $produit->stock_minimum }}</small>
                                                </td>
                                                <td>{{ number_format($produit->prix_achat, 0, ',', ' ') }} FCFA</td>
                                                <td>{{ number_format($produit->prix_vente, 0, ',', ' ') }} FCFA</td>
                                                <td>
                                                    <div>
                                                        <strong>{{ number_format($produit->marge, 0, ',', ' ') }}
                                                            FCFA</strong>
                                                        <br>
                                                        <small
                                                            class="text-muted">{{ number_format($produit->marge_pourcentage, 1) }}%</small>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="badge bg-primary">{{ $produit->boutique->nom }}</span>
                                                </td>
                                                <td>
                                                    <div class="btn-group" role="group">
                                                        <a href="{{ route('produits.show', $produit) }}"
                                                            class="btn btn-sm btn-outline-info" title="Voir">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                        <a href="{{ route('produits.edit', $produit) }}"
                                                            class="btn btn-sm btn-outline-warning" title="Modifier">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <button type="button" class="btn btn-sm btn-outline-danger"
                                                            title="Supprimer"
                                                            onclick="confirmerSuppression('{{ $produit->nom }}', '{{ route('produits.destroy', $produit) }}')">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endforeach
                @else
                    <!-- Vue liste classique -->
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 saas-table">
                            <thead>
                                <tr>
                                    <th>Image</th>
                                    <th>Produit</th>
                                    <th>Catégorie</th>
                                    <th>Stock</th>
                                    <th>Prix d'achat</th>
                                    <th>Prix de vente</th>
                                    <th>Marge</th>
                                    <th>Boutique</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($produits as $produit)
                                    <tr>
                                        <td>
                                            @if ($produit->image)
                                                <img src="{{ asset($produit->image) }}" alt="{{ $produit->nom }}"
                                                    class="rounded shadow-sm"
                                                    style="width: 70px; height: 70px; object-fit: cover; border: 1px solid #dee2e6;">
                                            @else
                                                <div class="rounded d-flex align-items-center justify-content-center shadow-sm"
                                                    style="width: 70px; height: 70px; border: 1px solid #dee2e6;">
                                                    <i class="fas fa-image text-muted fa-lg"></i>
                                                </div>
                                            @endif
                                        </td>
                                        <td>
                                            <div>
                                                <strong>{{ $produit->nom }}</strong>
                                                <br>
                                                <small class="text-muted">{{ $produit->code_produit }}</small>
                                                @if ($produit->barcode)
                                                    <br>
                                                    <small class="text-dark">
                                                        <i class="fas fa-barcode me-1"></i>{{ $produit->barcode }}
                                                    </small>
                                                @endif
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary">{{ $produit->categorie }}</span>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <span class="me-2">{{ $produit->quantite_stock }}</span>
                                                @if ($produit->isStockFaible())
                                                    <i class="fas fa-exclamation-triangle text-warning"
                                                        title="Stock faible"></i>
                                                @elseif($produit->quantite_stock == 0)
                                                    <i class="fas fa-times-circle text-danger" title="En rupture"></i>
                                                @else
                                                    <i class="fas fa-check-circle text-success" title="Disponible"></i>
                                                @endif
                                            </div>
                                            <small class="text-muted">Min: {{ $produit->stock_minimum }}</small>
                                        </td>
                                        <td>{{ number_format($produit->prix_achat, 0, ',', ' ') }} FCFA</td>
                                        <td>{{ number_format($produit->prix_vente, 0, ',', ' ') }} FCFA</td>
                                        <td>
                                            <div>
                                                <strong>{{ number_format($produit->marge, 0, ',', ' ') }} FCFA</strong>
                                                <br>
                                                <small
                                                    class="text-muted">{{ number_format($produit->marge_pourcentage, 1) }}%</small>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-primary">{{ $produit->boutique->nom }}</span>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('produits.show', $produit) }}"
                                                    class="btn btn-sm btn-outline-info" title="Voir">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('produits.edit', $produit) }}"
                                                    class="btn btn-sm btn-outline-warning" title="Modifier">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <button type="button" class="btn btn-sm btn-outline-danger"
                                                    title="Supprimer"
                                                    onclick="confirmerSuppression('{{ $produit->nom }}', '{{ route('produits.destroy', $produit) }}')">
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
                        {{ $produits->links() }}
                    </div>
                @endif
            @else
                <div class="text-center py-5">
                    <i class="fas fa-box fa-3x text-muted mb-3"></i>
                    <h4>Aucun produit trouvé</h4>
                    <p class="text-muted">Commencez par ajouter votre premier produit.</p>
                    <a href="{{ route('produits.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>
                        Ajouter un produit
                    </a>
                </div>
            @endif
        </div>
    </div>

    <!-- Script pour la confirmation de suppression -->
    <script>
        function confirmerSuppression(nomProduit, urlSuppression) {
            // Créer une boîte de dialogue personnalisée avec Bootstrap
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
                                    <h4>Êtes-vous sûr de vouloir supprimer ce produit ?</h4>
                                </div>
                                <div class="alert alert-warning">
                                    <strong>Produit :</strong> ${nomProduit}
                                </div>
                                <div class="alert alert-danger">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    <strong>Attention :</strong> Cette action est irréversible ! Le produit sera définitivement supprimé de votre inventaire.
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
