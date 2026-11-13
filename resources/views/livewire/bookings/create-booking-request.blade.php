@php use Carbon\Carbon; @endphp

<div>
    <div class="page">
        <div class="page-header">
            <div>
                <p class="page-header__eyebrow">Бронирование</p>
                <h1 class="page-header__title">
                    Новая заявка — {{ $restaurant->name }}
                </h1>
                <p class="page-header__subtitle">
                    Выберите столики (в одной зоне) и слоты
                </p>
            </div>
        </div>

        @if ($errorMessage)
            <div class="flash flash--error">{{ $errorMessage }}</div>
        @endif

        @if ($hasPending)
            <div class="flash flash--error">
                У вас уже есть заявка на эту дату в этом ресторане. Дождитесь её рассмотрения.
            </div>
        @else
            <div class="form-card form-card--steps">
                <div class="form-section">
                    <h2 class="form-section__title">
                        <span class="form-section__num">1</span>Дата визита
                    </h2>
                    <div class="form-section__body">
                        <div class="inline-field">
                            <label class="form-field__label" for="booking-date">Дата</label>
                            <input type="date"
                                   id="booking-date"
                                   wire:model.live="bookingDate"
                                   min="{{ now()->toDateString() }}"
                                   value="{{ $bookingDate }}"
                                   class="form-field__input">
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <h2 class="form-section__title">
                        <span class="form-section__num">2</span>Столики
                        <span class="form-section__hint">одна зона · выбрано: <b>{{ count($selectedTableIds) }}</b></span>
                    </h2>
                    <div class="form-section__body">
                        @foreach ($tables->groupBy('location') as $location => $locationTables)
                            <div class="selection-group">
                                <div class="selection-group__title">{{ $location }}</div>
                                <div class="selection-grid">
                                    @foreach ($locationTables as $table)
                                        @php
                                            $isSelected = in_array($table->id, $selectedTableIds);
                                        @endphp
                                        <button type="button"
                                                wire:click="toggleTable({{ $table->id }})"
                                                class="selection-item {{ $isSelected ? 'selection-item--active' : '' }}">
                                            <strong>{{ $table->table_number }}</strong>
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="form-section">
                    <h2 class="form-section__title">
                        <span class="form-section__num">3</span>Слоты
                        <span class="form-section__hint">выбрано: <b>{{ count($selectedSlotIds) }}</b></span>
                    </h2>
                    <div class="form-section__body">
                        <div class="selection-grid">
                            @foreach ($timeSlots as $slot)
                                @php
                                    $isSelected = in_array($slot->id, $selectedSlotIds);
                                    $isPast = in_array($slot->id, $pastSlotIds);
                                    $time = Carbon::parse($slot->start_time)->format('H:i')
                                          . '–'
                                          . Carbon::parse($slot->end_time)->format('H:i');
                                @endphp
                                <button type="button"
                                        wire:click="toggleSlot({{ $slot->id }})"
                                        @if ($isPast) disabled @endif
                                        class="selection-item {{ $isSelected ? 'selection-item--active' : '' }} {{ $isPast ? 'selection-item--disabled' : '' }}">
                                    {{ $time }}
                                </button>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <h2 class="form-section__title">
                        <span class="form-section__num">4</span>Комментарий
                        <span class="form-section__hint">необязательно</span>
                    </h2>
                    <div class="form-section__body">
                        <textarea wire:model="comment"
                                  rows="3"
                                  placeholder="Например: день рождения, нужен детский стул"
                                  class="form-field__textarea"></textarea>
                    </div>
                </div>

                <div class="form-card__footer">
                    <div class="form-card__summary">
                        Выбрано: <b>{{ count($selectedTableIds) }}</b> столиков · <b>{{ count($selectedSlotIds) }}</b> слотов
                    </div>
                    <a href="{{ route('tables.index', $restaurant) }}" class="btn btn--ghost">
                        Отмена
                    </a>
                    <button wire:click="submit"
                            wire:loading.attr="disabled"
                            class="btn btn--primary">
                        <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m22 2-7 20-4-9-9-4Z"/><path d="M22 2 11 13"/></svg>
                        Отправить на одобрение
                    </button>
                </div>
            </div>
        @endif
    </div>
</div>
