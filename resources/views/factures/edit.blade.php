@extends('layouts.app')

@section('title', 'Modifier la Facture')

@section('content')
    <div class="container-fluid">
        <!-- En-tête -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h1 class="h3 mb-0 text-gray-800">
                            <i class="fas fa-edit me-2"></i>
                            Modifier la Facture {{ $facture->numero_facture }}
                        </h1>
                        <p class="text-muted mb-0">Modifiez les informations de la facture</p>
                    </div>
                    <div>
                        <a href="{{ route('factures.show', $facture) }}" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-1"></i>
                            Retour
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Formulaire de modification -->
            <div class="col-lg-8">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-edit me-2"></i>
                            Informations de la Facture
                        </h6>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('factures.update', $facture) }}">
                            @csrf
                            @method('PUT')

                            <!-- Informations non modifiables -->
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Numéro de facture</label>
                                        <p class="form-control-plaintext">
                                            <i class="fas fa-hashtag me-2"></i>
                                            {{ $facture->numero_facture }}
                                        </p>
                                        <small class="text-muted">Non modifiable</small>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Date de vente</label>
                                        <p class="form-control-plaintext">
                                            <i class="fas fa-calendar me-2"></i>
                                            {{ $facture->created_at->format('d/m/Y à H:i') }}
                                        </p>
                                        <small class="text-muted">Non modifiable</small>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Boutique</label>
                                        <p class="form-control-plaintext">
                                            <i class="fas fa-store me-2"></i>
                                            {{ $facture->vente->boutique->nom }}
                                        </p>
                                        <small class="text-muted">Non modifiable</small>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Vendeur</label>
                                        <p class="form-control-plaintext">
                                            <i class="fas fa-user me-2"></i>
                                            {{ $facture->vente->user->name }}
                                        </p>
                                        <small class="text-muted">Non modifiable</small>
                                    </div>
                                </div>
                            </div>

                            <hr>

                            <!-- Champs modifiables -->
                            <div class="row">
                                <!-- Client -->
                                <div class="col-md-6 mb-3">
                                    <label for="client_id" class="form-label">
                                        <i class="fas fa-user me-1"></i>
                                        Client
                                    </label>
                                    <select class="form-select @error('client_id') is-invalid @enderror" id="client_id"
                                        name="client_id">
                                        <option value="">Aucun client (Anonyme)</option>
                                        @foreach ($clients as $client)
                                            <option value="{{ $client->id }}"
                                                {{ old('client_id', $facture->vente->client_id) == $client->id ? 'selected' : '' }}>
                                                {{ $client->nom_complet }}
                                                @if ($client->telephone)
                                                    - {{ $client->telephone }}
                                                @endif
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('client_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Remise -->
                                <div class="col-md-6 mb-3">
                                    <label for="remise" class="form-label">
                                        <i class="fas fa-percent me-1"></i>
                                        Remise (FCFA)
                                    </label>
                                    <input type="number" step="0.01" min="0"
                                        class="form-control @error('remise') is-invalid @enderror" id="remise"
                                        name="remise" value="{{ old('remise', $facture->vente->remise ?? 0) }}"
                                        placeholder="0.00">
                                    @error('remise')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">Sous-total actuel :
                                        {{ number_format($facture->vente->details->sum('sous_total'), 0, ',', ' ') }}
                                        FCFA</small>
                                </div>
                            </div>

                            <div class="row">
                                <!-- Mode de paiement -->
                                <div class="col-md-6 mb-3">
                                    <label for="mode_paiement" class="form-label">
                                        <i class="fas fa-money-bill-wave me-1"></i>
                                        Mode de paiement <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select @error('mode_paiement') is-invalid @enderror"
                                        id="mode_paiement" name="mode_paiement" required>
                                        <option value="especes"
                                            {{ old('mode_paiement', $facture->vente->mode_paiement) == 'especes' ? 'selected' : '' }}>
                                            Espèces</option>
                                        <option value="wave"
                                            {{ old('mode_paiement', $facture->vente->mode_paiement) == 'wave' ? 'selected' : '' }}>
                                            Wave</option>
                                        <option value="orange_money"
                                            {{ old('mode_paiement', $facture->vente->mode_paiement) == 'orange_money' ? 'selected' : '' }}>
                                            Orange Money</option>
                                        <option value="mtn_money"
                                            {{ old('mode_paiement', $facture->vente->mode_paiement) == 'mtn_money' ? 'selected' : '' }}>
                                            MTN Money</option>
                                        <option value="carte"
                                            {{ old('mode_paiement', $facture->vente->mode_paiement) == 'carte' ? 'selected' : '' }}>
                                            Carte bancaire</option>
                                    </select>
                                    @error('mode_paiement')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Statut de paiement -->
                                <div class="col-md-6 mb-3">
                                    <label for="statut_paiement" class="form-label">
                                        <i class="fas fa-check-circle me-1"></i>
                                        Statut de paiement <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select @error('statut_paiement') is-invalid @enderror"
                                        id="statut_paiement" name="statut_paiement" required>
                                        <option value="complet"
                                            {{ old('statut_paiement', $facture->vente->statut_paiement) == 'complet' ? 'selected' : '' }}>
                                            Soldé</option>
                                        <option value="partiel"
                                            {{ old('statut_paiement', $facture->vente->statut_paiement) == 'partiel' ? 'selected' : '' }}>
                                            Partiel</option>
                                        <option value="impaye"
                                            {{ old('statut_paiement', $facture->vente->statut_paiement) == 'impaye' ? 'selected' : '' }}>
                                            Impayé</option>
                                    </select>
                                    @error('statut_paiement')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Notes -->
                            <div class="mb-3">
                                <label for="notes" class="form-label">
                                    <i class="fas fa-sticky-note me-1"></i>
                                    Notes
                                </label>
                                <textarea class="form-control @error('notes') is-invalid @enderror" id="notes" name="notes" rows="3"
                                    placeholder="Notes sur la facture...">{{ old('notes', $facture->vente->notes) }}</textarea>
                                @error('notes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Boutons d'action -->
                            <div class="d-flex justify-content-between mt-4">
                                <a href="{{ route('factures.show', $facture) }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-times me-1"></i>
                                    Annuler
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-1"></i>
                                    Enregistrer les modifications
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Informations complémentaires -->
            <div class="col-lg-4">
                <!-- Résumé de la facture -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-info-circle me-2"></i>
                            Résumé de la Facture
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Sous-total</label>
                            <p class="form-control-plaintext">
                                {{ number_format($facture->vente->details->sum('sous_total'), 0, ',', ' ') }} FCFA
                            </p>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Remise actuelle</label>
                            <p class="form-control-plaintext text-warning">
                                -{{ number_format($facture->vente->remise ?? 0, 0, ',', ' ') }} FCFA
                            </p>
                        </div>

                        <hr>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Total actuel</label>
                            <p class="form-control-plaintext">
                                <strong class="text-success fs-5">
                                    {{ number_format($facture->vente->total_final, 0, ',', ' ') }} FCFA
                                </strong>
                            </p>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Montant payé</label>
                            <p class="form-control-plaintext">
                                {{ number_format($facture->vente->montant_paye ?? 0, 0, ',', ' ') }} FCFA
                            </p>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Solde restant</label>
                            <p class="form-control-plaintext">
                                <strong class="text-danger">
                                    {{ number_format($facture->vente->solde_restant ?? $facture->vente->total_final, 0, ',', ' ') }}
                                    FCFA
                                </strong>
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Produits vendus -->
                <div class="card shadow">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-shopping-cart me-2"></i>
                            Produits Vendus ({{ $facture->vente->details->count() }})
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Produit</th>
                                        <th>Qté</th>
                                        <th>Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($facture->vente->details as $detail)
                                        <tr>
                                            <td>
                                                <small>{{ $detail->produit->nom }}</small>
                                            </td>
                                            <td>
                                                <span class="badge bg-primary">{{ $detail->quantite }}</span>
                                            </td>
                                            <td>
                                                <small>{{ number_format($detail->sous_total, 0, ',', ' ') }} FCFA</small>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <small class="text-muted">Les produits ne peuvent pas être modifiés pour des raisons de
                            traçabilité.</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Calculer automatiquement le nouveau total quand la remise change
            const remiseInput = document.getElementById('remise');
            const sousTotal = {{ $facture->vente->details->sum('sous_total') }};

            remiseInput.addEventListener('input', function() {
                const remise = parseFloat(this.value) || 0;
                const nouveauTotal = sousTotal - remise;

                // Vous pourriez afficher un aperçu du nouveau total ici si nécessaire
                console.log('Nouveau total prévu:', nouveauTotal);
            });
        });
    </script>
@endsection

