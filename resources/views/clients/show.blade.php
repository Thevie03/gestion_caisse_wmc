@extends('layouts.app')

@section('content')

    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="mb-0">
                        <i class="fas fa-user me-2 text-primary"></i>
                        {{ $client->nom_complet }}
                    </h2>
                    <p class="text-muted mb-0">Détails et historique du client</p>
                </div>
                <div>
                    <a href="{{ route('clients.edit', $client) }}" class="btn btn-primary me-2">
                        <i class="fas fa-edit me-2"></i>
                        Modifier
                    </a>
                    <a href="{{ route('clients.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>
                        Retour
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Informations du client -->
        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-user me-2"></i>
                        Informations personnelles
                    </h5>
                </div>
                <div class="card-body">
                    <div class="text-center mb-4">
                        <div
                            class="avatar-lg bg-primary text-white rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3">
                            {{ strtoupper(substr($client->prenom, 0, 1)) }}{{ $client->nom ? strtoupper(substr($client->nom, 0, 1)) : '' }}
                        </div>
                        <h4>{{ $client->nom_complet }}</h4>
                        <p class="text-muted">{{ $client->email }}</p>
                        @if ($client->actif)
                            <span class="badge bg-success">Actif</span>
                        @else
                            <span class="badge bg-danger">Inactif</span>
                        @endif
                    </div>

                    <div class="row">
                        <div class="col-6 text-center">
                            <h5 class="text-primary">{{ number_format($client->solde_points, 0) }}</h5>
                            <small class="text-muted">Points de fidélité</small>
                        </div>
                        <div class="col-6 text-center">
                            <h5 class="text-success">{{ $stats['total_ventes'] }}</h5>
                            <small class="text-muted">Total achats</small>
                        </div>
                    </div>

                    <hr>

                    <div class="mb-2">
                        <strong><i class="fas fa-phone me-2"></i>Téléphone :</strong>
                        <span class="text-muted">{{ $client->telephone ?? 'Non renseigné' }}</span>
                    </div>

                    @if ($client->adresse)
                        <div class="mb-2">
                            <strong><i class="fas fa-map-marker-alt me-2"></i>Adresse :</strong>
                            <div class="text-muted">{{ $client->adresse }}</div>
                        </div>
                    @endif

                    @if ($client->ville)
                        <div class="mb-2">
                            <strong><i class="fas fa-city me-2"></i>Ville :</strong>
                            <span class="text-muted">{{ $client->ville }}</span>
                        </div>
                    @endif

                    @if ($client->date_naissance)
                        <div class="mb-2">
                            <strong><i class="fas fa-birthday-cake me-2"></i>Date de naissance :</strong>
                            <span class="text-muted">{{ $client->date_naissance->format('d/m/Y') }}</span>
                        </div>
                    @endif

                    @if ($client->sexe)
                        <div class="mb-2">
                            <strong><i class="fas fa-{{ $client->sexe == 'M' ? 'male' : 'female' }} me-2"></i>Sexe
                                :</strong>
                            <span class="text-muted">{{ $client->sexe == 'M' ? 'Masculin' : 'Féminin' }}</span>
                        </div>
                    @endif

                    @if ($client->notes)
                        <div class="mt-3">
                            <strong><i class="fas fa-sticky-note me-2"></i>Notes :</strong>
                            <div class="text-muted mt-1">{{ $client->notes }}</div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Statistiques -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-chart-bar me-2"></i>
                        Statistiques
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6 mb-3">
                            <h4 class="text-primary">{{ number_format($stats['chiffre_affaires'], 0) }} F</h4>
                            <small class="text-muted">Chiffre d'affaires</small>
                        </div>
                        <div class="col-6 mb-3">
                            <h4 class="text-success">{{ $stats['total_ventes'] }}</h4>
                            <small class="text-muted">Nombre d'achats</small>
                        </div>
                    </div>

                    @if ($stats['derniere_vente'])
                        <hr>
                        <div class="text-center">
                            <h6>Dernier achat</h6>
                            <p class="text-muted mb-1">{{ $stats['derniere_vente']->created_at->format('d/m/Y H:i') }}</p>
                            <p class="text-primary fw-bold">{{ number_format($stats['derniere_vente']->total_final, 0) }} F
                            </p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Historique des ventes -->
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-shopping-cart me-2"></i>
                        Historique des achats
                    </h5>
                </div>
                <div class="card-body">
                    @if ($client->ventes->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>N° Vente</th>
                                        <th>Boutique</th>
                                        <th>Produits</th>
                                        <th>Total</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($client->ventes as $vente)
                                        <tr>
                                            <td>{{ $vente->created_at->format('d/m/Y H:i') }}</td>
                                            <td>
                                                <span class="badge bg-info">#{{ $vente->numero_vente }}</span>
                                            </td>
                                            <td>{{ $vente->boutique->nom }}</td>
                                            <td>
                                                @foreach ($vente->venteDetails as $detail)
                                                    <span class="badge bg-light text-dark me-1">
                                                        {{ $detail->produit->nom }} ({{ $detail->quantite }})
                                                    </span>
                                                @endforeach
                                            </td>
                                            <td>
                                                <strong>{{ number_format($vente->total_final, 0) }} F</strong>
                                            </td>
                                            <td>
                                                <a href="{{ route('ventes.show', $vente) }}"
                                                    class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-shopping-cart fa-3x text-muted mb-3"></i>
                            <h5>Aucun achat</h5>
                            <p class="text-muted">Ce client n'a pas encore effectué d'achat.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

@endsection
