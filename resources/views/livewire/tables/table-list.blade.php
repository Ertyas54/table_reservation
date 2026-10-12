@php use Carbon\Carbon; @endphp

<div>
    <div class="page-header">
        <div>
            <h1 class="page-header__title">
                Столики ресторана «{{ $restaurant->name }}»
            </h1>
            <p class="page-header__subtitle">
                Выберите дату и забронируйте столик
            </p>
        </div>

        <div class="page-header__actions">
            <label class="date-field">
                <span>Дата:</span>
                <input type="date"
                       wire:model.live="bookingDate"
                       value="{{ $bookingDate }}">
            </label>

            <a href="{{ route('tables.create', $restaurant) }}" class="btn btn--primary">
                + Добавить столик
            </a>
        </div>
    </div>

    @if (session('message'))
        <div class="flash flash--success">{{ session('message') }}</div>
    @endif
    @if (session('error'))
        <div class="flash flash--error">{{ session('error') }}</div>
    @endif

    @forelse ($tables as $table)
        <div class="table-card" wire:key="table-{{ $table->id }}">
            <div class="table-card__header">
                <div class="table-card__info">
                    <div class="table-card__number">
                        {{ $table->table_number }}
                    </div>
                    <div>
                        <div class="table-card__name">
                            Столик {{ $table->table_number }}
                        </div>
                        <div class="table-card__zone">
                            {{ $table->location }}
                        </div>
                    </div>

                    @if ($table->is_active)
                        <span class="badge badge--active">
                            <span class="badge__dot"></span>
                            активен
                        </span>
                    @else
                        <span class="badge badge--inactive">
                            <span class="badge__dot"></span>
                            не активен
                        </span>
                    @endif
                </div>

                <div class="table-card__actions">
                    <a href="{{ route('tables.edit', [$restaurant, $table]) }}"
                       class="btn btn--ghost">
                        Редактировать
                    </a>
                    <button wire:click="delete({{ $table->id }})"
                            wire:confirm="Удалить столик?"
                            class="btn btn--danger">
                        Удалить
                    </button>
                </div>
            </div>

            <div class="table-card__slots">
                <div class="table-card__slots-title">
                    Слоты на {{ $bookingDate }}
                </div>

                <div class="slots">
                    @forelse ($timeSlots as $timeSlot)
                        @php
                            $key = $table->id . '-' . $timeSlot->id;
                            $booking = $bookings->get($key);
                            $isMine = $booking && $booking->user_id === auth()->id();
                            $time = Carbon::parse($timeSlot->start_time)->format('H:i')
                                  . '–'
                                  . Carbon::parse($timeSlot->end_time)->format('H:i');
                            $isDisabled = !$table->is_active || !$timeSlot->is_active;
                        @endphp

                        <span wire:key="cell-{{ $key }}">
                            @if ($isDisabled)
                                <span class="slot slot--disabled" title="Недоступно">
                                    {{ $time }}
                                </span>
                            @elseif (!$booking)
                                <button wire:click="book({{ $table->id }}, {{ $timeSlot->id }})"
                                        class="slot">
                                    {{ $time }}
                                </button>
                            @elseif ($isMine)
                                <button wire:click="cancel({{ $table->id }}, {{ $timeSlot->id }})"
                                        wire:confirm="Отменить бронь?"
                                        class="slot slot--mine">
                                    {{ $time }}
                                    <span class="slot__mine-label">(моя)</span>
                                </button>
                            @else
                                <span class="slot slot--taken">
                                    {{ $time }}
                                </span>
                            @endif
                        </span>
                    @empty
                        <span class="slots-empty">Слотов нет</span>
                    @endforelse
                </div>
            </div>
        </div>
    @empty
        <div class="empty-state">
            <div class="empty-state__title">Столиков пока нет</div>
            <div class="empty-state__text">Добавьте первый столик, чтобы начать</div>
            <a href="{{ route('tables.create', $restaurant) }}" class="btn btn--primary">
                + Добавить столик
            </a>
        </div>
    @endforelse
</div>
