<form id="send-verification" method="post" action="{{ route('verification.send') }}">
    @csrf
</form>

<form method="post" action="{{ route('profile.update') }}">
    @csrf
    @method('patch')

    <div class="mb-3">
        <label for="name" class="form-label fw-semibold">
            <i class="fas fa-user me-2 text-primary"></i>
            Nom complet
        </label>
        <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name"
            value="{{ old('name', $user->name) }}" required autofocus autocomplete="name"
            placeholder="Votre nom complet">
        @error('name')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="mb-3">
        <label for="email" class="form-label fw-semibold">
            <i class="fas fa-envelope me-2 text-primary"></i>
            Adresse email
        </label>
        <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email"
            value="{{ old('email', $user->email) }}" required autocomplete="username" placeholder="votre@email.com">
        @error('email')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror

        @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && !$user->hasVerifiedEmail())
            <div class="alert alert-warning mt-2 mb-0">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <strong>Email non vérifié.</strong>
                <button form="send-verification" class="btn btn-link btn-sm p-0 ms-2 text-decoration-none">
                    Renvoyer l'email de vérification
                </button>
            </div>

            @if (session('status') === 'verification-link-sent')
                <div class="alert alert-success mt-2 mb-0">
                    <i class="fas fa-check-circle me-2"></i>
                    Un nouveau lien de vérification a été envoyé à votre adresse email.
                </div>
            @endif
        @endif
    </div>

    <div class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top">
        <div>
            @if (session('status') === 'profile-updated')
                <div class="alert alert-success mb-0 py-2 px-3" role="alert">
                    <i class="fas fa-check-circle me-2"></i>
                    <span>Profil mis à jour avec succès !</span>
                </div>
            @endif
        </div>
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-save me-2"></i>
            Enregistrer les modifications
        </button>
    </div>
</form>
