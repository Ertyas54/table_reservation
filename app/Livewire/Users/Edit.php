<?php

namespace App\Livewire\Users;

use App\Models\User;
use App\Models\Restaurant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Livewire\Component;

class Edit extends Component
{
    public User $user;

    public string $first_name = '';
    public string $last_name = '';
    public string $email = '';
    public string $phone = '';
    public string $password = '';
    public string $role = 'client';
    public array $selectedRestaurantIds = [];

    public function mount(User $user): void
    {
        if (auth()->user()->role !== 'admin') {
            abort(403);
        }

        $this->user = $user;
        $this->first_name = $user->first_name;
        $this->last_name = $user->last_name;
        $this->email = $user->email;
        $this->phone = $user->phone ?? '';
        $this->role = $user->role;
        $this->selectedRestaurantIds = $user->managedRestaurants()
            ->pluck('restaurants.id')
            ->toArray();
    }

    protected function rules(): array
    {
        return [
            'first_name' => 'required|string|max:100',
            'last_name'  => 'required|string|max:100',
            'email'      => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($this->user->id),
            ],
            'phone'      => 'nullable|string|max:20',
            'password'   => 'nullable|string|min:6',
            'role' => 'required|in:admin,owner,manager,client',
            'selectedRestaurantIds'   => 'array',
            'selectedRestaurantIds.*' => 'integer|exists:restaurants,id',
        ];
    }

    public function save(): void
    {
        $data = $this->validate();

        if ($this->user->id === auth()->id() && $data['role'] !== 'admin') {
            $this->addError('role', 'Нельзя снять с себя роль администратора');
            return;
        }

        if (in_array($data['role'], ['owner', 'manager'])
            && empty($this->selectedRestaurantIds)) {
            $this->addError('selectedRestaurantIds', 'Выберите хотя бы один ресторан');
            return;
        }

        $restaurantIds = $data['selectedRestaurantIds'] ?? [];
        unset($data['selectedRestaurantIds']);

        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $this->user->update($data);

        DB::table('restaurant_user')->where('user_id', $this->user->id)->delete();
        if (in_array($data['role'], ['owner', 'manager'])) {
            $pivotRole = $data['role'];
            foreach ($restaurantIds as $rid) {
                DB::table('restaurant_user')->insert([
                    'restaurant_id' => $rid,
                    'user_id'       => $this->user->id,
                    'role'          => $pivotRole,
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ]);
            }
        }

        session()->flash('message', 'Пользователь обновлён');
        $this->redirectRoute('users.index', navigate: true);
    }

    public function render()
    {
        return view('livewire.users.edit', [
            'allRestaurants' => Restaurant::orderBy('name')->get(),
        ]);
    }

    public function updatedRole(): void
    {
        if (!in_array($this->role, ['owner', 'manager'])) {
            $this->selectedRestaurantIds = [];
        }
    }
}
