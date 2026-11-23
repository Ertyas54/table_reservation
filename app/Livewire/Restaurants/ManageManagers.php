<?php

namespace App\Livewire\Restaurants;

use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class ManageManagers extends Component
{
    public Restaurant $restaurant;

    public string $email = '';
    public ?string $flashMessage = null;
    public ?string $flashError = null;

    public function mount(Restaurant $restaurant): void
    {
        $user = auth()->user();
        if (!$user->isAdmin() && !$user->ownsRestaurant($restaurant)) {
            abort(403);
        }
        $this->restaurant = $restaurant;
    }

    private function ensureCanManage(): void
    {
        $user = auth()->user();
        if (!$user->isAdmin() && !$user->ownsRestaurant($this->restaurant)) {
            abort(403);
        }
    }

    public function addByEmail(): void
    {
        $this->ensureCanManage();
        $this->flashMessage = null;
        $this->flashError = null;

        $this->validate(
            ['email' => 'required|email'],
            ['email.required' => 'Введите email', 'email.email' => 'Некорректный email']
        );

        $email = mb_strtolower(trim($this->email));
        $candidate = User::whereRaw('LOWER(email) = ?', [$email])->first();

        if (!$candidate) {
            $this->flashError = 'Пользователь с такой почтой не зарегистрирован';
            return;
        }

        if ($candidate->id === auth()->id()) {
            $this->flashError = 'Нельзя назначить себя менеджером';
            return;
        }

        if ($candidate->isAdmin()) {
            $this->flashError = 'Пользователь с ролью «Администратор» не может быть менеджером';
            return;
        }

        if ($candidate->isOwner()) {
            $this->flashError = 'Пользователь с ролью «Владелец ресторана» не может быть менеджером';
            return;
        }

        $alreadyManager = DB::table('restaurant_user')
            ->where('restaurant_id', $this->restaurant->id)
            ->where('user_id', $candidate->id)
            ->where('role', 'manager')
            ->exists();

        if ($alreadyManager) {
            $this->flashError = 'Этот пользователь уже управляет рестораном';
            return;
        }

        DB::transaction(function () use ($candidate) {
            // если был клиентом — повышаем до менеджера
            if ($candidate->role === 'client') {
                $candidate->update(['role' => 'manager']);
            }

            DB::table('restaurant_user')->insert([
                'restaurant_id' => $this->restaurant->id,
                'user_id'       => $candidate->id,
                'role'          => 'manager',
                'created_at'    => now(),
                'updated_at'    => now(),
            ]);
        });

        $this->flashMessage = 'Пользователь ' . $candidate->first_name . ' ' . $candidate->last_name . ' назначен менеджером';
        $this->email = '';
    }

    public function remove(int $userId): void
    {
        $this->ensureCanManage();
        $this->flashMessage = null;
        $this->flashError = null;

        DB::table('restaurant_user')
            ->where('restaurant_id', $this->restaurant->id)
            ->where('user_id', $userId)
            ->where('role', 'manager')
            ->delete();

        $remaining = DB::table('restaurant_user')
            ->where('user_id', $userId)
            ->where('role', 'manager')
            ->count();

        if ($remaining === 0) {
            User::where('id', $userId)
                ->where('role', 'manager')
                ->update(['role' => 'client']);
        }

        $this->flashMessage = 'Менеджер снят с ресторана';
    }

    public function render()
    {
        $managerIds = DB::table('restaurant_user')
            ->where('restaurant_id', $this->restaurant->id)
            ->where('role', 'manager')
            ->pluck('user_id');

        $managers = User::whereIn('id', $managerIds)
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();

        return view('livewire.restaurants.manage-managers', [
            'managers' => $managers,
        ]);
    }
}
