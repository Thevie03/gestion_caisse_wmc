<x-guest-layout>
    <div class="login-wrapper">
        <!-- Left Panel - Login Form -->
        <div class="login-left-panel">
            <div class="login-content">
                <!-- Header Section -->
                <div class="login-header">
                    <!-- Logo -->
                    <div class="login-logo">
                        <img src="{{ asset('images/logos/logo_wmc_orange.png') }}" alt="Logo WMC" class="login-logo-img">
                    </div>

                    <!-- Title -->
                    <h2 class="login-title">Veuillez vous connecter</h2>

                    <!-- Compact Description -->
                    <div class="login-description-compact">
                        <p class="login-tagline">Application de vente et de gestion de stock</p>
                    </div>
                </div>

                <!-- Session Status -->
                <x-auth-session-status class="mb-3" :status="session('status')" />

                <!-- Login Form -->
                <form method="POST" action="{{ route('login') }}" class="login-form">
                    @csrf

                    <!-- Email Field -->
                    <div class="form-group">
                        <label for="email" class="form-label">Votre email <span class="text-danger">*</span></label>
                        <input id="email" type="email" name="email" value="{{ old('email') }}"
                            class="form-control @error('email') is-invalid @enderror" placeholder="Email" required
                            autofocus autocomplete="username">
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Password Field -->
                    <div class="form-group">
                        <label for="password" class="form-label">Mot de passe <span class="text-danger">*</span></label>
                        <input id="password" type="password" name="password"
                            class="form-control @error('password') is-invalid @enderror" placeholder="Mot de passe"
                            required autocomplete="current-password">
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Remember Me & Forgot Password -->
                    <div class="login-options">
                        <div class="form-check">
                            <input id="remember_me" type="checkbox" name="remember" class="form-check-input"
                                {{ old('remember') ? 'checked' : '' }}>
                            <label for="remember_me" class="form-check-label">
                                Se souvenir de moi
                            </label>
                        </div>

                        {{-- @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}" class="forgot-link">
                                Forgot password?
                            </a>
                        @endif --}}
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" class="login-btn">
                        Se connecter
                    </button>

                    <!-- Register Link -->
                    {{-- @if (Route::has('register'))
                        <div class="login-footer">
                            <p class="register-text">
                                Don't have an account? <a href="{{ route('register') }}" class="register-link">Sign
                                    Up</a>
                            </p>
                        </div>
                    @endif --}}
                </form>

                <!-- Footer with Description and Copyright -->
                <div class="login-footer-section">
                    <div class="login-description-full">
                        <p class="login-description-text">
                            <strong>WMC</strong> vous permet de vendre, gérer votre stock et suivre votre caisse en
                            toute simplicité.
                            Conçue pour s'adapter à la réalité des commerces en Côte d'Ivoire.
                        </p>
                    </div>
                    <div class="loginopyright">
                        <p>&copy; {{ date('Y') }} WMC. Tous droits réservés.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Panel - Login Image -->
        <div class="login-right-panel">
            <div class="login-image-wrapper">
                <img src="{{ asset('images/photo pages/photologin1.png') }}" alt="Login" class="login-image">
            </div>
        </div>
    </div>
</x-guest-layout>
