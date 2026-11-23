@php use Carbon\Carbon; @endphp

<div>
    <div class="page">
        <div class="page-header">
            <div>
                <p class="page-header__eyebrow">Заведения</p>
                <h1 class="page-header__title">Рестораны</h1>
                <p class="page-header__subtitle">Выберите заведение и забронируйте столик</p>
            </div>
            @if(auth()->user()->role === 'admin')
                <div class="page-header__actions">
                    <a href="{{ route('restaurants.create') }}" class="btn btn--primary">
                        <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"/></svg>
                        Добавить ресторан
                    </a>
                </div>
            @endif
        </div>

        @if (session('message'))
            <div class="flash flash--success">{{ session('message') }}</div>
        @endif
        @if (session('error'))
            <div class="flash flash--error">{{ session('error') }}</div>
        @endif

        @if ($restaurants->isEmpty())
            <div class="empty-state">
                <div class="empty-state__icon">
                    <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 2v7c0 1.1.9 2 2 2h4a2 2 0 0 0 2-2V2"/><path d="M7 2v20"/><path d="M21 15V2a5 5 0 0 0-5 5v6c0 1.1.9 2 2 2h3Zm0 0v7"/></svg>
                </div>
                <div class="empty-state__title">Ресторанов пока нет</div>
                <div class="empty-state__text">Добавьте первый ресторан, чтобы начать</div>
            </div>
        @else
            <div class="restaurant-grid">
                @foreach ($restaurants as $restaurant)
                    @php
                        $isDisabled = !$restaurant->is_active;
                    @endphp
                    <article class="restaurant-card" wire:key="restaurant-{{ $restaurant->id }}">
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

                        @if ($restaurant->description)
                            <p class="restaurant-card__description">{{ $restaurant->description }}</p>
                        @endif

                        <div class="restaurant-card__meta">
                            {{-- Город --}}
                            <div class="meta-item">
    <span class="meta-item__icon">
        <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 21h18"/><path d="M9 8h1"/><path d="M9 12h1"/><path d="M9 16h1"/><path d="M14 8h1"/><path d="M14 12h1"/><path d="M14 16h1"/><path d="M5 21V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v16"/></svg>
    </span>
                                <div class="meta-item__body">
                                    <div class="meta-item__label">Город</div>
                                    <div class="meta-item__value">{{ $restaurant->city }}</div>
                                </div>
                            </div>

                            {{-- Адрес (улица, дом) --}}
                            <div class="meta-item">
    <span class="meta-item__icon">
        <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>
    </span>
                                <div class="meta-item__body">
                                    <div class="meta-item__label">Адрес</div>
                                    <div class="meta-item__value">{{ $restaurant->address }}</div>
                                </div>
                            </div>

                            <div class="meta-item">
                                <span class="meta-item__icon">
                                    <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
                                </span>
                                <div class="meta-item__body">
                                    <div class="meta-item__label">Часы</div>
                                    <div class="meta-item__value">
                                        {{ Carbon::parse($restaurant->opening_time)->format('H:i') }}
                                        –
                                        {{ Carbon::parse($restaurant->closing_time)->format('H:i') }}
                                    </div>
                                </div>
                            </div>

                            @if ($restaurant->phone)
                                <div class="meta-item">
                                    <span class="meta-item__icon">
                                        <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92Z"/></svg>
                                    </span>
                                    <div class="meta-item__body">
                                        <div class="meta-item__label">Телефон</div>
                                        <div class="meta-item__value">{{ $restaurant->phone }}</div>
                                    </div>
                                </div>
                            @endif

                            @if ($restaurant->email)
                                <div class="meta-item meta-item--wide">
                                    <span class="meta-item__icon">
                                        <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 7-10 6L2 7"/></svg>
                                    </span>
                                    <div class="meta-item__body">
                                        <div class="meta-item__label">Почта</div>
                                        <div class="meta-item__value">{{ $restaurant->email }}</div>
                                    </div>
                                </div>
                            @endif
                        </div>

                        <div class="restaurant-card__footer">
                            @if ($isDisabled)
                                <span class="slot slot--disabled" title="Недоступно">
                                    Забронировать столик
                                </span>
                            @else
                                <a href="{{ route('tables.index', $restaurant) }}" class="btn btn--primary">
                                    <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
                                    Забронировать столик
                                </a>
                            @endif

                            @if(auth()->user()->role === 'admin')
                                <a href="{{ route('restaurants.edit', $restaurant) }}" class="icon-btn" title="Редактировать">
                                    <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg>
                                </a>
                                <button wire:click="delete({{ $restaurant->id }})"
                                        wire:confirm="Удалить ресторан?"
                                        class="icon-btn icon-btn--danger"
                                        title="Удалить">
                                    <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M8 6V4a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2"/><path d="m19 6-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6M14 11v6"/></svg>
                                </button>
                            @endif
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </div>
</div>
