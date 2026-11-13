@php
    use Carbon\Carbon;
    $monthsShort = [1 => 'янв', 'фев', 'мар', 'апр', 'мая', 'июн',
                     'июл', 'авг', 'сен', 'окт', 'ноя', 'дек'];
@endphp

<div>
    <div class="page">
        <div class="page-header">
            <div>
                <p class="page-header__eyebrow">Модерация</p>
                <h1 class="page-header__title">Заявки на бронирование</h1>
                <p class="page-header__subtitle">Одобрение и отклонение заявок гостей</p>
            </div>
        </div>

        @if (session('message'))
            <div class="flash flash--success">{{ session('message') }}</div>
        @endif
        @if (session('error'))
            <div class="flash flash--error">{{ session('error') }}</div>
        @endif

        <div class="toolbar">
            <div class="toolbar__group">
                <label class="date-field">
                    <span>Статус:</span>
                    <select wire:model.live="statusFilter" class="filters__select">
                        <option value="pending">На модерации</option>
                        <option value="approved">Одобрено</option>
                        <option value="rejected">Отклонено</option>
                        <option value="cancelled">Отменено</option>
                        <option value="">Все</option>
                    </select>
                </label>
            </div>
            <div class="toolbar__spacer"></div>
            <span class="toolbar__hint">Заявок: <b>{{ $groups->count() }}</b></span>
        </div>

        @if ($groups->isEmpty())
            <div class="empty-state">
                <div class="empty-state__icon">
                    <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 12h-6l-2 3h-4l-2-3H2"/><path d="M5.45 5.11 2 12v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6l-3.45-6.89A2 2 0 0 0 16.76 4H7.24a2 2 0 0 0-1.79 1.11Z"/></svg>
                </div>
                <div class="empty-state__title">Заявок нет</div>
            </div>
        @else
            @foreach ($groups as $groupId => $items)
                @php
                    $first = $items->first();
                    $bySlot = $items->groupBy('slot_id');
                    $tables = $items->pluck('table')->unique('id');
                    $status = $first->status;
                    $date = Carbon::parse($first->booking_date);
                @endphp

                <div class="request-card">
                    <div class="request-card__header">
                        <div class="date-block" title="{{ $date->format('d.m.Y') }}">
                            <span class="date-block__day">{{ $date->day }}</span>
                            <span class="date-block__month">{{ $monthsShort[$date->month] }}</span>
                        </div>
                        <div class="request-card__heading">
                            <h2 class="request-card__title">
                                {{ $first->restaurant->name }}
                            </h2>
                            <p class="request-card__subtitle">
                                <span class="avatar-xs avatar-xs--{{ $first->user->role === 'admin' ? 'admin' : 'client' }}">
                                    {{ mb_strtoupper(mb_substr($first->user->first_name ?? 'Г', 0, 1) . mb_substr($first->user->last_name ?? '', 0, 1)) }}
                                </span>
                                {{ $first->user->first_name ?? $first->user->email }} · {{ $date->format('d.m.Y') }}
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
                            <span class="request-card__label">Столики</span>
                            <span>
                                @foreach ($tables as $table)
                                    <span class="badge badge--zone">{{ $table->table_number }} · {{ $table->location }}</span>
                                @endforeach
                            </span>
                        </div>

                        <div class="request-card__row">
                            <span class="request-card__label">Слоты</span>
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
                                <span class="request-card__label">Комментарий</span>
                                <blockquote class="request-card__quote">{{ $first->comment }}</blockquote>
                            </div>
                        @endif
                    </div>

                    @if ($status === 'pending')
                        <div class="request-card__footer">
                            <span class="request-card__footer-note">Ожидает решения администратора</span>
                            <button wire:click="openRejectModal('{{ $groupId }}')"
                                    class="btn btn--outline-danger">
                                <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18M6 6l12 12"/></svg>
                                Отклонить
                            </button>
                            <button wire:click="approveGroup('{{ $groupId }}')"
                                    class="btn btn--primary">
                                <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
                                Одобрить
                            </button>
                        </div>
                    @endif
                </div>
            @endforeach
        @endif

        @if ($rejectingGroupId)
            <div class="modal-backdrop">
                <div class="modal">
                    <div class="modal__header">
                        <div>
                            <h3 class="modal__title">Причина отказа</h3>
                            <p class="modal__subtitle">Текст увидит гости в карточке заявки</p>
                        </div>
                        <button wire:click="closeRejectModal" class="icon-btn modal__close" title="Закрыть">
                            <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18M6 6l12 12"/></svg>
                        </button>
                    </div>
                    <textarea wire:model="rejectReason" rows="4"
                              placeholder="Например: все столики заняты"
                              class="form-field__textarea"></textarea>
                    <div class="modal__footer">
                        <button wire:click="closeRejectModal" class="btn btn--ghost">
                            Отмена
                        </button>
                        <button wire:click="confirmReject" class="btn btn--outline-danger">
                            <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18M6 6l12 12"/></svg>
                            Отклонить
                        </button>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
