@extends('layouts.app')

@section('content')

    <!-- Statistiques des alertes -->
    <div class="row mb-4">
        <div class="col-md-4 mb-3">
            <div class="card stat-card bg-danger text-white">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <div class="stat-value">{{ $rupture->count() }}</div>
                        <div class="stat-label">En rupture</div>
                    </div>
                    <div class="ms-3">
                        <i class="fas fa-times-circle fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-3">
            <div class="card stat-card bg-warning text-white">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <div class="stat-value">{{ $stockFaible->count() }}</div>
                        <div class="stat-label">Stock faible</div>
                    </div>
                    <div class="ms-3">
                        <i class="fas fa-exclamation-triangle fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-3">
            <div class="card stat-card bg-info text-white">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <div class="stat-value">{{ $rupture->count() + $stockFaible->count() }}</div>
                        <div class="stat-label">Total alertes</div>
                    </div>
                    <div class="ms-3">
                        <i class="fas fa-bell fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Produits en rupture -->
    @if ($rupture->count() > 0)
        <div class="card mb-4">
            <div class="card-header bg-danger text-white">
                <h5 class="card-title mb-0">
                    <i class="fas fa-times-circle me-2"></i>
                    Produits en rupture ({{ $rupture->count() }})
                </h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Produit</th>
                                <th>Catégorie</th>
                                <th>Stock actuel</th>
                                <th>Stock minimum</th>
                                <th>Prix d'achat</th>
                                <th>Boutique</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($rupture as $produit)
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
                                        <span class="badge bg-danger">{{ $produit->quantite_stock }}</span>
                                    </td>
                                    <td>{{ $produit->stock_minimum }}</td>
                                    <td>{{ number_format($produit->prix_achat, 0, ',', ' ') }} FCFA</td>
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
                                                class="btn btn-sm btn-outline-primary" title="Réapprovisionner">
                                                <i class="fas fa-plus"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    <!-- Produits en stock faible -->
    @if ($stockFaible->count() > 0)
        <div class="card mb-4">
            <div class="card-header bg-warning text-white">
                <h5 class="card-title mb-0">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Produits en stock faible ({{ $stockFaible->count() }})
                </h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Produit</th>
                                <th>Catégorie</th>
                                <th>Stock actuel</th>
                                <th>Stock minimum</th>
                                <th>Prix d'achat</th>
                                <th>Boutique</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($stockFaible as $produit)
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
                                        <span class="badge bg-warning">{{ $produit->quantite_stock }}</span>
                                    </td>
                                    <td>{{ $produit->stock_minimum }}</td>
                                    <td>{{ number_format($produit->prix_achat, 0, ',', ' ') }} FCFA</td>
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
                                                class="btn btn-sm btn-outline-primary" title="Réapprovisionner">
                                                <i class="fas fa-plus"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    <!-- Aucune alerte -->
    @if ($rupture->count() == 0 && $stockFaible->count() == 0)
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                <h4>Aucune alerte de stock</h4>
                <p class="text-muted">Tous vos produits ont un stock suffisant !</p>
                <a href="{{ route('stock.index') }}" class="btn btn-primary">
                    <i class="fas fa-list me-2"></i>
                    Voir tous les produits
                </a>
            </div>
        </div>
    @endif
@endsection




































