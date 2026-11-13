<?php

namespace App\Livewire\Bookings;

use App\Http\Exceptions\BookingException;
use App\Http\Services\BookingService;
use App\Models\Booking;
use App\Models\Restaurant;
use App\Models\RestaurantTable;
use App\Models\TimeSlot;
use App\Models\User;
use Livewire\Component;

class CreateBookingRequest extends Component
{
    public Restaurant $restaurant;
    public string $bookingDate;

    public array $selectedTableIds = [];
    public array $selectedSlotIds = [];

    public string $comment = '';

    public ?string $errorMessage = null;

    public function mount(Restaurant $restaurant, ?string $date = null): void
    {
        $this->restaurant = $restaurant;
        $this->bookingDate = $date ?? now()->toDateString();

        if ($this->bookingDate < now()->toDateString()) {
            $this->bookingDate = now()->toDateString();
        }
    }

    public function updatedBookingDate(): void
    {
        if ($this->bookingDate < now()->toDateString()) {
            $this->bookingDate = now()->toDateString();
            $this->errorMessage = 'Нельзя выбрать прошедшую дату';
            return;
        }
        $this->selectedTableIds = [];
        $this->selectedSlotIds = [];
        $this->errorMessage = null;
    }

    public function toggleTable(int $tableId): void
    {
        if (in_array($tableId, $this->selectedTableIds)) {
            $this->selectedTableIds = array_values(
                array_filter($this->selectedTableIds, fn($id) => $id !== $tableId)
            );
        } else {
            if (!empty($this->selectedTableIds)) {
                $selectedZone = RestaurantTable::find($this->selectedTableIds[0])?->location;
                $newZone = RestaurantTable::find($tableId)?->location;
                if ($selectedZone !== $newZone) {
                    $this->errorMessage = 'Все столики должны быть в одной зоне';
                    return;
                }
            }
            $this->selectedTableIds[] = $tableId;
        }
        $this->errorMessage = null;
    }

    public function toggleSlot(int $slotId): void
    {
        if (in_array($slotId, $this->selectedSlotIds)) {
            $this->selectedSlotIds = array_values(
                array_filter($this->selectedSlotIds, fn($id) => $id !== $slotId)
            );
        } else {
            $this->selectedSlotIds[] = $slotId;
        }
        $this->errorMessage = null;
    }

    public function submit(): void
    {
        $user = auth()->user();
        if (!$user instanceof User) {
            $this->errorMessage = 'Вы не авторизованы';
            return;
        }

        if (empty($this->selectedTableIds) || empty($this->selectedSlotIds)) {
            $this->errorMessage = 'Выберите хотя бы один столик и один слот';
            return;
        }

        $items = [];
        foreach ($this->selectedTableIds as $tableId) {
            foreach ($this->selectedSlotIds as $slotId) {
                $items[] = ['table_id' => $tableId, 'slot_id' => $slotId];
            }
        }

        try {
            app(BookingService::class)->createRequest(
                $user,
                $this->restaurant,
                $this->bookingDate,
                $items,
                $this->comment ?: null,
            );

            $this->redirectRoute('bookings.my');
        } catch (BookingException $e) {
            $this->errorMessage = $e->getMessage();
        }
    }

    public function render()
    {
        $tables = RestaurantTable::where('restaurant_id', $this->restaurant->id)
            ->where('is_active', true)
            ->orderBy('location')
            ->orderBy('table_number')
            ->get();

        $timeSlots = TimeSlot::where('restaurant_id', $this->restaurant->id)
            ->where('is_active', true)
            ->orderBy('start_time')
            ->get();

        $booked = Booking::where('restaurant_id', $this->restaurant->id)
            ->whereDate('booking_date', $this->bookingDate)
            ->where('status', 'approved')
            ->get()
            ->keyBy(fn($b) => $b->table_id . '-' . $b->slot_id);

        $now = now();
        $isToday = $this->bookingDate === $now->toDateString();
        $pastSlotIds = collect();
        if ($isToday) {
            $pastSlotIds = $timeSlots->filter(
                fn($s) => $s->start_time <= $now->format('H:i:s')
            )->pluck('id')->toArray();
        }

        $hasPending = Booking::where('user_id', auth()->id())
            ->where('restaurant_id', $this->restaurant->id)
            ->whereDate('booking_date', $this->bookingDate)
            ->where('status', 'pending')
            ->exists();

        return view('livewire.bookings.create-booking-request', [
            'tables'      => $tables,
            'timeSlots'   => $timeSlots,
            'booked'      => $booked,
            'pastSlotIds' => $pastSlotIds,
            'isToday'     => $isToday,
            'hasPending'  => $hasPending,
        ]);
    }
}
