@extends('layouts.app')

@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-plus-circle me-2"></i>
                        Nouvelle demande d'assistance
                    </h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('tickets.store') }}" class="needs-validation" novalidate>
                        @csrf

                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Note :</strong> Utilisez ce formulaire pour signaler un bug, demander de l'assistance ou
                            poser une question.
                            Notre équipe WMC vous répondra dans les plus brefs délais.
                        </div>

                        <!-- Sujet -->
                        <div class="mb-3">
                            <label for="sujet" class="form-label">
                                <i class="fas fa-heading me-1 text-primary"></i>
                                Sujet <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control @error('sujet') is-invalid @enderror" id="sujet"
                                name="sujet" value="{{ old('sujet') }}" placeholder="Ex: Problème de connexion" required>
                            @error('sujet')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Priorité -->
                        <div class="mb-3">
                            <label for="priorite" class="form-label">
                                <i class="fas fa-exclamation-triangle me-1 text-primary"></i>
                                Priorité <span class="text-danger">*</span>
                            </label>
                            <select class="form-select @error('priorite') is-invalid @enderror" id="priorite"
                                name="priorite" required>
                                <option value="normale" {{ old('priorite', 'normale') == 'normale' ? 'selected' : '' }}>
                                    Normale
                                </option>
                                <option value="faible" {{ old('priorite') == 'faible' ? 'selected' : '' }}>
                                    Faible
                                </option>
                                <option value="elevee" {{ old('priorite') == 'elevee' ? 'selected' : '' }}>
                                    Élevée
                                </option>
                                <option value="urgente" {{ old('priorite') == 'urgente' ? 'selected' : '' }}>
                                    Urgente
                                </option>
                            </select>
                            @error('priorite')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">
                                <small class="text-muted">
                                    <strong>Urgente :</strong> Problème bloquant l'utilisation de l'application<br>
                                    <strong>Élevée :</strong> Problème important mais non bloquant<br>
                                    <strong>Normale :</strong> Question ou problème mineur<br>
                                    <strong>Faible :</strong> Suggestion ou amélioration
                                </small>
                            </div>
                        </div>

                        <!-- Message -->
                        <div class="mb-3">
                            <label for="message" class="form-label">
                                <i class="fas fa-comment-alt me-1 text-primary"></i>
                                Description détaillée <span class="text-danger">*</span>
                            </label>
                            <textarea class="form-control @error('message') is-invalid @enderror" id="message" name="message" rows="8"
                                placeholder="Décrivez votre problème ou votre demande en détail..." required>{{ old('message') }}</textarea>
                            @error('message')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">
                                <small class="text-muted">Minimum 10 caractères. Soyez le plus précis possible pour nous
                                    aider à mieux vous assister.</small>
                            </div>
                        </div>

                        <!-- Boutons d'action -->
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('tickets.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-2"></i>
                                Annuler
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-paper-plane me-2"></i>
                                Envoyer la demande
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
