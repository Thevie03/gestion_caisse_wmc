<p class="text-muted small mb-3">
    <i class="fas fa-exclamation-triangle me-1 text-warning"></i>
    Une fois votre compte supprimé, toutes vos ressources et données seront définitivement supprimées. Avant de
    supprimer votre compte, veuillez télécharger toutes les données ou informations que vous souhaitez conserver.
</p>

<button type="button" class="btn btn-outline-danger w-100" data-bs-toggle="modal"
    data-bs-target="#confirmUserDeletionModal">
    <i class="fas fa-trash-alt me-2"></i>
    Supprimer mon compte
</button>

<!-- Modal de confirmation -->
<div class="modal fade" id="confirmUserDeletionModal" tabindex="-1" aria-labelledby="confirmUserDeletionModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-danger">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="confirmUserDeletionModalLabel">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Confirmer la suppression du compte
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <form method="post" action="{{ route('profile.destroy') }}">
                @csrf
                @method('delete')

                <div class="modal-body">
                    <div class="alert alert-danger mb-3">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Attention !</strong> Cette action est irréversible.
                    </div>

                    <p class="mb-3">
                        Êtes-vous sûr de vouloir supprimer votre compte ? Une fois votre compte supprimé, toutes vos
                        ressources et données seront définitivement supprimées.
                    </p>

                    <p class="text-muted small mb-3">
                        Veuillez entrer votre mot de passe pour confirmer que vous souhaitez supprimer définitivement
                        votre compte.
                    </p>

                    <div class="mb-3">
                        <label for="password" class="form-label fw-semibold">
                            <i class="fas fa-lock me-2"></i>
                            Mot de passe
                        </label>
                        <input type="password"
                            class="form-control @error('password', 'userDeletion') is-invalid @enderror" id="password"
                            name="password" placeholder="Entrez votre mot de passe" required>
                        @error('password', 'userDeletion')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-2"></i>
                        Annuler
                    </button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash-alt me-2"></i>
                        Supprimer définitivement mon compte
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
