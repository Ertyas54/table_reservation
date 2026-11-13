@php use Carbon\Carbon; @endphp

<div>
    <div class="page-header">
        <div>
            <h1 class="page-header__title">Мои заявки</h1>
            <p class="page-header__subtitle">Все ваши брони и заявки</p>
        </div>
        <a href="{{ route('restaurants.index') }}" class="btn btn--ghost">
            ← К ресторанам
        </a>
    </div>

    @if (session('message'))
        <div class="flash flash--success">{{ session('message') }}</div>
    @endif
    @if (session('error'))
        <div class="flash flash--error">{{ session('error') }}</div>
    @endif

    @if ($groups->isEmpty())
        <div class="empty-state">
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

                $bySlot = $items->groupBy('slot_id');
                $tables = $items->pluck('table')->unique('id');
            @endphp

            <div class="request-card">
                <div class="request-card__header">
                    <div>
                        <h2 class="request-card__title">
                            {{ $first->restaurant->name }}
                        </h2>
                        <p class="request-card__subtitle">
                            {{ Carbon::parse($first->booking_date)->format('d.m.Y') }}
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
                                <span class="badge badge--zone">{{ $table->table_number }}</span>
                            @endforeach
                        </span>
                    </div>

                    <div class="request-card__row">
                        <span class="request-card__label">Слоты:</span>
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
                            <span class="request-card__label">Комментарий:</span>
                            <span>{{ $first->comment }}</span>
                        </div>
                    @endif

                    @if ($first->admin_comment)
                        <div class="request-card__row request-card__row--error">
                            <span class="request-card__label">Причина отказа:</span>
                            <span>{{ $first->admin_comment }}</span>
                        </div>
                    @endif
                </div>

                @if (in_array($status, ['pending', 'approved']))
                    <div class="request-card__footer">
                        <button wire:click="cancelGroup('{{ $groupId }}')"
                                wire:confirm="Отменить заявку?"
                                class="btn btn--danger">
                            Отменить
                        </button>
                    </div>
                @endif
            </div>
        @endforeach
    @endif
</div>
