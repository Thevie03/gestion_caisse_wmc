@extends('layouts.app')

@section('title', 'Récapitulatif des Ventes')

@section('content')
    <div class="container-fluid">
        <style>
            .sales-search-card .card-header {
                padding: 0.7rem 1rem;
            }

            .sales-search-card .card-body {
                padding: 0.9rem 1rem;
            }

            .sales-search-form {
                --bs-gutter-x: 0.9rem;
                --bs-gutter-y: 0.65rem;
            }

            .sales-search-form .form-label {
                margin-bottom: 0.3rem;
                font-size: 0.9rem;
            }

            .sales-search-form .form-control,
            .sales-search-form .form-select {
                min-height: 38px;
                padding-top: 0.35rem;
                padding-bottom: 0.35rem;
            }

            .sales-search-actions {
                margin-top: 0.25rem;
            }
        </style>

        <!-- En-tête -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h1 class="h3 mb-0 text-gray-800">
                            <i class="fas fa-chart-line me-2"></i>
                            Récapitulatif des Ventes
                        </h1>
                        <p class="text-muted mb-0">Recherche et analyse des ventes par dates</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filtres -->
        <div class="card shadow mb-4 sales-search-card">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-caret-down me-2" style="color: #C23028;"></i>
                    Critères de Recherche
                </h6>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('factures.index') }}" class="row sales-search-form">
                    <div class="col-12 col-md-6 col-lg-3">
                        <label for="date_debut" class="form-label">Date de début <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="date_debut" name="date_debut"
                            value="{{ request('date_debut') }}" required>
                    </div>
                    <div class="col-12 col-md-6 col-lg-3">
                        <label for="date_fin" class="form-label">Date de fin <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="date_fin" name="date_fin"
                            value="{{ request('date_fin') }}" required>
                    </div>
                    <div class="col-12 col-md-6 col-lg-3">
                        <label for="numero_vente" class="form-label">Numéro de vente</label>
                        <input type="text" class="form-control" id="numero_vente" name="numero_vente"
                            value="{{ request('numero_vente') }}" placeholder="V-20251029-0001">
                    </div>
                    <div class="col-12 col-md-6 col-lg-3">
                        <label for="client" class="form-label">Client</label>
                        <input type="text" class="form-control" id="client" name="client"
                            value="{{ request('client') }}" placeholder="Nom du client">
                    </div>

                    <div class="col-12 col-md-6 col-lg-3">
                        <label for="mode_paiement" class="form-label">Mode de paiement</label>
                        <select class="form-select" id="mode_paiement" name="mode_paiement">
                            <option value="">Tous</option>
                            <option value="especes" {{ request('mode_paiement') == 'especes' ? 'selected' : '' }}>Espèces
                            </option>
                            <option value="wave" {{ request('mode_paiement') == 'wave' ? 'selected' : '' }}>Wave</option>
                            <option value="orange_money" {{ request('mode_paiement') == 'orange_money' ? 'selected' : '' }}>
                                Orange Money</option>
                            <option value="mtn_money" {{ request('mode_paiement') == 'mtn_money' ? 'selected' : '' }}>MTN
                                Money</option>
                            <option value="carte" {{ request('mode_paiement') == 'carte' ? 'selected' : '' }}>Carte
                            </option>
                        </select>
                    </div>

                    <div class="col-12 col-md-6 col-lg-3">
                        <label for="statut_paiement" class="form-label">Statut paiement</label>
                        <select class="form-select" id="statut_paiement" name="statut_paiement">
                            <option value="">Tous</option>
                            <option value="complet" {{ request('statut_paiement') == 'complet' ? 'selected' : '' }}>Soldé
                            </option>
                            <option value="partiel" {{ request('statut_paiement') == 'partiel' ? 'selected' : '' }}>Partiel
                            </option>
                            <option value="impaye" {{ request('statut_paiement') == 'impaye' ? 'selected' : '' }}>Impayé
                            </option>
                        </select>
                    </div>

                    @if (auth()->user()->isAdmin() && $boutiques->count() > 0)
                        <div class="col-12 col-md-6 col-lg-3">
                            <label for="boutique_id" class="form-label">Boutique</label>
                            <select class="form-select" id="boutique_id" name="boutique_id">
                                <option value="">Toutes les boutiques</option>
                                @foreach ($boutiques as $boutique)
                                    <option value="{{ $boutique->id }}"
                                        {{ request('boutique_id') == $boutique->id ? 'selected' : '' }}>
                                        {{ $boutique->nom }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    <div class="col-12 sales-search-actions">
                        <button type="submit" class="btn"
                            style="background-color: #C23028; color: white; border: none;">
                            <i class="fas fa-search me-1"></i>
                            Rechercher
                        </button>
                        <a href="{{ route('factures.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-times me-1"></i>
                            Réinitialiser
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Statistiques Générales -->
        <div class="row mb-4">
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-primary shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                    TOTAL VENTES
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
                                    CHIFFRE D'AFFAIRES
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
                                    PANIER MOYEN
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
                                    PRODUITS VENDUS
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    {{ number_format($stats['produits_vendus']) }}
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-box fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistiques par Mode de Paiement -->
        <div class="row mb-4">
            <div class="col-xl-4 col-md-6 mb-4">
                <div class="card shadow h-100">
                    <div class="card-header bg-success text-white">
                        <h6 class="m-0 font-weight-bold">
                            <i class="fas fa-money-bill-wave me-2"></i>
                            Espèces
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="h4 mb-0 font-weight-bold text-success">
                            {{ number_format($stats['especes'], 0, ',', ' ') }} FCFA
                        </div>
                        @if ($stats['chiffre_affaires'] > 0)
                            <small class="text-muted">
                                {{ number_format(($stats['especes'] / $stats['chiffre_affaires']) * 100, 1) }}% du CA
                            </small>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-xl-4 col-md-6 mb-4">
                <div class="card shadow h-100">
                    <div class="card-header text-white" style="background-color: #5DADE2;">
                        <h6 class="m-0 font-weight-bold">
                            <i class="fas fa-mobile-alt me-2"></i>
                            Mobile Money
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="h4 mb-0 font-weight-bold" style="color: #5DADE2;">
                            {{ number_format($stats['mobile_money'], 0, ',', ' ') }} FCFA
                        </div>
                        @if ($stats['chiffre_affaires'] > 0)
                            <small class="text-muted">
                                {{ number_format(($stats['mobile_money'] / $stats['chiffre_affaires']) * 100, 1) }}% du CA
                            </small>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-xl-4 col-md-6 mb-4">
                <div class="card shadow h-100">
                    <div class="card-header text-white" style="background-color: #C23028;">
                        <h6 class="m-0 font-weight-bold">
                            <i class="fas fa-credit-card me-2"></i>
                            Carte
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="h4 mb-0 font-weight-bold" style="color: #C23028;">
                            {{ number_format($stats['carte'], 0, ',', ' ') }} FCFA
                        </div>
                        @if ($stats['chiffre_affaires'] > 0)
                            <small class="text-muted">
                                {{ number_format(($stats['carte'] / $stats['chiffre_affaires']) * 100, 1) }}% du CA
                            </small>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistiques par Statut de Paiement -->
        <div class="row mb-4">
            <div class="col-xl-4 col-md-6 mb-4">
                <div class="card shadow h-100">
                    <div class="card-header bg-success text-white">
                        <h6 class="m-0 font-weight-bold">
                            <i class="fas fa-check-circle me-2"></i>
                            Ventes Soldées
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="h4 mb-0 font-weight-bold text-success">
                            {{ number_format($stats['soldes'], 0, ',', ' ') }} FCFA
                        </div>
                        @if ($stats['chiffre_affaires'] > 0)
                            <small class="text-muted">
                                {{ number_format(($stats['soldes'] / $stats['chiffre_affaires']) * 100, 1) }}% du CA
                            </small>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-xl-4 col-md-6 mb-4">
                <div class="card shadow h-100">
                    <div class="card-header bg-warning text-white">
                        <h6 class="m-0 font-weight-bold">
                            <i class="fas fa-circle-half-stroke me-2"></i>
                            Paiements Partiels
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="h4 mb-0 font-weight-bold text-warning">
                            {{ number_format($stats['partiels'], 0, ',', ' ') }} FCFA
                        </div>
                        @if ($stats['chiffre_affaires'] > 0)
                            <small class="text-muted">
                                {{ number_format(($stats['partiels'] / $stats['chiffre_affaires']) * 100, 1) }}% du CA
                            </small>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-xl-4 col-md-6 mb-4">
                <div class="card shadow h-100">
                    <div class="card-header bg-danger text-white">
                        <h6 class="m-0 font-weight-bold">
                            <i class="fas fa-ban me-2"></i>
                            Ventes Impayées
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="h4 mb-0 font-weight-bold text-danger">
                            {{ number_format($stats['impayes'], 0, ',', ' ') }} FCFA
                        </div>
                        @if ($stats['chiffre_affaires'] > 0)
                            <small class="text-muted">
                                {{ number_format(($stats['impayes'] / $stats['chiffre_affaires']) * 100, 1) }}% du CA
                            </small>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Liste des Ventes -->
        <div class="card shadow">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-list me-2"></i>
                    Liste des Ventes ({{ $ventes->total() }})
                </h6>
            </div>
            <div class="card-body">
                @if ($ventes->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>N° Vente</th>
                                    <th>Date</th>
                                    <th>Client</th>
                                    <th>Vendeur</th>
                                    <th>Total</th>
                                    <th>Mode Paiement</th>
                                    <th>Statut</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($ventes as $vente)
                                    <tr>
                                        <td>
                                            <strong class="text-primary">{{ $vente->numero_vente }}</strong>
                                        </td>
                                        <td>{{ $vente->created_at->format('d/m/Y H:i') }}</td>
                                        <td>
                                            @if ($vente->client)
                                                {{ $vente->client->nom_complet }}
                                            @else
                                                <span class="text-muted">Anonyme</span>
                                            @endif
                                        </td>
                                        <td>{{ $vente->user->name }}</td>
                                        <td>
                                            <strong>{{ number_format($vente->total_final, 0, ',', ' ') }} FCFA</strong>
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
                                                    <span class="badge bg-primary">Carte</span>
                                                @break
                                            @endswitch
                                        </td>
                                        <td>
                                            @if ($vente->statut_paiement == 'complet')
                                                <span class="badge bg-success">
                                                    <i class="fas fa-check-circle me-1"></i>Soldé
                                                </span>
                                            @elseif($vente->statut_paiement == 'partiel')
                                                <span class="badge bg-warning">
                                                    <i class="fas fa-clock me-1"></i>Partiel
                                                </span>
                                                <small class="text-muted d-block">
                                                    Reste: {{ number_format($vente->solde_restant, 0, ',', ' ') }} FCFA
                                                </small>
                                            @else
                                                <span class="badge bg-danger">
                                                    <i class="fas fa-exclamation-circle me-1"></i>Impayé
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('ventes.show', $vente) }}" class="btn btn-sm btn-info"
                                                    title="Voir détails">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('ventes.imprimer', $vente) }}"
                                                    class="btn btn-sm btn-primary" target="_blank" title="Imprimer">
                                                    <i class="fas fa-print"></i>
                                                </a>
                                                @auth
                                                    @if (
                                                        (auth()->user()->isAdmin() || auth()->user()->isOwner()) &&
                                                            (auth()->user()->isAdmin() || $vente->boutique_id == auth()->user()->boutique_id))
                                                        <a href="{{ route('ventes.edit', $vente) }}"
                                                            class="btn btn-sm btn-warning" title="Modifier la vente">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <button type="button" class="btn btn-sm btn-outline-danger"
                                                            onclick="confirmerSuppressionVente('{{ $vente->numero_vente }}', '{{ route('ventes.destroy', $vente) }}')"
                                                            title="Supprimer la vente">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    @endif
                                                @endauth
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="mt-3">
                        {{ $ventes->appends(request()->query())->links() }}
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                        <h4>Aucune vente trouvée</h4>
                        <p class="text-muted">Aucune vente ne correspond à vos critères de recherche.</p>
                        <a href="{{ route('ventes.pos') }}" class="btn btn-primary">
                            <i class="fas fa-cash-register me-2"></i>
                            Nouvelle vente
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Modal de confirmation de suppression -->
    <div class="modal fade" id="modalSuppression" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-exclamation-triangle text-warning me-2"></i>
                        Confirmation de suppression
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Êtes-vous sûr de vouloir supprimer définitivement la facture <strong id="nomFacture"></strong> ?</p>
                    <p class="text-danger">
                        <i class="fas fa-exclamation-circle me-1"></i>
                        Cette action est irréversible et supprimera également la vente associée !
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>
                        Annuler
                    </button>
                    <form id="formSuppression" method="POST" style="display: inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-trash me-1"></i>
                            Supprimer définitivement
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de confirmation de suppression de vente -->
    <div class="modal fade" id="modalSuppressionVente" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-exclamation-triangle text-warning me-2"></i>
                        Confirmation de suppression
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Êtes-vous sûr de vouloir supprimer définitivement la vente <strong id="nomVente"></strong> ?</p>
                    <p class="text-danger">
                        <i class="fas fa-exclamation-circle me-1"></i>
                        Cette action est irréversible. Le stock des produits sera restauré automatiquement.
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>
                        Annuler
                    </button>
                    <form id="formSuppressionVente" method="POST" style="display: inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-trash me-1"></i>
                            Supprimer définitivement
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function confirmerSuppression(nomFacture, url) {
            document.getElementById('nomFacture').textContent = nomFacture;
            document.getElementById('formSuppression').action = url;
            new bootstrap.Modal(document.getElementById('modalSuppression')).show();
        }

        function confirmerSuppressionVente(numeroVente, url) {
            document.getElementById('nomVente').textContent = numeroVente;
            document.getElementById('formSuppressionVente').action = url;
            new bootstrap.Modal(document.getElementById('modalSuppressionVente')).show();
        }
    </script>
@endsection
