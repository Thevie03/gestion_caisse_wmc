@extends('layouts.app')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="mb-0">
                        <i class="fas fa-truck-loading me-2 text-primary"></i>
                        Ajouter un fournisseur
                    </h2>
                    <p class="text-muted mb-0">Remplissez les informations du nouveau fournisseur</p>
                </div>
                <a href="{{ route('fournisseurs.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i>
                    Retour à la liste
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-truck me-2"></i>
                        Informations du fournisseur
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('fournisseurs.store') }}" method="POST">
                        @csrf

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="nom" class="form-label">Nom de l'entreprise <span
                                        class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('nom') is-invalid @enderror" id="nom"
                                    name="nom" value="{{ old('nom') }}" required>
                                @error('nom')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="contact_nom" class="form-label">Nom du contact</label>
                                <input type="text" class="form-control @error('contact_nom') is-invalid @enderror"
                                    id="contact_nom" name="contact_nom" value="{{ old('contact_nom') }}">
                                @error('contact_nom')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror"
                                    id="email" name="email" value="{{ old('email') }}">
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="telephone" class="form-label">Téléphone</label>
                                <input type="text" class="form-control @error('telephone') is-invalid @enderror"
                                    id="telephone" name="telephone" value="{{ old('telephone') }}">
                                @error('telephone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="adresse" class="form-label">Adresse</label>
                            <textarea class="form-control @error('adresse') is-invalid @enderror" id="adresse" name="adresse" rows="2">{{ old('adresse') }}</textarea>
                            @error('adresse')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="ville" class="form-label">Ville</label>
                                <input type="text" class="form-control @error('ville') is-invalid @enderror"
                                    id="ville" name="ville" value="{{ old('ville') }}">
                                @error('ville')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="code_postal" class="form-label">Code postal</label>
                                <input type="text" class="form-control @error('code_postal') is-invalid @enderror"
                                    id="code_postal" name="code_postal" value="{{ old('code_postal') }}">
                                @error('code_postal')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="pays" class="form-label">Pays</label>
                                <input type="text" class="form-control @error('pays') is-invalid @enderror"
                                    id="pays" name="pays" value="{{ old('pays', 'Mali') }}">
                                @error('pays')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div> --}}

                        <div class="mb-3">
                            <label for="site_web" class="form-label">Site web</label>
                            <input type="url" class="form-control @error('site_web') is-invalid @enderror"
                                id="site_web" name="site_web" value="{{ old('site_web') }}"
                                placeholder="https://example.com">
                            @error('site_web')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="notes" class="form-label">Notes</label>
                            <textarea class="form-control @error('notes') is-invalid @enderror" id="notes" name="notes" rows="3"
                                placeholder="Notes sur le fournisseur...">{{ old('notes') }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex justify-content-end">
                            <a href="{{ route('fournisseurs.index') }}" class="btn btn-outline-secondary me-2">
                                <i class="fas fa-times me-2"></i>
                                Annuler
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>
                                Créer le fournisseur
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        Informations
                    </h6>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="fas fa-lightbulb me-2"></i>
                        <strong>Conseil :</strong> Remplissez au minimum le nom pour créer le fournisseur.
                    </div>

                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Note :</strong> L'email est optionnel mais doit être unique s'il est fourni.
                    </div>

                    <div class="alert alert-success">
                        <i class="fas fa-check-circle me-2"></i>
                        <strong>Avantage :</strong> Vous pourrez ensuite associer des produits à ce fournisseur.
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
