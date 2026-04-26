@extends('layouts.app')

@section('content')
    <div class="container-fluid px-4 py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-1 fw-bold">Nouveau Contrat</h1>
                <p class="text-muted mb-0">Uploader un contrat signé avec une boutique</p>
            </div>
            <div>
                <a href="{{ route('admin.contrats.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Retour
                </a>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-8 mx-auto">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <form action="{{ route('admin.contrats.store') }}" method="POST" enctype="multipart/form-data">
                            @csrf

                            <!-- Boutique -->
                            <div class="mb-4">
                                <label for="boutique_id" class="form-label fw-bold">Boutique <span
                                        class="text-danger">*</span></label>
                                <select name="boutique_id" id="boutique_id"
                                    class="form-select @error('boutique_id') is-invalid @enderror" required>
                                    <option value="">Sélectionner une boutique</option>
                                    @foreach ($boutiques as $boutique)
                                        <option value="{{ $boutique->id }}"
                                            {{ old('boutique_id') == $boutique->id ? 'selected' : '' }}>
                                            {{ $boutique->nom }} - {{ $boutique->owner?->name ?? 'N/A' }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('boutique_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Type de contrat -->
                            <div class="mb-4">
                                <label for="type_contrat" class="form-label fw-bold">Type de contrat <span
                                        class="text-danger">*</span></label>
                                <select name="type_contrat" id="type_contrat"
                                    class="form-select @error('type_contrat') is-invalid @enderror" required>
                                    <option value="standard" {{ old('type_contrat') == 'standard' ? 'selected' : '' }}>
                                        Standard</option>
                                    <option value="premium" {{ old('type_contrat') == 'premium' ? 'selected' : '' }}>Premium
                                    </option>
                                    <option value="entreprise" {{ old('type_contrat') == 'entreprise' ? 'selected' : '' }}>
                                        Entreprise</option>
                                    <option value="personnalise"
                                        {{ old('type_contrat') == 'personnalise' ? 'selected' : '' }}>Personnalisé</option>
                                </select>
                                @error('type_contrat')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="row">
                                <!-- Date de signature -->
                                <div class="col-md-6 mb-4">
                                    <label for="date_signature" class="form-label fw-bold">Date de signature <span
                                            class="text-danger">*</span></label>
                                    <input type="date" name="date_signature" id="date_signature"
                                        class="form-control @error('date_signature') is-invalid @enderror"
                                        value="{{ old('date_signature', date('Y-m-d')) }}" required>
                                    @error('date_signature')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Date d'expiration -->
                                <div class="col-md-6 mb-4">
                                    <label for="date_expiration" class="form-label fw-bold">Date d'expiration</label>
                                    <input type="date" name="date_expiration" id="date_expiration"
                                        class="form-control @error('date_expiration') is-invalid @enderror"
                                        value="{{ old('date_expiration') }}">
                                    @error('date_expiration')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">Laisser vide si le contrat n'a pas de date
                                        d'expiration</small>
                                </div>
                            </div>

                            <!-- Montant -->
                            <div class="mb-4">
                                <label for="montant" class="form-label fw-bold">Montant (FCFA)</label>
                                <input type="number" name="montant" id="montant" step="0.01" min="0"
                                    class="form-control @error('montant') is-invalid @enderror"
                                    value="{{ old('montant') }}" placeholder="0.00">
                                @error('montant')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Fichier contrat -->
                            <div class="mb-4">
                                <label for="fichier_contrat" class="form-label fw-bold">Fichier du contrat <span
                                        class="text-danger">*</span></label>
                                <input type="file" name="fichier_contrat" id="fichier_contrat"
                                    class="form-control @error('fichier_contrat') is-invalid @enderror"
                                    accept=".pdf,.doc,.docx" required>
                                @error('fichier_contrat')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Formats acceptés : PDF, DOC, DOCX (max 10MB)</small>
                            </div>

                            <!-- Notes -->
                            <div class="mb-4">
                                <label for="notes" class="form-label fw-bold">Notes</label>
                                <textarea name="notes" id="notes" rows="4" class="form-control @error('notes') is-invalid @enderror"
                                    placeholder="Notes supplémentaires sur le contrat...">{{ old('notes') }}</textarea>
                                @error('notes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Boutons -->
                            <div class="d-flex justify-content-end gap-2">
                                <a href="{{ route('admin.contrats.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-times me-2"></i>Annuler
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Enregistrer le contrat
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
