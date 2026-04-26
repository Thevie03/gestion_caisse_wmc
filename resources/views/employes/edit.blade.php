@extends('layouts.app')

@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-user-edit me-2"></i>
                        Informations de l'employé
                    </h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('employes.update', $employe) }}" class="needs-validation" novalidate>
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <!-- Nom complet -->
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label">
                                    <i class="fas fa-user me-1 text-primary"></i>
                                    Nom complet <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror"
                                    id="name" name="name" value="{{ old('name', $employe->name) }}"
                                    placeholder="Ex: Jean Dupont" required>
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
                                    id="email" name="email" value="{{ old('email', $employe->email) }}"
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
                                    id="telephone" name="telephone" value="{{ old('telephone', $employe->telephone) }}"
                                    placeholder="+221 33 123 45 67" required>
                                @error('telephone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Boutique -->
                            <div class="col-md-6 mb-3">
                                <label for="boutique_id" class="form-label">
                                    <i class="fas fa-store me-1 text-primary"></i>
                                    Boutique assignée <span class="text-danger" id="boutique_required">*</span>
                                </label>
                                <select class="form-select @error('boutique_id') is-invalid @enderror" id="boutique_id"
                                    name="boutique_id">
                                    <option value="">Aucune (accès aux 2 boutiques)</option>
                                    @foreach ($boutiques as $boutique)
                                        <option value="{{ $boutique->id }}"
                                            {{ old('boutique_id', $employe->boutique_id) == $boutique->id ? 'selected' : '' }}>
                                            {{ $boutique->nom }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('boutique_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">
                                    <small class="text-muted" id="boutique_help">
                                        {{ $employe->role === 'admin'
                                            ? 'Optionnel : laissez vide pour un accès aux 2 boutiques'
                                            : 'Obligatoire pour un employé' }}
                                    </small>
                                </div>
                            </div>
                        </div>

                        <!-- Rôle (uniquement pour super admin, pas pour les propriétaires de boutique) -->
                        @if (auth()->user()->isSuperAdmin())
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="role" class="form-label">
                                        <i class="fas fa-user-tag me-1 text-primary"></i>
                                        Type de compte <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select @error('role') is-invalid @enderror" id="role"
                                        name="role" required>
                                        <option value="employe"
                                            {{ old('role', $employe->role) == 'employe' ? 'selected' : '' }}>
                                            Employé
                                        </option>
                                        <option value="admin"
                                            {{ old('role', $employe->role) == 'admin' ? 'selected' : '' }}>
                                            Administrateur
                                        </option>
                                    </select>
                                    @error('role')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">
                                        <small class="text-muted">
                                            <strong>Employé :</strong> Accès limité selon les permissions assignées<br>
                                            <strong>Administrateur :</strong> Accès complet + visibilité sur Cosmetica &amp;
                                            Maison des Abaya
                                        </small>
                                    </div>
                                </div>
                            </div>
                        @else
                            {{-- Pour les propriétaires de boutique, le rôle est toujours 'employe' --}}
                            <input type="hidden" name="role" value="employe">
                        @endif

                        <!-- Statut -->
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="actif" name="actif"
                                        {{ old('actif', $employe->actif) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="actif">
                                        <i class="fas fa-toggle-on me-1 text-success"></i>
                                        Compte actif
                                    </label>
                                </div>
                                <div class="form-text">
                                    <small class="text-muted">Décochez pour désactiver le compte de l'employé</small>
                                </div>
                            </div>
                        </div>

                        <hr>

                        <!-- Section mot de passe -->
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Mot de passe :</strong> Laissez vide pour conserver le mot de passe actuel.
                        </div>

                        <div class="row">
                            <!-- Nouveau mot de passe -->
                            <div class="col-md-6 mb-3">
                                <label for="password" class="form-label">
                                    <i class="fas fa-lock me-1 text-primary"></i>
                                    Nouveau mot de passe
                                </label>
                                <div class="input-group">
                                    <input type="password" class="form-control @error('password') is-invalid @enderror"
                                        id="password" name="password"
                                        placeholder="Laissez vide pour conserver l'actuel">
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
                                    Confirmer le nouveau mot de passe
                                </label>
                                <div class="input-group">
                                    <input type="password" class="form-control" id="password_confirmation"
                                        name="password_confirmation" placeholder="Confirmez le nouveau mot de passe">
                                    <button class="btn btn-outline-secondary" type="button"
                                        onclick="togglePassword('password_confirmation')">
                                        <i class="fas fa-eye" id="password_confirmation-icon"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Informations sur l'employé -->
                        <div class="alert alert-light border">
                            <div class="row">
                                <div class="col-md-6">
                                    <strong>Créé le :</strong> {{ $employe->created_at->format('d/m/Y à H:i') }}
                                </div>
                                <div class="col-md-6">
                                    <strong>Dernière modification :</strong>
                                    {{ $employe->updated_at->format('d/m/Y à H:i') }}
                                </div>
                            </div>
                        </div>

                        <!-- Gestion des permissions (seulement pour les employés) -->
                        <div class="mb-4" id="permissions_section">
                            <h6 class="mb-3">
                                <i class="fas fa-user-shield me-2 text-primary"></i>
                                Permissions d'accès
                            </h6>
                            <div class="alert alert-warning" id="permissions_note" style="display: none;">
                                <i class="fas fa-info-circle me-2"></i>
                                <strong>Note :</strong> Les administrateurs possèdent toutes les permissions et voient
                                automatiquement les boutiques Cosmetica &amp; Maison des Abaya. Cette section s'applique
                                uniquement aux employés.
                            </div>
                            <div class="row">
                                @foreach ($permissions as $module => $modulePermissions)
                                    <div class="col-md-6 col-lg-4 mb-3">
                                        <div class="card border">
                                            <div class="card-header bg-light">
                                                <h6 class="card-title mb-0">
                                                    @php
                                                        $moduleIcons = [
                                                            'produits' => 'fas fa-box',
                                                            'ventes' => 'fas fa-shopping-cart',
                                                            'stock' => 'fas fa-warehouse',
                                                            'depenses' => 'fas fa-money-bill-wave',
                                                            'rapports' => 'fas fa-chart-bar',
                                                            'employes' => 'fas fa-users',
                                                            'categories' => 'fas fa-tags',
                                                        ];
                                                        $icon = $moduleIcons[$module] ?? 'fas fa-cog';
                                                    @endphp
                                                    <i class="{{ $icon }} me-2"></i>
                                                    {{ ucfirst($module) }}
                                                </h6>
                                            </div>
                                            <div class="card-body">
                                                @foreach ($modulePermissions as $permission)
                                                    <div class="form-check mb-2">
                                                        <input class="form-check-input" type="checkbox"
                                                            name="permissions[]" value="{{ $permission->id }}"
                                                            id="permission_{{ $permission->id }}"
                                                            {{ in_array($permission->id, $userPermissions) ? 'checked' : '' }}>
                                                        <label class="form-check-label"
                                                            for="permission_{{ $permission->id }}">
                                                            <strong>{{ ucfirst($permission->action) }}</strong>
                                                            @if ($permission->description)
                                                                <br><small
                                                                    class="text-muted">{{ $permission->description }}</small>
                                                            @endif
                                                        </label>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- Gestion des permissions de sidebar (seulement pour les employés) -->
                        <div class="mb-4" id="sidebar_permissions_section">
                            <h6 class="mb-3">
                                <i class="fas fa-list me-2 text-primary"></i>
                                Permissions d'accès aux onglets de la sidebar
                            </h6>
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                <strong>Note :</strong> Cochez les onglets que l'employé pourra voir dans la sidebar. Les
                                administrateurs ont automatiquement accès à tous les onglets.
                            </div>
                            <div class="row">
                                @php
                                    $sidebarItems = [
                                        'dashboard' => [
                                            'icon' => 'fas fa-tachometer-alt',
                                            'label' => 'Tableau de bord',
                                        ],
                                        'point_vente' => [
                                            'icon' => 'fas fa-cash-register',
                                            'label' => 'Point de vente',
                                        ],
                                        'historique_ventes' => [
                                            'icon' => 'fas fa-history',
                                            'label' => 'Historique ventes',
                                        ],
                                        'recapitulatif_ventes' => [
                                            'icon' => 'fas fa-chart-line',
                                            'label' => 'Récapitulatif Ventes',
                                        ],
                                        'produits' => ['icon' => 'fas fa-box', 'label' => 'produits/articles'],
                                        'categories' => ['icon' => 'fas fa-tags', 'label' => 'Catégories'],
                                        'stock' => ['icon' => 'fas fa-warehouse', 'label' => 'Stock'],
                                        'clients' => ['icon' => 'fas fa-users', 'label' => 'Clients'],
                                        'fournisseurs' => ['icon' => 'fas fa-truck', 'label' => 'Fournisseurs'],
                                        'depenses' => ['icon' => 'fas fa-receipt', 'label' => 'Dépenses'],
                                        'rapports' => ['icon' => 'fas fa-chart-bar', 'label' => 'Rapports'],
                                        'archivage' => ['icon' => 'fas fa-archive', 'label' => 'Archivage'],
                                        'boutiques' => ['icon' => 'fas fa-store', 'label' => 'Boutiques'],
                                        'employes' => ['icon' => 'fas fa-users-cog', 'label' => 'Employés'],
                                    ];
                                @endphp
                                @foreach ($sidebarPermissions as $permission)
                                    @php
                                        $action = $permission->action;
                                        $item = $sidebarItems[$action] ?? null;
                                    @endphp
                                    @if ($item)
                                        <div class="col-md-6 col-lg-4 mb-3">
                                            <div class="card border">
                                                <div class="card-body">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox"
                                                            name="permissions[]" value="{{ $permission->id }}"
                                                            id="sidebar_permission_{{ $permission->id }}"
                                                            {{ in_array($permission->id, $userPermissions) ? 'checked' : '' }}>
                                                        <label class="form-check-label d-flex align-items-center"
                                                            for="sidebar_permission_{{ $permission->id }}">
                                                            <i class="{{ $item['icon'] }} me-2 text-primary"></i>
                                                            <span>{{ $item['label'] }}</span>
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>

                        <!-- Boutons d'action -->
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('employes.show', $employe) }}" class="btn btn-outline-info">
                                <i class="fas fa-eye me-2"></i>
                                Voir le profil
                            </a>
                            <div>
                                <a href="{{ route('employes.index') }}" class="btn btn-outline-secondary me-2">
                                    <i class="fas fa-times me-2"></i>
                                    Annuler
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>
                                    Enregistrer les modifications
                                </button>
                            </div>
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
                if (password.value !== '' && password.value !== passwordConfirmation.value) {
                    passwordConfirmation.setCustomValidity('Les mots de passe ne correspondent pas');
                } else {
                    passwordConfirmation.setCustomValidity('');
                }
            }

            password.addEventListener('input', validatePassword);
            passwordConfirmation.addEventListener('input', validatePassword);

            // Gérer l'affichage du statut
            const actifCheckbox = document.getElementById('actif');
            const actifLabel = actifCheckbox.nextElementSibling;

            function updateStatusDisplay() {
                if (actifCheckbox.checked) {
                    actifLabel.innerHTML = '<i class="fas fa-toggle-on me-1 text-success"></i>Compte actif';
                } else {
                    actifLabel.innerHTML = '<i class="fas fa-toggle-off me-1 text-danger"></i>Compte inactif';
                }
            }

            actifCheckbox.addEventListener('change', updateStatusDisplay);
            updateStatusDisplay(); // Initialiser l'affichage

            // Gestion du champ rôle (si présent) - Afficher/masquer les permissions et rendre boutique optionnelle
            const roleSelect = document.getElementById('role');
            const boutiqueSelect = document.getElementById('boutique_id');
            const boutiqueRequired = document.getElementById('boutique_required');
            const boutiqueHelp = document.getElementById('boutique_help');
            const permissionsSection = document.getElementById('permissions_section');
            const permissionsNote = document.getElementById('permissions_note');

            if (roleSelect) {
                function updateFormBasedOnRole() {
                    const selectedRole = roleSelect.value;

                    if (selectedRole === 'admin') {
                        // Pour admin : boutique optionnelle, masquer les permissions
                        boutiqueSelect.removeAttribute('required');
                        boutiqueSelect.value = '';
                        if (boutiqueRequired) boutiqueRequired.style.display = 'none';
                        if (boutiqueHelp) {
                            boutiqueHelp.textContent = 'Optionnel : laisser vide pour un accès aux 2 boutiques';
                        }
                        if (permissionsSection) {
                            permissionsSection.querySelector('.row').style.display = 'none';
                            if (permissionsNote) permissionsNote.style.display = 'block';
                        }
                    } else {
                        // Pour employé : boutique obligatoire, afficher les permissions
                        boutiqueSelect.setAttribute('required', 'required');
                        if (boutiqueRequired) boutiqueRequired.style.display = 'inline';
                        if (boutiqueHelp) boutiqueHelp.textContent = 'Obligatoire pour un employé';
                        if (permissionsSection) {
                            permissionsSection.querySelector('.row').style.display = 'block';
                            if (permissionsNote) permissionsNote.style.display = 'none';
                        }
                    }
                }

                roleSelect.addEventListener('change', updateFormBasedOnRole);
                updateFormBasedOnRole(); // Initialiser au chargement
            }

            // Gestion des permissions - Sélectionner/désélectionner tout pour chaque module
            const moduleCards = document.querySelectorAll('.card.border');

            moduleCards.forEach(card => {
                const header = card.querySelector('.card-header');
                const checkboxes = card.querySelectorAll('input[type="checkbox"]');

                // Ajouter un checkbox "Tout sélectionner" dans l'en-tête
                const selectAllCheckbox = document.createElement('div');
                selectAllCheckbox.className = 'form-check';
                selectAllCheckbox.innerHTML = `
                    <input class="form-check-input" type="checkbox" id="select_all_${Math.random().toString(36).substr(2, 9)}">
                    <label class="form-check-label" for="select_all_${Math.random().toString(36).substr(2, 9)}">
                        <small><strong>Tout sélectionner</strong></small>
                    </label>
                `;

                header.appendChild(selectAllCheckbox);

                const selectAllInput = selectAllCheckbox.querySelector('input[type="checkbox"]');

                // Gérer la sélection/désélection de tout
                selectAllInput.addEventListener('change', function() {
                    checkboxes.forEach(checkbox => {
                        checkbox.checked = this.checked;
                    });
                });

                // Mettre à jour l'état du "Tout sélectionner" quand les checkboxes individuelles changent
                checkboxes.forEach(checkbox => {
                    checkbox.addEventListener('change', function() {
                        const checkedCount = Array.from(checkboxes).filter(cb => cb.checked)
                            .length;
                        selectAllInput.checked = checkedCount === checkboxes.length;
                        selectAllInput.indeterminate = checkedCount > 0 && checkedCount <
                            checkboxes.length;
                    });
                });

                // Initialiser l'état
                const checkedCount = Array.from(checkboxes).filter(cb => cb.checked).length;
                selectAllInput.checked = checkedCount === checkboxes.length;
                selectAllInput.indeterminate = checkedCount > 0 && checkedCount < checkboxes.length;
            });
        });
    </script>
@endsection
