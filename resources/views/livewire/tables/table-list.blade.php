@php use Carbon\Carbon; @endphp

<div wire:poll.60s>
    <div class="page">
        <div class="page-header">
            <div>
                <p class="page-header__eyebrow">Столики</p>
                <h1 class="page-header__title">
                    Столики ресторана «{{ $restaurant->name }}»
                </h1>
            </div>

            <div class="page-header__actions">
                @auth
                    <a href="{{ route('bookings.create', ['restaurant' => $restaurant->id, 'date' => $bookingDate]) }}"
                       class="btn btn--primary">
                        <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"/></svg>
                        Забронировать
                    </a>
                @endauth

                @if(auth()->user()->role === 'admin')
                    <a href="{{ route('tables.create', $restaurant) }}" class="btn btn--ghost">
                        <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"/></svg>
                        Добавить столик
                    </a>
                @endif
            </div>
        </div>

        <div class="toolbar">
            <div class="toolbar__group">
                <label class="date-field">
                    <span>Дата:</span>
                    <input type="date"
                           wire:model.live="bookingDate"
                           value="{{ $bookingDate }}"
                           min="{{ now()->toDateString() }}">
                </label>
            </div>
            <div class="legend">
                <span class="legend__item"><span class="legend__swatch legend__swatch--free"></span>свободно</span>
                <span class="legend__item"><span class="legend__swatch legend__swatch--taken"></span>занято</span>
                <span class="legend__item"><span class="legend__swatch legend__swatch--off"></span>недоступно</span>
            </div>
        </div>

        @if (session('message'))
            <div class="flash flash--success">{{ session('message') }}</div>
        @endif
        @if (session('error'))
            <div class="flash flash--error">{{ session('error') }}</div>
        @endif

        {{-- Компактная сетка карточек --}}
        <div class="tables-grid">
            @forelse ($tables as $table)
                <div class="table-card table-card--compact" wire:key="table-{{ $table->id }}">
                    {{-- Шапка: номер + зона + статус + действия --}}
                    <div class="table-card__head">
                        <div class="table-card__head-left">
                            <div class="table-card__number">{{ $table->table_number }}</div>
                            <div>
                                <div class="table-card__name">Столик {{ $table->table_number }}</div>
                                <div class="table-card__zone">{{ $table->location }}</div>
                            </div>
                        </div>

                        <div class="table-card__head-right">
                            @if ($table->is_active)
                                <span class="badge badge--active badge--sm">
                                    <span class="badge__dot"></span>
                                </span>
                            @else
                                <span class="badge badge--inactive badge--sm">
                                    <span class="badge__dot"></span>
                                </span>
                            @endif

                            @if(auth()->user()->role === 'admin')
                                <div class="table-card__actions">
                                    <a href="{{ route('tables.edit', [$restaurant, $table]) }}" class="icon-btn icon-btn--sm" title="Редактировать">
                                        <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg>
                                    </a>
                                    <button wire:click="delete({{ $table->id }})"
                                            wire:confirm="Удалить столик?"
                                            class="icon-btn icon-btn--sm icon-btn--danger"
                                            title="Удалить">
                                        <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M8 6V4a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2"/><path d="m19 6-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6M14 11v6"/></svg>
                                    </button>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Слоты сразу под шапкой --}}
                    <div class="slots slots--compact">
                        @forelse ($timeSlots as $timeSlot)
                            @php
                                $key = $table->id . '-' . $timeSlot->id;
                                $booking = $bookings->get($key);
                                $time = Carbon::parse($timeSlot->start_time)->format('H:i')
                                      . '–'
                                      . Carbon::parse($timeSlot->end_time)->format('H:i');
                                $isPast = $isToday && $pastSlots->contains($timeSlot->id);
                                $isDisabled = !$table->is_active || !$timeSlot->is_active || $isPast;
                            @endphp

                            <span wire:key="cell-{{ $key }}">
                                @if ($isDisabled)
                                    <span class="slot slot--disabled slot--sm" title="{{ $isPast ? 'Время прошло' : 'Недоступно' }}">
                                        {{ $time }}
                                    </span>
                                @elseif ($booking)
                                    <span class="slot slot--taken slot--sm" title="Забронировано">
                                        {{ $time }}
                                    </span>
                                @else
                                    <span class="slot slot--free slot--sm" title="Свободно">
                                        {{ $time }}
                                    </span>
                                @endif
                            </span>
                        @empty
                            <span class="slots-empty">Слотов нет</span>
                        @endforelse
                    </div>
                </div>
            @empty
                <div class="empty-state" style="grid-column: 1 / -1;">
                    <div class="empty-state__icon">
                        <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 9V6a2 2 0 0 0-2-2H7a2 2 0 0 0-2 2v3"/><path d="M3 11a2 2 0 0 1 2-1h14a2 2 0 0 1 2 1v2a2 2 0 0 1-2 1H5a2 2 0 0 1-2-1Z"/><path d="M5 15v6M19 15v6"/></svg>
                    </div>
                    <div class="empty-state__title">Столиков пока нет</div>
                    @if(auth()->user()->role === 'admin')
                        <div class="empty-state__text">Добавьте первый столик, чтобы начать</div>
                        <a href="{{ route('tables.create', $restaurant) }}" class="btn btn--primary">
                            <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"/></svg>
                            Добавить столик
                        </a>
                    @endif
                </div>
            @endforelse
        </div>
    </div>
</div>
