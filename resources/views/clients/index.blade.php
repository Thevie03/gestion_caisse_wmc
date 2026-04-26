@extends('layouts.app')

@section('content')

    <!-- En-tête avec bouton d'ajout -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="mb-0">
                        <i class="fas fa-users me-2 text-primary"></i>
                        Gestion des clients
                    </h2>
                    <p class="text-muted mb-0">Gérez vos clients et leur historique d'achats</p>
                </div>
                <a href="{{ route('clients.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>
                    Ajouter un client
                </a>
            </div>
        </div>
    </div>

    <!-- Statistiques -->
    <div class="row mb-4">
        <div class="col-md-4 mb-3">
            <div class="card stat-card">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <div class="stat-value">{{ $stats['total_clients'] }}</div>
                        <div class="stat-label">Total clients</div>
                    </div>
                    <div class="ms-3">
                        <i class="fas fa-users fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-3">
            <div class="card stat-card bg-success text-white">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <div class="stat-value">{{ $stats['clients_actifs'] }}</div>
                        <div class="stat-label">Clients actifs</div>
                    </div>
                    <div class="ms-3">
                        <i class="fas fa-user-check fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-3">
            <div class="card stat-card bg-warning text-white">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <div class="stat-value">{{ $stats['clients_inactifs'] }}</div>
                        <div class="stat-label">Clients inactifs</div>
                    </div>
                    <div class="ms-3">
                        <i class="fas fa-user-times fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtres -->
    <div class="card saas-surface-card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('clients.index') }}" class="row g-3">
                <div class="col-md-4">
                    <label for="search" class="form-label">Rechercher</label>
                    <input type="text" class="form-control" id="search" name="search" value="{{ request('search') }}"
                        placeholder="Nom, email ou téléphone">
                </div>

                <div class="col-md-3">
                    <label for="statut" class="form-label">Statut</label>
                    <select class="form-select" id="statut" name="statut">
                        <option value="">Tous les statuts</option>
                        <option value="actif" {{ request('statut') == 'actif' ? 'selected' : '' }}>Actif</option>
                        <option value="inactif" {{ request('statut') == 'inactif' ? 'selected' : '' }}>Inactif</option>
                    </select>
                </div>

                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="fas fa-search me-1"></i>
                        Filtrer
                    </button>
                    <a href="{{ route('clients.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-times me-1"></i>
                        Effacer
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Liste des clients -->
    <div class="card saas-surface-card">
        <div class="card-body">
            @if ($clients->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover saas-table">
                        <thead>
                            <tr>
                                <th>Nom complet</th>
                                <th>Email</th>
                                <th>Téléphone</th>
                                <th>Ville</th>
                                <th>Points</th>
                                <th>Statut</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($clients as $client)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div
                                                class="avatar-sm bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3">
                                                {{ strtoupper(substr($client->prenom, 0, 1)) }}{{ $client->nom ? strtoupper(substr($client->nom, 0, 1)) : '' }}
                                            </div>
                                            <div>
                                                <strong>{{ $client->nom_complet }}</strong>
                                                @if ($client->notes)
                                                    <br><small
                                                        class="text-muted">{{ Str::limit($client->notes, 30) }}</small>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td>{{ $client->email }}</td>
                                    <td>{{ $client->telephone ?? '-' }}</td>
                                    <td>{{ $client->ville ?? '-' }}</td>
                                    <td>
                                        <span class="badge bg-info">{{ number_format($client->solde_points, 0) }}
                                            pts</span>
                                    </td>
                                    <td>
                                        @if ($client->actif)
                                            <span class="badge bg-success">Actif</span>
                                        @else
                                            <span class="badge bg-danger">Inactif</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('clients.show', $client) }}"
                                                class="btn btn-sm btn-outline-info" title="Voir">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('clients.edit', $client) }}"
                                                class="btn btn-sm btn-outline-primary" title="Modifier">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form action="{{ route('clients.toggle', $client) }}" method="POST"
                                                class="d-inline">
                                                @csrf
                                                <button type="submit"
                                                    class="btn btn-sm btn-outline-{{ $client->actif ? 'warning' : 'success' }}"
                                                    title="{{ $client->actif ? 'Désactiver' : 'Activer' }}">
                                                    <i class="fas fa-{{ $client->actif ? 'ban' : 'check' }}"></i>
                                                </button>
                                            </form>
                                            <form action="{{ route('clients.destroy', $client) }}" method="POST"
                                                class="d-inline"
                                                onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce client ?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger"
                                                    title="Supprimer">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-center mt-4">
                    {{ $clients->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-users fa-3x text-muted mb-3"></i>
                    <h4>Aucun client trouvé</h4>
                    <p class="text-muted">Commencez par créer vos premiers clients.</p>
                    <a href="{{ route('clients.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>
                        Ajouter un client
                    </a>
                </div>
            @endif
        </div>
    </div>

@endsection
