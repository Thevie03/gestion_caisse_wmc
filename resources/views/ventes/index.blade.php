@extends('layouts.app')

@section('content')

    <!-- Filtres -->
    <div class="card saas-surface-card mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('ventes.index') }}" class="row align-items-end">
                <div class="col-12 col-lg-3">
                    <label for="recherche" class="form-label">Rechercher</label>
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="fas fa-search"></i>
                        </span>
                        <input type="text" class="form-control" id="recherche" name="recherche"
                            placeholder="N° vente, client, vendeur" value="{{ request('recherche') }}">
                    </div>
                </div>
                <div class="col-12 col-lg-2">
                    <label for="date_debut" class="form-label">Date de début</label>
                    <input type="date" class="form-control" id="date_debut" name="date_debut"
                        value="{{ request('date_debut') }}">
                </div>

                <div class="col-12 col-lg-2">
                    <label for="date_fin" class="form-label">Date de fin</label>
                    <input type="date" class="form-control" id="date_fin" name="date_fin"
                        value="{{ request('date_fin') }}">
                </div>

                <div class="col-12 col-lg-3">
                    <label for="mode_paiement" class="form-label">Mode de paiement</label>
                    <select class="form-select" id="mode_paiement" name="mode_paiement">
                        <option value="">Tous les modes</option>
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

                <div class="col-12 col-lg-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="fas fa-search"></i>
                    </button>
                    <a href="{{ route('ventes.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-times"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Liste des ventes -->
    <div class="card saas-surface-card">
        <div class="card-header">
            <h5 class="card-title mb-0">
                <i class="fas fa-list me-2"></i>
                Liste des ventes ({{ $ventes->total() }})
            </h5>
        </div>
        <div class="card-body p-0">
            @if ($ventes->count() > 0)
                <!-- Optimisation mobile : tableau responsive avec cards sur petit écran -->
                <div class="table-responsive d-none d-md-block">
                    <table class="table table-hover mb-0 saas-table">
                        <thead>
                            <tr>
                                <th>N° Vente</th>
                                <th>Date/Heure</th>
                                <th>Client</th>
                                <th>Vendeur</th>
                                <th>produits/articles</th>
                                <th>Total</th>
                                <th>Mode paiement</th>
                                <th>Statut paiement</th>
                                <th>Boutique</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($ventes as $vente)
                                <tr>
                                    <td>
                                        <strong>{{ $vente->numero_vente }}</strong>
                                    </td>
                                    <td>
                                        <div>
                                            <strong>{{ $vente->created_at->format('d/m/Y') }}</strong>
                                            <br>
                                            <small class="text-muted">{{ $vente->created_at->format('H:i:s') }}</small>
                                        </div>
                                    </td>
                                    <td>
                                        @if ($vente->client)
                                            <div>
                                                <strong>{{ $vente->client->nom_complet }}</strong>
                                                <br>
                                                <small class="text-muted">{{ $vente->client->email }}</small>
                                            </div>
                                        @else
                                            <span class="text-muted">Anonyme</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div>
                                            <strong>{{ $vente->user->name }}</strong>
                                            <br>
                                            <small class="text-muted">{{ $vente->user->email }}</small>
                                        </div>
                                    </td>
                                    <td>
                                        <div>
                                            <strong>{{ $vente->venteDetails->count() }} produit(s)</strong>
                                            <br>
                                            <small class="text-muted">
                                                @foreach ($vente->venteDetails->take(2) as $detail)
                                                    {{ $detail->produit->nom }}{{ $detail->quantite > 1 ? ' (x' . $detail->quantite . ')' : '' }}{{ !$loop->last ? ', ' : '' }}
                                                @endforeach
                                                @if ($vente->venteDetails->count() > 2)
                                                    ...
                                                @endif
                                            </small>
                                        </div>
                                    </td>
                                    <td>
                                        <div>
                                            <strong>{{ number_format($vente->total_final, 0, ',', ' ') }} FCFA</strong>
                                            @if ($vente->remise > 0)
                                                <br>
                                                <small class="text-success">Remise:
                                                    {{ number_format($vente->remise, 0, ',', ' ') }} FCFA</small>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        @php
                                            $paiementsGroupe = $vente->paiements->groupBy('mode_paiement');
                                            $modeAffiche = $paiementsGroupe->keys()->first() ?? $vente->mode_paiement;
                                        @endphp

                                        @if ($paiementsGroupe->count() > 1)
                                            <span class="badge bg-dark">
                                                <i class="fas fa-random me-1"></i>
                                                Mixte
                                            </span>
                                            <br>
                                            <small class="text-muted">
                                                @foreach ($paiementsGroupe as $mode => $paiementsMode)
                                                    {{ ucfirst(str_replace('_', ' ', $mode)) }} :
                                                    {{ number_format($paiementsMode->sum('montant'), 0, ',', ' ') }} FCFA
                                                    @if (!$loop->last)
                                                        <br>
                                                    @endif
                                                @endforeach
                                            </small>
                                        @else
                                            @switch($modeAffiche)
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
                                    <td>
                                        @php
                                            // Initialiser les soldes si nécessaire
                                            if ($vente->solde_restant === null) {
                                                $vente->solde_restant =
                                                    $vente->total_final - ($vente->montant_paye ?? 0);
                                                $vente->statut_paiement =
                                                    $vente->solde_restant > 0 ? 'partiel' : 'complet';
                                            }
                                        @endphp

                                        @if ($vente->statut_paiement == 'partiel' && $vente->solde_restant > 0)
                                            <span class="badge bg-warning"
                                                title="Solde restant: {{ number_format($vente->solde_restant, 0, ',', ' ') }} FCFA">
                                                <i class="fas fa-clock me-1"></i>
                                                Partiel
                                            </span>
                                            <br>
                                            <small class="text-muted">
                                                <i class="fas fa-info-circle"></i>
                                                {{ number_format($vente->solde_restant, 0, ',', ' ') }} FCFA
                                            </small>
                                        @elseif($vente->statut_paiement == 'impaye')
                                            <span class="badge bg-danger">
                                                <i class="fas fa-times-circle me-1"></i>
                                                Impayé
                                            </span>
                                        @else
                                            <span class="badge bg-success">
                                                <i class="fas fa-check-circle me-1"></i>
                                                Soldé
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary">{{ $vente->boutique->nom }}</span>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('ventes.show', $vente) }}"
                                                class="btn btn-sm btn-outline-info" title="Voir">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <div class="dropdown">
                                                <button class="btn btn-sm btn-outline-warning dropdown-toggle"
                                                    type="button" data-bs-toggle="dropdown" title="Imprimer">
                                                    <i class="fas fa-print"></i>
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
                                            @if ($vente->facture && $vente->client && $vente->client->email)
                                                <form id="formEnvoyerEmail{{ $vente->id }}"
                                                    action="{{ route('factures.envoyer-email', $vente->facture) }}"
                                                    method="POST" style="display: inline;">
                                                    @csrf
                                                    <button type="button" class="btn btn-sm btn-success"
                                                        onclick="confirmerEnvoiEmail('{{ $vente->client->email }}', '{{ $vente->facture->numero_facture }}', '{{ $vente->id }}')"
                                                        title="Envoyer la facture par email">
                                                        <i class="fas fa-envelope"></i>
                                                    </button>
                                                </form>
                                            @elseif($vente->facture && (!$vente->client || !$vente->client->email))
                                                <button type="button" class="btn btn-sm btn-outline-secondary" disabled
                                                    title="{{ !$vente->client ? 'Aucun client associé' : 'Le client n\'a pas d\'email renseigné' }}">
                                                    <i class="fas fa-envelope"></i>
                                                </button>
                                            @endif
                                            @auth
                                                @if (auth()->user()->isAdmin() || (auth()->user()->isOwner() && $vente->boutique_id == auth()->user()->boutique_id))
                                                    <a href="{{ route('ventes.edit', $vente) }}"
                                                        class="btn btn-sm btn-warning" title="Modifier la vente">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <button type="button" class="btn btn-sm btn-outline-danger"
                                                        onclick="confirmerSuppression('{{ $vente->numero_vente }}', '{{ route('ventes.destroy', $vente) }}')"
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

                <!-- Optimisation mobile : vue en cards pour petits écrans -->
                <div class="d-md-none p-3">
                    @foreach ($ventes as $vente)
                        <div class="card saas-surface-card mb-3 shadow-sm">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <h6 class="mb-1 fw-bold">{{ $vente->numero_vente }}</h6>
                                        <small class="text-muted">{{ $vente->created_at->format('d/m/Y H:i') }}</small>
                                    </div>
                                    <strong class="text-success">{{ number_format($vente->total_final, 0, ',', ' ') }}
                                        FCFA</strong>
                                </div>

                                @if ($vente->client)
                                    <p class="mb-1"><small><strong>Client:</strong>
                                            {{ $vente->client->nom_complet }}</small></p>
                                @endif

                                <p class="mb-1"><small><strong>produits/articles:</strong>
                                        {{ $vente->venteDetails->count() }}</small></p>

                                <div class="d-flex gap-2 mb-2">
                                    @php
                                        $paiementsGroupeMobile = $vente->paiements->groupBy('mode_paiement');
                                        $modeMobile = $paiementsGroupeMobile->keys()->first() ?? $vente->mode_paiement;
                                    @endphp

                                    @if ($paiementsGroupeMobile->count() > 1)
                                        <span class="badge bg-dark">Mixte</span>
                                    @else
                                        @switch($modeMobile)
                                            @case('especes')
                                                <span class="badge bg-success">Espèces</span>
                                            @break

                                            @case('mobile_money')
                                                <span class="badge bg-primary">Mobile Money</span>
                                            @break

                                            @case('carte')
                                                <span class="badge bg-info">Carte</span>
                                            @break
                                        @endswitch
                                    @endif

                                    @if ($vente->statut_paiement == 'complet')
                                        <span class="badge bg-success">Soldé</span>
                                    @elseif ($vente->statut_paiement == 'partiel')
                                        <span class="badge bg-warning">Partiel</span>
                                    @else
                                        <span class="badge bg-danger">Impayé</span>
                                    @endif
                                </div>

                                <div class="btn-group w-100" role="group">
                                    <a href="{{ route('ventes.show', $vente) }}" class="btn btn-sm btn-outline-info">
                                        <i class="fas fa-eye"></i> Voir
                                    </a>
                                    @if ($vente->facture && $vente->client && $vente->client->email)
                                        <form id="formEnvoyerEmailMobile{{ $vente->id }}"
                                            action="{{ route('factures.envoyer-email', $vente->facture) }}"
                                            method="POST" style="display: inline;">
                                            @csrf
                                            <button type="button" class="btn btn-sm btn-success"
                                                onclick="confirmerEnvoiEmail('{{ $vente->client->email }}', '{{ $vente->facture->numero_facture }}', '{{ $vente->id }}', true)"
                                                title="Envoyer la facture par email">
                                                <i class="fas fa-envelope"></i>
                                            </button>
                                        </form>
                                    @elseif($vente->facture && (!$vente->client || !$vente->client->email))
                                        <button type="button" class="btn btn-sm btn-outline-secondary" disabled
                                            title="{{ !$vente->client ? 'Aucun client associé' : 'Le client n\'a pas d\'email renseigné' }}">
                                            <i class="fas fa-envelope"></i>
                                        </button>
                                    @endif
                                    @auth
                                        @if (auth()->user()->isAdmin() || (auth()->user()->isOwner() && $vente->boutique_id == auth()->user()->boutique_id))
                                            <a href="{{ route('ventes.edit', $vente) }}" class="btn btn-sm btn-warning">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button type="button" class="btn btn-sm btn-outline-danger"
                                                onclick="confirmerSuppression('{{ $vente->numero_vente }}', '{{ route('ventes.destroy', $vente) }}')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        @endif
                                    @endauth
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Pagination -->
                <div class="card-footer">
                    {{ $ventes->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-shopping-cart fa-3x text-muted mb-3"></i>
                    <h4>Aucune vente trouvée</h4>
                    <p class="text-muted">Commencez par effectuer votre première vente.</p>
                    <a href="{{ route('ventes.pos') }}" class="btn btn-success">
                        <i class="fas fa-cash-register me-2"></i>
                        Nouvelle vente
                    </a>
                </div>
            @endif
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
                    <p>Êtes-vous sûr de vouloir supprimer définitivement la vente <strong id="nomFacture"></strong> ?</p>
                    <p class="text-danger">
                        <i class="fas fa-exclamation-circle me-1"></i>
                        Cette action est irréversible ! Le stock des produits sera restauré et la facture associée sera
                        supprimée.
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

    <script>
        function confirmerSuppression(nomFacture, url) {
            document.getElementById('nomFacture').textContent = nomFacture;
            document.getElementById('formSuppression').action = url;
            new bootstrap.Modal(document.getElementById('modalSuppression')).show();
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
                    <button type="button" class="btn btn-success" id="btnConfirmerEnvoi">
                        <i class="fas fa-paper-plane me-1"></i>
                        Confirmer l'envoi
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        function confirmerEnvoiEmail(email, numeroFacture, venteId, isMobile = false) {
            document.getElementById('emailDestinataire').textContent = email;
            document.getElementById('numeroFactureEmail').textContent = numeroFacture;

            // Définir l'action du bouton de confirmation
            const btnConfirmer = document.getElementById('btnConfirmerEnvoi');
            const formId = isMobile ? 'formEnvoyerEmailMobile' + venteId : 'formEnvoyerEmail' + venteId;
            btnConfirmer.onclick = function() {
                document.getElementById(formId).submit();
            };

            new bootstrap.Modal(document.getElementById('modalEnvoiEmail')).show();
        }
    </script>
@endsection
