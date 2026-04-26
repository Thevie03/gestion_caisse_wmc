@extends('layouts.app')

@section('content')
    <div class="container-fluid px-4 py-4 admin-dashboard">
        <style>
            .admin-stats-grid {
                display: grid;
                grid-template-columns: repeat(3, minmax(0, 1fr));
                gap: 1rem;
            }

            .admin-charts-grid {
                display: grid;
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 1rem;
            }

            .admin-two-grid {
                display: grid;
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 1rem;
            }

            @media (max-width: 991.98px) {
                .admin-stats-grid {
                    grid-template-columns: repeat(2, minmax(0, 1fr));
                }

                .admin-charts-grid {
                    grid-template-columns: 1fr;
                }

                .admin-two-grid {
                    grid-template-columns: 1fr;
                }
            }

            @media (max-width: 767.98px) {
                .admin-stats-grid {
                    grid-template-columns: 1fr;
                }
            }

            .admin-dashboard .card {
                background: var(--bg-primary);
                border: 1px solid var(--border-light) !important;
            }

            .admin-dashboard .card:hover,
            .admin-dashboard .card:focus-within {
                background: var(--bg-primary) !important;
                border-color: var(--border-light) !important;
            }

            .admin-dashboard .card-header {
                background: var(--bg-secondary) !important;
                border-bottom: 1px solid var(--border-light) !important;
            }

            .admin-dashboard .card-header h5,
            .admin-dashboard .card-header h6,
            .admin-dashboard h1 {
                color: var(--text-primary) !important;
            }

            .admin-dashboard .text-muted,
            .admin-dashboard .table th,
            .admin-dashboard .table td,
            .admin-dashboard .list-group-item {
                color: var(--text-secondary) !important;
            }

            .admin-dashboard .table {
                --bs-table-color: var(--text-secondary);
                --bs-table-bg: transparent;
                --bs-table-border-color: var(--border-light);
                --bs-table-hover-color: var(--text-primary);
                --bs-table-hover-bg: rgba(var(--primary-color-rgb), 0.08);
            }

            .admin-dashboard .table> :not(caption)>*>* {
                background-color: transparent !important;
                color: var(--text-secondary) !important;
                border-color: var(--border-light) !important;
            }

            .admin-dashboard .table thead th {
                color: var(--text-primary) !important;
            }

            .admin-dashboard .table-hover>tbody>tr:hover>* {
                background-color: rgba(var(--primary-color-rgb), 0.12) !important;
                color: var(--text-primary) !important;
            }

            .admin-dashboard .list-group-item {
                background-color: transparent !important;
                border-color: var(--border-light) !important;
            }

            .admin-dashboard .list-group-item:hover {
                background-color: rgba(var(--primary-color-rgb), 0.08) !important;
                color: var(--text-primary) !important;
            }

            .admin-dashboard .btn-link {
                color: var(--primary-color) !important;
            }
        </style>

        <!-- En-tête avec actions -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-1 fw-bold">Tableau de bord Super Admin</h1>
                <p class="text-muted mb-0">Vue globale du système et gestion des clients</p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.users.index') }}" class="btn btn-outline-primary">
                    <i class="fas fa-users me-2"></i>Gérer les utilisateurs
                </a>
                <a href="{{ route('admin.boutiques.index') }}" class="btn btn-outline-primary">
                    <i class="fas fa-store me-2"></i>Gérer les boutiques
                </a>
                <a href="{{ route('admin.users.create') }}" class="btn btn-primary">
                    <i class="fas fa-user-plus me-2"></i>Nouveau client
                </a>
            </div>
        </div>

        <!-- Statistiques financières plateforme -->
        <div class="row g-3 mb-4 admin-stats-grid">
            <div>
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <p class="text-muted small mb-1">Revenus plateforme</p>
                                <h3 class="mb-0 fw-bold">{{ number_format($stats['revenus_plateforme'] ?? 0, 0, ',', ' ') }}
                                    FCFA</h3>
                                <small class="text-success">
                                    <i class="fas fa-arrow-up me-1"></i>
                                    {{ number_format($stats['revenus_plateforme_mois'] ?? 0, 0, ',', ' ') }} FCFA ce mois
                                </small>
                            </div>
                            <div class="ms-3">
                                <div class="bg-primary bg-opacity-10 rounded-circle p-3">
                                    <i class="fas fa-coins fa-2x text-primary"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div>
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <p class="text-muted small mb-1">Dépenses plateforme</p>
                                <h3 class="mb-0 fw-bold text-danger">
                                    {{ number_format($stats['depenses_plateforme'] ?? 0, 0, ',', ' ') }} FCFA
                                </h3>
                                <small class="text-danger">
                                    <i class="fas fa-arrow-down me-1"></i>
                                    {{ number_format($stats['depenses_plateforme_mois'] ?? 0, 0, ',', ' ') }} FCFA ce mois
                                </small>
                            </div>
                            <div class="ms-3">
                                <div class="bg-danger bg-opacity-10 rounded-circle p-3">
                                    <i class="fas fa-wallet fa-2x text-danger"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div>
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <p class="text-muted small mb-1">Bénéfice net</p>
                                @php
                                    $benefice = $stats['benefice_plateforme'] ?? 0;
                                    $beneficeClass = $benefice >= 0 ? 'text-success' : 'text-danger';
                                @endphp
                                <h3 class="mb-0 fw-bold {{ $beneficeClass }}">
                                    {{ number_format($benefice, 0, ',', ' ') }} FCFA
                                </h3>
                                <small class="text-muted">
                                    <i class="fas fa-balance-scale-left me-1"></i>
                                    {{ number_format($stats['benefice_plateforme_mois'] ?? 0, 0, ',', ' ') }} FCFA ce mois
                                </small>
                            </div>
                            <div class="ms-3">
                                <div class="bg-success bg-opacity-10 rounded-circle p-3">
                                    <i class="fas fa-chart-line fa-2x text-success"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistiques écosystème -->
        <div class="row g-3 mb-4 admin-stats-grid">
            <div>
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <p class="text-muted small mb-1">Abonnements actifs</p>
                                <h3 class="mb-0 fw-bold">{{ number_format($stats['abonnements_actifs']) }}</h3>
                                <small class="text-warning">
                                    <i class="fas fa-exclamation-triangle me-1"></i>
                                    {{ number_format($stats['abonnements_expirent_bientot'] ?? 0) }} expirent bientôt
                                </small>
                            </div>
                            <div class="ms-3">
                                <div class="bg-info bg-opacity-10 rounded-circle p-3">
                                    <i class="fas fa-credit-card fa-2x text-info"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div>
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <p class="text-muted small mb-1">Boutiques actives</p>
                                <h3 class="mb-0 fw-bold">{{ number_format($stats['boutiques_actives']) }}</h3>
                                <small class="text-muted">
                                    <i class="fas fa-store me-1"></i>{{ number_format($stats['boutiques_total']) }} au
                                    total
                                </small>
                            </div>
                            <div class="ms-3">
                                <div class="bg-success bg-opacity-10 rounded-circle p-3">
                                    <i class="fas fa-store fa-2x text-success"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div>
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <p class="text-muted small mb-1">Commerçants</p>
                                <h3 class="mb-0 fw-bold">{{ number_format($stats['commercants_total']) }}</h3>
                                <small class="text-success">
                                    <i class="fas fa-user-check me-1"></i>{{ number_format($stats['commercants_actifs']) }}
                                    actifs
                                </small>
                            </div>
                            <div class="ms-3">
                                <div class="bg-primary bg-opacity-10 rounded-circle p-3">
                                    <i class="fas fa-users fa-2x text-primary"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Graphiques -->
        <div class="row g-3 mb-4 admin-charts-grid">
            <div>
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-bottom">
                        <h5 class="mb-0 fw-semibold">
                            <i class="fas fa-chart-line me-2 text-primary"></i>
                            Évolution des abonnements (6 derniers mois)
                        </h5>
                    </div>
                    <div class="card-body">
                        <canvas id="chartAbonnements" height="100"></canvas>
                    </div>
                </div>
            </div>

            <div>
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-bottom">
                        <h5 class="mb-0 fw-semibold">
                            <i class="fas fa-chart-area me-2 text-success"></i>
                            Évolution des revenus (6 derniers mois)
                        </h5>
                    </div>
                    <div class="card-body">
                        <canvas id="chartRevenus" height="100"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Top commerçants et boutiques -->
        <div class="row g-3 mb-4 admin-two-grid">
            <div>
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 fw-semibold">
                            <i class="fas fa-trophy me-2 text-warning"></i>
                            Top 5 Commerçants
                        </h5>
                        <a href="{{ route('admin.users.index') }}" class="btn btn-sm btn-link">Voir tout</a>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>Commerçant</th>
                                        <th class="text-end">Chiffre d'affaires</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($topCommercants as $merchant)
                                        <tr>
                                            <td>
                                                <div>
                                                    <strong>{{ $merchant->name }}</strong>
                                                    <br>
                                                    <small class="text-muted">{{ $merchant->email }}</small>
                                                </div>
                                            </td>
                                            <td class="text-end">
                                                <strong class="text-success">
                                                    {{ number_format($merchant->chiffre_affaires ?? 0, 0, ',', ' ') }} FCFA
                                                </strong>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="2" class="text-center text-muted py-4">Aucun commerçant pour
                                                le
                                                moment</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div>
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 fw-semibold">
                            <i class="fas fa-store me-2 text-primary"></i>
                            Top 5 Boutiques
                        </h5>
                        <a href="{{ route('admin.boutiques.index') }}" class="btn btn-sm btn-link">Voir tout</a>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>Boutique</th>
                                        <th class="text-end">Chiffre d'affaires</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($topBoutiques as $boutique)
                                        <tr>
                                            <td>
                                                <div>
                                                    <strong>{{ $boutique->nom }}</strong>
                                                    <br>
                                                    <small
                                                        class="text-muted">{{ $boutique->owner?->name ?? 'N/A' }}</small>
                                                </div>
                                            </td>
                                            <td class="text-end">
                                                <strong class="text-success">
                                                    {{ number_format($boutique->chiffre_affaires ?? 0, 0, ',', ' ') }} FCFA
                                                </strong>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="2" class="text-center text-muted py-4">Aucune boutique pour le
                                                moment</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Abonnements récents et à expirer -->
        <div class="row g-3 mb-4 admin-two-grid">
            <div>
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-bottom">
                        <h5 class="mb-0 fw-semibold">
                            <i class="fas fa-clock me-2 text-info"></i>
                            Abonnements récents
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="list-group list-group-flush">
                            @forelse($abonnementsRecents as $abonnement)
                                <div class="list-group-item px-0 border-0">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <strong>{{ $abonnement->user?->name ?? 'Utilisateur supprimé' }}</strong>
                                            <br>
                                            <small class="text-muted">
                                                {{ $abonnement->type_label }} •
                                                {{ number_format($abonnement->montant, 0, ',', ' ') }} FCFA
                                            </small>
                                        </div>
                                        <span
                                            class="badge bg-{{ $abonnement->statut === 'actif' ? 'success' : ($abonnement->statut === 'suspendu' ? 'warning' : 'danger') }}">
                                            {{ strtoupper($abonnement->statut) }}
                                        </span>
                                    </div>
                                    <small class="text-muted">
                                        Expire le {{ $abonnement->date_expiration->format('d/m/Y') }}
                                    </small>
                                </div>
                            @empty
                                <div class="text-center text-muted py-4">Aucun abonnement récent</div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            <div>
                <div class="card border-0 shadow-sm border-warning">
                    <div class="card-header bg-warning bg-opacity-10 border-bottom">
                        <h5 class="mb-0 fw-semibold">
                            <i class="fas fa-exclamation-triangle me-2 text-warning"></i>
                            Abonnements expirant bientôt (7 jours)
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="list-group list-group-flush">
                            @forelse($abonnementsAExpirer as $abonnement)
                                <div class="list-group-item px-0 border-0">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <strong>{{ $abonnement->user?->name ?? 'Utilisateur supprimé' }}</strong>
                                            <br>
                                            <small class="text-muted">{{ $abonnement->user?->email }}</small>
                                        </div>
                                        <span class="badge bg-warning">
                                            {{ max(0, now()->diffInDays($abonnement->date_expiration, false)) }} jours
                                        </span>
                                    </div>
                                    <small class="text-muted">
                                        Expire le {{ $abonnement->date_expiration->format('d/m/Y') }}
                                    </small>
                                </div>
                            @empty
                                <div class="text-center text-muted py-4">Aucun abonnement n'expire bientôt</div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Paiements en attente -->
        @if ($paiementsEnAttente->count() > 0)
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0 fw-semibold">
                        <i class="fas fa-hourglass-half me-2 text-danger"></i>
                        Paiements en attente de confirmation
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Client</th>
                                    <th>Abonnement</th>
                                    <th>Montant</th>
                                    <th>Mode</th>
                                    <th>Date</th>
                                    <th>Référence</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($paiementsEnAttente as $paiement)
                                    <tr>
                                        <td>
                                            <strong>{{ $paiement->user->name }}</strong>
                                            <br>
                                            <small class="text-muted">{{ $paiement->user->email }}</small>
                                        </td>
                                        <td>{{ $paiement->abonnement->type_label }}</td>
                                        <td><strong>{{ number_format($paiement->montant, 0, ',', ' ') }} FCFA</strong></td>
                                        <td>{{ ucfirst(str_replace('_', ' ', $paiement->mode_paiement)) }}</td>
                                        <td>{{ $paiement->date_paiement->format('d/m/Y') }}</td>
                                        <td><code>{{ $paiement->reference ?? 'N/A' }}</code></td>
                                        <td class="text-end">
                                            <form method="POST"
                                                action="{{ route('admin.paiements.confirmer', $paiement) }}"
                                                class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-success">
                                                    <i class="fas fa-check me-1"></i>Confirmer
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif

        <!-- Répartition par devise -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom">
                <h5 class="mb-0 fw-semibold">
                    <i class="fas fa-globe me-2 text-info"></i>
                    Répartition des boutiques par devise
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    @forelse($revenuParDevise as $ligne)
                        <div class="col-md-3 mb-3">
                            <div class="border rounded p-3 text-center">
                                <h4 class="mb-1">{{ $ligne->devise ?? 'N/A' }}</h4>
                                <p class="text-muted mb-0">{{ $ligne->total }} boutique(s)</p>
                            </div>
                        </div>
                    @empty
                        <div class="col-12 text-center text-muted py-4">Aucune donnée disponible</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <script>
        const computedStyles = getComputedStyle(document.documentElement);
        const chartTextColor = computedStyles.getPropertyValue('--text-secondary')?.trim() || '#9ca3af';
        const chartGridColor = computedStyles.getPropertyValue('--border-light')?.trim() || 'rgba(148, 163, 184, 0.25)';

        // Graphique évolution abonnements
        const ctxAbonnements = document.getElementById('chartAbonnements');
        if (ctxAbonnements) {
            new Chart(ctxAbonnements, {
                type: 'line',
                data: {
                    labels: @json($evolutionAbonnements['labels']),
                    datasets: [{
                        label: 'Nouveaux abonnements',
                        data: @json($evolutionAbonnements['data']),
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
                        x: {
                            ticks: {
                                color: chartTextColor
                            },
                            grid: {
                                color: chartGridColor
                            }
                        },
                        y: {
                            beginAtZero: true,
                            ticks: {
                                color: chartTextColor,
                                stepSize: 1
                            },
                            grid: {
                                color: chartGridColor
                            }
                        }
                    }
                }
            });
        }

        // Graphique évolution revenus
        const ctxRevenus = document.getElementById('chartRevenus');
        if (ctxRevenus) {
            new Chart(ctxRevenus, {
                type: 'bar',
                data: {
                    labels: @json($evolutionRevenus['labels']),
                    datasets: [{
                        label: 'Revenus (FCFA)',
                        data: @json($evolutionRevenus['data']),
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
                        x: {
                            ticks: {
                                color: chartTextColor
                            },
                            grid: {
                                color: chartGridColor
                            }
                        },
                        y: {
                            beginAtZero: true,
                            ticks: {
                                color: chartTextColor,
                                callback: function(value) {
                                    return new Intl.NumberFormat('fr-FR').format(value) + ' FCFA';
                                }
                            },
                            grid: {
                                color: chartGridColor
                            }
                        }
                    }
                }
            });
        }
    </script>
@endsection
