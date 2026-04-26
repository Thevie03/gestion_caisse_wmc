@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-plus-circle me-2"></i>
                            Créer une nouvelle catégorie
                        </h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('categories.store') }}" class="needs-validation" novalidate>
                            @csrf

                            <div class="row">
                                <!-- Boutique -->
                                <div class="col-md-6 mb-3">
                                    <label for="boutique_id" class="form-label">
                                        <i class="fas fa-store me-1 text-primary"></i>
                                        Boutique <span class="text-danger">*</span>
                                    </label>
                                    @if ($user->isEmploye())
                                        <input type="hidden" name="boutique_id" value="{{ $boutiqueId }}">
                                        <input type="text" class="form-control"
                                            value="{{ auth()->user()->boutique->nom }}" disabled>
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
                                        @error('boutique_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    @endif
                                </div>

                                <!-- Nom de la catégorie -->
                                <div class="col-md-6 mb-3">
                                    <label for="nom" class="form-label">
                                        <i class="fas fa-tag me-1 text-primary"></i>
                                        Nom de la catégorie <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" class="form-control @error('nom') is-invalid @enderror"
                                        id="nom" name="nom" value="{{ old('nom') }}" required>
                                    @error('nom')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Icône -->
                                <div class="col-md-6 mb-3">
                                    <label for="icone" class="form-label">
                                        <i class="fas fa-icons me-1 text-primary"></i>
                                        Icône
                                    </label>
                                    <select class="form-select @error('icone') is-invalid @enderror" id="icone"
                                        name="icone">
                                        <option value="">Aucune icône (facultatif)</option>
                                        @foreach ($icones as $value => $label)
                                            <option value="{{ $value }}"
                                                {{ old('icone') == $value ? 'selected' : '' }}>
                                                <i class="{{ $value }}"></i> {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('icone')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="row">
                                <!-- Couleur -->
                                <div class="col-md-6 mb-3">
                                    <label for="couleur" class="form-label">
                                        <i class="fas fa-palette me-1 text-primary"></i>
                                        Couleur
                                    </label>
                                    <select class="form-select @error('couleur') is-invalid @enderror" id="couleur"
                                        name="couleur">
                                        <option value="">Aucune couleur (facultatif)</option>
                                        @foreach ($couleurs as $value => $label)
                                            <option value="{{ $value }}"
                                                {{ old('couleur') == $value ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('couleur')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Aperçu -->
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">
                                        <i class="fas fa-eye me-1 text-primary"></i>
                                        Aperçu
                                    </label>
                                    <div class="border rounded p-3 bg-light">
                                        <div class="d-flex align-items-center">
                                            <i id="preview-icon" class="fas fa-tag text-secondary fa-lg me-2"></i>
                                            <span id="preview-name" class="fw-bold">Nom de la catégorie</span>
                                        </div>
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
                                    rows="3" placeholder="Décrivez cette catégorie (optionnel)">{{ old('description') }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Boutons -->
                            <div class="d-flex justify-content-between">
                                <a href="{{ route('categories.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left me-2"></i>
                                    Retour
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>
                                    Créer la catégorie
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const nomInput = document.getElementById('nom');
            const iconeSelect = document.getElementById('icone');
            const couleurSelect = document.getElementById('couleur');
            const previewIcon = document.getElementById('preview-icon');
            const previewName = document.getElementById('preview-name');

            // Mettre à jour l'aperçu
            function updatePreview() {
                const nom = nomInput.value || 'Nom de la catégorie';
                const icone = iconeSelect.value || 'fas fa-tag';
                const couleur = couleurSelect.value || 'secondary';

                previewName.textContent = nom;
                previewIcon.className = `${icone} text-${couleur} fa-lg me-2`;
            }

            // Écouter les changements
            nomInput.addEventListener('input', updatePreview);
            iconeSelect.addEventListener('change', updatePreview);
            couleurSelect.addEventListener('change', updatePreview);

            // Aperçu initial
            updatePreview();
        });
    </script>
@endsection
