@extends('layouts.app')

@section('content')

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-warehouse me-2"></i>
                        Mouvement de stock
                    </h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('stock.store') }}" class="needs-validation" novalidate>
                        @csrf

                        <div class="row">
                            <!-- Produit -->
                            <div class="col-md-6 mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <label for="produit_id" class="form-label mb-0">
                                        <i class="fas fa-box me-1 text-primary"></i>
                                        Produit <span class="text-danger">*</span>
                                    </label>
                                    <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal"
                                        data-bs-target="#ajouterProduitModal">
                                        <i class="fas fa-plus me-1"></i>
                                        Ajouter un produit
                                    </button>
                                </div>
                                <select class="form-select @error('produit_id') is-invalid @enderror" id="produit_id"
                                    name="produit_id" required>
                                    <option value="">Sélectionnez un produit</option>
                                    @foreach ($produits as $produit)
                                        <option value="{{ $produit->id }}"
                                            {{ old('produit_id', request('produit')) == $produit->id ? 'selected' : '' }}
                                            data-stock="{{ $produit->quantite_stock }}"
                                            data-minimum="{{ $produit->stock_minimum }}">
                                            {{ $produit->nom }} (Stock: {{ $produit->quantite_stock }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('produit_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Type de mouvement -->
                            <div class="col-md-6 mb-3">
                                <label for="type" class="form-label">
                                    <i class="fas fa-exchange-alt me-1 text-primary"></i>
                                    Type de mouvement <span class="text-danger">*</span>
                                </label>
                                <select class="form-select @error('type') is-invalid @enderror" id="type"
                                    name="type" required>
                                    <option value="">Sélectionnez le type</option>
                                    <option value="entree" {{ old('type') == 'entree' ? 'selected' : '' }}>
                                        📥 Entrée de stock
                                    </option>
                                    <option value="sortie" {{ old('type') == 'sortie' ? 'selected' : '' }}>
                                        📤 Sortie de stock
                                    </option>
                                    <option value="ajustement" {{ old('type') == 'ajustement' ? 'selected' : '' }}>
                                        🔧 Ajustement de stock
                                    </option>
                                </select>
                                @error('type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <!-- Quantité -->
                            <div class="col-md-6 mb-3">
                                <label for="quantite" class="form-label">
                                    <i class="fas fa-hashtag me-1 text-primary"></i>
                                    Quantité <span class="text-danger">*</span>
                                </label>
                                <input type="number" min="1"
                                    class="form-control @error('quantite') is-invalid @enderror" id="quantite"
                                    name="quantite" value="{{ old('quantite', 1) }}" required>
                                @error('quantite')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">
                                    <span id="stock-info" class="text-muted">Sélectionnez un produit pour voir le stock
                                        actuel</span>
                                </div>
                            </div>

                            <!-- Motif -->
                            <div class="col-md-6 mb-3">
                                <label for="motif" class="form-label">
                                    <i class="fas fa-comment me-1 text-primary"></i>
                                    Motif <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control @error('motif') is-invalid @enderror"
                                    id="motif" name="motif" value="{{ old('motif') }}"
                                    placeholder="Ex: Réception de commande, Ajustement inventaire..." required>
                                @error('motif')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Informations du produit sélectionné -->
                        <div id="produit-info" class="alert alert-info" style="display: none;">
                            <div class="row">
                                <div class="col-md-4">
                                    <strong>Stock actuel:</strong> <span id="stock-actuel">-</span>
                                </div>
                                <div class="col-md-4">
                                    <strong>Stock minimum:</strong> <span id="stock-minimum">-</span>
                                </div>
                                <div class="col-md-4">
                                    <strong>Nouveau stock:</strong> <span id="nouveau-stock">-</span>
                                </div>
                            </div>
                        </div>

                        <!-- Avertissements -->
                        <div id="avertissement-stock-faible" class="alert alert-warning" style="display: none;">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <strong>Attention:</strong> Ce produit est en stock faible !
                        </div>

                        <div id="avertissement-rupture" class="alert alert-danger" style="display: none;">
                            <i class="fas fa-times-circle me-2"></i>
                            <strong>Attention:</strong> Ce produit est en rupture de stock !
                        </div>

                        <div id="avertissement-sortie" class="alert alert-danger" style="display: none;">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <strong>Attention:</strong> Stock insuffisant pour cette sortie !
                        </div>

                        <!-- Boutons d'action -->
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('stock.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-2"></i>
                                Annuler
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>
                                Enregistrer le mouvement
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Script pour la gestion dynamique -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const produitSelect = document.getElementById('produit_id');
            const typeSelect = document.getElementById('type');
            const quantiteInput = document.getElementById('quantite');
            const stockInfo = document.getElementById('stock-info');
            const produitInfo = document.getElementById('produit-info');
            const stockActuel = document.getElementById('stock-actuel');
            const stockMinimum = document.getElementById('stock-minimum');
            const nouveauStock = document.getElementById('nouveau-stock');
            const avertissementStockFaible = document.getElementById('avertissement-stock-faible');
            const avertissementRupture = document.getElementById('avertissement-rupture');
            const avertissementSortie = document.getElementById('avertissement-sortie');

            function mettreAJourInfos() {
                const option = produitSelect.options[produitSelect.selectedIndex];
                const type = typeSelect.value;
                const quantite = parseInt(quantiteInput.value) || 0;

                if (option.value) {
                    const stock = parseInt(option.dataset.stock);
                    const minimum = parseInt(option.dataset.minimum);

                    // Afficher les informations du produit
                    stockActuel.textContent = stock;
                    stockMinimum.textContent = minimum;

                    // Calculer le nouveau stock
                    let nouveauStockValue = stock;
                    if (type === 'entree' || type === 'ajustement') {
                        nouveauStockValue = stock + quantite;
                    } else if (type === 'sortie') {
                        nouveauStockValue = stock - quantite;
                    }

                    nouveauStock.textContent = nouveauStockValue;

                    // Afficher les avertissements
                    avertissementStockFaible.style.display = stock <= minimum && stock > 0 ? 'block' : 'none';
                    avertissementRupture.style.display = stock === 0 ? 'block' : 'none';
                    avertissementSortie.style.display = type === 'sortie' && quantite > stock ? 'block' : 'none';

                    // Mettre à jour le texte d'information
                    stockInfo.textContent = `Stock actuel: ${stock} | Minimum: ${minimum}`;

                    // Afficher les informations du produit
                    produitInfo.style.display = 'block';
                } else {
                    produitInfo.style.display = 'none';
                    stockInfo.textContent = 'Sélectionnez un produit pour voir le stock actuel';
                }
            }

            // Écouter les changements
            produitSelect.addEventListener('change', mettreAJourInfos);
            typeSelect.addEventListener('change', mettreAJourInfos);
            quantiteInput.addEventListener('input', mettreAJourInfos);

            // Initialiser si un produit est présélectionné
            if (produitSelect.value) {
                mettreAJourInfos();
            }
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
                <form id="formAjouterProduit" method="POST" action="{{ route('produits.store') }}">
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
                                            <option value="{{ $category->nom }}">{{ $category->nom }}</option>
                                        @endforeach
                                    @else
                                        <option value="" disabled>Aucune catégorie disponible</option>
                                    @endif
                                </select>
                            </div>
                        </div>

                        <!-- Prix de vente -->
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="prix_vente" class="form-label">
                                    <i class="fas fa-tag me-1 text-primary"></i>
                                    Prix de vente (FCFA) <span class="text-danger">*</span>
                                </label>
                                <input type="number" step="0.01" min="0" class="form-control"
                                    id="prix_vente" name="prix_vente" required>
                            </div>

                            <!-- Prix d'achat (optionnel) -->
                            <div class="col-md-6 mb-3">
                                <label for="prix_achat" class="form-label">
                                    <i class="fas fa-shopping-cart me-1 text-secondary"></i>
                                    Prix d'achat (FCFA) <small class="text-muted">(optionnel)</small>
                                </label>
                                <input type="number" step="0.01" min="0" class="form-control"
                                    id="prix_achat" name="prix_achat" placeholder="0">
                            </div>
                        </div>

                        <div class="row">
                            <!-- Quantité en stock -->
                            <div class="col-md-4 mb-3">
                                <label for="quantite_stock" class="form-label">
                                    <i class="fas fa-boxes me-1 text-primary"></i>
                                    Quantité en stock <span class="text-danger">*</span>
                                </label>
                                <input type="number" min="0" class="form-control" id="quantite_stock"
                                    name="quantite_stock" value="1" required>
                            </div>

                            <!-- Stock minimum -->
                            <div class="col-md-4 mb-3">
                                <label for="stock_minimum" class="form-label">
                                    <i class="fas fa-exclamation-triangle me-1 text-warning"></i>
                                    Stock minimum <span class="text-danger">*</span>
                                </label>
                                <input type="number" min="0" class="form-control" id="stock_minimum"
                                    name="stock_minimum" value="5" required>
                            </div>

                            <!-- Boutique (auto-rempli) -->
                            <div class="col-md-4 mb-3">
                                <label for="boutique_id" class="form-label">
                                    <i class="fas fa-store me-1 text-primary"></i>
                                    Boutique
                                </label>
                                <select class="form-select" id="boutique_id" name="boutique_id" required>
                                    @if (auth()->user()->isEmploye())
                                        <option value="{{ auth()->user()->boutique_id }}" selected>
                                            {{ auth()->user()->boutique->nom }}
                                        </option>
                                    @else
                                        @if (isset($boutiques) && $boutiques->count() > 0)
                                            @foreach ($boutiques as $boutique)
                                                <option value="{{ $boutique->id }}"
                                                    {{ session('boutique_active') == $boutique->id ? 'selected' : '' }}>
                                                    {{ $boutique->nom }}
                                                </option>
                                            @endforeach
                                        @endif
                                    @endif
                                </select>
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
                            </div>
                        </div>

                        <!-- Description -->
                        <div class="mb-3">
                            <label for="description" class="form-label">
                                <i class="fas fa-align-left me-1 text-primary"></i>
                                Description
                            </label>
                            <textarea class="form-control" id="description" name="description" rows="2"></textarea>
                        </div>

                        <!-- Message d'information -->
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Astuce :</strong> Le produit sera ajouté à votre inventaire et sera immédiatement
                            disponible pour les mouvements de stock.
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
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Fermer le modal
                            const modal = bootstrap.Modal.getInstance(document.getElementById(
                                'ajouterProduitModal'));
                            modal.hide();

                            // Afficher un message de succès élégant
                            showSuccessMessage('Produit ajouté avec succès !');

                            // Recharger la page pour afficher le nouveau produit
                            window.location.reload();
                        } else {
                            showErrorMessage('Erreur lors de l\'ajout du produit : ' + (data.message ||
                                'Erreur inconnue'));
                        }
                    })
                    .catch(error => {
                        console.error('Erreur:', error);
                        showErrorMessage('Erreur lors de l\'ajout du produit');
                    });
            });

            // Réinitialiser le formulaire quand le modal se ferme
            document.getElementById('ajouterProduitModal').addEventListener('hidden.bs.modal', function() {
                document.getElementById('formAjouterProduit').reset();
            });
        });

        // Fonction pour afficher un message de succès
        function showSuccessMessage(message) {
            // Créer l'élément de notification
            const notification = document.createElement('div');
            notification.className = 'alert alert-success alert-dismissible fade show position-fixed';
            notification.style.cssText =
                'top: 20px; right: 20px; z-index: 9999; min-width: 300px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);';
            notification.innerHTML = `
                <i class="fas fa-check-circle me-2"></i>
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            `;

            // Ajouter à la page
            document.body.appendChild(notification);

            // Supprimer automatiquement après 5 secondes
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.remove();
                }
            }, 5000);
        }

        // Fonction pour afficher un message d'erreur
        function showErrorMessage(message) {
            // Créer l'élément de notification
            const notification = document.createElement('div');
            notification.className = 'alert alert-danger alert-dismissible fade show position-fixed';
            notification.style.cssText =
                'top: 20px; right: 20px; z-index: 9999; min-width: 300px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);';
            notification.innerHTML = `
                <i class="fas fa-exclamation-circle me-2"></i>
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            `;

            // Ajouter à la page
            document.body.appendChild(notification);

            // Supprimer automatiquement après 7 secondes
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.remove();
                }
            }, 7000);
        }
    </script>
@endsection
