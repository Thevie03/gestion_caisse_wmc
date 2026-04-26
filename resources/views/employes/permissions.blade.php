@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <!-- En-tête -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h2 class="mb-0">
                            <i class="fas fa-user-shield me-2 text-primary"></i>
                            Permissions de {{ $employe->name }}
                        </h2>
                        <p class="text-muted mb-0">Gérez les autorisations d'accès de cet employé</p>
                    </div>
                    <a href="{{ route('employes.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-2"></i>
                        Retour
                    </a>
                </div>
            </div>
        </div>

        <!-- Informations de l'employé -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-user me-2"></i>
                            Informations de l'employé
                        </h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-borderless">
                            <tr>
                                <td class="fw-bold">Nom :</td>
                                <td>{{ $employe->name }}</td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Email :</td>
                                <td>{{ $employe->email }}</td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Téléphone :</td>
                                <td>{{ $employe->telephone }}</td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Boutique :</td>
                                <td>
                                    @if ($employe->role === 'admin')
                                        <span class="badge bg-info text-dark">Cosmetica &amp; Maison des Abaya</span>
                                    @elseif ($employe->boutique)
                                        <span class="badge bg-primary">{{ $employe->boutique->nom }}</span>
                                    @else
                                        <span class="badge bg-secondary">Non assignée</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Statut :</td>
                                <td>
                                    @if ($employe->actif)
                                        <span class="badge bg-success">Actif</span>
                                    @else
                                        <span class="badge bg-secondary">Inactif</span>
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-chart-pie me-2"></i>
                            Statistiques des permissions
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-6">
                                <h3 class="text-primary">{{ $employe->permissions->count() }}</h3>
                                <small class="text-muted">Permissions accordées</small>
                            </div>
                            <div class="col-6">
                                <h3 class="text-info">
                                    {{ $permissions->flatten()->count() - $employe->permissions->count() }}</h3>
                                <small class="text-muted">Permissions disponibles</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Formulaire de gestion des permissions -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-cogs me-2"></i>
                    Gestion des permissions
                </h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('employes.update-permissions', $employe) }}">
                    @csrf
                    @method('PUT')

                    <div class="row">
                        @foreach ($permissions as $module => $modulePermissions)
                            <div class="col-md-6 col-lg-4 mb-4">
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
                                                <input class="form-check-input" type="checkbox" name="permissions[]"
                                                    value="{{ $permission->id }}" id="permission_{{ $permission->id }}"
                                                    {{ in_array($permission->id, $userPermissions) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="permission_{{ $permission->id }}">
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

                    <!-- Actions -->
                    <div class="d-flex justify-content-between mt-4">
                        <a href="{{ route('employes.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times me-2"></i>
                            Annuler
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>
                            Sauvegarder les permissions
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Sélectionner/désélectionner tout pour chaque module
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
