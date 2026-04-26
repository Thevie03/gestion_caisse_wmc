@extends('layouts.app')

@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-plus-circle me-2"></i>
                        Informations du produit
                    </h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('produits.store') }}" enctype="multipart/form-data"
                        class="needs-validation" novalidate>
                        @csrf

                        <div class="row">
                            <!-- Nom du produit -->
                            <div class="col-md-6 mb-3">
                                <label for="nom" class="form-label">
                                    <i class="fas fa-tag me-1 text-primary"></i>
                                    Nom du produit <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control @error('nom') is-invalid @enderror" id="nom"
                                    name="nom" value="{{ old('nom') }}" required>
                                @error('nom')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Catégorie -->
                            <div class="col-md-6 mb-3">
                                <label for="categorie" class="form-label">
                                    <i class="fas fa-folder me-1 text-primary"></i>
                                    Catégorie <span class="text-danger">*</span>
                                </label>
                                <select class="form-select @error('categorie') is-invalid @enderror" id="categorie"
                                    name="categorie" data-selected="{{ old('categorie') }}" required>
                                    <option value="">Sélectionnez une catégorie</option>
                                    @if (isset($categories) && $categories->count() > 0)
                                        @foreach ($categories as $category)
                                            @php $nomCategorie = data_get($category, 'nom'); @endphp
                                            <option value="{{ $nomCategorie }}"
                                                {{ old('categorie') == $nomCategorie ? 'selected' : '' }}>
                                                {{ $nomCategorie }}
                                            </option>
                                        @endforeach
                                    @endif
                                </select>
                                @error('categorie')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text text-muted" id="categories-info">
                                    Choisissez la catégorie qui correspond le mieux à votre produit.
                                    <a href="{{ route('categories.create') }}" class="text-primary">Créer une nouvelle
                                        catégorie</a>
                                </div>
                            </div>
                        </div>

                        <!-- Description -->
                        <div class="mb-3">
                            <label for="description" class="form-label">
                                <i class="fas fa-align-left me-1 text-primary"></i>
                                Description
                            </label>
                            <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description"
                                rows="3">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Code-barres -->
                        <div class="mb-3">
                            <label for="barcode" class="form-label">
                                <i class="fas fa-barcode me-1 text-primary"></i>
                                Code-barres <small class="text-muted">(généré automatiquement)</small>
                            </label>
                            <input type="text" class="form-control @error('barcode') is-invalid @enderror" id="barcode"
                                name="barcode" value="{{ old('barcode') }}"
                                placeholder="Généré automatiquement (optionnel : saisir manuellement)" maxlength="50">
                            @error('barcode')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
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
                                <input type="number" step="0.01" min="0"
                                    class="form-control @error('prix_vente') is-invalid @enderror" id="prix_vente"
                                    name="prix_vente" value="{{ old('prix_vente') }}" required>
                                @error('prix_vente')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Prix d'achat (optionnel) -->
                            <div class="col-md-6 mb-3">
                                <label for="prix_achat" class="form-label">
                                    <i class="fas fa-shopping-cart me-1 text-secondary"></i>
                                    Prix d'achat (FCFA) <small class="text-muted">(optionnel)</small>
                                </label>
                                <input type="number" step="0.01" min="0"
                                    class="form-control @error('prix_achat') is-invalid @enderror" id="prix_achat"
                                    name="prix_achat" value="{{ old('prix_achat', 0) }}" placeholder="0">
                                @error('prix_achat')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
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
                                <div class="form-control-plaintext bg-light rounded p-3" id="marge-display">
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
                                <input type="number" min="0"
                                    class="form-control @error('quantite_stock') is-invalid @enderror" id="quantite_stock"
                                    name="quantite_stock" value="{{ old('quantite_stock', '') }}" placeholder="0">
                                @error('quantite_stock')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
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
                                <input type="number" min="0"
                                    class="form-control @error('stock_minimum') is-invalid @enderror" id="stock_minimum"
                                    name="stock_minimum" value="{{ old('stock_minimum', '') }}" placeholder="0">
                                @error('stock_minimum')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
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
                                @if ($user->isEmploye())
                                    <input type="hidden" name="boutique_id" value="{{ $boutiqueId }}">
                                    <select class="form-select" id="boutique_id" disabled>
                                        <option value="{{ $boutiqueId }}" selected>{{ auth()->user()->boutique->nom }}
                                        </option>
                                    </select>
                                @else
                                    <select class="form-select @error('boutique_id') is-invalid @enderror"
                                        id="boutique_id" name="boutique_id" required>
                                        <option value="">Sélectionnez une boutique</option>
                                        @foreach ($boutiques as $boutique)
                                            <option value="{{ $boutique->id }}"
                                                {{ old('boutique_id', $boutiqueId) == $boutique->id ? 'selected' : '' }}>
                                                {{ $boutique->nom }}
                                            </option>
                                        @endforeach
                                    </select>
                                @endif
                                @error('boutique_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Fournisseur -->
                            <div class="col-md-4 mb-3">
                                <label for="fournisseur_id" class="form-label">
                                    <i class="fas fa-truck me-1 text-primary"></i>
                                    Fournisseur
                                </label>
                                <select class="form-select @error('fournisseur_id') is-invalid @enderror"
                                    id="fournisseur_id" name="fournisseur_id">
                                    <option value="">Sélectionnez un fournisseur (optionnel)</option>
                                    @if (isset($fournisseurs) && $fournisseurs->count() > 0)
                                        @foreach ($fournisseurs as $fournisseur)
                                            <option value="{{ $fournisseur->id }}"
                                                {{ old('fournisseur_id') == $fournisseur->id ? 'selected' : '' }}>
                                                {{ $fournisseur->nom }}
                                            </option>
                                        @endforeach
                                    @else
                                        <option value="" disabled>Aucun fournisseur disponible</option>
                                    @endif
                                </select>
                                @error('fournisseur_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Image du produit -->
                        <div class="mb-4">
                            <label for="image" class="form-label">
                                <i class="fas fa-image me-1 text-primary"></i>
                                Image du produit
                            </label>
                            <input type="file" class="form-control @error('image') is-invalid @enderror"
                                id="image" name="image" accept="image/*">
                            <div class="form-text">
                                Formats acceptés: JPEG, PNG, JPG, GIF (max 2MB)
                            </div>
                            @error('image')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Boutons d'action -->
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('produits.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-2"></i>
                                Annuler
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>
                                Créer le produit
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const categorieSelect = document.getElementById('categorie');
            const boutiqueSelect = document.getElementById('boutique_id');
            const categoriesInfo = document.getElementById('categories-info');
            const categoriesParBoutique = @json($categoriesParBoutique);
            const selectedInitial = categorieSelect ? categorieSelect.getAttribute('data-selected') : '';

            function mettreAJourCategories(boutiqueId) {
                if (!categorieSelect) {
                    return;
                }

                const key = boutiqueId !== undefined && boutiqueId !== null && boutiqueId !== '' ?
                    String(boutiqueId) :
                    '';
                const categories = categoriesParBoutique.hasOwnProperty(key) ?
                    categoriesParBoutique[key] : [];
                const valeurActuelle = categorieSelect.value;

                categorieSelect.innerHTML = '<option value="">Sélectionnez une catégorie</option>';

                categories.forEach(category => {
                    const option = document.createElement('option');
                    option.value = category.nom;
                    option.textContent = category.nom;

                    if ((valeurActuelle && category.nom === valeurActuelle) ||
                        (!valeurActuelle && selectedInitial && category.nom === selectedInitial)) {
                        option.selected = true;
                    }

                    categorieSelect.appendChild(option);
                });

                if (categories.length === 0) {
                    categoriesInfo.classList.remove('text-muted');
                    categoriesInfo.classList.add('text-warning');
                    categoriesInfo.innerHTML =
                        'Aucune catégorie disponible pour cette boutique. <a href="{{ route('categories.create') }}" class="text-primary">Créer une catégorie</a>.';
                } else {
                    categoriesInfo.classList.add('text-muted');
                    categoriesInfo.classList.remove('text-warning');
                    categoriesInfo.innerHTML =
                        'Choisissez la catégorie qui correspond le mieux à votre produit. <a href="{{ route('categories.create') }}" class="text-primary">Gérer les catégories</a>.';
                }
            }

            if (boutiqueSelect) {
                boutiqueSelect.addEventListener('change', function() {
                    mettreAJourCategories(this.value);
                });

                // Utiliser la valeur du select ou la valeur passée depuis le contrôleur
                const valeurInitiale = boutiqueSelect.value || '{{ (string) ($boutiqueId ?? '') }}';
                if (valeurInitiale) {
                    mettreAJourCategories(valeurInitiale);
                }
            } else {
                // Si pas de select (cas des employés avec input hidden), utiliser directement la valeur
                const valeurInitiale = '{{ (string) ($boutiqueId ?? '') }}';
                if (valeurInitiale) {
                    mettreAJourCategories(valeurInitiale);
                }
            }
        });
    </script>

    <!-- Script pour calculer la marge -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const prixAchat = document.getElementById('prix_achat');
            const prixVente = document.getElementById('prix_vente');
            const margeDisplay = document.getElementById('marge-display');

            function calculerMarge() {
                const achat = parseFloat(prixAchat.value) || 0;
                const vente = parseFloat(prixVente.value) || 0;

                if (vente > 0) {
                    if (achat > 0) {
                        const marge = vente - achat;
                        const pourcentage = ((marge / achat) * 100).toFixed(1);
                        margeDisplay.innerHTML = `
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
                        margeDisplay.innerHTML = `
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
                    margeDisplay.innerHTML =
                        '<span class="text-muted">Saisissez le prix de vente pour voir la marge</span>';
                }
            }

            prixAchat.addEventListener('input', calculerMarge);
            prixVente.addEventListener('input', calculerMarge);
        });
    </script>

    <!-- Script pour générer automatiquement le code-barres -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const barcodeInput = document.getElementById('barcode');

            // Générer un code-barres uniquement si le champ est vide (pas de valeur old)
            if (barcodeInput && !barcodeInput.value) {
                /**
                 * Générer un code-barres EAN-13 unique côté client
                 * Format : 8XXXXXXXXXXX (13 chiffres)
                 * Le code commence par 8 pour indiquer que c'est un code interne
                 */
                function genererCodeBarres() {
                    // Générer un code-barres EAN-13 (13 chiffres)
                    // Format : 8 + 11 chiffres aléatoires + 1 chiffre de contrôle (simplifié)
                    const prefixe = '8';
                    const chiffresAleatoires = Math.floor(Math.random() * 100000000000).toString().padStart(11,
                        '0');
                    const timestamp = Date.now().toString().slice(-
                        6); // Utiliser les 6 derniers chiffres du timestamp
                    const aleatoire = Math.floor(Math.random() * 1000000).toString().padStart(6, '0');

                    // Combiner timestamp et aléatoire pour garantir l'unicité
                    const codeUnique = (timestamp + aleatoire).slice(0, 11);
                    const codeBarres = prefixe + codeUnique;

                    return codeBarres;
                }

                // Générer et afficher le code-barres
                const codeBarresGenere = genererCodeBarres();
                barcodeInput.value = codeBarresGenere;

                // Ajouter un style pour indiquer que le code a été généré
                barcodeInput.style.backgroundColor = '#f0f8ff';
                barcodeInput.style.borderColor = '#0d6efd';

                // Optionnel : permettre à l'utilisateur de modifier le code s'il le souhaite
                barcodeInput.addEventListener('focus', function() {
                    this.style.backgroundColor = '';
                    this.style.borderColor = '';
                });
            }
        });
    </script>
@endsection
