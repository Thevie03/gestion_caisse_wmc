@extends('layouts.app')

@section('content')
    <div class="container-fluid px-4 py-4">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <nav aria-label="breadcrumb" class="mb-2">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('admin.paiements.index') }}"
                                class="text-decoration-none">Paiements</a></li>
                        <li class="breadcrumb-item active">Détails du paiement</li>
                    </ol>
                </nav>
                <h1 class="h3 mb-1 fw-bold">Détails du paiement</h1>
                <p class="text-muted mb-0">Référence: {{ $paiement->reference ?? 'N/A' }}</p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.paiements.edit', $paiement) }}" class="btn btn-primary">
                    <i class="fas fa-edit me-2"></i>Modifier
                </a>
                <a href="{{ route('admin.paiements.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Retour
                </a>
            </div>
        </div>

        <div class="row g-4">
            <!-- Informations du paiement -->
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white border-bottom py-3">
                        <h5 class="mb-0 fw-semibold">
                            <i class="fas fa-money-bill-wave me-2 text-success"></i>Informations du paiement
                        </h5>
                    </div>
                    <div class="card-body">
                        <dl class="row mb-0">
                            <dt class="col-sm-5 fw-semibold text-dark">Montant :</dt>
                            <dd class="col-sm-7 text-dark">
                                <strong class="text-success">{{ number_format($paiement->montant, 0, ',', ' ') }}
                                    FCFA</strong>
                            </dd>

                            <dt class="col-sm-5 fw-semibold text-dark">Mode de paiement :</dt>
                            <dd class="col-sm-7 text-dark">
                                <span class="badge bg-info">
                                    {{ ucfirst(str_replace('_', ' ', $paiement->mode_paiement)) }}
                                </span>
                            </dd>

                            <dt class="col-sm-5 fw-semibold text-dark">Date de paiement :</dt>
                            <dd class="col-sm-7 text-dark">{{ $paiement->date_paiement->format('d/m/Y') }}</dd>

                            <dt class="col-sm-5 fw-semibold text-dark">Statut :</dt>
                            <dd class="col-sm-7">
                                <span
                                    class="badge bg-{{ $paiement->statut === 'confirme' ? 'success' : ($paiement->statut === 'en_attente' ? 'warning' : 'danger') }}">
                                    {{ strtoupper(str_replace('_', ' ', $paiement->statut)) }}
                                </span>
                            </dd>

                            <dt class="col-sm-5 fw-semibold text-dark">Référence :</dt>
                            <dd class="col-sm-7 text-dark">
                                <code>{{ $paiement->reference ?? 'N/A' }}</code>
                            </dd>

                            @if ($paiement->confirmePar)
                                <dt class="col-sm-5 fw-semibold text-dark">Confirmé par :</dt>
                                <dd class="col-sm-7 text-dark">{{ $paiement->confirmePar->name }}</dd>

                                <dt class="col-sm-5 fw-semibold text-dark">Date de confirmation :</dt>
                                <dd class="col-sm-7 text-dark">
                                    {{ $paiement->confirme_le?->format('d/m/Y à H:i') ?? 'N/A' }}
                                </dd>
                            @endif

                            @if ($paiement->notes)
                                <dt class="col-sm-5 fw-semibold text-dark">Notes :</dt>
                                <dd class="col-sm-7 text-dark">{{ $paiement->notes }}</dd>
                            @endif
                        </dl>
                    </div>
                </div>
            </div>

            <!-- Informations du client -->
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white border-bottom py-3">
                        <h5 class="mb-0 fw-semibold">
                            <i class="fas fa-user me-2 text-primary"></i>Informations du client
                        </h5>
                    </div>
                    <div class="card-body">
                        <dl class="row mb-0">
                            <dt class="col-sm-4 fw-semibold text-dark">Nom :</dt>
                            <dd class="col-sm-8 text-dark">{{ $paiement->user->name }}</dd>

                            <dt class="col-sm-4 fw-semibold text-dark">Email :</dt>
                            <dd class="col-sm-8 text-dark">{{ $paiement->user->email }}</dd>

                            <dt class="col-sm-4 fw-semibold text-dark">Téléphone :</dt>
                            <dd class="col-sm-8 text-dark">{{ $paiement->user->telephone ?? 'N/A' }}</dd>

                            <dt class="col-sm-4 fw-semibold text-dark">Boutique :</dt>
                            <dd class="col-sm-8 text-dark">{{ $paiement->user->boutique?->nom ?? 'N/A' }}</dd>
                        </dl>
                    </div>
                </div>
            </div>

            <!-- Informations de l'abonnement -->
            <div class="col-lg-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-bottom py-3">
                        <h5 class="mb-0 fw-semibold">
                            <i class="fas fa-calendar-alt me-2 text-info"></i>Informations de l'abonnement
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="text-center p-3 bg-light rounded">
                                    <p class="text-muted small mb-1">Type d'abonnement</p>
                                    <h5 class="mb-0 fw-bold">
                                        <span class="badge bg-info">{{ $abonnement->type_label }}</span>
                                    </h5>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="text-center p-3 bg-light rounded">
                                    <p class="text-muted small mb-1">Montant total</p>
                                    <h5 class="mb-0 fw-bold text-dark">
                                        {{ number_format($abonnement->montant, 0, ',', ' ') }} FCFA
                                    </h5>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="text-center p-3 bg-light rounded">
                                    <p class="text-muted small mb-1">Montant payé</p>
                                    <h5 class="mb-0 fw-bold text-success">
                                        {{ number_format($montantPaye, 0, ',', ' ') }} FCFA
                                    </h5>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="text-center p-3 bg-light rounded">
                                    <p class="text-muted small mb-1">Reste à payer</p>
                                    <h5
                                        class="mb-0 fw-bold {{ $montantRestant > 0.01 ? 'text-warning' : 'text-success' }}">
                                        @if ($montantRestant > 0.01)
                                            {{ number_format($montantRestant, 0, ',', ' ') }} FCFA
                                        @else
                                            <i class="fas fa-check-circle me-1"></i>Payé
                                        @endif
                                    </h5>
                                </div>
                            </div>
                        </div>
                        <hr>
                        <dl class="row mb-0">
                            <dt class="col-sm-3 fw-semibold text-dark">Date de début :</dt>
                            <dd class="col-sm-3 text-dark">{{ $abonnement->date_debut->format('d/m/Y') }}</dd>

                            <dt class="col-sm-3 fw-semibold text-dark">Date d'expiration :</dt>
                            <dd class="col-sm-3 text-dark">
                                <span class="{{ $abonnement->estExpire() ? 'text-danger' : '' }}">
                                    {{ $abonnement->date_expiration->format('d/m/Y') }}
                                </span>
                            </dd>

                            <dt class="col-sm-3 fw-semibold text-dark">Statut :</dt>
                            <dd class="col-sm-3">
                                <span
                                    class="badge bg-{{ $abonnement->statut === 'actif' ? 'success' : ($abonnement->statut === 'suspendu' ? 'warning' : 'danger') }}">
                                    {{ strtoupper($abonnement->statut) }}
                                </span>
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
