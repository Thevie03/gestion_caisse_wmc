@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card saas-surface-card shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">
                            <i class="fas fa-file-contract me-2"></i>
                            Conditions Générales d'Utilisation
                        </h4>
                    </div>
                    <div class="card-body">
                        @if (session('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="fas fa-check-circle me-2"></i>
                                {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"
                                    aria-label="Close"></button>
                            </div>
                        @endif

                        @if ($acceptation)
                            <div class="alert alert-info mb-4">
                                <i class="fas fa-info-circle me-2"></i>
                                <strong>Vous avez accepté les conditions générales d'utilisation le
                                    {{ $acceptation->accepted_at->format('d/m/Y à H:i') }}.</strong>
                            </div>
                        @endif

                        <div class="conditions-content cgu-content">
                            @php
                                $cguFromDb = \App\Models\ParametreSysteme::get('cgu', '');
                            @endphp
                            @if (!empty($cguFromDb))
                                {!! \App\Support\HtmlSanitizer::clean($cguFromDb) !!}
                            @else
                                <h5 class="mb-3">1. Objet</h5>
                                <p class="mb-4">
                                    Les présentes Conditions Générales d'Utilisation (ci-après "CGU") ont pour objet de
                                    définir
                                    les conditions d'accès et d'utilisation de l'application GestionCaisse développée par
                                    WMC.
                                </p>

                                <h5 class="mb-3">2. Acceptation des Conditions</h5>
                                <p class="mb-4">
                                    L'utilisation de l'application GestionCaisse implique l'acceptation pleine et entière
                                    des
                                    présentes CGU. En accédant et en utilisant l'application, vous reconnaissez avoir lu,
                                    compris et accepté d'être lié par ces conditions.
                                </p>

                                <h5 class="mb-3">3. Description du Service</h5>
                                <p class="mb-4">
                                    GestionCaisse est une application de gestion de caisse permettant aux boutiques et
                                    commerçants de gérer leurs ventes, stocks, clients, fournisseurs et autres aspects de
                                    leur
                                    activité commerciale.
                                </p>

                                <h5 class="mb-3">4. Inscription et Compte Utilisateur</h5>
                                <p class="mb-4">
                                    Pour utiliser l'application, vous devez créer un compte. Vous êtes responsable de la
                                    confidentialité de vos identifiants et de toutes les activités qui se produisent sous
                                    votre
                                    compte. Vous vous engagez à nous informer immédiatement de toute utilisation non
                                    autorisée
                                    de votre compte.
                                </p>

                                <h5 class="mb-3">4.1. Gestion des Employés</h5>
                                <p class="mb-4">
                                    <strong>Chaque administrateur de boutique peut créer et gérer un maximum de 2 employés
                                        pour sa boutique.</strong>
                                    Cette limite est fixe et ne peut être dépassée. L'administrateur de boutique est
                                    responsable
                                    de la gestion de ses employés et de leurs accès au système.
                                </p>

                                <h5 class="mb-3">5. Abonnements et Paiements</h5>
                                <p class="mb-4">
                                    L'utilisation de l'application peut être soumise à un abonnement payant. Les tarifs et
                                    modalités de paiement sont définis dans les conditions d'abonnement. Les paiements sont
                                    effectués selon les modalités convenues et doivent être effectués dans les délais
                                    impartis.
                                </p>

                                <h5 class="mb-3">6. Obligations de l'Utilisateur</h5>
                                <p class="mb-4">
                                    Vous vous engagez à :
                                <ul>
                                    <li>Utiliser l'application conformément à sa destination et aux lois en vigueur</li>
                                    <li>Ne pas tenter d'accéder de manière non autorisée à l'application ou à ses systèmes
                                    </li>
                                    <li>Ne pas utiliser l'application à des fins illégales ou frauduleuses</li>
                                    <li>Respecter les droits de propriété intellectuelle de WMC</li>
                                    <li>Maintenir la confidentialité de vos identifiants</li>
                                </ul>
                                </p>

                                <h5 class="mb-3">7. Protection des Données Personnelles</h5>
                                <p class="mb-4">
                                    WMC s'engage à protéger vos données personnelles conformément à la réglementation en
                                    vigueur. Les données collectées sont utilisées uniquement dans le cadre de la fourniture
                                    du
                                    service et ne sont pas partagées avec des tiers sans votre consentement, sauf obligation
                                    légale.
                                </p>

                                <h5 class="mb-3">8. Propriété Intellectuelle</h5>
                                <p class="mb-4">
                                    L'application GestionCaisse, son code source, son design, ses logos et tous les éléments
                                    qui
                                    la composent sont la propriété exclusive de WMC et sont protégés par les lois sur la
                                    propriété intellectuelle.
                                </p>

                                <h5 class="mb-3">9. Disponibilité du Service</h5>
                                <p class="mb-4">
                                    WMC s'efforce d'assurer une disponibilité maximale de l'application, mais ne peut
                                    garantir une disponibilité à 100%. Des interruptions peuvent survenir pour maintenance,
                                    mises à jour ou pour des raisons indépendantes de notre volonté.
                                </p>

                                <h5 class="mb-3">10. Limitation de Responsabilité</h5>
                                <p class="mb-4">
                                    WMC ne pourra être tenu responsable des dommages directs ou indirects résultant de
                                    l'utilisation ou de l'impossibilité d'utiliser l'application, y compris la perte de
                                    données
                                    ou de profits.
                                </p>

                                <h5 class="mb-3">11. Résiliation</h5>
                                <p class="mb-4">
                                    WMC se réserve le droit de suspendre ou résilier votre accès à l'application en cas
                                    de
                                    non-respect des présentes CGU, de non-paiement de l'abonnement ou pour toute autre
                                    raison
                                    légitime.
                                </p>

                                <h5 class="mb-3">12. Modification des CGU</h5>
                                <p class="mb-4">
                                    WMC se réserve le droit de modifier les présentes CGU à tout moment. Les
                                    modifications
                                    seront notifiées aux utilisateurs et l'utilisation continue de l'application après
                                    notification vaut acceptation des nouvelles conditions.
                                </p>

                                <h5 class="mb-3">13. Droit Applicable et Juridiction</h5>
                                <p class="mb-4">
                                    Les présentes CGU sont régies par le droit applicable. Tout litige relatif à leur
                                    interprétation ou à leur exécution relève de la compétence des tribunaux compétents.
                                </p>

                                <h5 class="mb-3">14. Contact</h5>
                                <p class="mb-4">
                                    Pour toute question concernant les présentes CGU, vous pouvez nous contacter via les
                                    moyens
                                    de contact fournis dans l'application.
                                </p>
                            @endif

                            <div class="alert alert-warning mt-4">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <strong>Important :</strong> En acceptant ces conditions, vous reconnaissez avoir lu et
                                compris l'ensemble des clauses ci-dessus et vous vous engagez à les respecter.
                            </div>
                        </div>

                        <div class="text-center mt-4">
                            <form action="{{ route('conditions-generales.accept') }}" method="POST" id="acceptForm">
                                @csrf
                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" id="acceptCheckbox"
                                        {{ $acceptation ? 'checked disabled' : 'required' }}>
                                    <label class="form-check-label" for="acceptCheckbox">
                                        <strong>J'ai lu et j'accepte les conditions générales d'utilisation</strong>
                                    </label>
                                </div>
                                <button type="submit" class="btn btn-primary btn-lg" id="acceptButton"
                                    @if ($acceptation) disabled @endif>
                                    <i class="fas fa-check-circle me-2"></i>
                                    {{ $acceptation ? 'Déjà accepté' : 'Accepter les conditions' }}
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const checkbox = document.getElementById('acceptCheckbox');
            const button = document.getElementById('acceptButton');
            const form = document.getElementById('acceptForm');
            const hasAccepted = {{ $acceptation ? 'true' : 'false' }};

            if (checkbox && button && !hasAccepted) {
                // Initialiser l'état du bouton (désactivé par défaut)
                button.disabled = !checkbox.checked;

                // Activer/désactiver le bouton selon l'état de la checkbox
                checkbox.addEventListener('change', function() {
                    button.disabled = !this.checked;

                    // Changer le style visuel
                    if (this.checked) {
                        button.classList.remove('btn-secondary');
                        button.classList.add('btn-primary');
                    } else {
                        button.classList.remove('btn-primary');
                        button.classList.add('btn-secondary');
                    }
                });
            }
        });
    </script>
@endsection
