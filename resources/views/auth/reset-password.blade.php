<x-guest-layout>
    <div class="container-fluid vh-100">
        <div class="row h-100">
            <!-- Left Side - Branding -->
            <div class="col-lg-6 d-none d-lg-flex align-items-center justify-content-center bg-gradient-info">
                <div class="text-center text-white px-5">
                    <div class="mb-4">
                        <i class="fas fa-shield-alt fa-5x opacity-75"></i>
                    </div>
                    <h1 class="display-4 fw-bold mb-3">Nouveau mot de passe</h1>
                    <p class="lead mb-4">Choisissez un mot de passe sécurisé pour votre compte</p>
                    <div class="row text-center">
                        <div class="col-4">
                            <div class="border-end border-white border-opacity-25 pe-3">
                                <h4 class="fw-bold">8+</h4>
                                <small>Caractères</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="border-end border-white border-opacity-25 pe-3">
                                <h4 class="fw-bold">A-Z</h4>
                                <small>Majuscules</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <h4 class="fw-bold">123</h4>
                            <small>Chiffres</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Side - Reset Form -->
            <div class="col-lg-6 d-flex align-items-center justify-content-center">
                <div class="w-100" style="max-width: 450px;">
                    <div class="text-center mb-4">
                        <div class="d-lg-none mb-3">
                            <i class="fas fa-shield-alt fa-3x text-info"></i>
                        </div>
                        <h2 class="fw-bold text-dark">Nouveau mot de passe</h2>
                        <p class="text-muted">Créez un mot de passe sécurisé pour votre compte</p>
                    </div>

                    <form method="POST" action="{{ route('password.store') }}" class="needs-validation" novalidate>
                        @csrf

                        <!-- Password Reset Token -->
                        <input type="hidden" name="token" value="{{ $request->route('token') }}">

                        <!-- Email Address -->
                        <div class="mb-3">
                            <label for="email" class="form-label fw-semibold">
                                <i class="fas fa-envelope me-2 text-info"></i>Adresse email
                            </label>
                            <input id="email" type="email" name="email"
                                value="{{ old('email', $request->email) }}"
                                class="form-control form-control-lg @error('email') is-invalid @enderror"
                                placeholder="votre@email.com" required autofocus autocomplete="username">
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Password -->
                        <div class="mb-3">
                            <label for="password" class="form-label fw-semibold">
                                <i class="fas fa-lock me-2 text-info"></i>Nouveau mot de passe
                            </label>
                            <div class="input-group">
                                <input id="password" type="password" name="password"
                                    class="form-control form-control-lg @error('password') is-invalid @enderror"
                                    placeholder="Votre nouveau mot de passe" required autocomplete="new-password">
                                <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">
                                <i class="fas fa-info-circle me-1"></i>
                                Minimum 8 caractères avec majuscules, minuscules et chiffres
                            </div>
                        </div>

                        <!-- Confirm Password -->
                        <div class="mb-4">
                            <label for="password_confirmation" class="form-label fw-semibold">
                                <i class="fas fa-lock me-2 text-info"></i>Confirmer le mot de passe
                            </label>
                            <div class="input-group">
                                <input id="password_confirmation" type="password" name="password_confirmation"
                                    class="form-control form-control-lg @error('password_confirmation') is-invalid @enderror"
                                    placeholder="Confirmez votre nouveau mot de passe" required
                                    autocomplete="new-password">
                                <button class="btn btn-outline-secondary" type="button"
                                    id="togglePasswordConfirmation">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            @error('password_confirmation')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Submit Button -->
                        <div class="d-grid mb-4">
                            <button type="submit" class="btn btn-info btn-lg">
                                <i class="fas fa-check-circle me-2"></i>
                                Réinitialiser le mot de passe
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

                    <!-- Security Tips -->
                    <div class="mt-4">
                        <div class="card border-0 bg-light">
                            <div class="card-body">
                                <h6 class="card-title">
                                    <i class="fas fa-lightbulb text-info me-2"></i>
                                    Conseils de sécurité
                                </h6>
                                <ul class="list-unstyled small mb-0">
                                    <li><i class="fas fa-check text-success me-2"></i>Utilisez au moins 8 caractères
                                    </li>
                                    <li><i class="fas fa-check text-success me-2"></i>Mélangez majuscules et minuscules
                                    </li>
                                    <li><i class="fas fa-check text-success me-2"></i>Ajoutez des chiffres et symboles
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Password Toggle Scripts -->
    <script>
        // Toggle password visibility
        function togglePasswordVisibility(toggleId, inputId) {
            document.getElementById(toggleId).addEventListener('click', function() {
                const password = document.getElementById(inputId);
                const icon = this.querySelector('i');

                if (password.type === 'password') {
                    password.type = 'text';
                    icon.classList.remove('fa-eye');
                    icon.classList.add('fa-eye-slash');
                } else {
                    password.type = 'password';
                    icon.classList.remove('fa-eye-slash');
                    icon.classList.add('fa-eye');
                }
            });
        }

        togglePasswordVisibility('togglePassword', 'password');
        togglePasswordVisibility('togglePasswordConfirmation', 'password_confirmation');
    </script>
</x-guest-layout>
