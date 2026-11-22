<div class="page">
    <div class="page-header">
        <div>
            <p class="page-header__eyebrow">Управление</p>
            <h1 class="page-header__title">
                {{ $restaurant ? 'Редактирование ресторана' : 'Новый ресторан' }}
            </h1>
            <p class="page-header__subtitle">Заполните карточку заведения</p>
        </div>
        <div class="page-header__actions">
            <a href="{{ route('restaurants.index') }}" class="btn btn--ghost">
                <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5"/><path d="m12 19-7-7 7-7"/></svg>
                К списку
            </a>
        </div>
    </div>

    <form wire:submit="save" class="form-card form-card--wide">
        <div class="form-card__body">
            <div class="form-field">
                <label class="form-field__label">Название</label>
                <input type="text"
                       wire:model="name"
                       placeholder="Например: Пушкин"
                       class="form-field__input @error('name') form-field__input--error @enderror">
                @error('name')
                <div class="form-field__error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-field">
                <label class="form-field__label">Описание</label>
                <textarea wire:model="description"
                          rows="3"
                          placeholder="Краткое описание"
                          class="form-field__textarea @error('description') form-field__input--error @enderror"></textarea>
                @error('description')
                <div class="form-field__error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-row">
                <div class="form-field">
                    <label class="form-field__label">Город</label>
                    <input type="text" wire:model="city"
                           class="form-field__input @error('city') form-field__input--error @enderror">
                    @error('city')
                    <div class="form-field__error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-field">
                    <label class="form-field__label">Адрес</label>
                    <input type="text" wire:model="address"
                           class="form-field__input @error('address') form-field__input--error @enderror">
                    @error('address')
                    <div class="form-field__error">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="form-row">
                <div class="form-field">
                    <label class="form-field__label">Телефон</label>
                    <input type="text" wire:model="phone"
                           placeholder="+7..."
                           class="form-field__input @error('phone') form-field__input--error @enderror">
                    @error('phone')
                    <div class="form-field__error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-field">
                    <label class="form-field__label">Email</label>
                    <input type="email" wire:model="email"
                           placeholder="restaurant@example.com"
                           class="form-field__input @error('email') form-field__input--error @enderror">
                    @error('email')
                    <div class="form-field__error">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="form-row">
                <div class="form-field">
                    <label class="form-field__label">Открытие</label>
                    <input type="time" wire:model="opening_time"
                           class="form-field__input @error('opening_time') form-field__input--error @enderror">
                    @error('opening_time')
                    <div class="form-field__error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-field">
                    <label class="form-field__label">Закрытие</label>
                    <input type="time" wire:model="closing_time"
                           class="form-field__input @error('closing_time') form-field__input--error @enderror">
                    @error('closing_time')
                    <div class="form-field__error">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <label class="check-row">
                <input type="checkbox" wire:model="is_active" id="is_active">
                <span class="check-row__text">Ресторан активен</span>
            </label>

            <div class="form-card__actions">
                <a href="{{ route('restaurants.index') }}" class="btn btn--ghost">
                    Отмена
                </a>
                <button type="submit" class="btn btn--primary">
                    <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
                    {{ $restaurant ? 'Сохранить изменения' : 'Создать ресторан' }}
                </button>
            </div>
        </div>
    </form>
</div>
