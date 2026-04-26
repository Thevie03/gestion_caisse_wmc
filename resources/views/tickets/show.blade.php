@extends('layouts.app')

@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <!-- Bouton retour -->
            <div class="mb-3">
                <a href="{{ route('tickets.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i>
                    Retour à la liste
                </a>
            </div>

            <!-- Détails du ticket -->
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
                                <strong>Date de création :</strong> {{ $ticket->created_at->format('d/m/Y à H:i') }}
                            </small>
                        </div>
                        <div class="col-md-6">
                            <small class="text-muted">
                                <i class="fas fa-store me-1"></i>
                                <strong>Boutique :</strong> {{ $ticket->boutique->nom }}
                            </small>
                        </div>
                    </div>

                    <hr>

                    <h6 class="mb-3">
                        <i class="fas fa-comment me-2"></i>
                        Votre message
                    </h6>
                    <div class="alert alert-light">
                        {!! nl2br(e($ticket->message)) !!}
                    </div>

                    @if ($ticket->reponse)
                        <hr>
                        <h6 class="mb-3">
                            <i class="fas fa-reply me-2 text-success"></i>
                            Réponse de WMC
                        </h6>
                        <div class="alert alert-success">
                            {!! nl2br(e($ticket->reponse)) !!}
                            <div class="mt-2">
                                <small class="text-muted">
                                    <i class="fas fa-user me-1"></i>
                                    Répondu par {{ $ticket->repondPar->name ?? 'WMC' }}
                                    le {{ $ticket->reponse_at->format('d/m/Y à H:i') }}
                                </small>
                            </div>
                        </div>
                    @else
                        <div class="alert alert-info">
                            <i class="fas fa-clock me-2"></i>
                            Votre demande est en attente de réponse. Nous vous répondrons dans les plus brefs délais.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
