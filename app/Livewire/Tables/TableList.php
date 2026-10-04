<?php

namespace App\Livewire\Tables;

use App\Models\Booking;
use App\Models\Restaurant;
use App\Models\RestaurantTable;
use App\Models\TimeSlot;
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

        $table->delete();
    }

    public function book(int $tableId, int $slotId): void
    {
        $table = RestaurantTable::where('restaurant_id', $this->restaurant->id)
            ->findOrFail($tableId);

        if (!$table->is_active) {
            session()->flash('error', 'Этот столик сейчас недоступен для брони');
            return;
        }

        $exists = Booking::where('table_id', $tableId)
            ->where('slot_id', $slotId)
            ->where('booking_date', $this->bookingDate)
            ->whereIn('status', ['pending', 'confirmed'])
            ->exists();

        if ($exists) {
            session()->flash('error', 'Этот слот уже занят');
            return;
        }

        Booking::create([
            'user_id' => auth()->id(),
            'restaurant_id' => $this->restaurant->id,
            'table_id' => $tableId,
            'slot_id' => $slotId,
            'booking_date' => $this->bookingDate,
            'status' => 'pending',
            'total_price' => 0,
        ]);

        session()->flash('message', 'Столик забронирован');
    }

    public function cancel(int $tableId, int $slotId): void
    {
        $booking = Booking::where('table_id', $tableId)
            ->where('slot_id', $slotId)
            ->where('booking_date', $this->bookingDate)
            ->where('user_id', auth()->id())
            ->whereIn('status', ['pending', 'confirmed'])
            ->first();

        if (!$booking) {
            session()->flash('error', 'Бронь не найдена');
            return;
        }

        $booking->update(['status' => 'cancelled']);
        session()->flash('message', 'Бронь отменена');
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
            ->whereIn('status', ['pending', 'confirmed'])
            ->get()
            ->keyBy(fn($b) => $b->table_id . '-' . $b->slot_id);

        return view('livewire.tables.table-list', [
            'tables' => $tables,
            'timeSlots' => $timeSlots,
            'bookings' => $bookings,
        ]);
    }
}
