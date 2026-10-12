<div class="form-card form-card--wide">
    <div class="form-card__header">
        <h1 class="form-card__title">
            {{ $restaurant ? 'Редактирование ресторана' : 'Новый ресторан' }}
        </h1>
    </div>

    <form wire:submit="save" class="form-card__body">
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

        <div class="form-field form-field--checkbox">
            <input type="checkbox" wire:model="is_active" id="is_active">
            <label for="is_active">Ресторан активен</label>
        </div>

        <div class="form-card__actions">
            <button type="submit" class="btn btn--primary">
                {{ $restaurant ? 'Сохранить изменения' : 'Создать ресторан' }}
            </button>
            <a href="{{ route('restaurants.index') }}" class="btn btn--ghost">
                Отмена
            </a>
        </div>
    </form>
</div>
