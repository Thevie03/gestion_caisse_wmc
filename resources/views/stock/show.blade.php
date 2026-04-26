@extends('layouts.app')

@section('content')

    <!-- Informations du produit -->
    <div class="row mb-4">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h5 class="card-title">{{ $produit->nom }}</h5>
                            <p class="text-muted mb-2">
                                <strong>Code:</strong> {{ $produit->code_produit }}<br>
                                <strong>Catégorie:</strong> {{ $produit->categorie }}<br>
                                <strong>Boutique:</strong> {{ $produit->boutique->nom }}
                            </p>
                        </div>
                        <div class="col-md-6">
                            <div class="row text-center">
                                <div class="col-4">
                                    <div class="stat-value text-primary">{{ $produit->quantite_stock }}</div>
                                    <div class="stat-label">Stock actuel</div>
                                </div>
                                <div class="col-4">
                                    <div class="stat-value text-warning">{{ $produit->stock_minimum }}</div>
                                    <div class="stat-label">Stock minimum</div>
                                </div>
                                <div class="col-4">
                                    <div class="stat-value text-success">
                                        {{ number_format($produit->prix_achat, 0, ',', ' ') }} FCFA</div>
                                    <div class="stat-label">Prix d'achat</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-body text-center">
                    <h6 class="card-title">Statut du stock</h6>
                    @if ($produit->quantite_stock == 0)
                        <div class="alert alert-danger mb-0">
                            <i class="fas fa-times-circle fa-2x mb-2"></i>
                            <div><strong>En rupture</strong></div>
                        </div>
                    @elseif($produit->isStockFaible())
                        <div class="alert alert-warning mb-0">
                            <i class="fas fa-exclamation-triangle fa-2x mb-2"></i>
                            <div><strong>Stock faible</strong></div>
                        </div>
                    @else
                        <div class="alert alert-success mb-0">
                            <i class="fas fa-check-circle fa-2x mb-2"></i>
                            <div><strong>Disponible</strong></div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Statistiques des mouvements -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card stat-card bg-success text-white">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <div class="stat-value">{{ $stats['total_entrees'] }}</div>
                        <div class="stat-label">Total entrées</div>
                    </div>
                    <div class="ms-3">
                        <i class="fas fa-arrow-down fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card stat-card bg-danger text-white">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <div class="stat-value">{{ $stats['total_sorties'] }}</div>
                        <div class="stat-label">Total sorties</div>
                    </div>
                    <div class="ms-3">
                        <i class="fas fa-arrow-up fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card stat-card bg-info text-white">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <div class="stat-value">{{ $stats['total_ajustements'] }}</div>
                        <div class="stat-label">Ajustements</div>
                    </div>
                    <div class="ms-3">
                        <i class="fas fa-edit fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card stat-card bg-primary text-white">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <div class="stat-value">{{ $mouvements->total() }}</div>
                        <div class="stat-label">Total mouvements</div>
                    </div>
                    <div class="ms-3">
                        <i class="fas fa-history fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Historique des mouvements -->
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">
                <i class="fas fa-history me-2"></i>
                Historique des mouvements ({{ $mouvements->total() }})
            </h5>
        </div>
        <div class="card-body p-0">
            @if ($mouvements->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Date/Heure</th>
                                <th>Type</th>
                                <th>Quantité</th>
                                <th>Motif</th>
                                <th>Utilisateur</th>
                                <th>Stock après</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($mouvements as $mouvement)
                                <tr>
                                    <td>
                                        <div>
                                            <strong>{{ $mouvement->created_at->format('d/m/Y') }}</strong>
                                            <br>
                                            <small
                                                class="text-muted">{{ $mouvement->created_at->format('H:i:s') }}</small>
                                        </div>
                                    </td>
                                    <td>
                                        @switch($mouvement->type)
                                            @case('entree')
                                                <span class="badge bg-success">
                                                    <i class="fas fa-arrow-down me-1"></i>
                                                    Entrée
                                                </span>
                                            @break

                                            @case('sortie')
                                                <span class="badge bg-danger">
                                                    <i class="fas fa-arrow-up me-1"></i>
                                                    Sortie
                                                </span>
                                            @break

                                            @case('ajustement')
                                                <span class="badge bg-info">
                                                    <i class="fas fa-edit me-1"></i>
                                                    Ajustement
                                                </span>
                                            @break
                                        @endswitch
                                    </td>
                                    <td>
                                        <strong
                                            class="{{ $mouvement->type === 'sortie' ? 'text-danger' : 'text-success' }}">
                                            {{ $mouvement->type === 'sortie' ? '-' : '+' }}{{ $mouvement->quantite }}
                                        </strong>
                                    </td>
                                    <td>
                                        <span class="text-muted">{{ $mouvement->motif }}</span>
                                    </td>
                                    <td>
                                        <div>
                                            <strong>{{ $mouvement->user->name }}</strong>
                                            <br>
                                            <small class="text-muted">{{ $mouvement->user->email }}</small>
                                        </div>
                                    </td>
                                    <td>
                                        <strong>{{ $produit->quantite_stock }}</strong>
                                        <small class="text-muted">unités</small>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="card-footer">
                    {{ $mouvements->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-history fa-3x text-muted mb-3"></i>
                    <h4>Aucun mouvement enregistré</h4>
                    <p class="text-muted">Aucun mouvement de stock n'a été enregistré pour ce produit.</p>
                    <a href="{{ route('stock.create') }}?produit={{ $produit->id }}" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>
                        Créer le premier mouvement
                    </a>
                </div>
            @endif
        </div>
    </div>
@endsection




































