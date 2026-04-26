@extends('layouts.app')

@section('content')
    <div class="container-fluid px-4 py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-1 fw-bold">{{ $boutique->nom }}</h1>
                <p class="text-muted mb-0">Détails et supervision de la boutique</p>
            </div>
            <div>
                <a href="{{ route('admin.boutiques.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Retour
                </a>
                <a href="{{ route('admin.boutiques.supervision', $boutique->id) }}" class="btn btn-primary">
                    <i class="fas fa-store me-2"></i>Superviser
                </a>
            </div>
        </div>

        <!-- Statistiques -->
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <p class="text-muted small mb-1">Chiffre d'affaires</p>
                        <h3 class="mb-0 fw-bold text-success">
                            {{ number_format($stats['chiffre_affaires'], 0, ',', ' ') }} {{ $boutique->devise ?? 'FCFA' }}
                        </h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <p class="text-muted small mb-1">Total produits/articles</p>
                        <h3 class="mb-0 fw-bold">{{ number_format($stats['produits_total']) }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <p class="text-muted small mb-1">Total Ventes</p>
                        <h3 class="mb-0 fw-bold">{{ number_format($stats['ventes_total']) }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <p class="text-muted small mb-1">Total Dépenses</p>
                        <h3 class="mb-0 fw-bold text-danger">
                            {{ number_format($stats['depenses_total'], 0, ',', ' ') }} {{ $boutique->devise ?? 'FCFA' }}
                        </h3>
                    </div>
                </div>
            </div>
        </div>

        <!-- Informations de la boutique -->
        <div class="row g-3 mb-4">
            <div class="col-md-6">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-bottom">
                        <h5 class="mb-0 fw-semibold">Informations</h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-borderless">
                            <tr>
                                <td class="fw-semibold">Nom :</td>
                                <td>{{ $boutique->nom }}</td>
                            </tr>
                            <tr>
                                <td class="fw-semibold">Propriétaire :</td>
                                <td>
                                    {{ $boutique->owner?->name ?? 'N/A' }}
                                    <br>
                                    <small class="text-muted">{{ $boutique->owner?->email ?? '' }}</small>
                                </td>
                            </tr>
                            <tr>
                                <td class="fw-semibold">Adresse :</td>
                                <td>{{ $boutique->adresse ?? 'Non définie' }}</td>
                            </tr>
                            <tr>
                                <td class="fw-semibold">Téléphone :</td>
                                <td>{{ $boutique->telephone ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <td class="fw-semibold">Email :</td>
                                <td>{{ $boutique->email ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <td class="fw-semibold">Devise :</td>
                                <td><span class="badge bg-info">{{ $boutique->devise ?? 'FCFA' }}</span></td>
                            </tr>
                            <tr>
                                <td class="fw-semibold">Statut :</td>
                                <td>
                                    <span class="badge bg-{{ $boutique->actif ? 'success' : 'danger' }}">
                                        {{ $boutique->actif ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td class="fw-semibold">Date création :</td>
                                <td>{{ $boutique->created_at->format('d/m/Y H:i') }}</td>
                            </tr>
                        </table>
                        <div class="mt-3 d-flex gap-2">
                            <a href="{{ route('admin.boutiques.edit', $boutique) }}" class="btn btn-primary">
                                <i class="fas fa-edit me-2"></i>Modifier
                            </a>
                            @if (auth()->user()->isAdmin())
                                <button type="button" class="btn btn-danger" data-bs-toggle="modal"
                                    data-bs-target="#deleteBoutiqueModal">
                                    <i class="fas fa-trash-alt me-2"></i>Supprimer
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-bottom">
                        <h5 class="mb-0 fw-semibold">Dernières ventes</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Total</th>
                                        <th>Statut</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($boutique->ventes as $vente)
                                        <tr>
                                            <td>{{ $vente->created_at->format('d/m/Y H:i') }}</td>
                                            <td>{{ number_format($vente->total_final, 0, ',', ' ') }}
                                                {{ $boutique->devise ?? 'FCFA' }}</td>
                                            <td>
                                                <span
                                                    class="badge bg-{{ $vente->statut_paiement === 'complet' ? 'success' : 'warning' }}">
                                                    {{ ucfirst($vente->statut_paiement) }}
                                                </span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="text-center text-muted">Aucune vente</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if (auth()->user()->isAdmin())
        <!-- Modal de suppression -->
        <div class="modal fade" id="deleteBoutiqueModal" tabindex="-1" aria-labelledby="deleteBoutiqueModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title" id="deleteBoutiqueModalLabel">
                            <i class="fas fa-exclamation-triangle me-2"></i>Supprimer la boutique
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        @if (session('boutique_data'))
                            <div class="alert alert-warning">
                                <h6 class="fw-bold">Cette boutique contient des données :</h6>
                                <ul class="mb-0">
                                    @foreach (session('boutique_data') as $type => $count)
                                        @if ($count > 0)
                                            <li>{{ ucfirst($type) }}: {{ $count }}</li>
                                        @endif
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <p class="mb-3">
                            <strong>Êtes-vous sûr de vouloir supprimer la boutique "{{ $boutique->nom }}" ?</strong>
                        </p>

                        @php
                            $hasData =
                                isset($dataCount) &&
                                ($dataCount['ventes'] > 0 ||
                                    $dataCount['produits'] > 0 ||
                                    $dataCount['depenses'] > 0 ||
                                    $dataCount['utilisateurs'] > 0 ||
                                    $dataCount['categories'] > 0 ||
                                    $dataCount['clients'] > 0 ||
                                    $dataCount['fournisseurs'] > 0);
                        @endphp

                        @if ($hasData)
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <strong>Attention !</strong> Cette boutique contient des données (ventes, produits,
                                dépenses, utilisateurs).
                                La suppression supprimera également toutes ces données de manière définitive.
                            </div>

                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" id="forceDelete" name="force"
                                    value="1">
                                <label class="form-check-label" for="forceDelete">
                                    <strong>Je comprends et je souhaite supprimer la boutique avec toutes ses
                                        données</strong>
                                </label>
                            </div>
                        @endif

                        <p class="text-muted small mb-0">
                            <i class="fas fa-info-circle me-1"></i>
                            Cette action est irréversible. Les utilisateurs associés seront désassociés de la boutique.
                        </p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times me-2"></i>Annuler
                        </button>
                        <form action="{{ route('admin.boutiques.destroy', $boutique) }}" method="POST"
                            id="deleteBoutiqueForm">
                            @csrf
                            @method('DELETE')
                            <input type="hidden" name="force" id="forceDeleteInput" value="0">
                            <button type="submit" class="btn btn-danger" id="confirmDeleteBtn" disabled>
                                <i class="fas fa-trash-alt me-2"></i>Supprimer définitivement
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const forceDeleteCheckbox = document.getElementById('forceDelete');
                const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
                const forceDeleteInput = document.getElementById('forceDeleteInput');
                const deleteForm = document.getElementById('deleteBoutiqueForm');
                const hasData = {{ $hasData ? 'true' : 'false' }};

                if (forceDeleteCheckbox) {
                    forceDeleteCheckbox.addEventListener('change', function() {
                        if (this.checked) {
                            confirmDeleteBtn.disabled = false;
                            forceDeleteInput.value = '1';
                        } else {
                            confirmDeleteBtn.disabled = true;
                            forceDeleteInput.value = '0';
                        }
                    });
                } else {
                    // Si pas de données, permettre la suppression directement
                    if (!hasData) {
                        confirmDeleteBtn.disabled = false;
                    }
                }

                // Confirmation finale
                deleteForm.addEventListener('submit', function(e) {
                    if (hasData && (!forceDeleteCheckbox || !forceDeleteCheckbox.checked)) {
                        e.preventDefault();
                        alert(
                            'Veuillez cocher la case de confirmation pour supprimer la boutique avec ses données.'
                            );
                        return false;
                    }

                    const message = hasData && forceDeleteCheckbox?.checked ?
                        'Êtes-vous ABSOLUMENT SÛR de vouloir supprimer cette boutique et TOUTES ses données ? Cette action est irréversible !' :
                        'Êtes-vous sûr de vouloir supprimer cette boutique ?';

                    if (!confirm(message)) {
                        e.preventDefault();
                        return false;
                    }
                });
            });
        </script>
    @endif
@endsection
