<div class="container">
    <div class="page-header">
        <div>
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
        <input type="text"
               wire:model.live.debounce.300ms="search"
               placeholder="Имя, фамилия или email..."
               class="filters__input">

        <select wire:model.live="role" class="filters__select">
            <option value="">Все роли</option>
            <option value="admin">Админы</option>
            <option value="client">Клиенты</option>
        </select>
    </div>

    @if($users->isEmpty())
        <div class="empty-state">
            <div class="empty-state__title">Пользователи не найдены</div>
            <div class="empty-state__text">Попробуйте изменить фильтры или поиск</div>
        </div>
    @else
        <div class="users-grid">
            @foreach($users as $user)
                <div wire:key="user-{{ $user->id }}" class="user-card">
                    {{-- Видимая часть --}}
                    <div class="user-card__main">
                        <div class="user-card__avatar {{ $user->role === 'admin' ? 'user-card__avatar--admin' : 'user-card__avatar--client' }}">
                            {{ mb_strtoupper(mb_substr($user->first_name, 0, 1) . mb_substr($user->last_name, 0, 1)) }}
                        </div>
                        <div class="user-card__info">
                            <div class="user-card__name">{{ $user->first_name }} {{ $user->last_name }}</div>
                            <div class="user-card__id">ID: <span class="user-card__id-value">#{{ $user->id }}</span></div>
                        </div>
                    </div>

                    {{-- Панель при hover --}}
                    <div class="user-card__overlay">
                        <div>
                            <div class="user-card__overlay-name">{{ $user->first_name }} {{ $user->last_name }}</div>

                            <div class="user-card__row">
                                <span class="user-card__label">Email:</span>
                                <span class="user-card__value">{{ $user->email }}</span>
                            </div>

                            <div class="user-card__row">
                                <span class="user-card__label">Тел:</span>
                                <span class="user-card__value">{{ $user->phone ?? '—' }}</span>
                            </div>

                            <div class="user-card__row">
                                <span class="user-card__label">Роль:</span>
                                <span class="user-role-badge {{ $user->role === 'admin' ? 'user-role-badge--admin' : 'user-role-badge--client' }}">
                                    {{ $user->role }}
                                </span>
                            </div>
                        </div>

                        <div class="user-card__actions">
                            <a href="{{ route('users.edit', $user) }}" class="btn btn--primary">
                                Редактировать
                            </a>

                            @if($user->id !== auth()->id())
                                @if($user->bookings_count > 0)
                                    <span class="user-card__blocked" title="Есть активные брони — удаление запрещено">
                                        Бронь
                                    </span>
                                @else
                                    <button wire:click="delete({{ $user->id }})"
                                            wire:confirm="Удалить пользователя {{ $user->email }}?"
                                            class="btn btn--danger">
                                        Удалить
                                    </button>
                                @endif
                            @else
                                <span class="user-card__blocked">Это вы</span>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div>
            {{ $users->links() }}
        </div>
    @endif
</div>
