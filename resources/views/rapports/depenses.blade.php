@extends('layouts.app')

@section('title', 'Rapport des Dépenses')

@section('content')
    <div class="container-fluid">
        <!-- En-tête -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h1 class="h3 mb-0 text-gray-800">
                            <i class="fas fa-receipt me-2"></i>
                            Rapport des Dépenses
                        </h1>
                        <p class="text-muted mb-0">
                            Période : {{ \Carbon\Carbon::parse($dateDebut)->format('d/m/Y') }} -
                            {{ \Carbon\Carbon::parse($dateFin)->format('d/m/Y') }}
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
                        {{-- <form method="GET" action="{{ route('rapports.depenses') }}" style="display: inline;">
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

        <!-- Statistiques des dépenses -->
        <div class="row mb-4">
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-primary shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                    Total Dépenses
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    {{ number_format($stats['total_depenses']) }}
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-receipt fa-2x text-gray-300"></i>
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
                                    Montant Total
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    {{ number_format($stats['montant_total'], 0, ',', ' ') }} FCFA
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
                                    Moyenne par Dépense
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    {{ number_format($stats['moyenne_depense'], 0, ',', ' ') }} FCFA
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
                <div class="card border-left-success shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                    Catégories
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    {{ $stats['depenses_par_categorie']->count() }}
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-tags fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Dépenses par catégorie -->
            <div class="col-lg-6 mb-4">
                <div class="card shadow">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-chart-pie me-2"></i>
                            Dépenses par Catégorie
                        </h6>
                    </div>
                    <div class="card-body">
                        @if ($stats['depenses_par_categorie']->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Catégorie</th>
                                            <th>Nombre</th>
                                            <th>Montant</th>
                                            <th>%</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($stats['depenses_par_categorie'] as $categorie => $data)
                                            @php
                                                $pourcentage =
                                                    $stats['montant_total'] > 0
                                                        ? ($data['total'] / $stats['montant_total']) * 100
                                                        : 0;
                                            @endphp
                                            <tr>
                                                <td>
                                                    <span class="badge bg-secondary">{{ $categorie }}</span>
                                                </td>
                                                <td>
                                                    <strong>{{ $data['count'] }}</strong>
                                                </td>
                                                <td>
                                                    <strong>{{ number_format($data['total'], 0, ',', ' ') }} FCFA</strong>
                                                </td>
                                                <td>
                                                    <div class="progress" style="height: 20px;">
                                                        <div class="progress-bar" role="progressbar"
                                                            style="width: {{ $pourcentage }}%"
                                                            aria-valuenow="{{ $pourcentage }}" aria-valuemin="0"
                                                            aria-valuemax="100">
                                                            {{ number_format($pourcentage, 1) }}%
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <p class="text-muted text-center">Aucune dépense par catégorie</p>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Évolution des dépenses par mois -->
            <div class="col-lg-6 mb-4">
                <div class="card shadow">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-chart-line me-2"></i>
                            Évolution par Mois
                        </h6>
                    </div>
                    <div class="card-body">
                        @if ($stats['depenses_par_mois']->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Mois</th>
                                            <th>Montant</th>
                                            <th>Graphique</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php
                                            $maxMontant = $stats['depenses_par_mois']->max();
                                        @endphp
                                        @foreach ($stats['depenses_par_mois'] as $mois => $montant)
                                            @php
                                                $pourcentage = $maxMontant > 0 ? ($montant / $maxMontant) * 100 : 0;
                                                $moisFormate = \Carbon\Carbon::createFromFormat('Y-m', $mois)->format(
                                                    'M Y',
                                                );
                                            @endphp
                                            <tr>
                                                <td>
                                                    <strong>{{ $moisFormate }}</strong>
                                                </td>
                                                <td>
                                                    <strong>{{ number_format($montant, 0, ',', ' ') }} FCFA</strong>
                                                </td>
                                                <td>
                                                    <div class="progress" style="height: 15px;">
                                                        <div class="progress-bar bg-info" role="progressbar"
                                                            style="width: {{ $pourcentage }}%"
                                                            aria-valuenow="{{ $pourcentage }}" aria-valuemin="0"
                                                            aria-valuemax="100">
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <p class="text-muted text-center">Aucune évolution disponible</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Liste détaillée des dépenses -->
        <div class="card shadow">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-list me-2"></i>
                    Détail des Dépenses ({{ $depenses->count() }})
                </h6>
            </div>
            <div class="card-body">
                @if ($depenses->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Date</th>
                                    <th>Description</th>
                                    <th>Catégorie</th>
                                    <th>Montant</th>
                                    <th>Boutique</th>
                                    <th>Utilisateur</th>
                                    <th>Notes</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($depenses as $depense)
                                    <tr>
                                        <td>
                                            <div>
                                                <strong>{{ $depense->date_depense->format('d/m/Y') }}</strong>
                                                <br>
                                                <small class="text-muted">{{ $depense->created_at->format('H:i') }}</small>
                                            </div>
                                        </td>
                                        <td>
                                            <div>
                                                <strong>{{ $depense->description }}</strong>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary">{{ $depense->categorie }}</span>
                                        </td>
                                        <td>
                                            <strong class="text-warning">
                                                {{ number_format($depense->montant, 0, ',', ' ') }} FCFA
                                            </strong>
                                        </td>
                                        <td>
                                            <span class="badge bg-primary">{{ $depense->boutique->nom }}</span>
                                        </td>
                                        <td>
                                            <div>
                                                <strong>{{ $depense->user->name }}</strong>
                                                <br>
                                                <small class="text-muted">{{ $depense->user->email }}</small>
                                            </div>
                                        </td>
                                        <td>
                                            @if ($depense->notes)
                                                <small class="text-muted">{{ Str::limit($depense->notes, 50) }}</small>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="fas fa-receipt fa-3x text-muted mb-3"></i>
                        <h4>Aucune dépense trouvée</h4>
                        <p class="text-muted">Aucune dépense ne correspond à la période sélectionnée.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
