@extends('layouts.app')

@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-user-plus me-2"></i>
                        Informations de l'administrateur
                    </h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admins.store') }}" class="needs-validation" novalidate>
                        @csrf

                        <div class="row">
                            <!-- Nom complet -->
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label">
                                    <i class="fas fa-user me-1 text-primary"></i>
                                    Nom complet <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror"
                                    id="name" name="name" value="{{ old('name') }}" placeholder="Ex: Jean Dupont"
                                    required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Email -->
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">
                                    <i class="fas fa-envelope me-1 text-primary"></i>
                                    Email <span class="text-danger">*</span>
                                </label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror"
                                    id="email" name="email" value="{{ old('email') }}"
                                    placeholder="jean.dupont@example.com" required>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <!-- Téléphone -->
                            <div class="col-md-6 mb-3">
                                <label for="telephone" class="form-label">
                                    <i class="fas fa-phone me-1 text-primary"></i>
                                    Téléphone <span class="text-danger">*</span>
                                </label>
                                <input type="tel" class="form-control @error('telephone') is-invalid @enderror"
                                    id="telephone" name="telephone" value="{{ old('telephone') }}"
                                    placeholder="+221 33 123 45 67" required>
                                @error('telephone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Boutique -->
                            <div class="col-md-6 mb-3">
                                <label for="boutique_id" class="form-label">
                                    <i class="fas fa-store me-1 text-primary"></i>
                                    Boutique assignée (optionnel)
                                </label>
                                <select class="form-select @error('boutique_id') is-invalid @enderror" id="boutique_id"
                                    name="boutique_id">
                                    <option value="">Aucune (accès à toutes les boutiques)</option>
                                    @foreach ($boutiques as $boutique)
                                        <option value="{{ $boutique->id }}"
                                            {{ old('boutique_id') == $boutique->id ? 'selected' : '' }}>
                                            {{ $boutique->nom }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('boutique_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">
                                    <small class="text-muted">Si aucune boutique n'est sélectionnée, l'admin aura accès à
                                        toutes les boutiques</small>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <!-- Mot de passe -->
                            <div class="col-md-6 mb-3">
                                <label for="password" class="form-label">
                                    <i class="fas fa-lock me-1 text-primary"></i>
                                    Mot de passe <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <input type="password" class="form-control @error('password') is-invalid @enderror"
                                        id="password" name="password" required>
                                    <button class="btn btn-outline-secondary" type="button"
                                        onclick="togglePassword('password')">
                                        <i class="fas fa-eye" id="password-icon"></i>
                                    </button>
                                </div>
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">
                                    <small class="text-muted">Minimum 8 caractères</small>
                                </div>
                            </div>

                            <!-- Confirmation mot de passe -->
                            <div class="col-md-6 mb-3">
                                <label for="password_confirmation" class="form-label">
                                    <i class="fas fa-lock me-1 text-primary"></i>
                                    Confirmer le mot de passe <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <input type="password" class="form-control" id="password_confirmation"
                                        name="password_confirmation" required>
                                    <button class="btn btn-outline-secondary" type="button"
                                        onclick="togglePassword('password_confirmation')">
                                        <i class="fas fa-eye" id="password_confirmation-icon"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Statut -->
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="actif" name="actif" checked>
                                    <label class="form-check-label" for="actif">
                                        <i class="fas fa-toggle-on me-1 text-success"></i>
                                        Compte actif
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Informations supplémentaires -->
                        <div class="alert alert-success">
                            <i class="fas fa-shield-alt me-2"></i>
                            <strong>Permissions complètes :</strong> L'administrateur créé aura automatiquement
                            <strong>toutes les permissions</strong> du système. Il pourra :
                            <ul class="mb-0 mt-2">
                                <li>Gérer les produits, ventes, stock et dépenses</li>
                                <li>Accéder à tous les rapports et statistiques</li>
                                <li>Gérer les employés et leurs permissions</li>
                                <li>Gérer les boutiques (si aucune boutique spécifique n'est assignée)</li>
                                <li>Accéder à toutes les fonctionnalités administratives</li>
                            </ul>
                        </div>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Note :</strong> L'administrateur pourra se connecter avec son email et son mot de passe.
                            Contrairement aux employés, les administrateurs n'ont pas besoin de permissions spécifiques -
                            ils ont accès à tout par défaut.
                        </div>

                        <!-- Boutons d'action -->
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('admins.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-2"></i>
                                Annuler
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>
                                Créer l'administrateur
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Script pour améliorer l'expérience utilisateur -->
    <script>
        function togglePassword(fieldId) {
            const field = document.getElementById(fieldId);
            const icon = document.getElementById(fieldId + '-icon');

            if (field.type === 'password') {
                field.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                field.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            // Auto-focus sur le champ nom
            document.getElementById('name').focus();

            // Validation en temps réel du mot de passe
            const password = document.getElementById('password');
            const passwordConfirmation = document.getElementById('password_confirmation');

            function validatePassword() {
                if (password.value !== passwordConfirmation.value) {
                    passwordConfirmation.setCustomValidity('Les mots de passe ne correspondent pas');
                } else {
                    passwordConfirmation.setCustomValidity('');
                }
            }

            password.addEventListener('input', validatePassword);
            passwordConfirmation.addEventListener('input', validatePassword);
        });
    </script>
@endsection
