@extends('layouts.app')

@section('content')
    <div class="container-fluid px-4 py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-1 fw-bold">Gestion des Boutiques</h1>
                <p class="text-muted mb-0">Supervision et gestion de toutes les boutiques</p>
            </div>
            <div>
                <a href="{{ route('admin.boutiques.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>Nouvelle boutique
                </a>
            </div>
        </div>

        <!-- Statistiques -->
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <p class="text-muted small mb-1">Total Boutiques</p>
                        <h3 class="mb-0 fw-bold">{{ number_format($stats['total']) }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <p class="text-muted small mb-1">Actives</p>
                        <h3 class="mb-0 fw-bold text-success">{{ number_format($stats['actives']) }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <p class="text-muted small mb-1">Inactives</p>
                        <h3 class="mb-0 fw-bold text-danger">{{ number_format($stats['inactives']) }}</h3>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filtres et recherche -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <form method="GET" action="{{ route('admin.boutiques.index') }}" class="row g-3">
                    <div class="col-md-6">
                        <input type="text" name="search" value="{{ request('search') }}" class="form-control"
                            placeholder="Rechercher par nom, adresse, email...">
                    </div>
                    <div class="col-md-3">
                        <select name="actif" class="form-select">
                            <option value="">Tous les statuts</option>
                            <option value="1" @selected(request('actif') === '1')>Actives</option>
                            <option value="0" @selected(request('actif') === '0')>Inactives</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-search me-2"></i>Rechercher
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Tableau des boutiques -->
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Nom boutique</th>
                                <th>Propriétaire</th>
                                <th>Adresse</th>
                                <th class="text-end">Chiffre d'affaires</th>
                                <th>Date création</th>
                                <th>Statut</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($boutiques as $boutique)
                                <tr>
                                    <td>
                                        <strong>{{ $boutique->nom }}</strong>
                                        <br>
                                        <small class="text-muted">{{ $boutique->devise ?? 'FCFA' }}</small>
                                    </td>
                                    <td>
                                        {{ $boutique->owner?->name ?? 'N/A' }}
                                        <br>
                                        <small class="text-muted">{{ $boutique->owner?->email ?? '' }}</small>
                                    </td>
                                    <td>
                                        <small>{{ \Illuminate\Support\Str::limit($boutique->adresse ?? 'N/A', 30) }}</small>
                                    </td>
                                    <td class="text-end">
                                        <strong class="text-success">
                                            {{ number_format($boutique->chiffre_affaires ?? 0, 0, ',', ' ') }} FCFA
                                        </strong>
                                    </td>
                                    <td>
                                        <small>{{ $boutique->created_at->format('d/m/Y') }}</small>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $boutique->actif ? 'success' : 'danger' }}">
                                            {{ $boutique->actif ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('admin.boutiques.show', $boutique) }}"
                                                class="btn btn-sm btn-outline-primary" data-bs-toggle="tooltip"
                                                title="Voir détails">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('admin.boutiques.edit', $boutique) }}"
                                                class="btn btn-sm btn-outline-warning" data-bs-toggle="tooltip"
                                                title="Modifier">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button type="button" class="btn btn-sm btn-outline-danger"
                                                data-bs-toggle="tooltip" title="Supprimer"
                                                onclick="confirmDeleteBoutique({{ $boutique->id }}, '{{ $boutique->nom }}')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                            <div class="btn-group" role="group">
                                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle"
                                                    type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                    <i class="fas fa-ellipsis-v"></i>
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-end">
                                                    <li>
                                                        <a class="dropdown-item"
                                                            href="{{ route('admin.boutiques.show', $boutique) }}">
                                                            <i class="fas fa-eye me-2 text-primary"></i>Voir détails
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item"
                                                            href="{{ route('admin.boutiques.supervision', $boutique->id) }}">
                                                            <i class="fas fa-store me-2 text-success"></i>Superviser
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item"
                                                            href="{{ route('admin.boutiques.edit', $boutique) }}">
                                                            <i class="fas fa-edit me-2 text-warning"></i>Modifier
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <hr class="dropdown-divider">
                                                    </li>
                                                    <li>
                                                        <form
                                                            action="{{ route('admin.boutiques.toggle-actif', $boutique) }}"
                                                            method="POST" class="d-inline">
                                                            @csrf
                                                            <button type="submit"
                                                                class="dropdown-item text-{{ $boutique->actif ? 'warning' : 'success' }}">
                                                                <i
                                                                    class="fas fa-{{ $boutique->actif ? 'ban' : 'check' }} me-2"></i>
                                                                {{ $boutique->actif ? 'Désactiver' : 'Activer' }}
                                                            </button>
                                                        </form>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">Aucune boutique trouvée</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    {{ $boutiques->withQueryString()->links() }}
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de confirmation de suppression -->
    <div class="modal fade" id="deleteBoutiqueModal" tabindex="-1" aria-labelledby="deleteBoutiqueModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="deleteBoutiqueModalLabel">
                        <i class="fas fa-exclamation-triangle me-2"></i>Confirmer la suppression
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Attention !</strong> Cette action est irréversible.
                    </div>
                    <p class="mb-2">Vous êtes sur le point de supprimer définitivement la boutique :</p>
                    <ul class="list-unstyled mb-3">
                        <li><strong>Nom :</strong> <span id="delete-boutique-nom"></span></li>
                    </ul>

                    <div id="boutique-data-warning" class="alert alert-warning" style="display: none;">
                        <h6 class="fw-bold">Cette boutique contient des données :</h6>
                        <ul id="boutique-data-list" class="mb-0"></ul>
                    </div>

                    <div id="force-delete-section" style="display: none;">
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="forceDeleteCheckbox" name="force"
                                value="1">
                            <label class="form-check-label" for="forceDeleteCheckbox">
                                <strong>Je comprends et je souhaite supprimer la boutique avec toutes ses données</strong>
                            </label>
                        </div>
                    </div>

                    <p class="text-danger mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        Cette action supprimera également tous les paramètres associés.
                        <span id="force-delete-text" style="display: none;">
                            <strong>La suppression forcée supprimera également toutes les ventes, produits, dépenses et
                                autres données associées.</strong>
                        </span>
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-2"></i>Annuler
                    </button>
                    <form id="delete-boutique-form" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <input type="hidden" name="force" id="forceDeleteInput" value="0">
                        <button type="submit" class="btn btn-danger" id="confirmDeleteBtn">
                            <i class="fas fa-trash me-2"></i>Supprimer définitivement
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function confirmDeleteBoutique(boutiqueId, boutiqueNom) {
            const form = document.getElementById('delete-boutique-form');
            form.action = `/admin/boutiques/${boutiqueId}`;
            document.getElementById('delete-boutique-nom').textContent = boutiqueNom;

            // Réinitialiser le formulaire
            document.getElementById('forceDeleteCheckbox').checked = false;
            document.getElementById('forceDeleteInput').value = '0';
            document.getElementById('force-delete-section').style.display = 'none';
            document.getElementById('boutique-data-warning').style.display = 'none';
            document.getElementById('force-delete-text').style.display = 'none';
            document.getElementById('confirmDeleteBtn').disabled = false;

            // Vérifier si la boutique a des données (optionnel : faire un appel AJAX)
            // Pour l'instant, on affiche toujours l'option de suppression forcée
            document.getElementById('force-delete-section').style.display = 'block';
            document.getElementById('force-delete-text').style.display = 'inline';

            const modal = new bootstrap.Modal(document.getElementById('deleteBoutiqueModal'));
            modal.show();
        }

        document.addEventListener('DOMContentLoaded', function() {
            const forceDeleteCheckbox = document.getElementById('forceDeleteCheckbox');
            const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
            const forceDeleteInput = document.getElementById('forceDeleteInput');

            if (forceDeleteCheckbox) {
                forceDeleteCheckbox.addEventListener('change', function() {
                    if (this.checked) {
                        confirmDeleteBtn.disabled = false;
                        forceDeleteInput.value = '1';
                    } else {
                        confirmDeleteBtn.disabled = true;
                        forceDeleteInput.value = '0';
                    }
                });
            }

            // Confirmation finale
            const deleteForm = document.getElementById('delete-boutique-form');
            if (deleteForm) {
                deleteForm.addEventListener('submit', function(e) {
                    const forceChecked = forceDeleteCheckbox && forceDeleteCheckbox.checked;
                    const message = forceChecked ?
                        'Êtes-vous ABSOLUMENT SÛR de vouloir supprimer cette boutique et TOUTES ses données ? Cette action est irréversible !' :
                        'Êtes-vous sûr de vouloir supprimer cette boutique ?';

                    if (!confirm(message)) {
                        e.preventDefault();
                        return false;
                    }
                });
            }
        });
    </script>
@endsection
