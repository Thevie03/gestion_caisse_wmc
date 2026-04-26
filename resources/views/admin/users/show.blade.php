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
                        <li class="breadcrumb-item active">{{ $user->name }}</li>
                    </ol>
                </nav>
                <h1 class="h3 mb-1 fw-bold">{{ $user->name }}</h1>
                <p class="text-muted mb-0">{{ $user->email }}</p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-primary">
                    <i class="fas fa-edit me-2"></i>Modifier
                </a>
                <form action="{{ route('admin.users.suspend', $user) }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn {{ $user->actif ? 'btn-warning' : 'btn-success' }}">
                        <i class="fas fa-{{ $user->actif ? 'ban' : 'check' }} me-2"></i>
                        {{ $user->actif ? 'Suspendre' : 'Réactiver' }}
                    </button>
                </form>
            </div>
        </div>

        <!-- Carte des identifiants de connexion -->
        <div class="card border-0 shadow-lg mb-4">
            <div class="card-header bg-primary text-white py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0 fw-bold">
                            <i class="fas fa-key me-2"></i>Identifiants de connexion
                        </h5>
                        <small class="opacity-75">Informations de connexion pour le client</small>
                    </div>
                    <button onclick="printCredentials()" class="btn btn-light btn-sm">
                        <i class="fas fa-print me-2"></i>Imprimer
                    </button>
                </div>
            </div>
            <div class="card-body p-4">
                <div class="row g-4">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-semibold text-dark mb-2">
                                <i class="fas fa-link me-2 text-primary"></i>URL de connexion
                            </label>
                            <div class="input-group">
                                <input type="text" class="form-control form-control-lg bg-light"
                                    value="{{ url('/login') }}" readonly id="login-url">
                                <button class="btn btn-outline-secondary" type="button"
                                    onclick="copyToClipboard('login-url')" title="Copier">
                                    <i class="fas fa-copy"></i>
                                </button>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold text-dark mb-2">
                                <i class="fas fa-envelope me-2 text-primary"></i>Email
                            </label>
                            <div class="input-group">
                                <input type="text" class="form-control form-control-lg bg-light fw-bold"
                                    value="{{ $user->email }}" readonly id="user-email">
                                <button class="btn btn-outline-secondary" type="button"
                                    onclick="copyToClipboard('user-email')" title="Copier">
                                    <i class="fas fa-copy"></i>
                                </button>
                            </div>
                        </div>

                        <div class="mb-0">
                            <label class="form-label fw-semibold text-dark mb-2">
                                <i class="fas fa-lock me-2 text-primary"></i>Mot de passe
                            </label>
                            <div class="input-group">
                                <input type="text" class="form-control form-control-lg bg-light font-monospace"
                                    value="••••••••" readonly id="password-display">
                                <button type="button" onclick="resetPassword()" class="btn btn-outline-primary">
                                    <i class="fas fa-sync-alt me-2"></i>Réinitialiser
                                </button>
                            </div>
                            <small class="text-muted">Le mot de passe ne peut pas être affiché pour des raisons de
                                sécurité</small>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="bg-light rounded p-4 h-100 d-flex flex-column justify-content-center">
                            <div class="text-center mb-3">
                                <div class="bg-primary bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3"
                                    style="width: 80px; height: 80px;">
                                    <i class="fas fa-store fa-2x text-primary"></i>
                                </div>
                                <h6 class="fw-bold text-dark mb-1">Boutique</h6>
                                <p class="h5 mb-0 text-primary fw-bold">{{ $user->boutique?->nom ?? 'Non définie' }}</p>
                            </div>
                            @if ($user->boutique?->adresse)
                                <div class="text-center">
                                    <small class="text-muted">
                                        <i class="fas fa-map-marker-alt me-1"></i>
                                        {{ $user->boutique->adresse }}
                                    </small>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistiques -->
        <div class="row g-4 mb-4">
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="bg-success bg-opacity-10 rounded-circle p-3 me-3">
                                <i class="fas fa-chart-line fa-lg text-success"></i>
                            </div>
                            <div>
                                <p class="text-muted small mb-1">Chiffre d'affaires</p>
                                <h4 class="mb-0 fw-bold text-dark">{{ number_format($stats['ventes_total'], 0, ',', ' ') }}
                                    FCFA</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="bg-danger bg-opacity-10 rounded-circle p-3 me-3">
                                <i class="fas fa-arrow-down fa-lg text-danger"></i>
                            </div>
                            <div>
                                <p class="text-muted small mb-1">Dépenses</p>
                                <h4 class="mb-0 fw-bold text-dark">
                                    {{ number_format($stats['depenses_total'], 0, ',', ' ') }} FCFA</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="bg-info bg-opacity-10 rounded-circle p-3 me-3">
                                <i class="fas fa-box fa-lg text-info"></i>
                            </div>
                            <div>
                                <p class="text-muted small mb-1">produits/articles actifs</p>
                                <h4 class="mb-0 fw-bold text-dark">{{ $stats['produits_actifs'] }} produits</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <!-- Informations boutique -->
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white border-bottom py-3">
                        <h5 class="mb-0 fw-semibold">
                            <i class="fas fa-store me-2 text-success"></i>Informations boutique
                        </h5>
                    </div>
                    <div class="card-body">
                        <dl class="row mb-0">
                            <dt class="col-sm-4 fw-semibold text-dark">Nom :</dt>
                            <dd class="col-sm-8 text-dark">{{ $user->boutique?->nom ?? 'Non défini' }}</dd>

                            <dt class="col-sm-4 fw-semibold text-dark">Adresse :</dt>
                            <dd class="col-sm-8 text-dark">{{ $user->boutique?->adresse ?? 'Non renseignée' }}</dd>

                            <dt class="col-sm-4 fw-semibold text-dark">Téléphone :</dt>
                            <dd class="col-sm-8 text-dark">{{ $user->boutique?->telephone ?? 'Non renseigné' }}</dd>

                            <dt class="col-sm-4 fw-semibold text-dark">Email :</dt>
                            <dd class="col-sm-8 text-dark">{{ $user->boutique?->email ?? 'Non renseigné' }}</dd>

                            <dt class="col-sm-4 fw-semibold text-dark">Devise :</dt>
                            <dd class="col-sm-8">
                                <span class="badge bg-secondary">{{ $user->boutique?->devise ?? 'FCFA' }}</span>
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>

            <!-- Abonnement -->
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white border-bottom py-3">
                        <h5 class="mb-0 fw-semibold">
                            <i class="fas fa-credit-card me-2 text-warning"></i>Abonnement
                        </h5>
                    </div>
                    <div class="card-body">
                        @php $aboActif = $user->abonnementActif; @endphp
                        @if ($aboActif)
                            <div class="mb-3">
                                <p class="fw-bold text-dark h5 mb-1">
                                    @if ($aboActif->type_abonnement === 'acquisition_definitive')
                                        🏆 {{ $aboActif->type_label }}
                                    @else
                                        {{ $aboActif->type_label }}
                                    @endif
                                </p>
                                @if ($aboActif->type_abonnement !== 'acquisition_definitive')
                                    <p class="text-muted mb-2">Expire le {{ $aboActif->date_expiration->format('d/m/Y') }}
                                    </p>
                                @else
                                    <p class="text-muted mb-2"><i class="fas fa-infinity me-1"></i>Accès permanent</p>
                                @endif
                                <span
                                    class="badge {{ $aboActif->statut === 'actif' ? 'bg-success' : ($aboActif->statut === 'suspendu' ? 'bg-warning' : 'bg-danger') }} text-white">
                                    {{ strtoupper($aboActif->statut) }}
                                </span>
                            </div>

                            <!-- Formulaire d'ajout de paiement -->
                            <hr>
                            <h6 class="fw-semibold text-dark mb-3">
                                <i class="fas fa-money-bill-wave me-2 text-success"></i>Enregistrer un paiement
                            </h6>
                            <form method="POST" action="{{ route('admin.abonnements.paiements.store', $aboActif) }}"
                                class="mb-3">
                                @csrf
                                <div class="row g-2">
                                    <div class="col-md-6">
                                        <label class="form-label small fw-semibold text-dark">Montant <span
                                                class="text-danger">*</span></label>
                                        <div class="input-group input-group-sm">
                                            <input type="number" step="0.01" name="montant" class="form-control"
                                                placeholder="0.00" required value="{{ $aboActif->montant ?? '' }}">
                                            <span class="input-group-text">FCFA</span>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-semibold text-dark">Mode de paiement <span
                                                class="text-danger">*</span></label>
                                        <select name="mode_paiement" class="form-select form-select-sm" required>
                                            <option value="wave">Wave</option>
                                            <option value="orange_money">Orange Money</option>
                                            <option value="mtn_money">MTN Money</option>
                                            <option value="especes">Espèces</option>
                                            <option value="carte_bancaire">Carte bancaire</option>
                                            <option value="virement">Virement</option>
                                            <option value="cheque">Chèque</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-semibold text-dark">Date de paiement <span
                                                class="text-danger">*</span></label>
                                        <input type="date" name="date_paiement" class="form-control form-control-sm"
                                            value="{{ now()->format('Y-m-d') }}" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-semibold text-dark">Référence</label>
                                        <input type="text" name="reference" class="form-control form-control-sm"
                                            placeholder="N° de transaction">
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label small fw-semibold text-dark">Notes</label>
                                        <textarea name="notes" class="form-control form-control-sm" rows="2"
                                            placeholder="Notes supplémentaires (optionnel)"></textarea>
                                    </div>
                                    <div class="col-12">
                                        <button type="submit" class="btn btn-success btn-sm w-100">
                                            <i class="fas fa-save me-2"></i>Enregistrer le paiement
                                        </button>
                                    </div>
                                </div>
                            </form>

                            <!-- Historique des paiements de cet abonnement -->
                            @php
                                $paiementsAbonnement = $aboActif->paiements()->latest('date_paiement')->get();
                            @endphp
                            @if ($paiementsAbonnement->count() > 0)
                                <hr>
                                <h6 class="fw-semibold text-dark mb-2">
                                    <i class="fas fa-list me-2 text-info"></i>Historique des paiements
                                </h6>
                                <div class="table-responsive">
                                    <table class="table table-sm table-hover mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th class="small">Date</th>
                                                <th class="small">Montant</th>
                                                <th class="small">Mode</th>
                                                <th class="small">Statut</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($paiementsAbonnement as $paiement)
                                                <tr>
                                                    <td class="small">{{ $paiement->date_paiement->format('d/m/Y') }}
                                                    </td>
                                                    <td class="small fw-bold">
                                                        {{ number_format($paiement->montant, 0, ',', ' ') }} FCFA</td>
                                                    <td class="small">
                                                        {{ ucfirst(str_replace('_', ' ', $paiement->mode_paiement)) }}</td>
                                                    <td>
                                                        <span
                                                            class="badge
                                                            {{ $paiement->statut === 'confirme'
                                                                ? 'bg-success'
                                                                : ($paiement->statut === 'refuse'
                                                                    ? 'bg-danger'
                                                                    : 'bg-warning') }}
                                                            text-white small">
                                                            {{ ucfirst(str_replace('_', ' ', $paiement->statut)) }}
                                                        </span>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        @else
                            <div class="alert alert-warning mb-3">
                                <i class="fas fa-exclamation-triangle me-2"></i>Aucun abonnement actif.
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Historique des abonnements -->
        <div class="card border-0 shadow-sm mt-4">
            <div class="card-header bg-white border-bottom py-3">
                <h5 class="mb-0 fw-semibold">
                    <i class="fas fa-history me-2 text-info"></i>Historique des abonnements
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="fw-semibold text-dark">Type</th>
                                <th class="fw-semibold text-dark">Début</th>
                                <th class="fw-semibold text-dark">Expiration</th>
                                <th class="fw-semibold text-dark">Statut</th>
                                <th class="fw-semibold text-dark text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($user->abonnements as $abonnement)
                                <tr>
                                    <td class="text-dark">{{ $abonnement->type_label }}</td>
                                    <td class="text-dark">{{ $abonnement->date_debut->format('d/m/Y') }}</td>
                                    <td class="text-dark">{{ $abonnement->date_expiration->format('d/m/Y') }}</td>
                                    <td>
                                        <span
                                            class="badge {{ $abonnement->statut === 'actif' ? 'bg-success' : ($abonnement->statut === 'suspendu' ? 'bg-warning' : 'bg-danger') }} text-white">
                                            {{ strtoupper($abonnement->statut) }}
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <form method="POST"
                                            action="{{ route('admin.abonnements.update', $abonnement) }}"
                                            class="d-inline-flex align-items-center gap-2">
                                            @csrf
                                            @method('PATCH')
                                            <select name="statut" class="form-select form-select-sm"
                                                style="width: auto;">
                                                <option value="actif" @selected($abonnement->statut === 'actif')>Actif</option>
                                                <option value="suspendu" @selected($abonnement->statut === 'suspendu')>Suspendu</option>
                                                <option value="expire" @selected($abonnement->statut === 'expire')>Expiré</option>
                                            </select>
                                            <button type="submit" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-save"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">
                                        <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                                        Pas encore d'historique.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal pour réinitialiser le mot de passe -->
    <div class="modal fade" id="password-modal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title fw-bold">
                        <i class="fas fa-key me-2"></i>Réinitialiser le mot de passe
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="reset-password-form" method="POST" action="{{ route('admin.users.reset-password', $user) }}">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-semibold text-dark">Nouveau mot de passe</label>
                            <div class="input-group">
                                <input type="password" name="password" id="new-password" class="form-control" required
                                    minlength="8">
                                <button type="button" onclick="generatePassword()" class="btn btn-outline-secondary"
                                    title="Générer un mot de passe">
                                    <i class="fas fa-dice"></i>
                                </button>
                            </div>
                            <small class="text-muted">Minimum 8 caractères</small>
                        </div>
                        <div class="mb-0">
                            <label class="form-label fw-semibold text-dark">Confirmer le mot de passe</label>
                            <input type="password" name="password_confirmation" id="password-confirmation"
                                class="form-control" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Réinitialiser
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function resetPassword() {
            const modal = new bootstrap.Modal(document.getElementById('password-modal'));
            modal.show();
        }

        function generatePassword() {
            const length = 12;
            const charset = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*";
            let password = "";
            for (let i = 0; i < length; i++) {
                password += charset.charAt(Math.floor(Math.random() * charset.length));
            }
            document.getElementById('new-password').value = password;
            document.getElementById('password-confirmation').value = password;
        }

        function copyToClipboard(elementId) {
            const element = document.getElementById(elementId);
            element.select();
            element.setSelectionRange(0, 99999); // Pour mobile
            navigator.clipboard.writeText(element.value);

            // Afficher un message de confirmation
            const btn = event.target.closest('button');
            const originalHtml = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-check text-success"></i>';
            setTimeout(() => {
                btn.innerHTML = originalHtml;
            }, 2000);
        }

        function printCredentials() {
            const printWindow = window.open('', '_blank');
            const userName = @json($user->name);
            const boutiqueNom = @json($user->boutique?->nom ?? 'Non définie');
            const userEmail = @json($user->email);
            const loginUrl = window.location.origin + '/login';
            const date = new Date().toLocaleDateString('fr-FR');

            printWindow.document.write(`
                <!DOCTYPE html>
                <html>
                    <head>
                        <title>Identifiants de connexion - ${userName}</title>
                        <style>
                            body { font-family: Arial, sans-serif; padding: 40px; background: white; }
                            .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #333; padding-bottom: 20px; }
                            .header h1 { color: #333; margin: 0; }
                            .credentials { background: #f8f9fa; padding: 30px; border-radius: 8px; margin: 20px 0; border: 1px solid #dee2e6; }
                            .credential-item { margin: 20px 0; padding: 15px; background: white; border-radius: 5px; }
                            .label { font-weight: bold; color: #495057; font-size: 14px; margin-bottom: 5px; }
                            .value { font-size: 18px; color: #212529; font-weight: 600; }
                            .footer { margin-top: 40px; text-align: center; color: #6c757d; font-size: 12px; border-top: 1px solid #dee2e6; padding-top: 20px; }
                        </style>
                    </head>
                    <body>
                        <div class="header">
                            <h1>Identifiants de connexion</h1>
                            <p style="color: #6c757d; margin: 10px 0 0 0;">Boutique : ${boutiqueNom}</p>
                        </div>
                        <div class="credentials">
                            <div class="credential-item">
                                <div class="label">URL de connexion :</div>
                                <div class="value">${loginUrl}</div>
                            </div>
                            <div class="credential-item">
                                <div class="label">Email :</div>
                                <div class="value">${userEmail}</div>
                            </div>
                            <div class="credential-item">
                                <div class="label">Mot de passe :</div>
                                <div class="value" style="color: #dc3545;">Contactez l'administrateur pour obtenir le mot de passe</div>
                            </div>
                        </div>
                        <div class="footer">
                            <p><strong>Ces identifiants sont confidentiels. Ne les partagez pas.</strong></p>
                            <p>Généré le ${date}</p>
                        </div>
                    </body>
                </html>
            `);
            printWindow.document.close();
            printWindow.print();
        }
    </script>
@endsection
