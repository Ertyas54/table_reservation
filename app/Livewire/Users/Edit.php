<?php

namespace App\Livewire\Users;

use App\Models\User;
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
            'role'       => 'required|in:admin,client',
        ];
    }

    public function save(): void
    {
        $data = $this->validate();

        if ($this->user->id === auth()->id() && $data['role'] !== 'admin') {
            $this->addError('role', 'Нельзя снять с себя роль администратора');
            return;
        }
        if (!empty($data['password'])) {
            $data['password'] = $data['password'];
        } else {
            unset($data['password']);
        }

        $this->user->update($data);

        session()->flash('message', 'Пользователь обновлён');
        $this->redirectRoute('users.index', navigate: true);
    }

    public function render()
    {
        return view('livewire.users.edit');
    }
}
