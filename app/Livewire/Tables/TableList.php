<?php

namespace App\Livewire\Tables;

use App\Exceptions\BookingException;
use App\Models\Booking;
use App\Models\Restaurant;
use App\Models\RestaurantTable;
use App\Models\TimeSlot;
use App\Models\User;
use App\Services\BookingService;
use Livewire\Component;

class TableList extends Component
{
    public Restaurant $restaurant;
    public string $bookingDate;

    public function mount(Restaurant $restaurant): void
    {
        $this->restaurant = $restaurant;
        $this->bookingDate = now()->toDateString();
    }

    public function delete(int $tableId): void
    {
        $table = RestaurantTable::where('restaurant_id', $this->restaurant->id)
            ->findOrFail($tableId);

        $hasActiveBookings = $table->bookings()
            ->exists();

        if ($hasActiveBookings) {
            session()->flash('error', 'Нельзя удалить столик — есть активные брони');
            return;
        }

        $table->delete();
    }

    public function book(int $tableId, int $slotId): void
    {
        $user = auth()->user();

        if (!$user instanceof User) {
            return;
        }

        try {
            $table = RestaurantTable::where('restaurant_id', $this->restaurant->id)
                ->findOrFail($tableId);

            $slot = TimeSlot::where('restaurant_id', $this->restaurant->id)
                ->findOrFail($slotId);

            app(BookingService::class)->create($user, $table, $slot, $this->bookingDate);
        } catch (BookingException $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function cancel(int $tableId, int $slotId): void
    {
        $user = auth()->user();

        if (!$user instanceof User) {
            return;
        }

        try {
            app(BookingService::class)->cancel($tableId, $slotId, $this->bookingDate, $user);
        } catch (BookingException $e) {
            session()->flash('error', $e->getMessage());
        }

    }

    public function render()
    {
        $tables = RestaurantTable::where('restaurant_id', $this->restaurant->id)
            ->orderBy('table_number')
            ->get();

        $timeSlots = TimeSlot::where('restaurant_id', $this->restaurant->id)
            ->where('is_active', true)
            ->orderBy('start_time')
            ->get();

        $bookings = Booking::where('restaurant_id', $this->restaurant->id)
            ->where('booking_date', $this->bookingDate)
            ->where('status', 'confirmed')
            ->get()
            ->keyBy(fn($b) => $b->table_id . '-' . $b->slot_id);

        return view('livewire.tables.table-list', [
            'tables' => $tables,
            'timeSlots' => $timeSlots,
            'bookings' => $bookings,
        ]);
    }
}
