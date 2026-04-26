@extends('layouts.app')

@section('content')

    <!-- En-tête avec bouton d'ajout -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="mb-0">
                        <i class="fas fa-truck me-2 text-primary"></i>
                        Gestion des fournisseurs
                    </h2>
                    <p class="text-muted mb-0">Gérez vos fournisseurs et leurs produits</p>
                </div>
                <a href="{{ route('fournisseurs.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>
                    Ajouter un fournisseur
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
                        <div class="stat-value">{{ $stats['total_fournisseurs'] }}</div>
                        <div class="stat-label">Total fournisseurs</div>
                    </div>
                    <div class="ms-3">
                        <i class="fas fa-truck fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-3">
            <div class="card stat-card bg-success text-white">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <div class="stat-value">{{ $stats['fournisseurs_actifs'] }}</div>
                        <div class="stat-label">Fournisseurs actifs</div>
                    </div>
                    <div class="ms-3">
                        <i class="fas fa-check-circle fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-3">
            <div class="card stat-card bg-warning text-white">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <div class="stat-value">{{ $stats['fournisseurs_inactifs'] }}</div>
                        <div class="stat-label">Fournisseurs inactifs</div>
                    </div>
                    <div class="ms-3">
                        <i class="fas fa-times-circle fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtres -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('fournisseurs.index') }}" class="row g-3">
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
                    <a href="{{ route('fournisseurs.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-times me-1"></i>
                        Effacer
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Liste des fournisseurs -->
    <div class="card">
        <div class="card-body">
            @if ($fournisseurs->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Nom</th>
                                <th>Contact</th>
                                <th>Email</th>
                                <th>Téléphone</th>
                                <th>Ville</th>
                                <th>Produits</th>
                                <th>Statut</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($fournisseurs as $fournisseur)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div
                                                class="avatar-sm bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3">
                                                {{ strtoupper(substr($fournisseur->nom, 0, 2)) }}
                                            </div>
                                            <div>
                                                <strong>{{ $fournisseur->nom }}</strong>
                                                @if ($fournisseur->notes)
                                                    <br><small
                                                        class="text-muted">{{ Str::limit($fournisseur->notes, 30) }}</small>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td>{{ $fournisseur->contact_nom ?? '-' }}</td>
                                    <td>{{ $fournisseur->email }}</td>
                                    <td>{{ $fournisseur->telephone ?? '-' }}</td>
                                    <td>{{ $fournisseur->ville ?? '-' }}</td>
                                    <td>
                                        <span class="badge bg-info">{{ $fournisseur->produits()->count() }} produits</span>
                                    </td>
                                    <td>
                                        @if ($fournisseur->actif)
                                            <span class="badge bg-success">Actif</span>
                                        @else
                                            <span class="badge bg-danger">Inactif</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('fournisseurs.show', $fournisseur) }}"
                                                class="btn btn-sm btn-outline-info" title="Voir">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('fournisseurs.edit', $fournisseur) }}"
                                                class="btn btn-sm btn-outline-primary" title="Modifier">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form action="{{ route('fournisseurs.toggle', $fournisseur) }}" method="POST"
                                                class="d-inline">
                                                @csrf
                                                <button type="submit"
                                                    class="btn btn-sm btn-outline-{{ $fournisseur->actif ? 'warning' : 'success' }}"
                                                    title="{{ $fournisseur->actif ? 'Désactiver' : 'Activer' }}">
                                                    <i class="fas fa-{{ $fournisseur->actif ? 'ban' : 'check' }}"></i>
                                                </button>
                                            </form>
                                            <form action="{{ route('fournisseurs.destroy', $fournisseur) }}" method="POST"
                                                class="d-inline"
                                                onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce fournisseur ?')">
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
                    {{ $fournisseurs->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-truck fa-3x text-muted mb-3"></i>
                    <h4>Aucun fournisseur trouvé</h4>
                    <p class="text-muted">Commencez par créer vos premiers fournisseurs.</p>
                    <a href="{{ route('fournisseurs.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>
                        Ajouter un fournisseur
                    </a>
                </div>
            @endif
        </div>
    </div>

@endsection






























