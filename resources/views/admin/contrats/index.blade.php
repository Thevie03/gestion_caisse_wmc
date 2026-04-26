@extends('layouts.app')

@section('content')
    <div class="container-fluid px-4 py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-1 fw-bold">Gestion des Contrats</h1>
                <p class="text-muted mb-0">Tous les contrats signés avec les boutiques</p>
            </div>
            <div>
                <a href="{{ route('admin.contrats.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>Nouveau contrat
                </a>
            </div>
        </div>

        <!-- Statistiques -->
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <p class="text-muted small mb-1">Total Contrats</p>
                        <h3 class="mb-0 fw-bold">{{ number_format($stats['total']) }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <p class="text-muted small mb-1">Actifs</p>
                        <h3 class="mb-0 fw-bold text-success">{{ number_format($stats['actifs']) }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <p class="text-muted small mb-1">Expirés</p>
                        <h3 class="mb-0 fw-bold text-warning">{{ number_format($stats['expires']) }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <p class="text-muted small mb-1">Résiliés</p>
                        <h3 class="mb-0 fw-bold text-danger">{{ number_format($stats['resilies']) }}</h3>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filtres et recherche -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <form method="GET" action="{{ route('admin.contrats.index') }}" class="row g-3">
                    <div class="col-md-4">
                        <input type="text" name="search" value="{{ request('search') }}" class="form-control"
                            placeholder="Rechercher par numéro, boutique...">
                    </div>
                    <div class="col-md-3">
                        <select name="boutique_id" class="form-select">
                            <option value="">Toutes les boutiques</option>
                            @foreach ($boutiques as $boutique)
                                <option value="{{ $boutique->id }}" @selected(request('boutique_id') == $boutique->id)>
                                    {{ $boutique->nom }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select name="statut" class="form-select">
                            <option value="">Tous les statuts</option>
                            <option value="actif" @selected(request('statut') === 'actif')>Actif</option>
                            <option value="expire" @selected(request('statut') === 'expire')>Expiré</option>
                            <option value="resilie" @selected(request('statut') === 'resilie')>Résilié</option>
                            <option value="en_attente" @selected(request('statut') === 'en_attente')>En attente</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-search me-2"></i>Rechercher
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Tableau des contrats -->
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>N° Contrat</th>
                                <th>Boutique</th>
                                <th>Type</th>
                                <th>Date signature</th>
                                <th>Date expiration</th>
                                <th>Montant</th>
                                <th>Statut</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($contrats as $contrat)
                                <tr>
                                    <td>
                                        <strong>{{ $contrat->numero_contrat }}</strong>
                                    </td>
                                    <td>
                                        <strong>{{ $contrat->boutique->nom }}</strong>
                                        <br>
                                        <small class="text-muted">{{ $contrat->boutique->owner?->name ?? 'N/A' }}</small>
                                    </td>
                                    <td>
                                        <span class="badge bg-info">{{ ucfirst($contrat->type_contrat) }}</span>
                                    </td>
                                    <td>{{ $contrat->date_signature->format('d/m/Y') }}</td>
                                    <td>
                                        @if ($contrat->date_expiration)
                                            {{ $contrat->date_expiration->format('d/m/Y') }}
                                            @if ($contrat->date_expiration->isPast() && $contrat->statut !== 'resilie')
                                                <br><small class="text-danger">Expiré</small>
                                            @endif
                                        @else
                                            <span class="text-muted">Indéterminée</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($contrat->montant)
                                            {{ number_format($contrat->montant, 0, ',', ' ') }} FCFA
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                    <td>
                                        @php
                                            $badgeClass = match ($contrat->statut) {
                                                'actif' => 'bg-success',
                                                'expire' => 'bg-warning',
                                                'resilie' => 'bg-danger',
                                                'en_attente' => 'bg-secondary',
                                                default => 'bg-secondary',
                                            };
                                        @endphp
                                        <span class="badge {{ $badgeClass }}">{{ ucfirst($contrat->statut) }}</span>
                                    </td>
                                    <td class="text-end">
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('admin.contrats.view', $contrat) }}"
                                                class="btn btn-sm btn-success" title="Voir le contrat">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('admin.contrats.show', $contrat) }}"
                                                class="btn btn-sm btn-info" title="Détails">
                                                <i class="fas fa-info-circle"></i>
                                            </a>
                                            <a href="{{ route('admin.contrats.download', $contrat) }}"
                                                class="btn btn-sm btn-primary" title="Télécharger">
                                                <i class="fas fa-download"></i>
                                            </a>
                                            <a href="{{ route('admin.contrats.edit', $contrat) }}"
                                                class="btn btn-sm btn-warning" title="Modifier">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form action="{{ route('admin.contrats.destroy', $contrat) }}" method="POST"
                                                class="d-inline"
                                                onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce contrat ?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger" title="Supprimer">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-4">
                                        <i class="fas fa-file-contract fa-3x text-muted mb-3"></i>
                                        <p class="text-muted">Aucun contrat trouvé</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="mt-4">
                    {{ $contrats->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection
