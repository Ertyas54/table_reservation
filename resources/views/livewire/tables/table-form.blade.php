<div class="page">
    <div class="page-header">
        <div>
            <p class="page-header__eyebrow">Управление</p>
            <h1 class="page-header__title">
                {{ $table ? 'Редактирование столика' : 'Новый столик' }}
            </h1>
            <p class="page-header__subtitle">Ресторан: {{ $restaurant->name }}</p>
        </div>
        <div class="page-header__actions">
            <a href="{{ route('tables.index', $restaurant) }}" class="btn btn--ghost">
                <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5"/><path d="m12 19-7-7 7-7"/></svg>
                К столикам
            </a>
        </div>
    </div>

    <form wire:submit="save" class="form-card">
        <div class="form-card__body">
            <div class="form-row">
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
            </div>

            <label class="check-row">
                <input type="checkbox" wire:model="is_active" id="is_active">
                <span class="check-row__text">Столик активен</span>
            </label>

            <div class="form-card__actions">
                <a href="{{ route('tables.index', $restaurant) }}" class="btn btn--ghost">
                    Отмена
                </a>
                <button type="submit" class="btn btn--primary">
                    <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
                    {{ $table ? 'Сохранить изменения' : 'Создать столик' }}
                </button>
            </div>
        </div>
    </form>
</div>
