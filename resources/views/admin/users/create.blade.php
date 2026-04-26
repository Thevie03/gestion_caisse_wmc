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
                        <li class="breadcrumb-item active">Nouveau client</li>
                    </ol>
                </nav>
                <h1 class="h3 mb-1 fw-bold">Créer un nouveau client</h1>
                <p class="text-muted mb-0">Créez un compte utilisateur avec sa boutique et son abonnement</p>
            </div>
        </div>

        <form method="POST" action="{{ route('admin.users.store') }}" id="createUserForm">
            @csrf

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
                                    name="name" value="{{ old('name') }}" placeholder="Ex: Jean Dupont" required>
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
                                    name="email" value="{{ old('email') }}" placeholder="exemple@email.com" required>
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
                                    id="telephone" name="telephone" value="{{ old('telephone') }}"
                                    placeholder="+221 XX XXX XX XX" required>
                                @error('telephone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label fw-semibold">
                                    <i class="fas fa-lock me-2 text-primary"></i>Mot de passe <span
                                        class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <input type="password"
                                        class="form-control form-control-lg @error('password') is-invalid @enderror"
                                        id="password" name="password" placeholder="Minimum 8 caractères" required>
                                    <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Le mot de passe sera remis au client</small>
                            </div>

                            <div class="mb-0">
                                <label for="password_confirmation" class="form-label fw-semibold">
                                    <i class="fas fa-lock me-2 text-primary"></i>Confirmer le mot de passe <span
                                        class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <input type="password" class="form-control form-control-lg" id="password_confirmation"
                                        name="password_confirmation" placeholder="Répétez le mot de passe" required>
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
                                    id="boutique_nom" name="boutique_nom" value="{{ old('boutique_nom') }}"
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
                                    id="boutique_adresse" name="boutique_adresse" value="{{ old('boutique_adresse') }}"
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
                                    <option value="FCFA" @selected(old('devise', 'FCFA') === 'FCFA')>FCFA (Franc CFA)</option>
                                    <option value="EUR" @selected(old('devise') === 'EUR')>EUR (Euro)</option>
                                    <option value="USD" @selected(old('devise') === 'USD')>USD (Dollar US)</option>
                                    <option value="XOF" @selected(old('devise') === 'XOF')>XOF (Franc Ouest-Africain)</option>
                                </select>
                                @error('devise')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section Abonnement -->
                <div class="col-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white border-bottom py-3">
                            <div class="d-flex align-items-center">
                                <div class="bg-warning bg-opacity-10 rounded-circle p-2 me-3">
                                    <i class="fas fa-credit-card text-warning"></i>
                                </div>
                                <div>
                                    <h5 class="mb-0 fw-semibold">Abonnement</h5>
                                    <small class="text-muted">Configuration de l'abonnement initial</small>
                                </div>
                            </div>
                        </div>
                        <div class="card-body p-4">
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <label for="type_abonnement" class="form-label fw-semibold">
                                        <i class="fas fa-tag me-2 text-warning"></i>Type d'abonnement <span
                                            class="text-danger">*</span>
                                    </label>
                                    <select
                                        class="form-select form-select-lg @error('type_abonnement') is-invalid @enderror"
                                        id="type_abonnement" name="type_abonnement" required>
                                        <option value="mensuel" @selected(old('type_abonnement') === 'mensuel')>📅 Mensuel</option>
                                        <option value="trimestriel" @selected(old('type_abonnement') === 'trimestriel')>📆 Trimestriel (3 mois)
                                        </option>
                                        <option value="semestriel" @selected(old('type_abonnement') === 'semestriel')>📊 Semestriel (6 mois)
                                        </option>
                                        <option value="annuel" @selected(old('type_abonnement') === 'annuel')>📆 Annuel</option>
                                        <option value="acquisition_definitive" @selected(old('type_abonnement') === 'acquisition_definitive')>🏆 Acquisition
                                            Définitive</option>
                                    </select>
                                    @error('type_abonnement')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-3">
                                    <label for="montant" class="form-label fw-semibold">
                                        <i class="fas fa-money-bill-wave me-2 text-warning"></i>Montant
                                    </label>
                                    <div class="input-group">
                                        <input type="number" step="0.01"
                                            class="form-control form-control-lg @error('montant') is-invalid @enderror"
                                            id="montant" name="montant" value="{{ old('montant', 0) }}"
                                            placeholder="0.00" min="0">
                                        <span class="input-group-text">FCFA</span>
                                    </div>
                                    @error('montant')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-3">
                                    <label for="date_debut" class="form-label fw-semibold">
                                        <i class="fas fa-calendar-alt me-2 text-warning"></i>Date de début <span
                                            class="text-danger">*</span>
                                    </label>
                                    <input type="date"
                                        class="form-control form-control-lg @error('date_debut') is-invalid @enderror"
                                        id="date_debut" name="date_debut"
                                        value="{{ old('date_debut', now()->format('Y-m-d')) }}" required>
                                    @error('date_debut')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-3">
                                    <label for="date_expiration" class="form-label fw-semibold">
                                        <i class="fas fa-calendar-times me-2 text-warning"></i>Date d'expiration
                                    </label>
                                    <input type="date"
                                        class="form-control form-control-lg @error('date_expiration') is-invalid @enderror"
                                        id="date_expiration" name="date_expiration"
                                        value="{{ old('date_expiration') }}">
                                    @error('date_expiration')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">Laissé vide pour calcul automatique</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="d-flex justify-content-between align-items-center mt-4">
                <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary btn-lg">
                    <i class="fas fa-times me-2"></i>Annuler
                </a>
                <button type="submit" class="btn btn-primary btn-lg px-5">
                    <i class="fas fa-user-plus me-2"></i>Créer le client
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

        // Auto-calculate expiration date based on subscription type
        document.getElementById('type_abonnement')?.addEventListener('change', function() {
            const type = this.value;
            const dateDebut = document.getElementById('date_debut').value;
            const dateExpiration = document.getElementById('date_expiration');

            if (dateDebut && !dateExpiration.value) {
                const startDate = new Date(dateDebut);
                let endDate = new Date(startDate);

                if (type === 'mensuel') {
                    endDate.setMonth(endDate.getMonth() + 1);
                } else if (type === 'trimestriel') {
                    endDate.setMonth(endDate.getMonth() + 3);
                } else if (type === 'semestriel') {
                    endDate.setMonth(endDate.getMonth() + 6);
                } else if (type === 'annuel') {
                    endDate.setFullYear(endDate.getFullYear() + 1);
                } else {
                    // Par défaut: mensuel
                    endDate.setMonth(endDate.getMonth() + 1);
                }

                dateExpiration.value = endDate.toISOString().split('T')[0];
            }
        });

        // Update expiration date when start date changes
        document.getElementById('date_debut')?.addEventListener('change', function() {
            const type = document.getElementById('type_abonnement').value;
            const dateExpiration = document.getElementById('date_expiration');

            if (this.value && !dateExpiration.value) {
                const startDate = new Date(this.value);
                let endDate = new Date(startDate);

                if (type === 'mensuel') {
                    endDate.setMonth(endDate.getMonth() + 1);
                } else if (type === 'trimestriel') {
                    endDate.setMonth(endDate.getMonth() + 3);
                } else if (type === 'semestriel') {
                    endDate.setMonth(endDate.getMonth() + 6);
                } else if (type === 'annuel') {
                    endDate.setFullYear(endDate.getFullYear() + 1);
                } else {
                    // Par défaut: mensuel
                    endDate.setMonth(endDate.getMonth() + 1);
                }

                dateExpiration.value = endDate.toISOString().split('T')[0];
            }
        });
    </script>
@endsection
