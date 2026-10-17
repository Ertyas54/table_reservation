<div class="container">
    <div class="page-header">
        <div>
            <h1 class="page-header__title">
                Редактировать: {{ $user->first_name }} {{ $user->last_name }}
            </h1>
        </div>
        <div class="page-header__actions">
            <a href="{{ route('users.index') }}" class="btn btn--ghost">← К списку</a>
        </div>
    </div>

    <form wire:submit="save" class="form-card">
        <div class="form-card__header">
            <h2 class="form-card__title">Данные пользователя</h2>
            <p class="form-card__subtitle">Обновите информацию и сохраните</p>
        </div>

        <div class="form-card__body">
            <div class="form-row">
                <div class="form-field">
                    <label class="form-field__label">Имя</label>
                    <input type="text" wire:model="first_name"
                           class="form-field__input @error('first_name') form-field__input--error @enderror">
                    @error('first_name') <div class="form-field__error">{{ $message }}</div> @enderror
                </div>

                <div class="form-field">
                    <label class="form-field__label">Фамилия</label>
                    <input type="text" wire:model="last_name"
                           class="form-field__input @error('last_name') form-field__input--error @enderror">
                    @error('last_name') <div class="form-field__error">{{ $message }}</div> @enderror
                </div>
            </div>

            <div class="form-field">
                <label class="form-field__label">Email</label>
                <input type="email" wire:model="email"
                       class="form-field__input @error('email') form-field__input--error @enderror">
                @error('email') <div class="form-field__error">{{ $message }}</div> @enderror
            </div>

            <div class="form-field">
                <label class="form-field__label">Телефон</label>
                <input type="text" wire:model="phone"
                       class="form-field__input @error('phone') form-field__input--error @enderror">
                @error('phone') <div class="form-field__error">{{ $message }}</div> @enderror
            </div>

            <div class="form-row">
                <div class="form-field">
                    <label class="form-field__label">
                        Новый пароль
                        <span class="form-field__hint">(оставьте пустым, чтобы не менять)</span>
                    </label>
                    <input type="password" wire:model="password"
                           class="form-field__input @error('password') form-field__input--error @enderror">
                    @error('password') <div class="form-field__error">{{ $message }}</div> @enderror
                </div>

                <div class="form-field">
                    <label class="form-field__label">Роль</label>
                    <select wire:model="role"
                            class="form-field__select @error('role') form-field__select--error @enderror"
                            @if($user->id === auth()->id()) disabled @endif>
                        <option value="client">Клиент</option>
                        <option value="admin">Админ</option>
                    </select>
                    @error('role') <div class="form-field__error">{{ $message }}</div> @enderror
                </div>
            </div>

            <div class="form-card__actions">
                <button type="submit" class="btn btn--primary">Сохранить</button>
                <a href="{{ route('users.index') }}" class="btn btn--ghost">Отмена</a>
            </div>
        </div>
    </form>
</div>
