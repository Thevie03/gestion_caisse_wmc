@extends('layouts.app')

@section('content')

    <div class="row">
        <div class="col-md-4">
            <!-- Informations personnelles -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-user me-2"></i>
                        Informations personnelles
                    </h5>
                </div>
                <div class="card-body text-center">
                    <div class="avatar bg-primary text-white rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3"
                        style="width: 80px; height: 80px;">
                        <i class="fas fa-user fa-2x"></i>
                    </div>
                    <h4>{{ $employe->name }}</h4>
                    <p class="text-muted mb-3">{{ $employe->email }}</p>

                    <div class="row text-center">
                        <div class="col-6">
                            <div class="stat-value text-primary">{{ $employe->telephone }}</div>
                            <div class="stat-label">Téléphone</div>
                        </div>
                        <div class="col-6">
                            <div class="stat-value text-info">
                                @if ($employe->role === 'admin')
                                    Cosmetica &amp; Maison des Abaya
                                @elseif ($employe->boutique)
                                    {{ $employe->boutique->nom }}
                                @else
                                    Non assignée
                                @endif
                            </div>
                            <div class="stat-label">Boutique</div>
                        </div>
                    </div>

                    <hr>

                    <div class="row text-center">
                        <div class="col-6">
                            <div class="stat-value text-success">{{ $employe->created_at->format('d/m/Y') }}</div>
                            <div class="stat-label">Créé le</div>
                        </div>
                        <div class="col-6">
                            @if ($employe->actif)
                                <span class="badge bg-success fs-6">Actif</span>
                            @else
                                <span class="badge bg-danger fs-6">Inactif</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-cogs me-2"></i>
                        Actions
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('employes.edit', $employe) }}" class="btn btn-primary">
                            <i class="fas fa-edit me-2"></i>
                            Modifier le profil
                        </a>
                        <a href="{{ route('employes.ventes', $employe) }}" class="btn btn-success">
                            <i class="fas fa-shopping-cart me-2"></i>
                            Historique des ventes
                        </a>
                        <form method="POST" action="{{ route('employes.toggle-status', $employe) }}"
                            style="display: inline;">
                            @csrf
                            <button type="submit" class="btn {{ $employe->actif ? 'btn-warning' : 'btn-success' }} w-100">
                                <i class="fas fa-{{ $employe->actif ? 'pause' : 'play' }} me-2"></i>
                                {{ $employe->actif ? 'Désactiver' : 'Activer' }}
                            </button>
                        </form>
                        <a href="{{ route('employes.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-list me-2"></i>
                            Voir tous les employés
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <!-- Statistiques de performance -->
            <div class="row mb-4">
                <div class="col-md-3 mb-3">
                    <div class="card stat-card bg-primary text-white">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <div class="stat-value">{{ $stats['total_ventes'] }}</div>
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
                                <div class="stat-value">{{ number_format($stats['chiffre_affaires'], 0, ',', ' ') }}
                                    FCFA</div>
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
                                <div class="stat-value">{{ number_format($stats['moyenne_vente'], 0, ',', ' ') }} FCFA
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
                                    {{ $stats['derniere_vente'] ? $stats['derniere_vente']->created_at->format('d/m') : 'N/A' }}
                                </div>
                                <div class="stat-label">Dernière vente</div>
                            </div>
                            <div class="ms-3">
                                <i class="fas fa-clock fa-2x opacity-75"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Ventes récentes -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-history me-2"></i>
                        Ventes récentes ({{ $ventesRecentes->count() }})
                    </h5>
                </div>
                <div class="card-body p-0">
                    @if ($ventesRecentes->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>N° Vente</th>
                                        <th>Montant</th>
                                        <th>Mode de paiement</th>
                                        <th>Boutique</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($ventesRecentes as $vente)
                                        <tr>
                                            <td>
                                                <div>
                                                    <strong>{{ $vente->created_at->format('d/m/Y') }}</strong>
                                                    <br>
                                                    <small
                                                        class="text-muted">{{ $vente->created_at->format('H:i') }}</small>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-secondary">{{ $vente->numero_vente }}</span>
                                            </td>
                                            <td>
                                                <strong>{{ number_format($vente->total_final, 0, ',', ' ') }}
                                                    FCFA</strong>
                                            </td>
                                            <td>
                                                @switch($vente->mode_paiement)
                                                    @case('especes')
                                                        <span class="badge bg-success">Espèces</span>
                                                    @break

                                                    @case('mobile_money')
                                                        <span class="badge bg-info">Mobile Money</span>
                                                    @break

                                                    @case('carte')
                                                        <span class="badge bg-warning">Carte</span>
                                                    @break
                                                @endswitch
                                            </td>
                                            <td>
                                                <span class="badge bg-primary">{{ $vente->boutique->nom }}</span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-shopping-cart fa-2x text-muted mb-3"></i>
                            <h5>Aucune vente enregistrée</h5>
                            <p class="text-muted">Cet employé n'a pas encore effectué de vente.</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Ventes par mois -->
            @if ($ventesParMois->count() > 0)
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-chart-bar me-2"></i>
                            Performance des 6 derniers mois
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Mois</th>
                                        <th>Nombre de ventes</th>
                                        <th>Chiffre d'affaires</th>
                                        <th>Moyenne par vente</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($ventesParMois as $vente)
                                        <tr>
                                            <td>
                                                <strong>{{ \Carbon\Carbon::create($vente->annee, $vente->mois, 1)->format('F Y') }}</strong>
                                            </td>
                                            <td>
                                                <span class="badge bg-primary">{{ $vente->nombre_ventes }}</span>
                                            </td>
                                            <td>
                                                <strong>{{ number_format($vente->chiffre_affaires, 0, ',', ' ') }}
                                                    FCFA</strong>
                                            </td>
                                            <td>
                                                {{ number_format($vente->chiffre_affaires / max(1, $vente->nombre_ventes), 0, ',', ' ') }}
                                                FCFA
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
    </div>
@endsection
