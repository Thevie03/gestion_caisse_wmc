@extends('layouts.app')

@section('content')
    <div class="container-fluid px-4 py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-1 fw-bold">Modifier le Contrat</h1>
                <p class="text-muted mb-0">{{ $contrat->numero_contrat }}</p>
            </div>
            <div>
                <a href="{{ route('admin.contrats.show', $contrat) }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Retour
                </a>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-8 mx-auto">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <form action="{{ route('admin.contrats.update', $contrat) }}" method="POST"
                            enctype="multipart/form-data">
                            @csrf
                            @method('PUT')

                            <!-- Boutique -->
                            <div class="mb-4">
                                <label for="boutique_id" class="form-label fw-bold">Boutique <span
                                        class="text-danger">*</span></label>
                                <select name="boutique_id" id="boutique_id"
                                    class="form-select @error('boutique_id') is-invalid @enderror" required>
                                    @foreach ($boutiques as $boutique)
                                        <option value="{{ $boutique->id }}"
                                            {{ old('boutique_id', $contrat->boutique_id) == $boutique->id ? 'selected' : '' }}>
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
                                    <option value="standard"
                                        {{ old('type_contrat', $contrat->type_contrat) == 'standard' ? 'selected' : '' }}>
                                        Standard</option>
                                    <option value="premium"
                                        {{ old('type_contrat', $contrat->type_contrat) == 'premium' ? 'selected' : '' }}>
                                        Premium</option>
                                    <option value="entreprise"
                                        {{ old('type_contrat', $contrat->type_contrat) == 'entreprise' ? 'selected' : '' }}>
                                        Entreprise</option>
                                    <option value="personnalise"
                                        {{ old('type_contrat', $contrat->type_contrat) == 'personnalise' ? 'selected' : '' }}>
                                        Personnalisé</option>
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
                                        value="{{ old('date_signature', $contrat->date_signature->format('Y-m-d')) }}"
                                        required>
                                    @error('date_signature')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Date d'expiration -->
                                <div class="col-md-6 mb-4">
                                    <label for="date_expiration" class="form-label fw-bold">Date d'expiration</label>
                                    <input type="date" name="date_expiration" id="date_expiration"
                                        class="form-control @error('date_expiration') is-invalid @enderror"
                                        value="{{ old('date_expiration', $contrat->date_expiration?->format('Y-m-d')) }}">
                                    @error('date_expiration')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Statut -->
                            <div class="mb-4">
                                <label for="statut" class="form-label fw-bold">Statut <span
                                        class="text-danger">*</span></label>
                                <select name="statut" id="statut"
                                    class="form-select @error('statut') is-invalid @enderror" required>
                                    <option value="actif"
                                        {{ old('statut', $contrat->statut) == 'actif' ? 'selected' : '' }}>Actif</option>
                                    <option value="expire"
                                        {{ old('statut', $contrat->statut) == 'expire' ? 'selected' : '' }}>Expiré</option>
                                    <option value="resilie"
                                        {{ old('statut', $contrat->statut) == 'resilie' ? 'selected' : '' }}>Résilié
                                    </option>
                                    <option value="en_attente"
                                        {{ old('statut', $contrat->statut) == 'en_attente' ? 'selected' : '' }}>En attente
                                    </option>
                                </select>
                                @error('statut')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Montant -->
                            <div class="mb-4">
                                <label for="montant" class="form-label fw-bold">Montant (FCFA)</label>
                                <input type="number" name="montant" id="montant" step="0.01" min="0"
                                    class="form-control @error('montant') is-invalid @enderror"
                                    value="{{ old('montant', $contrat->montant) }}" placeholder="0.00">
                                @error('montant')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Fichier contrat -->
                            <div class="mb-4">
                                <label for="fichier_contrat" class="form-label fw-bold">Fichier du contrat</label>
                                <input type="file" name="fichier_contrat" id="fichier_contrat"
                                    class="form-control @error('fichier_contrat') is-invalid @enderror"
                                    accept=".pdf,.doc,.docx">
                                @error('fichier_contrat')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Laisser vide pour conserver le fichier actuel. Formats acceptés :
                                    PDF, DOC, DOCX (max 10MB)</small>
                                @if ($contrat->fichier_contrat)
                                    <div class="mt-2">
                                        <small class="text-muted">Fichier actuel :
                                            {{ basename($contrat->fichier_contrat) }}</small>
                                        <a href="{{ route('admin.contrats.download', $contrat) }}"
                                            class="btn btn-sm btn-link" target="_blank">
                                            <i class="fas fa-eye me-1"></i>Voir
                                        </a>
                                    </div>
                                @endif
                            </div>

                            <!-- Notes -->
                            <div class="mb-4">
                                <label for="notes" class="form-label fw-bold">Notes</label>
                                <textarea name="notes" id="notes" rows="4" class="form-control @error('notes') is-invalid @enderror"
                                    placeholder="Notes supplémentaires sur le contrat...">{{ old('notes', $contrat->notes) }}</textarea>
                                @error('notes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Boutons -->
                            <div class="d-flex justify-content-end gap-2">
                                <a href="{{ route('admin.contrats.show', $contrat) }}" class="btn btn-secondary">
                                    <i class="fas fa-times me-2"></i>Annuler
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Enregistrer les modifications
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection


