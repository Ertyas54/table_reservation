@php use Carbon\Carbon; @endphp

<div>
    <div class="page-header">
        <div>
            <h1 class="page-header__title">Заявки на бронирование</h1>
            <p class="page-header__subtitle">Одобрение и отклонение заявок</p>
        </div>
    </div>

    @if (session('message'))
        <div class="flash flash--success">{{ session('message') }}</div>
    @endif
    @if (session('error'))
        <div class="flash flash--error">{{ session('error') }}</div>
    @endif

    <div class="form-card" style="margin-bottom: 16px;">
        <div class="form-card__body">
            <div class="form-field">
                <label class="form-field__label">Статус</label>
                <select wire:model.live="statusFilter" class="form-field__select">
                    <option value="pending">На модерации</option>
                    <option value="approved">Одобрено</option>
                    <option value="rejected">Отклонено</option>
                    <option value="cancelled">Отменено</option>
                    <option value="">Все</option>
                </select>
            </div>
        </div>
    </div>

    @if ($groups->isEmpty())
        <div class="empty-state">
            <div class="empty-state__title">Заявок нет</div>
        </div>
    @else
        @foreach ($groups as $groupId => $items)
            @php
                $first = $items->first();
                $bySlot = $items->groupBy('slot_id');
                $tables = $items->pluck('table')->unique('id');
                $status = $first->status;
            @endphp

            <div class="request-card">
                <div class="request-card__header">
                    <div>
                        <h2 class="request-card__title">
                            {{ $first->restaurant->name }}
                        </h2>
                        <p class="request-card__subtitle">
                            {{ Carbon::parse($first->booking_date)->format('d.m.Y') }}
                            · пользователь: {{ $first->user->first_name ?? $first->user->email }}
                        </p>
                    </div>

                    @if ($status === 'pending')
                        <span class="badge badge--pending">На модерации</span>
                    @elseif ($status === 'approved')
                        <span class="badge badge--active">Одобрено</span>
                    @elseif ($status === 'rejected')
                        <span class="badge badge--rejected">Отклонено</span>
                    @elseif ($status === 'cancelled')
                        <span class="badge badge--inactive">Отменено</span>
                    @endif
                </div>

                <div class="request-card__body">
                    <div class="request-card__row">
                        <span class="request-card__label">Столики:</span>
                        <span>
                            @foreach ($tables as $table)
                                <span class="badge badge--zone">{{ $table->table_number }} ({{ $table->location }})</span>
                            @endforeach
                        </span>
                    </div>

                    <div class="request-card__row">
                        <span class="request-card__label">Слоты:</span>
                        <span>
                            @foreach ($bySlot as $slotId => $slotItems)
                                @php $slot = $slotItems->first()->slot; @endphp
                                <span class="badge badge--zone">
                                    {{ Carbon::parse($slot->start_time)->format('H:i') }}–{{ Carbon::parse($slot->end_time)->format('H:i') }}
                                </span>
                            @endforeach
                        </span>
                    </div>

                    @if ($first->comment)
                        <div class="request-card__row">
                            <span class="request-card__label">Комментарий:</span>
                            <span>{{ $first->comment }}</span>
                        </div>
                    @endif
                </div>

                @if ($status === 'pending')
                    <div class="request-card__footer">
                        <button wire:click="approveGroup('{{ $groupId }}')"
                                class="btn btn--primary">
                            Одобрить
                        </button>
                        <button wire:click="openRejectModal('{{ $groupId }}')"
                                class="btn btn--danger">
                            Отклонить
                        </button>
                    </div>
                @endif
            </div>
        @endforeach
    @endif

    @if ($rejectingGroupId)
        <div class="modal-backdrop">
            <div class="modal">
                <h3>Причина отказа</h3>
                <textarea wire:model="rejectReason" rows="4"
                          placeholder="Например: все столики заняты"
                          class="form-field__textarea"></textarea>
                <div class="form-card__actions" style="margin-top: 16px;">
                    <button wire:click="confirmReject" class="btn btn--danger">
                        Отклонить
                    </button>
                    <button wire:click="closeRejectModal" class="btn btn--ghost">
                        Отмена
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
