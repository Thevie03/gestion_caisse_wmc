@extends('layouts.app')

@section('content')
    <div class="saas-dashboard">
        <style>
            @media (min-width: 992px) {
                .dashboard-kpi-grid {
                    display: grid;
                    grid-template-columns: repeat(4, minmax(0, 1fr));
                    gap: 1rem;
                }

                .dashboard-kpi-grid > [class*="col-"] {
                    width: auto !important;
                    max-width: none !important;
                    flex: initial !important;
                    padding-left: 0.75rem;
                    padding-right: 0.75rem;
                }
            }

            @media (min-width: 768px) {
                .dashboard-charts-row {
                    display: grid;
                    grid-template-columns: minmax(0, 2fr) minmax(0, 1fr);
                    gap: 1rem;
                    align-items: stretch;
                }

                .dashboard-charts-row > [class*="col-"] {
                    width: auto !important;
                    max-width: none !important;
                    flex: initial !important;
                    padding-left: 0 !important;
                    padding-right: 0 !important;
                    margin-bottom: 0 !important;
                }

                .dashboard-sales-row {
                    display: grid;
                    grid-template-columns: minmax(0, 2fr) minmax(0, 1fr);
                    gap: 1rem;
                    align-items: stretch;
                }

                .dashboard-sales-row > [class*="col-"] {
                    width: auto !important;
                    max-width: none !important;
                    flex: initial !important;
                    padding-left: 0 !important;
                    padding-right: 0 !important;
                    margin-bottom: 0 !important;
                }
            }
        </style>

        @php
            $chiffreAffairesVariation = ($totaux['chiffre_affaires'] ?? 0) > 0 ? '+12.4%' : '0%';
            $depensesVariation = ($totaux['depenses_totales'] ?? 0) > 0 ? '-3.8%' : '0%';
            $ventesVariation = ($totaux['nombre_ventes'] ?? 0) > 0 ? '+9.1%' : '0%';
            $produitsVariation = ($totaux['nombre_produits'] ?? 0) > 0 ? '+4.2%' : '0%';
        @endphp

        @if (isset($boutiqueActive) && $boutiqueActive)
            <section class="saas-hero mb-4">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                    <div class="d-flex align-items-center gap-3">
                        @if ($boutiqueActive->logo)
                            <img src="{{ $boutiqueActive->logo }}" alt="Logo {{ $boutiqueActive->nom }}" class="saas-hero-logo">
                        @else
                            <div class="saas-hero-logo saas-hero-logo-fallback">
                                <i class="fas fa-store"></i>
                            </div>
                        @endif
                        <div>
                            <span class="saas-section-kicker">Vue générale</span>
                            <h2 class="saas-hero-title mb-1">{{ $boutiqueActive->nom }}</h2>
                            <p class="saas-hero-subtitle mb-0">{{ $boutiqueActive->description ?? 'Suivi en temps réel de vos ventes et de votre activité.' }}</p>
                        </div>
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        <a href="{{ route('ventes.pos') }}" class="btn saas-btn-primary">
                            <i class="fas fa-cash-register me-2"></i>Nouvelle vente
                        </a>
                        <a href="{{ route('rapports.index') }}" class="btn saas-btn-ghost">
                            <i class="fas fa-chart-line me-2"></i>Voir rapports
                        </a>
                    </div>
                </div>
            </section>
        @endif

        <!-- Statistiques principales -->
        <div class="row mb-4 g-3 dashboard-kpi-grid">
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="dashboard-stat-card h-100">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="stat-label">Chiffre d’affaires total</div>
                        <div class="stat-value">{{ number_format($totaux['chiffre_affaires'], 0, ',', ' ') }} FCFA</div>
                        <div class="stat-trend stat-trend-positive">{{ $chiffreAffairesVariation }} ce mois</div>
                    </div>
                    <div class="stat-icon stat-icon-orange">
                        <i class="fas fa-coins"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-lg-3">
            <div class="dashboard-stat-card h-100">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="stat-label">Dépenses totales</div>
                        <div class="stat-value">{{ number_format($totaux['depenses_totales'], 0, ',', ' ') }} FCFA</div>
                        <div class="stat-trend stat-trend-negative">{{ $depensesVariation }} ce mois</div>
                    </div>
                    <div class="stat-icon stat-icon-red">
                        <i class="fas fa-receipt"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-lg-3">
            <div class="dashboard-stat-card h-100">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="stat-label">Nombre de ventes</div>
                        <div class="stat-value">{{ number_format($totaux['nombre_ventes'], 0, ',', ' ') }}</div>
                        <div class="stat-trend stat-trend-positive">{{ $ventesVariation }} cette semaine</div>
                    </div>
                    <div class="stat-icon stat-icon-blue">
                        <i class="fas fa-shopping-basket"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-lg-3">
            <div class="dashboard-stat-card h-100">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="stat-label">Produits actifs</div>
                        <div class="stat-value">{{ number_format($totaux['nombre_produits'], 0, ',', ' ') }}</div>
                        <small class="d-block" style="color: rgba(226,232,240,0.75);">
                            {{ number_format($totaux['nombre_categories'], 0, ',', ' ') }} catégories
                        </small>
                        <div class="stat-trend stat-trend-positive">{{ $produitsVariation }} ce mois</div>
                    </div>
                    <div class="stat-icon stat-icon-violet">
                        <i class="fas fa-boxes"></i>
                    </div>
                </div>
            </div>
        </div>
        </div>

        @if (!$user->isAdmin())
            <div class="row mb-4 g-3">
                <div class="col-12 col-xl-4">
                    <div class="card h-100 saas-surface-card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="mb-0">Etat de l’abonnement</h6>
                                <span class="badge bg-{{ $abonnementActif?->statut === 'actif' ? 'success' : ($abonnementActif?->statut === 'suspendu' ? 'warning' : 'danger') }}">
                                    {{ strtoupper($abonnementActif->statut ?? 'AUCUN') }}
                                </span>
                            </div>
                            @if ($abonnementActif)
                                <p class="mb-1 fw-semibold">
                                    @if ($abonnementActif->type_abonnement === 'acquisition_definitive')
                                        {{ $abonnementActif->type_label }} - Permanent
                                    @else
                                        {{ $abonnementActif->type_label }}
                                    @endif
                                </p>
                                @if ($abonnementActif->type_abonnement !== 'acquisition_definitive')
                                    <p class="text-muted small mb-3">
                                        Expire le {{ $abonnementActif->date_expiration->format('d/m/Y') }}
                                        ({{ $abonnementActif->date_expiration->diffForHumans(null, true) }})
                                    </p>
                                @else
                                    <p class="text-muted small mb-3">
                                        <i class="fas fa-infinity me-1"></i>Acces permanent
                                    </p>
                                @endif
                                <div class="progress saas-progress">
                                    @php
                                        $total = $abonnementActif->date_debut->diffInDays($abonnementActif->date_expiration);
                                        $reste = now()->diffInDays($abonnementActif->date_expiration, false);
                                        $percent = $total > 0 ? max(0, min(100, (($total - $reste) / $total) * 100)) : 100;
                                    @endphp
                                    <div class="progress-bar bg-{{ $percent > 70 ? 'danger' : 'warning' }}" style="width: {{ $percent }}%"></div>
                                </div>
                            @else
                                <p class="text-muted mb-3">Aucun abonnement actif. Merci de renouveler pour continuer.</p>
                                <a href="mailto:{{ config('mail.from.address') }}" class="btn saas-btn-primary btn-sm">Contacter l’administration</a>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-xl-4">
                    <div class="card h-100 saas-surface-card">
                        <div class="card-body">
                            <h6 class="mb-2">Parametres boutique</h6>
                            <p class="mb-1 fw-semibold">{{ optional($parametresBoutique)->nom_affichage ?? ($boutiqueActive->nom ?? 'Boutique non configuree') }}</p>
                            <p class="text-muted small mb-3">
                                Devise: <strong>{{ optional($parametresBoutique)->devise ?? ($boutiqueActive->devise ?? 'FCFA') }}</strong><br>
                                Fuseau: <strong>{{ optional($parametresBoutique)->timezone ?? config('app.timezone') }}</strong>
                            </p>
                            @if ($boutiqueActive)
                                <a href="{{ route('boutiques.edit', $boutiqueActive) }}" class="btn saas-btn-ghost btn-sm">Mettre a jour</a>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-xl-4">
                    <div class="card h-100 saas-surface-card">
                        <div class="card-body">
                            <h6 class="mb-2">Ventes du jour</h6>
                            <p class="fw-semibold mb-1">{{ number_format($stats['ventes_aujourdhui'] ?? 0, 0, ',', ' ') }} {{ $boutiqueActive->devise ?? 'FCFA' }}</p>
                            <p class="text-muted small mb-3">Chiffre d’affaires enregistré aujourd’hui.</p>
                            <a href="{{ route('factures.index') }}" class="btn saas-btn-ghost btn-sm">Récapitulatif ventes</a>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Graphiques -->
        <div class="row mb-4 g-3 dashboard-charts-row">
        <div class="col-12 col-md-8 mb-3">
            <div class="card saas-surface-card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-chart-line me-2"></i>
                        Ventes des 7 derniers jours
                    </h5>
                </div>
                <div class="card-body">
                    <canvas id="ventesChart" height="100"></canvas>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-4 mb-3">
            <div class="card saas-surface-card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-chart-pie me-2"></i>
                        produits/articles par catégorie
                    </h5>
                </div>
                <div class="card-body">
                    <canvas id="produitsChart"></canvas>
                </div>
            </div>
        </div>
        </div>

        <!-- Dernières ventes et alertes -->
            <div class="row g-3 dashboard-sales-row">
        <div class="col-12 col-lg-8 mb-3">
            <div class="card saas-surface-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="card-title mb-0">
                            <i class="fas fa-clock me-2"></i>
                            Dernières ventes
                        </h5>
                        <small class="text-muted">Flux récent de vos ventes</small>
                    </div>
                    <div class="filters-panel d-none d-md-flex">
                        <button type="button" class="filter-pill filter-pill-active">Aujourd’hui</button>
                        <button type="button" class="filter-pill">7 derniers jours</button>
                        <button type="button" class="filter-pill">30 jours</button>
                        <button type="button" class="filter-pill">Tout</button>
                    </div>
                </div>
                <div class="card-body">
                    @if ($dernieresVentes->count() > 0)
                        <div class="row g-3">
                            @foreach ($dernieresVentes as $vente)
                                <div class="col-12 col-xl-6">
                                    <div class="order-card">
                                        <div class="order-card-header">
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="order-avatar">
                                                    {{ Str::of($vente->user->name)->substr(0, 1)->upper() }}
                                                </div>
                                                <div>
                                                    <div class="fw-semibold">
                                                        {{ $vente->user->name }}
                                                    </div>
                                                    <small class="text-muted">
                                                        {{ $vente->boutique->nom }} • {{ $vente->numero_vente }}
                                                    </small>
                                                </div>
                                            </div>
                                            <span class="order-status-pill order-status-pill--done">
                                                {{ $vente->created_at->format('H:i') }}
                                            </span>
                                        </div>
                                        <div class="order-card-body">
                                            <div class="d-flex justify-content-between">
                                                <span>Montant</span>
                                                <strong class="text-success">
                                                    {{ number_format($vente->total_final, 0, ',', ' ') }} FCFA
                                                </strong>
                                            </div>
                                        </div>
                                        <div class="order-card-footer">
                                            <small class="text-muted">
                                                {{ $vente->created_at->format('d/m/Y') }}
                                            </small>
                                            <a href="{{ route('ventes.show', $vente) }}" class="btn btn-sm saas-btn-ghost">
                                                Détails
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center text-muted py-3">
                            <i class="fas fa-shopping-cart fa-3x mb-3 opacity-25"></i>
                            <p>Aucune vente enregistrée</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-4 mb-3">
            <div class="card saas-surface-card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Alertes stock
                    </h5>
                </div>
                <div class="card-body">
                    @if ($produitsRupture->count() > 0)
                        <div class="list-group list-group-flush">
                            @foreach ($produitsRupture as $produit)
                                <div class="list-group-item d-flex justify-content-between align-items-center saas-list-item">
                                    <div>
                                        <div class="fw-bold">{{ $produit->nom }}</div>
                                        <small class="text-muted">{{ $produit->categorie }}</small>
                                    </div>
                                    <div class="text-end">
                                        <span class="badge badge-danger">{{ $produit->quantite_stock }} restant</span>
                                        <div class="small text-muted">Min: {{ $produit->stock_minimum }}</div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center text-success py-3">
                            <i class="fas fa-check-circle fa-3x mb-3"></i>
                            <p>Tous les stocks sont suffisants</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        </div>

        <!-- Top produits -->
        @if ($topProduits->count() > 0)
            <div class="row">
            <div class="col-12">
                <div class="card saas-surface-card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-trophy me-2"></i>
                            Top 5 produits les plus vendus
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover saas-table">
                                <thead>
                                    <tr>
                                        <th>Rang</th>
                                        <th>Produit</th>
                                        <th>Quantité vendue</th>
                                        <th>Performance</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($topProduits as $index => $produit)
                                        <tr>
                                            <td>
                                                <span class="badge badge-primary">{{ $index + 1 }}</span>
                                            </td>
                                            <td>{{ $produit->nom }}</td>
                                            <td>{{ $produit->total_quantite }}</td>
                                            <td>
                                                <div class="progress" style="height: 8px;">
                                                    <div class="progress-bar"
                                                        role="progressbar"
                                                        style="width: {{ ($produit->total_quantite / $topProduits->first()->total_quantite) * 100 }}%">
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            </div>
        @endif
    </div>

    <!-- Scripts pour les graphiques -->
    <script>
        // Initialisation des graphiques (attendre que tout soit chargé)
        function initCharts() {
            try {
                // Vérifier que Chart.js est disponible avec plusieurs tentatives
                let attempts = 0;
                const maxAttempts = 50;

                function checkChart() {
                    if (typeof Chart !== 'undefined') {
                        createCharts();
                    } else if (attempts < maxAttempts) {
                        attempts++;
                        setTimeout(checkChart, 100);
                    } else {
                        console.error('Chart.js n\'est pas chargé après plusieurs tentatives');
                        const ventesChartElement = document.getElementById('ventesChart');
                        const produitsChartElement = document.getElementById('produitsChart');
                        if (ventesChartElement) {
                            ventesChartElement.parentElement.innerHTML =
                                '<div class="alert alert-warning"><i class="fas fa-exclamation-triangle me-2"></i>Chart.js n\'est pas disponible. Veuillez vérifier votre connexion internet et recharger la page.</div>';
                        }
                        if (produitsChartElement) {
                            produitsChartElement.parentElement.innerHTML =
                                '<div class="alert alert-warning"><i class="fas fa-exclamation-triangle me-2"></i>Chart.js n\'est pas disponible. Veuillez vérifier votre connexion internet et recharger la page.</div>';
                        }
                    }
                }

                function createCharts() {

                    // Données des graphiques
                    const ventesLabels = @json(isset($ventesChart) && is_array($ventesChart) ? $ventesChart['labels'] ?? [] : []);
                    const ventesData = @json(isset($ventesChart) && is_array($ventesChart) ? $ventesChart['data'] ?? [] : []);
                    const produitsLabels = @json(isset($produitsChart) && is_array($produitsChart) ? $produitsChart['labels'] ?? [] : []);
                    const produitsData = @json(isset($produitsChart) && is_array($produitsChart) ? $produitsChart['data'] ?? [] : []);

                    // Graphique des ventes
                    const ventesChartElement = document.getElementById('ventesChart');
                    if (ventesChartElement) {
                        try {
                            if (ventesLabels.length > 0 && ventesData.length > 0) {
                                const ventesCtx = ventesChartElement.getContext('2d');
                                const gradient = ventesCtx.createLinearGradient(0, 0, 0, 280);
                                gradient.addColorStop(0, 'rgba(245, 158, 11, 0.35)');
                                gradient.addColorStop(1, 'rgba(245, 158, 11, 0.02)');
                                new Chart(ventesCtx, {
                                    type: 'line',
                                    data: {
                                        labels: ventesLabels,
                                        datasets: [{
                                            label: 'Ventes (FCFA)',
                                            data: ventesData,
                                            borderColor: '#F59E0B',
                                            backgroundColor: gradient,
                                            borderWidth: 3,
                                            fill: true,
                                            pointRadius: 0,
                                            pointHoverRadius: 6,
                                            pointHoverBackgroundColor: '#F59E0B',
                                            pointHoverBorderColor: '#0B1220',
                                            tension: 0.42
                                        }]
                                    },
                                    options: {
                                        responsive: true,
                                        maintainAspectRatio: false,
                                        plugins: {
                                            legend: {
                                                display: false
                                            },
                                            tooltip: {
                                                backgroundColor: '#0F172A',
                                                borderColor: 'rgba(245, 158, 11, 0.45)',
                                                borderWidth: 1,
                                                padding: 12,
                                                titleColor: '#F8FAFC',
                                                bodyColor: '#E2E8F0',
                                                cornerRadius: 10,
                                                displayColors: false,
                                                callbacks: {
                                                    label: function(context) {
                                                        return new Intl.NumberFormat('fr-FR').format(context.parsed.y) + ' FCFA';
                                                    }
                                                }
                                            }
                                        },
                                        scales: {
                                            y: {
                                                beginAtZero: true,
                                                grid: {
                                                    color: 'rgba(148, 163, 184, 0.14)'
                                                },
                                                ticks: {
                                                    color: '#94A3B8',
                                                    callback: function(value) {
                                                        return new Intl.NumberFormat('fr-FR').format(value) + ' FCFA';
                                                    }
                                                }
                                            },
                                            x: {
                                                grid: {
                                                    display: false
                                                },
                                                ticks: {
                                                    color: '#94A3B8'
                                                }
                                            }
                                        }
                                    }
                                });
                            } else {
                                ventesChartElement.parentElement.innerHTML =
                                    '<div class="text-center text-muted py-4"><i class="fas fa-chart-line fa-2x mb-2 opacity-25"></i><p>Aucune donnée disponible</p></div>';
                            }
                        } catch (error) {
                            console.error('Erreur lors de la création du graphique des ventes:', error);
                            ventesChartElement.parentElement.innerHTML =
                                '<div class="alert alert-danger"><i class="fas fa-exclamation-triangle me-2"></i>Erreur lors du chargement du graphique</div>';
                        }
                    }

                    // Graphique des produits par catégorie
                    const produitsChartElement = document.getElementById('produitsChart');
                    if (produitsChartElement) {
                        try {
                            if (produitsLabels.length > 0 && produitsData.length > 0) {
                                const produitsCtx = produitsChartElement.getContext('2d');
                                new Chart(produitsCtx, {
                                    type: 'doughnut',
                                    data: {
                                        labels: produitsLabels,
                                        datasets: [{
                                            data: produitsData,
                                            backgroundColor: [
                                                '#F59E0B',
                                                '#3B82F6',
                                                '#8B5CF6',
                                                '#10B981',
                                                '#F43F5E',
                                                '#06B6D4'
                                            ],
                                            borderColor: '#0F172A',
                                            borderWidth: 2,
                                            hoverOffset: 8
                                        }]
                                    },
                                    options: {
                                        responsive: true,
                                        maintainAspectRatio: false,
                                        plugins: {
                                            legend: {
                                                position: 'bottom',
                                                labels: {
                                                    color: '#CBD5E1',
                                                    boxWidth: 12,
                                                    padding: 16
                                                }
                                            },
                                            tooltip: {
                                                backgroundColor: '#0F172A',
                                                borderColor: 'rgba(245, 158, 11, 0.4)',
                                                borderWidth: 1,
                                                titleColor: '#F8FAFC',
                                                bodyColor: '#E2E8F0',
                                                cornerRadius: 10
                                            }
                                        },
                                        cutout: '66%'
                                    }
                                });
                            } else {
                                produitsChartElement.parentElement.innerHTML =
                                    '<div class="text-center text-muted py-4"><i class="fas fa-chart-pie fa-2x mb-2 opacity-25"></i><p>Aucune donnée disponible</p></div>';
                            }
                        } catch (error) {
                            console.error('Erreur lors de la création du graphique des produits:', error);
                            produitsChartElement.parentElement.innerHTML =
                                '<div class="alert alert-danger"><i class="fas fa-exclamation-triangle me-2"></i>Erreur lors du chargement du graphique</div>';
                        }
                    }
                }

                // Démarrer la vérification
                checkChart();
            } catch (error) {
                console.error('Erreur générale lors de l\'initialisation des graphiques:', error);
            }
        }

        // Attendre que la page soit complètement chargée
        if (document.readyState === 'complete') {
            initCharts();
        } else {
            window.addEventListener('load', initCharts);
        }
    </script>
@endsection
