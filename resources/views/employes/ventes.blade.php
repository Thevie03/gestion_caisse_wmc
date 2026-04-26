@extends('layouts.app')

@section('content')

    <!-- Filtres -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('employes.ventes', $employe) }}" class="row g-3">
                <div class="col-md-3">
                    <label for="date_debut" class="form-label">Date de début</label>
                    <input type="date" class="form-control" id="date_debut" name="date_debut"
                        value="{{ request('date_debut') }}">
                </div>

                <div class="col-md-3">
                    <label for="date_fin" class="form-label">Date de fin</label>
                    <input type="date" class="form-control" id="date_fin" name="date_fin"
                        value="{{ request('date_fin') }}">
                </div>

                <div class="col-md-2">
                    <label for="montant_min" class="form-label">Montant min (FCFA)</label>
                    <input type="number" class="form-control" id="montant_min" name="montant_min"
                        value="{{ request('montant_min') }}" placeholder="0">
                </div>

                <div class="col-md-2">
                    <label for="montant_max" class="form-label">Montant max (FCFA)</label>
                    <input type="number" class="form-control" id="montant_max" name="montant_max"
                        value="{{ request('montant_max') }}" placeholder="999999">
                </div>

                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="fas fa-search"></i>
                    </button>
                    <a href="{{ route('employes.ventes', $employe) }}" class="btn btn-outline-secondary">
                        <i class="fas fa-times"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Statistiques rapides -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card stat-card bg-primary text-white">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <div class="stat-value">{{ $ventes->total() }}</div>
                        <div class="stat-label">Total ventes</div>
                    </div>
                    <div class="ms-3">
                        <i class="fas fa-shopping-cart fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card stat-card bg-success text-white">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <div class="stat-value">{{ number_format($ventes->sum('total_final'), 0, ',', ' ') }} FCFA
                        </div>
                        <div class="stat-label">Chiffre d'affaires</div>
                    </div>
                    <div class="ms-3">
                        <i class="fas fa-money-bill-wave fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card stat-card bg-info text-white">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <div class="stat-value">{{ number_format($ventes->avg('total_final'), 0, ',', ' ') }} FCFA
                        </div>
                        <div class="stat-label">Moyenne par vente</div>
                    </div>
                    <div class="ms-3">
                        <i class="fas fa-chart-line fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card stat-card bg-warning text-white">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <div class="stat-value">
                            {{ $ventes->max('total_final') ? number_format($ventes->max('total_final'), 0, ',', ' ') : '0' }}
                            FCFA</div>
                        <div class="stat-label">Plus grosse vente</div>
                    </div>
                    <div class="ms-3">
                        <i class="fas fa-trophy fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Liste des ventes -->
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">
                <i class="fas fa-list me-2"></i>
                Historique des ventes ({{ $ventes->total() }})
            </h5>
        </div>
        <div class="card-body p-0">
            @if ($ventes->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>N° Vente</th>
                                <th>Produits</th>
                                <th>Montant</th>
                                <th>Mode de paiement</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($ventes as $vente)
                                <tr>
                                    <td>
                                        <div>
                                            <strong>{{ $vente->created_at->format('d/m/Y') }}</strong>
                                            <br>
                                            <small class="text-muted">{{ $vente->created_at->format('H:i') }}</small>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary">{{ $vente->numero_vente }}</span>
                                    </td>
                                    <td>
                                        <div>
                                            <strong>{{ $vente->venteDetails->count() }} produit(s)</strong>
                                            <br>
                                            <small class="text-muted">
                                                @foreach ($vente->venteDetails->take(2) as $detail)
                                                    {{ $detail->produit->nom }}{{ $loop->last ? '' : ', ' }}
                                                @endforeach
                                                @if ($vente->venteDetails->count() > 2)
                                                    et {{ $vente->venteDetails->count() - 2 }} autre(s)
                                                @endif
                                            </small>
                                        </div>
                                    </td>
                                    <td>
                                        <div>
                                            <strong>{{ number_format($vente->total_final, 0, ',', ' ') }} FCFA</strong>
                                            @if ($vente->remise > 0)
                                                <br>
                                                <small class="text-success">
                                                    <i class="fas fa-tag me-1"></i>
                                                    Remise: {{ number_format($vente->remise, 0, ',', ' ') }} FCFA
                                                </small>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        @switch($vente->mode_paiement)
                                            @case('especes')
                                                <span class="badge bg-success">
                                                    <i class="fas fa-money-bill me-1"></i>
                                                    Espèces
                                                </span>
                                            @break

                                            @case('mobile_money')
                                                <span class="badge bg-info">
                                                    <i class="fas fa-mobile-alt me-1"></i>
                                                    Mobile Money
                                                </span>
                                            @break

                                            @case('carte')
                                                <span class="badge bg-warning">
                                                    <i class="fas fa-credit-card me-1"></i>
                                                    Carte
                                                </span>
                                            @break
                                        @endswitch
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('ventes.show', $vente) }}"
                                                class="btn btn-sm btn-outline-info" title="Voir les détails">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('ventes.imprimer', $vente) }}"
                                                class="btn btn-sm btn-outline-warning" title="Imprimer"
                                                target="_blank">
                                                <i class="fas fa-print"></i>
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
                    {{ $ventes->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-shopping-cart fa-3x text-muted mb-3"></i>
                    <h4>Aucune vente trouvée</h4>
                    <p class="text-muted">
                        @if (request()->hasAny(['date_debut', 'date_fin', 'montant_min', 'montant_max']))
                            Aucune vente ne correspond aux critères de recherche.
                        @else
                            Cet employé n'a pas encore effectué de vente.
                        @endif
                    </p>
                    @if (request()->hasAny(['date_debut', 'date_fin', 'montant_min', 'montant_max']))
                        <a href="{{ route('employes.ventes', $employe) }}" class="btn btn-primary">
                            <i class="fas fa-times me-2"></i>
                            Effacer les filtres
                        </a>
                    @endif
                </div>
            @endif
        </div>
    </div>
@endsection




































