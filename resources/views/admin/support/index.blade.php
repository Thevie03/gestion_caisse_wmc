@extends('layouts.app')

@section('content')
    <div class="container-fluid px-4 py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-1 fw-bold">
                    <i class="fas fa-life-ring me-2 text-primary"></i>
                    Support & Assistance
                </h1>
                <p class="text-muted mb-0">Gestion des demandes d'assistance des boutiques</p>
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
                <div class="card stat-card bg-danger text-white">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <div class="stat-value">{{ $stats['urgents'] }}</div>
                            <div class="stat-label">Urgents</div>
                        </div>
                        <div class="ms-3">
                            <i class="fas fa-exclamation-triangle fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filtres -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" action="{{ route('admin.support.index') }}" class="row g-3">
                    <div class="col-md-3">
                        <label for="boutique_id" class="form-label">Boutique</label>
                        <select class="form-select" id="boutique_id" name="boutique_id">
                            <option value="">Toutes les boutiques</option>
                            @foreach ($boutiques as $boutique)
                                <option value="{{ $boutique->id }}"
                                    {{ request('boutique_id') == $boutique->id ? 'selected' : '' }}>
                                    {{ $boutique->nom }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label for="statut" class="form-label">Statut</label>
                        <select class="form-select" id="statut" name="statut">
                            <option value="">Tous les statuts</option>
                            <option value="ouvert" {{ request('statut') == 'ouvert' ? 'selected' : '' }}>Ouvert</option>
                            <option value="en_cours" {{ request('statut') == 'en_cours' ? 'selected' : '' }}>En cours
                            </option>
                            <option value="resolu" {{ request('statut') == 'resolu' ? 'selected' : '' }}>Résolu</option>
                            <option value="ferme" {{ request('statut') == 'ferme' ? 'selected' : '' }}>Fermé</option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label for="priorite" class="form-label">Priorité</label>
                        <select class="form-select" id="priorite" name="priorite">
                            <option value="">Toutes les priorités</option>
                            <option value="urgente" {{ request('priorite') == 'urgente' ? 'selected' : '' }}>Urgente
                            </option>
                            <option value="elevee" {{ request('priorite') == 'elevee' ? 'selected' : '' }}>Élevée</option>
                            <option value="normale" {{ request('priorite') == 'normale' ? 'selected' : '' }}>Normale
                            </option>
                            <option value="faible" {{ request('priorite') == 'faible' ? 'selected' : '' }}>Faible</option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label for="search" class="form-label">Recherche</label>
                        <input type="text" class="form-control" id="search" name="search"
                            value="{{ request('search') }}" placeholder="Sujet ou message...">
                    </div>

                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-filter me-2"></i>
                            Filtrer
                        </button>
                        <a href="{{ route('admin.support.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-times me-2"></i>
                            Réinitialiser
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Liste des tickets -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-list me-2"></i>
                    Demandes d'assistance
                </h5>
            </div>
            <div class="card-body">
                @if ($tickets->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Boutique</th>
                                    <th>Sujet</th>
                                    <th>Priorité</th>
                                    <th>Statut</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($tickets as $ticket)
                                    <tr
                                        class="{{ $ticket->priorite == 'urgente' && $ticket->statut != 'resolu' ? 'table-danger' : '' }}">
                                        <td>{{ $ticket->created_at->format('d/m/Y H:i') }}</td>
                                        <td>
                                            <strong>{{ $ticket->boutique->nom }}</strong>
                                            <br><small class="text-muted">{{ $ticket->user->name }}</small>
                                        </td>
                                        <td>
                                            <strong>{{ $ticket->sujet }}</strong>
                                            @if ($ticket->reponse)
                                                <br><small class="text-success">
                                                    <i class="fas fa-reply me-1"></i>
                                                    Répondu
                                                </small>
                                            @else
                                                <br><small class="text-warning">
                                                    <i class="fas fa-clock me-1"></i>
                                                    En attente
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
                                            <a href="{{ route('admin.support.show', $ticket) }}"
                                                class="btn btn-sm btn-primary">
                                                <i class="fas fa-eye"></i>
                                                Voir/Répondre
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
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
