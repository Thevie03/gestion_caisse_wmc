<x-guest-layout>
    <div class="container-fluid vh-100">
        <div class="row h-100">
            <!-- Left Side - Branding -->
            <div class="col-lg-6 d-none d-lg-flex align-items-center justify-content-center bg-gradient-success">
                <div class="text-center text-white px-5">
                    <div class="mb-4">
                        <i class="fas fa-user-plus fa-5x opacity-75"></i>
                    </div>
                    <h1 class="display-4 fw-bold mb-3">Rejoignez-nous</h1>
                    <p class="lead mb-4">Créez votre compte et commencez à gérer vos boutiques</p>
                    <div class="row g-3 mb-4">
                        <div class="col-12">
                            <div class="position-relative overflow-hidden rounded-4 shadow-lg border border-white border-opacity-25">
                                <img
                                    src="{{ asset('images/pos/pos-banner.jpg') }}"
                                    alt="Terminal point de vente"
                                    class="w-100"
                                    style="height: 190px; object-fit: cover;">
                                <div class="position-absolute bottom-0 start-0 end-0 p-3"
                                    style="background: linear-gradient(180deg, rgba(0,0,0,0) 0%, rgba(0,0,0,0.72) 100%);">
                                    <small class="fw-semibold text-white">Interface caisse en magasin</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="position-relative overflow-hidden rounded-4 shadow border border-white border-opacity-25">
                                <img
                                    src="{{ asset('images/pos/pos-stock.jpg') }}"
                                    alt="Gestion des stocks"
                                    class="w-100"
                                    style="height: 120px; object-fit: cover;">
                                <div class="position-absolute bottom-0 start-0 end-0 px-2 py-1"
                                    style="background: rgba(0, 0, 0, 0.55);">
                                    <small class="text-white">Stock</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="position-relative overflow-hidden rounded-4 shadow border border-white border-opacity-25">
                                <img
                                    src="{{ asset('images/pos/pos-payment.jpg') }}"
                                    alt="Paiement et ticket"
                                    class="w-100"
                                    style="height: 120px; object-fit: cover;">
                                <div class="position-absolute bottom-0 start-0 end-0 px-2 py-1"
                                    style="background: rgba(0, 0, 0, 0.55);">
                                    <small class="text-white">Paiement</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row text-center">
                        <div class="col-4">
                            <div class="border-end border-white border-opacity-25 pe-3">
                                <h4 class="fw-bold">100%</h4>
                                <small>Gratuit</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="border-end border-white border-opacity-25 pe-3">
                                <h4 class="fw-bold">24/7</h4>
                                <small>Support</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <h4 class="fw-bold">∞</h4>
                            <small>Produits</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Side - Register Form -->
            <div class="col-lg-6 d-flex align-items-center justify-content-center">
                <div class="w-100" style="max-width: 450px;">
                    <div class="text-center mb-4">
                        <div class="d-lg-none mb-3">
                            <i class="fas fa-user-plus fa-3x text-success"></i>
                        </div>
                        <h2 class="fw-bold text-dark">Créer un compte</h2>
                        <p class="text-muted">Rejoignez l'équipe GestionCaisse</p>
                    </div>

                    <form method="POST" action="{{ route('register') }}" class="needs-validation" novalidate>
                        @csrf

                        <!-- Name -->
                        <div class="mb-3">
                            <label for="name" class="form-label fw-semibold">
                                <i class="fas fa-user me-2 text-success"></i>Nom complet
                            </label>
                            <input id="name" type="text" name="name" value="{{ old('name') }}"
                                class="form-control form-control-lg @error('name') is-invalid @enderror"
                                placeholder="Votre nom complet" required autofocus autocomplete="name">
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Email Address -->
                        <div class="mb-3">
                            <label for="email" class="form-label fw-semibold">
                                <i class="fas fa-envelope me-2 text-success"></i>Email
                            </label>
                            <input id="email" type="email" name="email" value="{{ old('email') }}"
                                class="form-control form-control-lg @error('email') is-invalid @enderror"
                                placeholder="votre@email.com" required autocomplete="username">
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Password -->
                        <div class="mb-3">
                            <label for="password" class="form-label fw-semibold">
                                <i class="fas fa-lock me-2 text-success"></i>Mot de passe
                            </label>
                            <div class="input-group">
                                <input id="password" type="password" name="password"
                                    class="form-control form-control-lg @error('password') is-invalid @enderror"
                                    placeholder="Votre mot de passe" required autocomplete="new-password">
                                <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Confirm Password -->
                        <div class="mb-4">
                            <label for="password_confirmation" class="form-label fw-semibold">
                                <i class="fas fa-lock me-2 text-success"></i>Confirmer le mot de passe
                            </label>
                            <div class="input-group">
                                <input id="password_confirmation" type="password" name="password_confirmation"
                                    class="form-control form-control-lg @error('password_confirmation') is-invalid @enderror"
                                    placeholder="Confirmez votre mot de passe" required autocomplete="new-password">
                                <button class="btn btn-outline-secondary" type="button"
                                    id="togglePasswordConfirmation">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            @error('password_confirmation')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Terms and Conditions -->
                        <div class="mb-4">
                            <div class="form-check">
                                <input id="terms" type="checkbox" class="form-check-input" required>
                                <label for="terms" class="form-check-label">
                                    J'accepte les <a href="#" class="text-decoration-none">conditions
                                        d'utilisation</a> et la <a href="#" class="text-decoration-none">politique
                                        de confidentialité</a>
                                </label>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="d-grid mb-4">
                            <button type="submit" class="btn btn-success btn-lg">
                                <i class="fas fa-user-plus me-2"></i>
                                Créer mon compte
                            </button>
                        </div>

                        <!-- Login Link -->
                        <div class="text-center">
                            <p class="mb-0">
                                Déjà un compte ?
                                <a href="{{ route('login') }}" class="text-decoration-none fw-semibold">
                                    Se connecter
                                </a>
                            </p>
                        </div>
                    </form>
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
