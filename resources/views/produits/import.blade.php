@extends('layouts.app')

@section('title', 'Importer des produits')

@section('content')
    <div class="row g-4">
        <div class="col-12 col-lg-7">
            <div class="card saas-surface-card shadow-sm">
                <div class="card-body">
                    <h5 class="card-title mb-3">
                        <i class="fas fa-file-excel me-2 text-success"></i>Importer un catalogue produits
                    </h5>
                    <p class="text-muted small mb-1">
                        Importez vos produits depuis un fichier Excel. Seules <strong>4 colonnes</strong> sont
                        obligatoires :
                        <em>Nom du produit</em>, <em>Catégorie</em>, <em>Quantité</em> et <em>Prix de vente</em>.
                    </p>
                    <p class="text-muted small mb-4">
                        Boutique cible :
                        <span class="badge text-bg-primary">{{ $boutique->nom ?? '—' }}</span>
                    </p>

                    @if (session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif
                    @if (session('warning'))
                        <div class="alert alert-warning">{{ session('warning') }}</div>
                    @endif
                    @if (session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif

                    <div class="d-flex flex-wrap gap-2 mb-4">
                        <a href="{{ route('produits.import.template') }}" class="btn btn-outline-success">
                            <i class="fas fa-download me-2"></i>Télécharger le modèle Excel
                        </a>
                        <a href="{{ route('produits.import.template.csv') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-file-csv me-2"></i>Modèle CSV
                        </a>
                        <a href="{{ route('produits.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Retour à la liste
                        </a>
                    </div>

                    <form action="{{ route('produits.import.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="mb-3">
                            <label for="fichier" class="form-label">
                                Fichier Excel (.xlsx, .xls, .csv) <span class="text-danger">*</span>
                            </label>
                            <input type="file" class="form-control @error('fichier') is-invalid @enderror" id="fichier"
                                name="fichier" accept=".xlsx,.xls,.csv" required>
                            @error('fichier')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="alert alert-light border small mb-4">
                            <strong>Fichier Excel :</strong> le format <code>.xlsx</code> est lu directement (aucune archive créée par l'application).
                            <br>
                            <strong>Génération automatique :</strong>
                            <ul class="mb-0 mt-2">
                                <li>SKU unique (<code>PRD000001</code>, <code>PRD000002</code>…)</li>
                                <li>Code-barres EAN-13 unique si absent du fichier</li>
                                <li>Création des catégories manquantes</li>
                                <li>Mouvement de stock initial si quantité &gt; 0</li>
                            </ul>
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-upload me-2"></i>Lancer l'importation
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-5">
            @if (!empty($lastReport))
                <div class="card saas-surface-card shadow-sm mb-4">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="fas fa-clipboard-check me-2"></i>Rapport d'importation</h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-2 mb-3">
                            <div class="col-4">
                                <div class="border rounded p-2 text-center">
                                    <div class="fw-bold text-success">{{ $lastReport['imported'] ?? 0 }}</div>
                                    <small class="text-muted">Importés</small>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="border rounded p-2 text-center">
                                    <div class="fw-bold text-danger">{{ $lastReport['skipped'] ?? 0 }}</div>
                                    <small class="text-muted">Ignorés</small>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="border rounded p-2 text-center">
                                    <div class="fw-bold text-primary">{{ $lastReport['categories_created'] ?? 0 }}</div>
                                    <small class="text-muted">Catégories</small>
                                </div>
                            </div>
                        </div>

                        @if (!empty($lastReport['imported_rows']))
                            <h6 class="small text-muted">Produits créés (aperçu)</h6>
                            <div class="table-responsive mb-3">
                                <table class="table table-sm table-striped mb-0">
                                    <thead>
                                        <tr>
                                            <th>Ligne</th>
                                            <th>Produit</th>
                                            <th>SKU</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($lastReport['imported_rows'] as $line => $row)
                                            <tr>
                                                <td>{{ $line }}</td>
                                                <td>{{ $row['nom'] ?? '—' }}</td>
                                                <td><code>{{ $row['code_produit'] ?? '—' }}</code></td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif

                        @if (!empty($lastReport['errors']))
                            <h6 class="small text-danger">Erreurs par ligne</h6>
                            <ul class="list-group list-group-flush small">
                                @foreach ($lastReport['errors'] as $line => $message)
                                    <li class="list-group-item px-0 py-2">
                                        <strong>Ligne {{ $line }} :</strong> {{ $message }}
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                </div>
            @endif

            <div class="card saas-surface-card shadow-sm">
                <div class="card-body">
                    <h6 class="card-title mb-3">
                        <i class="fas fa-info-circle me-2 text-info"></i>Format attendu
                    </h6>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Nom du produit</th>
                                    <th>Catégorie</th>
                                    <th>Quantité</th>
                                    <th>Prix de vente</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Savon noir</td>
                                    <td>Cosmétiques</td>
                                    <td>50</td>
                                    <td>2500</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <p class="text-muted small mt-3 mb-0">
                        Colonnes optionnelles reconnues si présentes :
                        <code>SKU</code>, <code>Code-barres</code>, <code>Prix d'achat</code>,
                        <code>Description</code>, <code>Stock minimum</code>.
                    </p>
                </div>
            </div>
        </div>
    </div>
@endsection
