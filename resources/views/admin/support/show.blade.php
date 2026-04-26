@extends('layouts.app')

@section('content')
    <div class="container-fluid px-4 py-4">
        <!-- Bouton retour -->
        <div class="mb-3">
            <a href="{{ route('admin.support.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>
                Retour à la liste
            </a>
        </div>

        <!-- Détails du ticket -->
        <div class="row">
            <div class="col-lg-8">
                <div class="card mb-3">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-ticket-alt me-2"></i>
                            {{ $ticket->sujet }}
                        </h5>
                        <div>
                            <span class="badge {{ $ticket->priorite_badge_class }} me-2">
                                {{ $ticket->priorite_label }}
                            </span>
                            <span
                                class="badge
                                @if ($ticket->statut == 'ouvert') bg-warning
                                @elseif($ticket->statut == 'en_cours') bg-info
                                @elseif($ticket->statut == 'resolu') bg-success
                                @else bg-secondary @endif">
                                {{ $ticket->statut_label }}
                            </span>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <small class="text-muted">
                                    <i class="fas fa-calendar me-1"></i>
                                    <strong>Date :</strong> {{ $ticket->created_at->format('d/m/Y à H:i') }}
                                </small>
                            </div>
                            <div class="col-md-6">
                                <small class="text-muted">
                                    <i class="fas fa-store me-1"></i>
                                    <strong>Boutique :</strong> {{ $ticket->boutique->nom }}
                                </small>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <small class="text-muted">
                                    <i class="fas fa-user me-1"></i>
                                    <strong>Demandeur :</strong> {{ $ticket->user->name }}
                                </small>
                            </div>
                            <div class="col-md-6">
                                <small class="text-muted">
                                    <i class="fas fa-envelope me-1"></i>
                                    <strong>Email :</strong> {{ $ticket->user->email }}
                                </small>
                            </div>
                        </div>

                        <hr>

                        <h6 class="mb-3">
                            <i class="fas fa-comment me-2"></i>
                            Message du client
                        </h6>
                        <div class="alert alert-light">
                            {!! nl2br(e($ticket->message)) !!}
                        </div>

                        @if ($ticket->reponse)
                            <hr>
                            <h6 class="mb-3">
                                <i class="fas fa-reply me-2 text-success"></i>
                                Votre réponse précédente
                            </h6>
                            <div class="alert alert-success">
                                {!! nl2br(e($ticket->reponse)) !!}
                                <div class="mt-2">
                                    <small class="text-muted">
                                        Répondu le {{ $ticket->reponse_at->format('d/m/Y à H:i') }}
                                    </small>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Formulaire de réponse -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-reply me-2"></i>
                            Répondre au ticket
                        </h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('admin.support.repondre', $ticket) }}">
                            @csrf
                            <div class="mb-3">
                                <label for="reponse" class="form-label">Votre réponse</label>
                                <textarea class="form-control @error('reponse') is-invalid @enderror" id="reponse" name="reponse" rows="6"
                                    required>{{ old('reponse', $ticket->reponse) }}</textarea>
                                @error('reponse')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="statut" class="form-label">Statut</label>
                                <select class="form-select @error('statut') is-invalid @enderror" id="statut"
                                    name="statut" required>
                                    <option value="ouvert"
                                        {{ old('statut', $ticket->statut) == 'ouvert' ? 'selected' : '' }}>Ouvert</option>
                                    <option value="en_cours"
                                        {{ old('statut', $ticket->statut) == 'en_cours' ? 'selected' : '' }}>En cours
                                    </option>
                                    <option value="resolu"
                                        {{ old('statut', $ticket->statut) == 'resolu' ? 'selected' : '' }}>Résolu</option>
                                    <option value="ferme"
                                        {{ old('statut', $ticket->statut) == 'ferme' ? 'selected' : '' }}>Fermé</option>
                                </select>
                                @error('statut')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-paper-plane me-2"></i>
                                Envoyer la réponse
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Actions rapides -->
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0">Actions rapides</h6>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('admin.support.update-statut', $ticket) }}" class="mb-3">
                            @csrf
                            <label class="form-label">Changer le statut</label>
                            <div class="input-group">
                                <select class="form-select" name="statut" onchange="this.form.submit()">
                                    <option value="ouvert" {{ $ticket->statut == 'ouvert' ? 'selected' : '' }}>Ouvert
                                    </option>
                                    <option value="en_cours" {{ $ticket->statut == 'en_cours' ? 'selected' : '' }}>En cours
                                    </option>
                                    <option value="resolu" {{ $ticket->statut == 'resolu' ? 'selected' : '' }}>Résolu
                                    </option>
                                    <option value="ferme" {{ $ticket->statut == 'ferme' ? 'selected' : '' }}>Fermé
                                    </option>
                                </select>
                            </div>
                        </form>

                        <hr>

                        <div class="small text-muted">
                            <p><strong>Informations :</strong></p>
                            <ul class="mb-0">
                                <li>Boutique : {{ $ticket->boutique->nom }}</li>
                                <li>Demandeur : {{ $ticket->user->name }}</li>
                                <li>Email : {{ $ticket->user->email }}</li>
                                <li>Téléphone : {{ $ticket->user->telephone ?? 'Non renseigné' }}</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
