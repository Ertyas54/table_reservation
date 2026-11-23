<div>
    <div class="page">
        <div class="page-header">
            <div>
                <p class="page-header__eyebrow">Управление</p>
                <h1 class="page-header__title">
                    Менеджеры ресторана «{{ $restaurant->name }}»
                </h1>
                <p class="page-header__subtitle">
                    Назначайте менеджеров по email — они получат доступ к управлению столиками и заявками
                </p>
            </div>
            <div class="page-header__actions">
                <a href="{{ route('restaurants.index') }}" class="btn btn--ghost">
                    <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5"/><path d="m12 19-7-7 7-7"/></svg>
                    К ресторанам
                </a>
            </div>
        </div>

        @if ($flashMessage)
            <div class="flash flash--success">{{ $flashMessage }}</div>
        @endif
        @if ($flashError)
            <div class="flash flash--error">{{ $flashError }}</div>
        @endif

        <div class="form-card form-card--wide">
            <div class="form-card__header">
                <h2 class="form-card__title">Добавить менеджера</h2>
                <p class="form-card__subtitle">
                    Введите email пользователя, зарегистрированного в системе
                </p>
            </div>
            <div class="form-card__body">
                <div class="form-row">
                    <div class="form-field">
                        <label class="form-field__label">Email пользователя</label>
                        <input type="email"
                               wire:model="email"
                               wire:keydown.enter="addByEmail"
                               placeholder="manager@example.com"
                               class="form-field__input @error('email') form-field__input--error @enderror">
                        @error('email') <div class="form-field__error">{{ $message }}</div> @enderror
                    </div>
                    <div class="form-field" style="align-self: end;">
                        <button wire:click="addByEmail"
                                wire:loading.attr="disabled"
                                class="btn btn--primary">
                            <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"/></svg>
                            Назначить менеджером
                        </button>
                    </div>
                </div>

                <div class="form-field__hint" style="margin-top: 8px;">
                    Если пользователь зарегистрирован как «Клиент», его роль автоматически изменится на «Менеджер».
                    Администраторов и владельцев других ресторанов назначить нельзя.
                </div>
            </div>
        </div>

        <div class="form-card form-card--wide" style="margin-top: 24px;">
            <div class="form-card__header">
                <h2 class="form-card__title">Текущие менеджеры</h2>
                <p class="form-card__subtitle">Всего: {{ $managers->count() }}</p>
            </div>
            <div class="form-card__body">
                @if ($managers->isEmpty())
                    <div class="empty-state" style="padding: 24px; text-align: center;">
                        <div class="empty-state__text">Менеджеров пока нет</div>
                    </div>
                @else
                    <div class="managers-list">
                        @foreach ($managers as $manager)
                            <div class="manager-row" wire:key="manager-{{ $manager->id }}">
                                <div class="manager-row__info">
                                    <div class="manager-row__avatar">
                                        {{ mb_strtoupper(mb_substr($manager->first_name, 0, 1) . mb_substr($manager->last_name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <div class="manager-row__name">
                                            {{ $manager->first_name }} {{ $manager->last_name }}
                                        </div>
                                        <div class="manager-row__email">{{ $manager->email }}</div>
                                    </div>
                                </div>
                                <button wire:click="remove({{ $manager->id }})"
                                        wire:confirm="Снять {{ $manager->first_name }} с должности менеджера?"
                                        class="btn btn--outline-danger">
                                    <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18M6 6l12 12"/></svg>
                                    Снять
                                </button>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
