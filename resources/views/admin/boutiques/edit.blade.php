@extends('layouts.app')

@section('content')
    <div class="container-fluid px-4 py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <nav aria-label="breadcrumb" class="mb-2">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('admin.boutiques.index') }}"
                                class="text-decoration-none">Boutiques</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.boutiques.show', $boutique) }}"
                                class="text-decoration-none">{{ $boutique->nom }}</a></li>
                        <li class="breadcrumb-item active">Modifier</li>
                    </ol>
                </nav>
                <h1 class="h3 mb-1 fw-bold">Modifier la boutique : {{ $boutique->nom }}</h1>
                <p class="text-muted mb-0">Mettre à jour les informations de la boutique</p>
            </div>
            <div>
                <a href="{{ route('admin.boutiques.show', $boutique) }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Retour
                </a>
            </div>
        </div>

        <form method="POST" action="{{ route('admin.boutiques.update', $boutique) }}" enctype="multipart/form-data"
            class="row g-4">
            @csrf
            @method('PUT')

            <!-- Informations principales -->
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0 text-white">
                            <i class="fas fa-store me-2"></i>Informations de la boutique
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-12">
                                <label for="nom" class="form-label fw-semibold">
                                    Nom de la boutique <span class="text-danger">*</span>
                                </label>
                                <div class="input-group input-group-lg">
                                    <span class="input-group-text"><i class="fas fa-tag"></i></span>
                                    <input type="text" class="form-control @error('nom') is-invalid @enderror"
                                        id="nom" name="nom" value="{{ old('nom', $boutique->nom) }}" required>
                                    @error('nom')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-12">
                                <label for="adresse" class="form-label fw-semibold">Adresse</label>
                                <div class="input-group input-group-lg">
                                    <span class="input-group-text"><i class="fas fa-map-marker-alt"></i></span>
                                    <textarea class="form-control @error('adresse') is-invalid @enderror" id="adresse" name="adresse" rows="2">{{ old('adresse', $boutique->adresse) }}</textarea>
                                    @error('adresse')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label for="telephone" class="form-label fw-semibold">
                                    Téléphone <span class="text-danger">*</span>
                                </label>
                                <div class="input-group input-group-lg">
                                    <span class="input-group-text"><i class="fas fa-phone"></i></span>
                                    <input type="text" class="form-control @error('telephone') is-invalid @enderror"
                                        id="telephone" name="telephone"
                                        value="{{ old('telephone', $boutique->telephone) }}" required>
                                    @error('telephone')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label for="email" class="form-label fw-semibold">Email</label>
                                <div class="input-group input-group-lg">
                                    <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                    <input type="email" class="form-control @error('email') is-invalid @enderror"
                                        id="email" name="email" value="{{ old('email', $boutique->email) }}">
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label for="devise" class="form-label fw-semibold">
                                    Devise <span class="text-danger">*</span>
                                </label>
                                <div class="input-group input-group-lg">
                                    <span class="input-group-text"><i class="fas fa-money-bill-wave"></i></span>
                                    <input type="text" class="form-control @error('devise') is-invalid @enderror"
                                        id="devise" name="devise"
                                        value="{{ old('devise', $boutique->devise ?? 'FCFA') }}" required>
                                    @error('devise')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label for="actif" class="form-label fw-semibold">Statut</label>
                                <div class="input-group input-group-lg">
                                    <span class="input-group-text"><i class="fas fa-toggle-on"></i></span>
                                    <select class="form-select @error('actif') is-invalid @enderror" id="actif"
                                        name="actif">
                                        <option value="1" {{ old('actif', $boutique->actif) ? 'selected' : '' }}>
                                            Active</option>
                                        <option value="0" {{ !old('actif', $boutique->actif) ? 'selected' : '' }}>
                                            Inactive</option>
                                    </select>
                                    @error('actif')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Administrateur -->
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0 text-white">
                            <i class="fas fa-user-tie me-2"></i>Administrateur de la boutique
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Nom complet <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('owner_name') is-invalid @enderror"
                                name="owner_name" value="{{ old('owner_name', $boutique->owner?->name) }}" required>
                            @error('owner_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control @error('owner_email') is-invalid @enderror"
                                name="owner_email" value="{{ old('owner_email', $boutique->owner?->email) }}" required>
                            @error('owner_email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Téléphone <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('owner_telephone') is-invalid @enderror"
                                name="owner_telephone" value="{{ old('owner_telephone', $boutique->owner?->telephone) }}"
                                required>
                            @error('owner_telephone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-0">
                            <label class="form-label fw-semibold">Nouveau mot de passe</label>
                            <input type="password" class="form-control @error('owner_password') is-invalid @enderror"
                                name="owner_password" placeholder="Laisser vide pour ne pas changer">
                            @error('owner_password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0 text-white">
                            <i class="fas fa-image me-2"></i>Logo de la boutique
                        </h5>
                    </div>
                    <div class="card-body">
                        @if ($boutique->logo)
                            <div class="mb-3 text-center">
                                <label class="form-label fw-semibold">Logo actuel</label>
                                <div class="mb-3">
                                    <img src="{{ $boutique->logo }}" alt="Logo actuel" class="img-thumbnail"
                                        style="max-width: 100%; max-height: 200px;">
                                </div>
                            </div>
                        @endif

                        <div class="mb-3">
                            <label for="logo" class="form-label fw-semibold">Nouveau logo</label>
                            <input type="file" class="form-control @error('logo') is-invalid @enderror"
                                id="logo" name="logo" accept="image/*" onchange="previewLogo(this)">
                            @error('logo')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">
                                Formats acceptés : JPEG, PNG, JPG, GIF, SVG. Taille max : 2MB
                            </small>
                        </div>

                        <div class="mb-3">
                            <label for="shared_hero_image" class="form-label fw-semibold">Image commune des onglets</label>
                            <input type="file" class="form-control @error('shared_hero_image') is-invalid @enderror"
                                id="shared_hero_image" name="shared_hero_image" accept="image/*">
                            @error('shared_hero_image')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">
                                Cette image sera identique sur tableau de bord, point de vente, produits/articles et stock (max 4MB).
                            </small>
                        </div>

                        @if ($boutique->pos_banner_image)
                            <div class="mb-3 text-center">
                                <label class="form-label fw-semibold">Image commune actuelle</label>
                                <div>
                                    <img src="{{ asset('images/pos/' . basename($boutique->pos_banner_image)) }}"
                                        alt="Image commune actuelle" class="img-thumbnail"
                                        style="max-width: 100%; max-height: 200px;">
                                </div>
                            </div>
                        @endif

                        <div id="logo-preview" class="text-center mb-3" style="display: none;">
                            <label class="form-label fw-semibold">Aperçu du nouveau logo</label>
                            <div>
                                <img id="logo-preview-img" src="" alt="Aperçu" class="img-thumbnail"
                                    style="max-width: 100%; max-height: 200px;">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Boutons d'action -->
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <a href="{{ route('admin.boutiques.show', $boutique) }}" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-2"></i>Annuler
                            </a>
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-save me-2"></i>Enregistrer les modifications
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <script>
        function previewLogo(input) {
            const preview = document.getElementById('logo-preview');
            const previewImg = document.getElementById('logo-preview-img');

            if (input.files && input.files[0]) {
                const reader = new FileReader();

                reader.onload = function(e) {
                    previewImg.src = e.target.result;
                    preview.style.display = 'block';
                };

                reader.readAsDataURL(input.files[0]);
            } else {
                preview.style.display = 'none';
            }
        }
    </script>
@endsection
