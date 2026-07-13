@extends('layouts.app')

@section('content')

    @php
        $posPaymentImage = !empty($activeBoutique?->pos_banner_image)
            ? asset('images/pos/' . basename($activeBoutique->pos_banner_image))
            : asset('images/pos/pos-banner.jpg');
    @endphp

    <div class="row pos-layout g-4 align-items-start">
        <!-- Panier de vente -->
        <div class="col-12 col-md-7 col-xl-8 pos-panier-col order-2 order-sm-1">
            <div class="card pos-card pos-panier-compact" style="border: none; box-shadow: var(--shadow-md); border-radius: var(--radius-md);">
                <div class="card-header pos-card-header" style="background: var(--bg-primary); border-bottom: 2px solid var(--border-light); border-radius: var(--radius-md) var(--radius-md) 0 0;">
                    <h5 class="card-title mb-0 fw-bold" style="color: var(--text-primary); font-size: 1.125rem;">
                        <i class="fas fa-shopping-cart me-2" style="color: var(--primary-color);"></i>
                        Panier de vente
                    </h5>
                </div>
                <div class="card-body pos-panier-body-compact" style="padding: var(--spacing-lg);">
                    <!-- Champ de scan code-barres -->
                    <div class="mb-4">
                        <label for="barcodeInput" class="form-label fw-semibold mb-2" style="color: var(--text-primary);">
                            <i class="fas fa-barcode me-2" style="color: var(--primary-color);"></i>
                            Scanner un code-barres
                        </label>
                        <div class="pos-barcode-input-wrapper">
                            <div class="input-group input-group-lg">
                                <span class="input-group-text" style="background: var(--primary-color); color: white; border: none; border-radius: var(--radius) 0 0 var(--radius);">
                                    <i class="fas fa-qrcode"></i>
                                </span>
                                <input type="text" class="form-control form-control-lg pos-barcode-input" id="barcodeInput" autofocus
                                    autocomplete="off" placeholder="Scannez ou saisissez le code-barres puis appuyez sur Entrée"
                                    style="border: 2px solid var(--border-color); border-left: none; border-right: none; padding: var(--spacing-md); font-size: 1rem;">
                                <span class="input-group-text" style="background: var(--bg-secondary); border: 2px solid var(--border-color); border-left: none; border-radius: 0 var(--radius) var(--radius) 0;">
                                    <small class="text-muted fw-medium">Entrée</small>
                                </span>
                            </div>
                        </div>
                        <div class="form-text mt-2">
                            <small class="text-muted d-flex align-items-center gap-1">
                                <i class="fas fa-info-circle" style="color: var(--info-color);"></i>
                                Placez le curseur ici et scannez un produit. Compatible avec les scanners USB.
                            </small>
                        </div>
                    </div>

                    <div id="panier-vide" class="text-center py-5 pos-empty-cart">
                        <div class="pos-empty-cart-visual mx-auto mb-3 rounded-4 overflow-hidden border pos-empty-cart-img-wrap"
                            style="max-width: 320px;">
                            <img src="{{ $posPaymentImage }}" alt="" class="w-100 d-block pos-empty-cart-img"
                                width="640" height="280" loading="lazy" decoding="async">
                        </div>
                        <div class="pos-empty-icon mb-3">
                            <i class="fas fa-shopping-cart" style="font-size: 4rem; color: var(--border-dark); opacity: 0.3;"></i>
                        </div>
                        <h4 class="fw-semibold mb-2" style="color: var(--text-secondary);">Panier vide</h4>
                        <p class="text-muted mb-0">Sélectionnez des produits pour commencer une vente</p>
                    </div>

                    <div id="panier-contenu" style="display: none;">
                        <div class="table-responsive pos-table-wrapper">
                            <table class="table table-hover pos-table">
                                <thead>
                                    <tr style="background: var(--bg-secondary);">
                                        <th style="font-weight: 600; color: var(--text-primary); padding: var(--spacing-md); border-bottom: 2px solid var(--border-light);">Produit</th>
                                        <th width="120" style="font-weight: 600; color: var(--text-primary); padding: var(--spacing-md); border-bottom: 2px solid var(--border-light);">Quantité</th>
                                        <th width="120" style="font-weight: 600; color: var(--text-primary); padding: var(--spacing-md); border-bottom: 2px solid var(--border-light);">Prix unit.</th>
                                        <th width="120" style="font-weight: 600; color: var(--text-primary); padding: var(--spacing-md); border-bottom: 2px solid var(--border-light);">Total</th>
                                        <th width="80" style="font-weight: 600; color: var(--text-primary); padding: var(--spacing-md); border-bottom: 2px solid var(--border-light);">Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="panier-liste">
                                    <!-- Les produits seront ajoutés ici dynamiquement -->
                                </tbody>
                            </table>
                        </div>

                        <div class="row mt-3 g-2">
                            <div class="col-12 col-md-6">
                                <label class="form-label fw-semibold mb-2" style="color: var(--text-primary);">
                                    <i class="fas fa-percent me-2" style="color: var(--primary-color);"></i>
                                    Remise (FCFA)
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text" style="background: var(--bg-secondary); border: 2px solid var(--border-color); border-right: none; border-radius: var(--radius) 0 0 var(--radius);">
                                        <i class="fas fa-tag" style="color: var(--text-muted);"></i>
                                    </span>
                                    <input type="number" class="form-control pos-input-discount" id="remise" value="0" min="0"
                                        step="0.01" style="border: 2px solid var(--border-color); border-left: none; border-right: none; padding: var(--spacing-md);">
                                    <span class="input-group-text" style="background: var(--bg-secondary); border: 2px solid var(--border-color); border-left: none; border-radius: 0 var(--radius) var(--radius) 0;">FCFA</span>
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label fw-semibold mb-2" style="color: var(--text-primary);">
                                    <i class="fas fa-sticky-note me-2" style="color: var(--primary-color);"></i>
                                    Notes
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text" style="background: var(--bg-secondary); border: 2px solid var(--border-color); border-right: none; border-radius: var(--radius) 0 0 var(--radius);">
                                        <i class="fas fa-comment" style="color: var(--text-muted);"></i>
                                    </span>
                                    <input type="text" class="form-control pos-input-notes" id="notes"
                                        placeholder="Notes optionnelles" style="border: 2px solid var(--border-color); border-left: none; border-radius: 0 var(--radius) var(--radius) 0; padding: var(--spacing-md);">
                                </div>
                            </div>
                        </div>

                        <div class="row mt-3">
                            <div class="col-12">
                                <div class="card pos-payment-card" style="border: 1px solid var(--border-light); border-radius: var(--radius-md); background: var(--bg-primary); box-shadow: var(--shadow-sm);">
                                    <div class="card-body" style="padding: var(--spacing-md);">
                                        <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-3">
                                            <div>
                                                <h6 class="card-title mb-1 fw-bold" style="color: var(--text-primary); font-size: 0.9375rem;">
                                                    <i class="fas fa-wallet me-2" style="color: var(--primary-color);"></i>
                                                    Modes de paiement
                                                </h6>
                                                <p class="text-muted small mb-0">Répartissez la facture entre plusieurs moyens en toute simplicité.</p>
                                            </div>
                                            <button type="button" class="btn btn-sm pos-btn-add-payment" id="ajouter-paiement" style="background: var(--primary-color); color: white; border: none; border-radius: var(--radius-full); padding: var(--spacing-xs) var(--spacing-md); font-weight: 600;">
                                                <i class="fas fa-plus me-1"></i>
                                                Ajouter
                                            </button>
                                        </div>

                                        <div id="paiements-container" class="paiements-grid mt-2"></div>

                                        <div class="paiement-summary mt-2" style="background: var(--bg-secondary); border-radius: var(--radius-md); padding: var(--spacing-md); border: 1px solid var(--border-light);">
                                            <div class="row g-3 text-center paiement-summary-row">
                                                <div class="col-6">
                                                    <p class="text-muted text-uppercase small mb-2 fw-semibold" style="letter-spacing: 0.5px;">Réparti</p>
                                                    <h6 class="mb-0 fw-bold" id="montant-reparti" style="color: var(--success-color); font-size: 1.125rem;">0 FCFA</h6>
                                                </div>
                                                <div class="col-6">
                                                    <p class="text-muted text-uppercase small mb-2 fw-semibold" style="letter-spacing: 0.5px;">Reste à payer</p>
                                                    <h6 class="mb-0 fw-bold text-danger" id="reste-a-payer" style="font-size: 1.125rem;">0 FCFA</h6>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="d-flex align-items-center gap-2 mt-2 flex-wrap">
                                            <div class="form-check form-switch mb-0 d-flex align-items-center gap-2">
                                                <input class="form-check-input border-danger" type="checkbox"
                                                    id="paiement_partiel">
                                                <label
                                                    class="form-check-label fw-semibold small text-uppercase text-danger mb-0"
                                                    for="paiement_partiel">
                                                    <i class="fas fa-exclamation-circle me-1"></i>
                                                    Paiement partiel
                                                </label>
                                            </div>
                                            <small class="text-danger fw-semibold">
                                                Activez-le uniquement si une partie reste à encaisser plus tard.
                                            </small>
                                        </div>

                                        <div class="alert alert-danger py-2 mt-2 mb-0" id="paiements-erreur"
                                            style="display: none;">
                                            <small id="paiements-erreur-message"></small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-3 g-2 pos-panier-bottom-row">
                            <div class="col-12 col-md-6 col-lg-4">
                                <div class="card pos-summary-card" style="border: 1px solid var(--border-light); border-radius: var(--radius-md); background: var(--bg-primary);">
                                    <div class="card-body" style="padding: var(--spacing-md);">
                                        <h6 class="card-title mb-3 fw-bold" style="color: var(--text-primary); font-size: 0.9375rem;">
                                            <i class="fas fa-receipt me-2" style="color: var(--primary-color);"></i>
                                            Résumé de la vente
                                        </h6>
                                        <div class="pos-summary-item">
                                            <span class="pos-summary-label">Sous-total :</span>
                                            <span class="pos-summary-value" id="sous-total">0 FCFA</span>
                                        </div>
                                        <div class="pos-summary-item">
                                            <span class="pos-summary-label">Remise :</span>
                                            <span class="pos-summary-value text-danger" id="remise-montant">0 FCFA</span>
                                        </div>
                                        <hr style="margin: var(--spacing-md) 0; border-color: var(--border-light);">
                                        <div class="pos-summary-item pos-summary-total">
                                            <span class="pos-summary-label fw-bold">Total :</span>
                                            <span class="pos-summary-value fw-bold" id="total-final" style="color: var(--primary-color); font-size: 1.25rem;">0 FCFA</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-6 col-lg-4">
                                <div class="card" style="border: 1px solid var(--border-light); border-radius: var(--radius-md); background: var(--bg-primary);">
                                    <div class="card-body" style="padding: var(--spacing-md);">
                                        <h6 class="card-title mb-3 fw-bold" style="color: var(--text-primary); font-size: 0.9375rem;">
                                            <i class="fas fa-user me-2" style="color: var(--primary-color);"></i>
                                            Client
                                        </h6>
                                        <select class="form-select pos-client-select" id="client_id" name="client_id" style="border: 2px solid var(--border-color); border-radius: var(--radius); padding: var(--spacing-sm) var(--spacing-md);">
                                            <option value="">Sélectionner un client (optionnel)</option>
                                            @foreach ($clients as $client)
                                                <option value="{{ $client->id }}">{{ $client->nom_complet }}</option>
                                            @endforeach
                                        </select>
                                        <small class="text-muted d-block mt-2">
                                            <i class="fas fa-info-circle me-1"></i>
                                            Laisser vide pour vente anonyme
                                        </small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-lg-4 d-grid gap-2 align-content-start">
                                <button type="button" class="btn btn-success btn-pos pos-btn-finalize" id="finaliser-vente" disabled style="background: var(--success-color); border: none; color: white; padding: var(--spacing-md) var(--spacing-lg); font-weight: 600; border-radius: var(--radius-md);">
                                    <i class="fas fa-check-circle me-2"></i>
                                    <span class="d-none d-sm-inline">Finaliser la vente</span>
                                    <span class="d-sm-none">Finaliser</span>
                                </button>
                                <button type="button" class="btn btn-danger btn-pos pos-btn-clear" id="vider-panier"
                                    onclick="viderPanier()" style="background: var(--danger-color); border: none; color: white; padding: var(--spacing-md) var(--spacing-lg); font-weight: 600; border-radius: var(--radius-md);">
                                    <i class="fas fa-trash-alt me-2"></i>
                                    <span class="d-none d-sm-inline">Vider le panier</span>
                                    <span class="d-sm-none">Vider</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Catalogue des produits -->
        <div class="col-12 col-md-5 col-xl-4 pos-catalogue-col order-1 order-sm-2">
            <div class="card pos-card" style="border: none; box-shadow: var(--shadow-md); border-radius: var(--radius-md);">
                <div class="card-header pos-card-header" style="background: var(--bg-primary); border-bottom: 2px solid var(--border-light); border-radius: var(--radius-md) var(--radius-md) 0 0;">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0 fw-bold" style="color: var(--text-primary); font-size: 1.125rem;">
                            <i class="fas fa-box me-2" style="color: var(--primary-color);"></i>
                            Catalogue des produits
                        </h5>
                        <button type="button" class="btn btn-sm pos-btn-add" data-bs-toggle="modal"
                            data-bs-target="#ajouterProduitModal" style="background: var(--success-color); color: white; border: none; border-radius: var(--radius); padding: var(--spacing-xs) var(--spacing-md); font-weight: 600;">
                            <i class="fas fa-plus me-1"></i>
                            Ajouter
                        </button>
                    </div>
                </div>
                <div class="card-body" style="padding: var(--spacing-lg);">
                    <div class="mb-3 pos-search-wrapper">
                        <div class="input-group">
                            <span class="input-group-text" style="background: var(--bg-secondary); border: 2px solid var(--border-color); border-right: none; border-radius: var(--radius) 0 0 var(--radius);">
                                <i class="fas fa-search" style="color: var(--text-muted);"></i>
                            </span>
                            <input type="text" class="form-control pos-search-input" id="recherche-produit"
                                placeholder="Rechercher un produit..." style="border: 2px solid var(--border-color); border-left: none; border-right: none; padding: var(--spacing-md);">
                            <span class="input-group-text" style="background: var(--bg-secondary); border: 2px solid var(--border-color); border-left: none; border-radius: 0 var(--radius) var(--radius) 0;">
                                <i class="fas fa-filter" style="color: var(--text-muted);"></i>
                            </span>
                        </div>
                    </div>

                    <div id="catalogue-produits" class="pos-products-grid">
                        @foreach ($produits as $produit)
                            <div class="produit-item" data-nom="{{ strtolower($produit->nom) }}"
                                data-categorie="{{ strtolower($produit->categorie) }}">
                                <div class="card produit-card" data-produit-id="{{ $produit->id }}"
                                    data-prix="{{ $produit->prix_vente }}" data-stock="{{ $produit->quantite_stock }}">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-start gap-3">
                                            {{-- Image du produit --}}
                                            <div class="pos-product-image">
                                                @if ($produit->image)
                                                    <img src="{{ asset($produit->image) }}" alt="{{ $produit->nom }}"
                                                        class="pos-product-img" loading="lazy" decoding="async">
                                                @else
                                                    <div class="pos-product-placeholder">
                                                        <i class="fas fa-image"></i>
                                                    </div>
                                                @endif
                                            </div>
                                            {{-- Informations du produit --}}
                                            <div class="flex-grow-1 pos-product-info">
                                                <h6 class="pos-product-name" title="{{ $produit->nom }}">
                                                    {{ $produit->nom }}
                                                </h6>
                                                <small class="pos-product-category">{{ $produit->categorie }}</small>
                                                <div class="d-flex align-items-center justify-content-between mt-2">
                                                    <strong class="pos-product-price">
                                                        {{ number_format($produit->prix_vente, 0, ',', ' ') }} FCFA
                                                    </strong>
                                                    @if ($produit->quantite_stock > 0)
                                                        <span class="pos-product-stock">
                                                            <i class="fas fa-box me-1"></i>{{ $produit->quantite_stock }}
                                                        </span>
                                                    @else
                                                        <span class="pos-product-stock-empty">
                                                            <i class="fas fa-info-circle me-1"></i>Stock non géré
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>
                                            {{-- Bouton ajouter --}}
                                            <div class="pos-product-action">
                                                <button class="btn ajouter-panier pos-add-btn"
                                                    data-produit-id="{{ $produit->id }}" title="Ajouter au panier">
                                                    <i class="fas fa-plus"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <nav id="pos-catalog-pagination" class="pos-catalog-pagination d-none mt-2" aria-label="Pagination du catalogue">
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <small id="pos-catalog-pagination-info" class="text-muted"></small>
                            <div class="btn-group btn-group-sm" role="group">
                                <button type="button" class="btn btn-outline-secondary" id="pos-catalog-page-prev"
                                    title="Page précédente">
                                    <i class="fas fa-chevron-left"></i>
                                </button>
                                <button type="button" class="btn btn-outline-secondary" id="pos-catalog-page-next"
                                    title="Page suivante">
                                    <i class="fas fa-chevron-right"></i>
                                </button>
                            </div>
                        </div>
                    </nav>

                    @if ($produits->isEmpty())
                        <div class="text-center py-5 pos-empty-products">
                            <div class="pos-empty-icon mb-3">
                                <i class="fas fa-box" style="font-size: 3rem; color: var(--border-dark); opacity: 0.3;"></i>
                            </div>
                            <p class="text-muted mb-0 fw-medium">Aucun produit disponible</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Script pour la gestion du panier -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let panier = [];
            let total = 0;
            let totalFinal = 0;

            const paiementsContainer = document.getElementById('paiements-container');
            const ajouterPaiementBtn = document.getElementById('ajouter-paiement');
            const paiementsErreur = document.getElementById('paiements-erreur');
            const paiementsErreurMessage = document.getElementById('paiements-erreur-message');
            const montantRepartiElement = document.getElementById('montant-reparti');
            const resteAPayerElement = document.getElementById('reste-a-payer');
            const paiementPartielCheckbox = document.getElementById('paiement_partiel');

            // Clé pour le localStorage
            const STORAGE_KEY = 'vente_pos_panier_encours';
            let saveTimer = null;

            function ajouterLignePaiement(paiement = {
                mode: 'especes',
                montant: ''
            }) {
                const row = document.createElement('div');
                row.className = 'paiement-row paiement-card shadow-sm p-3 mb-2 rounded-3';
                row.innerHTML = `
                    <div class="row g-2 align-items-center paiement-row-fields">
                        <div class="col-12 d-flex justify-content-between align-items-center">
                            <span class="badge bg-light text-dark text-uppercase small fw-semibold">
                                Mode de paiement
                            </span>
                            <button type="button" class="btn btn-link text-danger text-decoration-none supprimer-paiement">
                                <i class="fas fa-times-circle me-1"></i>
                                Retirer
                            </button>
                        </div>
                        <div class="col-12 col-md-6 paiement-row-col">
                            <label class="form-label text-muted small mb-1">Sélectionner</label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-white">
                                    <i class="fas fa-wallet text-muted"></i>
                                </span>
                                <select class="form-select mode-paiement-select">
                                    <option value="especes" ${paiement.mode === 'especes' ? 'selected' : ''}>Espèces</option>
                                    <option value="wave" ${paiement.mode === 'wave' ? 'selected' : ''}>Wave</option>
                                    <option value="orange_money" ${paiement.mode === 'orange_money' ? 'selected' : ''}>Orange Money</option>
                                    <option value="mtn_money" ${paiement.mode === 'mtn_money' ? 'selected' : ''}>MTN Money</option>
                                    <option value="carte" ${paiement.mode === 'carte' ? 'selected' : ''}>Carte bancaire</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-12 col-md-6 paiement-row-col">
                            <label class="form-label text-muted small mb-1">Montant</label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-white">
                                    <i class="fas fa-coins text-warning"></i>
                                </span>
                                <input type="number" class="form-control montant-paiement-input" placeholder="Montant encaissé"
                                    min="0" step="0.01" value="${paiement.montant ?? ''}">
                                <span class="input-group-text">FCFA</span>
                            </div>
                        </div>
                    </div>
                `;

                paiementsContainer.appendChild(row);

                row.querySelector('.supprimer-paiement').addEventListener('click', function() {
                    if (paiementsContainer.querySelectorAll('.paiement-row').length > 1) {
                        row.remove();
                        mettreAJourResumePaiements();
                        sauvegarderPanier();
                    }
                    mettreAJourBoutonsPaiement();
                });

                mettreAJourBoutonsPaiement();
            }

            function mettreAJourBoutonsPaiement() {
                const lignes = paiementsContainer.querySelectorAll('.paiement-row');
                lignes.forEach((ligne) => {
                    const bouton = ligne.querySelector('.supprimer-paiement');
                    bouton.style.visibility = lignes.length > 1 ? 'visible' : 'hidden';
                });
            }

            function initialiserPaiements(paiementsSauvegardes = []) {
                paiementsContainer.innerHTML = '';

                if (paiementsSauvegardes.length > 0) {
                    paiementsSauvegardes.forEach(paiement => ajouterLignePaiement(paiement));
                } else {
                    ajouterLignePaiement();
                }

                mettreAJourResumePaiements();
            }

            function getPaiementsDepuisUI(preserveZeros = true) {
                const lignes = Array.from(paiementsContainer.querySelectorAll('.paiement-row'));

                const paiements = lignes.map(ligne => {
                    const mode = ligne.querySelector('.mode-paiement-select').value;
                    const montant = Math.round(parseFloat(ligne.querySelector('.montant-paiement-input')
                        .value) || 0);
                    return {
                        mode,
                        montant
                    };
                });

                if (preserveZeros) {
                    return paiements;
                }

                return paiements.filter(paiement => paiement.montant > 0);
            }

            function mettreAJourResumePaiements() {
                const paiements = getPaiementsDepuisUI();
                const montantReparti = paiements.reduce((somme, paiement) => somme + (paiement.montant || 0), 0);
                const reste = Math.max(totalFinal - montantReparti, 0);

                montantRepartiElement.textContent = montantReparti.toLocaleString() + ' FCFA';
                resteAPayerElement.textContent = reste.toLocaleString() + ' FCFA';
                masquerErreurPaiements();
            }

            function afficherErreurPaiements(message) {
                paiementsErreurMessage.textContent = message;
                paiementsErreur.style.display = 'block';
                paiementsErreur.scrollIntoView({
                    behavior: 'smooth',
                    block: 'nearest'
                });
            }

            function masquerErreurPaiements() {
                paiementsErreur.style.display = 'none';
                paiementsErreurMessage.textContent = '';
            }

            initialiserPaiements();

            ajouterPaiementBtn.addEventListener('click', function() {
                ajouterLignePaiement();
                sauvegarderPanier();
            });

            paiementsContainer.addEventListener('input', function(e) {
                if (e.target.classList.contains('montant-paiement-input')) {
                    mettreAJourResumePaiements();
                    sauvegarderPanier();
                }
            });

            paiementsContainer.addEventListener('change', function(e) {
                if (e.target.classList.contains('mode-paiement-select')) {
                    sauvegarderPanier();
                }
            });

            paiementPartielCheckbox.addEventListener('change', function() {
                masquerErreurPaiements();
                mettreAJourResumePaiements();
                sauvegarderPanier();
            });

            // Fonction pour sauvegarder le panier dans localStorage
            function sauvegarderPanier() {
                const donneesPanier = {
                    panier: panier,
                    remise: document.getElementById('remise').value,
                    notes: document.getElementById('notes').value,
                    client_id: document.getElementById('client_id').value,
                    paiements: getPaiementsDepuisUI(true),
                    paiement_partiel: paiementPartielCheckbox.checked
                };
                if (saveTimer) {
                    clearTimeout(saveTimer);
                }
                saveTimer = setTimeout(() => {
                    localStorage.setItem(STORAGE_KEY, JSON.stringify(donneesPanier));
                }, 250);
            }

            // Fonction pour restaurer le panier depuis localStorage
            function restaurerPanier() {
                const donneesSauvegardees = localStorage.getItem(STORAGE_KEY);
                if (!donneesSauvegardees) {
                    return;
                }

                try {
                    const donnees = JSON.parse(donneesSauvegardees);

                    document.getElementById('remise').value = donnees.remise || '0';
                    document.getElementById('notes').value = donnees.notes || '';
                    document.getElementById('client_id').value = donnees.client_id || '';
                    paiementPartielCheckbox.checked = !!donnees.paiement_partiel;
                    initialiserPaiements(donnees.paiements || []);

                    if (donnees.panier && donnees.panier.length > 0) {
                        panier = donnees.panier;
                        mettreAJourPanier();
                        showInfoToast('Vente en cours restaurée avec succès !');
                    } else {
                        mettreAJourResumePaiements();
                    }
                } catch (e) {
                    console.error('Erreur lors de la restauration du panier:', e);
                    localStorage.removeItem(STORAGE_KEY);
                }
            }

            // Restaurer le panier au chargement de la page
            restaurerPanier();

            // ============================================
            // GESTION DU SCAN CODE-BARRES
            // ============================================
            const barcodeInput = document.getElementById('barcodeInput');
            let barcodeTimeout = null;
            let isProcessingBarcode = false;

            /**
             * Fonction pour ajouter un produit au panier depuis le code-barres
             * @param {Object} product - Objet produit retourné par l'API
             */
            function ajouterProduitDepuisBarcode(product) {
                // Vérifier si le produit est déjà dans le panier
                const produitExistant = panier.find(p => p.id === product.id);

                if (produitExistant) {
                    // Vérifier le stock disponible uniquement si le stock est géré (stock > 0)
                    // Permettre l'ajout même si stock = 0 car le stock est optionnel
                    const produitCard = document.querySelector(`[data-produit-id="${product.id}"]`);
                    const stock = produitCard ? parseInt(produitCard.dataset.stock) : (product.quantite_stock || 0);

                    if (stock === 0 || stock === null || produitExistant.quantite < stock) {
                        produitExistant.quantite++;
                    } else {
                        showErrorToast('Stock insuffisant pour ce produit !');
                        return;
                    }
                } else {
                    // Ajouter le produit au panier
                    panier.push({
                        id: product.id,
                        nom: product.nom,
                        prix: Math.round(parseFloat(product.prix_vente) || 0),
                        quantite: 1
                    });
                }

                mettreAJourPanier();
                sauvegarderPanier();
                showSuccessToast(`Produit "${product.nom}" ajouté au panier !`);
            }

            /**
             * Fonction pour rechercher un produit par code-barres
             * @param {string} barcode - Le code-barres à rechercher
             */
            async function rechercherProduitParBarcode(barcode) {
                // Ignorer si le champ est vide ou si une recherche est déjà en cours
                if (!barcode || barcode.trim() === '' || isProcessingBarcode) {
                    return;
                }

                isProcessingBarcode = true;
                barcodeInput.disabled = true;

                try {
                    // Mode hors connexion : recherche dans IndexedDB
                    if (window.WmcOffline && !window.WmcOffline.network.isFullyOnline()) {
                        const produitLocal = await window.WmcOffline.db.findProduitByBarcode(barcode.trim());
                        if (produitLocal) {
                            ajouterProduitDepuisBarcode({
                                id: produitLocal.id,
                                nom: produitLocal.nom,
                                prix_vente: produitLocal.prix_vente,
                                quantite_stock: produitLocal.quantite_stock,
                                categorie: produitLocal.categorie,
                                barcode: produitLocal.barcode,
                            });
                        } else {
                            showErrorToast('Produit introuvable en local avec ce code-barres');
                        }
                        return;
                    }

                    // Récupérer le token CSRF pour les en-têtes
                    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute(
                        'content');

                    // Faire la requête API
                    const response = await fetch(
                        `{{ route('api.find-product-by-barcode') }}?barcode=${encodeURIComponent(barcode.trim())}`, {
                            method: 'GET',
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                                'X-CSRF-TOKEN': csrfToken || ''
                            },
                            credentials: 'same-origin'
                        });

                    const data = await response.json();

                    if (data.success && data.product) {
                        ajouterProduitDepuisBarcode(data.product);
                    } else {
                        showErrorToast(data.message || 'Produit introuvable avec ce code-barres');
                    }
                } catch (error) {
                    // Fallback IndexedDB si le réseau échoue
                    if (window.WmcOffline) {
                        const produitLocal = await window.WmcOffline.db.findProduitByBarcode(barcode.trim());
                        if (produitLocal) {
                            ajouterProduitDepuisBarcode({
                                id: produitLocal.id,
                                nom: produitLocal.nom,
                                prix_vente: produitLocal.prix_vente,
                                quantite_stock: produitLocal.quantite_stock,
                                categorie: produitLocal.categorie,
                                barcode: produitLocal.barcode,
                            });
                            return;
                        }
                    }
                    console.error('Erreur lors de la recherche du produit:', error);
                    showErrorToast('Erreur lors de la recherche du produit. Mode hors connexion activé ?');
                } finally {
                    // Réinitialiser l'état
                    isProcessingBarcode = false;
                    barcodeInput.disabled = false;
                    barcodeInput.value = '';
                    barcodeInput.focus();
                }
            }

            /**
             * Gestion de la saisie dans le champ code-barres
             * Supporte deux modes :
             * 1. Scan avec ENTER (scanners USB standard)
             * 2. Détection par délai (fallback pour scanners sans ENTER)
             */
            barcodeInput.addEventListener('keypress', function(e) {
                // Détection de la touche ENTER
                if (e.key === 'Enter' || e.keyCode === 13) {
                    e.preventDefault();
                    const barcode = this.value.trim();
                    if (barcode) {
                        rechercherProduitParBarcode(barcode);
                    }
                    return;
                }

                // Annuler le timeout précédent
                if (barcodeTimeout) {
                    clearTimeout(barcodeTimeout);
                }

                // Détection par délai (fallback) : si aucune saisie pendant 300ms, traiter comme un scan complet
                // Ceci est utile pour les scanners qui n'envoient pas ENTER
                barcodeTimeout = setTimeout(() => {
                    const barcode = this.value.trim();
                    // Ne traiter que si la longueur est raisonnable (code-barres typique : 8-13 caractères)
                    if (barcode.length >= 3 && barcode.length <= 50) {
                        rechercherProduitParBarcode(barcode);
                    }
                }, 300);
            });

            /**
             * Gestion du focus : maintenir le focus sur le champ de scan
             * pour faciliter les scans successifs
             */
            barcodeInput.addEventListener('blur', function() {
                // Remettre le focus après un court délai si on n'est pas en train de cliquer sur un autre élément
                setTimeout(() => {
                    // Vérifier que l'élément actif n'est pas un input ou un bouton
                    const activeElement = document.activeElement;
                    if (activeElement &&
                        activeElement.tagName !== 'INPUT' &&
                        activeElement.tagName !== 'BUTTON' &&
                        activeElement.tagName !== 'TEXTAREA' &&
                        activeElement.tagName !== 'SELECT') {
                        barcodeInput.focus();
                    }
                }, 100);
            });

            // S'assurer que le champ a le focus au chargement de la page
            window.addEventListener('load', function() {
                setTimeout(() => {
                    barcodeInput.focus();
                }, 500);
            });

            // Recherche + pagination catalogue (5 produits par page)
            const POS_CATALOG_PER_PAGE = 5;
            let posCatalogPage = 1;

            function getPosCatalogItems() {
                return Array.from(document.querySelectorAll('#catalogue-produits > .produit-item'));
            }

            function refreshPosCatalogPagination(resetPage) {
                if (resetPage) {
                    posCatalogPage = 1;
                }
                const input = document.getElementById('recherche-produit');
                const q = input ? input.value.toLowerCase().trim() : '';
                const items = getPosCatalogItems();
                const nav = document.getElementById('pos-catalog-pagination');
                const info = document.getElementById('pos-catalog-pagination-info');
                const btnPrev = document.getElementById('pos-catalog-page-prev');
                const btnNext = document.getElementById('pos-catalog-page-next');

                items.forEach((el) => {
                    const nom = el.dataset.nom || '';
                    const cat = el.dataset.categorie || '';
                    const match = !q || nom.includes(q) || cat.includes(q);
                    el.classList.toggle('pos-cat-search-hidden', !match);
                    el.classList.remove('pos-cat-page-hidden');
                });

                const visible = items.filter((el) => !el.classList.contains('pos-cat-search-hidden'));
                const totalPages = Math.max(1, Math.ceil(visible.length / POS_CATALOG_PER_PAGE));
                posCatalogPage = Math.min(Math.max(1, posCatalogPage), totalPages);

                visible.forEach((el, idx) => {
                    const page = Math.floor(idx / POS_CATALOG_PER_PAGE) + 1;
                    if (page !== posCatalogPage) {
                        el.classList.add('pos-cat-page-hidden');
                    }
                });

                if (!nav || !info || !btnPrev || !btnNext) {
                    return;
                }

                if (visible.length <= POS_CATALOG_PER_PAGE) {
                    nav.classList.add('d-none');
                    return;
                }

                nav.classList.remove('d-none');
                info.textContent =
                    `Page ${posCatalogPage} sur ${totalPages} · ${visible.length} produit(s)`;
                btnPrev.disabled = posCatalogPage <= 1;
                btnNext.disabled = posCatalogPage >= totalPages;
            }

            window.refreshPosCatalogPagination = function(resetPage) {
                refreshPosCatalogPagination(resetPage);
            };

            let searchTimer = null;
            document.getElementById('recherche-produit').addEventListener('input', function() {
                clearTimeout(searchTimer);
                searchTimer = setTimeout(() => {
                    refreshPosCatalogPagination(true);
                }, 180);
            });

            document.getElementById('pos-catalog-page-prev')?.addEventListener('click', function() {
                if (posCatalogPage > 1) {
                    posCatalogPage -= 1;
                    refreshPosCatalogPagination(false);
                }
            });

            document.getElementById('pos-catalog-page-next')?.addEventListener('click', function() {
                const visible = getPosCatalogItems().filter((el) => !el.classList.contains('pos-cat-search-hidden'));
                const totalPages = Math.max(1, Math.ceil(visible.length / POS_CATALOG_PER_PAGE));
                if (posCatalogPage < totalPages) {
                    posCatalogPage += 1;
                    refreshPosCatalogPagination(false);
                }
            });

            refreshPosCatalogPagination(false);

            // Ajouter un produit au panier
            document.addEventListener('click', function(e) {
                // Vérifier si le clic est sur le bouton ou son icône
                const bouton = e.target.closest('.ajouter-panier');
                if (!bouton) return;
                
                e.preventDefault();
                e.stopPropagation();
                
                if (!bouton.dataset || !bouton.dataset.produitId) {
                    console.error('Bouton ajouter-panier invalide - pas de data-produit-id');
                    return;
                }
                
                const produitId = parseInt(bouton.dataset.produitId);
                if (isNaN(produitId)) {
                    console.error('ID produit invalide:', bouton.dataset.produitId);
                    return;
                }
                
                const produitCard = bouton.closest('.produit-card');
                if (!produitCard) {
                    console.error('Carte produit introuvable');
                    return;
                }
                
                // Arrondir le prix pour éviter les erreurs de précision float
                const prix = Math.round(parseFloat(produitCard.dataset.prix) || 0);
                const stock = parseInt(produitCard.dataset.stock) || 0;
                
                // Récupérer le nom du produit (chercher dans plusieurs sélecteurs possibles)
                const nomElement = produitCard.querySelector('.pos-product-name') || 
                                 produitCard.querySelector('.card-title') || 
                                 produitCard.querySelector('h6') ||
                                 produitCard.querySelector('[class*="product-name"]');
                const nom = nomElement ? nomElement.textContent.trim() : 'Produit sans nom';
                
                // Vérifier si le produit est déjà dans le panier
                const produitExistant = panier.find(p => p.id === produitId);

                if (produitExistant) {
                    // Vérifier le stock uniquement si le stock est géré (stock > 0)
                    // Permettre l'ajout même si stock = 0 car le stock est optionnel
                    if (stock === 0 || stock === null || produitExistant.quantite < stock) {
                        produitExistant.quantite++;
                    } else {
                        if (typeof showErrorToast === 'function') {
                            showErrorToast('Stock insuffisant pour ce produit !');
                        }
                        return;
                    }
                } else {
                    panier.push({
                        id: produitId,
                        nom: nom,
                        prix: prix, // Prix arrondi
                        quantite: 1
                    });
                }

                mettreAJourPanier();
                sauvegarderPanier(); // Sauvegarder après ajout
                
                // Feedback visuel
                if (typeof showSuccessToast === 'function') {
                    showSuccessToast(`"${nom}" ajouté au panier`);
                }
                
                // Animation sur le bouton
                bouton.style.transform = 'scale(0.9)';
                setTimeout(() => {
                    bouton.style.transform = '';
                }, 200);
            });

            // Mettre à jour l'affichage du panier
            function mettreAJourPanier() {
                const panierVide = document.getElementById('panier-vide');
                const panierContenu = document.getElementById('panier-contenu');
                const panierListe = document.getElementById('panier-liste');

                if (panier.length === 0) {
                    panierVide.style.display = 'block';
                    panierContenu.style.display = 'none';
                    document.getElementById('finaliser-vente').disabled = true;
                } else {
                    panierVide.style.display = 'none';
                    panierContenu.style.display = 'block';
                    document.getElementById('finaliser-vente').disabled = false;

                    // Afficher les produits du panier
                    panierListe.innerHTML = '';
                    total = 0;

                    panier.forEach(produit => {
                        // Arrondir le sous-total pour éviter les erreurs de précision float
                        const sousTotal = Math.round(produit.prix * produit.quantite);
                        total = Math.round(total + sousTotal);

                        const ligne = document.createElement('tr');
                        ligne.className = 'pos-cart-item';
                        ligne.style.borderBottom = '1px solid var(--border-light)';
                        ligne.innerHTML = `
                            <td style="padding: var(--spacing-md);">
                                <div class="fw-semibold" style="color: var(--text-primary);">${produit.nom}</div>
                            </td>
                            <td style="padding: var(--spacing-md);">
                                <div class="input-group input-group-sm pos-quantity-control">
                                    <button class="btn btn-outline-secondary pos-qty-btn" type="button" onclick="modifierQuantite(${produit.id}, -1)" style="border: 1px solid var(--border-color); border-radius: var(--radius-sm) 0 0 var(--radius-sm);">
                                        <i class="fas fa-minus"></i>
                                    </button>
                                    <input type="number" class="form-control text-center pos-qty-input" value="${produit.quantite}" min="1" onchange="modifierQuantite(${produit.id}, 0, this.value)" style="border: 1px solid var(--border-color); border-left: none; border-right: none; padding: var(--spacing-xs); font-weight: 600;">
                                    <button class="btn btn-outline-secondary pos-qty-btn" type="button" onclick="modifierQuantite(${produit.id}, 1)" style="border: 1px solid var(--border-color); border-radius: 0 var(--radius-sm) var(--radius-sm) 0;">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                            </td>
                            <td style="padding: var(--spacing-md);">
                                <span class="fw-semibold" style="color: var(--text-secondary);">${produit.prix.toLocaleString()} FCFA</span>
                            </td>
                            <td style="padding: var(--spacing-md);">
                                <span class="fw-bold" style="color: var(--success-color); font-size: 1rem;">${sousTotal.toLocaleString()} FCFA</span>
                            </td>
                            <td style="padding: var(--spacing-md);">
                                <button class="btn btn-sm pos-remove-btn" onclick="supprimerProduit(${produit.id})" style="background: transparent; border: 1px solid var(--danger-color); color: var(--danger-color); border-radius: var(--radius-sm); padding: var(--spacing-xs) var(--spacing-sm);">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        `;
                        panierListe.appendChild(ligne);
                    });

                    calculerTotal();
                }
            }

            // Modifier la quantité d'un produit
            window.modifierQuantite = function(produitId, delta, nouvelleQuantite = null) {
                const produit = panier.find(p => p.id === produitId);
                if (produit) {
                    if (nouvelleQuantite !== null) {
                        produit.quantite = parseInt(nouvelleQuantite);
                    } else {
                        produit.quantite += delta;
                    }

                    if (produit.quantite <= 0) {
                        supprimerProduit(produitId);
                    } else {
                        mettreAJourPanier();
                        sauvegarderPanier(); // Sauvegarder après modification
                    }
                }
            };

            // Supprimer un produit du panier
            window.supprimerProduit = function(produitId) {
                panier = panier.filter(p => p.id !== produitId);
                mettreAJourPanier();
                sauvegarderPanier(); // Sauvegarder après suppression
            };

            // Vider complètement le panier
            window.viderPanier = function() {
                if (panier.length === 0) {
                    return;
                }

                if (confirm('Êtes-vous sûr de vouloir vider le panier ? Cette action est irréversible.')) {
                    panier = [];
                    document.getElementById('remise').value = '0';
                    document.getElementById('notes').value = '';
                    document.getElementById('client_id').value = '';
                    initialiserPaiements();

                    mettreAJourPanier();
                    masquerErreurPaiements();
                    localStorage.removeItem(STORAGE_KEY); // Supprimer la sauvegarde
                    showSuccessToast('Panier vidé avec succès !');
                }
            };

            // Fonction pour afficher un message de succès élégant (globale)
            window.showSuccessToast = function(message) {
                const notification = document.createElement('div');
                notification.className = 'alert alert-success alert-dismissible fade show position-fixed';
                notification.style.cssText =
                    'top: 20px; right: 20px; z-index: 9999; min-width: 300px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);';
                notification.innerHTML = `
                    <i class="fas fa-check-circle me-2"></i>
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                `;
                document.body.appendChild(notification);
                setTimeout(() => {
                    if (notification.parentNode) {
                        notification.remove();
                    }
                }, 5000);
            };

            // Fonction pour afficher un message d'erreur élégant (globale)
            window.showErrorToast = function(message) {
                const notification = document.createElement('div');
                notification.className = 'alert alert-danger alert-dismissible fade show position-fixed';
                notification.style.cssText =
                    'top: 20px; right: 20px; z-index: 9999; min-width: 300px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);';
                notification.innerHTML = `
                    <i class="fas fa-exclamation-circle me-2"></i>
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                `;
                document.body.appendChild(notification);
                setTimeout(() => {
                    if (notification.parentNode) {
                        notification.remove();
                    }
                }, 7000);
            };

            // Fonction pour afficher un message d'information élégant (globale)
            window.showInfoToast = function(message) {
                const notification = document.createElement('div');
                notification.className = 'alert alert-info alert-dismissible fade show position-fixed';
                notification.style.cssText =
                    'top: 20px; right: 20px; z-index: 9999; min-width: 300px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);';
                notification.innerHTML = `
                    <i class="fas fa-info-circle me-2"></i>
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                `;
                document.body.appendChild(notification);
                setTimeout(() => {
                    if (notification.parentNode) {
                        notification.remove();
                    }
                }, 6000);
            };

            // Fonction pour afficher un message d'avertissement élégant (globale)
            window.showWarningToast = function(message) {
                const notification = document.createElement('div');
                notification.className = 'alert alert-warning alert-dismissible fade show position-fixed';
                notification.style.cssText =
                    'top: 20px; right: 20px; z-index: 9999; min-width: 300px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);';
                notification.innerHTML = `
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                `;
                document.body.appendChild(notification);
                setTimeout(() => {
                    if (notification.parentNode) {
                        notification.remove();
                    }
                }, 6000);
            };

            // Calculer le total
            function calculerTotal() {
                // Arrondir la remise et le total final pour éviter les erreurs de précision float
                const remise = Math.round(parseFloat(document.getElementById('remise').value) || 0);
                totalFinal = Math.max(Math.round(total - remise), 0);

                document.getElementById('sous-total').textContent = total.toLocaleString() + ' FCFA';
                document.getElementById('remise-montant').textContent = remise.toLocaleString() + ' FCFA';
                document.getElementById('total-final').textContent = totalFinal.toLocaleString() + ' FCFA';
                mettreAJourResumePaiements();
            }

            // Écouter les changements de remise
            document.getElementById('remise').addEventListener('input', function() {
                calculerTotal();
                sauvegarderPanier();
            });

            // Sauvegarder lors des changements de notes
            document.getElementById('notes').addEventListener('input', sauvegarderPanier);

            // Sauvegarder lors des changements de client
            document.getElementById('client_id').addEventListener('change', sauvegarderPanier);

            // Finaliser la vente
            document.getElementById('finaliser-vente').addEventListener('click', async function() {
                if (panier.length === 0) {
                    showWarningToast('Le panier est vide ! Veuillez ajouter des produits.');
                    return;
                }

                let paiements = getPaiementsDepuisUI(false);

                if (paiements.length === 0) {
                    const modeDefaut = paiementsContainer.querySelector('.mode-paiement-select')?.value ||
                        'especes';
                    paiements = [{
                        mode: modeDefaut,
                        montant: Math.round(totalFinal)
                    }];
                }

                const montantTotalPaiements = paiements.reduce((somme, paiement) => somme + paiement
                    .montant, 0);
                const paiementPartielActif = paiementPartielCheckbox.checked;

                if (montantTotalPaiements > totalFinal) {
                    afficherErreurPaiements('Le montant réparti dépasse le total de la vente.');
                    return;
                }

                if (totalFinal > 0 && montantTotalPaiements <= 0) {
                    afficherErreurPaiements(
                        'Veuillez saisir au moins un montant de paiement supérieur à zéro.');
                    return;
                }

                if (!paiementPartielActif && totalFinal > 0 && montantTotalPaiements !== totalFinal) {
                    afficherErreurPaiements(
                        'Le montant réparti doit couvrir la totalité du total final (aucun solde restant).'
                    );
                    return;
                }

                if (paiementPartielActif) {
                    if (montantTotalPaiements >= totalFinal) {
                        afficherErreurPaiements(
                            'Pour un paiement partiel, le montant réglé doit être strictement inférieur au total de la vente.'
                        );
                        return;
                    }
                }

                masquerErreurPaiements();

                const clientIdValue = document.getElementById('client_id').value;
                const ventePayload = {
                    produits: panier.map(p => ({
                        id: p.id,
                        quantite: p.quantite,
                        prix_unitaire: p.prix,
                        nom: p.nom,
                    })),
                    remise: parseFloat(document.getElementById('remise').value) || 0,
                    notes: document.getElementById('notes').value,
                    paiements: paiements,
                };

                if (clientIdValue && String(clientIdValue).startsWith('local-')) {
                    ventePayload.client_offline_uuid = String(clientIdValue).replace('local-', '');
                } else if (clientIdValue) {
                    ventePayload.client_id = parseInt(clientIdValue, 10);
                }

                // Mode hors connexion : enregistrer localement
                if (window.WmcOffline && !window.WmcOffline.network.isFullyOnline()) {
                    try {
                        const result = await window.WmcOffline.sync.saveVenteOffline(ventePayload);
                        panier = [];
                        mettreAJourPanier();
                        localStorage.removeItem(STORAGE_KEY);
                        document.getElementById('remise').value = 0;
                        document.getElementById('notes').value = '';
                        document.getElementById('client_id').value = '';
                        showSuccessToast(`Vente enregistrée hors connexion (${result.numero_local}). Synchronisation automatique au retour du réseau.`);
                    } catch (err) {
                        console.error(err);
                        showErrorToast('Impossible d\'enregistrer la vente hors connexion.');
                    }
                    return;
                }

                // Mode en ligne : soumission classique
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '{{ route('ventes.store') }}';

                const csrfToken = document.createElement('input');
                csrfToken.type = 'hidden';
                csrfToken.name = '_token';
                csrfToken.value = '{{ csrf_token() }}';
                form.appendChild(csrfToken);

                panier.forEach((produit, index) => {
                    const produitIdInput = document.createElement('input');
                    produitIdInput.type = 'hidden';
                    produitIdInput.name = `produits[${index}][id]`;
                    produitIdInput.value = produit.id;
                    form.appendChild(produitIdInput);

                    const quantiteInput = document.createElement('input');
                    quantiteInput.type = 'hidden';
                    quantiteInput.name = `produits[${index}][quantite]`;
                    quantiteInput.value = produit.quantite;
                    form.appendChild(quantiteInput);
                });

                const remiseInput = document.createElement('input');
                remiseInput.type = 'hidden';
                remiseInput.name = 'remise';
                remiseInput.value = document.getElementById('remise').value;
                form.appendChild(remiseInput);

                const clientIdInput = document.createElement('input');
                clientIdInput.type = 'hidden';
                clientIdInput.name = 'client_id';
                clientIdInput.value = clientIdValue;
                form.appendChild(clientIdInput);

                const notesInput = document.createElement('input');
                notesInput.type = 'hidden';
                notesInput.name = 'notes';
                notesInput.value = document.getElementById('notes').value;
                form.appendChild(notesInput);

                paiements.forEach((paiement, index) => {
                    const modeInput = document.createElement('input');
                    modeInput.type = 'hidden';
                    modeInput.name = `paiements[${index}][mode]`;
                    modeInput.value = paiement.mode;
                    form.appendChild(modeInput);

                    const montantInput = document.createElement('input');
                    montantInput.type = 'hidden';
                    montantInput.name = `paiements[${index}][montant]`;
                    montantInput.value = paiement.montant;
                    form.appendChild(montantInput);
                });

                localStorage.removeItem(STORAGE_KEY);
                document.body.appendChild(form);
                form.submit();
            });
        });
    </script>

    <!-- Modal pour ajouter un produit -->
    <div class="modal fade" id="ajouterProduitModal" tabindex="-1" aria-labelledby="ajouterProduitModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="ajouterProduitModalLabel">
                        <i class="fas fa-plus-circle me-2"></i>
                        Ajouter un nouveau produit
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="formAjouterProduit" method="POST" action="{{ route('produits.store') }}"
                    enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <div class="row">
                            <!-- Nom du produit -->
                            <div class="col-md-6 mb-3">
                                <label for="nom" class="form-label">
                                    <i class="fas fa-tag me-1 text-primary"></i>
                                    Nom du produit <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control" id="nom" name="nom" required>
                                <div class="invalid-feedback" id="error-nom"></div>
                            </div>

                            <!-- Catégorie -->
                            <div class="col-md-6 mb-3">
                                <label for="categorie" class="form-label">
                                    <i class="fas fa-folder me-1 text-primary"></i>
                                    Catégorie <span class="text-danger">*</span>
                                </label>
                                <select class="form-select" id="categorie" name="categorie" required>
                                    <option value="">Sélectionnez une catégorie</option>
                                    @if (isset($categories) && $categories->count() > 0)
                                        @foreach ($categories as $category)
                                            @php $nomCategorie = data_get($category, 'nom'); @endphp
                                            <option value="{{ $nomCategorie }}">{{ $nomCategorie }}</option>
                                        @endforeach
                                    @endif
                                </select>
                                <div class="invalid-feedback" id="error-categorie"></div>
                                <div class="form-text text-muted">
                                    @if (isset($categories) && $categories->count() > 0)
                                        Choisissez la catégorie qui correspond le mieux à votre produit.
                                        <a href="{{ route('categories.create') }}" class="text-primary"
                                            target="_blank">Créer une catégorie</a>
                                    @else
                                        <span class="text-warning">Aucune catégorie disponible.
                                            <a href="{{ route('categories.create') }}" class="text-primary"
                                                target="_blank">Créer une catégorie</a>
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Description -->
                        <div class="mb-3">
                            <label for="description" class="form-label">
                                <i class="fas fa-align-left me-1 text-primary"></i>
                                Description
                            </label>
                            <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                            <div class="invalid-feedback" id="error-description"></div>
                        </div>

                        <!-- Code-barres -->
                        <div class="mb-3">
                            <label for="barcode" class="form-label">
                                <i class="fas fa-barcode me-1 text-primary"></i>
                                Code-barres <small class="text-muted">(généré automatiquement)</small>
                            </label>
                            <input type="text" class="form-control" id="barcode" name="barcode"
                                placeholder="Généré automatiquement (optionnel : saisir manuellement)" maxlength="50">
                            <div class="invalid-feedback" id="error-barcode"></div>
                            <div class="form-text">
                                <small class="text-muted">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Le code-barres sera généré automatiquement s'il n'est pas renseigné. Il doit être
                                    unique. Compatible avec les scanners USB.
                                </small>
                            </div>
                        </div>

                        <div class="row">
                            <!-- Prix de vente -->
                            <div class="col-md-6 mb-3">
                                <label for="prix_vente" class="form-label">
                                    <i class="fas fa-tag me-1 text-primary"></i>
                                    Prix de vente (FCFA) <span class="text-danger">*</span>
                                </label>
                                <input type="number" step="0.01" min="0" class="form-control"
                                    id="prix_vente" name="prix_vente" required>
                                <div class="invalid-feedback" id="error-prix_vente"></div>
                            </div>

                            <!-- Prix d'achat (optionnel) -->
                            <div class="col-md-6 mb-3">
                                <label for="prix_achat" class="form-label">
                                    <i class="fas fa-shopping-cart me-1 text-secondary"></i>
                                    Prix d'achat (FCFA) <small class="text-muted">(optionnel)</small>
                                </label>
                                <input type="number" step="0.01" min="0" class="form-control"
                                    id="prix_achat" name="prix_achat" placeholder="0">
                                <div class="invalid-feedback" id="error-prix_achat"></div>
                                <div class="form-text">
                                    <small class="text-muted">Pour calculer la marge (optionnel)</small>
                                </div>
                            </div>
                        </div>

                        <!-- Marge calculée -->
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label class="form-label">
                                    <i class="fas fa-chart-line me-1 text-success"></i>
                                    Marge calculée
                                </label>
                                <div class="form-control-plaintext bg-light rounded p-3" id="marge-display-modal">
                                    <span class="text-muted">Saisissez le prix de vente pour voir la marge</span>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <!-- Quantité en stock -->
                            <div class="col-md-4 mb-3">
                                <label for="quantite_stock" class="form-label">
                                    <i class="fas fa-boxes me-1 text-primary"></i>
                                    Quantité en stock <small class="text-muted">(optionnel)</small>
                                </label>
                                <input type="number" min="0" class="form-control" id="quantite_stock"
                                    name="quantite_stock" value="" placeholder="0">
                                <div class="invalid-feedback" id="error-quantite_stock"></div>
                                <div class="form-text">
                                    <small class="text-muted">Laisser vide si non applicable</small>
                                </div>
                            </div>

                            <!-- Stock minimum -->
                            <div class="col-md-4 mb-3">
                                <label for="stock_minimum" class="form-label">
                                    <i class="fas fa-exclamation-triangle me-1 text-warning"></i>
                                    Stock minimum <small class="text-muted">(optionnel)</small>
                                </label>
                                <input type="number" min="0" class="form-control" id="stock_minimum"
                                    name="stock_minimum" value="" placeholder="0">
                                <div class="invalid-feedback" id="error-stock_minimum"></div>
                                <div class="form-text">
                                    <small class="text-muted">Seuil d'alerte (optionnel)</small>
                                </div>
                            </div>

                            <!-- Boutique -->
                            <div class="col-md-4 mb-3">
                                <label for="boutique_id" class="form-label">
                                    <i class="fas fa-store me-1 text-primary"></i>
                                    Boutique <span class="text-danger">*</span>
                                </label>
                                @if (auth()->user()->isEmploye())
                                    <input type="hidden" name="boutique_id" value="{{ auth()->user()->boutique_id }}">
                                    <select class="form-select" id="boutique_id" disabled>
                                        <option value="{{ auth()->user()->boutique_id }}" selected>
                                            {{ auth()->user()->boutique->nom }}
                                        </option>
                                    </select>
                                @else
                                    <select class="form-select" id="boutique_id" name="boutique_id" required>
                                        <option value="">Sélectionnez une boutique</option>
                                        @if (isset($boutiques) && $boutiques->count() > 0)
                                            @foreach ($boutiques as $boutique)
                                                <option value="{{ $boutique->id }}"
                                                    {{ isset($boutiqueId) && $boutiqueId == $boutique->id ? 'selected' : '' }}>
                                                    {{ $boutique->nom }}
                                                </option>
                                            @endforeach
                                        @else
                                            <option value="">Aucune boutique disponible</option>
                                        @endif
                                    </select>
                                @endif
                                <div class="invalid-feedback" id="error-boutique_id"></div>
                            </div>

                            <!-- Fournisseur -->
                            <div class="col-md-4 mb-3">
                                <label for="fournisseur_id" class="form-label">
                                    <i class="fas fa-truck me-1 text-primary"></i>
                                    Fournisseur
                                </label>
                                <select class="form-select" id="fournisseur_id" name="fournisseur_id">
                                    <option value="">Sélectionnez un fournisseur (optionnel)</option>
                                    @if (isset($fournisseurs) && $fournisseurs->count() > 0)
                                        @foreach ($fournisseurs as $fournisseur)
                                            <option value="{{ $fournisseur->id }}">{{ $fournisseur->nom }}</option>
                                        @endforeach
                                    @else
                                        <option value="" disabled>Aucun fournisseur disponible</option>
                                    @endif
                                </select>
                                <div class="invalid-feedback" id="error-fournisseur_id"></div>
                            </div>
                        </div>

                        <!-- Image du produit -->
                        <div class="mb-4">
                            <label for="image" class="form-label">
                                <i class="fas fa-image me-1 text-primary"></i>
                                Image du produit
                            </label>
                            <input type="file" class="form-control" id="image" name="image" accept="image/*">
                            <div class="invalid-feedback" id="error-image"></div>
                            <div class="form-text">
                                Formats acceptés: JPEG, PNG, JPG, GIF (max 2MB)
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times me-2"></i>
                            Annuler
                        </button>
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-plus me-2"></i>
                            Ajouter le produit
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Script pour gérer l'ajout de produit -->
    <script>
        /**
         * Fonction pour ajouter un produit au catalogue dynamiquement
         * Déclarée en dehors de DOMContentLoaded pour être disponible immédiatement
         */
        window.ajouterProduitAuCatalogue = function(produit) {
            const catalogueProduits = document.getElementById('catalogue-produits');

            if (!catalogueProduits) {
                console.error('Élément catalogue-produits introuvable');
                return;
            }

            // Vérifier si le message "Aucun produit disponible" existe et le supprimer
            const cardBody = catalogueProduits.closest('.card-body');
            if (cardBody) {
                const messageVide = cardBody.querySelector('.text-center');
                if (messageVide && messageVide.querySelector('.fa-box')) {
                    messageVide.remove();
                }
            }

            // Créer l'élément HTML du produit
            const produitItem = document.createElement('div');
            produitItem.className = 'mb-2 produit-item';
            produitItem.setAttribute('data-nom', produit.nom.toLowerCase());
            produitItem.setAttribute('data-categorie', (produit.categorie || '').toLowerCase());

            const produitCard = document.createElement('div');
            produitCard.className = 'card produit-card';
            produitCard.setAttribute('data-produit-id', produit.id);
            produitCard.setAttribute('data-prix', produit.prix_vente);
            produitCard.setAttribute('data-stock', produit.quantite_stock || 0);

            // Formatage du prix
            const prixFormate = new Intl.NumberFormat('fr-FR').format(produit.prix_vente);
            const stock = produit.quantite_stock || 0;
            const stockText = stock > 0 ?
                `Stock: ${stock}` :
                '<i class="fas fa-info-circle me-1"></i>Stock non géré';

            // Image du produit - construire l'URL correctement
            let imageUrl = '';
            if (produit.image) {
                // Si l'image commence déjà par http ou /, l'utiliser tel quel
                if (produit.image.startsWith('http') || produit.image.startsWith('/')) {
                    imageUrl = produit.image;
                } else {
                    // Sinon, ajouter le chemin de base
                    imageUrl = '/' + produit.image;
                }
            }
            const imageHtml = produit.image ?
                `<img src="${imageUrl}" alt="${produit.nom}" class="rounded" style="width: 60px; height: 60px; object-fit: cover; border: 1px solid #dee2e6;">` :
                `<div class="bg-light rounded d-flex align-items-center justify-content-center" style="width: 60px; height: 60px; border: 1px solid #dee2e6;"><i class="fas fa-image text-muted"></i></div>`;

            const imageHtmlNew = produit.image ?
                `<img src="${imageUrl}" alt="${produit.nom}" class="pos-product-img">` :
                `<div class="pos-product-placeholder"><i class="fas fa-image"></i></div>`;
            
            produitCard.innerHTML = `
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start gap-3">
                        <div class="pos-product-image">
                            ${imageHtmlNew}
                        </div>
                        <div class="flex-grow-1 pos-product-info">
                            <h6 class="pos-product-name" title="${produit.nom}">${produit.nom}</h6>
                            <small class="pos-product-category">${produit.categorie || ''}</small>
                            <div class="d-flex align-items-center justify-content-between mt-2">
                                <strong class="pos-product-price">${prixFormate} FCFA</strong>
                                ${stock > 0 ? 
                                    `<span class="pos-product-stock"><i class="fas fa-box me-1"></i>${stock}</span>` : 
                                    `<span class="pos-product-stock-empty"><i class="fas fa-info-circle me-1"></i>Stock non géré</span>`
                                }
                            </div>
                        </div>
                        <div class="pos-product-action">
                            <button class="btn ajouter-panier pos-add-btn" data-produit-id="${produit.id}" title="Ajouter au panier">
                                <i class="fas fa-plus"></i>
                            </button>
                        </div>
                    </div>
                </div>
            `;

            produitItem.appendChild(produitCard);

            // Ajouter au début du catalogue pour qu'il soit visible en premier
            catalogueProduits.insertBefore(produitItem, catalogueProduits.firstChild);

            if (typeof window.refreshPosCatalogPagination === 'function') {
                window.refreshPosCatalogPagination(true);
            }

            // Le bouton utilisera automatiquement la logique existante via la délégation d'événements
            // qui est définie sur document.addEventListener('click', ...) dans le script principal

            // Animation d'apparition
            produitItem.style.opacity = '0';
            produitItem.style.transform = 'translateY(-10px)';
            setTimeout(() => {
                produitItem.style.transition = 'all 0.3s ease';
                produitItem.style.opacity = '1';
                produitItem.style.transform = 'translateY(0)';
            }, 10);
        };

        document.addEventListener('DOMContentLoaded', function() {
            // Gérer la soumission du formulaire d'ajout de produit
            document.getElementById('formAjouterProduit').addEventListener('submit', function(e) {
                e.preventDefault();

                const formData = new FormData(this);

                fetch('{{ route('produits.store') }}', {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                .getAttribute('content')
                        }
                    })
                    .then(response => {
                        // Parser la réponse JSON
                        return response.json().then(data => {
                            // Si le status HTTP est 200 et data.success existe, c'est un succès
                            if (response.ok && data.success) {
                                return {
                                    success: true,
                                    produit: data.produit,
                                    message: data.message
                                };
                            } else {
                                // Sinon, c'est une erreur (validation ou autre)
                                return {
                                    success: false,
                                    errors: data.errors || {},
                                    message: data.message || 'Une erreur est survenue'
                                };
                            }
                        }).catch(() => {
                            // Si la réponse n'est pas du JSON valide
                            return {
                                success: false,
                                errors: {},
                                message: 'Erreur lors de la communication avec le serveur'
                            };
                        });
                    })
                    .then(result => {
                        // Réinitialiser les erreurs visuelles
                        document.querySelectorAll('.is-invalid').forEach(el => {
                            el.classList.remove('is-invalid');
                        });
                        document.querySelectorAll('.invalid-feedback').forEach(el => {
                            el.textContent = '';
                            el.style.display = 'none';
                        });

                        if (result.success && result.produit) {
                            // Fermer le modal
                            const modal = bootstrap.Modal.getInstance(document.getElementById(
                                'ajouterProduitModal'));
                            if (modal) {
                                modal.hide();
                            }

                            // Afficher un message de succès
                            if (typeof window.showSuccessToast === 'function') {
                                window.showSuccessToast('Produit ajouté avec succès !');
                            }

                            // Ajouter le produit au catalogue dynamiquement
                            if (typeof window.ajouterProduitAuCatalogue === 'function') {
                                try {
                                    window.ajouterProduitAuCatalogue(result.produit);
                                } catch (error) {
                                    console.error('Erreur lors de l\'ajout du produit au catalogue:', error);
                                    // Fallback: recharger la page en cas d'erreur
                                    window.location.reload();
                                }
                            } else {
                                console.error('Fonction ajouterProduitAuCatalogue non disponible');
                                // Fallback: recharger la page si la fonction n'est pas disponible
                                window.location.reload();
                            }
                        } else {
                            // Afficher les erreurs de validation
                            if (result.errors) {
                                Object.keys(result.errors).forEach(field => {
                                    const input = document.getElementById(field) || document
                                        .querySelector(`[name="${field}"]`);
                                    const errorDiv = document.getElementById(`error-${field}`);

                                    if (input) {
                                        input.classList.add('is-invalid');
                                    }

                                    if (errorDiv) {
                                        errorDiv.textContent = result.errors[field][0];
                                        errorDiv.style.display = 'block';
                                    }
                                });
                            }

                            if (typeof window.showErrorToast === 'function') {
                                window.showErrorToast('Erreur lors de l\'ajout du produit : ' + (result.message ||
                                    'Veuillez vérifier les champs'));
                            } else {
                                alert('Erreur lors de l\'ajout du produit : ' + (result.message ||
                                    'Veuillez vérifier les champs'));
                            }
                        }
                    })
                    .catch(error => {
                        console.error('Erreur:', error);
                        if (typeof window.showErrorToast === 'function') {
                            window.showErrorToast('Erreur lors de l\'ajout du produit');
                        } else {
                            alert('Erreur lors de l\'ajout du produit');
                        }
                    });
            });

            // Réinitialiser le formulaire et les erreurs quand le modal se ferme
            document.getElementById('ajouterProduitModal').addEventListener('hidden.bs.modal', function() {
                document.getElementById('formAjouterProduit').reset();
                // Réinitialiser les erreurs visuelles
                document.querySelectorAll('.is-invalid').forEach(el => {
                    el.classList.remove('is-invalid');
                });
                document.querySelectorAll('.invalid-feedback').forEach(el => {
                    el.textContent = '';
                    el.style.display = 'none';
                });
                // Régénérer le code-barres quand le modal se rouvre
                genererCodeBarresModal();
            });

            // Générer automatiquement le code-barres dans le modal
            function genererCodeBarresModal() {
                const barcodeInput = document.querySelector('#ajouterProduitModal #barcode');
                if (barcodeInput && !barcodeInput.value) {
                    /**
                     * Générer un code-barres EAN-13 unique côté client
                     * Format : 8XXXXXXXXXXX (13 chiffres)
                     */
                    const prefixe = '8';
                    const timestamp = Date.now().toString().slice(-6);
                    const aleatoire = Math.floor(Math.random() * 1000000).toString().padStart(6, '0');
                    const codeUnique = (timestamp + aleatoire).slice(0, 11);
                    const codeBarres = prefixe + codeUnique;

                    barcodeInput.value = codeBarres;
                    barcodeInput.style.backgroundColor = '#f0f8ff';
                    barcodeInput.style.borderColor = '#0d6efd';
                }
            }

            // Générer le code-barres au chargement et à l'ouverture du modal
            genererCodeBarresModal();
            document.getElementById('ajouterProduitModal').addEventListener('shown.bs.modal', function() {
                genererCodeBarresModal();
            });

            // Script pour calculer la marge dans le modal
            const prixAchatModal = document.getElementById('prix_achat');
            const prixVenteModal = document.getElementById('prix_vente');
            const margeDisplayModal = document.getElementById('marge-display-modal');

            if (prixAchatModal && prixVenteModal && margeDisplayModal) {
                function calculerMargeModal() {
                    const achat = parseFloat(prixAchatModal.value) || 0;
                    const vente = parseFloat(prixVenteModal.value) || 0;

                    if (vente > 0) {
                        if (achat > 0) {
                            const marge = vente - achat;
                            const pourcentage = ((marge / achat) * 100).toFixed(1);
                            margeDisplayModal.innerHTML = `
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong class="text-success">${marge.toLocaleString()} FCFA</strong>
                                        <br>
                                        <small class="text-muted">${pourcentage}% de marge</small>
                                    </div>
                                    <div class="text-end">
                                        <small class="text-muted">Prix d'achat: ${achat.toLocaleString()} FCFA</small>
                                        <br>
                                        <small class="text-muted">Prix de vente: ${vente.toLocaleString()} FCFA</small>
                                    </div>
                                </div>
                            `;
                        } else {
                            margeDisplayModal.innerHTML = `
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong class="text-info">${vente.toLocaleString()} FCFA</strong>
                                        <br>
                                        <small class="text-muted">Prix de vente</small>
                                    </div>
                                    <div class="text-end">
                                        <small class="text-muted">Prix d'achat non renseigné</small>
                                        <br>
                                        <small class="text-muted">Marge non calculable</small>
                                    </div>
                                </div>
                            `;
                        }
                    } else {
                        margeDisplayModal.innerHTML =
                            '<span class="text-muted">Saisissez le prix de vente pour voir la marge</span>';
                    }
                }

                prixAchatModal.addEventListener('input', calculerMargeModal);
                prixVenteModal.addEventListener('input', calculerMargeModal);
            }

            // La fonction ajouterProduitAuCatalogue est déclarée en haut du script (avant DOMContentLoaded)
        });
    </script>

    <style>
        /* ============================================
           POINT DE VENTE - DESIGN MODERNE ET SMOOTH
           ============================================ */

        .pos-hero-border {
            border-color: var(--border-light) !important;
        }

        .pos-hero-gradient {
            background: linear-gradient(135deg, var(--bg-primary) 0%, var(--bg-tertiary) 100%);
        }

        .pos-hero-badge {
            background: rgba(var(--primary-color-rgb), 0.2);
            color: var(--primary-color);
            border: 1px solid rgba(var(--primary-color-rgb), 0.35);
            font-weight: 600;
        }

        .pos-hero-title {
            color: var(--text-primary);
            font-size: clamp(1.35rem, 2.5vw, 1.75rem);
        }

        .pos-hero-sub {
            font-size: 0.95rem;
            max-width: 36rem;
        }

        .pos-hero-thumb-wrap {
            border-color: var(--border-light) !important;
        }

        .pos-hero-thumb {
            height: 88px;
            object-fit: cover;
            display: block;
        }

        .pos-hero-thumb-label {
            background: rgba(0, 0, 0, 0.55);
            color: #fff;
            font-weight: 600;
        }

        .pos-hero-banner {
            height: 200px;
            object-fit: cover;
            display: block;
        }

        @media (min-width: 992px) {
            .pos-hero-banner {
                height: 100%;
                min-height: 260px;
                max-height: 320px;
            }
        }

        .pos-hero-banner-caption {
            background: linear-gradient(180deg, rgba(0, 0, 0, 0) 0%, rgba(0, 0, 0, 0.75) 100%);
        }

        .pos-hero-banner-caption small {
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.6);
        }

        .pos-empty-cart-img-wrap {
            border-color: var(--border-light) !important;
            box-shadow: var(--shadow-sm);
        }

        .pos-empty-cart-img {
            height: 140px;
            object-fit: cover;
            opacity: 0.9;
        }

        .pos-layout {
            margin-top: var(--spacing-md);
            display: flex !important;
            flex-wrap: wrap !important;
            align-items: flex-start !important;
        }

        .pos-layout > .pos-panier-col,
        .pos-layout > .pos-catalogue-col {
            display: flex !important;
            flex-direction: column !important;
            align-items: stretch !important;
        }

        .pos-layout > .pos-panier-col > .card,
        .pos-layout > .pos-catalogue-col > .card {
            height: 100% !important;
            display: flex !important;
            flex-direction: column !important;
            margin-bottom: 0 !important;
        }

        .pos-layout > .pos-panier-col > .card > .card-body,
        .pos-layout > .pos-catalogue-col > .card > .card-body {
            flex: 1 !important;
            overflow-y: auto !important;
        }

        @media (min-width: 768px) {
            .pos-layout {
                flex-direction: row !important;
                flex-wrap: nowrap !important;
            }

            .pos-layout > .pos-panier-col {
                order: 1 !important;
                flex: 0 0 66.6667% !important;
                max-width: 66.6667% !important;
                width: 66.6667% !important;
            }

            .pos-layout > .pos-catalogue-col {
                order: 2 !important;
                flex: 0 0 33.3333% !important;
                max-width: 33.3333% !important;
                width: 33.3333% !important;
            }
        }

        @media (max-width: 575.98px) {
            .pos-layout {
                flex-wrap: wrap !important;
            }

            .pos-layout > .pos-panier-col,
            .pos-layout > .pos-catalogue-col {
                flex: 0 0 100% !important;
                max-width: 100% !important;
                width: 100% !important;
            }
        }

        .pos-card {
            transition: all var(--transition);
        }

        .pos-card:hover {
            box-shadow: var(--shadow-lg) !important;
        }

        .pos-card-header {
            padding: var(--spacing-md) var(--spacing-lg) !important;
        }

        /* Champ de scan code-barres */
        .pos-barcode-input-wrapper {
            margin-bottom: var(--spacing-md);
        }

        .pos-barcode-input:focus {
            border-color: var(--primary-color) !important;
            box-shadow: 0 0 0 3px rgba(var(--primary-color-rgb), 0.1) !important;
            outline: none;
        }

        /* Panier vide */
        .pos-empty-cart,
        .pos-empty-products {
            padding: var(--spacing-2xl) var(--spacing-lg);
        }

        .pos-empty-icon {
            opacity: 0.5;
        }

        /* Tableau du panier */
        .pos-table-wrapper {
            border-radius: var(--radius);
            overflow: hidden;
            border: 1px solid var(--border-light);
        }

        .pos-table {
            margin-bottom: 0;
        }

        .pos-table tbody tr {
            transition: all var(--transition);
            border-bottom: 1px solid var(--border-light);
        }

        .pos-table tbody tr:hover {
            background: var(--bg-hover);
        }

        .pos-table tbody td {
            padding: var(--spacing-md);
            vertical-align: middle;
        }

        /* Compactage du bloc Panier de vente */
        .pos-panier-compact .pos-panier-body-compact {
            padding: var(--spacing-md) !important;
        }

        .pos-panier-compact .mb-4 {
            margin-bottom: var(--spacing-md) !important;
        }

        .pos-panier-compact .mt-4 {
            margin-top: var(--spacing-md) !important;
        }

        .pos-panier-compact .mt-3 {
            margin-top: var(--spacing-sm) !important;
        }

        .pos-panier-compact .form-label {
            margin-bottom: 0.35rem !important;
        }

        .pos-panier-compact .input-group-text,
        .pos-panier-compact .form-control,
        .pos-panier-compact .form-select {
            min-height: 36px;
        }

        .pos-panier-compact .card-body {
            padding: var(--spacing-sm) var(--spacing-md);
        }

        .pos-panier-compact #panier-vide {
            padding: var(--spacing-lg) var(--spacing-md) !important;
        }

        .pos-panier-compact #panier-vide .pos-empty-cart-visual {
            max-width: 240px !important;
            margin-bottom: var(--spacing-sm) !important;
        }

        .pos-panier-compact #panier-vide .pos-empty-icon i {
            font-size: 2.6rem !important;
        }

        .pos-panier-compact .pos-table tbody td,
        .pos-panier-compact .pos-table thead th {
            padding: 0.5rem 0.6rem !important;
        }

        .pos-panier-compact .paiements-grid {
            gap: 0.45rem;
            min-height: 0;
        }

        .pos-panier-compact .paiement-card {
            padding: 0.55rem !important;
        }

        .pos-panier-compact .paiement-summary {
            padding: 0.55rem 0.6rem !important;
        }

        .pos-panier-compact .btn-pos {
            padding: var(--spacing-sm) var(--spacing-md) !important;
            font-size: 0.92rem;
        }

        .pos-panier-compact .paiement-card .form-label {
            margin-bottom: 0.2rem !important;
            font-size: 0.8rem !important;
        }

        .pos-panier-compact .paiement-card .input-group-text,
        .pos-panier-compact .paiement-card .form-control,
        .pos-panier-compact .paiement-card .form-select {
            min-height: 32px !important;
            padding-top: 0.2rem !important;
            padding-bottom: 0.2rem !important;
        }

        .pos-panier-compact .pos-payment-card > .card-body {
            padding: 0.55rem 0.65rem !important;
        }

        .pos-panier-compact .pos-payment-card .card-title {
            font-size: 0.9rem !important;
            margin-bottom: 0.2rem !important;
            line-height: 1.15;
        }

        .pos-panier-compact .pos-payment-card p.text-muted.small {
            font-size: 0.78rem !important;
            line-height: 1.15;
        }

        .pos-panier-compact .pos-btn-add-payment {
            padding: 0.28rem 0.65rem !important;
            font-size: 0.82rem !important;
            min-height: 30px !important;
        }

        .pos-panier-compact .paiement-card .badge {
            font-size: 0.68rem !important;
            padding: 0.2rem 0.45rem !important;
        }

        .pos-panier-compact .paiement-card .supprimer-paiement {
            font-size: 0.78rem !important;
            padding: 0.1rem 0.2rem !important;
        }

        .pos-panier-compact .paiement-row-fields {
            row-gap: 0.25rem !important;
            column-gap: 0.45rem !important;
        }

        .pos-panier-compact .paiement-summary p.small,
        .pos-panier-compact .paiement-summary p {
            margin-bottom: 0.15rem !important;
        }

        .pos-panier-compact .paiement-summary h6 {
            font-size: 1rem !important;
            line-height: 1.1;
        }

        .pos-panier-compact #paiements-container + .paiement-summary + .d-flex {
            margin-top: 0.45rem !important;
        }

        .pos-panier-compact .pos-summary-card .card-body,
        .pos-panier-compact .pos-panier-bottom-row .card .card-body {
            padding: 0.6rem 0.7rem !important;
        }

        .pos-panier-compact .pos-summary-item {
            padding: 0.2rem 0 !important;
        }

        /* Grille de produits */
        .pos-products-grid {
            display: flex;
            flex-direction: column;
            gap: var(--spacing-md);
            max-height: calc(100vh - 400px);
            overflow-y: auto;
            padding-right: var(--spacing-xs);
        }

        .produit-item.pos-cat-search-hidden,
        .produit-item.pos-cat-page-hidden {
            display: none !important;
        }

        .pos-catalog-pagination {
            padding-top: 0.5rem;
            border-top: 1px solid var(--border-light);
        }

        .pos-products-grid::-webkit-scrollbar {
            width: 6px;
        }

        .pos-products-grid::-webkit-scrollbar-track {
            background: var(--bg-secondary);
            border-radius: var(--radius-full);
        }

        .pos-products-grid::-webkit-scrollbar-thumb {
            background: var(--border-dark);
            border-radius: var(--radius-full);
        }

        .pos-products-grid::-webkit-scrollbar-thumb:hover {
            background: var(--text-muted);
        }

        /* Cartes de produits */
        .produit-card {
            border: 1px solid var(--border-light) !important;
            border-radius: var(--radius-md) !important;
            transition: all var(--transition);
            background: var(--bg-primary);
            overflow: hidden;
        }

        .produit-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
            border-color: var(--primary-light) !important;
        }

        .produit-card .card-body {
            padding: var(--spacing-md);
        }

        /* Image produit */
        .pos-product-image {
            flex-shrink: 0;
        }

        .pos-product-img {
            width: 70px;
            height: 70px;
            object-fit: cover;
            border-radius: var(--radius);
            border: 2px solid var(--border-light);
        }

        .pos-product-placeholder {
            width: 70px;
            height: 70px;
            background: var(--bg-secondary);
            border-radius: var(--radius);
            border: 2px solid var(--border-light);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--text-muted);
            font-size: 1.5rem;
        }

        /* Informations produit */
        .pos-product-info {
            min-width: 0;
        }

        .pos-product-name {
            font-size: 0.9375rem;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: var(--spacing-xs);
            line-height: 1.3;
            overflow: hidden;
            text-overflow: ellipsis;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
        }

        .pos-product-category {
            display: block;
            color: var(--text-muted);
            font-size: 0.8125rem;
            margin-bottom: var(--spacing-xs);
        }

        .pos-product-price {
            color: var(--success-color);
            font-size: 1rem;
            font-weight: 700;
        }

        .pos-product-stock {
            color: var(--text-muted);
            font-size: 0.8125rem;
            background: var(--bg-secondary);
            padding: 2px 8px;
            border-radius: var(--radius-sm);
        }

        .pos-product-stock-empty {
            color: var(--text-muted);
            font-size: 0.75rem;
            font-style: italic;
        }

        /* Bouton ajouter */
        .pos-product-action {
            flex-shrink: 0;
        }

        .pos-add-btn {
            width: 44px;
            height: 44px;
            border-radius: var(--radius-full);
            background: var(--primary-color) !important;
            color: white !important;
            border: none;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all var(--transition);
            padding: 0;
            box-shadow: 0 2px 8px rgba(var(--primary-color-rgb), 0.2);
        }

        .pos-add-btn:hover {
            background: var(--primary-dark) !important;
            transform: scale(1.1) rotate(90deg);
            box-shadow: 0 4px 16px rgba(var(--primary-color-rgb), 0.4);
        }

        .pos-add-btn:active {
            transform: scale(0.95);
        }

        .pos-add-btn i {
            color: white !important;
            font-size: 1rem;
        }

        /* Recherche */
        .pos-search-wrapper {
            margin-bottom: var(--spacing-md);
        }

        .pos-search-input:focus {
            border-color: var(--primary-color) !important;
            box-shadow: 0 0 0 3px rgba(var(--primary-color-rgb), 0.1) !important;
            outline: none;
        }

        /* Bouton ajouter produit */
        .pos-btn-add {
            transition: all var(--transition);
        }

        .pos-btn-add:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
        }

        /* Carte de paiement */
        .pos-payment-card {
            background: var(--bg-primary) !important;
        }

        .pos-btn-add-payment {
            transition: all var(--transition);
        }

        .pos-btn-add-payment:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(var(--primary-color-rgb), 0.3);
        }

        /* Paiements */
        .paiements-grid {
            display: flex;
            flex-direction: column;
            gap: var(--spacing-md);
            min-height: 100px;
        }

        .paiement-card {
            border: 2px dashed var(--border-color) !important;
            background-color: var(--bg-primary) !important;
            border-radius: var(--radius-md) !important;
            transition: all var(--transition);
            padding: var(--spacing-md) !important;
        }

        .paiement-card:hover {
            border-color: var(--primary-light) !important;
            background: var(--bg-hover) !important;
            transform: translateY(-1px);
        }

        .paiement-row-fields .paiement-row-col {
            margin-bottom: 0 !important;
        }

        @media (min-width: 576px) {
            .paiement-row-fields {
                display: grid;
                grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
                column-gap: var(--spacing-sm);
                row-gap: var(--spacing-xs);
            }

            .paiement-row-fields > .col-12:first-child {
                grid-column: 1 / -1;
            }

            .paiement-row-fields .paiement-row-col {
                width: 100%;
                max-width: 100%;
                flex: initial;
            }
        }

        .paiement-summary {
            background: var(--bg-secondary) !important;
            border-radius: var(--radius-md) !important;
        }

        .paiement-summary h6 {
            font-weight: 700;
            letter-spacing: 0.5px;
            color: var(--text-primary);
        }

        .paiement-summary-row {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: var(--spacing-sm);
        }

        .paiement-summary-row > [class*="col-"] {
            width: 100% !important;
            max-width: 100% !important;
            flex: initial !important;
        }

        .paiement-summary p {
            letter-spacing: 1px;
            color: var(--text-muted);
        }

        /* Résumé de vente */
        .pos-summary-card {
            background: var(--bg-primary) !important;
            border: 1px solid var(--border-light) !important;
        }

        .pos-summary-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: var(--spacing-xs) 0;
        }

        .pos-summary-label {
            color: var(--text-secondary);
            font-size: 0.875rem;
        }

        .pos-summary-value {
            color: var(--text-primary);
            font-weight: 600;
            font-size: 0.9375rem;
        }

        .pos-summary-total {
            padding-top: var(--spacing-sm);
            border-top: 2px solid var(--border-light);
            margin-top: var(--spacing-sm);
        }

        /* Inputs */
        .pos-input-discount:focus,
        .pos-input-notes:focus,
        .pos-client-select:focus {
            border-color: var(--primary-color) !important;
            box-shadow: 0 0 0 3px rgba(var(--primary-color-rgb), 0.1) !important;
            outline: none;
        }

        /* Boutons d'action */
        .btn-pos {
            border-radius: var(--radius-md);
            padding: var(--spacing-md) var(--spacing-lg);
            font-weight: 600;
            transition: all var(--transition);
            font-size: 1rem;
        }

        .pos-btn-finalize:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(16, 185, 129, 0.4);
        }

        .pos-btn-finalize:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            transform: none;
        }

        .pos-btn-clear:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(239, 68, 68, 0.4);
        }

        /* Contrôles de quantité */
        .pos-quantity-control {
            max-width: 120px;
        }

        .pos-qty-btn {
            transition: all var(--transition);
            border-color: var(--border-color) !important;
        }

        .pos-qty-btn:hover {
            background: var(--primary-color) !important;
            border-color: var(--primary-color) !important;
            color: white !important;
        }

        .pos-qty-input {
            font-weight: 600;
            text-align: center;
        }

        .pos-qty-input:focus {
            border-color: var(--primary-color) !important;
            box-shadow: 0 0 0 2px rgba(var(--primary-color-rgb), 0.1) !important;
            outline: none;
        }

        /* Bouton supprimer */
        .pos-remove-btn {
            transition: all var(--transition);
        }

        .pos-remove-btn:hover {
            background: var(--danger-color) !important;
            border-color: var(--danger-color) !important;
            color: white !important;
            transform: scale(1.1);
        }

        /* Ligne du panier */
        .pos-cart-item {
            transition: all var(--transition);
        }

        .pos-cart-item:hover {
            background: var(--bg-hover) !important;
        }

        /* Responsive */
        @media (max-width: 991.98px) {
            .pos-layout {
                flex-direction: column;
            }

            .pos-layout > div {
                margin-bottom: var(--spacing-lg);
            }

            .pos-products-grid {
                max-height: 500px;
            }
        }

        @media (max-width: 767.98px) {
            .pos-table-wrapper {
                overflow-x: auto;
            }

            .pos-table {
                min-width: 600px;
            }

            .paiement-card .row > div {
                text-align: left;
            }

            .pos-barcode-input-wrapper .input-group {
                flex-wrap: nowrap;
            }

            .pos-barcode-input-wrapper .input-group-text:last-child {
                display: none;
            }

            .pos-products-grid {
                max-height: 400px;
            }

            .pos-product-img,
            .pos-product-placeholder {
                width: 50px;
                height: 50px;
            }

            .pos-add-btn {
                width: 36px;
                height: 36px;
            }
        }

        /* Animation pour les produits ajoutés */
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }

        .produit-item {
            animation: slideIn 0.3s ease-out;
        }

        .pos-cart-item {
            animation: fadeIn 0.2s ease-out;
        }

        /* Amélioration générale */
        .pos-layout .card {
            border: none;
            box-shadow: var(--shadow-md);
        }

        .pos-layout .card-header {
            background: var(--bg-primary);
            border-bottom: 2px solid var(--border-light);
        }

        /* Scrollbar personnalisée pour le catalogue */
        .pos-products-grid::-webkit-scrollbar {
            width: 8px;
        }

        .pos-products-grid::-webkit-scrollbar-track {
            background: var(--bg-secondary);
            border-radius: var(--radius-full);
        }

        .pos-products-grid::-webkit-scrollbar-thumb {
            background: var(--border-dark);
            border-radius: var(--radius-full);
        }

        .pos-products-grid::-webkit-scrollbar-thumb:hover {
            background: var(--primary-color);
        }
    </style>
@endsection
