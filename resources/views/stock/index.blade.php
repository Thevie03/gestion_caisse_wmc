@extends('layouts.app')

@section('content')

    <!-- Statistiques -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card stat-card">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <div class="stat-value">{{ $stats['total_produits'] }}</div>
                        <div class="stat-label">Total produits</div>
                    </div>
                    <div class="ms-3">
                        <i class="fas fa-box fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
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

        <div class="col-md-3 mb-3">
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

        <div class="col-md-3 mb-3">
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

    <!-- Filtres -->
    <div class="card saas-surface-card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('stock.index') }}" class="row g-3">
                <div class="col-md-4">
                    <label for="search" class="form-label">Rechercher</label>
                    <input type="text" class="form-control" id="search" name="search" value="{{ request('search') }}"
                        placeholder="Nom, catégorie ou code produit">
                </div>

                <div class="col-md-3">
                    <label for="stock_status" class="form-label">Statut du stock</label>
                    <select class="form-select" id="stock_status" name="stock_status">
                        <option value="">Tous les statuts</option>
                        <option value="disponible" {{ request('stock_status') == 'disponible' ? 'selected' : '' }}>
                            Disponible</option>
                        <option value="faible" {{ request('stock_status') == 'faible' ? 'selected' : '' }}>Stock faible
                        </option>
                        <option value="rupture" {{ request('stock_status') == 'rupture' ? 'selected' : '' }}>En rupture
                        </option>
                    </select>
                </div>

                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="fas fa-search"></i>
                    </button>
                    <a href="{{ route('stock.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-times"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Liste des produits -->
    <div class="card saas-surface-card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">
                <i class="fas fa-list me-2"></i>
                État du stock ({{ $produits->total() }})
            </h5>
            <div class="btn-group">
                <a href="{{ route('stock.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>
                    Ajouter un mouvement
                </a>
                <a href="{{ route('stock.historique') }}" class="btn btn-outline-info">
                    <i class="fas fa-history me-2"></i>
                    Historique
                </a>
                <a href="{{ route('stock.alertes') }}" class="btn btn-outline-warning">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Alertes
                </a>
            </div>
        </div>
        <div class="card-body p-0">
            @if ($produits->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover mb-0 saas-table">
                        <thead>
                            <tr>
                                <th>Produit</th>
                                <th>Catégorie</th>
                                <th>Stock actuel</th>
                                <th>Stock minimum</th>
                                <th>Statut</th>
                                <th>Valeur stock</th>
                                <th>Boutique</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($produits as $produit)
                                <tr>
                                    <td>
                                        <div>
                                            <strong>{{ $produit->nom }}</strong>
                                            <br>
                                            <small class="text-muted">{{ $produit->code_produit }}</small>
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
                                    </td>
                                    <td>{{ $produit->stock_minimum }}</td>
                                    <td>
                                        @if ($produit->quantite_stock == 0)
                                            <span class="badge bg-danger">Rupture</span>
                                        @elseif($produit->isStockFaible())
                                            <span class="badge bg-warning">Stock faible</span>
                                        @else
                                            <span class="badge bg-success">Disponible</span>
                                        @endif
                                    </td>
                                    <td>
                                        <strong>{{ number_format($produit->quantite_stock * $produit->prix_achat, 0, ',', ' ') }}
                                            FCFA</strong>
                                    </td>
                                    <td>
                                        <span class="badge bg-primary">{{ $produit->boutique->nom }}</span>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('stock.show', $produit) }}"
                                                class="btn btn-sm btn-outline-info" title="Voir l'historique">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('stock.create') }}?produit={{ $produit->id }}"
                                                class="btn btn-sm btn-outline-primary" title="Ajuster le stock">
                                                <i class="fas fa-edit"></i>
                                            </a>
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
            @else
                <div class="text-center py-5">
                    <i class="fas fa-box fa-3x text-muted mb-3"></i>
                    <h4>Aucun produit trouvé</h4>
                    <p class="text-muted">Commencez par ajouter des produits à votre inventaire.</p>
                    <a href="{{ route('produits.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>
                        Ajouter un produit
                    </a>
                </div>
            @endif
        </div>
    </div>
@endsection
