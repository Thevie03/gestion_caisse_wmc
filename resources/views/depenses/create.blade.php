@extends('layouts.app')

@section('content')

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-receipt me-2"></i>
                        Informations de la dépense
                    </h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('depenses.store') }}" class="needs-validation" novalidate>
                        @csrf

                        <div class="row">
                            <!-- Description -->
                            <div class="col-md-8 mb-3">
                                <label for="description" class="form-label">
                                    <i class="fas fa-align-left me-1 text-primary"></i>
                                    Description <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control @error('description') is-invalid @enderror"
                                    id="description" name="description" value="{{ old('description') }}"
                                    placeholder="Ex: Paiement loyer, Achat fournitures..." required>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Catégorie -->
                            <div class="col-md-4 mb-3">
                                <label for="categorie" class="form-label">
                                    <i class="fas fa-tags me-1 text-primary"></i>
                                    Catégorie <span class="text-danger">*</span>
                                </label>
                                <select class="form-select @error('categorie') is-invalid @enderror" id="categorie"
                                    name="categorie" required>
                                    <option value="">Sélectionnez une catégorie</option>
                                    <option value="Loyer" {{ old('categorie') == 'Loyer' ? 'selected' : '' }}>Loyer
                                    </option>
                                    <option value="Électricité"
                                        {{ old('categorie') == 'Électricité' ? 'selected' : '' }}>Électricité</option>
                                    <option value="Eau" {{ old('categorie') == 'Eau' ? 'selected' : '' }}>Eau
                                    </option>
                                    <option value="Transport" {{ old('categorie') == 'Transport' ? 'selected' : '' }}>
                                        Transport</option>
                                    <option value="Fournitures"
                                        {{ old('categorie') == 'Fournitures' ? 'selected' : '' }}>Fournitures</option>
                                    <option value="Marketing" {{ old('categorie') == 'Marketing' ? 'selected' : '' }}>
                                        Marketing</option>
                                    <option value="Maintenance"
                                        {{ old('categorie') == 'Maintenance' ? 'selected' : '' }}>Maintenance</option>
                                    <option value="Formation" {{ old('categorie') == 'Formation' ? 'selected' : '' }}>
                                        Formation</option>
                                    <option value="Autres" {{ old('categorie') == 'Autres' ? 'selected' : '' }}>Autres
                                    </option>
                                </select>
                                @error('categorie')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <!-- Montant -->
                            <div class="col-md-4 mb-3">
                                <label for="montant" class="form-label">
                                    <i class="fas fa-money-bill-wave me-1 text-primary"></i>
                                    Montant (FCFA) <span class="text-danger">*</span>
                                </label>
                                <input type="number" step="0.01" min="0.01"
                                    class="form-control @error('montant') is-invalid @enderror" id="montant"
                                    name="montant" value="{{ old('montant') }}" placeholder="0.00" required>
                                @error('montant')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Date -->
                            <div class="col-md-4 mb-3">
                                <label for="date" class="form-label">
                                    <i class="fas fa-calendar me-1 text-primary"></i>
                                    Date <span class="text-danger">*</span>
                                </label>
                                <input type="date" class="form-control @error('date') is-invalid @enderror"
                                    id="date" name="date" value="{{ old('date', date('Y-m-d')) }}" required>
                                @error('date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Boutique -->
                            <div class="col-md-4 mb-3">
                                <label for="boutique_id" class="form-label">
                                    <i class="fas fa-store me-1 text-primary"></i>
                                    Boutique <span class="text-danger">*</span>
                                </label>
                                <select class="form-select @error('boutique_id') is-invalid @enderror" id="boutique_id"
                                    name="boutique_id" required>
                                    <option value="">Sélectionnez une boutique</option>
                                    @foreach ($boutiques as $boutique)
                                        <option value="{{ $boutique->id }}"
                                            {{ old('boutique_id', session('boutique_active')) == $boutique->id ? 'selected' : '' }}>
                                            {{ $boutique->nom }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('boutique_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Justificatif -->
                        <div class="mb-3">
                            <label for="justificatif" class="form-label">
                                <i class="fas fa-file-alt me-1 text-primary"></i>
                                Justificatif (optionnel)
                            </label>
                            <textarea class="form-control @error('justificatif') is-invalid @enderror" id="justificatif" name="justificatif"
                                rows="3" placeholder="Ajoutez des détails ou justifications pour cette dépense...">{{ old('justificatif') }}</textarea>
                            @error('justificatif')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">
                                <small class="text-muted">Ex: Numéro de facture, nom du fournisseur, etc.</small>
                            </div>
                        </div>

                        <!-- Informations supplémentaires -->
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Astuce :</strong> Cette dépense sera automatiquement associée à votre compte et à la
                            boutique sélectionnée.
                        </div>

                        <!-- Boutons d'action -->
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('depenses.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-2"></i>
                                Annuler
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>
                                Enregistrer la dépense
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Script pour améliorer l'expérience utilisateur -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Auto-focus sur le champ description
            document.getElementById('description').focus();

            // Validation en temps réel du montant
            const montantInput = document.getElementById('montant');
            montantInput.addEventListener('input', function() {
                const value = parseFloat(this.value);
                if (value < 0.01) {
                    this.setCustomValidity('Le montant doit être supérieur à 0');
                } else {
                    this.setCustomValidity('');
                }
            });

            // Formatage automatique du montant
            montantInput.addEventListener('blur', function() {
                if (this.value) {
                    this.value = parseFloat(this.value).toFixed(2);
                }
            });
        });
    </script>
@endsection




































