@extends('layouts.app')

@section('content')
    <div class="container-fluid px-4 py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-1 fw-bold">Statistiques Globales</h1>
                <p class="text-muted mb-0">Vue d'ensemble des performances du système</p>
            </div>
        </div>

        <!-- Statistiques principales -->
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <p class="text-muted small mb-1">Total Commerçants</p>
                        <h3 class="mb-0 fw-bold">{{ number_format($stats['total_commercants']) }}</h3>
                        <small class="text-success">{{ $stats['commercants_actifs'] }} actifs</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <p class="text-muted small mb-1">Total Boutiques</p>
                        <h3 class="mb-0 fw-bold">{{ number_format($stats['total_boutiques']) }}</h3>
                        <small class="text-success">{{ $stats['boutiques_actives'] }} actives</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <p class="text-muted small mb-1">Abonnements Actifs</p>
                        <h3 class="mb-0 fw-bold text-success">{{ number_format($stats['abonnements_actifs']) }}</h3>
                        <small class="text-danger">{{ $stats['abonnements_expires'] }} expirés</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <p class="text-muted small mb-1">Revenus Totaux</p>
                        <h3 class="mb-0 fw-bold text-primary">
                            {{ number_format($stats['revenus_totaux'], 0, ',', ' ') }} FCFA
                        </h3>
                    </div>
                </div>
            </div>
        </div>

        <!-- Graphiques -->
        <div class="row g-3 mb-4">
            <div class="col-md-6">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-bottom">
                        <h5 class="mb-0 fw-semibold">
                            <i class="fas fa-users me-2 text-primary"></i>
                            Utilisateurs actifs par mois (12 derniers mois)
                        </h5>
                    </div>
                    <div class="card-body">
                        <canvas id="chartUtilisateurs" height="100"></canvas>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-bottom">
                        <h5 class="mb-0 fw-semibold">
                            <i class="fas fa-money-bill-wave me-2 text-success"></i>
                            Revenus par mois (12 derniers mois)
                        </h5>
                    </div>
                    <div class="card-body">
                        <canvas id="chartRevenus" height="100"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Top boutiques -->
        <div class="row g-3 mb-4">
            <div class="col-md-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-bottom">
                        <h5 class="mb-0 fw-semibold">
                            <i class="fas fa-trophy me-2 text-warning"></i>
                            Top 10 Boutiques par Chiffre d'Affaires
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Rang</th>
                                        <th>Boutique</th>
                                        <th>Propriétaire</th>
                                        <th class="text-end">Chiffre d'affaires</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($topBoutiques as $index => $boutique)
                                        <tr>
                                            <td>
                                                <span class="badge bg-{{ $index < 3 ? 'warning' : 'secondary' }}">
                                                    #{{ $index + 1 }}
                                                </span>
                                            </td>
                                            <td>
                                                <strong>{{ $boutique->nom }}</strong>
                                            </td>
                                            <td>{{ $boutique->owner?->name ?? 'N/A' }}</td>
                                            <td class="text-end">
                                                <strong class="text-success">
                                                    {{ number_format($boutique->chiffre_affaires ?? 0, 0, ',', ' ') }} FCFA
                                                </strong>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-bottom">
                        <h5 class="mb-0 fw-semibold">
                            <i class="fas fa-exclamation-triangle me-2 text-danger"></i>
                            Abonnements Expirés
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="list-group list-group-flush">
                            @forelse($abonnementsExpires->take(10) as $abonnement)
                                <div class="list-group-item px-0 border-0">
                                    <div>
                                        <strong>{{ $abonnement->user?->name ?? 'N/A' }}</strong>
                                        <br>
                                        <small class="text-muted">
                                            Expiré le {{ $abonnement->date_expiration->format('d/m/Y') }}
                                        </small>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center text-muted py-4">Aucun abonnement expiré</div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Graphique utilisateurs actifs
        const ctxUtilisateurs = document.getElementById('chartUtilisateurs');
        if (ctxUtilisateurs) {
            new Chart(ctxUtilisateurs, {
                type: 'line',
                data: {
                    labels: @json($utilisateursActifs['labels']),
                    datasets: [{
                        label: 'Utilisateurs actifs',
                        data: @json($utilisateursActifs['data']),
                        borderColor: 'rgb(59, 130, 246)',
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        tension: 0.4,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        }

        // Graphique revenus
        const ctxRevenus = document.getElementById('chartRevenus');
        if (ctxRevenus) {
            new Chart(ctxRevenus, {
                type: 'bar',
                data: {
                    labels: @json($revenusParMois['labels']),
                    datasets: [{
                        label: 'Revenus (FCFA)',
                        data: @json($revenusParMois['data']),
                        backgroundColor: 'rgba(34, 197, 94, 0.8)',
                        borderColor: 'rgb(34, 197, 94)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return 'Revenus: ' + new Intl.NumberFormat('fr-FR').format(context.parsed
                                        .y) + ' FCFA';
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return new Intl.NumberFormat('fr-FR').format(value) + ' FCFA';
                                }
                            }
                        }
                    }
                }
            });
        }
    </script>
@endsection



