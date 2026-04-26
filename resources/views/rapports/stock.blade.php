@extends('layouts.app')

@section('title', 'Rapport du Stock')

@section('content')
    <div class="container-fluid">
        <!-- En-tête -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h1 class="h3 mb-0 text-gray-800">
                            <i class="fas fa-warehouse me-2"></i>
                            Rapport du Stock
                        </h1>
                        <p class="text-muted mb-0">État actuel du stock et alertes</p>
                    </div>
                    <div>
                        <a href="{{ route('rapports.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-1"></i>
                            Retour
                        </a>
                        <button class="btn btn-outline-primary" onclick="window.print()">
                            <i class="fas fa-print me-1"></i>
                            Imprimer
                        </button>
                        {{-- <form method="GET" action="{{ route('rapports.stock') }}" style="display: inline;">
                            @foreach (request()->all() as $key => $value)
                                @if ($key !== 'export_excel')
                                    <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                                @endif
                            @endforeach
                            <input type="hidden" name="export_excel" value="1">
                            <button type="submit" class="btn btn-outline-success">
                                <i class="fas fa-file-excel me-1"></i>
                                Export Excel
                            </button>
                        </form> --}}
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistiques du stock -->
        <div class="row mb-4">
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-primary shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                    Total produits/articles
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    {{ number_format($stats['total_produits']) }}
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-box fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-success shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                    Valeur du Stock
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    {{ number_format($stats['valeur_stock'], 0, ',', ' ') }} FCFA
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-warning shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                    Stock Faible
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    {{ number_format($stats['produits_faibles']) }}
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-danger shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                    Rupture de Stock
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    {{ number_format($stats['produits_rupture']) }}
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-times-circle fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Stock par catégorie -->
            <div class="col-lg-6 mb-4">
                <div class="card shadow">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-chart-pie me-2"></i>
                            Stock par Catégorie
                        </h6>
                    </div>
                    <div class="card-body">
                        @if ($stats['categories']->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Catégorie</th>
                                            <th>Quantité</th>
                                            <th>Valeur</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($stats['categories'] as $categorie => $data)
                                            <tr>
                                                <td>
                                                    <span class="badge bg-secondary">{{ $categorie }}</span>
                                                </td>
                                                <td>
                                                    <strong>{{ $data['quantite'] }}</strong>
                                                </td>
                                                <td>
                                                    <strong>{{ number_format($data['valeur'], 0, ',', ' ') }} FCFA</strong>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <p class="text-muted text-center">Aucune catégorie trouvée</p>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Alertes de stock -->
            <div class="col-lg-6 mb-4">
                <div class="card shadow">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-bell me-2"></i>
                            Alertes de Stock
                        </h6>
                    </div>
                    <div class="card-body">
                        @php
                            $produitsFaibles = $produits->where('quantite_stock', '<=', 'stock_minimum');
                            $produitsRupture = $produits->where('quantite_stock', 0);
                        @endphp

                        @if ($produitsFaibles->count() > 0 || $produitsRupture->count() > 0)
                            <div class="alert alert-warning">
                                <h6><i class="fas fa-exclamation-triangle me-1"></i> Produits en Stock Faible</h6>
                                <ul class="mb-0">
                                    @foreach ($produitsFaibles->take(5) as $produit)
                                        <li>{{ $produit->nom }} - Stock: {{ $produit->quantite_stock }}</li>
                                    @endforeach
                                </ul>
                            </div>

                            @if ($produitsRupture->count() > 0)
                                <div class="alert alert-danger">
                                    <h6><i class="fas fa-times-circle me-1"></i> Produits en Rupture</h6>
                                    <ul class="mb-0">
                                        @foreach ($produitsRupture->take(5) as $produit)
                                            <li>{{ $produit->nom }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                        @else
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle me-1"></i>
                                Aucune alerte de stock
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Liste détaillée des produits -->
        <div class="card shadow">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-list me-2"></i>
                    Détail du Stock ({{ $produits->count() }})
                </h6>
            </div>
            <div class="card-body">
                @if ($produits->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Produit</th>
                                    <th>Catégorie</th>
                                    <th>Boutique</th>
                                    <th>Stock Actuel</th>
                                    <th>Stock Minimum</th>
                                    <th>Prix d'Achat</th>
                                    <th>Prix de Vente</th>
                                    <th>Valeur Stock</th>
                                    <th>Statut</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($produits as $produit)
                                    <tr>
                                        <td>
                                            <div>
                                                <strong>{{ $produit->nom }}</strong>
                                                @if ($produit->description)
                                                    <br>
                                                    <small
                                                        class="text-muted">{{ Str::limit($produit->description, 50) }}</small>
                                                @endif
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary">{{ $produit->categorie }}</span>
                                        </td>
                                        <td>
                                            <span class="badge bg-primary">{{ $produit->boutique->nom }}</span>
                                        </td>
                                        <td>
                                            <strong>{{ $produit->quantite_stock }}</strong>
                                        </td>
                                        <td>
                                            {{ $produit->stock_minimum }}
                                        </td>
                                        <td>
                                            {{ number_format($produit->prix_achat, 0, ',', ' ') }} FCFA
                                        </td>
                                        <td>
                                            {{ number_format($produit->prix_vente, 0, ',', ' ') }} FCFA
                                        </td>
                                        <td>
                                            <strong>{{ number_format($produit->quantite_stock * $produit->prix_achat, 0, ',', ' ') }}
                                                FCFA</strong>
                                        </td>
                                        <td>
                                            @if ($produit->quantite_stock == 0)
                                                <span class="badge bg-danger">
                                                    <i class="fas fa-times-circle me-1"></i>
                                                    Rupture
                                                </span>
                                            @elseif($produit->quantite_stock <= $produit->stock_minimum)
                                                <span class="badge bg-warning">
                                                    <i class="fas fa-exclamation-triangle me-1"></i>
                                                    Faible
                                                </span>
                                            @else
                                                <span class="badge bg-success">
                                                    <i class="fas fa-check-circle me-1"></i>
                                                    Normal
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="fas fa-warehouse fa-3x text-muted mb-3"></i>
                        <h4>Aucun produit trouvé</h4>
                        <p class="text-muted">Aucun produit ne correspond aux critères sélectionnés.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
