@extends('layouts.app')

@section('content')
    <div class="container-fluid px-4 py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-1 fw-bold">Notifications</h1>
                <p class="text-muted mb-0">Gestion de toutes vos notifications</p>
            </div>
            <div class="d-flex gap-2">
                <form method="POST" action="{{ route('admin.notifications.mark-all-read') }}" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-outline-primary">
                        <i class="fas fa-check-double me-2"></i>Tout marquer comme lu
                    </button>
                </form>
                <form method="POST" action="{{ route('admin.notifications.delete-all-read') }}" class="d-inline"
                    onsubmit="return confirm('Supprimer toutes les notifications lues ?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-outline-danger">
                        <i class="fas fa-trash me-2"></i>Supprimer les lues
                    </button>
                </form>
            </div>
        </div>

        <!-- Statistiques -->
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <p class="text-muted small mb-1">Total</p>
                        <h3 class="mb-0 fw-bold">{{ number_format($stats['total']) }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <p class="text-muted small mb-1">Non lues</p>
                        <h3 class="mb-0 fw-bold text-danger">{{ number_format($stats['non_lues']) }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <p class="text-muted small mb-1">Par type</p>
                        <div class="d-flex flex-wrap gap-2">
                            @foreach ($stats['par_type'] as $type => $count)
                                <span
                                    class="badge bg-{{ $type === 'info' ? 'primary' : ($type === 'warning' ? 'warning' : ($type === 'error' ? 'danger' : 'success')) }}">
                                    {{ ucfirst($type) }}: {{ $count }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <p class="text-muted small mb-1">Par module</p>
                        <div class="d-flex flex-wrap gap-2">
                            @foreach ($stats['par_module'] as $module => $count)
                                <span class="badge bg-secondary">{{ ucfirst($module ?? 'Général') }}:
                                    {{ $count }}</span>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filtres -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <form method="GET" action="{{ route('admin.notifications.index') }}" class="row g-3">
                    <div class="col-md-3">
                        <select name="type" class="form-select">
                            <option value="">Tous les types</option>
                            <option value="info" @selected(request('type') === 'info')>Info</option>
                            <option value="warning" @selected(request('type') === 'warning')>Avertissement</option>
                            <option value="error" @selected(request('type') === 'error')>Erreur</option>
                            <option value="success" @selected(request('type') === 'success')>Succès</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select name="module" class="form-select">
                            <option value="">Tous les modules</option>
                            <option value="abonnements" @selected(request('module') === 'abonnements')>Abonnements</option>
                            <option value="paiements" @selected(request('module') === 'paiements')>Paiements</option>
                            <option value="users" @selected(request('module') === 'users')>Utilisateurs</option>
                            <option value="boutiques" @selected(request('module') === 'boutiques')>Boutiques</option>
                            <option value="support" @selected(request('module') === 'support')>Support & Assistance</option>
                            <option value="systeme" @selected(request('module') === 'systeme')>Système</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select name="priorite" class="form-select">
                            <option value="">Toutes les priorités</option>
                            <option value="basse" @selected(request('priorite') === 'basse')>Basse</option>
                            <option value="normale" @selected(request('priorite') === 'normale')>Normale</option>
                            <option value="haute" @selected(request('priorite') === 'haute')>Haute</option>
                            <option value="critique" @selected(request('priorite') === 'critique')>Critique</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select name="lue" class="form-select">
                            <option value="">Tous les statuts</option>
                            <option value="false" @selected(request('lue') === 'false')>Non lues</option>
                            <option value="true" @selected(request('lue') === 'true')>Lues</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-filter me-2"></i>Filtrer
                        </button>
                        <a href="{{ route('admin.notifications.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-times me-2"></i>Réinitialiser
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Liste des notifications -->
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="list-group list-group-flush">
                    @forelse($notifications as $notification)
                        @php
                            $isSupport = $notification->module === 'support' && isset($notification->data['ticket_id']);
                            $ticketRoute = null;
                            $notificationRoute = null;
                            if ($isSupport && auth()->user()->isAdmin() && !auth()->user()->isOwner()) {
                                $ticketRoute = route('admin.support.show', $notification->data['ticket_id']);
                            } else {
                                $notificationRoute = route('admin.notifications.show', $notification);
                            }
                        @endphp
                        @if ($isSupport && $ticketRoute)
                            <a href="{{ $ticketRoute }}"
                                class="list-group-item px-0 border-0 border-bottom text-decoration-none">
                            @else
                                <a href="{{ $notificationRoute }}"
                                    class="list-group-item px-0 border-0 border-bottom text-decoration-none">
                        @endif
                        <div class="d-flex align-items-start">
                            <div class="me-3">
                                <div class="bg-{{ $notification->couleur }} bg-opacity-10 rounded-circle p-3">
                                    <i class="{{ $notification->icone }} text-{{ $notification->couleur }} fa-lg"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <h5
                                            class="mb-1 {{ !$notification->lue ? 'fw-bold' : '' }} {{ $isSupport && $ticketRoute ? 'text-primary' : '' }}">
                                            {{ $notification->titre }}
                                            @if (!$notification->lue)
                                                <span class="badge bg-primary rounded-pill ms-2"
                                                    style="width: 8px; height: 8px;"></span>
                                            @endif
                                            @if ($isSupport && $ticketRoute)
                                                <i class="fas fa-external-link-alt ms-2 small"></i>
                                            @endif
                                        </h5>
                                        <p class="text-muted mb-2">
                                            @if ($notification->module === 'stock')
                                                @php
                                                    $message = $notification->message;
                                                    // Colorer 'sortie' en rouge
                                                    $message = preg_replace(
                                                        "/'sortie'/",
                                                        "<span class='text-danger fw-bold'>'sortie'</span>",
                                                        $message,
                                                    );
                                                    // Colorer 'entree' en vert
                                                    $message = preg_replace(
                                                        "/'entree'/",
                                                        "<span class='text-success fw-bold'>'entree'</span>",
                                                        $message,
                                                    );
                                                @endphp
                                                {!! $message !!}
                                            @else
                                                {{ $notification->message }}
                                            @endif
                                        </p>
                                    </div>
                                    <div class="text-end">
                                        <span
                                            class="badge bg-{{ $notification->priorite === 'critique' ? 'danger' : ($notification->priorite === 'haute' ? 'warning' : 'secondary') }}">
                                            {{ ucfirst($notification->priorite) }}
                                        </span>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        @if ($notification->module)
                                            <span
                                                class="badge bg-secondary me-2">{{ ucfirst($notification->module) }}</span>
                                        @endif
                                        <small class="text-muted">
                                            <i class="fas fa-clock me-1"></i>
                                            {{ $notification->created_at->diffForHumans() }}
                                            @if ($notification->lue && $notification->lue_at)
                                                • Lu le {{ $notification->lue_at->format('d/m/Y H:i') }}
                                            @endif
                                        </small>
                                    </div>
                                    <div class="d-flex gap-2">
                                        @if ($isSupport && $ticketRoute)
                                            <a href="{{ $ticketRoute }}" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-eye me-1"></i>Voir le ticket
                                            </a>
                                        @else
                                            <a href="{{ route('admin.notifications.show', $notification) }}"
                                                class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-eye me-1"></i>Voir
                                            </a>
                                        @endif
                                        @if (!$notification->lue)
                                            <button type="button" class="btn btn-sm btn-outline-success mark-as-read-btn"
                                                data-notification-id="{{ $notification->id }}"
                                                data-url="{{ route('admin.notifications.mark-read', $notification) }}"
                                                onclick="event.stopPropagation();">
                                                <i class="fas fa-check me-1"></i>Marquer lu
                                            </button>
                                        @endif
                                        <form method="POST"
                                            action="{{ route('admin.notifications.destroy', $notification) }}"
                                            class="d-inline" onclick="event.stopPropagation();"
                                            onsubmit="return confirm('Supprimer cette notification ?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                <i class="fas fa-trash me-1"></i>Supprimer
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @if ($isSupport && $ticketRoute)
                            </a>
                        @else
                            </a>
                        @endif
                    @empty
                        <div class="text-center text-muted py-5">
                            <i class="fas fa-bell-slash fa-3x mb-3"></i>
                            <p>Aucune notification trouvée</p>
                        </div>
                    @endforelse
                </div>

                <div class="mt-3">
                    {{ $notifications->withQueryString()->links() }}
                </div>
            </div>
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
                        const notificationId = button.getAttribute('data-notification-id');
                        const listItem = button.closest('.list-group-item') || button.closest('a');

                        // Désactiver le bouton pendant la requête
                        button.disabled = true;
                        const originalHtml = button.innerHTML;
                        button.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>';

                        // Envoyer la requête AJAX
                        fetch(url, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                    'Accept': 'application/json'
                                }
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    // Masquer le bouton
                                    button.style.display = 'none';

                                    // Retirer le badge "Non lue" si présent
                                    if (listItem) {
                                        const badge = listItem.querySelector(
                                            '.badge.bg-primary.rounded-pill, .badge.bg-primary');
                                        if (badge && (badge.textContent.includes('Nouveau') || badge
                                                .style.width === '8px')) {
                                            badge.remove();
                                        }

                                        // Retirer la classe bg-light si présente
                                        listItem.classList.remove('bg-light');

                                        // Retirer le fw-bold du titre
                                        const title = listItem.querySelector(
                                            'h5.fw-bold, h6.fw-bold');
                                        if (title) {
                                            title.classList.remove('fw-bold');
                                        }

                                        // Ajouter "Lu le" dans les informations
                                        const smallText = listItem.querySelector(
                                        'small.text-muted');
                                        if (smallText) {
                                            const now = new Date();
                                            const dateStr = now.toLocaleDateString('fr-FR') + ' ' +
                                                now.toLocaleTimeString('fr-FR', {
                                                    hour: '2-digit',
                                                    minute: '2-digit'
                                                });
                                            const luText = smallText.textContent;
                                            if (!luText.includes('Lu le')) {
                                                smallText.innerHTML += ` • Lu le ${dateStr}`;
                                            }
                                        }
                                    }

                                    // Mettre à jour le compteur de notifications non lues si présent
                                    const nonLuesCount = document.querySelector(
                                        '.text-danger.fw-bold');
                                    if (nonLuesCount) {
                                        const currentCount = parseInt(nonLuesCount.textContent
                                            .replace(/\D/g, '')) || 0;
                                        if (currentCount > 0) {
                                            nonLuesCount.textContent = number_format(currentCount -
                                                1);
                                        }
                                    }
                                }
                            })
                            .catch(error => {
                                console.error('Erreur:', error);
                                button.disabled = false;
                                button.innerHTML = originalHtml;
                                alert('Une erreur est survenue. Veuillez réessayer.');
                            });
                    });
                });
            });

            function number_format(number) {
                return new Intl.NumberFormat('fr-FR').format(number);
            }
        </script>
    @endpush
@endsection
