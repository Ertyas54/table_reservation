<?php

namespace App\Livewire\Bookings;

use App\Http\Exceptions\BookingException;
use App\Http\Services\BookingService;
use App\Models\Booking;
use App\Models\User;
use Livewire\Component;

class BookingRequests extends Component
{
    public function cancelGroup(string $groupId): void
    {
        $user = auth()->user();
        if (!$user instanceof User) {
            return;
        }

        try {
            app(BookingService::class)->cancel($groupId, $user);
        } catch (BookingException $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function render()
    {
        $user = auth()->user();

        $groups = Booking::where('user_id', $user->id)
            ->with(['restaurant', 'table', 'slot'])
            ->orderByDesc('created_at')
            ->get()
            ->groupBy('group_id');

        return view('livewire.bookings.booking-requests', [
            'groups' => $groups,
        ]);
    }
}
