@extends('layouts.app')

@section('title', 'Historique des Modifications et Suppressions de Ventes')

@section('content')
    <div class="container-fluid">
        <!-- En-tête -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h1 class="h3 mb-0 text-gray-800">
                            <i class="fas fa-history me-2"></i>
                            Historique des Modifications et Suppressions de Ventes
                        </h1>
                        <p class="text-muted mb-0">Traçabilité complète des actions administratives sur les ventes</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistiques -->
        <div class="row mb-4">
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-primary shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                    Total Actions
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    {{ number_format($stats['total']) }}
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-list fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-warning shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                    Modifications
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    {{ number_format($stats['modifications']) }}
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-edit fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-danger shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                    Suppressions
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    {{ number_format($stats['suppressions']) }}
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-trash fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-info shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                    Aujourd'hui
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    {{ number_format($stats['aujourd_hui']) }}
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-calendar-day fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filtres -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-filter me-2"></i>
                    Filtres de Recherche
                </h6>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('historique.ventes.index') }}" class="row g-3">
                    <div class="col-md-3">
                        <label for="type_action" class="form-label">Type d'action</label>
                        <select class="form-select" id="type_action" name="type_action">
                            <option value="">Tous</option>
                            <option value="modification" {{ request('type_action') == 'modification' ? 'selected' : '' }}>
                                Modifications</option>
                            <option value="suppression" {{ request('type_action') == 'suppression' ? 'selected' : '' }}>
                                Suppressions</option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label for="date_debut" class="form-label">Date de début</label>
                        <input type="date" class="form-control" id="date_debut" name="date_debut"
                            value="{{ request('date_debut') }}">
                    </div>

                    <div class="col-md-3">
                        <label for="date_fin" class="form-label">Date de fin</label>
                        <input type="date" class="form-control" id="date_fin" name="date_fin"
                            value="{{ request('date_fin') }}">
                    </div>

                    <div class="col-md-3">
                        <label for="user_id" class="form-label">Administrateur</label>
                        <select class="form-select" id="user_id" name="user_id">
                            <option value="">Tous</option>
                            @foreach ($users as $userItem)
                                <option value="{{ $userItem->id }}"
                                    {{ request('user_id') == $userItem->id ? 'selected' : '' }}>
                                    {{ $userItem->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label for="numero_vente" class="form-label">Numéro de vente</label>
                        <input type="text" class="form-control" id="numero_vente" name="numero_vente"
                            value="{{ request('numero_vente') }}" placeholder="V-20251116-0001">
                    </div>

                    <div class="col-md-6">
                        <label for="recherche" class="form-label">Rechercher</label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="fas fa-search"></i>
                            </span>
                            <input type="text" class="form-control" id="recherche" name="recherche"
                                value="{{ request('recherche') }}" placeholder="Utilisateur, boutique, changements...">
                        </div>
                    </div>

                    <div class="col-md-6 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary me-2">
                            <i class="fas fa-search me-1"></i>
                            Rechercher
                        </button>
                        <a href="{{ route('historique.ventes.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-times me-1"></i>
                            Réinitialiser
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Liste de l'historique -->
        <div class="card shadow">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-list me-2"></i>
                    Historique ({{ $historiques->total() }})
                </h6>
            </div>
            <div class="card-body">
                @if ($historiques->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Date/Heure</th>
                                    <th>Type</th>
                                    <th>N° Vente</th>
                                    <th>Administrateur</th>
                                    <th>Boutique</th>
                                    <th>Changements</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($historiques as $historique)
                                    <tr>
                                        <td>
                                            <div>
                                                <strong>{{ $historique->created_at->format('d/m/Y') }}</strong>
                                                <br>
                                                <small
                                                    class="text-muted">{{ $historique->created_at->format('H:i:s') }}</small>
                                            </div>
                                        </td>
                                        <td>
                                            @if ($historique->type_action === 'modification')
                                                <span class="badge bg-warning">
                                                    <i class="fas fa-edit me-1"></i>
                                                    Modification
                                                </span>
                                            @else
                                                <span class="badge bg-danger">
                                                    <i class="fas fa-trash me-1"></i>
                                                    Suppression
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            <strong class="text-primary">{{ $historique->numero_vente }}</strong>
                                        </td>
                                        <td>
                                            <div>
                                                <strong>{{ $historique->user->name }}</strong>
                                                <br>
                                                <small class="text-muted">{{ $historique->user->email }}</small>
                                            </div>
                                        </td>
                                        <td>
                                            @if ($historique->boutique)
                                                <span class="badge bg-secondary">{{ $historique->boutique->nom }}</span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($historique->changements)
                                                <small>{{ Str::limit($historique->changements, 100) }}</small>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            <a href="{{ route('historique.ventes.show', $historique) }}"
                                                class="btn btn-sm btn-info" title="Voir détails">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="mt-3">
                        {{ $historiques->appends(request()->query())->links() }}
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="fas fa-history fa-3x text-muted mb-3"></i>
                        <h4>Aucun historique trouvé</h4>
                        <p class="text-muted">Aucune modification ou suppression de vente ne correspond à vos critères de
                            recherche.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
