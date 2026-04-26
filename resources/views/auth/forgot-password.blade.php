<x-guest-layout>
    <div class="container-fluid vh-100">
        <div class="row h-100">
            <!-- Left Side - Branding -->
            <div class="col-lg-6 d-none d-lg-flex align-items-center justify-content-center bg-gradient-warning">
                <div class="text-center text-white px-5">
                    <div class="mb-4">
                        <i class="fas fa-key fa-5x opacity-75"></i>
                    </div>
                    <h1 class="display-4 fw-bold mb-3">Mot de passe oublié ?</h1>
                    <p class="lead mb-4">Pas de problème, nous allons vous aider à le récupérer</p>
                    <div class="row text-center">
                        <div class="col-4">
                            <div class="border-end border-white border-opacity-25 pe-3">
                                <h4 class="fw-bold">1</h4>
                                <small>Étape</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="border-end border-white border-opacity-25 pe-3">
                                <h4 class="fw-bold">2</h4>
                                <small>Minutes</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <h4 class="fw-bold">100%</h4>
                            <small>Sécurisé</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Side - Reset Form -->
            <div class="col-lg-6 d-flex align-items-center justify-content-center">
                <div class="w-100" style="max-width: 400px;">
                    <div class="text-center mb-4">
                        <div class="d-lg-none mb-3">
                            <i class="fas fa-key fa-3x text-warning"></i>
                        </div>
                        <h2 class="fw-bold text-dark">Réinitialiser le mot de passe</h2>
                        <p class="text-muted">Entrez votre email pour recevoir un lien de réinitialisation</p>
                    </div>

                    <!-- Session Status -->
                    <x-auth-session-status class="mb-4" :status="session('status')" />

                    <form method="POST" action="{{ route('password.email') }}" class="needs-validation" novalidate>
                        @csrf

                        <!-- Email Address -->
                        <div class="mb-4">
                            <label for="email" class="form-label fw-semibold">
                                <i class="fas fa-envelope me-2 text-warning"></i>Adresse email
                            </label>
                            <input id="email" type="email" name="email" value="{{ old('email') }}"
                                class="form-control form-control-lg @error('email') is-invalid @enderror"
                                placeholder="votre@email.com" required autofocus>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">
                                <i class="fas fa-info-circle me-1"></i>
                                Nous vous enverrons un lien de réinitialisation à cette adresse
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="d-grid mb-4">
                            <button type="submit" class="btn btn-warning btn-lg">
                                <i class="fas fa-paper-plane me-2"></i>
                                Envoyer le lien de réinitialisation
                            </button>
                        </div>

                        <!-- Back to Login -->
                        <div class="text-center">
                            <p class="mb-0">
                                Vous vous souvenez de votre mot de passe ?
                                <a href="{{ route('login') }}" class="text-decoration-none fw-semibold">
                                    Se connecter
                                </a>
                            </p>
                        </div>
                    </form>

                    <!-- Help Card -->
                    <div class="mt-4">
                        <div class="card border-0 bg-light">
                            <div class="card-body text-center">
                                <i class="fas fa-question-circle text-warning fa-2x mb-2"></i>
                                <h6 class="card-title">Besoin d'aide ?</h6>
                                <p class="card-text small text-muted">
                                    Si vous ne recevez pas l'email, vérifiez votre dossier spam ou contactez
                                    l'administrateur.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-guest-layout>
