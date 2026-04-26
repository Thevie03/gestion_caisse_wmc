@extends('layouts.app')

@section('title', 'Rapport des Ventes')

@section('content')
    <div class="container-fluid">
        <!-- En-tête -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h1 class="h3 mb-0 text-gray-800">
                            <i class="fas fa-chart-line me-2"></i>
                            Rapport des Ventes
                        </h1>
                        <p class="text-muted mb-0">
                            Période : {{ \Carbon\Carbon::parse($dateDebut)->format('d/m/Y') }} -
                            {{ \Carbon\Carbon::parse($dateFin)->format('d/m/Y') }}
                            | Type : {{ ucfirst($typeRapport) }}
                        </p>
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
                        {{-- <form method="GET" action="{{ route('rapports.ventes') }}" style="display: inline;">
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

        <!-- Statistiques du rapport -->
        <div class="row mb-4">
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-primary shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                    Total Ventes
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    {{ number_format($stats['total_ventes']) }}
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-shopping-cart fa-2x text-gray-300"></i>
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
                                    Chiffre d'Affaires
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    {{ number_format($stats['chiffre_affaires'], 0, ',', ' ') }} FCFA
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
                <div class="card border-left-info shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                    Moyenne par Vente
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    {{ number_format($stats['moyenne_vente'], 0, ',', ' ') }} FCFA
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-chart-line fa-2x text-gray-300"></i>
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
                                    Meilleur Jour
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    {{ number_format($stats['meilleur_jour'], 0, ',', ' ') }} FCFA
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-trophy fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Top 10 des produits vendus -->
            <div class="col-lg-6 mb-4">
                <div class="card shadow">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-star me-2"></i>
                            Top 10 des Produits Vendus
                        </h6>
                    </div>
                    <div class="card-body">
                        @if ($stats['produits_vendus']->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Produit</th>
                                            <th>Quantité</th>
                                            <th>Montant</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($stats['produits_vendus'] as $produit)
                                            <tr>
                                                <td>{{ $produit['nom'] }}</td>
                                                <td>
                                                    <span class="badge bg-primary">{{ $produit['quantite'] }}</span>
                                                </td>
                                                <td>
                                                    <strong>{{ number_format($produit['montant'], 0, ',', ' ') }}
                                                        FCFA</strong>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <p class="text-muted text-center">Aucune vente trouvée</p>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Ventes par mode de paiement -->
            <div class="col-lg-6 mb-4">
                <div class="card shadow">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-credit-card me-2"></i>
                            Ventes par Mode de Paiement
                        </h6>
                    </div>
                    <div class="card-body">
                        @if ($stats['ventes_par_mode_paiement']->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Mode de Paiement</th>
                                            <th>Nombre</th>
                                            <th>Montant</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($stats['ventes_par_mode_paiement'] as $mode => $data)
                                            <tr>
                                                <td>
                                                    @php
                                                        $iconClass = match ($mode) {
                                                            'especes' => 'fas fa-money-bill',
                                                            'mobile_money' => 'fas fa-mobile-alt',
                                                            'carte' => 'fas fa-credit-card',
                                                            default => 'fas fa-question',
                                                        };
                                                    @endphp
                                                    <i class="{{ $iconClass }} me-1"></i>
                                                    {{ ucfirst(str_replace('_', ' ', $mode)) }}
                                                </td>
                                                <td>
                                                    <span class="badge bg-info">{{ $data['count'] }}</span>
                                                </td>
                                                <td>
                                                    <strong>{{ number_format($data['total'], 0, ',', ' ') }} FCFA</strong>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <p class="text-muted text-center">Aucune donnée disponible</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Liste détaillée des ventes -->
        <div class="card shadow">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-list me-2"></i>
                    Détail des Ventes ({{ $ventes->count() }})
                </h6>
            </div>
            <div class="card-body">
                @if ($ventes->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>N° Facture</th>
                                    <th>Date</th>
                                    <th>Client</th>
                                    <th>Boutique</th>
                                    <th>Vendeur</th>
                                    <th>Total</th>
                                    <th>Paiement</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($ventes as $vente)
                                    <tr>
                                        <td>
                                            <strong class="text-primary">{{ $vente->numero_facture }}</strong>
                                        </td>
                                        <td>
                                            <div>
                                                <strong>{{ $vente->created_at->format('d/m/Y') }}</strong>
                                                <br>
                                                <small class="text-muted">{{ $vente->created_at->format('H:i') }}</small>
                                            </div>
                                        </td>
                                        <td>
                                            <div>
                                                <strong>{{ $vente->nom_client ?? 'Client anonyme' }}</strong>
                                                @if ($vente->telephone_client)
                                                    <br>
                                                    <small class="text-muted">
                                                        <i class="fas fa-phone me-1"></i>
                                                        {{ $vente->telephone_client }}
                                                    </small>
                                                @endif
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-primary">{{ $vente->boutique->nom }}</span>
                                        </td>
                                        <td>
                                            <div>
                                                <strong>{{ $vente->user->name }}</strong>
                                                <br>
                                                <small class="text-muted">{{ $vente->user->email }}</small>
                                            </div>
                                        </td>
                                        <td>
                                            <strong class="text-success">
                                                {{ number_format($vente->total_final, 0, ',', ' ') }} FCFA
                                            </strong>
                                        </td>
                                        <td>
                                            @php
                                                $badgeClass = match ($vente->mode_paiement) {
                                                    'especes' => 'bg-success',
                                                    'mobile_money' => 'bg-info',
                                                    'carte' => 'bg-warning',
                                                    default => 'bg-secondary',
                                                };
                                                $iconClass = match ($vente->mode_paiement) {
                                                    'especes' => 'fas fa-money-bill',
                                                    'mobile_money' => 'fas fa-mobile-alt',
                                                    'carte' => 'fas fa-credit-card',
                                                    default => 'fas fa-question',
                                                };
                                            @endphp
                                            <span class="badge {{ $badgeClass }}">
                                                <i class="{{ $iconClass }} me-1"></i>
                                                {{ ucfirst(str_replace('_', ' ', $vente->mode_paiement)) }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="fas fa-shopping-cart fa-3x text-muted mb-3"></i>
                        <h4>Aucune vente trouvée</h4>
                        <p class="text-muted">Aucune vente ne correspond à la période sélectionnée.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
