@extends('layouts.app')

@section('content')
    <div class="container-fluid px-4 py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-1 fw-bold">Paramètres du Système</h1>
                <p class="text-muted mb-0">Configuration générale de l'application</p>
            </div>
        </div>

        <div class="row">
            <div class="col-md-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-bottom">
                        <h5 class="mb-0 fw-semibold">Paramètres Généraux</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('admin.parametres.update') }}" enctype="multipart/form-data">
                            @csrf

                            <!-- Nom de l'application -->
                            <div class="mb-4">
                                <label class="form-label fw-semibold">
                                    <i class="fas fa-tag me-2 text-primary"></i>
                                    Nom de l'application
                                </label>
                                <input type="text" name="app_nom" class="form-control"
                                    value="{{ \App\Models\ParametreSysteme::get('app_nom', config('app.name')) }}" required>
                            </div>

                            <!-- Logo de l'application -->
                            <div class="mb-4">
                                <label class="form-label fw-semibold">
                                    <i class="fas fa-image me-2 text-primary"></i>
                                    Logo de l'application
                                </label>
                                <input type="file" name="app_logo" class="form-control" accept="image/*">
                                @if (\App\Models\ParametreSysteme::get('app_logo'))
                                    <small class="text-muted">Logo actuel :
                                        <img src="{{ asset(\App\Models\ParametreSysteme::get('app_logo')) }}" alt="Logo"
                                            height="40" class="ms-2">
                                    </small>
                                @endif
                            </div>

                            <!-- Devise par défaut -->
                            <div class="mb-4">
                                <label class="form-label fw-semibold">
                                    <i class="fas fa-coins me-2 text-primary"></i>
                                    Devise par défaut
                                </label>
                                <select name="devise_defaut" class="form-select" required>
                                    <option value="FCFA" @selected(\App\Models\ParametreSysteme::get('devise_defaut') === 'FCFA')>FCFA</option>
                                    <option value="EUR" @selected(\App\Models\ParametreSysteme::get('devise_defaut') === 'EUR')>EUR (€)</option>
                                    <option value="USD" @selected(\App\Models\ParametreSysteme::get('devise_defaut') === 'USD')>USD ($)</option>
                                    <option value="XOF" @selected(\App\Models\ParametreSysteme::get('devise_defaut') === 'XOF')>XOF</option>
                                </select>
                            </div>

                            <!-- Durée période d'essai -->
                            <div class="mb-4">
                                <label class="form-label fw-semibold">
                                    <i class="fas fa-calendar-alt me-2 text-primary"></i>
                                    Durée de la période d'essai (en jours)
                                </label>
                                <input type="number" name="periode_essai_jours" class="form-control"
                                    value="{{ \App\Models\ParametreSysteme::get('periode_essai_jours', 30) }}"
                                    min="0" required>
                            </div>

                            <!-- Rappel abonnement -->
                            <div class="mb-4">
                                <label class="form-label fw-semibold">
                                    <i class="fas fa-bell me-2 text-primary"></i>
                                    Nombre de jours avant expiration pour envoyer un rappel
                                </label>
                                <input type="number" name="rappel_abonnement_jours" class="form-control"
                                    value="{{ \App\Models\ParametreSysteme::get('rappel_abonnement_jours', 7) }}"
                                    min="1" required>
                            </div>

                            <!-- Message de rappel -->
                            <div class="mb-4">
                                <label class="form-label fw-semibold">
                                    <i class="fas fa-envelope me-2 text-primary"></i>
                                    Message automatique de rappel d'abonnement
                                </label>
                                <textarea name="message_rappel_abonnement" class="form-control" rows="3" required>
{{ \App\Models\ParametreSysteme::get('message_rappel_abonnement', 'Votre abonnement expire bientôt. Veuillez le renouveler pour continuer à utiliser nos services.') }}
                                </textarea>
                            </div>

                            <!-- Conditions générales -->
                            <div class="mb-4">
                                <label class="form-label fw-semibold">
                                    <i class="fas fa-file-contract me-2 text-primary"></i>
                                    Conditions générales d'utilisation (CGU)
                                    <span class="text-danger">*</span>
                                </label>
                                <textarea name="cgu" class="form-control" rows="20" id="cgu_textarea" required>{{ \App\Models\ParametreSysteme::get('cgu', '') }}</textarea>
                                <div class="form-text">
                                    <small class="text-muted">
                                        <i class="fas fa-info-circle me-1"></i>
                                        Vous pouvez utiliser du HTML pour formater le texte. Les modifications seront
                                        visibles dans toutes les boutiques après enregistrement.
                                    </small>
                                </div>
                                <div class="alert alert-success mt-2 mb-0">
                                    <i class="fas fa-check-circle me-2"></i>
                                    <strong>Information :</strong> Les CGU modifiées ici seront automatiquement mises à jour
                                    dans le dashboard de toutes les boutiques. N'oubliez pas de mentionner que chaque
                                    administrateur de boutique peut créer au maximum <strong>2 employés</strong> pour sa
                                    boutique.
                                </div>
                            </div>

                            <div class="d-flex justify-content-end">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Enregistrer les paramètres
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-bottom">
                        <h5 class="mb-0 fw-semibold">Informations</h5>
                    </div>
                    <div class="card-body">
                        <p class="text-muted small">
                            Les paramètres modifiés ici affectent l'ensemble du système.
                            Assurez-vous de bien vérifier avant d'enregistrer.
                        </p>
                        <hr>
                        <h6 class="fw-semibold">Paramètres disponibles :</h6>
                        <ul class="small text-muted">
                            <li>Nom et logo de l'application</li>
                            <li>Devise par défaut</li>
                            <li>Durée période d'essai</li>
                            <li>Rappels d'abonnement</li>
                            <li>Conditions générales</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
