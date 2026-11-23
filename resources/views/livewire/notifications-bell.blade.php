<div class="notif-bell" wire:poll.30s="loadNotifications">
    <button class="notif-bell__trigger" aria-label="Уведомления">
        <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
            <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
        </svg>

        @if ($unreadCount > 0)
            <span class="notif-bell__badge">{{ $unreadCount }}</span>
        @endif
    </button>

    <div class="notif-dropdown">
        <div class="notif-dropdown__header">
            <span class="notif-dropdown__title">Уведомления</span>
            @if ($unreadCount > 0)
                <button wire:click="markAllAsRead" class="notif-dropdown__mark-all">
                    Прочитать все
                </button>
            @endif
        </div>

        <div class="notif-dropdown__list">
            @forelse ($notifications as $notification)
                <div class="notif-item {{ $notification['read_at'] ? 'notif-item--read' : 'notif-item--unread' }}"
                     wire:key="notif-{{ $notification['id'] }}">
                    <div class="notif-item__body">
                        <div class="notif-item__message">
                            {{ $notification['data']['message'] ?? 'Уведомление' }}
                        </div>
                        <div class="notif-item__meta">
                            @if (!empty($notification['data']['restaurant']))
                                {{ $notification['data']['restaurant'] }}
                            @endif
                            @if (!empty($notification['data']['date']))
                                · {{ $notification['data']['date'] }}
                            @endif
                            @if (!empty($notification['data']['time']))
                                · {{ $notification['data']['time'] }}
                            @endif
                        </div>
                        @if (!empty($notification['data']['reason']))
                            <div class="notif-item__reason">
                                Причина: {{ $notification['data']['reason'] }}
                            </div>
                        @endif
                    </div>

                    @if (! $notification['read_at'])
                        <button wire:click="markAsRead('{{ $notification['id'] }}')"
                                class="notif-item__mark"
                                title="Отметить как прочитанное">
                            ✕
                        </button>
                    @endif
                </div>
            @empty
                <div class="notif-dropdown__empty">Уведомлений пока нет</div>
            @endforelse
        </div>
    </div>
</div>
