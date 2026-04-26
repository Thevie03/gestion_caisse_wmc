@extends('layouts.app')

@section('content')
    <div class="row">
        <div class="col-lg-4 mb-4">
            <!-- Carte de profil -->
            <div class="card">
                <div class="card-body text-center">
                    <div class="avatar bg-danger text-white rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3"
                        style="width: 100px; height: 100px; font-size: 2.5rem;">
                        <i class="fas fa-user-shield"></i>
                    </div>
                    <h4 class="mb-1">{{ $admin->name }}</h4>
                    <p class="text-muted mb-3">
                        <span class="badge bg-danger">Administrateur</span>
                    </p>

                    <div class="d-grid gap-2">
                        <a href="{{ route('admins.edit', $admin) }}" class="btn btn-primary">
                            <i class="fas fa-edit me-2"></i>
                            Modifier
                        </a>
                        <a href="{{ route('admins.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-2"></i>
                            Retour à la liste
                        </a>
                    </div>
                </div>
            </div>

            <!-- Informations de contact -->
            <div class="card mt-4">
                <div class="card-header">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        Informations
                    </h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <strong><i class="fas fa-envelope me-2 text-primary"></i>Email :</strong><br>
                        <a href="mailto:{{ $admin->email }}">{{ $admin->email }}</a>
                    </div>
                    <div class="mb-3">
                        <strong><i class="fas fa-phone me-2 text-primary"></i>Téléphone :</strong><br>
                        <a href="tel:{{ $admin->telephone }}">{{ $admin->telephone }}</a>
                    </div>
                    <div class="mb-3">
                        <strong><i class="fas fa-store me-2 text-primary"></i>Boutique :</strong><br>
                        @if ($admin->boutique)
                            <span class="badge bg-primary">{{ $admin->boutique->nom }}</span>
                        @else
                            <span class="badge bg-info">Toutes les boutiques</span>
                        @endif
                    </div>
                    <div>
                        <strong><i class="fas fa-shield-alt me-2 text-success"></i>Permissions :</strong><br>
                        <span class="badge bg-success">
                            <i class="fas fa-check-circle me-1"></i>
                            Accès complet à toutes les fonctionnalités
                        </span>
                        <small class="d-block text-muted mt-1">Les administrateurs ont automatiquement toutes les
                            permissions</small>
                    </div>
                    <div class="mb-3">
                        <strong><i class="fas fa-calendar me-2 text-primary"></i>Membre depuis :</strong><br>
                        {{ $admin->created_at->format('d/m/Y') }}
                    </div>
                    <div>
                        <strong><i class="fas fa-circle me-2 text-primary"></i>Statut :</strong><br>
                        @if ($admin->actif)
                            <span class="badge bg-success">
                                <i class="fas fa-check me-1"></i>
                                Actif
                            </span>
                        @else
                            <span class="badge bg-danger">
                                <i class="fas fa-times me-1"></i>
                                Inactif
                            </span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <!-- Statistiques -->
            <div class="row mb-4">
                <div class="col-md-6 mb-3">
                    <div class="card stat-card">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <div class="stat-value">{{ $stats['total_ventes'] }}</div>
                                <div class="stat-label">Total ventes</div>
                            </div>
                            <div class="ms-3">
                                <i class="fas fa-shopping-cart fa-2x opacity-75"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 mb-3">
                    <div class="card stat-card bg-success text-white">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <div class="stat-value">{{ number_format($stats['chiffre_affaires'], 0, ',', ' ') }} FCFA
                                </div>
                                <div class="stat-label">Chiffre d'affaires</div>
                            </div>
                            <div class="ms-3">
                                <i class="fas fa-money-bill-wave fa-2x opacity-75"></i>
                            </div>
                        </div>
                    </div>
                </div>

                @if ($stats['total_ventes'] > 0)
                    <div class="col-md-6 mb-3">
                        <div class="card stat-card bg-info text-white">
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1">
                                    <div class="stat-value">{{ number_format($stats['moyenne_vente'], 0, ',', ' ') }} FCFA
                                    </div>
                                    <div class="stat-label">Moyenne par vente</div>
                                </div>
                                <div class="ms-3">
                                    <i class="fas fa-chart-line fa-2x opacity-75"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Ventes récentes -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-shopping-cart me-2"></i>
                        Ventes récentes
                    </h5>
                </div>
                <div class="card-body">
                    @if ($ventesRecentes->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Montant</th>
                                        <th>Boutique</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($ventesRecentes as $vente)
                                        <tr>
                                            <td>{{ $vente->created_at->format('d/m/Y H:i') }}</td>
                                            <td>
                                                <strong>{{ number_format($vente->total_final, 0, ',', ' ') }} FCFA</strong>
                                            </td>
                                            <td>
                                                @if ($vente->boutique)
                                                    <span class="badge bg-primary">{{ $vente->boutique->nom }}</span>
                                                @else
                                                    <span class="badge bg-secondary">N/A</span>
                                                @endif
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
                        <div class="text-center py-5">
                            <i class="fas fa-shopping-cart fa-3x text-muted mb-3"></i>
                            <p class="text-muted">Aucune vente enregistrée</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
