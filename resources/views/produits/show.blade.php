@extends('layouts.app')

@section('content')

    <div class="row">
        <!-- Informations principales -->
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        Informations du produit
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            @if ($produit->image)
                                <img src="{{ asset($produit->image) }}" alt="{{ $produit->nom }}"
                                    class="img-fluid rounded shadow-sm">
                            @else
                                <div class="bg-light rounded d-flex align-items-center justify-content-center"
                                    style="height: 200px;">
                                    <div class="text-center">
                                        <i class="fas fa-image fa-3x text-muted mb-2"></i>
                                        <p class="text-muted">Aucune image</p>
                                    </div>
                                </div>
                            @endif
                        </div>
                        <div class="col-md-8">
                            <table class="table table-borderless">
                                <tr>
                                    <td class="fw-bold">Nom:</td>
                                    <td>{{ $produit->nom }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Code produit:</td>
                                    <td><span class="badge bg-secondary">{{ $produit->code_produit }}</span></td>
                                </tr>
                                <tr>
                                    <td class="fw-bold" style="vertical-align: middle; font-size: 1.05em;">Code-barres:</td>
                                    <td>
                                        @if ($produit->barcode)
                                            <div class="code-barcode-container mb-2">
                                                <div class="bg-dark text-white rounded p-3 d-inline-block"
                                                    style="min-width: 350px;">
                                                    <div class="d-flex align-items-center justify-content-center gap-3">
                                                        <i class="fas fa-barcode fa-2x"></i>
                                                        <span
                                                            style="font-size: 1.5em; font-weight: bold; letter-spacing: 3px; font-family: 'Courier New', monospace;">
                                                            {{ $produit->barcode }}
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="mt-2">
                                                <small class="text-muted">
                                                    <i class="fas fa-info-circle me-1"></i>
                                                    Scannez ce code avec un lecteur de code-barres
                                                </small>
                                            </div>
                                        @else
                                            <span class="text-muted fst-italic">Aucun code-barres défini</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Catégorie:</td>
                                    <td><span class="badge bg-primary">{{ $produit->categorie }}</span></td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Description:</td>
                                    <td>{{ $produit->description ?: 'Aucune description' }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Boutique:</td>
                                    <td><span class="badge bg-info">{{ $produit->boutique->nom }}</span></td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Statut:</td>
                                    <td>
                                        @if ($produit->actif)
                                            <span class="badge bg-success">Actif</span>
                                        @else
                                            <span class="badge bg-danger">Inactif</span>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Statistiques de vente -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-chart-bar me-2"></i>
                        Statistiques de vente
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-4">
                            <div class="border-end">
                                <h3 class="text-primary">{{ $stats['ventes_total'] }}</h3>
                                <p class="text-muted mb-0">Unités vendues</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="border-end">
                                <h3 class="text-success">{{ number_format($stats['chiffre_affaires'], 0, ',', ' ') }}
                                    FCFA</h3>
                                <p class="text-muted mb-0">Chiffre d'affaires</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <h3 class="text-info">
                                @if ($stats['derniere_vente'])
                                    {{ $stats['derniere_vente']->created_at->format('d/m/Y') }}
                                @else
                                    Jamais
                                @endif
                            </h3>
                            <p class="text-muted mb-0">Dernière vente</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Informations financières et stock -->
        <div class="col-lg-4">
            <!-- Prix et marge -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-money-bill-wave me-2"></i>
                        Informations financières
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6">
                            <div class="border-end">
                                <h4 class="text-primary">{{ number_format($produit->prix_achat, 0, ',', ' ') }} FCFA
                                </h4>
                                <small class="text-muted">Prix d'achat</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <h4 class="text-success">{{ number_format($produit->prix_vente, 0, ',', ' ') }} FCFA</h4>
                            <small class="text-muted">Prix de vente</small>
                        </div>
                    </div>
                    <hr>
                    <div class="text-center">
                        <h3 class="text-warning">{{ number_format($produit->marge, 0, ',', ' ') }} FCFA</h3>
                        <p class="text-muted mb-0">Marge: {{ number_format($produit->marge_pourcentage, 1) }}%</p>
                    </div>
                </div>
            </div>

            <!-- Stock -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-boxes me-2"></i>
                        Gestion du stock
                    </h5>
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        <h2
                            class="{{ $produit->isStockFaible() ? 'text-warning' : ($produit->quantite_stock == 0 ? 'text-danger' : 'text-success') }}">
                            {{ $produit->quantite_stock }}
                        </h2>
                        <p class="text-muted">Unités en stock</p>

                        @if ($produit->isStockFaible())
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                Stock faible ! Minimum: {{ $produit->stock_minimum }}
                            </div>
                        @elseif($produit->quantite_stock == 0)
                            <div class="alert alert-danger">
                                <i class="fas fa-times-circle me-2"></i>
                                En rupture de stock !
                            </div>
                        @else
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle me-2"></i>
                                Stock disponible
                            </div>
                        @endif
                    </div>

                    <!-- Ajustement de stock -->
                    <button class="btn btn-primary w-100" data-bs-toggle="modal" data-bs-target="#ajusterStockModal">
                        <i class="fas fa-edit me-2"></i>
                        Ajuster le stock
                    </button>
                </div>
            </div>

            <!-- Mouvements de stock récents -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-history me-2"></i>
                        Mouvements récents
                    </h5>
                </div>
                <div class="card-body">
                    @if ($produit->mouvementsStock->count() > 0)
                        <div class="list-group list-group-flush">
                            @foreach ($produit->mouvementsStock->take(5) as $mouvement)
                                <div class="list-group-item px-0">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <strong
                                                class="{{ $mouvement->type === 'entree' ? 'text-success' : 'text-danger' }}">
                                                {{ $mouvement->type === 'entree' ? '+' : '-' }}{{ $mouvement->quantite }}
                                            </strong>
                                            <br>
                                            <small class="text-muted">{{ $mouvement->motif }}</small>
                                        </div>
                                        <div class="text-end">
                                            <small
                                                class="text-muted">{{ $mouvement->created_at->format('d/m H:i') }}</small>
                                            <br>
                                            <small class="text-muted">{{ $mouvement->user->name }}</small>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-muted text-center">Aucun mouvement de stock</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Modal pour ajuster le stock -->
    <div class="modal fade" id="ajusterStockModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Ajuster le stock</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="{{ route('produits.ajuster-stock', $produit) }}">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="type" class="form-label">Type d'ajustement</label>
                            <select class="form-select" id="type" name="type" required>
                                <option value="entree">Entrée de stock</option>
                                <option value="sortie">Sortie de stock</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="quantite" class="form-label">Quantité</label>
                            <input type="number" class="form-control" id="quantite" name="quantite" min="1"
                                required>
                        </div>
                        <div class="mb-3">
                            <label for="motif" class="form-label">Motif</label>
                            <textarea class="form-control" id="motif" name="motif" rows="3" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-primary">Ajuster</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Script pour la confirmation de suppression -->
    <script>
        function confirmerSuppression(nomProduit, urlSuppression) {
            // Créer une boîte de dialogue personnalisée avec Bootstrap
            const modalHtml = `
                <div class="modal fade" id="modalSuppression" tabindex="-1" aria-labelledby="modalSuppressionLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header bg-danger text-white">
                                <h5 class="modal-title" id="modalSuppressionLabel">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    Confirmation de suppression
                                </h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="text-center mb-3">
                                    <i class="fas fa-trash fa-3x text-danger mb-3"></i>
                                    <h4>Êtes-vous sûr de vouloir supprimer ce produit ?</h4>
                                </div>
                                <div class="alert alert-warning">
                                    <strong>Produit :</strong> ${nomProduit}
                                </div>
                                <div class="alert alert-danger">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    <strong>Attention :</strong> Cette action est irréversible ! Le produit sera définitivement supprimé de votre inventaire.
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                    <i class="fas fa-times me-2"></i>
                                    Annuler
                                </button>
                                <form method="POST" action="${urlSuppression}" style="display: inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger">
                                        <i class="fas fa-trash me-2"></i>
                                        Supprimer définitivement
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            `;

            // Supprimer l'ancien modal s'il existe
            const existingModal = document.getElementById('modalSuppression');
            if (existingModal) {
                existingModal.remove();
            }

            // Ajouter le nouveau modal au DOM
            document.body.insertAdjacentHTML('beforeend', modalHtml);

            // Afficher le modal
            const modal = new bootstrap.Modal(document.getElementById('modalSuppression'));
            modal.show();
        }
    </script>
@endsection
