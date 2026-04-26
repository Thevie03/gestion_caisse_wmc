<p class="text-muted small mb-3">
    <i class="fas fa-info-circle me-1"></i>
    Assurez-vous d'utiliser un mot de passe long et aléatoire pour rester en sécurité.
</p>

<form method="post" action="{{ route('password.update') }}">
    @csrf
    @method('put')

    <div class="mb-3">
        <label for="current_password" class="form-label fw-semibold">
            <i class="fas fa-lock me-2 text-primary"></i>
            Mot de passe actuel
        </label>
        <input type="password" class="form-control @error('current_password', 'updatePassword') is-invalid @enderror"
            id="current_password" name="current_password" autocomplete="current-password"
            placeholder="Entrez votre mot de passe actuel">
        @error('current_password', 'updatePassword')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="mb-3">
        <label for="password" class="form-label fw-semibold">
            <i class="fas fa-key me-2 text-primary"></i>
            Nouveau mot de passe
        </label>
        <input type="password" class="form-control @error('password', 'updatePassword') is-invalid @enderror"
            id="password" name="password" autocomplete="new-password" placeholder="Entrez votre nouveau mot de passe">
        @error('password', 'updatePassword')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="mb-3">
        <label for="password_confirmation" class="form-label fw-semibold">
            <i class="fas fa-check-double me-2 text-primary"></i>
            Confirmer le mot de passe
        </label>
        <input type="password"
            class="form-control @error('password_confirmation', 'updatePassword') is-invalid @enderror"
            id="password_confirmation" name="password_confirmation" autocomplete="new-password"
            placeholder="Confirmez votre nouveau mot de passe">
        @error('password_confirmation', 'updatePassword')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top">
        <div>
            @if (session('status') === 'password-updated')
                <div class="alert alert-success mb-0 py-2 px-3" role="alert">
                    <i class="fas fa-check-circle me-2"></i>
                    <span>Mot de passe mis à jour !</span>
                </div>
            @endif
        </div>
        <button type="submit" class="btn btn-success">
            <i class="fas fa-save me-2"></i>
            Mettre à jour
        </button>
    </div>
</form>
