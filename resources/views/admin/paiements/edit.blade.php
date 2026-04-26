@extends('layouts.app')

@section('content')
    <div class="container-fluid px-4 py-4">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <nav aria-label="breadcrumb" class="mb-2">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('admin.paiements.index') }}"
                                class="text-decoration-none">Paiements</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.paiements.show', $paiement) }}"
                                class="text-decoration-none">Détails</a></li>
                        <li class="breadcrumb-item active">Modifier</li>
                    </ol>
                </nav>
                <h1 class="h3 mb-1 fw-bold">Modifier le paiement</h1>
                <p class="text-muted mb-0">Référence: {{ $paiement->reference ?? 'N/A' }}</p>
            </div>
            <a href="{{ route('admin.paiements.show', $paiement) }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i>Retour
            </a>
        </div>

        <form method="POST" action="{{ route('admin.paiements.update', $paiement) }}">
            @csrf
            @method('PUT')

            <div class="row g-4">
                <!-- Section Informations du paiement -->
                <div class="col-lg-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-white border-bottom py-3">
                            <div class="d-flex align-items-center">
                                <div class="bg-primary bg-opacity-10 rounded-circle p-2 me-3">
                                    <i class="fas fa-money-bill-wave text-primary"></i>
                                </div>
                                <div>
                                    <h5 class="mb-0 fw-semibold">Informations du paiement</h5>
                                    <small class="text-muted">Modifier les détails du paiement</small>
                                </div>
                            </div>
                        </div>
                        <div class="card-body p-4">
                            <div class="mb-3">
                                <label for="montant" class="form-label fw-semibold">
                                    <i class="fas fa-coins me-2 text-primary"></i>Montant <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <input type="number" step="0.01" name="montant" id="montant"
                                        class="form-control form-control-lg @error('montant') is-invalid @enderror"
                                        value="{{ old('montant', $paiement->montant) }}" required min="0">
                                    <span class="input-group-text">FCFA</span>
                                </div>
                                @error('montant')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="mode_paiement" class="form-label fw-semibold">
                                    <i class="fas fa-credit-card me-2 text-primary"></i>Mode de paiement <span
                                        class="text-danger">*</span>
                                </label>
                                <select name="mode_paiement" id="mode_paiement"
                                    class="form-select form-select-lg @error('mode_paiement') is-invalid @enderror"
                                    required>
                                    <option value="especes"
                                        {{ old('mode_paiement', $paiement->mode_paiement) === 'especes' ? 'selected' : '' }}>
                                        Espèces
                                    </option>
                                    <option value="wave"
                                        {{ old('mode_paiement', $paiement->mode_paiement) === 'wave' ? 'selected' : '' }}>
                                        Wave
                                    </option>
                                    <option value="orange_money"
                                        {{ old('mode_paiement', $paiement->mode_paiement) === 'orange_money' ? 'selected' : '' }}>
                                        Orange Money
                                    </option>
                                    <option value="mtn_money"
                                        {{ old('mode_paiement', $paiement->mode_paiement) === 'mtn_money' ? 'selected' : '' }}>
                                        MTN Money
                                    </option>
                                    <option value="carte_bancaire"
                                        {{ old('mode_paiement', $paiement->mode_paiement) === 'carte_bancaire' ? 'selected' : '' }}>
                                        Carte bancaire
                                    </option>
                                    <option value="virement"
                                        {{ old('mode_paiement', $paiement->mode_paiement) === 'virement' ? 'selected' : '' }}>
                                        Virement
                                    </option>
                                    <option value="cheque"
                                        {{ old('mode_paiement', $paiement->mode_paiement) === 'cheque' ? 'selected' : '' }}>
                                        Chèque
                                    </option>
                                </select>
                                @error('mode_paiement')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="date_paiement" class="form-label fw-semibold">
                                    <i class="fas fa-calendar me-2 text-primary"></i>Date de paiement <span
                                        class="text-danger">*</span>
                                </label>
                                <input type="date" name="date_paiement" id="date_paiement"
                                    class="form-control form-control-lg @error('date_paiement') is-invalid @enderror"
                                    value="{{ old('date_paiement', $paiement->date_paiement->format('Y-m-d')) }}" required>
                                @error('date_paiement')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="reference" class="form-label fw-semibold">
                                    <i class="fas fa-hashtag me-2 text-primary"></i>Référence
                                </label>
                                <input type="text" name="reference" id="reference"
                                    class="form-control form-control-lg @error('reference') is-invalid @enderror"
                                    value="{{ old('reference', $paiement->reference) }}"
                                    placeholder="Référence du paiement">
                                @error('reference')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-0">
                                <label for="notes" class="form-label fw-semibold">
                                    <i class="fas fa-sticky-note me-2 text-primary"></i>Notes
                                </label>
                                <textarea name="notes" id="notes" rows="3" class="form-control @error('notes') is-invalid @enderror"
                                    placeholder="Notes supplémentaires (optionnel)">{{ old('notes', $paiement->notes) }}</textarea>
                                @error('notes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section Prolongation de l'abonnement -->
                <div class="col-lg-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-white border-bottom py-3">
                            <div class="d-flex align-items-center">
                                <div class="bg-success bg-opacity-10 rounded-circle p-2 me-3">
                                    <i class="fas fa-calendar-plus text-success"></i>
                                </div>
                                <div>
                                    <h5 class="mb-0 fw-semibold">Prolonger l'abonnement</h5>
                                    <small class="text-muted">Optionnel - Prolonger l'abonnement du client</small>
                                </div>
                            </div>
                        </div>
                        <div class="card-body p-4">
                            <div class="mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="prolonger_abonnement"
                                        id="prolonger_abonnement" value="1"
                                        {{ old('prolonger_abonnement') ? 'checked' : '' }}
                                        onchange="toggleProlongationFields()">
                                    <label class="form-check-label fw-semibold" for="prolonger_abonnement">
                                        Prolonger l'abonnement du client
                                    </label>
                                </div>
                                <small class="text-muted">Cochez cette case si vous souhaitez prolonger l'abonnement en
                                    même temps que la modification du paiement.</small>
                            </div>

                            <div id="prolongation-fields" style="display: none;">
                                <div class="mb-3">
                                    <label for="type_abonnement" class="form-label fw-semibold">
                                        <i class="fas fa-calendar-alt me-2 text-success"></i>Type d'abonnement <span
                                            class="text-danger">*</span>
                                    </label>
                                    <select name="type_abonnement" id="type_abonnement"
                                        class="form-select form-select-lg @error('type_abonnement') is-invalid @enderror">
                                        <option value="">Choisir un type</option>
                                        <option value="mensuel"
                                            {{ old('type_abonnement', $abonnement->type_abonnement) === 'mensuel' ? 'selected' : '' }}>
                                            Mensuel
                                        </option>
                                        <option value="trimestriel"
                                            {{ old('type_abonnement', $abonnement->type_abonnement) === 'trimestriel' ? 'selected' : '' }}>
                                            Trimestriel
                                        </option>
                                        <option value="semestriel"
                                            {{ old('type_abonnement', $abonnement->type_abonnement) === 'semestriel' ? 'selected' : '' }}>
                                            Semestriel
                                        </option>
                                        <option value="annuel"
                                            {{ old('type_abonnement', $abonnement->type_abonnement) === 'annuel' ? 'selected' : '' }}>
                                            Annuel
                                        </option>
                                        <option value="acquisition_definitive"
                                            {{ old('type_abonnement', $abonnement->type_abonnement) === 'acquisition_definitive' ? 'selected' : '' }}>
                                            Acquisition Définitive
                                        </option>
                                    </select>
                                    @error('type_abonnement')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="date_echeance_display" class="form-label fw-semibold">
                                        <i class="fas fa-calendar-check me-2 text-success"></i>Date d'échéance
                                    </label>
                                    <input type="text" id="date_echeance_display" class="form-control form-control-lg"
                                        readonly placeholder="Calcul automatique">
                                    <input type="hidden" name="date_echeance" id="date_echeance">
                                    <small class="text-muted">Calculée automatiquement selon le type d'abonnement et la
                                        date
                                        de paiement</small>
                                </div>

                                <div class="alert alert-info mb-0">
                                    <i class="fas fa-info-circle me-2"></i>
                                    <strong>Note :</strong> L'abonnement sera mis à jour avec le nouveau type et la nouvelle
                                    date d'expiration calculée automatiquement.
                                </div>
                            </div>

                            <!-- Informations actuelles de l'abonnement -->
                            <hr class="my-4">
                            <div>
                                <h6 class="fw-semibold mb-3">Abonnement actuel</h6>
                                <dl class="row mb-0 small">
                                    <dt class="col-sm-5">Type :</dt>
                                    <dd class="col-sm-7">
                                        <span class="badge bg-info">{{ $abonnement->type_label }}</span>
                                    </dd>
                                    <dt class="col-sm-5">Date d'expiration :</dt>
                                    <dd class="col-sm-7">
                                        {{ $abonnement->date_expiration->format('d/m/Y') }}
                                    </dd>
                                    <dt class="col-sm-5">Statut :</dt>
                                    <dd class="col-sm-7">
                                        <span
                                            class="badge bg-{{ $abonnement->statut === 'actif' ? 'success' : ($abonnement->statut === 'suspendu' ? 'warning' : 'danger') }}">
                                            {{ strtoupper($abonnement->statut) }}
                                        </span>
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Boutons d'action -->
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <a href="{{ route('admin.paiements.show', $paiement) }}" class="btn btn-secondary">
                                    <i class="fas fa-times me-2"></i>Annuler
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Enregistrer les modifications
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <script>
        const typeDurations = {
            mensuel: 1,
            trimestriel: 3,
            semestriel: 6,
            annuel: 12,
        };

        function toggleProlongationFields() {
            const checkbox = document.getElementById('prolonger_abonnement');
            const fields = document.getElementById('prolongation-fields');
            const typeSelect = document.getElementById('type_abonnement');

            if (checkbox.checked) {
                fields.style.display = 'block';
                typeSelect.required = true;
                updateEcheance();
            } else {
                fields.style.display = 'none';
                typeSelect.required = false;
            }
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

        document.getElementById('type_abonnement').addEventListener('change', updateEcheance);
        document.getElementById('date_paiement').addEventListener('change', updateEcheance);

        // Initialiser l'affichage si la case est déjà cochée
        if (document.getElementById('prolonger_abonnement').checked) {
            toggleProlongationFields();
        }
    </script>
@endsection
