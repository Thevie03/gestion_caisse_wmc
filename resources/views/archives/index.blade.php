@extends('layouts.app')

@section('content')
    <div class="row g-4">
        <div class="col-12 col-xl-4">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <h5 class="card-title mb-3">
                        <i class="fas fa-database me-2 text-primary"></i>Nouvel export d’archive
                    </h5>
                    <p class="text-muted small">
                        Sélectionnez ce que vous souhaitez archiver. L'opération est effectuée immédiatement et le fichier
                        sera téléchargé automatiquement.
                    </p>
                    <form action="{{ route('archives.store') }}" method="POST" class="mt-4" id="archiveForm">
                        @csrf

                        @if (auth()->user()->isSuperAdmin())
                            {{-- Super Admin : Dropdown pour choisir le type d'archivage --}}
                            <div class="mb-3">
                                <label for="archive_type" class="form-label">
                                    <i class="fas fa-list me-1"></i>Type d'archivage <span class="text-danger">*</span>
                                </label>
                                <select class="form-select @error('archive_type') is-invalid @enderror" id="archive_type"
                                    name="archive_type" required>
                                    <option value="">-- Sélectionnez un type --</option>
                                    <option value="boutique_specific">Archiver une boutique spécifique</option>
                                    <option value="all_boutiques">Archiver toutes les boutiques</option>
                                    <option value="system_data">Archiver les données système (WMC)</option>
                                </select>
                                @error('archive_type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Dropdown pour sélectionner une boutique (affiché seulement si "boutique spécifique" est sélectionné) --}}
                            <div class="mb-3" id="boutique_select_container" style="display: none;">
                                <label for="boutique_id" class="form-label">
                                    <i class="fas fa-store me-1"></i>Sélectionner une boutique <span
                                        class="text-danger">*</span>
                                </label>
                                <select class="form-select @error('boutique_id') is-invalid @enderror" id="boutique_id"
                                    name="boutique_id">
                                    <option value="">-- Choisir une boutique --</option>
                                    @if (isset($boutiques) && $boutiques)
                                        @foreach ($boutiques as $boutique)
                                            <option value="{{ $boutique->id }}">{{ $boutique->nom }}</option>
                                        @endforeach
                                    @endif
                                </select>
                                @error('boutique_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Message d'information pour les données système --}}
                            <div class="alert alert-info small" id="system_data_info" style="display: none;">
                                <i class="fas fa-info-circle me-2"></i>
                                <strong>Données système :</strong> Cette archive contiendra les contrats, abonnements,
                                paiements, informations sur les boutiques, paramètres système et utilisateurs.
                            </div>

                            {{-- Message d'information pour toutes les boutiques --}}
                            <div class="alert alert-warning small" id="all_boutiques_info" style="display: none;">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <strong>Attention :</strong> Cette archive contiendra toutes les données opérationnelles de
                                toutes les boutiques (ventes, produits, clients, etc.).
                            </div>
                        @elseif (auth()->user()->isOwner())
                            {{-- Propriétaire : Afficher ses boutiques assignées --}}
                            @php
                                $user = auth()->user();
                                $ownedBoutiques = $user->ownedBoutiques()->where('actif', true)->get();
                                if ($ownedBoutiques->isEmpty() && $user->boutique_id) {
                                    $ownedBoutiques = collect([$user->boutique]);
                                }
                                $boutiqueActive =
                                    session('boutique_active') ??
                                    ($ownedBoutiques->isNotEmpty() ? $ownedBoutiques->first()->id : $user->boutique_id);
                            @endphp

                            @if ($ownedBoutiques->count() > 1)
                                {{-- Plusieurs boutiques : afficher un select --}}
                                <div class="mb-3">
                                    <label for="boutique_id" class="form-label">
                                        <i class="fas fa-store me-1"></i>Sélectionner une boutique <span
                                            class="text-danger">*</span>
                                    </label>
                                    <select class="form-select @error('boutique_id') is-invalid @enderror" id="boutique_id"
                                        name="boutique_id" required>
                                        <option value="">-- Choisir une boutique --</option>
                                        @foreach ($ownedBoutiques as $boutique)
                                            <option value="{{ $boutique->id }}"
                                                {{ $boutique->id == $boutiqueActive ? 'selected' : '' }}>
                                                {{ $boutique->nom }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('boutique_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            @else
                                {{-- Une seule boutique : affichage automatique --}}
                                <div class="alert alert-info small mb-3">
                                    <i class="fas fa-info-circle me-2"></i>
                                    <strong>Votre boutique :</strong>
                                    {{ $ownedBoutiques->first()->nom ?? ($user->boutique->nom ?? 'Non définie') }}
                                </div>
                                <input type="hidden" name="boutique_id" value="{{ $boutiqueActive }}">
                            @endif
                            <input type="hidden" name="archive_type" value="boutique_specific">
                        @endif

                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" role="switch" id="encrypt" name="encrypt"
                                value="1">
                            <label class="form-check-label" for="encrypt">Activer le chiffrement de l'archive
                                (optionnel)</label>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-file-archive me-2"></i>Lancer l'export
                        </button>
                    </form>

                    @if (auth()->user()->isSuperAdmin())
                        <script>
                            document.addEventListener('DOMContentLoaded', function() {
                                const archiveType = document.getElementById('archive_type');
                                const boutiqueSelect = document.getElementById('boutique_select_container');
                                const boutiqueId = document.getElementById('boutique_id');
                                const systemDataInfo = document.getElementById('system_data_info');
                                const allBoutiquesInfo = document.getElementById('all_boutiques_info');
                                const archiveForm = document.getElementById('archiveForm');

                                archiveType.addEventListener('change', function() {
                                    const value = this.value;

                                    // Masquer tous les éléments
                                    boutiqueSelect.style.display = 'none';
                                    systemDataInfo.style.display = 'none';
                                    allBoutiquesInfo.style.display = 'none';

                                    // Réinitialiser le champ boutique
                                    boutiqueId.value = '';
                                    boutiqueId.required = false;

                                    // Afficher les éléments selon le choix
                                    if (value === 'boutique_specific') {
                                        boutiqueSelect.style.display = 'block';
                                        boutiqueId.required = true;
                                    } else if (value === 'system_data') {
                                        systemDataInfo.style.display = 'block';
                                    } else if (value === 'all_boutiques') {
                                        allBoutiquesInfo.style.display = 'block';
                                    }
                                });

                                // Validation du formulaire
                                archiveForm.addEventListener('submit', function(e) {
                                    const selectedType = archiveType.value;

                                    if (!selectedType) {
                                        e.preventDefault();
                                        alert('Veuillez sélectionner un type d\'archivage.');
                                        return false;
                                    }

                                    if (selectedType === 'boutique_specific' && !boutiqueId.value) {
                                        e.preventDefault();
                                        alert('Veuillez sélectionner une boutique.');
                                        return false;
                                    }

                                    // Ajouter les champs cachés selon le type
                                    if (selectedType === 'system_data') {
                                        const input = document.createElement('input');
                                        input.type = 'hidden';
                                        input.name = 'archive_system_data';
                                        input.value = '1';
                                        archiveForm.appendChild(input);
                                    } else if (selectedType === 'all_boutiques') {
                                        const input = document.createElement('input');
                                        input.type = 'hidden';
                                        input.name = 'archive_all_boutiques';
                                        input.value = '1';
                                        archiveForm.appendChild(input);
                                    }
                                });
                            });
                        </script>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-12 col-xl-8">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-hourglass-half me-2 text-warning"></i>Tâches récentes
                        </h5>
                        <a href="{{ route('archives.import.create') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-upload me-1"></i>Importer une archive
                        </a>
                    </div>
                    @if ($tasks->isEmpty())
                        <p class="text-muted small mb-0">Aucune tâche récente d’export ou d’import.</p>
                    @else
                        <div class="table-responsive">
                            <table class="table table-sm align-middle">
                                <thead>
                                    <tr>
                                        <th>Type</th>
                                        <th>Statut</th>
                                        <th>Progression</th>
                                        <th>Utilisateur</th>
                                        <th>Début</th>
                                        <th>Fin</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($tasks as $task)
                                        <tr>
                                            <td>
                                                <span
                                                    class="badge text-bg-{{ $task->type === 'export' ? 'primary' : 'info' }}">
                                                    {{ $task->type === 'export' ? 'Export' : 'Import' }}
                                                </span>
                                            </td>
                                            <td>
                                                @php
                                                    $statusClasses = [
                                                        'pending' => 'secondary',
                                                        'running' => 'warning',
                                                        'completed' => 'success',
                                                        'failed' => 'danger',
                                                    ];
                                                @endphp
                                                <span
                                                    class="badge text-bg-{{ $statusClasses[$task->status] ?? 'secondary' }}">
                                                    {{ ucfirst($task->status) }}
                                                </span>
                                                @if ($task->message)
                                                    <div class="small text-muted">{{ $task->message }}</div>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="progress" style="height: 6px;">
                                                    <div class="progress-bar" style="width: {{ $task->progress }}%;"
                                                        role="progressbar" aria-valuenow="{{ $task->progress }}"
                                                        aria-valuemin="0" aria-valuemax="100">
                                                    </div>
                                                </div>
                                                <small class="text-muted">{{ $task->progress }}%</small>
                                            </td>
                                            <td>{{ $task->user?->name ?? 'N/A' }}</td>
                                            <td>{{ $task->started_at?->format('d/m/Y H:i') ?? '—' }}</td>
                                            <td>{{ $task->finished_at?->format('d/m/Y H:i') ?? '—' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm mt-4">
        <div class="card-body">
            <h5 class="card-title mb-3">
                <i class="fas fa-archive me-2 text-success"></i>Archives disponibles
            </h5>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Nom du fichier</th>
                            <th>Taille</th>
                            <th>Checksum</th>
                            <th>Créé par</th>
                            <th>Date</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($archives as $archive)
                            <tr>
                                <td>{{ $archive->filename }}</td>
                                <td>
                                    @if ($archive->size)
                                        @php
                                            $units = ['octets', 'Ko', 'Mo', 'Go', 'To'];
                                            $size = $archive->size;
                                            $unitIndex = 0;
                                            while ($size >= 1024 && $unitIndex < count($units) - 1) {
                                                $size /= 1024;
                                                $unitIndex++;
                                            }
                                        @endphp
                                        {{ number_format($size, $unitIndex === 0 ? 0 : 2, ',', ' ') }}
                                        {{ $units[$unitIndex] }}
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="text-truncate" style="max-width: 220px;">
                                    <code class="small">{{ $archive->checksum ?? '—' }}</code>
                                </td>
                                <td>{{ $archive->user?->name ?? '—' }}</td>
                                <td>{{ $archive->created_at->format('d/m/Y H:i') }}</td>
                                <td class="text-end">
                                    <div class="btn-group btn-group-sm" role="group">
                                        <a href="{{ route('archives.download', $archive) }}"
                                            class="btn btn-outline-primary">
                                            <i class="fas fa-download"></i>
                                        </a>
                                        <form action="{{ route('archives.destroy', $archive) }}" method="POST"
                                            onsubmit="return confirm('Supprimer définitivement cette archive ?');">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-outline-danger" type="submit">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">
                                    Aucune archive générée pour le moment.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div>
                {{ $archives->links() }}
            </div>
        </div>
    </div>
@endsection
