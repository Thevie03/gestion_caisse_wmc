@extends('layouts.app')

@section('page-title', 'Mon Profil')

@section('content')
    <div class="container-fluid px-4 py-4">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-1 fw-bold">
                    <i class="fas fa-user-circle me-2 text-primary"></i>
                    Mon Profil
                </h1>
                <p class="text-muted mb-0">Gérez vos informations personnelles et la sécurité de votre compte</p>
            </div>
        </div>

        <div class="row g-4">
            <!-- Informations du profil -->
            <div class="col-12 col-lg-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-bottom">
                        <h5 class="mb-0 fw-semibold">
                            <i class="fas fa-user me-2 text-primary"></i>
                            Informations du profil
                        </h5>
                    </div>
                    <div class="card-body">
                        @include('profile.partials.update-profile-information-form')
                    </div>
                </div>
            </div>

            <!-- Informations supplémentaires -->
            <div class="col-12 col-lg-4">
                <!-- Personnalisation du thème -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-bottom">
                        <h5 class="mb-0 fw-semibold">
                            <i class="fas fa-palette me-2 text-primary"></i>
                            Personnalisation du thème
                        </h5>
                    </div>
                    <div class="card-body">
                        @include('profile.partials.update-theme-form')
                    </div>
                    @if (session('theme_updated'))
                        <script>
                            // Forcer le rechargement complet de la page après mise à jour du thème
                            // Vider le cache du navigateur et recharger
                            setTimeout(function() {
                                // Vider le cache CSS
                                if ('caches' in window) {
                                    caches.keys().then(function(names) {
                                        for (let name of names) {
                                            caches.delete(name);
                                        }
                                    });
                                }
                                // Recharger avec cache-busting
                                window.location.href = window.location.href.split('?')[0] + '?v=' + Date.now();
                            }, 200);
                        </script>
                    @endif
                </div>

                <!-- Sécurité -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-bottom">
                        <h5 class="mb-0 fw-semibold">
                            <i class="fas fa-shield-alt me-2 text-success"></i>
                            Sécurité
                        </h5>
                    </div>
                    <div class="card-body">
                        @include('profile.partials.update-password-form')
                    </div>
                </div>

                <!-- Zone de danger -->
                <div class="card border-0 shadow-sm border-danger">
                    <div class="card-header bg-white border-bottom border-danger">
                        <h5 class="mb-0 fw-semibold text-danger">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            Zone de danger
                        </h5>
                    </div>
                    <div class="card-body">
                        @include('profile.partials.delete-user-form')
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
