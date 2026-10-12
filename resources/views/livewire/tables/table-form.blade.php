<div class="form-card">
    <div class="form-card__header">
        <h1 class="form-card__title">
            {{ $table ? 'Редактирование столика' : 'Новый столик' }}
        </h1>
        <p class="form-card__subtitle">
            Ресторан: {{ $restaurant->name }}
        </p>
    </div>

    <form wire:submit="save" class="form-card__body">
        <div class="form-field">
            <label class="form-field__label">Номер столика</label>
            <input type="text"
                   wire:model="table_number"
                   placeholder="Например: A1, VIP-2"
                   class="form-field__input @error('table_number') form-field__input--error @enderror">
            @error('table_number')
            <div class="form-field__error">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-field">
            <label class="form-field__label">Расположение</label>
            <select wire:model="location"
                    class="form-field__select @error('location') form-field__select--error @enderror">
                <option value="Основной зал">Основной зал</option>
                <option value="VIP">VIP</option>
                <option value="Терраса">Терраса</option>
                <option value="Бар">Бар</option>
            </select>
            @error('location')
            <div class="form-field__error">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-field form-field--checkbox">
            <input type="checkbox" wire:model="is_active" id="is_active">
            <label for="is_active">Столик активен (доступен для брони)</label>
        </div>

        <div class="form-card__actions">
            <button type="submit" class="btn btn--primary">
                {{ $table ? 'Сохранить изменения' : 'Создать столик' }}
            </button>
            <a href="{{ route('tables.index', $restaurant) }}" class="btn btn--ghost">
                Отмена
            </a>
        </div>
    </form>
</div>
