@extends('layouts.app')

@section('content')

    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="mb-0">
                        <i class="fas fa-bell me-2 text-primary"></i>
                        Notifications
                    </h2>
                    <p class="text-muted mb-0">Gérez vos notifications et alertes</p>
                </div>
                <div>
                    <form action="{{ route('notifications.verifier-stock') }}" method="POST" class="d-inline me-2">
                        @csrf
                        <button type="submit" class="btn btn-warning">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            Vérifier le stock
                        </button>
                    </form>
                    <form action="{{ route('notifications.marquer-toutes-lues') }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-check me-2"></i>
                            Tout marquer comme lu
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistiques -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card stat-card">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <div class="stat-value">{{ $stats['total'] }}</div>
                        <div class="stat-label">Total notifications</div>
                    </div>
                    <div class="ms-3">
                        <i class="fas fa-bell fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card stat-card bg-warning text-white">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <div class="stat-value">{{ $stats['non_lues'] }}</div>
                        <div class="stat-label">Non lues</div>
                    </div>
                    <div class="ms-3">
                        <i class="fas fa-exclamation-circle fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card stat-card bg-danger text-white">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <div class="stat-value">{{ $stats['par_type']['error'] ?? 0 }}</div>
                        <div class="stat-label">Erreurs</div>
                    </div>
                    <div class="ms-3">
                        <i class="fas fa-times-circle fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card stat-card bg-success text-white">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <div class="stat-value">{{ $stats['par_type']['success'] ?? 0 }}</div>
                        <div class="stat-label">Succès</div>
                    </div>
                    <div class="ms-3">
                        <i class="fas fa-check-circle fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtres -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('notifications.index') }}" class="row g-3">
                <div class="col-md-3">
                    <label for="type" class="form-label">Type</label>
                    <select class="form-select" id="type" name="type">
                        <option value="">Tous les types</option>
                        <option value="info" {{ request('type') == 'info' ? 'selected' : '' }}>Information</option>
                        <option value="warning" {{ request('type') == 'warning' ? 'selected' : '' }}>Avertissement</option>
                        <option value="error" {{ request('type') == 'error' ? 'selected' : '' }}>Erreur</option>
                        <option value="success" {{ request('type') == 'success' ? 'selected' : '' }}>Succès</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <label for="module" class="form-label">Module</label>
                    <select class="form-select" id="module" name="module">
                        <option value="">Tous les modules</option>
                        <option value="stock" {{ request('module') == 'stock' ? 'selected' : '' }}>Stock</option>
                        <option value="ventes" {{ request('module') == 'ventes' ? 'selected' : '' }}>Ventes</option>
                        <option value="support" {{ request('module') == 'support' ? 'selected' : '' }}>Support & Assistance
                        </option>
                    </select>
                </div>

                <div class="col-md-3">
                    <label for="lue" class="form-label">Statut</label>
                    <select class="form-select" id="lue" name="lue">
                        <option value="">Tous les statuts</option>
                        <option value="false" {{ request('lue') == 'false' ? 'selected' : '' }}>Non lues</option>
                        <option value="true" {{ request('lue') == 'true' ? 'selected' : '' }}>Lues</option>
                    </select>
                </div>

                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="fas fa-search me-1"></i>
                        Filtrer
                    </button>
                    <a href="{{ route('notifications.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-times me-1"></i>
                        Effacer
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Liste des notifications -->
    <div class="card">
        <div class="card-body">
            @if ($notifications->count() > 0)
                <div class="list-group">
                    @foreach ($notifications as $notification)
                        @php
                            $isSupport = $notification->module === 'support' && isset($notification->data['ticket_id']);
                            $ticketRoute = null;
                            $notificationRoute = route('notifications.show', $notification);
                            if ($isSupport && auth()->user()->isOwner()) {
                                $ticketRoute = route('tickets.show', $notification->data['ticket_id']);
                            }
                        @endphp
                        @if ($isSupport && $ticketRoute)
                            <a href="{{ $ticketRoute }}"
                                class="list-group-item {{ $notification->lue ? '' : 'bg-light' }} text-decoration-none">
                            @else
                                <a href="{{ $notificationRoute }}"
                                    class="list-group-item {{ $notification->lue ? '' : 'bg-light' }} text-decoration-none">
                        @endif
                        <div class="d-flex w-100 justify-content-between align-items-start">
                            <div class="d-flex">
                                <div class="me-3">
                                    <i class="{{ $notification->icone }} text-{{ $notification->couleur }} fa-lg"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <h6
                                        class="mb-1 {{ $notification->lue ? '' : 'fw-bold' }} {{ $isSupport && $ticketRoute ? 'text-primary' : '' }}">
                                        {{ $notification->titre }}
                                        @if (!$notification->lue)
                                            <span class="badge bg-primary ms-2">Nouveau</span>
                                        @endif
                                        @if ($isSupport && $ticketRoute)
                                            <i class="fas fa-external-link-alt ms-2 small"></i>
                                        @endif
                                    </h6>
                                    <p class="mb-1">
                                        <x-notification-message
                                            :message="$notification->message"
                                            :module="$notification->module"
                                        />
                                    </p>
                                    <small class="text-muted">
                                        <i class="fas fa-clock me-1"></i>
                                        {{ $notification->created_at->diffForHumans() }}
                                        @if ($notification->module)
                                            • <span class="badge bg-secondary">{{ ucfirst($notification->module) }}</span>
                                        @endif
                                        @if ($notification->priorite !== 'normale')
                                            • <span
                                                class="badge bg-{{ $notification->priorite === 'critique' ? 'danger' : 'warning' }}">
                                                {{ ucfirst($notification->priorite) }}
                                            </span>
                                        @endif
                                    </small>
                                </div>
                            </div>
                            <div class="d-flex align-items-center">
                                @if (!$notification->lue)
                                    <button type="button" class="btn btn-sm btn-outline-success mark-as-read-btn"
                                        data-notification-id="{{ $notification->id }}"
                                        data-url="{{ route('notifications.marquer-lue', $notification) }}"
                                        onclick="event.stopPropagation();" title="Marquer comme lu">
                                        <i class="fas fa-check"></i>
                                    </button>
                                @endif
                            </div>
                        </div>
                        </a>
                    @endforeach
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-center mt-4">
                    {{ $notifications->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-bell-slash fa-3x text-muted mb-3"></i>
                    <h4>Aucune notification</h4>
                    <p class="text-muted">Vous n'avez aucune notification pour le moment.</p>
                </div>
            @endif
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Gérer tous les boutons "Marquer comme lu" dans la liste
                document.querySelectorAll('.mark-as-read-btn').forEach(function(btn) {
                    btn.addEventListener('click', function(e) {
                        e.preventDefault();
                        e.stopPropagation();

                        const button = this;
                        const url = button.getAttribute('data-url');
                        const listItem = button.closest('.list-group-item') || button.closest('a');

                        // Désactiver le bouton pendant la requête
                        button.disabled = true;
                        const originalHtml = button.innerHTML;
                        button.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

                        // Créer un AbortController pour gérer le timeout
                        const controller = new AbortController();
                        const timeoutId = setTimeout(() => controller.abort(),
                        10000); // 10 secondes timeout

                        // Envoyer la requête AJAX
                        fetch(url, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                    'Accept': 'application/json',
                                    'X-Requested-With': 'XMLHttpRequest'
                                },
                                credentials: 'same-origin',
                                signal: controller.signal
                            })
                            .then(response => {
                                clearTimeout(timeoutId);
                                if (!response.ok) {
                                    return response.json().then(data => {
                                        throw new Error(data.message || 'Erreur HTTP: ' +
                                            response.status);
                                    });
                                }
                                return response.json();
                            })
                            .then(data => {
                                if (data.success) {
                                    // Masquer le bouton
                                    button.style.display = 'none';

                                    // Retirer le badge "Nouveau" si présent
                                    if (listItem) {
                                        const badge = listItem.querySelector('.badge.bg-primary');
                                        if (badge && badge.textContent.includes('Nouveau')) {
                                            badge.remove();
                                        }

                                        // Retirer la classe bg-light si présente
                                        listItem.classList.remove('bg-light');

                                        // Retirer le fw-bold du titre
                                        const title = listItem.querySelector('h6.fw-bold');
                                        if (title) {
                                            title.classList.remove('fw-bold');
                                        }
                                    }

                                    // Mettre à jour le compteur de notifications non lues
                                    const nonLuesCard = document.querySelector(
                                        '.stat-card.bg-warning .stat-value');
                                    if (nonLuesCard) {
                                        const currentCount = parseInt(nonLuesCard.textContent
                                            .replace(/\D/g, '')) || 0;
                                        if (currentCount > 0) {
                                            nonLuesCard.textContent = currentCount - 1;
                                        }
                                    }
                                } else {
                                    throw new Error(data.message ||
                                    'Erreur lors de la mise à jour');
                                }
                            })
                            .catch(error => {
                                clearTimeout(timeoutId);
                                console.error('Erreur:', error);
                                button.disabled = false;
                                button.innerHTML = originalHtml;

                                if (error.name === 'AbortError') {
                                    alert('La requête a pris trop de temps. Veuillez réessayer.');
                                } else {
                                    alert('Une erreur est survenue: ' + (error.message ||
                                        'Erreur inconnue'));
                                }
                            });
                    });
                });
            });
        </script>
    @endpush

@endsection
