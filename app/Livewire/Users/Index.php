<?php

namespace App\Livewire\Users;

use App\Models\User;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public string $role = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingRole(): void
    {
        $this->resetPage();
    }

    public function delete(int $id): void
    {
        if (auth()->user()->role !== 'admin') {
            abort(403);
        }

        $user = User::findOrFail($id);

        if ($user->id === auth()->id()) {
            session()->flash('error', 'Нельзя удалить себя');
            return;
        }

        if ($user->bookings()->whereIn('status', ['pending', 'confirmed'])->exists()) {
            session()->flash('error', 'У пользователя есть брони — удаление запрещено');
            return;
        }

        $user->delete();
        session()->flash('message', 'Пользователь удалён');
    }

    public function render()
    {
        $users = User::query()
            ->withCount(['bookings' => function ($q) {
                $q->whereIn('status', ['pending', 'confirmed']);
            }])
            ->when($this->search, function ($q) {
                $q->where(function ($q) {
                    $q->where('first_name', 'like', "%{$this->search}%")
                        ->orWhere('last_name', 'like', "%{$this->search}%")
                        ->orWhere('email', 'like', "%{$this->search}%");
                });
            })
            ->when($this->role, fn($q) => $q->where('role', $this->role))
            ->orderBy('id')
            ->paginate(15);

        return view('livewire.users.index', [
            'users' => $users,
            'total' => User::count(),
        ]);
    }}
