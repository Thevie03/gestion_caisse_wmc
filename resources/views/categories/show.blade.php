@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <!-- En-tête -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h2 class="mb-0">
                            <i class="{{ $category->icone_formatee }} text-{{ $category->couleur_formatee }} me-2"></i>
                            {{ $category->nom }}
                        </h2>
                        <p class="text-muted mb-0">{{ $category->description ?: 'Aucune description' }}</p>
                    </div>
                    <div class="btn-group">
                        <a href="{{ route('categories.edit', $category) }}" class="btn btn-warning">
                            <i class="fas fa-edit me-2"></i>
                            Modifier
                        </a>
                        <a href="{{ route('categories.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-2"></i>
                            Retour
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistiques -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card border-left-primary">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <h6 class="text-primary text-uppercase mb-1">Produits</h6>
                                <h3 class="mb-0">{{ $stats['total_produits'] }}</h3>
                            </div>
                            <div class="text-primary">
                                <i class="fas fa-box fa-2x"></i>
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
                                <h6 class="text-success text-uppercase mb-1">Stock total</h6>
                                <h3 class="mb-0">{{ number_format($stats['stock_total'], 0, ',', ' ') }}</h3>
                            </div>
                            <div class="text-success">
                                <i class="fas fa-warehouse fa-2x"></i>
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
                                <h6 class="text-info text-uppercase mb-1">Valeur stock</h6>
                                <h3 class="mb-0">{{ number_format($stats['valeur_stock'], 0, ',', ' ') }} FCFA</h3>
                            </div>
                            <div class="text-info">
                                <i class="fas fa-money-bill-wave fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Informations de la catégorie -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-info-circle me-2"></i>
                            Informations
                        </h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-borderless">
                            <tr>
                                <td class="fw-bold">Nom :</td>
                                <td>{{ $category->nom }}</td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Icône :</td>
                                <td>
                                    <i
                                        class="{{ $category->icone_formatee }} text-{{ $category->couleur_formatee }} me-2"></i>
                                    {{ $category->icone }}
                                </td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Couleur :</td>
                                <td>
                                    <span
                                        class="badge bg-{{ $category->couleur_formatee }}">{{ $category->couleur }}</span>
                                </td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Statut :</td>
                                <td>
                                    @if ($category->active)
                                        <span class="badge bg-success">Active</span>
                                    @else
                                        <span class="badge bg-secondary">Inactive</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Créée le :</td>
                                <td>{{ $category->created_at->format('d/m/Y à H:i') }}</td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Modifiée le :</td>
                                <td>{{ $category->updated_at->format('d/m/Y à H:i') }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-chart-pie me-2"></i>
                            Répartition des produits
                        </h5>
                    </div>
                    <div class="card-body">
                        @if ($category->produits->count() > 0)
                            <div class="mb-3">
                                <h6>Par boutique :</h6>
                                @foreach ($category->produits->groupBy('boutique.nom') as $boutique => $produits)
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span>{{ $boutique }}</span>
                                        <span class="badge bg-primary">{{ $produits->count() }} produits</span>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center text-muted">
                                <i class="fas fa-box fa-2x mb-2"></i>
                                <p>Aucun produit dans cette catégorie</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Liste des produits de cette catégorie -->
        @if ($category->produits->count() > 0)
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-list me-2"></i>
                        Produits de cette catégorie ({{ $category->produits->count() }})
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Image</th>
                                    <th>Produit</th>
                                    <th>Stock</th>
                                    <th>Prix d'achat</th>
                                    <th>Prix de vente</th>
                                    <th>Boutique</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($category->produits as $produit)
                                    <tr>
                                        <td>
                                            @if ($produit->image)
                                                <img src="{{ asset($produit->image) }}" alt="{{ $produit->nom }}"
                                                    class="rounded" style="width: 40px; height: 40px; object-fit: cover;">
                                            @else
                                                <div class="bg-light rounded d-flex align-items-center justify-content-center"
                                                    style="width: 40px; height: 40px;">
                                                    <i class="fas fa-image text-muted"></i>
                                                </div>
                                            @endif
                                        </td>
                                        <td>
                                            <div>
                                                <strong>{{ $produit->nom }}</strong>
                                                <br>
                                                <small class="text-muted">{{ $produit->code_produit }}</small>
                                            </div>
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
    </div>
@endsection






























