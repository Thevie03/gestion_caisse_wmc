@extends('layouts.app')

@section('content')
    <div class="container-fluid px-4 py-4">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <nav aria-label="breadcrumb" class="mb-2">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('admin.users.index') }}"
                                class="text-decoration-none">Commerçants</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.users.show', $user) }}"
                                class="text-decoration-none">{{ $user->name }}</a></li>
                        <li class="breadcrumb-item active">Modifier</li>
                    </ol>
                </nav>
                <h1 class="h3 mb-1 fw-bold">Modifier {{ $user->name }}</h1>
                <p class="text-muted mb-0">Mettre à jour les informations du client et de sa boutique</p>
            </div>
        </div>

        <form method="POST" action="{{ route('admin.users.update', $user) }}" id="editUserForm">
            @csrf
            @method('PUT')

            <div class="row g-4">
                <!-- Section Informations personnelles -->
                <div class="col-lg-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-white border-bottom py-3">
                            <div class="d-flex align-items-center">
                                <div class="bg-primary bg-opacity-10 rounded-circle p-2 me-3">
                                    <i class="fas fa-user text-primary"></i>
                                </div>
                                <div>
                                    <h5 class="mb-0 fw-semibold">Informations personnelles</h5>
                                    <small class="text-muted">Données du compte utilisateur</small>
                                </div>
                            </div>
                        </div>
                        <div class="card-body p-4">
                            <div class="mb-3">
                                <label for="name" class="form-label fw-semibold">
                                    <i class="fas fa-user me-2 text-primary"></i>Nom complet <span
                                        class="text-danger">*</span>
                                </label>
                                <input type="text"
                                    class="form-control form-control-lg @error('name') is-invalid @enderror" id="name"
                                    name="name" value="{{ old('name', $user->name) }}" placeholder="Ex: Jean Dupont"
                                    required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="email" class="form-label fw-semibold">
                                    <i class="fas fa-envelope me-2 text-primary"></i>Email <span
                                        class="text-danger">*</span>
                                </label>
                                <input type="email"
                                    class="form-control form-control-lg @error('email') is-invalid @enderror" id="email"
                                    name="email" value="{{ old('email', $user->email) }}" placeholder="exemple@email.com"
                                    required>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="telephone" class="form-label fw-semibold">
                                    <i class="fas fa-phone me-2 text-primary"></i>Téléphone <span
                                        class="text-danger">*</span>
                                </label>
                                <input type="text"
                                    class="form-control form-control-lg @error('telephone') is-invalid @enderror"
                                    id="telephone" name="telephone" value="{{ old('telephone', $user->telephone) }}"
                                    placeholder="+221 XX XXX XX XX" required>
                                @error('telephone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="actif" class="form-label fw-semibold">
                                    <i class="fas fa-toggle-on me-2 text-primary"></i>Statut du compte
                                </label>
                                <select class="form-select form-select-lg @error('actif') is-invalid @enderror"
                                    id="actif" name="actif">
                                    <option value="1" @selected(old('actif', $user->actif) == 1)>
                                        <i class="fas fa-check-circle me-2"></i>Actif
                                    </option>
                                    <option value="0" @selected(old('actif', $user->actif) == 0)>
                                        <i class="fas fa-ban me-2"></i>Suspendu
                                    </option>
                                </select>
                                @error('actif')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label fw-semibold">
                                    <i class="fas fa-lock me-2 text-primary"></i>Nouveau mot de passe
                                </label>
                                <div class="input-group">
                                    <input type="password"
                                        class="form-control form-control-lg @error('password') is-invalid @enderror"
                                        id="password" name="password"
                                        placeholder="Laisser vide pour conserver le mot de passe actuel">
                                    <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Laissez vide pour conserver le mot de passe actuel</small>
                            </div>

                            <div class="mb-0">
                                <label for="password_confirmation" class="form-label fw-semibold">
                                    <i class="fas fa-lock me-2 text-primary"></i>Confirmer le nouveau mot de passe
                                </label>
                                <div class="input-group">
                                    <input type="password" class="form-control form-control-lg" id="password_confirmation"
                                        name="password_confirmation" placeholder="Répétez le nouveau mot de passe">
                                    <button class="btn btn-outline-secondary" type="button"
                                        id="togglePasswordConfirmation">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section Boutique -->
                <div class="col-lg-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-white border-bottom py-3">
                            <div class="d-flex align-items-center">
                                <div class="bg-success bg-opacity-10 rounded-circle p-2 me-3">
                                    <i class="fas fa-store text-success"></i>
                                </div>
                                <div>
                                    <h5 class="mb-0 fw-semibold">Informations boutique</h5>
                                    <small class="text-muted">Détails de la boutique du client</small>
                                </div>
                            </div>
                        </div>
                        <div class="card-body p-4">
                            <div class="mb-3">
                                <label for="boutique_nom" class="form-label fw-semibold">
                                    <i class="fas fa-store me-2 text-success"></i>Nom de la boutique <span
                                        class="text-danger">*</span>
                                </label>
                                <input type="text"
                                    class="form-control form-control-lg @error('boutique_nom') is-invalid @enderror"
                                    id="boutique_nom" name="boutique_nom"
                                    value="{{ old('boutique_nom', $user->boutique?->nom) }}"
                                    placeholder="Ex: Ma Boutique" required>
                                @error('boutique_nom')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="boutique_adresse" class="form-label fw-semibold">
                                    <i class="fas fa-map-marker-alt me-2 text-success"></i>Adresse
                                </label>
                                <input type="text"
                                    class="form-control form-control-lg @error('boutique_adresse') is-invalid @enderror"
                                    id="boutique_adresse" name="boutique_adresse"
                                    value="{{ old('boutique_adresse', $user->boutique?->adresse) }}"
                                    placeholder="Adresse complète de la boutique">
                                @error('boutique_adresse')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-0">
                                <label for="devise" class="form-label fw-semibold">
                                    <i class="fas fa-coins me-2 text-success"></i>Devise
                                </label>
                                <select class="form-select form-select-lg @error('devise') is-invalid @enderror"
                                    id="devise" name="devise">
                                    <option value="FCFA" @selected(old('devise', $user->boutique?->devise ?? 'FCFA') === 'FCFA')>FCFA (Franc CFA)</option>
                                    <option value="EUR" @selected(old('devise', $user->boutique?->devise ?? 'FCFA') === 'EUR')>EUR (Euro)</option>
                                    <option value="USD" @selected(old('devise', $user->boutique?->devise ?? 'FCFA') === 'USD')>USD (Dollar US)</option>
                                    <option value="XOF" @selected(old('devise', $user->boutique?->devise ?? 'FCFA') === 'XOF')>XOF (Franc Ouest-Africain)</option>
                                </select>
                                @error('devise')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="d-flex justify-content-between align-items-center mt-4">
                <a href="{{ route('admin.users.show', $user) }}" class="btn btn-outline-secondary btn-lg">
                    <i class="fas fa-times me-2"></i>Annuler
                </a>
                <button type="submit" class="btn btn-primary btn-lg px-5">
                    <i class="fas fa-save me-2"></i>Enregistrer les modifications
                </button>
            </div>
        </form>
    </div>

    <style>
        .form-control:focus,
        .form-select:focus {
            border-color: var(--bs-primary);
            box-shadow: 0 0 0 0.2rem rgba(var(--bs-primary-rgb), 0.25);
        }

        .form-control-lg,
        .form-select-lg {
            transition: all 0.3s ease;
        }

        .form-control-lg:hover,
        .form-select-lg:hover {
            border-color: var(--bs-primary);
        }

        .card {
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .card:hover {
            transform: translateY(-2px);
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
        }

        .btn-lg {
            padding: 0.75rem 1.5rem;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 0.5rem 1rem rgba(var(--bs-primary-rgb), 0.4);
        }

        .breadcrumb-item a {
            color: var(--bs-primary);
        }

        .breadcrumb-item a:hover {
            text-decoration: underline;
        }
    </style>

    <script>
        // Toggle password visibility
        document.getElementById('togglePassword')?.addEventListener('click', function() {
            const passwordInput = document.getElementById('password');
            const icon = this.querySelector('i');

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });

        document.getElementById('togglePasswordConfirmation')?.addEventListener('click', function() {
            const passwordInput = document.getElementById('password_confirmation');
            const icon = this.querySelector('i');

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
    </script>
@endsection
