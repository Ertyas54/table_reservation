<?php

namespace App\Livewire;

use Livewire\Component;

class NotificationsBell extends Component
{
    public $notifications = [];
    public $unreadCount = 0;

    public function mount(): void
    {
        $this->loadNotifications();
    }

    public function loadNotifications(): void
    {
        $user = auth()->user();
        if (! $user) {
            return;
        }

        $this->notifications = $user->notifications()
            ->latest()
            ->take(10)
            ->get()
            ->toArray();

        $this->unreadCount = $user->unreadNotifications()->count();
    }

    public function markAsRead(string $id): void
    {
        $notification = auth()->user()->notifications()->find($id);

        if ($notification) {
            $notification->markAsRead();
        }

        $this->loadNotifications();
    }

    public function markAllAsRead(): void
    {
        auth()->user()->unreadNotifications->markAsRead();
        $this->loadNotifications();
    }

    public function render()
    {
        return view('livewire.notifications-bell');
    }
}
