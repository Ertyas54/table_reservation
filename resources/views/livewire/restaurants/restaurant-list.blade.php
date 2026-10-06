@php use Carbon\Carbon; @endphp

<div>
    <div class="page-header">
        <div>
            <h1 class="page-header__title">Рестораны</h1>
        </div>
        <a href="{{ route('restaurants.create') }}" class="btn btn--primary">
            + Добавить ресторан
        </a>
    </div>

    @if (session('message'))
        <div class="flash flash--success">{{ session('message') }}</div>
    @endif
    @if (session('error'))
        <div class="flash flash--error">{{ session('error') }}</div>
    @endif

    @if ($restaurants->isEmpty())
        <div class="empty-state">
            <div class="empty-state__title">Ресторанов пока нет</div>
            <div class="empty-state__text">Добавьте первый ресторан, чтобы начать</div>
        </div>
    @else
        <div class="restaurant-grid">
            @foreach ($restaurants as $restaurant)
                @php
                    $isDisabled = !$restaurant->is_active;
                @endphp
                <div class="restaurant-card" wire:key="restaurant-{{ $restaurant->id }}">
                    <div class="restaurant-card__header">
                        <h2 class="restaurant-card__title">
                            {{ $restaurant->name }}
                        </h2>

                        @if ($restaurant->is_active)
                            <span class="badge badge--active">
                                <span class="badge__dot"></span>
                                работает
                            </span>
                        @else
                            <span class="badge badge--inactive">
                                <span class="badge__dot"></span>
                                не работает
                            </span>
                        @endif
                    </div>

                    <div class="restaurant-card__row">
                        <span class="restaurant-card__label">Описание:</span>
                        <span>{{ $restaurant->description }}</span>
                    </div>

                    <div class="restaurant-card__row">
                        <span class="restaurant-card__label">Город:</span>
                        <span>{{ $restaurant->city }}</span>
                    </div>
                    <div class="restaurant-card__row">
                        <span class="restaurant-card__label">Адрес:</span>
                        <span>{{ $restaurant->address }}</span>
                    </div>

                    <div class="restaurant-card__row">
                        <span class="restaurant-card__label">Часы:</span>
                        <span>
                            {{ Carbon::parse($restaurant->opening_time)->format('H:i') }}
                            –
                            {{ Carbon::parse($restaurant->closing_time)->format('H:i') }}
                        </span>
                    </div>

                    @if ($restaurant->phone)
                        <div class="restaurant-card__row">
                            <span class="restaurant-card__label">Тел:</span>
                            <span>{{ $restaurant->phone }}</span>
                        </div>
                    @endif
                    @if ($restaurant->email)
                        <div class="restaurant-card__row">
                            <span class="restaurant-card__label">email:</span>
                            <span>{{ $restaurant->email }}</span>
                        </div>
                    @endif

                    <div class="restaurant-card__actions">
                        @if ($isDisabled)
                            <div class="restaurant-card__actions-primary">
                                <span class="slot slot--disabled" title="Недоступно">
                                        Забронировать столик
                                </span>
                            </div>
                        @else
                            <div class="restaurant-card__actions-primary">
                                <a href="{{ route('tables.index', $restaurant) }}" class="btn btn--ghost">
                                    Забронировать столик
                                </a>
                            </div>
                        @endif

                        <div class="restaurant-card__actions-secondary">
                            <a href="{{ route('restaurants.edit', $restaurant) }}" class="btn btn--ghost">
                                Редактировать
                            </a>
                            <button wire:click="delete({{ $restaurant->id }})"
                                    wire:confirm="Удалить ресторан?" class="btn btn--danger">
                                Удалить
                            </button>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
