@php
    use Carbon\Carbon;
    $monthsShort = [1 => 'янв', 'фев', 'мар', 'апр', 'мая', 'июн',
                     'июл', 'авг', 'сен', 'окт', 'ноя', 'дек'];
@endphp

<div>
    <div class="page">
        <div class="page-header">
            <div>
                <p class="page-header__eyebrow">Бронирование</p>
                <h1 class="page-header__title">Мои заявки</h1>
                <p class="page-header__subtitle">Все ваши брони и заявки</p>
            </div>
            <div class="page-header__actions">
                <a href="{{ route('restaurants.index') }}" class="btn btn--ghost">
                    <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5"/><path d="m12 19-7-7 7-7"/></svg>
                    К ресторанам
                </a>
            </div>
        </div>

        @if (session('message'))
            <div class="flash flash--success">{{ session('message') }}</div>
        @endif
        @if (session('error'))
            <div class="flash flash--error">{{ session('error') }}</div>
        @endif

        @if ($groups->isEmpty())
            <div class="empty-state">
                <div class="empty-state__icon">
                    <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
                </div>
                <div class="empty-state__title">Заявок пока нет</div>
                <div class="empty-state__text">
                    Выберите ресторан и забронируйте столик
                </div>
                <a href="{{ route('restaurants.index') }}" class="btn btn--primary">
                    К ресторанам
                </a>
            </div>
        @else
            @foreach ($groups as $groupId => $items)
                @php
                    $first = $items->first();
                    $status = $first->status;
                    $date = Carbon::parse($first->booking_date);

                    $bySlot = $items->groupBy('slot_id');
                    $tables = $items->pluck('table')->unique('id');
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
                                {{ $date->format('d.m.Y') }} · {{ $tables->count() }} столиков
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
                                    <span class="badge badge--zone">{{ $table->table_number }}</span>
                                @endforeach
                            </span>
                        </div>

                        <div class="request-card__row">
                            <span class="request-card__label">Слоты</span>
                            <span>
                                @foreach ($bySlot as $slotId => $slotItems)
                                    @php
                                        $slot = $slotItems->first()->slot;
                                        $time = Carbon::parse($slot->start_time)->format('H:i')
                                              . '–'
                                              . Carbon::parse($slot->end_time)->format('H:i');
                                    @endphp
                                    <span class="badge badge--zone">{{ $time }}</span>
                                @endforeach
                            </span>
                        </div>

                        @if ($first->comment)
                            <div class="request-card__row">
                                <span class="request-card__label">Комментарий</span>
                                <blockquote class="request-card__quote">{{ $first->comment }}</blockquote>
                            </div>
                        @endif

                        @if ($first->admin_comment)
                            <div class="request-card__row">
                                <span class="request-card__label">Причина отказа</span>
                                <div class="request-card__note">{{ $first->admin_comment }}</div>
                            </div>
                        @endif
                    </div>

                    @if (in_array($status, ['pending', 'approved']))
                        <div class="request-card__footer">
                            <span class="request-card__footer-note">
                                {{ $status === 'pending' ? 'Ожидает решения администратора' : 'Можно отменить до визита' }}
                            </span>
                            <button wire:click="cancelGroup('{{ $groupId }}')"
                                    wire:confirm="Отменить заявку?"
                                    class="btn btn--outline-danger">
                                <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18M6 6l12 12"/></svg>
                                Отменить
                            </button>
                        </div>
                    @endif
                </div>
            @endforeach
        @endif
    </div>
</div>
