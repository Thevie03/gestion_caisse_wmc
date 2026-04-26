@extends('layouts.app')

@section('content')
    <div class="row g-4">
        <div class="col-12 col-lg-7">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h5 class="card-title mb-3">
                        <i class="fas fa-upload me-2 text-primary"></i>Importer une archive
                    </h5>
                    <p class="text-muted small">
                        Sélectionnez un fichier d’archive exporté précédemment pour restaurer la base de données et les
                        fichiers.
                        L’import remplace les données existantes, assurez-vous d’avoir une sauvegarde récente.
                    </p>

                    <form action="{{ route('archives.import.store') }}" method="POST" enctype="multipart/form-data"
                        class="mt-4">
                        @csrf

                        <div class="mb-3">
                            <label for="archive" class="form-label">Fichier d’archive (.zip) <span
                                    class="text-danger">*</span></label>
                            <input type="file" class="form-control @error('archive') is-invalid @enderror" id="archive"
                                name="archive" accept=".zip" required>
                            @error('archive')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">Mot de passe de l’archive (si chiffrée)</label>
                            <input type="password" class="form-control @error('password') is-invalid @enderror"
                                id="password" name="password" maxlength="191" autocomplete="new-password">
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-check mb-3">
                            <input class="form-check-input @error('confirm_overwrite') is-invalid @enderror" type="checkbox"
                                value="1" id="confirm_overwrite" name="confirm_overwrite" required>
                            <label class="form-check-label" for="confirm_overwrite">
                                Je comprends que cette opération écrase les données actuelles.
                            </label>
                            @error('confirm_overwrite')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex gap-2">
                            <a href="{{ route('archives.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-1"></i>Retour
                            </a>
                            <button type="submit" class="btn btn-danger">
                                <i class="fas fa-undo-alt me-2"></i>Lancer l’import
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-5">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <h5 class="card-title mb-3">
                        <i class="fas fa-history me-2 text-secondary"></i>Imports récents
                    </h5>
                    @if ($tasks->isEmpty())
                        <p class="text-muted small mb-0">Aucun import récent enregistré.</p>
                    @else
                        <div class="list-group list-group-flush">
                            @foreach ($tasks as $task)
                                <div class="list-group-item px-0">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <span class="badge text-bg-secondary">Import</span>
                                            <span class="ms-2">{{ $task->created_at->format('d/m/Y H:i') }}</span>
                                        </div>
                                        @php
                                            $statusClasses = [
                                                'pending' => 'secondary',
                                                'running' => 'warning',
                                                'completed' => 'success',
                                                'failed' => 'danger',
                                            ];
                                        @endphp
                                        <span class="badge text-bg-{{ $statusClasses[$task->status] ?? 'secondary' }}">
                                            {{ ucfirst($task->status) }}
                                        </span>
                                    </div>
                                    <div class="progress my-2" style="height: 6px;">
                                        <div class="progress-bar" style="width: {{ $task->progress }}%;" role="progressbar"
                                            aria-valuenow="{{ $task->progress }}" aria-valuemin="0" aria-valuemax="100">
                                        </div>
                                    </div>
                                    @if ($task->message)
                                        <div class="small text-muted">{{ $task->message }}</div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection













