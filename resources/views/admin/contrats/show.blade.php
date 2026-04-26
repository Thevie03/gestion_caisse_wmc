@extends('layouts.app')

@section('content')
    <div class="container-fluid px-4 py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-1 fw-bold">Détails du Contrat</h1>
                <p class="text-muted mb-0">{{ $contrat->numero_contrat }}</p>
            </div>
            <div>
                <a href="{{ route('admin.contrats.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Retour
                </a>
                <a href="{{ route('admin.contrats.view', $contrat) }}" class="btn btn-info">
                    <i class="fas fa-eye me-2"></i>Voir le contrat
                </a>
                <a href="{{ route('admin.contrats.edit', $contrat) }}" class="btn btn-warning">
                    <i class="fas fa-edit me-2"></i>Modifier
                </a>
                <a href="{{ route('admin.contrats.download', $contrat) }}" class="btn btn-primary">
                    <i class="fas fa-download me-2"></i>Télécharger
                </a>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-file-contract me-2"></i>Informations du contrat</h5>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <strong>Numéro de contrat :</strong>
                                <p class="mb-0">{{ $contrat->numero_contrat }}</p>
                            </div>
                            <div class="col-md-6">
                                <strong>Type de contrat :</strong>
                                <p class="mb-0">
                                    <span class="badge bg-info">{{ ucfirst($contrat->type_contrat) }}</span>
                                </p>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <strong>Boutique :</strong>
                                <p class="mb-0">
                                    <a href="{{ route('admin.boutiques.show', $contrat->boutique) }}">
                                        {{ $contrat->boutique->nom }}
                                    </a>
                                    <br>
                                    <small class="text-muted">Propriétaire :
                                        {{ $contrat->boutique->owner?->name ?? 'N/A' }}</small>
                                </p>
                            </div>
                            <div class="col-md-6">
                                <strong>Statut :</strong>
                                <p class="mb-0">
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
                                </p>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <strong>Date de signature :</strong>
                                <p class="mb-0">{{ $contrat->date_signature->format('d/m/Y') }}</p>
                            </div>
                            <div class="col-md-6">
                                <strong>Date d'expiration :</strong>
                                <p class="mb-0">
                                    @if ($contrat->date_expiration)
                                        {{ $contrat->date_expiration->format('d/m/Y') }}
                                        @if ($contrat->date_expiration->isPast() && $contrat->statut !== 'resilie')
                                            <span class="badge bg-danger ms-2">Expiré</span>
                                        @elseif($contrat->date_expiration->diffInDays(now()) <= 30)
                                            <span class="badge bg-warning ms-2">Expire bientôt</span>
                                        @endif
                                    @else
                                        <span class="text-muted">Indéterminée</span>
                                    @endif
                                </p>
                            </div>
                        </div>

                        @if ($contrat->montant)
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <strong>Montant :</strong>
                                    <p class="mb-0 fs-5 fw-bold">{{ number_format($contrat->montant, 0, ',', ' ') }} FCFA
                                    </p>
                                </div>
                            </div>
                        @endif

                        @if ($contrat->notes)
                            <div class="mb-3">
                                <strong>Notes :</strong>
                                <p class="mb-0">{{ $contrat->notes }}</p>
                            </div>
                        @endif

                        <div class="row">
                            <div class="col-md-6">
                                <strong>Créé par :</strong>
                                <p class="mb-0">{{ $contrat->createur->name ?? 'N/A' }}</p>
                            </div>
                            <div class="col-md-6">
                                <strong>Date de création :</strong>
                                <p class="mb-0">{{ $contrat->created_at->format('d/m/Y à H:i') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-secondary text-white">
                        <h5 class="mb-0"><i class="fas fa-file me-2"></i>Fichier</h5>
                    </div>
                    <div class="card-body text-center">
                        <i class="fas fa-file-pdf fa-4x text-danger mb-3"></i>
                        <p class="mb-3">
                            <strong>{{ basename($contrat->fichier_contrat) }}</strong>
                        </p>
                        <div class="d-grid gap-2">
                            <a href="{{ route('admin.contrats.view', $contrat) }}" class="btn btn-info w-100">
                                <i class="fas fa-eye me-2"></i>Voir le contrat
                            </a>
                            <a href="{{ route('admin.contrats.download', $contrat) }}" class="btn btn-primary w-100">
                                <i class="fas fa-download me-2"></i>Télécharger
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
