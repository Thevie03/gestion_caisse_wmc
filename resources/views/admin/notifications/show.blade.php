@extends('layouts.app')

@section('content')
    <div class="container-fluid px-4 py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-1 fw-bold">Détails de la notification</h1>
                <p class="text-muted mb-0">Informations complètes sur la notification</p>
            </div>
            <div>
                <a href="{{ route('admin.notifications.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Retour
                </a>
            </div>
        </div>

        <div class="row">
            <div class="col-md-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex align-items-start mb-4">
                            <div class="me-3">
                                <div class="bg-{{ $notification->couleur }} bg-opacity-10 rounded-circle p-4">
                                    <i class="{{ $notification->icone }} text-{{ $notification->couleur }} fa-2x"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1">
                                <h2 class="mb-2">{{ $notification->titre }}</h2>
                                <div class="d-flex gap-2 mb-3">
                                    <span class="badge bg-{{ $notification->couleur }}">
                                        {{ ucfirst($notification->type) }}
                                    </span>
                                    <span
                                        class="badge bg-{{ $notification->priorite === 'critique' ? 'danger' : ($notification->priorite === 'haute' ? 'warning' : 'secondary') }}">
                                        Priorité: {{ ucfirst($notification->priorite) }}
                                    </span>
                                    @if ($notification->module)
                                        <span class="badge bg-secondary">
                                            Module: {{ ucfirst($notification->module) }}
                                        </span>
                                    @endif
                                    @if (!$notification->lue)
                                        <span class="badge bg-primary">Non lue</span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <h5 class="fw-semibold mb-2">Message</h5>
                            <p class="text-muted">
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

                        @if ($notification->data)
                            <div class="mb-4">
                                <h5 class="fw-semibold mb-2">Données supplémentaires</h5>
                                <div class="bg-light rounded p-3">
                                    <pre class="mb-0">{{ json_encode($notification->data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                </div>
                            </div>
                        @endif

                        <div class="d-flex gap-2">
                            @if (!$notification->lue)
                                <button type="button" class="btn btn-primary mark-as-read-btn"
                                    data-notification-id="{{ $notification->id }}"
                                    data-url="{{ route('admin.notifications.mark-read', $notification) }}">
                                    <i class="fas fa-check me-2"></i>Marquer comme lu
                                </button>
                            @endif
                            <form method="POST" action="{{ route('admin.notifications.destroy', $notification) }}"
                                class="d-inline" onsubmit="return confirm('Supprimer cette notification ?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger">
                                    <i class="fas fa-trash me-2"></i>Supprimer
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-bottom">
                        <h5 class="mb-0 fw-semibold">Informations</h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-borderless">
                            <tr>
                                <td class="fw-semibold">Date de création :</td>
                                <td>{{ $notification->created_at->format('d/m/Y H:i:s') }}</td>
                            </tr>
                            <tr>
                                <td class="fw-semibold">Il y a :</td>
                                <td>{{ $notification->created_at->diffForHumans() }}</td>
                            </tr>
                            @if ($notification->lue && $notification->lue_at)
                                <tr>
                                    <td class="fw-semibold">Date de lecture :</td>
                                    <td>{{ $notification->lue_at->format('d/m/Y H:i:s') }}</td>
                                </tr>
                            @endif
                            @if ($notification->user)
                                <tr>
                                    <td class="fw-semibold">Destinataire :</td>
                                    <td>{{ $notification->user->name }}</td>
                                </tr>
                            @else
                                <tr>
                                    <td class="fw-semibold">Destinataire :</td>
                                    <td>Tous les admins</td>
                                </tr>
                            @endif
                        </table>

                        @if ($notification->data)
                            @if (isset($notification->data['user_id']))
                                <hr>
                                <div class="d-grid">
                                    <a href="{{ route('admin.users.show', $notification->data['user_id']) }}"
                                        class="btn btn-outline-primary btn-sm">
                                        <i class="fas fa-user me-2"></i>Voir l'utilisateur
                                    </a>
                                </div>
                            @endif

                            @if (isset($notification->data['boutique_id']))
                                <div class="d-grid mt-2">
                                    <a href="{{ route('admin.boutiques.show', $notification->data['boutique_id']) }}"
                                        class="btn btn-outline-primary btn-sm">
                                        <i class="fas fa-store me-2"></i>Voir la boutique
                                    </a>
                                </div>
                            @endif

                            @if (isset($notification->data['abonnement_id']))
                                <div class="d-grid mt-2">
                                    <a href="{{ route('admin.users.show', $notification->data['user_id'] ?? 0) }}#abonnements"
                                        class="btn btn-outline-primary btn-sm">
                                        <i class="fas fa-credit-card me-2"></i>Voir l'abonnement
                                    </a>
                                </div>
                            @endif

                            @if (isset($notification->data['paiement_id']))
                                <div class="d-grid mt-2">
                                    <a href="{{ route('admin.paiements.index') }}" class="btn btn-outline-primary btn-sm">
                                        <i class="fas fa-money-check-alt me-2"></i>Voir le paiement
                                    </a>
                                </div>
                            @endif
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const markAsReadBtn = document.querySelector('.mark-as-read-btn');
                if (markAsReadBtn) {
                    markAsReadBtn.addEventListener('click', function() {
                        const btn = this;
                        const url = btn.getAttribute('data-url');
                        const notificationId = btn.getAttribute('data-notification-id');

                        // Désactiver le bouton pendant la requête
                        btn.disabled = true;
                        btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>En cours...';

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
                                    btn.style.display = 'none';

                                    // Mettre à jour le badge "Non lue" si présent
                                    const nonLueBadge = document.querySelector('.badge.bg-primary');
                                    if (nonLueBadge && nonLueBadge.textContent.trim() === 'Non lue') {
                                        nonLueBadge.remove();
                                    }

                                    // Ajouter l'information "Lue le" dans la section informations
                                    const infoTable = document.querySelector('.table.table-borderless');
                                    if (infoTable) {
                                        const lueRow = infoTable.querySelector(
                                            'tr:has(td:contains("Date de lecture"))');
                                        if (!lueRow) {
                                            const newRow = document.createElement('tr');
                                            const now = new Date();
                                            const dateStr = now.toLocaleDateString('fr-FR') + ' ' + now
                                                .toLocaleTimeString('fr-FR', {
                                                    hour: '2-digit',
                                                    minute: '2-digit',
                                                    second: '2-digit'
                                                });
                                            newRow.innerHTML = `
                                        <td class="fw-semibold">Date de lecture :</td>
                                        <td>${dateStr}</td>
                                    `;
                                            // Insérer après la ligne "Il y a"
                                            const ilYaRow = Array.from(infoTable.querySelectorAll('tr'))
                                                .find(tr => tr.textContent.includes('Il y a'));
                                            if (ilYaRow) {
                                                ilYaRow.parentNode.insertBefore(newRow, ilYaRow
                                                .nextSibling);
                                            } else {
                                                infoTable.querySelector('tbody')?.appendChild(newRow);
                                            }
                                        }
                                    }

                                    // Afficher un message de succès
                                    const alertDiv = document.createElement('div');
                                    alertDiv.className = 'alert alert-success alert-dismissible fade show';
                                    alertDiv.innerHTML = `
                                <i class="fas fa-check-circle me-2"></i>
                                Notification marquée comme lue.
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            `;
                                    const container = document.querySelector('.container-fluid');
                                    if (container) {
                                        container.insertBefore(alertDiv, container.firstChild);
                                        // Masquer automatiquement après 3 secondes
                                        setTimeout(() => {
                                            alertDiv.remove();
                                        }, 3000);
                                    }
                                }
                            })
                            .catch(error => {
                                console.error('Erreur:', error);
                                btn.disabled = false;
                                btn.innerHTML = '<i class="fas fa-check me-2"></i>Marquer comme lu';
                                alert('Une erreur est survenue. Veuillez réessayer.');
                            });
                    });
                }
            });
        </script>
    @endpush
@endsection
