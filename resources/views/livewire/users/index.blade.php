<div class="page">
    <div class="page-header">
        <div>
            <p class="page-header__eyebrow">Администрирование</p>
            <h1 class="page-header__title">Пользователи</h1>
            <p class="page-header__subtitle">Всего: {{ $total }}</p>
        </div>
    </div>

    @if(session('message'))
        <div class="flash flash--success">{{ session('message') }}</div>
    @endif
    @if(session('error'))
        <div class="flash flash--error">{{ session('error') }}</div>
    @endif

    <div class="filters">
        <div class="toolbar__search">
            <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
            <input type="text"
                   wire:model.live.debounce.300ms="search"
                   placeholder="Имя, фамилия или email..."
                   class="filters__input">
        </div>

        <select wire:model.live="role" class="filters__select">
            <option value="">Все роли</option>
            <option value="admin">Админы</option>
            <option value="client">Клиенты</option>
        </select>

        <span class="filters__count">Показано: <b>{{ $users->count() }}</b> из {{ $total }}</span>
    </div>

    @if($users->isEmpty())
        <div class="empty-state">
            <div class="empty-state__icon">
                <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            </div>
            <div class="empty-state__title">Пользователи не найдены</div>
            <div class="empty-state__text">Попробуйте изменить фильтры или поиск</div>
        </div>
    @else
        <div class="users-grid">
            @foreach($users as $user)
                <article wire:key="user-{{ $user->id }}" class="user-card">

                    {{-- Шапка: аватар, имя, роль, кнопки действий --}}
                    <div class="user-card__head">
                        <div class="user-card__head-left">
                            <div class="user-card__avatar user-card__avatar--{{ $user->role }}">
                                {{ mb_strtoupper(mb_substr($user->first_name, 0, 1) . mb_substr($user->last_name, 0, 1)) }}
                            </div>
                            <div>
                                <div class="user-card__name">{{ $user->first_name }} {{ $user->last_name }}</div>
                                <div class="user-card__role">
                                    <span class="user-card__role-dot user-card__role-dot--{{ $user->role }}"></span>
                                    {{ $user->role === 'admin' ? 'Администратор' : 'Клиент' }}
                                </div>
                            </div>
                        </div>

                        <div class="user-card__head-right">
                            <a href="{{ route('users.edit', $user) }}" class="icon-btn icon-btn--sm" title="Редактировать">
                                <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg>
                            </a>

                            @if($user->id !== auth()->id())
                                @if($user->bookings_count > 0)
                                    <span class="icon-btn icon-btn--sm" style="color: var(--ink-3); cursor: not-allowed;" title="Есть брони — удаление запрещено">
                                        <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                                    </span>
                                @else
                                    <button wire:click="delete({{ $user->id }})"
                                            wire:confirm="Удалить пользователя {{ $user->email }}?"
                                            class="icon-btn icon-btn--sm icon-btn--danger"
                                            title="Удалить">
                                        <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M8 6V4a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2"/><path d="m19 6-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6M14 11v6"/></svg>
                                    </button>
                                @endif
                            @else
                                <span class="icon-btn icon-btn--sm" style="color: var(--ink-3); cursor: default;" title="Это вы">
                                    <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                                </span>
                            @endif
                        </div>
                    </div>

                    {{-- Тело: контакты --}}
                    <div class="user-card__body">
                        <div class="user-card__row">
                            <svg class="user-card__icon" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 7-10 6L2 7"/></svg>
                            <div style="min-width: 0;">
                                <div class="user-card__label">Почта</div>
                                <div class="user-card__value">{{ $user->email }}</div>
                            </div>
                        </div>

                        <div class="user-card__row">
                            <svg class="user-card__icon" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92Z"/></svg>
                            <div style="min-width: 0;">
                                <div class="user-card__label">Телефон</div>
                                <div class="user-card__value">{{ $user->phone ?? '—' }}</div>
                            </div>
                        </div>

                        <div class="user-card__row">
                            <svg class="user-card__icon" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                            <div style="min-width: 0;">
                                <div class="user-card__label">ID в системе</div>
                                <div class="user-card__value user-card__value--id">#{{ $user->id }}</div>
                            </div>
                        </div>
                    </div>

                </article>
            @endforeach
        </div>

        <div class="list-footer">
            {{ $users->links() }}
        </div>
    @endif
</div>
