@extends('layouts.app')

@section('content')
    <!-- En-tête avec bouton d'ajout -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="mb-0">
                        <i class="fas fa-life-ring me-2 text-primary"></i>
                        Bugs ou Assistance
                    </h2>
                    <p class="text-muted mb-0">Signalez un problème ou demandez de l'assistance</p>
                </div>
                <a href="{{ route('tickets.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>
                    Nouvelle demande
                </a>
            </div>
        </div>
    </div>

    <!-- Statistiques -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card stat-card">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <div class="stat-value">{{ $stats['total'] }}</div>
                        <div class="stat-label">Total demandes</div>
                    </div>
                    <div class="ms-3">
                        <i class="fas fa-ticket-alt fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card stat-card bg-warning text-white">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <div class="stat-value">{{ $stats['ouverts'] }}</div>
                        <div class="stat-label">Ouverts</div>
                    </div>
                    <div class="ms-3">
                        <i class="fas fa-clock fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card stat-card bg-info text-white">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <div class="stat-value">{{ $stats['en_cours'] }}</div>
                        <div class="stat-label">En cours</div>
                    </div>
                    <div class="ms-3">
                        <i class="fas fa-spinner fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card stat-card bg-success text-white">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <div class="stat-value">{{ $stats['resolus'] }}</div>
                        <div class="stat-label">Résolus</div>
                    </div>
                    <div class="ms-3">
                        <i class="fas fa-check-circle fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Liste des tickets -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="fas fa-list me-2"></i>
                Mes demandes d'assistance
            </h5>
        </div>
        <div class="card-body">
            @if ($tickets->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Sujet</th>
                                <th>Priorité</th>
                                <th>Statut</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($tickets as $ticket)
                                <tr>
                                    <td>{{ $ticket->created_at->format('d/m/Y H:i') }}</td>
                                    <td>
                                        <strong>{{ $ticket->sujet }}</strong>
                                        @if ($ticket->reponse)
                                            <br><small class="text-success">
                                                <i class="fas fa-reply me-1"></i>
                                                Répondu le {{ $ticket->reponse_at->format('d/m/Y H:i') }}
                                            </small>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge {{ $ticket->priorite_badge_class }}">
                                            {{ $ticket->priorite_label }}
                                        </span>
                                    </td>
                                    <td>
                                        <span
                                            class="badge
                                            @if ($ticket->statut == 'ouvert') bg-warning
                                            @elseif($ticket->statut == 'en_cours') bg-info
                                            @elseif($ticket->statut == 'resolu') bg-success
                                            @else bg-secondary @endif">
                                            {{ $ticket->statut_label }}
                                        </span>
                                    </td>
                                    <td>
                                        <a href="{{ route('tickets.show', $ticket) }}"
                                            class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-eye"></i>
                                            Voir
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-3">
                    {{ $tickets->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                    <p class="text-muted">Aucune demande d'assistance pour le moment.</p>
                    <a href="{{ route('tickets.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>
                        Créer une demande
                    </a>
                </div>
            @endif
        </div>
    </div>
@endsection
