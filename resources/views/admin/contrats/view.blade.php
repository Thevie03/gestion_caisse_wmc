@extends('layouts.app')

@section('content')
    <div class="container-fluid px-4 py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-1 fw-bold">Visualisation du Contrat</h1>
                <p class="text-muted mb-0">{{ $contrat->numero_contrat }} - {{ $contrat->boutique->nom }}</p>
            </div>
            <div>
                <a href="{{ route('admin.contrats.show', $contrat) }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Retour
                </a>
                <a href="{{ route('admin.contrats.download', $contrat) }}" class="btn btn-primary">
                    <i class="fas fa-download me-2"></i>Télécharger
                </a>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                @php
                    $extension = strtolower(pathinfo($contrat->fichier_contrat, PATHINFO_EXTENSION));
                    $fileUrl = Storage::url($contrat->fichier_contrat);
                @endphp

                @if ($extension === 'pdf')
                    <!-- Affichage PDF avec iframe -->
                    <iframe src="{{ route('admin.contrats.view', $contrat) }}?inline=1"
                        style="width: 100%; height: 80vh; border: none;" title="Contrat PDF">
                        <p>Votre navigateur ne supporte pas l'affichage des PDF.
                            <a href="{{ route('admin.contrats.download', $contrat) }}">Télécharger le fichier</a>
                        </p>
                    </iframe>
                @elseif(in_array($extension, ['doc', 'docx']))
                    <!-- Pour les documents Word, on propose le téléchargement ou l'ouverture avec Google Docs Viewer -->
                    <div class="p-5 text-center">
                        <i class="fas fa-file-word fa-5x text-primary mb-4"></i>
                        <h4 class="mb-3">Document Word</h4>
                        <p class="text-muted mb-4">
                            Les documents Word ne peuvent pas être affichés directement dans le navigateur.
                        </p>
                        <div class="d-flex justify-content-center gap-2">
                            <a href="{{ route('admin.contrats.download', $contrat) }}" class="btn btn-primary">
                                <i class="fas fa-download me-2"></i>Télécharger le document
                            </a>
                            <a href="https://docs.google.com/viewer?url={{ urlencode(url($fileUrl)) }}&embedded=true"
                                target="_blank" class="btn btn-info">
                                <i class="fas fa-external-link-alt me-2"></i>Ouvrir avec Google Docs Viewer
                            </a>
                        </div>
                    </div>
                @else
                    <!-- Format non supporté -->
                    <div class="p-5 text-center">
                        <i class="fas fa-file fa-5x text-muted mb-4"></i>
                        <h4 class="mb-3">Format non supporté</h4>
                        <p class="text-muted mb-4">
                            Ce type de fichier ne peut pas être affiché directement dans le navigateur.
                        </p>
                        <a href="{{ route('admin.contrats.download', $contrat) }}" class="btn btn-primary">
                            <i class="fas fa-download me-2"></i>Télécharger le fichier
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
