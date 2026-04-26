@extends('layouts.app')

@section('title', 'Détails de la Facture')

@section('content')
    <div class="container-fluid">
        <!-- En-tête -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h1 class="h3 mb-0 text-gray-800">
                            <i class="fas fa-file-invoice me-2"></i>
                            Facture {{ $facture->numero_facture }}
                        </h1>
                        <p class="text-muted mb-0">Détails de la facture</p>
                    </div>
                    <div>
                        <a href="{{ route('factures.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-1"></i>
                            Retour
                        </a>
                        <a href="{{ route('factures.afficher', $facture) }}" class="btn btn-info" target="_blank">
                            <i class="fas fa-file-pdf me-1"></i>
                            Afficher PDF
                        </a>
                        <a href="{{ route('factures.telecharger', $facture) }}" class="btn btn-success">
                            <i class="fas fa-download me-1"></i>
                            Télécharger PDF
                        </a>
                        @auth
                            @if (auth()->user()->isAdmin())
                                <a href="{{ route('factures.edit', $facture) }}" class="btn btn-warning">
                                    <i class="fas fa-edit me-1"></i>
                                    Modifier
                                </a>
                                <button type="button" class="btn btn-outline-danger"
                                    onclick="confirmerSuppression('{{ $facture->numero_facture }}', '{{ route('factures.destroy', $facture) }}')">
                                    <i class="fas fa-trash me-1"></i>
                                    Supprimer
                                </button>
                            @endif
                        @endauth
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Informations de la facture -->
            <div class="col-lg-8">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-info-circle me-2"></i>
                            Informations de la Facture
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Numéro de facture</label>
                                    <p class="form-control-plaintext">
                                        <i class="fas fa-hashtag me-2"></i>
                                        {{ $facture->numero_facture }}
                                    </p>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-bold">Date de vente</label>
                                    <p class="form-control-plaintext">
                                        <i class="fas fa-calendar me-2"></i>
                                        {{ $facture->created_at->format('d/m/Y à H:i') }}
                                    </p>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-bold">Boutique</label>
                                    <p class="form-control-plaintext">
                                        <i class="fas fa-store me-2"></i>
                                        {{ $facture->boutique->nom }}
                                    </p>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Vendeur</label>
                                    <p class="form-control-plaintext">
                                        <i class="fas fa-user me-2"></i>
                                        {{ $facture->user->name }}
                                    </p>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-bold">Mode de paiement</label>
                                    <p class="form-control-plaintext">
                                        @php
                                            $iconClass = match ($facture->mode_paiement) {
                                                'especes' => 'fas fa-money-bill',
                                                'mobile_money' => 'fas fa-mobile-alt',
                                                'carte' => 'fas fa-credit-card',
                                                default => 'fas fa-question',
                                            };
                                        @endphp
                                        <i class="{{ $iconClass }} me-2"></i>
                                        {{ ucfirst(str_replace('_', ' ', $facture->mode_paiement)) }}
                                    </p>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-bold">Total</label>
                                    <p class="form-control-plaintext">
                                        <i class="fas fa-dollar-sign me-2"></i>
                                        <strong class="text-success fs-5">
                                            {{ number_format($facture->total, 0, ',', ' ') }} FCFA
                                        </strong>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Détails des produits -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-shopping-cart me-2"></i>
                            Produits Vendus ({{ $facture->details->count() }})
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead class="table-light">
                                    <tr>
                                        <th>Produit</th>
                                        <th>Catégorie</th>
                                        <th>Quantité</th>
                                        <th>Prix unitaire</th>
                                        <th>Remise</th>
                                        <th>Sous-total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($facture->details as $detail)
                                        <tr>
                                            <td>
                                                <div>
                                                    <strong>{{ $detail->produit->nom }}</strong>
                                                    @if ($detail->produit->description)
                                                        <br>
                                                        <small
                                                            class="text-muted">{{ $detail->produit->description }}</small>
                                                    @endif
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-secondary">{{ $detail->produit->categorie }}</span>
                                            </td>
                                            <td>
                                                <span class="badge bg-primary">{{ $detail->quantite }}</span>
                                            </td>
                                            <td>
                                                {{ number_format($detail->prix_unitaire, 0, ',', ' ') }} FCFA
                                            </td>
                                            <td>
                                                @if ($detail->remise > 0)
                                                    <span class="text-warning">
                                                        -{{ number_format($detail->remise, 0, ',', ' ') }} FCFA
                                                    </span>
                                                @else
                                                    <span class="text-muted">Aucune</span>
                                                @endif
                                            </td>
                                            <td>
                                                <strong class="text-success">
                                                    {{ number_format($detail->sous_total, 0, ',', ' ') }} FCFA
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

            <!-- Informations client et résumé -->
            <div class="col-lg-4">
                <!-- Informations client -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-user me-2"></i>
                            Informations Client
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Nom du client</label>
                            <p class="form-control-plaintext">
                                <i class="fas fa-user me-2"></i>
                                {{ $facture->nom_client ?? 'Client anonyme' }}
                            </p>
                        </div>

                        @if ($facture->telephone_client)
                            <div class="mb-3">
                                <label class="form-label fw-bold">Téléphone</label>
                                <p class="form-control-plaintext">
                                    <i class="fas fa-phone me-2"></i>
                                    {{ $facture->telephone_client }}
                                </p>
                            </div>
                        @endif

                        @if ($facture->email_client)
                            <div class="mb-3">
                                <label class="form-label fw-bold">Email</label>
                                <p class="form-control-plaintext">
                                    <i class="fas fa-envelope me-2"></i>
                                    {{ $facture->email_client }}
                                </p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Résumé financier -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-calculator me-2"></i>
                            Résumé Financier
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Sous-total :</span>
                            <strong>{{ number_format($facture->details->sum('sous_total'), 0, ',', ' ') }} FCFA</strong>
                        </div>

                        @if ($facture->remise_globale > 0)
                            <div class="d-flex justify-content-between mb-2 text-warning">
                                <span>Remise globale :</span>
                                <strong>-{{ number_format($facture->remise_globale, 0, ',', ' ') }} FCFA</strong>
                            </div>
                        @endif

                        <hr>

                        <div class="d-flex justify-content-between mb-3">
                            <span class="fw-bold">Total :</span>
                            <strong class="text-success fs-5">
                                {{ number_format($facture->total, 0, ',', ' ') }} FCFA
                            </strong>
                        </div>

                        <div class="d-flex justify-content-between">
                            <span>Nombre d'articles :</span>
                            <strong>{{ $facture->details->sum('quantite') }}</strong>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="card shadow">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-cogs me-2"></i>
                            Actions
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="{{ route('factures.afficher', $facture) }}" class="btn btn-info" target="_blank">
                                <i class="fas fa-file-pdf me-1"></i>
                                Afficher PDF
                            </a>
                            <a href="{{ route('factures.telecharger', $facture) }}" class="btn btn-success">
                                <i class="fas fa-download me-1"></i>
                                Télécharger PDF
                            </a>
                            @if ($facture->email_client)
                                <form id="formEnvoyerEmail" action="{{ route('factures.envoyer-email', $facture) }}"
                                    method="POST" class="d-grid">
                                    @csrf
                                    <button type="button" class="btn btn-success"
                                        onclick="confirmerEnvoiEmail('{{ $facture->email_client }}', '{{ $facture->numero_facture }}')">
                                        <i class="fas fa-envelope me-1"></i>
                                        Envoyer par email
                                    </button>
                                </form>
                            @else
                                <button type="button" class="btn btn-outline-secondary" disabled
                                    title="Le client n'a pas d'email renseigné">
                                    <i class="fas fa-envelope me-1"></i>
                                    Email non disponible
                                </button>
                            @endif
                            <button class="btn btn-outline-primary" onclick="window.print()">
                                <i class="fas fa-print me-1"></i>
                                Imprimer
                            </button>
                            @auth
                                @if (auth()->user()->isAdmin())
                                    <hr class="my-2">
                                    <a href="{{ route('factures.edit', $facture) }}" class="btn btn-warning w-100">
                                        <i class="fas fa-edit me-1"></i>
                                        Modifier la facture
                                    </a>
                                    <button type="button" class="btn btn-outline-danger w-100"
                                        onclick="confirmerSuppression('{{ $facture->numero_facture }}', '{{ route('factures.destroy', $facture) }}')">
                                        <i class="fas fa-trash me-1"></i>
                                        Supprimer la facture
                                    </button>
                                @endif
                            @endauth
                        </div>
                    </div>
                </div>
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
                        Cette action est irréversible !
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

    <!-- Modal de confirmation d'envoi d'email -->
    <div class="modal fade" id="modalEnvoiEmail" tabindex="-1" aria-labelledby="modalEnvoiEmailLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title" id="modalEnvoiEmailLabel">
                        <i class="fas fa-envelope me-2"></i>
                        Confirmation d'envoi
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="text-center mb-3">
                        <i class="fas fa-paper-plane fa-3x text-success mb-3"></i>
                    </div>
                    <p class="text-center mb-2">
                        <strong>Envoyer la facture <span id="numeroFactureEmail" class="text-primary"></span> par email
                            ?</strong>
                    </p>
                    <div class="alert alert-info mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Destinataire :</strong> <span id="emailDestinataire" class="fw-bold"></span>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>
                        Annuler
                    </button>
                    <button type="button" class="btn btn-success" id="btnConfirmerEnvoiFacture">
                        <i class="fas fa-paper-plane me-1"></i>
                        Confirmer l'envoi
                    </button>
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

        function confirmerEnvoiEmail(email, numeroFacture) {
            document.getElementById('emailDestinataire').textContent = email;
            document.getElementById('numeroFactureEmail').textContent = numeroFacture;

            // Définir l'action du bouton de confirmation
            const btnConfirmer = document.getElementById('btnConfirmerEnvoiFacture');
            if (btnConfirmer) {
                btnConfirmer.onclick = function() {
                    document.getElementById('formEnvoyerEmail').submit();
                };
            }

            new bootstrap.Modal(document.getElementById('modalEnvoiEmail')).show();
        }
    </script>
@endsection
