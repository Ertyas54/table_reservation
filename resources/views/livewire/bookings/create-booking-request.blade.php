@php use Carbon\Carbon; @endphp

<div>
    <div class="page-header">
        <div>
            <h1 class="page-header__title">
                Новая заявка — {{ $restaurant->name }}
            </h1>
            <p class="page-header__subtitle">
                Выберите столики (в одной зоне) и слоты
            </p>
        </div>
        <a href="{{ route('bookings.create', ['restaurant' => $restaurant->id, 'date' => $bookingDate]) }}"
           class="btn btn--primary">
            + Забронировать
        </a>
    </div>

    @if ($errorMessage)
        <div class="flash flash--error">{{ $errorMessage }}</div>
    @endif

    @if ($hasPending)
        <div class="flash flash--error">
            У вас уже есть заявка на эту дату в этом ресторане. Дождитесь её рассмотрения.
        </div>
    @else
        <div class="form-card" style="margin-bottom: 16px;">
            <div class="form-card__body">
                <div class="form-field">
                    <label class="form-field__label">Дата</label>
                    <input type="date"
                           wire:model.live="bookingDate"
                           min="{{ now()->toDateString() }}"
                           value="{{ $bookingDate }}"
                           class="form-field__input">
                </div>
            </div>
        </div>

        <div class="form-card" style="margin-bottom: 16px;">
            <div class="form-card__header">
                <h2 class="form-card__title">1. Выберите столики</h2>
                <p class="form-card__subtitle">
                    Только в одной зоне: {{ count($selectedTableIds) }} выбрано
                </p>
            </div>
            <div class="form-card__body">
                <div class="selection-grid">
                    @foreach ($tables as $table)
                        @php
                            $isSelected = in_array($table->id, $selectedTableIds);
                        @endphp
                        <button type="button"
                                wire:click="toggleTable({{ $table->id }})"
                                class="selection-item {{ $isSelected ? 'selection-item--active' : '' }}">
                            <strong>{{ $table->table_number }}</strong>
                            <small>{{ $table->location }}</small>
                        </button>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="form-card" style="margin-bottom: 16px;">
            <div class="form-card__header">
                <h2 class="form-card__title">2. Выберите слоты</h2>
                <p class="form-card__subtitle">
                    {{ count($selectedSlotIds) }} выбрано
                </p>
            </div>
            <div class="form-card__body">
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

        <div class="form-card" style="margin-bottom: 16px;">
            <div class="form-card__header">
                <h2 class="form-card__title">3. Комментарий (необязательно)</h2>
            </div>
            <div class="form-card__body">
                <textarea wire:model="comment"
                          rows="3"
                          placeholder="Например: день рождения, нужен детский стул"
                          class="form-field__textarea"></textarea>
            </div>
        </div>

        <div class="form-card">
            <div class="form-card__body">
                <div class="form-card__actions">
                    <button wire:click="submit"
                            wire:loading.attr="disabled"
                            class="btn btn--primary">
                        Отправить на одобрение
                    </button>
                    <a href="{{ route('tables.index', $restaurant) }}" class="btn btn--ghost">
                        Отмена
                    </a>
                </div>
            </div>
        </div>
    @endif
</div>
