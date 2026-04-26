@extends('layouts.app')

@section('title', 'Rapport Financier Global')

@section('content')
    <div class="container-fluid">
        <!-- En-tête -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h1 class="h3 mb-0 text-gray-800">
                            <i class="fas fa-chart-line me-2"></i>
                            Rapport Financier Global
                            @if (auth()->user()->isOwner() && !auth()->user()->isAdmin())
                                <small class="text-muted d-block mt-1">(Votre boutique)</small>
                            @endif
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
                        {{-- <form method="GET" action="{{ route('rapports.financier') }}" style="display: inline;">
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

        <!-- Statistiques globales -->
        <div class="row mb-4">
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-success shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                    CA Total
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    {{ number_format($statsGlobales['chiffre_affaires_total'], 0, ',', ' ') }} FCFA
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
                                    Dépenses Totales
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    {{ number_format($statsGlobales['depenses_totales'], 0, ',', ' ') }} FCFA
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
                <div class="card border-left-info shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                    Bénéfice Net
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    {{ number_format($statsGlobales['benefice_net'], 0, ',', ' ') }} FCFA
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
                <div class="card border-left-primary shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                    Transactions
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    {{ number_format($statsGlobales['nombre_transactions']) }}
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-shopping-cart fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Rapport par boutique -->
        <div class="row">
            @foreach ($rapportBoutiques as $rapport)
                <div class="col-lg-6 mb-4">
                    <div class="card shadow">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">
                                <i class="fas fa-store me-2"></i>
                                {{ $rapport['boutique']->nom }}
                            </h6>
                        </div>
                        <div class="card-body">
                            <!-- Statistiques de la boutique -->
                            <div class="row mb-3">
                                <div class="col-6">
                                    <div class="text-center">
                                        <div class="h4 text-success mb-0">
                                            {{ number_format($rapport['chiffre_affaires'], 0, ',', ' ') }} FCFA
                                        </div>
                                        <small class="text-muted">Chiffre d'Affaires</small>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="text-center">
                                        <div class="h4 text-warning mb-0">
                                            {{ number_format($rapport['depenses'], 0, ',', ' ') }} FCFA
                                        </div>
                                        <small class="text-muted">Dépenses</small>
                                    </div>
                                </div>
                            </div>

                            <!-- Bénéfice -->
                            <div class="row mb-3">
                                <div class="col-12">
                                    <div class="text-center">
                                        <div
                                            class="h3 {{ $rapport['benefice'] >= 0 ? 'text-success' : 'text-danger' }} mb-0">
                                            {{ number_format($rapport['benefice'], 0, ',', ' ') }} FCFA
                                        </div>
                                        <small class="text-muted">Bénéfice Net</small>
                                    </div>
                                </div>
                            </div>

                            <!-- Détails -->
                            <div class="row">
                                <div class="col-6">
                                    <div class="text-center">
                                        <div class="h5 text-primary mb-0">{{ $rapport['nombre_ventes'] }}</div>
                                        <small class="text-muted">Ventes</small>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="text-center">
                                        <div class="h5 text-warning mb-0">{{ $rapport['nombre_depenses'] }}</div>
                                        <small class="text-muted">Dépenses</small>
                                    </div>
                                </div>
                            </div>

                            <!-- Barre de progression du bénéfice -->
                            @php
                                $pourcentageBenefice =
                                    $rapport['chiffre_affaires'] > 0
                                        ? ($rapport['benefice'] / $rapport['chiffre_affaires']) * 100
                                        : 0;
                            @endphp
                            <div class="mt-3">
                                <div class="d-flex justify-content-between">
                                    <small class="text-muted">Marge de bénéfice</small>
                                    <small class="text-muted">{{ number_format($pourcentageBenefice, 1) }}%</small>
                                </div>
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar {{ $rapport['benefice'] >= 0 ? 'bg-success' : 'bg-danger' }}"
                                        role="progressbar" style="width: {{ abs($pourcentageBenefice) }}%"
                                        aria-valuenow="{{ abs($pourcentageBenefice) }}" aria-valuemin="0"
                                        aria-valuemax="100">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Comparaison des boutiques (uniquement si plusieurs boutiques) -->
        @if (auth()->user()->isAdmin() && count($rapportBoutiques) > 1)
            <div class="row">
                <div class="col-12">
                    <div class="card shadow">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">
                                <i class="fas fa-chart-bar me-2"></i>
                                Comparaison des Boutiques
                            </h6>
                        </div>
                        <div class="card-body">
                            <canvas id="chartComparaison" width="400" height="100"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Tableau de synthèse -->
        <div class="card shadow">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-table me-2"></i>
                    Tableau de Synthèse
                </h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Boutique</th>
                                <th>Chiffre d'Affaires</th>
                                <th>Dépenses</th>
                                <th>Bénéfice</th>
                                <th>Marge (%)</th>
                                <th>Ventes</th>
                                <th>Dépenses</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($rapportBoutiques as $rapport)
                                @php
                                    $marge =
                                        $rapport['chiffre_affaires'] > 0
                                            ? ($rapport['benefice'] / $rapport['chiffre_affaires']) * 100
                                            : 0;
                                @endphp
                                <tr>
                                    <td>
                                        <strong>{{ $rapport['boutique']->nom }}</strong>
                                    </td>
                                    <td>
                                        <strong class="text-success">
                                            {{ number_format($rapport['chiffre_affaires'], 0, ',', ' ') }} FCFA
                                        </strong>
                                    </td>
                                    <td>
                                        <strong class="text-warning">
                                            {{ number_format($rapport['depenses'], 0, ',', ' ') }} FCFA
                                        </strong>
                                    </td>
                                    <td>
                                        <strong class="{{ $rapport['benefice'] >= 0 ? 'text-success' : 'text-danger' }}">
                                            {{ number_format($rapport['benefice'], 0, ',', ' ') }} FCFA
                                        </strong>
                                    </td>
                                    <td>
                                        <span class="badge {{ $marge >= 0 ? 'bg-success' : 'bg-danger' }}">
                                            {{ number_format($marge, 1) }}%
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-primary">{{ $rapport['nombre_ventes'] }}</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-warning">{{ $rapport['nombre_depenses'] }}</span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="table-light">
                            <tr>
                                <th><strong>TOTAL</strong></th>
                                <th>
                                    <strong class="text-success">
                                        {{ number_format($statsGlobales['chiffre_affaires_total'], 0, ',', ' ') }} FCFA
                                    </strong>
                                </th>
                                <th>
                                    <strong class="text-warning">
                                        {{ number_format($statsGlobales['depenses_totales'], 0, ',', ' ') }} FCFA
                                    </strong>
                                </th>
                                <th>
                                    <strong
                                        class="{{ $statsGlobales['benefice_net'] >= 0 ? 'text-success' : 'text-danger' }}">
                                        {{ number_format($statsGlobales['benefice_net'], 0, ',', ' ') }} FCFA
                                    </strong>
                                </th>
                                <th>
                                    @php
                                        $margeGlobale =
                                            $statsGlobales['chiffre_affaires_total'] > 0
                                                ? ($statsGlobales['benefice_net'] /
                                                        $statsGlobales['chiffre_affaires_total']) *
                                                    100
                                                : 0;
                                    @endphp
                                    <span class="badge {{ $margeGlobale >= 0 ? 'bg-success' : 'bg-danger' }}">
                                        {{ number_format($margeGlobale, 1) }}%
                                    </span>
                                </th>
                                <th>
                                    <span class="badge bg-primary">{{ $statsGlobales['nombre_transactions'] }}</span>
                                </th>
                                <th>
                                    <span class="badge bg-warning">
                                        {{ collect($rapportBoutiques)->sum('nombre_depenses') }}
                                    </span>
                                </th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Graphique de comparaison des boutiques
        document.addEventListener('DOMContentLoaded', function() {
            const chartElement = document.getElementById('chartComparaison');
            if (!chartElement) {
                return; // Pas de graphique si l'élément n'existe pas
            }

            const ctx = chartElement.getContext('2d');

            const boutiques = @json(collect($rapportBoutiques)->pluck('boutique.nom'));
            const chiffreAffaires = @json(collect($rapportBoutiques)->pluck('chiffre_affaires'));
            const depenses = @json(collect($rapportBoutiques)->pluck('depenses'));
            const benefices = @json(collect($rapportBoutiques)->pluck('benefice'));

            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: boutiques,
                    datasets: [{
                        label: 'Chiffre d\'Affaires (FCFA)',
                        data: chiffreAffaires,
                        backgroundColor: 'rgba(75, 192, 192, 0.6)',
                        borderColor: 'rgba(75, 192, 192, 1)',
                        borderWidth: 1
                    }, {
                        label: 'Dépenses (FCFA)',
                        data: depenses,
                        backgroundColor: 'rgba(255, 99, 132, 0.6)',
                        borderColor: 'rgba(255, 99, 132, 1)',
                        borderWidth: 1
                    }, {
                        label: 'Bénéfice (FCFA)',
                        data: benefices,
                        backgroundColor: 'rgba(54, 162, 235, 0.6)',
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return new Intl.NumberFormat('fr-FR').format(value) + ' FCFA';
                                }
                            }
                        }
                    },
                    plugins: {
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return context.dataset.label + ': ' + new Intl.NumberFormat('fr-FR')
                                        .format(context.parsed.y) + ' FCFA';
                                }
                            }
                        }
                    }
                }
            });
        });
    </script>
@endsection
