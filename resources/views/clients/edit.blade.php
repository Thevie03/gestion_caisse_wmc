@extends('layouts.app')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="mb-0">
                        <i class="fas fa-user-edit me-2 text-primary"></i>
                        Modifier le client
                    </h2>
                    <p class="text-muted mb-0">Modifiez les informations de {{ $client->nom_complet }}</p>
                </div>
                <a href="{{ route('clients.index') }}" class="btn btn-outline-secondary">
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
                        <i class="fas fa-user me-2"></i>
                        Informations du client
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('clients.update', $client) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="prenom" class="form-label">Prénom <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('prenom') is-invalid @enderror"
                                    id="prenom" name="prenom" value="{{ old('prenom', $client->prenom) }}" required>
                                @error('prenom')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="nom" class="form-label">Nom</label>
                                <input type="text" class="form-control @error('nom') is-invalid @enderror" id="nom"
                                    name="nom" value="{{ old('nom', $client->nom) }}">
                                @error('nom')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror"
                                    id="email" name="email" value="{{ old('email', $client->email) }}">
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="telephone" class="form-label">Téléphone</label>
                                <input type="text" class="form-control @error('telephone') is-invalid @enderror"
                                    id="telephone" name="telephone" value="{{ old('telephone', $client->telephone) }}">
                                @error('telephone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="sexe" class="form-label">Sexe</label>
                                <select class="form-select @error('sexe') is-invalid @enderror" id="sexe"
                                    name="sexe">
                                    <option value="">Sélectionner</option>
                                    <option value="M" {{ old('sexe', $client->sexe) == 'M' ? 'selected' : '' }}>
                                        Masculin</option>
                                    <option value="F" {{ old('sexe', $client->sexe) == 'F' ? 'selected' : '' }}>
                                        Féminin</option>
                                </select>
                                @error('sexe')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- <div class="col-md-6 mb-3">
                                <label for="date_naissance" class="form-label">Date de naissance</label>
                                <input type="date" class="form-control @error('date_naissance') is-invalid @enderror"
                                    id="date_naissance" name="date_naissance"
                                    value="{{ old('date_naissance', $client->date_naissance?->format('Y-m-d')) }}">
                                @error('date_naissance')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div> --}}
                        </div>

                        <div class="mb-3">
                            <label for="adresse" class="form-label">Adresse</label>
                            <textarea class="form-control @error('adresse') is-invalid @enderror" id="adresse" name="adresse" rows="2">{{ old('adresse', $client->adresse) }}</textarea>
                            @error('adresse')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="ville" class="form-label">Ville</label>
                                <input type="text" class="form-control @error('ville') is-invalid @enderror"
                                    id="ville" name="ville" value="{{ old('ville', $client->ville) }}">
                                @error('ville')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="code_postal" class="form-label">Code postal</label>
                                <input type="text" class="form-control @error('code_postal') is-invalid @enderror"
                                    id="code_postal" name="code_postal"
                                    value="{{ old('code_postal', $client->code_postal) }}">
                                @error('code_postal')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="pays" class="form-label">Pays</label>
                                <input type="text" class="form-control @error('pays') is-invalid @enderror"
                                    id="pays" name="pays" value="{{ old('pays', $client->pays) }}">
                                @error('pays')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div> --}}

                        <div class="mb-3">
                            <label for="notes" class="form-label">Notes</label>
                            <textarea class="form-control @error('notes') is-invalid @enderror" id="notes" name="notes" rows="3"
                                placeholder="Notes sur le client...">{{ old('notes', $client->notes) }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="actif" name="actif" value="1"
                                    {{ old('actif', $client->actif) ? 'checked' : '' }}>
                                <label class="form-check-label" for="actif">
                                    Client actif
                                </label>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end">
                            <a href="{{ route('clients.index') }}" class="btn btn-outline-secondary me-2">
                                <i class="fas fa-times me-2"></i>
                                Annuler
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>
                                Mettre à jour
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
                        Informations du client
                    </h6>
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        <div
                            class="avatar-lg bg-primary text-white rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3">
                            {{ strtoupper(substr($client->prenom, 0, 1)) }}{{ $client->nom ? strtoupper(substr($client->nom, 0, 1)) : '' }}
                        </div>
                        <h5>{{ $client->nom_complet }}</h5>
                        <p class="text-muted">{{ $client->email }}</p>
                    </div>

                    <div class="row text-center">
                        <div class="col-6">
                            <div class="border-end">
                                <h6 class="text-primary">{{ number_format($client->solde_points, 0) }}</h6>
                                <small class="text-muted">Points</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <h6 class="text-success">{{ $client->ventes()->count() }}</h6>
                            <small class="text-muted">Achats</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
