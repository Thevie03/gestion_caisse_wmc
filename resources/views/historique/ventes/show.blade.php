@extends('layouts.app')

@section('title', 'Détails de l\'Historique')

@section('content')
    <div class="container-fluid">
        <!-- En-tête -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h1 class="h3 mb-0 text-gray-800">
                            <i class="fas fa-info-circle me-2"></i>
                            Détails de l'Action
                        </h1>
                        <p class="text-muted mb-0">
                            @if ($historique->type_action === 'modification')
                                Modification de la vente {{ $historique->numero_vente }}
                            @else
                                Suppression de la vente {{ $historique->numero_vente }}
                            @endif
                        </p>
                    </div>
                    <div>
                        <a href="{{ route('historique.ventes.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-1"></i>
                            Retour
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Informations générales -->
            <div class="col-lg-4">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-info-circle me-2"></i>
                            Informations Générales
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Type d'action</label>
                            <p class="form-control-plaintext">
                                @if ($historique->type_action === 'modification')
                                    <span class="badge bg-warning">
                                        <i class="fas fa-edit me-1"></i>
                                        Modification
                                    </span>
                                @else
                                    <span class="badge bg-danger">
                                        <i class="fas fa-trash me-1"></i>
                                        Suppression
                                    </span>
                                @endif
                            </p>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Numéro de vente</label>
                            <p class="form-control-plaintext">
                                <strong class="text-primary">{{ $historique->numero_vente }}</strong>
                            </p>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Date et heure</label>
                            <p class="form-control-plaintext">
                                {{ $historique->created_at->format('d/m/Y à H:i:s') }}
                            </p>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Administrateur</label>
                            <p class="form-control-plaintext">
                                <strong>{{ $historique->user->name }}</strong>
                                <br>
                                <small class="text-muted">{{ $historique->user->email }}</small>
                            </p>
                        </div>

                        @if ($historique->boutique)
                            <div class="mb-3">
                                <label class="form-label fw-bold">Boutique</label>
                                <p class="form-control-plaintext">
                                    <span class="badge bg-secondary">{{ $historique->boutique->nom }}</span>
                                </p>
                            </div>
                        @endif

                        @if ($historique->changements)
                            <div class="mb-3">
                                <label class="form-label fw-bold">Résumé des changements</label>
                                <p class="form-control-plaintext">
                                    <small>{{ $historique->changements }}</small>
                                </p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Détails avant/après -->
            <div class="col-lg-8">
                @if ($historique->type_action === 'modification')
                    <!-- Comparaison avant/après pour les modifications -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card shadow mb-4">
                                <div class="card-header bg-danger text-white py-3">
                                    <h6 class="m-0 font-weight-bold">
                                        <i class="fas fa-arrow-left me-2"></i>
                                        Avant Modification
                                    </h6>
                                </div>
                                <div class="card-body">
                                    @if ($historique->donnees_avant)
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">Client</label>
                                            <p>{{ $historique->donnees_avant['client_nom'] ?? 'Anonyme' }}</p>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">Total</label>
                                            <p>
                                                <strong>{{ number_format($historique->donnees_avant['total_final'] ?? 0, 0, ',', ' ') }}
                                                    FCFA</strong>
                                            </p>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">Remise</label>
                                            <p>{{ number_format($historique->donnees_avant['remise'] ?? 0, 0, ',', ' ') }}
                                                FCFA</p>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">Mode de paiement</label>
                                            <p>
                                                <span class="badge bg-secondary">
                                                    {{ ucfirst(str_replace('_', ' ', $historique->donnees_avant['mode_paiement'] ?? '-')) }}
                                                </span>
                                            </p>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">Statut</label>
                                            <p>
                                                <span class="badge bg-secondary">
                                                    {{ ucfirst($historique->donnees_avant['statut_paiement'] ?? '-') }}
                                                </span>
                                            </p>
                                        </div>
                                        @if (isset($historique->donnees_avant['produits']) && count($historique->donnees_avant['produits']) > 0)
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">Produits</label>
                                                <ul class="list-unstyled">
                                                    @foreach ($historique->donnees_avant['produits'] as $produit)
                                                        <li>
                                                            <small>{{ $produit['produit_nom'] }} - Qté:
                                                                {{ $produit['quantite'] }} -
                                                                {{ number_format($produit['prix_unitaire'], 0, ',', ' ') }}
                                                                FCFA</small>
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        @endif
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="card shadow mb-4">
                                <div class="card-header bg-success text-white py-3">
                                    <h6 class="m-0 font-weight-bold">
                                        <i class="fas fa-arrow-right me-2"></i>
                                        Après Modification
                                    </h6>
                                </div>
                                <div class="card-body">
                                    @if ($historique->donnees_apres)
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">Client</label>
                                            <p>{{ $historique->donnees_apres['client_nom'] ?? 'Anonyme' }}</p>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">Total</label>
                                            <p>
                                                <strong>{{ number_format($historique->donnees_apres['total_final'] ?? 0, 0, ',', ' ') }}
                                                    FCFA</strong>
                                            </p>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">Remise</label>
                                            <p>{{ number_format($historique->donnees_apres['remise'] ?? 0, 0, ',', ' ') }}
                                                FCFA</p>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">Mode de paiement</label>
                                            <p>
                                                <span class="badge bg-secondary">
                                                    {{ ucfirst(str_replace('_', ' ', $historique->donnees_apres['mode_paiement'] ?? '-')) }}
                                                </span>
                                            </p>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">Statut</label>
                                            <p>
                                                <span class="badge bg-secondary">
                                                    {{ ucfirst($historique->donnees_apres['statut_paiement'] ?? '-') }}
                                                </span>
                                            </p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Produits modifiés -->
                    @if ($historique->produits_modifies && count($historique->produits_modifies) > 0)
                        <div class="card shadow mb-4">
                            <div class="card-header py-3">
                                <h6 class="m-0 font-weight-bold text-primary">
                                    <i class="fas fa-shopping-cart me-2"></i>
                                    Produits Modifiés
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Produit</th>
                                                <th>Quantité</th>
                                                <th>Prix unitaire</th>
                                                <th>Sous-total</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($historique->produits_modifies as $produit)
                                                <tr>
                                                    <td><strong>{{ $produit['produit_nom'] }}</strong></td>
                                                    <td>
                                                        <span
                                                            class="text-danger">{{ $produit['ancienne_quantite'] }}</span>
                                                        →
                                                        <span
                                                            class="text-success">{{ $produit['nouvelle_quantite'] }}</span>
                                                    </td>
                                                    <td>
                                                        <span class="text-danger">
                                                            {{ number_format($produit['ancien_prix'], 0, ',', ' ') }}
                                                            FCFA</span>
                                                        →
                                                        <span class="text-success">
                                                            {{ number_format($produit['nouveau_prix'], 0, ',', ' ') }}
                                                            FCFA</span>
                                                    </td>
                                                    <td>
                                                        <span class="text-danger">
                                                            {{ number_format($produit['ancien_sous_total'], 0, ',', ' ') }}
                                                            FCFA</span>
                                                        →
                                                        <span class="text-success">
                                                            {{ number_format($produit['nouveau_sous_total'], 0, ',', ' ') }}
                                                            FCFA</span>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    @endif
                @else
                    <!-- Détails de la suppression -->
                    <div class="card shadow mb-4">
                        <div class="card-header bg-danger text-white py-3">
                            <h6 class="m-0 font-weight-bold">
                                <i class="fas fa-trash me-2"></i>
                                Détails de la Vente Supprimée
                            </h6>
                        </div>
                        <div class="card-body">
                            @if ($historique->donnees_avant)
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">Client</label>
                                            <p>{{ $historique->donnees_avant['client_nom'] ?? 'Anonyme' }}</p>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">Total</label>
                                            <p>
                                                <strong>{{ number_format($historique->donnees_avant['total_final'] ?? 0, 0, ',', ' ') }}
                                                    FCFA</strong>
                                            </p>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">Mode de paiement</label>
                                            <p>
                                                <span class="badge bg-secondary">
                                                    {{ ucfirst(str_replace('_', ' ', $historique->donnees_avant['mode_paiement'] ?? '-')) }}
                                                </span>
                                            </p>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">Statut</label>
                                            <p>
                                                <span class="badge bg-secondary">
                                                    {{ ucfirst($historique->donnees_avant['statut_paiement'] ?? '-') }}
                                                </span>
                                            </p>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">Montant payé</label>
                                            <p>{{ number_format($historique->donnees_avant['montant_paye'] ?? 0, 0, ',', ' ') }}
                                                FCFA</p>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">Solde restant</label>
                                            <p>{{ number_format($historique->donnees_avant['solde_restant'] ?? 0, 0, ',', ' ') }}
                                                FCFA</p>
                                        </div>
                                    </div>
                                </div>

                                @if (isset($historique->donnees_avant['produits']) && count($historique->donnees_avant['produits']) > 0)
                                    <hr>
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">produits/articles vendus</label>
                                        <div class="table-responsive">
                                            <table class="table table-sm">
                                                <thead>
                                                    <tr>
                                                        <th>Produit</th>
                                                        <th>Quantité</th>
                                                        <th>Prix unitaire</th>
                                                        <th>Sous-total</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach ($historique->donnees_avant['produits'] as $produit)
                                                        <tr>
                                                            <td>{{ $produit['produit_nom'] }}</td>
                                                            <td>{{ $produit['quantite'] }}</td>
                                                            <td>{{ number_format($produit['prix_unitaire'], 0, ',', ' ') }}
                                                                FCFA</td>
                                                            <td>{{ number_format($produit['sous_total'], 0, ',', ' ') }}
                                                                FCFA</td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                @endif
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
