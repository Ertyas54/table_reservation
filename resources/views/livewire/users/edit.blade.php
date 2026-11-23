<div class="page">
    <div class="page-header">
        <div>
            <p class="page-header__eyebrow">Администрирование</p>
            <h1 class="page-header__title">
                Редактировать: {{ $user->first_name }} {{ $user->last_name }}
            </h1>
            <p class="page-header__subtitle">Обновите данные и сохраните изменения</p>
        </div>
        <div class="page-header__actions">
            <a href="{{ route('users.index') }}" class="btn btn--ghost">
                <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5"/><path d="m12 19-7-7 7-7"/></svg>
                К списку
            </a>
        </div>
    </div>

    <form wire:submit="save" class="form-card form-card--wide">
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

            <div class="form-row">
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
            </div>

            <div class="form-row">
                <div class="form-field">
                    <div class="form-field__label-row">
                        <label class="form-field__label">Новый пароль</label>
                        <span class="info-tip">i
                            <span class="info-tip__bubble">Оставьте пустым, чтобы не менять</span>
                        </span>
                    </div>
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
                <a href="{{ route('users.index') }}" class="btn btn--ghost">Отмена</a>
                <button type="submit" class="btn btn--primary">
                    <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
                    Сохранить
                </button>
            </div>
        </div>
    </form>
</div>
