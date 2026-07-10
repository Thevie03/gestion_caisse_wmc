@extends('layouts.app')

@section('page-title', 'Détail de la notification')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">
                            <i class="{{ $notification->icone }} text-{{ $notification->couleur }} me-2"></i>
                            {{ $notification->titre }}
                        </h5>
                        <div class="d-flex align-items-center">
                            <span class="badge bg-{{ $notification->couleur }} me-2">
                                {{ ucfirst($notification->type) }}
                            </span>
                            <span class="badge bg-secondary">
                                {{ ucfirst($notification->priorite) }}
                            </span>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-8">
                                <h6>Message :</h6>
                                <p class="text-muted">
                                    <x-notification-message
                                        :message="$notification->message"
                                        :module="$notification->module"
                                    />
                                </p>

                                @if ($notification->data)
                                    <h6 class="mt-4">Détails supplémentaires :</h6>
                                    <div class="table-responsive">
                                        <table class="table table-sm">
                                            <tbody>
                                                @foreach ($notification->data as $key => $value)
                                                    <tr>
                                                        <td class="fw-bold">{{ ucfirst(str_replace('_', ' ', $key)) }}</td>
                                                        <td>{{ $value }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @endif
                            </div>
                            <div class="col-md-4">
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <h6>Informations</h6>
                                        <ul class="list-unstyled mb-0">
                                            <li class="mb-2">
                                                <i class="fas fa-calendar me-2 text-muted"></i>
                                                <strong>Date :</strong> {{ $notification->created_at->format('d/m/Y H:i') }}
                                            </li>
                                            <li class="mb-2">
                                                <i class="fas fa-clock me-2 text-muted"></i>
                                                <strong>Il y a :</strong> {{ $notification->created_at->diffForHumans() }}
                                            </li>
                                            @if ($notification->lue)
                                                <li class="mb-2">
                                                    <i class="fas fa-check-circle me-2 text-success"></i>
                                                    <strong>Lue le :</strong>
                                                    {{ $notification->lue_at->format('d/m/Y H:i') }}
                                                </li>
                                            @endif
                                            @if ($notification->module)
                                                <li class="mb-2">
                                                    <i class="fas fa-puzzle-piece me-2 text-muted"></i>
                                                    <strong>Module :</strong> {{ ucfirst($notification->module) }}
                                                </li>
                                            @endif
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('notifications.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-2"></i>
                                Retour à la liste
                            </a>
                            <div>
                                @if (!$notification->lue)
                                    <button type="button" class="btn btn-primary mark-as-read-btn"
                                        data-notification-id="{{ $notification->id }}"
                                        data-url="{{ route('notifications.mark-read', $notification) }}">
                                        <i class="fas fa-check me-2"></i>
                                        Marquer comme lue
                                    </button>
                                @endif
                            </div>
                        </div>
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

                                    // Ajouter l'information "Lue le" dans la section informations
                                    const infoList = document.querySelector('.list-unstyled');
                                    if (infoList) {
                                        const lueItem = Array.from(infoList.querySelectorAll('li')).find(
                                            li => li.textContent.includes('Lue le'));
                                        if (!lueItem) {
                                            const newItem = document.createElement('li');
                                            newItem.className = 'mb-2';
                                            const now = new Date();
                                            const dateStr = now.toLocaleDateString('fr-FR') + ' ' + now
                                                .toLocaleTimeString('fr-FR', {
                                                    hour: '2-digit',
                                                    minute: '2-digit'
                                                });
                                            newItem.innerHTML = `
                                        <i class="fas fa-check-circle me-2 text-success"></i>
                                        <strong>Lue le :</strong> ${dateStr}
                                    `;
                                            // Insérer après la ligne "Il y a"
                                            const ilYaItem = Array.from(infoList.querySelectorAll('li'))
                                                .find(li => li.textContent.includes('Il y a'));
                                            if (ilYaItem) {
                                                ilYaItem.parentNode.insertBefore(newItem, ilYaItem
                                                    .nextSibling);
                                            } else {
                                                infoList.appendChild(newItem);
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
                                btn.innerHTML = '<i class="fas fa-check me-2"></i>Marquer comme lue';
                                alert('Une erreur est survenue. Veuillez réessayer.');
                            });
                    });
                }
            });
        </script>
    @endpush
@endsection
