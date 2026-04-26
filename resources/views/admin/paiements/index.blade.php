@extends('layouts.app')

@section('content')
    <div class="container-fluid px-2 px-md-4 py-3 py-md-4 admin-paiements-page">
        <style>
            .admin-paiements-page .card {
                background: var(--bg-primary);
                border: 1px solid var(--border-light) !important;
            }

            .admin-paiements-page .table {
                --bs-table-color: var(--text-secondary);
                --bs-table-bg: transparent;
                --bs-table-border-color: var(--border-light);
                --bs-table-hover-color: var(--text-primary);
                --bs-table-hover-bg: rgba(var(--primary-color-rgb), 0.12);
            }

            .admin-paiements-page .table> :not(caption)>*>* {
                background: transparent !important;
                color: var(--text-secondary) !important;
                border-color: var(--border-light) !important;
            }

            .admin-paiements-page .table thead th {
                color: var(--text-primary) !important;
                font-weight: 700;
            }

            .admin-paiements-page .table-hover>tbody>tr:hover>* {
                background: rgba(var(--primary-color-rgb), 0.12) !important;
                color: var(--text-primary) !important;
            }

            .admin-paiements-page .text-muted {
                color: var(--text-secondary) !important;
            }

            .admin-paiements-page code {
                color: var(--text-primary);
                background: rgba(var(--primary-color-rgb), 0.12);
                border-radius: 6px;
                padding: 0.1rem 0.35rem;
            }
        </style>

        <div
            class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-3 mb-md-4 gap-2">
            <div>
                <h1 class="h3 mb-1 fw-bold">Gestion des Paiements d'Abonnements</h1>
                <p class="text-muted mb-0 small d-none d-md-block">Confirmer et gérer les paiements des clients</p>
            </div>
            <button type="button" class="btn btn-primary w-100 w-md-auto" data-bs-toggle="modal"
                data-bs-target="#addPaymentModal">
                <i class="fas fa-plus me-2"></i>Ajouter un paiement
            </button>
        </div>

        <!-- Statistiques -->
        <div class="row g-2 g-md-3 mb-3 mb-md-4">
            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-2 p-md-3">
                        <p class="text-muted small mb-1">Total Paiements</p>
                        <h3 class="mb-0 fw-bold h5 h-md-3">{{ number_format($stats['total']) }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-2 p-md-3">
                        <p class="text-muted small mb-1">Confirmés</p>
                        <h3 class="mb-0 fw-bold text-success h5 h-md-3">{{ number_format($stats['confirmes']) }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-2 p-md-3">
                        <p class="text-muted small mb-1">En attente</p>
                        <h3 class="mb-0 fw-bold text-warning h5 h-md-3">{{ number_format($stats['en_attente']) }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-2 p-md-3">
                        <p class="text-muted small mb-1">Revenus totaux</p>
                        <h3 class="mb-0 fw-bold text-primary h6 h-md-3">
                            {{ number_format($stats['revenus_totaux'], 0, ',', ' ') }} FCFA
                        </h3>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filtres -->
        <div class="card border-0 shadow-sm mb-3 mb-md-4">
            <div class="card-body p-2 p-md-3">
                <form method="GET" action="{{ route('admin.paiements.index') }}" class="row g-2 g-md-3">
                    <div class="col-12 col-md-6">
                        <input type="text" name="search" value="{{ request('search') }}" class="form-control"
                            placeholder="Rechercher par client...">
                    </div>
                    <div class="col-12 col-md-3">
                        <select name="statut" class="form-select">
                            <option value="">Tous les statuts</option>
                            <option value="en_attente" @selected(request('statut') === 'en_attente')>En attente</option>
                            <option value="confirme" @selected(request('statut') === 'confirme')>Confirmés</option>
                            <option value="refuse" @selected(request('statut') === 'refuse')>Refusés</option>
                        </select>
                    </div>
                    <div class="col-12 col-md-3">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-search me-2"></i>Filtrer
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Tableau des paiements - Desktop -->
        <div class="card border-0 shadow-sm d-none d-lg-block">
            <div class="card-body p-2 p-md-3">
                <div class="table-responsive">
                    <table class="table table-hover table-sm">
                        <thead>
                            <tr>
                                <th>Client</th>
                                <th>Type</th>
                                <th class="text-end d-none d-xl-table-cell">Montant abo.</th>
                                <th class="text-end">Payé</th>
                                <th class="text-end d-none d-xl-table-cell">Reste</th>
                                <th class="d-none d-xl-table-cell">Début</th>
                                <th class="d-none d-xl-table-cell">Expiration</th>
                                <th>Statut</th>
                                <th class="d-none d-xl-table-cell">Date</th>
                                <th class="d-none d-xl-table-cell">Mode</th>
                                <th class="d-none d-xl-table-cell">Réf.</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($paiements as $paiement)
                                @php
                                    $abonnement = $paiement->abonnement;
                                    $montantPaye = $abonnement->montant_paye;
                                    $montantRestant = $abonnement->montant_restant;
                                @endphp
                                <tr>
                                    <td>
                                        <strong class="d-block">{{ $paiement->user->name }}</strong>
                                        <small class="text-muted d-none d-xl-inline">{{ $paiement->user->email }}</small>
                                    </td>
                                    <td>
                                        <span class="badge bg-info">
                                            {{ $abonnement->type_label }}
                                        </span>
                                    </td>
                                    <td class="text-end d-none d-xl-table-cell">
                                        <strong>{{ number_format($abonnement->montant, 0, ',', ' ') }} FCFA</strong>
                                    </td>
                                    <td class="text-end">
                                        <span class="text-success fw-semibold">
                                            {{ number_format($montantPaye, 0, ',', ' ') }} FCFA
                                        </span>
                                    </td>
                                    <td class="text-end d-none d-xl-table-cell">
                                        @if ($montantRestant > 0.01)
                                            <span class="text-warning fw-semibold">
                                                {{ number_format($montantRestant, 0, ',', ' ') }} FCFA
                                            </span>
                                        @else
                                            <span class="text-success fw-semibold">
                                                <i class="fas fa-check-circle me-1"></i>Payé
                                            </span>
                                        @endif
                                    </td>
                                    <td class="d-none d-xl-table-cell">
                                        <small>{{ $abonnement->date_debut->format('d/m/Y') }}</small>
                                    </td>
                                    <td class="d-none d-xl-table-cell">
                                        <small class="{{ $abonnement->estExpire() ? 'text-danger' : '' }}">
                                            {{ $abonnement->date_expiration->format('d/m/Y') }}
                                        </small>
                                    </td>
                                    <td>
                                        <span
                                            class="badge bg-{{ $paiement->statut === 'confirme' ? 'success' : ($paiement->statut === 'en_attente' ? 'warning' : 'danger') }}">
                                            {{ strtoupper(str_replace('_', ' ', $paiement->statut)) }}
                                        </span>
                                        <br class="d-none d-xl-inline">
                                        <small class="text-muted d-none d-xl-inline">
                                            {{ number_format($paiement->montant, 0, ',', ' ') }} FCFA
                                        </small>
                                    </td>
                                    <td class="d-none d-xl-table-cell">
                                        <small>{{ $paiement->date_paiement->format('d/m/Y') }}</small>
                                    </td>
                                    <td class="d-none d-xl-table-cell">
                                        <small>{{ ucfirst(str_replace('_', ' ', $paiement->mode_paiement)) }}</small>
                                    </td>
                                    <td class="d-none d-xl-table-cell">
                                        <code class="small">{{ $paiement->reference ?? 'N/A' }}</code>
                                    </td>
                                    <td class="text-end">
                                        <div class="btn-group btn-group-sm" role="group">
                                            <a href="{{ route('admin.paiements.show', $paiement) }}" class="btn btn-info"
                                                title="Voir">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('admin.paiements.edit', $paiement) }}"
                                                class="btn btn-primary" title="Modifier">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            @if ($paiement->statut === 'en_attente')
                                                <form method="POST"
                                                    action="{{ route('admin.paiements.confirmer', $paiement) }}"
                                                    class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-success"
                                                        onclick="return confirm('Confirmer ce paiement ?')"
                                                        title="Confirmer">
                                                        <i class="fas fa-check"></i>
                                                    </button>
                                                </form>
                                                <form method="POST"
                                                    action="{{ route('admin.paiements.refuser', $paiement) }}"
                                                    class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-danger"
                                                        onclick="return confirm('Refuser ce paiement ?')" title="Refuser">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="12" class="text-center text-muted py-4">Aucun paiement trouvé</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    {{ $paiements->withQueryString()->links() }}
                </div>
            </div>
        </div>

        <!-- Cards des paiements - Mobile/Tablette -->
        <div class="d-lg-none">
            @forelse($paiements as $paiement)
                @php
                    $abonnement = $paiement->abonnement;
                    $montantPaye = $abonnement->montant_paye;
                    $montantRestant = $abonnement->montant_restant;
                @endphp
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div class="flex-grow-1">
                                <h6 class="fw-bold mb-1">{{ $paiement->user->name }}</h6>
                                <small class="text-muted d-block">{{ $paiement->user->email }}</small>
                            </div>
                            <span
                                class="badge bg-{{ $paiement->statut === 'confirme' ? 'success' : ($paiement->statut === 'en_attente' ? 'warning' : 'danger') }}">
                                {{ strtoupper(str_replace('_', ' ', $paiement->statut)) }}
                            </span>
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <small class="text-muted d-block">Type abonnement</small>
                                <span class="badge bg-info">{{ $abonnement->type_label }}</span>
                            </div>
                            <div class="col-6">
                                <small class="text-muted d-block">Montant payé</small>
                                <strong class="text-success">{{ number_format($montantPaye, 0, ',', ' ') }} FCFA</strong>
                            </div>
                            <div class="col-6">
                                <small class="text-muted d-block">Montant abonnement</small>
                                <strong>{{ number_format($abonnement->montant, 0, ',', ' ') }} FCFA</strong>
                            </div>
                            <div class="col-6">
                                <small class="text-muted d-block">Reste à payer</small>
                                @if ($montantRestant > 0.01)
                                    <strong class="text-warning">{{ number_format($montantRestant, 0, ',', ' ') }}
                                        FCFA</strong>
                                @else
                                    <span class="text-success"><i class="fas fa-check-circle me-1"></i>Payé</span>
                                @endif
                            </div>
                            <div class="col-6">
                                <small class="text-muted d-block">Date paiement</small>
                                <small>{{ $paiement->date_paiement->format('d/m/Y') }}</small>
                            </div>
                            <div class="col-6">
                                <small class="text-muted d-block">Mode</small>
                                <small>{{ ucfirst(str_replace('_', ' ', $paiement->mode_paiement)) }}</small>
                            </div>
                            <div class="col-6">
                                <small class="text-muted d-block">Début</small>
                                <small>{{ $abonnement->date_debut->format('d/m/Y') }}</small>
                            </div>
                            <div class="col-6">
                                <small class="text-muted d-block">Expiration</small>
                                <small class="{{ $abonnement->estExpire() ? 'text-danger' : '' }}">
                                    {{ $abonnement->date_expiration->format('d/m/Y') }}
                                </small>
                            </div>
                            @if ($paiement->reference)
                                <div class="col-12">
                                    <small class="text-muted d-block">Référence</small>
                                    <code class="small">{{ $paiement->reference }}</code>
                                </div>
                            @endif
                            @if ($paiement->statut !== 'en_attente' && $paiement->confirmePar)
                                <div class="col-12">
                                    <small class="text-muted d-block">Confirmé par</small>
                                    <small>{{ $paiement->confirmePar->name }} -
                                        {{ $paiement->confirme_le?->format('d/m/Y H:i') ?? '' }}</small>
                                </div>
                            @endif
                        </div>

                        <div class="d-flex gap-2 flex-wrap">
                            <a href="{{ route('admin.paiements.show', $paiement) }}"
                                class="btn btn-sm btn-info flex-fill">
                                <i class="fas fa-eye me-1"></i>Voir
                            </a>
                            <a href="{{ route('admin.paiements.edit', $paiement) }}"
                                class="btn btn-sm btn-primary flex-fill">
                                <i class="fas fa-edit me-1"></i>Modifier
                            </a>
                            @if ($paiement->statut === 'en_attente')
                                <form method="POST" action="{{ route('admin.paiements.confirmer', $paiement) }}"
                                    class="d-inline flex-fill">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-success w-100"
                                        onclick="return confirm('Confirmer ce paiement ?')">
                                        <i class="fas fa-check me-1"></i>Confirmer
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('admin.paiements.refuser', $paiement) }}"
                                    class="d-inline flex-fill">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-danger w-100"
                                        onclick="return confirm('Refuser ce paiement ?')">
                                        <i class="fas fa-times me-1"></i>Refuser
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center text-muted py-4">
                        Aucun paiement trouvé
                    </div>
                </div>
            @endforelse

            <div class="mt-3">
                {{ $paiements->withQueryString()->links() }}
            </div>
        </div>
    </div>

    <!-- Modal d'ajout de paiement -->
    <div class="modal fade" id="addPaymentModal" tabindex="-1" aria-labelledby="addPaymentModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="addPaymentModalLabel">
                        <i class="fas fa-plus-circle me-2"></i>Ajouter un paiement d'abonnement
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <form id="add-payment-form" method="POST" action="{{ route('admin.paiements.store') }}">
                    @csrf
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-12">
                                <label for="boutique_id" class="form-label fw-semibold">Boutique <span
                                        class="text-danger">*</span></label>
                                <select name="boutique_id" id="boutique_id" class="form-select" required
                                    onchange="handleBoutiqueChange(this.value)">
                                    <option value="">Sélectionner une boutique</option>
                                    @foreach ($boutiques as $boutique)
                                        <option value="{{ $boutique->id }}">
                                            {{ $boutique->nom }}
                                            @if ($boutique->owner)
                                                — {{ $boutique->owner->name }}
                                            @endif
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12">
                                <div id="boutique-summary" class="alert alert-info small mb-0">
                                    <i class="fas fa-info-circle me-2"></i>
                                    Sélectionnez une boutique pour voir les informations de son abonnement.
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="type_abonnement" class="form-label fw-semibold">Type d'abonnement <span
                                        class="text-danger">*</span></label>
                                <select name="type_abonnement" id="type_abonnement" class="form-select" required>
                                    <option value="">Choisir un type</option>
                                    <option value="mensuel">Mensuel</option>
                                    <option value="trimestriel">Trimestriel</option>
                                    <option value="semestriel">Semestriel</option>
                                    <option value="annuel">Annuel</option>
                                    <option value="acquisition_definitive">Acquisition Définitive</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="montant" class="form-label fw-semibold">Montant <span
                                        class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="number" step="0.01" name="montant" id="montant"
                                        class="form-control" placeholder="0" required min="0">
                                    <span class="input-group-text">FCFA</span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="mode_paiement" class="form-label fw-semibold">Mode de paiement <span
                                        class="text-danger">*</span></label>
                                <select name="mode_paiement" id="mode_paiement" class="form-select" required>
                                    <option value="wave">Wave</option>
                                    <option value="orange_money">Orange Money</option>
                                    <option value="mtn_money">MTN Money</option>
                                    <option value="especes">Espèces</option>
                                    <option value="carte_bancaire">Carte bancaire</option>
                                    <option value="virement">Virement</option>
                                    <option value="cheque">Chèque</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="date_paiement" class="form-label fw-semibold">Date de paiement <span
                                        class="text-danger">*</span></label>
                                <input type="date" name="date_paiement" id="date_paiement" class="form-control"
                                    value="{{ now()->format('Y-m-d') }}" required>
                            </div>
                            <div class="col-md-6">
                                <label for="date_echeance_display" class="form-label fw-semibold">Date d'échéance</label>
                                <input type="text" id="date_echeance_display" class="form-control" readonly
                                    placeholder="Calcul automatique">
                                <input type="hidden" name="date_echeance" id="date_echeance">
                                <small class="text-muted">Calculée automatiquement selon le type d'abonnement</small>
                            </div>
                            <div class="col-md-6">
                                <label for="reference" class="form-label fw-semibold">Référence</label>
                                <input type="text" name="reference" id="reference" class="form-control"
                                    placeholder="Auto-générée si vide">
                            </div>
                            <div class="col-12">
                                <label for="notes" class="form-label fw-semibold">Notes</label>
                                <textarea name="notes" id="notes" class="form-control" rows="3"
                                    placeholder="Notes supplémentaires (optionnel)"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times me-2"></i>Annuler
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Enregistrer le paiement
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        const boutiquesData = @json($boutiquesData);
        const typeDurations = {
            mensuel: 1,
            trimestriel: 3,
            semestriel: 6,
            annuel: 12,
        };

        function handleBoutiqueChange(boutiqueId) {
            const summary = document.getElementById('boutique-summary');
            const typeSelect = document.getElementById('type_abonnement');
            const montantInput = document.getElementById('montant');

            typeSelect.value = '';
            montantInput.value = '';

            if (!boutiqueId || !boutiquesData[boutiqueId]) {
                summary.className = 'alert alert-info small mb-0';
                summary.innerHTML =
                    '<i class="fas fa-info-circle me-2"></i>Sélectionnez une boutique pour voir les informations de son abonnement.';
                updateEcheance();
                return;
            }

            const data = boutiquesData[boutiqueId];

            if (!data.owner_id) {
                summary.className = 'alert alert-warning small mb-0';
                summary.innerHTML =
                    '<i class="fas fa-exclamation-triangle me-2"></i>Aucun administrateur associé à cette boutique. Veuillez en assigner un.';
                return;
            }

            let content =
                `<strong>Boutique gérée par :</strong> ${data.owner_name} (${data.owner_email ?? 'Email indisponible'})<br>`;

            if (data.current_type) {
                content +=
                    `<strong>Abonnement actuel :</strong> ${data.current_type} (expire le ${data.current_expiration ?? 'N/A'})`;
                summary.className = 'alert alert-success small mb-0';
                typeSelect.value = data.current_type;
                montantInput.value = data.current_amount ?? '';
            } else {
                content += `<strong>Abonnement actuel :</strong> Aucun (nouvelle souscription)`;
                summary.className = 'alert alert-info small mb-0';
            }

            summary.innerHTML = content;
            updateEcheance();
        }

        function updateEcheance() {
            const type = document.getElementById('type_abonnement').value;
            const datePaiement = document.getElementById('date_paiement').value;
            const displayInput = document.getElementById('date_echeance_display');
            const hiddenInput = document.getElementById('date_echeance');

            if (!type || !datePaiement) {
                displayInput.value = '';
                hiddenInput.value = '';
                return;
            }

            const monthsToAdd = typeDurations[type] || 1;
            const baseDate = new Date(datePaiement + 'T00:00:00');
            baseDate.setMonth(baseDate.getMonth() + monthsToAdd);

            const formatted = baseDate.toISOString().slice(0, 10);
            displayInput.value = new Intl.DateTimeFormat('fr-FR').format(baseDate);
            hiddenInput.value = formatted;
        }

        function generateReference() {
            const randomPart = Math.random().toString(36).substring(2, 7).toUpperCase();
            return `ABO-${new Date().toISOString().slice(0, 10).replace(/-/g, '')}-${randomPart}`;
        }

        document.getElementById('type_abonnement').addEventListener('change', updateEcheance);
        document.getElementById('date_paiement').addEventListener('change', updateEcheance);

        const paymentModal = document.getElementById('addPaymentModal');
        paymentModal.addEventListener('shown.bs.modal', () => {
            const referenceInput = document.getElementById('reference');
            if (!referenceInput.value) {
                referenceInput.value = generateReference();
            }
        });
    </script>
@endsection
