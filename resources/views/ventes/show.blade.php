@extends('layouts.app')

@section('content')

    <div class="row">
        <!-- Informations de la vente -->
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        Informations de la vente
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td class="fw-bold">Numéro de vente :</td>
                                    <td><span class="badge bg-primary">{{ $vente->numero_vente }}</span></td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Date et heure :</td>
                                    <td>{{ $vente->created_at->format('d/m/Y à H:i:s') }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Vendeur :</td>
                                    <td>{{ $vente->user->name }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Boutique :</td>
                                    <td><span class="badge bg-secondary">{{ $vente->boutique->nom }}</span></td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td class="fw-bold">Mode de paiement :</td>
                                    <td>
                                        @php
                                            $paiementsParMode = $vente->paiements->groupBy('mode_paiement');
                                        @endphp

                                        @if ($paiementsParMode->count() > 1)
                                            <span class="badge bg-dark">
                                                <i class="fas fa-random me-1"></i>
                                                Mixte
                                            </span>
                                            <div class="mt-2">
                                                <small class="text-muted d-block">Répartition :</small>
                                                @foreach ($paiementsParMode as $mode => $paiementsMode)
                                                    <small class="d-block">
                                                        {{ ucfirst(str_replace('_', ' ', $mode)) }} :
                                                        {{ number_format($paiementsMode->sum('montant'), 0, ',', ' ') }}
                                                        FCFA
                                                    </small>
                                                @endforeach
                                            </div>
                                        @elseif ($paiementsParMode->count() === 1)
                                            @php
                                                $modeUnique = $paiementsParMode->keys()->first();
                                            @endphp
                                            @switch($modeUnique)
                                                @case('especes')
                                                    <span class="badge bg-success">
                                                        <i class="fas fa-money-bill-wave me-1"></i>
                                                        Espèces
                                                    </span>
                                                @break

                                                @case('mobile_money')
                                                    <span class="badge bg-primary">
                                                        <i class="fas fa-mobile-alt me-1"></i>
                                                        Mobile Money
                                                    </span>
                                                @break

                                                @case('carte')
                                                    <span class="badge bg-info">
                                                        <i class="fas fa-credit-card me-1"></i>
                                                        Carte
                                                    </span>
                                                @break
                                            @endswitch
                                            <small class="d-block text-muted mt-1">
                                                {{ number_format($paiementsParMode[$modeUnique]->sum('montant'), 0, ',', ' ') }}
                                                FCFA
                                            </small>
                                        @else
                                            @switch($vente->mode_paiement)
                                                @case('especes')
                                                    <span class="badge bg-success">
                                                        <i class="fas fa-money-bill-wave me-1"></i>
                                                        Espèces
                                                    </span>
                                                @break

                                                @case('mobile_money')
                                                    <span class="badge bg-primary">
                                                        <i class="fas fa-mobile-alt me-1"></i>
                                                        Mobile Money
                                                    </span>
                                                @break

                                                @case('carte')
                                                    <span class="badge bg-info">
                                                        <i class="fas fa-credit-card me-1"></i>
                                                        Carte
                                                    </span>
                                                @break
                                            @endswitch
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Sous-total :</td>
                                    <td>{{ number_format($vente->total, 0, ',', ' ') }} FCFA</td>
                                </tr>
                                @if ($vente->remise > 0)
                                    <tr>
                                        <td class="fw-bold">Remise :</td>
                                        <td class="text-success">-{{ number_format($vente->remise, 0, ',', ' ') }} FCFA
                                        </td>
                                    </tr>
                                @endif
                                <tr>
                                    <td class="fw-bold">Total final :</td>
                                    <td><strong class="text-success">{{ number_format($vente->total_final, 0, ',', ' ') }}
                                            FCFA</strong></td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    @if ($vente->notes)
                        <div class="mt-3">
                            <h6>Notes :</h6>
                            <div class="alert alert-light">
                                {{ $vente->notes }}
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Produits vendus -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-box me-2"></i>
                        produits/articles vendus ({{ $vente->venteDetails->count() }})
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Produit</th>
                                    <th width="100">Quantité</th>
                                    <th width="120">Prix unitaire</th>
                                    <th width="120">Sous-total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($vente->venteDetails as $detail)
                                    <tr>
                                        <td>
                                            <div>
                                                <strong>{{ $detail->produit->nom }}</strong>
                                                <br>
                                                <small class="text-muted">{{ $detail->produit->categorie }}</small>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-primary">{{ $detail->quantite }}</span>
                                        </td>
                                        <td>{{ number_format($detail->prix_unitaire, 0, ',', ' ') }} FCFA</td>
                                        <td><strong>{{ number_format($detail->sous_total, 0, ',', ' ') }} FCFA</strong>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr class="table-light">
                                    <th colspan="3">Sous-total :</th>
                                    <th>{{ number_format($vente->total, 0, ',', ' ') }} FCFA</th>
                                </tr>
                                @if ($vente->remise > 0)
                                    <tr class="table-light">
                                        <th colspan="3">Remise :</th>
                                        <th class="text-success">-{{ number_format($vente->remise, 0, ',', ' ') }} FCFA
                                        </th>
                                    </tr>
                                @endif
                                <tr class="table-success">
                                    <th colspan="3">Total final :</th>
                                    <th>{{ number_format($vente->total_final, 0, ',', ' ') }} FCFA</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Actions et informations complémentaires -->
        <div class="col-lg-4">
            <!-- Actions rapides -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-tools me-2"></i>
                        Actions
                    </h5>
                </div>
                <div class="card-body">
                    @if ($vente->solde_restant > 0)
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#paiementPartielModal">
                            <i class="fas fa-money-bill-wave me-2"></i>
                            Paiement partiel
                        </button>
                        <div class="alert alert-warning">
                            <small>
                                <i class="fas fa-info-circle me-1"></i>
                                Solde restant : <strong>{{ number_format($vente->solde_restant, 0, ',', ' ') }}
                                    FCFA</strong>
                            </small>
                        </div>
                        <hr>
                    @else
                        <div class="alert alert-success">
                            <small>
                                <i class="fas fa-check-circle me-1"></i>
                                Vente entièrement payée
                            </small>
                        </div>
                        <hr>
                    @endif
                    <div class="d-grid gap-2">
                        <div class="dropdown">
                            <button class="btn btn-warning dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                <i class="fas fa-print me-2"></i>
                                Imprimer la facture
                            </button>
                            <ul class="dropdown-menu">
                                <li>
                                    <h6 class="dropdown-header">Ticket simple</h6>
                                </li>
                                <li>
                                    <a class="dropdown-item"
                                        href="{{ route('ventes.imprimer', ['vente' => $vente, 'width' => 80]) }}"
                                        target="_blank">
                                        <i class="fas fa-receipt me-2"></i>
                                        Ticket 80mm (standard)
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item"
                                        href="{{ route('ventes.imprimer', ['vente' => $vente, 'width' => 58]) }}"
                                        target="_blank">
                                        <i class="fas fa-receipt me-2"></i>
                                        Ticket 58mm (POS)
                                    </a>
                                </li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li>
                                    <a class="dropdown-item"
                                        href="{{ route('ventes.imprimer', ['vente' => $vente, 'type' => 'professionnelle']) }}"
                                        target="_blank">
                                        <i class="fas fa-file-invoice me-2"></i>
                                        Facture professionnelle
                                    </a>
                                </li>
                            </ul>
                        </div>
                        @php
                            $hasFacture = $vente->facture !== null;
                            $hasClient = $vente->client !== null;
                            $hasEmail = $hasClient && !empty($vente->client->email);
                            $canSendEmail = $hasFacture && $hasClient && $hasEmail;
                        @endphp
                        @if ($canSendEmail)
                            <form id="formEnvoyerEmail" action="{{ route('factures.envoyer-email', $vente->facture) }}"
                                method="POST" class="d-grid">
                                @csrf
                                <button type="button" class="btn btn-success"
                                    onclick="confirmerEnvoiEmail('{{ $vente->client->email }}', '{{ $vente->facture->numero_facture }}')">
                                    <i class="fas fa-envelope me-2"></i>
                                    Envoyer la facture par email
                                </button>
                            </form>
                        @else
                            <button type="button" class="btn btn-outline-secondary" disabled
                                title="@if (!$hasFacture) Facture non disponible.@elseif(!$hasClient)Aucun client associé à cette vente.@elseif(!$hasEmail)Le client n'a pas d'email renseigné. @endif">
                                <i class="fas fa-envelope me-2"></i>
                                Envoyer par email
                                @if (!$hasFacture)
                                    <small class="d-block text-muted mt-1">(Facture non disponible)</small>
                                @elseif(!$hasClient)
                                    <small class="d-block text-muted mt-1">(Aucun client)</small>
                                @elseif(!$hasEmail)
                                    <small class="d-block text-muted mt-1">(Email manquant)</small>
                                @endif
                            </button>
                        @endif
                        <a href="{{ route('ventes.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-list me-2"></i>
                            Voir toutes les ventes
                        </a>
                        <a href="{{ route('ventes.pos') }}" class="btn btn-success">
                            <i class="fas fa-cash-register me-2"></i>
                            Nouvelle vente
                        </a>
                        @auth
                            @if (auth()->user()->isAdmin() || (auth()->user()->isOwner() && $vente->boutique_id == auth()->user()->boutique_id))
                                <hr class="my-2">
                                <a href="{{ route('ventes.edit', $vente) }}" class="btn btn-warning w-100">
                                    <i class="fas fa-edit me-1"></i>
                                    Modifier la vente
                                </a>
                                <button type="button" class="btn btn-outline-danger w-100"
                                    onclick="if(confirm('Êtes-vous sûr de vouloir supprimer cette vente ? Le stock des produits sera restauré.')) { document.getElementById('formSuppressionVente').submit(); }">
                                    <i class="fas fa-trash me-1"></i>
                                    Supprimer la vente
                                </button>
                                <form id="formSuppressionVente" action="{{ route('ventes.destroy', $vente) }}"
                                    method="POST" style="display: none;">
                                    @csrf
                                    @method('DELETE')
                                </form>
                            @endif
                        @endauth
                    </div>
                </div>
            </div>

            <!-- Statistiques de la vente -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-chart-bar me-2"></i>
                        Statistiques
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6">
                            <div class="border-end">
                                <h4 class="text-primary">{{ $vente->venteDetails->count() }}</h4>
                                <p class="text-muted mb-0">produits/articles</p>
                            </div>
                        </div>
                        <div class="col-6">
                            <h4 class="text-success">{{ number_format($vente->total_final, 0, ',', ' ') }} FCFA</h4>
                            <p class="text-muted mb-0">Total</p>
                        </div>
                    </div>
                    <hr>
                    <div class="text-center">
                        <h5 class="text-info">
                            @if ($vente->venteDetails->count() > 0)
                                {{ number_format($vente->total_final / $vente->venteDetails->count(), 0, ',', ' ') }} FCFA
                            @else
                                0 FCFA
                            @endif
                        </h5>
                        <p class="text-muted mb-0">Panier moyen</p>
                    </div>
                </div>
            </div>

            <!-- Historique des paiements -->
            @if ($vente->paiements && $vente->paiements->count() > 0)
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-money-bill-wave me-2"></i>
                            Historique des paiements
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Montant</th>
                                        <th>Mode</th>
                                        <th>Notes</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($vente->paiements as $paiement)
                                        <tr>
                                            <td>{{ $paiement->created_at->format('d/m/Y H:i') }}</td>
                                            <td><strong>{{ number_format($paiement->montant, 0, ',', ' ') }} FCFA</strong>
                                            </td>
                                            <td>
                                                @switch($paiement->mode_paiement)
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
                                            <td>{{ $paiement->notes ?? '-' }}</td>
                                            <td>
                                                <form action="{{ route('paiements.destroy', $paiement) }}" method="POST"
                                                    class="d-inline"
                                                    onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce paiement ?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr class="bg-light">
                                        <th>Total payé</th>
                                        <th colspan="3"><strong>{{ number_format($vente->montant_paye, 0, ',', ' ') }}
                                                FCFA</strong></th>
                                        <th></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Informations de traçabilité -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-history me-2"></i>
                        Traçabilité
                    </h5>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        <div class="timeline-item">
                            <div class="timeline-marker bg-success"></div>
                            <div class="timeline-content">
                                <h6 class="timeline-title">Vente enregistrée</h6>
                                <p class="timeline-text">{{ $vente->created_at->format('d/m/Y à H:i:s') }}</p>
                            </div>
                        </div>
                        <div class="timeline-item">
                            <div class="timeline-marker bg-info"></div>
                            <div class="timeline-content">
                                <h6 class="timeline-title">Vendeur</h6>
                                <p class="timeline-text">{{ $vente->user->name }}</p>
                            </div>
                        </div>
                        <div class="timeline-item">
                            <div class="timeline-marker bg-warning"></div>
                            <div class="timeline-content">
                                <h6 class="timeline-title">Boutique</h6>
                                <p class="timeline-text">{{ $vente->boutique->nom }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .timeline {
            position: relative;
            padding-left: 30px;
        }

        .timeline-item {
            position: relative;
            margin-bottom: 20px;
        }

        .timeline-marker {
            position: absolute;
            left: -35px;
            top: 5px;
            width: 10px;
            height: 10px;
            border-radius: 50%;
        }

        .timeline-item:not(:last-child)::before {
            content: '';
            position: absolute;
            left: -30px;
            top: 15px;
            width: 2px;
            height: calc(100% + 10px);
            background-color: #e9ecef;
        }

        .timeline-title {
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 5px;
        }

        .timeline-text {
            font-size: 12px;
            color: #6c757d;
            margin: 0;
        }
    </style>
@endsection

<!-- Modal de paiement partiel -->
<div class="modal fade" id="paiementPartielModal" tabindex="-1" aria-labelledby="paiementPartielModalLabel"
    aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="paiementPartielModalLabel">
                    <i class="fas fa-money-bill-wave me-2"></i>
                    Paiement partiel
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('paiements.store', $vente) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        Total facture : <strong>{{ number_format($vente->total_final, 0, ',', ' ') }} FCFA</strong><br>
                        Montant payé : <strong>{{ number_format($vente->montant_paye, 0, ',', ' ') }} FCFA</strong><br>
                        <span class="text-danger">Solde restant :
                            <strong>{{ number_format($vente->solde_restant, 0, ',', ' ') }} FCFA</strong></span>
                    </div>

                    <div class="mb-3">
                        <label for="montant" class="form-label">Montant du paiement <span
                                class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="number" class="form-control @error('montant') is-invalid @enderror"
                                id="montant" name="montant" step="0.01" min="0.01" required
                                placeholder="Montant à payer">
                            <span class="input-group-text">FCFA</span>
                        </div>
                        @error('montant')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div id="erreur_montant_modal" class="alert alert-danger mt-2 py-2" style="display: none;"
                            role="alert">
                            <small>
                                <i class="fas fa-exclamation-circle me-1"></i>
                                <strong>Erreur :</strong> Le montant du paiement partiel ne peut pas dépasser le solde
                                restant de
                                <strong>{{ number_format($vente->solde_restant, 0, ',', ' ') }} FCFA</strong>. <a
                                    href="#" onclick="document.getElementById('montant').focus(); return false;"
                                    class="alert-link">Modifier le montant</a>
                            </small>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="mode_paiement" class="form-label">Mode de paiement <span
                                class="text-danger">*</span></label>
                        <select class="form-select @error('mode_paiement') is-invalid @enderror" id="mode_paiement"
                            name="mode_paiement" required>
                            <option value="">Choisir...</option>
                            <option value="especes">Espèces</option>
                            <option value="wave">Wave</option>
                            <option value="orange_money">Orange Money</option>
                            <option value="mtn_money">MTN Money</option>
                            <option value="carte">Carte bancaire</option>
                        </select>
                        @error('mode_paiement')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="notes" class="form-label">Notes (optionnel)</label>
                        <textarea class="form-control @error('notes') is-invalid @enderror" id="notes" name="notes" rows="2"
                            placeholder="Notes complémentaires..."></textarea>
                        @error('notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-2"></i>
                        Annuler
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-check me-2"></i>
                        Enregistrer le paiement
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const montantInput = document.getElementById('montant');
        const form = montantInput.closest('form');
        const erreurAlert = document.getElementById('erreur_montant_modal');
        const soldeRestant = {{ $vente->solde_restant }};

        form.addEventListener('submit', function(e) {
            const montant = parseFloat(montantInput.value);

            if (montant > soldeRestant) {
                e.preventDefault();
                // Afficher l'alerte d'erreur
                erreurAlert.style.display = 'block';
                // Faire défiler jusqu'à l'erreur
                erreurAlert.scrollIntoView({
                    behavior: 'smooth',
                    block: 'nearest'
                });
                // Focus sur le champ
                montantInput.focus();
                return false;
            }

            // Cacher l'alerte d'erreur si elle était visible
            erreurAlert.style.display = 'none';
        });

        // Cacher l'alerte d'erreur quand l'utilisateur modifie le montant
        montantInput.addEventListener('input', function() {
            erreurAlert.style.display = 'none';
        });
    });

    function confirmerEnvoiEmail(email, numeroFacture) {
        document.getElementById('emailDestinataire').textContent = email;
        document.getElementById('numeroFactureEmail').textContent = numeroFacture;

        // Définir l'action du bouton de confirmation
        const btnConfirmer = document.getElementById('btnConfirmerEnvoiVente');
        if (btnConfirmer) {
            btnConfirmer.onclick = function() {
                document.getElementById('formEnvoyerEmail').submit();
            };
        }

        new bootstrap.Modal(document.getElementById('modalEnvoiEmail')).show();
    }
</script>

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
                <button type="button" class="btn btn-success" id="btnConfirmerEnvoiVente">
                    <i class="fas fa-paper-plane me-1"></i>
                    Confirmer l'envoi
                </button>
            </div>
        </div>
    </div>
</div>
