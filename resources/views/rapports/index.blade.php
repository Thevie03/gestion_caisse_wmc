@extends('layouts.app')

@section('title', 'Rapports et Analyses')

@section('content')
    <div class="container-fluid saas-dashboard">
        <!-- En-tête -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h1 class="h3 mb-0 text-gray-800">
                            <i class="fas fa-chart-bar me-2"></i>
                            Rapports et Analyses
                        </h1>
                        <p class="text-muted mb-0">Analysez les performances de votre entreprise</p>
                    </div>
                    <div>
                        <button class="btn btn-outline-primary" onclick="window.print()">
                            <i class="fas fa-print me-1"></i>
                            Imprimer
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistiques générales -->
        <div class="row mb-4">
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card saas-surface-card shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                    Ventes Aujourd'hui
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    {{ number_format($stats['ventes_aujourd_hui']) }}
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
                <div class="card saas-surface-card shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                    CA Aujourd'hui
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    {{ number_format($stats['chiffre_affaires_aujourd_hui'], 0, ',', ' ') }} FCFA
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
                <div class="card saas-surface-card shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                    Dépenses Aujourd'hui
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    {{ number_format($stats['depenses_aujourd_hui'], 0, ',', ' ') }} FCFA
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
                <div class="card saas-surface-card shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                    CA Ce Mois
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    {{ number_format($stats['chiffre_affaires_ce_mois'], 0, ',', ' ') }} FCFA
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-chart-line fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Types de rapports -->
        <div class="row">
            <!-- Rapport des Ventes -->
            <div class="col-lg-6 mb-4">
                <div class="card saas-surface-card shadow">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-shopping-cart me-2"></i>
                            Rapport des Ventes
                        </h6>
                    </div>
                    <div class="card-body">
                        <form method="GET" action="{{ route('rapports.ventes') }}">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="date_debut_ventes" class="form-label">Date de début</label>
                                    <input type="date" class="form-control" id="date_debut_ventes" name="date_debut"
                                        value="{{ date('Y-m-01') }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="date_fin_ventes" class="form-label">Date de fin</label>
                                    <input type="date" class="form-control" id="date_fin_ventes" name="date_fin"
                                        value="{{ date('Y-m-d') }}" required>
                                </div>
                                <div class="col-md-12">
                                    <label for="type_rapport_ventes" class="form-label">Type de rapport</label>
                                    <select class="form-select" id="type_rapport_ventes" name="type_rapport" required>
                                        <option value="journalier">Journalier</option>
                                        <option value="hebdomadaire">Hebdomadaire</option>
                                        <option value="mensuel">Mensuel</option>
                                    </select>
                                </div>
                                <div class="col-12">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-chart-line me-1"></i>
                                        Générer Rapport
                                    </button>
                                    <button type="submit" name="export_pdf" value="1" class="btn btn-outline-danger">
                                        <i class="fas fa-file-pdf me-1"></i>
                                        Export PDF
                                    </button>
                                    {{-- <button type="submit" name="export_excel" value="1"
                                        class="btn btn-outline-success">
                                        <i class="fas fa-file-excel me-1"></i>
                                        Export Excel
                                    </button> --}}
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Rapport du Stock -->
            <div class="col-lg-6 mb-4">
                <div class="card saas-surface-card shadow">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-warehouse me-2"></i>
                            Rapport du Stock
                        </h6>
                    </div>
                    <div class="card-body">
                        <form method="GET" action="{{ route('rapports.stock') }}">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="categorie_stock" class="form-label">Catégorie</label>
                                    <select class="form-select" id="categorie_stock" name="categorie">
                                        <option value="">Toutes les catégories</option>
                                        <option value="Soins du visage">Soins du visage</option>
                                        <option value="Soins des cheveux">Soins des cheveux</option>
                                        <option value="Parfums">Parfums</option>
                                        <option value="Maquillage">Maquillage</option>
                                        <option value="Abayas">Abayas</option>
                                        <option value="Hijabs">Hijabs</option>
                                        <option value="Accessoires">Accessoires</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check mt-4">
                                        <input class="form-check-input" type="checkbox" id="stock_faible"
                                            name="stock_faible" value="1">
                                        <label class="form-check-label" for="stock_faible">
                                            Stock faible seulement
                                        </label>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-chart-bar me-1"></i>
                                        Générer Rapport
                                    </button>
                                    <button type="submit" name="export_pdf" value="1"
                                        class="btn btn-outline-danger">
                                        <i class="fas fa-file-pdf me-1"></i>
                                        Export PDF
                                    </button>
                                    {{-- <button type="submit" name="export_excel" value="1"
                                        class="btn btn-outline-success">
                                        <i class="fas fa-file-excel me-1"></i>
                                        Export Excel
                                    </button> --}}
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Rapport des Dépenses -->
            <div class="col-lg-6 mb-4">
                <div class="card saas-surface-card shadow">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-receipt me-2"></i>
                            Rapport des Dépenses
                        </h6>
                    </div>
                    <div class="card-body">
                        <form method="GET" action="{{ route('rapports.depenses') }}">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="date_debut_depenses" class="form-label">Date de début</label>
                                    <input type="date" class="form-control" id="date_debut_depenses"
                                        name="date_debut" value="{{ date('Y-m-01') }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="date_fin_depenses" class="form-label">Date de fin</label>
                                    <input type="date" class="form-control" id="date_fin_depenses" name="date_fin"
                                        value="{{ date('Y-m-d') }}" required>
                                </div>
                                <div class="col-12">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-chart-pie me-1"></i>
                                        Générer Rapport
                                    </button>
                                    <button type="submit" name="export_pdf" value="1"
                                        class="btn btn-outline-danger">
                                        <i class="fas fa-file-pdf me-1"></i>
                                        Export PDF
                                    </button>
                                    {{-- <button type="submit" name="export_excel" value="1"
                                        class="btn btn-outline-success">
                                        <i class="fas fa-file-excel me-1"></i>
                                        Export Excel
                                    </button> --}}
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Rapport Financier Global -->
            @if (auth()->user()->isAdmin() || auth()->user()->isOwner())
                <div class="col-lg-6 mb-4">
                    <div class="card saas-surface-card shadow">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">
                                <i class="fas fa-chart-line me-2 text-danger"></i>
                                Rapport Financier Global
                                @if (auth()->user()->isOwner() && !auth()->user()->isAdmin())
                                    <small class="text-muted d-block mt-1">(Votre boutique)</small>
                                @endif
                            </h6>
                        </div>
                        <div class="card-body">
                            <form method="GET" action="{{ route('rapports.financier') }}">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="date_debut_financier" class="form-label">Date de début</label>
                                        <div class="input-group">
                                            <input type="date" class="form-control" id="date_debut_financier"
                                                name="date_debut" value="{{ date('Y-m-01') }}" required>
                                            <span class="input-group-text">
                                                <i class="fas fa-calendar"></i>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="date_fin_financier" class="form-label">Date de fin</label>
                                        <div class="input-group">
                                            <input type="date" class="form-control" id="date_fin_financier"
                                                name="date_fin" value="{{ date('Y-m-d') }}" required>
                                            <span class="input-group-text">
                                                <i class="fas fa-calendar"></i>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <button type="submit" class="btn btn-danger">
                                            <i class="fas fa-chart-line me-1"></i>
                                            Générer Rapport
                                        </button>
                                        <button type="submit" name="export_pdf" value="1"
                                            class="btn btn-outline-danger">
                                            <i class="fas fa-file-pdf me-1"></i>
                                            Export PDF
                                        </button>
                                        {{-- <button type="submit" name="export_excel" value="1"
                                            class="btn btn-outline-success">
                                            <i class="fas fa-file-excel me-1"></i>
                                            Export Excel
                                        </button> --}}
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <!-- Graphiques rapides -->
        <div class="row">
            <div class="col-12">
                <div class="card saas-surface-card shadow">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-chart-area me-2"></i>
                            Vue d'ensemble des 30 derniers jours
                        </h6>
                    </div>
                    <div class="card-body">
                        <canvas id="chartVentes" width="400" height="100"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Graphique des ventes des 30 derniers jours avec données réelles
        document.addEventListener('DOMContentLoaded', function() {
            const ctx = document.getElementById('chartVentes').getContext('2d');

            // Données réelles depuis le contrôleur
            const labels = @json($chartData['labels'] ?? []);
            const ventesData = @json($chartData['ventes'] ?? []);
            const depensesData = @json($chartData['depenses'] ?? []);

            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Chiffre d\'affaires (FCFA)',
                        data: ventesData,
                        borderColor: 'rgb(75, 192, 192)',
                        backgroundColor: 'rgba(75, 192, 192, 0.2)',
                        tension: 0.1
                    }, {
                        label: 'Dépenses (FCFA)',
                        data: depensesData,
                        borderColor: 'rgb(255, 99, 132)',
                        backgroundColor: 'rgba(255, 99, 132, 0.2)',
                        tension: 0.1
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
