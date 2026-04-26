@extends('layouts.app')

@section('content')
    <div class="container-fluid px-4 py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <nav aria-label="breadcrumb" class="mb-2">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.boutiques.index') }}"
                                class="text-decoration-none">Boutiques</a></li>
                        <li class="breadcrumb-item active">Créer une boutique</li>
                    </ol>
                </nav>
                <h1 class="h3 mb-1 fw-bold">Créer une boutique et son administrateur</h1>
                <p class="text-muted mb-0">Chaque boutique dispose automatiquement de son administrateur principal.</p>
            </div>
            <div>
                <a href="{{ route('admin.boutiques.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Retour
                </a>
            </div>
        </div>

        <form method="POST" action="{{ route('admin.boutiques.store') }}" enctype="multipart/form-data" class="row g-4">
            @csrf

            <!-- Informations Boutique -->
            <div class="col-lg-7">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0 text-white">
                            <i class="fas fa-store me-2"></i>Informations de la boutique
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-12">
                                <label class="form-label fw-semibold">Nom de la boutique <span
                                        class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('nom') is-invalid @enderror" name="nom"
                                    value="{{ old('nom') }}" required placeholder="Ex: Maison des saveurs">
                                @error('nom')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-12">
                                <label class="form-label fw-semibold">Adresse</label>
                                <textarea class="form-control @error('adresse') is-invalid @enderror" name="adresse" rows="2"
                                    placeholder="Adresse complète">{{ old('adresse') }}</textarea>
                                @error('adresse')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Téléphone <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('telephone') is-invalid @enderror"
                                    name="telephone" value="{{ old('telephone') }}" required placeholder="+2250700000000">
                                @error('telephone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Email de la boutique</label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror"
                                    name="email" value="{{ old('email') }}" placeholder="boutique@example.com">
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Devise <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('devise') is-invalid @enderror"
                                    name="devise" value="{{ old('devise', 'FCFA') }}" required>
                                @error('devise')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Logo</label>
                                <input type="file" class="form-control @error('logo') is-invalid @enderror"
                                    name="logo" accept="image/*">
                                @error('logo')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Formats acceptés : JPG, PNG, GIF (max 2MB)</small>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label fw-semibold">Image commune des onglets</label>
                                <input type="file" class="form-control @error('shared_hero_image') is-invalid @enderror"
                                    name="shared_hero_image" accept="image/*">
                                @error('shared_hero_image')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Cette image sera identique sur tableau de bord, point de vente, produits/articles et stock (max 4MB).</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Administrateur + abonnement -->
            <div class="col-lg-5">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0 text-white">
                            <i class="fas fa-user-tie me-2"></i>Administrateur de la boutique
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info small mb-3">
                            <i class="fas fa-info-circle me-2"></i>
                            Vous pouvez créer un nouvel administrateur ou assigner la boutique à un propriétaire existant.
                        </div>

                        <!-- Sélection du type de propriétaire -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Type de propriétaire <span
                                    class="text-danger">*</span></label>
                            <div class="btn-group w-100" role="group">
                                <input type="radio" class="btn-check" name="owner_type" id="owner_type_new" value="new"
                                    {{ old('owner_type', 'new') === 'new' ? 'checked' : '' }}
                                    onchange="toggleOwnerFields()">
                                <label class="btn btn-outline-primary" for="owner_type_new">
                                    <i class="fas fa-user-plus me-1"></i>Nouveau
                                </label>

                                <input type="radio" class="btn-check" name="owner_type" id="owner_type_existing"
                                    value="existing" {{ old('owner_type') === 'existing' ? 'checked' : '' }}
                                    onchange="toggleOwnerFields()">
                                <label class="btn btn-outline-success" for="owner_type_existing">
                                    <i class="fas fa-user-check me-1"></i>Existant
                                </label>
                            </div>
                        </div>

                        <!-- Sélection du propriétaire existant -->
                        <div id="existing_owner_section"
                            style="display: {{ old('owner_type') === 'existing' ? 'block' : 'none' }};">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Sélectionner un propriétaire <span
                                        class="text-danger">*</span></label>
                                <select class="form-select @error('owner_id') is-invalid @enderror" name="owner_id"
                                    id="owner_id">
                                    <option value="">-- Sélectionner un propriétaire --</option>
                                    @foreach (\App\Models\User::where('role', 'admin')->where('actif', true)->withCount('ownedBoutiques')->get() as $owner)
                                        <option value="{{ $owner->id }}"
                                            {{ old('owner_id') == $owner->id ? 'selected' : '' }}>
                                            {{ $owner->name }} ({{ $owner->email }})
                                            @if ($owner->owned_boutiques_count > 0)
                                                - {{ $owner->owned_boutiques_count }} boutique(s)
                                            @endif
                                        </option>
                                    @endforeach
                                </select>
                                @error('owner_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Les propriétaires existants peuvent gérer plusieurs
                                    boutiques.</small>
                            </div>
                        </div>

                        <!-- Formulaire pour nouveau propriétaire -->
                        <div id="new_owner_section"
                            style="display: {{ old('owner_type', 'new') === 'new' ? 'block' : 'none' }};">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Nom complet <span
                                        class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('owner_name') is-invalid @enderror"
                                    name="owner_name" id="owner_name" value="{{ old('owner_name') }}"
                                    placeholder="Nom & prénom">
                                @error('owner_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Email <span class="text-danger">*</span></label>
                                <input type="email" class="form-control @error('owner_email') is-invalid @enderror"
                                    name="owner_email" id="owner_email" value="{{ old('owner_email') }}"
                                    placeholder="admin@boutique.com">
                                @error('owner_email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Téléphone <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('owner_telephone') is-invalid @enderror"
                                    name="owner_telephone" id="owner_telephone" value="{{ old('owner_telephone') }}"
                                    placeholder="+2250700000000">
                                @error('owner_telephone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-0">
                                <label class="form-label fw-semibold">Mot de passe</label>
                                <div class="input-group">
                                    <input type="text"
                                        class="form-control @error('owner_password') is-invalid @enderror"
                                        name="owner_password" id="owner_password" value="{{ old('owner_password') }}"
                                        placeholder="Laisser vide pour générer automatiquement">
                                    <button type="button" class="btn btn-outline-primary" onclick="generatePassword()">
                                        <i class="fas fa-magic me-1"></i>Générer
                                    </button>
                                    @error('owner_password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <small class="text-muted">Si vide, un mot de passe sécurisé sera généré.</small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm" id="subscription_section">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="mb-0">
                            <i class="fas fa-credit-card me-2"></i>Abonnement
                        </h5>
                    </div>
                    <div class="card-body">
                        <div id="subscription_info_existing" style="display: none;">
                            <div class="alert alert-info mb-0">
                                <i class="fas fa-info-circle me-2"></i>
                                Le propriétaire existant conserve son abonnement actuel. Aucun nouvel abonnement ne sera
                                créé.
                            </div>
                        </div>
                        <div id="subscription_form_new">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Type d'abonnement <span
                                        class="text-danger">*</span></label>
                                <select class="form-select @error('type_abonnement') is-invalid @enderror"
                                    name="type_abonnement" id="type_abonnement">
                                    <option value="">Sélectionner</option>
                                    <option value="mensuel" @selected(old('type_abonnement') === 'mensuel')>Mensuel</option>
                                    <option value="trimestriel" @selected(old('type_abonnement') === 'trimestriel')>Trimestriel</option>
                                    <option value="semestriel" @selected(old('type_abonnement') === 'semestriel')>Semestriel</option>
                                    <option value="annuel" @selected(old('type_abonnement') === 'annuel')>Annuel</option>
                                    <option value="acquisition_definitive" @selected(old('type_abonnement') === 'acquisition_definitive')>Acquisition
                                        Définitive
                                    </option>
                                </select>
                                @error('type_abonnement')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-0">
                                <label class="form-label fw-semibold">Montant abonnement <span
                                        class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="number" step="0.01"
                                        class="form-control @error('montant_abonnement') is-invalid @enderror"
                                        name="montant_abonnement" id="montant_abonnement"
                                        value="{{ old('montant_abonnement', 0) }}">
                                    <span class="input-group-text">FCFA</span>
                                    @error('montant_abonnement')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <small class="text-muted">Ce montant sera utilisé pour le suivi de l'abonnement.</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <a href="{{ route('admin.boutiques.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-times me-2"></i>Annuler
                        </a>
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-save me-2"></i>Créer la boutique
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <script>
        function generatePassword() {
            const charset = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789!@#$%';
            let password = '';
            for (let i = 0; i < 12; i++) {
                password += charset.charAt(Math.floor(Math.random() * charset.length));
            }
            document.getElementById('owner_password').value = password;
        }

        function toggleOwnerFields() {
            const ownerType = document.querySelector('input[name="owner_type"]:checked').value;
            const newOwnerSection = document.getElementById('new_owner_section');
            const existingOwnerSection = document.getElementById('existing_owner_section');
            const subscriptionFormNew = document.getElementById('subscription_form_new');
            const subscriptionInfoExisting = document.getElementById('subscription_info_existing');

            if (ownerType === 'new') {
                newOwnerSection.style.display = 'block';
                existingOwnerSection.style.display = 'none';
                subscriptionFormNew.style.display = 'block';
                subscriptionInfoExisting.style.display = 'none';
                // Rendre les champs requis
                document.getElementById('owner_name').required = true;
                document.getElementById('owner_email').required = true;
                document.getElementById('owner_telephone').required = true;
                document.getElementById('type_abonnement').required = true;
                document.getElementById('montant_abonnement').required = true;
                // Désactiver le champ owner_id
                document.getElementById('owner_id').required = false;
                document.getElementById('owner_id').value = '';
            } else {
                newOwnerSection.style.display = 'none';
                existingOwnerSection.style.display = 'block';
                subscriptionFormNew.style.display = 'none';
                subscriptionInfoExisting.style.display = 'block';
                // Désactiver les champs requis
                document.getElementById('owner_name').required = false;
                document.getElementById('owner_email').required = false;
                document.getElementById('owner_telephone').required = false;
                document.getElementById('type_abonnement').required = false;
                document.getElementById('montant_abonnement').required = false;
                // Activer le champ owner_id
                document.getElementById('owner_id').required = true;
            }
        }

        // Initialiser au chargement de la page
        document.addEventListener('DOMContentLoaded', function() {
            toggleOwnerFields();
        });
    </script>
@endsection
