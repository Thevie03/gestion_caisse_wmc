@extends('layouts.app')

@section('title', 'Modifier la Vente')

@section('content')
    <div class="container-fluid">
        <!-- En-tête -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h1 class="h3 mb-0 text-gray-800">
                            <i class="fas fa-edit me-2"></i>
                            Modifier la Vente {{ $vente->numero_vente }}
                        </h1>
                        <p class="text-muted mb-0">Modifiez les informations de la vente</p>
                    </div>
                    <div>
                        <a href="{{ route('ventes.show', $vente) }}" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-1"></i>
                            Retour
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Formulaire de modification -->
            <div class="col-lg-8">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-edit me-2"></i>
                            Informations de la Vente
                        </h6>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('ventes.update', $vente) }}">
                            @csrf
                            @method('PUT')

                            <!-- Informations non modifiables -->
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Numéro de vente</label>
                                        <p class="form-control-plaintext">
                                            <i class="fas fa-hashtag me-2"></i>
                                            {{ $vente->numero_vente }}
                                        </p>
                                        <small class="text-muted">Non modifiable</small>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Date de vente</label>
                                        <p class="form-control-plaintext">
                                            <i class="fas fa-calendar me-2"></i>
                                            {{ $vente->created_at->format('d/m/Y à H:i') }}
                                        </p>
                                        <small class="text-muted">Non modifiable</small>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Boutique</label>
                                        <p class="form-control-plaintext">
                                            <i class="fas fa-store me-2"></i>
                                            {{ $vente->boutique->nom }}
                                        </p>
                                        <small class="text-muted">Non modifiable</small>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Vendeur</label>
                                        <p class="form-control-plaintext">
                                            <i class="fas fa-user me-2"></i>
                                            {{ $vente->user->name }}
                                        </p>
                                        <small class="text-muted">Non modifiable</small>
                                    </div>
                                </div>
                            </div>

                            <hr>

                            <!-- Produits vendus - Modifiables -->
                            <div class="mb-4">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h5 class="mb-0">
                                        <i class="fas fa-shopping-cart me-2"></i>
                                        Produits Vendus
                                    </h5>
                                    <button type="button" class="btn btn-sm btn-outline-primary" id="ajouter-produit">
                                        <i class="fas fa-plus me-1"></i>
                                        Ajouter produit
                                    </button>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-bordered">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Produit</th>
                                                <th width="120">Quantité</th>
                                                <th width="150">Prix unitaire (FCFA)</th>
                                                <th width="150">Sous-total (FCFA)</th>
                                                <th width="80">Stock</th>
                                                <th width="110">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody id="produitsTableBody">
                                            @foreach ($vente->venteDetails as $index => $detail)
                                                <tr class="produit-row">
                                                    <td>
                                                        <div class="produit-nom-cell">
                                                            <strong>{{ $detail->produit->nom }}</strong>
                                                            <br>
                                                            <small class="text-muted">{{ $detail->produit->categorie }}</small>
                                                        </div>
                                                        <input type="hidden" class="detail-id-input" name="details[{{ $index }}][id]"
                                                            value="{{ $detail->id }}">
                                                        <input type="hidden" class="produit-id-input"
                                                            name="details[{{ $index }}][produit_id]"
                                                            value="{{ $detail->produit_id }}">
                                                    </td>
                                                    <td>
                                                        <input type="number" step="1" min="1"
                                                            class="form-control form-control-sm quantite-input"
                                                            name="details[{{ $index }}][quantite]"
                                                            value="{{ old("details.$index.quantite", $detail->quantite) }}"
                                                            required
                                                            data-stock="{{ $detail->produit->quantite_stock + $detail->quantite }}"
                                                            data-index="{{ $index }}">
                                                        @error("details.$index.quantite")
                                                            <div class="text-danger small">{{ $message }}</div>
                                                        @enderror
                                                    </td>
                                                    <td>
                                                        <input type="number" step="0.01" min="0"
                                                            class="form-control form-control-sm prix-input"
                                                            name="details[{{ $index }}][prix_unitaire]"
                                                            value="{{ old("details.$index.prix_unitaire", $detail->prix_unitaire) }}"
                                                            required data-index="{{ $index }}">
                                                        @error("details.$index.prix_unitaire")
                                                            <div class="text-danger small">{{ $message }}</div>
                                                        @enderror
                                                    </td>
                                                    <td>
                                                        <input type="number" step="0.01" min="0" readonly
                                                            class="form-control form-control-sm sous-total-input"
                                                            name="details[{{ $index }}][sous_total]"
                                                            value="{{ $detail->sous_total }}">
                                                        <small class="text-muted sous-total-display">
                                                            {{ number_format($detail->sous_total, 0, ',', ' ') }} FCFA
                                                        </small>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-info stock-display"
                                                            data-stock-actuel="{{ $detail->produit->quantite_stock }}">
                                                            {{ $detail->produit->quantite_stock + $detail->quantite }}
                                                        </span>
                                                    </td>
                                                    <td class="text-center">
                                                        <button type="button"
                                                            class="btn btn-sm btn-outline-danger retirer-produit"
                                                            title="Retirer ce produit">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                        <tfoot class="table-light">
                                            <tr>
                                                <th colspan="4" class="text-end">Sous-total :</th>
                                                <th colspan="2">
                                                    <span id="totalSousTotalDisplay">
                                                        {{ number_format($vente->venteDetails->sum('sous_total'), 0, ',', ' ') }}
                                                        FCFA
                                                    </span>
                                                    <input type="hidden" id="totalSousTotal" name="total_sous_total"
                                                        value="{{ $vente->venteDetails->sum('sous_total') }}">
                                                </th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>

                            <hr>

                            <!-- Champs modifiables -->
                            <div class="row">
                                <!-- Client -->
                                <div class="col-md-6 mb-3">
                                    <label for="client_id" class="form-label">
                                        <i class="fas fa-user me-1"></i>
                                        Client
                                    </label>
                                    <select class="form-select @error('client_id') is-invalid @enderror" id="client_id"
                                        name="client_id">
                                        <option value="">Aucun client (Anonyme)</option>
                                        @foreach ($clients as $client)
                                            <option value="{{ $client->id }}"
                                                {{ old('client_id', $vente->client_id) == $client->id ? 'selected' : '' }}>
                                                {{ $client->nom_complet }}
                                                @if ($client->telephone)
                                                    - {{ $client->telephone }}
                                                @endif
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('client_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Remise -->
                                <div class="col-md-6 mb-3">
                                    <label for="remise" class="form-label">
                                        <i class="fas fa-percent me-1"></i>
                                        Remise (FCFA)
                                    </label>
                                    <input type="number" step="0.01" min="0"
                                        class="form-control @error('remise') is-invalid @enderror" id="remise"
                                        name="remise" value="{{ old('remise', $vente->remise ?? 0) }}"
                                        placeholder="0.00">
                                    @error('remise')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">Sous-total :
                                        <span
                                            id="sousTotalPreview">{{ number_format($vente->venteDetails->sum('sous_total'), 0, ',', ' ') }}</span>
                                        FCFA</small>
                                </div>
                            </div>

                            <div class="row">
                                <!-- Mode de paiement -->
                                <div class="col-md-6 mb-3">
                                    <label for="mode_paiement" class="form-label">
                                        <i class="fas fa-money-bill-wave me-1"></i>
                                        Mode de paiement <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select @error('mode_paiement') is-invalid @enderror"
                                        id="mode_paiement" name="mode_paiement" required>
                                        <option value="especes"
                                            {{ old('mode_paiement', $vente->mode_paiement) == 'especes' ? 'selected' : '' }}>
                                            Espèces</option>
                                        <option value="wave"
                                            {{ old('mode_paiement', $vente->mode_paiement) == 'wave' ? 'selected' : '' }}>
                                            Wave</option>
                                        <option value="orange_money"
                                            {{ old('mode_paiement', $vente->mode_paiement) == 'orange_money' ? 'selected' : '' }}>
                                            Orange Money</option>
                                        <option value="mtn_money"
                                            {{ old('mode_paiement', $vente->mode_paiement) == 'mtn_money' ? 'selected' : '' }}>
                                            MTN Money</option>
                                        <option value="carte"
                                            {{ old('mode_paiement', $vente->mode_paiement) == 'carte' ? 'selected' : '' }}>
                                            Carte bancaire</option>
                                    </select>
                                    @error('mode_paiement')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Statut de paiement -->
                                <div class="col-md-6 mb-3">
                                    <label for="statut_paiement" class="form-label">
                                        <i class="fas fa-check-circle me-1"></i>
                                        Statut de paiement <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select @error('statut_paiement') is-invalid @enderror"
                                        id="statut_paiement" name="statut_paiement" required>
                                        <option value="complet"
                                            {{ old('statut_paiement', $vente->statut_paiement) == 'complet' ? 'selected' : '' }}>
                                            Soldé</option>
                                        <option value="partiel"
                                            {{ old('statut_paiement', $vente->statut_paiement) == 'partiel' ? 'selected' : '' }}>
                                            Partiel</option>
                                        <option value="impaye"
                                            {{ old('statut_paiement', $vente->statut_paiement) == 'impaye' ? 'selected' : '' }}>
                                            Impayé</option>
                                    </select>
                                    @error('statut_paiement')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Notes -->
                            <div class="mb-3">
                                <label for="notes" class="form-label">
                                    <i class="fas fa-sticky-note me-1"></i>
                                    Notes
                                </label>
                                <textarea class="form-control @error('notes') is-invalid @enderror" id="notes" name="notes" rows="3"
                                    placeholder="Notes sur la vente...">{{ old('notes', $vente->notes) }}</textarea>
                                @error('notes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Boutons d'action -->
                            <div class="d-flex justify-content-between mt-4">
                                <a href="{{ route('ventes.show', $vente) }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-times me-1"></i>
                                    Annuler
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-1"></i>
                                    Enregistrer les modifications
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Informations complémentaires -->
            <div class="col-lg-4">
                <!-- Résumé de la vente -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-info-circle me-2"></i>
                            Résumé de la Vente
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Sous-total</label>
                            <p class="form-control-plaintext">
                                {{ number_format($vente->venteDetails->sum('sous_total'), 0, ',', ' ') }} FCFA
                            </p>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Remise actuelle</label>
                            <p class="form-control-plaintext text-warning">
                                -{{ number_format($vente->remise ?? 0, 0, ',', ' ') }} FCFA
                            </p>
                        </div>

                        <hr>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Remise</label>
                            <p class="form-control-plaintext text-warning" id="remisePreview">
                                -{{ number_format($vente->remise ?? 0, 0, ',', ' ') }} FCFA
                            </p>
                        </div>

                        <hr>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Total final</label>
                            <p class="form-control-plaintext">
                                <strong class="text-success fs-5" id="totalFinalPreview">
                                    {{ number_format($vente->total_final, 0, ',', ' ') }} FCFA
                                </strong>
                            </p>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Montant payé</label>
                            <p class="form-control-plaintext">
                                {{ number_format($vente->montant_paye ?? 0, 0, ',', ' ') }} FCFA
                            </p>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Solde restant</label>
                            <p class="form-control-plaintext">
                                <strong class="text-danger">
                                    {{ number_format($vente->solde_restant ?? $vente->total_final, 0, ',', ' ') }}
                                    FCFA
                                </strong>
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Produits vendus -->
                <div class="card shadow">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-shopping-cart me-2"></i>
                            Produits Vendus ({{ $vente->venteDetails->count() }})
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Produit</th>
                                        <th>Qté</th>
                                        <th>Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($vente->venteDetails as $detail)
                                        <tr>
                                            <td>
                                                <small>{{ $detail->produit->nom }}</small>
                                            </td>
                                            <td>
                                                <span class="badge bg-primary">{{ $detail->quantite }}</span>
                                            </td>
                                            <td>
                                                <small>{{ number_format($detail->sous_total, 0, ',', ' ') }} FCFA</small>
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
    </div>

    @php
        $produitsDisponibles = $produits->map(function ($produit) {
            return [
                'id' => $produit->id,
                'nom' => $produit->nom,
                'categorie' => $produit->categorie,
                'prix_vente' => round($produit->prix_vente),
                'stock' => (int) $produit->quantite_stock,
            ];
        })->values();
    @endphp

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const produitsDisponibles = @json($produitsDisponibles);

            const produitsTableBody = document.getElementById('produitsTableBody');
            const addProduitBtn = document.getElementById('ajouter-produit');
            const form = document.querySelector('form');
            let detailIndex = produitsTableBody.querySelectorAll('.produit-row').length;

            function getProduitById(produitId) {
                return produitsDisponibles.find(p => String(p.id) === String(produitId));
            }

            function reindexRows() {
                const rows = produitsTableBody.querySelectorAll('.produit-row');
                rows.forEach((row, index) => {
                    row.querySelectorAll('input, select').forEach(field => {
                        if (!field.name) return;
                        field.name = field.name.replace(/details\[\d+\]/, `details[${index}]`);
                        field.dataset.index = String(index);
                    });
                });
                detailIndex = rows.length;
            }

            function calculerSousTotal(row) {
                if (!row) return;
                const quantite = parseInt(row.querySelector('.quantite-input').value) || 0;
                const prix = parseFloat(row.querySelector('.prix-input').value) || 0;
                const sousTotal = Math.round(prix * quantite);

                row.querySelector('.sous-total-input').value = sousTotal.toFixed(0);
                row.querySelector('.sous-total-display').textContent = sousTotal.toLocaleString('fr-FR', {
                    minimumFractionDigits: 0,
                    maximumFractionDigits: 0
                }) + ' FCFA';

                const stockDisponible = parseFloat(row.querySelector('.quantite-input').dataset.stock) || 0;
                const stockActuel = stockDisponible - quantite;
                const stockBadge = row.querySelector('.stock-display');

                stockBadge.textContent = stockActuel;
                if (stockActuel < 0) {
                    stockBadge.className = 'badge bg-danger stock-display';
                    row.querySelector('.quantite-input').classList.add('is-invalid');
                } else if (stockActuel < 5) {
                    stockBadge.className = 'badge bg-warning stock-display';
                    row.querySelector('.quantite-input').classList.remove('is-invalid');
                } else {
                    stockBadge.className = 'badge bg-info stock-display';
                    row.querySelector('.quantite-input').classList.remove('is-invalid');
                }

                calculerTotal();
            }

            function calculerTotal() {
                let total = 0;
                document.querySelectorAll('.sous-total-input').forEach(input => {
                    total += parseFloat(input.value) || 0;
                });
                total = Math.round(total);

                const remise = Math.round(parseFloat(document.getElementById('remise').value) || 0);
                const totalFinal = Math.round(total - remise);

                document.getElementById('totalSousTotal').value = total.toFixed(0);
                document.getElementById('totalSousTotalDisplay').textContent = total.toLocaleString('fr-FR', {
                    minimumFractionDigits: 0,
                    maximumFractionDigits: 0
                }) + ' FCFA';
                document.getElementById('sousTotalPreview').textContent = total.toLocaleString('fr-FR', {
                    minimumFractionDigits: 0,
                    maximumFractionDigits: 0
                });

                const totalFinalDisplay = document.getElementById('totalFinalPreview');
                if (totalFinalDisplay) {
                    totalFinalDisplay.textContent = totalFinal.toLocaleString('fr-FR', {
                        minimumFractionDigits: 0,
                        maximumFractionDigits: 0
                    });
                }
            }

            function handleProduitSelection(select) {
                const row = select.closest('tr');
                const produit = getProduitById(select.value);
                if (!produit) return;

                row.querySelector('.produit-nom-cell').innerHTML = `<strong>${produit.nom}</strong><br><small class="text-muted">${produit.categorie ?? ''}</small>`;
                row.querySelector('.produit-id-input').value = produit.id;

                const quantiteInput = row.querySelector('.quantite-input');
                quantiteInput.dataset.stock = produit.stock;
                if (!quantiteInput.value || parseInt(quantiteInput.value, 10) < 1) {
                    quantiteInput.value = 1;
                }

                row.querySelector('.prix-input').value = Math.round(produit.prix_vente);
                row.querySelector('.stock-display').textContent = produit.stock - (parseInt(quantiteInput.value, 10) || 0);
                calculerSousTotal(row);
            }

            function addProduitRow() {
                const options = produitsDisponibles.map((produit) =>
                    `<option value="${produit.id}">${produit.nom} (${produit.stock})</option>`
                ).join('');

                const row = document.createElement('tr');
                row.className = 'produit-row';
                row.innerHTML = `
                    <td>
                        <div class="mb-2">
                            <select class="form-select form-select-sm produit-select">
                                <option value="">Choisir un produit...</option>
                                ${options}
                            </select>
                        </div>
                        <div class="produit-nom-cell text-muted small">Sélectionnez un produit</div>
                        <input type="hidden" class="produit-id-input" name="details[${detailIndex}][produit_id]">
                    </td>
                    <td>
                        <input type="number" step="1" min="1" class="form-control form-control-sm quantite-input"
                            name="details[${detailIndex}][quantite]" value="1" required data-stock="0" data-index="${detailIndex}">
                    </td>
                    <td>
                        <input type="number" step="0.01" min="0" class="form-control form-control-sm prix-input"
                            name="details[${detailIndex}][prix_unitaire]" value="0" required data-index="${detailIndex}">
                    </td>
                    <td>
                        <input type="number" step="0.01" min="0" readonly class="form-control form-control-sm sous-total-input"
                            name="details[${detailIndex}][sous_total]" value="0">
                        <small class="text-muted sous-total-display">0 FCFA</small>
                    </td>
                    <td>
                        <span class="badge bg-info stock-display">0</span>
                    </td>
                    <td class="text-center">
                        <button type="button" class="btn btn-sm btn-outline-danger retirer-produit" title="Retirer ce produit">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                `;

                produitsTableBody.appendChild(row);
                detailIndex++;
            }

            addProduitBtn.addEventListener('click', addProduitRow);

            produitsTableBody.addEventListener('input', function(e) {
                if (e.target.classList.contains('quantite-input') || e.target.classList.contains('prix-input')) {
                    calculerSousTotal(e.target.closest('tr'));
                }
            });

            produitsTableBody.addEventListener('change', function(e) {
                if (e.target.classList.contains('produit-select')) {
                    handleProduitSelection(e.target);
                }
            });

            produitsTableBody.addEventListener('click', function(e) {
                const removeBtn = e.target.closest('.retirer-produit');
                if (!removeBtn) return;

                const rows = produitsTableBody.querySelectorAll('.produit-row');
                if (rows.length <= 1) {
                    alert('La vente doit contenir au moins un produit.');
                    return;
                }

                removeBtn.closest('tr').remove();
                reindexRows();
                calculerTotal();
            });

            document.getElementById('remise').addEventListener('input', function() {
                calculerTotal();
                const remise = parseFloat(this.value) || 0;
                const remisePreview = document.getElementById('remisePreview');
                if (remisePreview) {
                    remisePreview.textContent = '-' + remise.toLocaleString('fr-FR', {
                        minimumFractionDigits: 0,
                        maximumFractionDigits: 0
                    }) + ' FCFA';
                }
            });

            form.addEventListener('submit', function(e) {
                let hasError = false;
                let hasProduit = false;

                produitsTableBody.querySelectorAll('.produit-row').forEach(row => {
                    const produitIdInput = row.querySelector('.produit-id-input');
                    if (!produitIdInput || !produitIdInput.value) {
                        hasError = true;
                        const select = row.querySelector('.produit-select');
                        if (select) {
                            select.classList.add('is-invalid');
                        }
                        return;
                    }

                    hasProduit = true;
                });

                if (!hasProduit) {
                    hasError = true;
                    alert('Vous devez conserver au moins un produit dans la vente.');
                }

                document.querySelectorAll('.quantite-input').forEach(input => {
                    const stockDisponible = parseFloat(input.dataset.stock) || 0;
                    const quantite = parseFloat(input.value) || 0;
                    const stockRestant = stockDisponible - quantite;

                    if (stockRestant < 0) {
                        hasError = true;
                        input.classList.add('is-invalid');
                    } else {
                        input.classList.remove('is-invalid');
                    }
                });

                if (hasError) {
                    e.preventDefault();
                    alert(
                        'Erreur : vérifiez les produits sélectionnés et les stocks disponibles.'
                        );
                }
            });

            calculerTotal();
        });
    </script>
@endsection
