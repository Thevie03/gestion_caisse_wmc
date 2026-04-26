@extends('layouts.app')

@section('content')

    <div class="row">
        <div class="col-md-8">
            <!-- Informations principales -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-receipt me-2"></i>
                        Informations de la dépense
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Description</label>
                                <p class="form-control-plaintext">{{ $depense->description }}</p>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Catégorie</label>
                                <p class="form-control-plaintext">
                                    <span class="badge bg-secondary fs-6">{{ $depense->categorie }}</span>
                                </p>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Montant</label>
                                <p class="form-control-plaintext">
                                    <span
                                        class="fs-4 text-danger fw-bold">{{ number_format($depense->montant, 0, ',', ' ') }}
                                        FCFA</span>
                                </p>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Date</label>
                                <p class="form-control-plaintext">
                                    <i class="fas fa-calendar me-2"></i>
                                    {{ $depense->date_depense->format('d/m/Y') }}
                                </p>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Boutique</label>
                                <p class="form-control-plaintext">
                                    <span class="badge bg-primary fs-6">{{ $depense->boutique->nom }}</span>
                                </p>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Enregistré le</label>
                                <p class="form-control-plaintext">
                                    <i class="fas fa-clock me-2"></i>
                                    {{ $depense->created_at->format('d/m/Y à H:i') }}
                                </p>
                            </div>
                        </div>
                    </div>

                    @if ($depense->notes)
                        <div class="mb-3">
                            <label class="form-label fw-bold">Justificatif</label>
                            <div class="alert alert-light border">
                                <i class="fas fa-file-alt me-2"></i>
                                {{ $depense->notes }}
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <!-- Informations utilisateur -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-user me-2"></i>
                        Utilisateur
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avatar bg-primary text-white rounded-circle d-flex align-items-center justify-content-center"
                                style="width: 50px; height: 50px;">
                                <i class="fas fa-user"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-1">{{ $depense->user->name }}</h6>
                            <p class="text-muted mb-0">{{ $depense->user->email }}</p>
                            @if ($depense->user->telephone)
                                <small class="text-muted">
                                    <i class="fas fa-phone me-1"></i>
                                    {{ $depense->user->telephone }}
                                </small>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-cogs me-2"></i>
                        Actions
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('depenses.edit', $depense) }}" class="btn btn-primary">
                            <i class="fas fa-edit me-2"></i>
                            Modifier la dépense
                        </a>
                        <button type="button" class="btn btn-danger"
                            onclick="confirmerSuppression('{{ $depense->description }}', '{{ route('depenses.destroy', $depense) }}')">
                            <i class="fas fa-trash me-2"></i>
                            Supprimer la dépense
                        </button>
                        <a href="{{ route('depenses.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-list me-2"></i>
                            Voir toutes les dépenses
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Script pour la confirmation de suppression -->
    <script>
        function confirmerSuppression(description, urlSuppression) {
            const modalHtml = `
                <div class="modal fade" id="modalSuppression" tabindex="-1" aria-labelledby="modalSuppressionLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header bg-danger text-white">
                                <h5 class="modal-title" id="modalSuppressionLabel">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    Confirmation de suppression
                                </h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="text-center mb-3">
                                    <i class="fas fa-trash fa-3x text-danger mb-3"></i>
                                    <h4>Êtes-vous sûr de vouloir supprimer cette dépense ?</h4>
                                </div>
                                <div class="alert alert-warning">
                                    <strong>Dépense :</strong> ${description}
                                </div>
                                <div class="alert alert-danger">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    <strong>Attention :</strong> Cette action est irréversible ! La dépense sera définitivement supprimée.
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                    <i class="fas fa-times me-2"></i>
                                    Annuler
                                </button>
                                <form method="POST" action="${urlSuppression}" style="display: inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger">
                                        <i class="fas fa-trash me-2"></i>
                                        Supprimer définitivement
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            `;

            const existingModal = document.getElementById('modalSuppression');
            if (existingModal) {
                existingModal.remove();
            }

            document.body.insertAdjacentHTML('beforeend', modalHtml);

            const modal = new bootstrap.Modal(document.getElementById('modalSuppression'));
            modal.show();
        }
    </script>
@endsection
