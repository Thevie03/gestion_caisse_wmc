@extends('layouts.app')

@section('content')

    <!-- Filtres -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('stock.historique') }}" class="row g-3">
                <div class="col-md-3">
                    <label for="type" class="form-label">Type de mouvement</label>
                    <select class="form-select" id="type" name="type">
                        <option value="">Tous les types</option>
                        <option value="entree" {{ request('type') == 'entree' ? 'selected' : '' }}>Entrées</option>
                        <option value="sortie" {{ request('type') == 'sortie' ? 'selected' : '' }}>Sorties</option>
                        <option value="ajustement" {{ request('type') == 'ajustement' ? 'selected' : '' }}>Ajustements
                        </option>
                    </select>
                </div>

                <div class="col-md-3">
                    <label for="produit_id" class="form-label">Produit</label>
                    <select class="form-select" id="produit_id" name="produit_id">
                        <option value="">Tous les produits</option>
                        @foreach ($produits as $produit)
                            <option value="{{ $produit->id }}"
                                {{ request('produit_id') == $produit->id ? 'selected' : '' }}>
                                {{ $produit->nom }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2">
                    <label for="date_debut" class="form-label">Date début</label>
                    <input type="date" class="form-control" id="date_debut" name="date_debut"
                        value="{{ request('date_debut') }}">
                </div>

                <div class="col-md-2">
                    <label for="date_fin" class="form-label">Date fin</label>
                    <input type="date" class="form-control" id="date_fin" name="date_fin"
                        value="{{ request('date_fin') }}">
                </div>

                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="fas fa-search"></i>
                    </button>
                    <a href="{{ route('stock.historique') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-times"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Liste des mouvements -->
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">
                <i class="fas fa-list me-2"></i>
                Mouvements de stock ({{ $mouvements->total() }})
            </h5>
        </div>
        <div class="card-body p-0">
            @if ($mouvements->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Date/Heure</th>
                                <th>Produit</th>
                                <th>Type</th>
                                <th>Quantité</th>
                                <th>Motif</th>
                                <th>Utilisateur</th>
                                <th>Boutique</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($mouvements as $mouvement)
                                <tr>
                                    <td>
                                        <div>
                                            <strong>{{ $mouvement->created_at->format('d/m/Y') }}</strong>
                                            <br>
                                            <small
                                                class="text-muted">{{ $mouvement->created_at->format('H:i:s') }}</small>
                                        </div>
                                    </td>
                                    <td>
                                        <div>
                                            <strong>{{ $mouvement->produit->nom }}</strong>
                                            <br>
                                            <small class="text-muted">{{ $mouvement->produit->code_produit }}</small>
                                        </div>
                                    </td>
                                    <td>
                                        @switch($mouvement->type)
                                            @case('entree')
                                                <span class="badge bg-success">
                                                    <i class="fas fa-arrow-down me-1"></i>
                                                    Entrée
                                                </span>
                                            @break

                                            @case('sortie')
                                                <span class="badge bg-danger">
                                                    <i class="fas fa-arrow-up me-1"></i>
                                                    Sortie
                                                </span>
                                            @break

                                            @case('ajustement')
                                                <span class="badge bg-info">
                                                    <i class="fas fa-edit me-1"></i>
                                                    Ajustement
                                                </span>
                                            @break
                                        @endswitch
                                    </td>
                                    <td>
                                        <strong
                                            class="{{ $mouvement->type === 'sortie' ? 'text-danger' : 'text-success' }}">
                                            {{ $mouvement->type === 'sortie' ? '-' : '+' }}{{ $mouvement->quantite }}
                                        </strong>
                                    </td>
                                    <td>
                                        <span class="text-muted">{{ $mouvement->motif }}</span>
                                    </td>
                                    <td>
                                        <div>
                                            <strong>{{ $mouvement->user->name }}</strong>
                                            <br>
                                            <small class="text-muted">{{ $mouvement->user->email }}</small>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-primary">{{ $mouvement->boutique->nom }}</span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="card-footer">
                    {{ $mouvements->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-history fa-3x text-muted mb-3"></i>
                    <h4>Aucun mouvement trouvé</h4>
                    <p class="text-muted">Aucun mouvement de stock ne correspond aux critères de recherche.</p>
                    <a href="{{ route('stock.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>
                        Créer un mouvement
                    </a>
                </div>
            @endif
        </div>
    </div>
@endsection




































