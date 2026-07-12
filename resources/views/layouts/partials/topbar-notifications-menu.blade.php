<ul class="dropdown-menu dropdown-menu-end wmc-topbar-dropdown"
    style="min-width: min(300px, calc(100vw - 24px)); max-width: calc(100vw - 24px); max-height: 400px; overflow-y: auto;">
    <li>
        <h6 class="dropdown-header d-flex justify-content-between align-items-center">
            <span>Notifications</span>
            @if ($notificationCount > 0)
                <small class="text-muted">{{ $notificationCount }} non lue(s)</small>
            @endif
        </h6>
    </li>

    @forelse($notifications as $notification)
        @php
            $isSupport = $notification->module === 'support' && isset($notification->data['ticket_id']);
            $notificationUrl = null;
            if ($isSupport) {
                if (auth()->user()->isAdmin() && !auth()->user()->isOwner()) {
                    $notificationUrl = route('admin.support.show', $notification->data['ticket_id']);
                } elseif (auth()->user()->isOwner()) {
                    $notificationUrl = route('tickets.show', $notification->data['ticket_id']);
                }
            } else {
                $notificationUrl = auth()->user()->isAdmin()
                    ? route('admin.notifications.show', $notification)
                    : route('notifications.show', $notification);
            }
        @endphp
        <li>
            <a class="dropdown-item {{ !$notification->lue ? 'fw-bold' : '' }}" href="{{ $notificationUrl }}"
                onclick="markAsRead({{ $notification->id }}); return true;">
                <div class="d-flex align-items-start">
                    <div class="me-2">
                        <i class="{{ $notification->icone }} text-{{ $notification->couleur }}"></i>
                    </div>
                    <div class="flex-grow-1 min-w-0">
                        <div class="fw-bold small">{{ $notification->titre }}</div>
                        <div class="text-muted small">
                            <x-notification-message :message="$notification->message" :module="$notification->module" :limit="50" />
                        </div>
                        <div class="text-muted" style="font-size: 0.75rem;">
                            {{ $notification->created_at->diffForHumans() }}
                        </div>
                    </div>
                    @if (!$notification->lue)
                        <div class="ms-2">
                            <span class="badge bg-primary rounded-pill" style="width: 8px; height: 8px;"></span>
                        </div>
                    @endif
                </div>
            </a>
        </li>
    @empty
        <li>
            <div class="dropdown-item text-center text-muted">
                <i class="fas fa-bell-slash me-2"></i>Aucune notification
            </div>
        </li>
    @endforelse

    @if ($notifications->count() > 0)
        <li><hr class="dropdown-divider"></li>
        <li>
            <a class="dropdown-item text-center"
                href="{{ auth()->user()->isAdmin() ? route('admin.notifications.index') : route('notifications.index') }}">
                <i class="fas fa-list me-2"></i>Voir toutes les notifications
            </a>
        </li>
    @endif
</ul>
