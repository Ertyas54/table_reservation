<?php

namespace App\Livewire\Bookings;

use App\Http\Exceptions\BookingException;
use App\Http\Services\BookingService;
use App\Models\Booking;
use App\Models\User;
use Livewire\Component;

class BookingRequestList extends Component
{
    public string $statusFilter = 'pending';
    public ?string $rejectReason = '';
    public ?string $rejectingGroupId = null;

    public function approveGroup(string $groupId): void
    {
        $user = auth()->user();
        if (!$user instanceof User) abort(403);

        try {
            app(BookingService::class)->approve($groupId, $user);
            session()->flash('message', 'Заявка одобрена');
        } catch (BookingException $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function openRejectModal(string $groupId): void
    {
        $user = auth()->user();
        $booking = \App\Models\Booking::forGroup($groupId)->with('restaurant')->first();
        if (!$user || !$booking || !$user->canManageRestaurant($booking->restaurant)) {
            abort(403);
        }
        $this->rejectingGroupId = $groupId;
        $this->rejectReason = '';
    }

    public function closeRejectModal(): void
    {
        $this->rejectingGroupId = null;
        $this->rejectReason = '';
    }

    public function confirmReject(): void
    {
        $user = auth()->user();
        if (!$user instanceof User) abort(403);

        if (!$this->rejectingGroupId) {
            return;
        }

        if (!trim($this->rejectReason)) {
            return;
        }

        try {
            app(BookingService::class)->reject(
                $this->rejectingGroupId,
                $user,
                $this->rejectReason
            );

            $this->closeRejectModal();
        } catch (BookingException $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function render()
    {
        $user = auth()->user();
        $query = Booking::with(['user', 'restaurant', 'table', 'slot'])
            ->orderByDesc('created_at');

        if (!$user->isAdmin()) {
            $restaurantIds = $user->managedRestaurants()->pluck('restaurants.id');
            if ($restaurantIds->isEmpty()) {
                return view('livewire.bookings.booking-request-list', [
                    'groups' => collect(),
                ]);
            }
            $query->whereIn('restaurant_id', $restaurantIds);
        }

        if ($this->statusFilter) {
            $query->where('status', $this->statusFilter);
        }

        $groups = $query->get()->groupBy('group_id');

        return view('livewire.bookings.booking-request-list', [
            'groups' => $groups,
        ]);
    }
}
