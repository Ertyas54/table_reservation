<?php

namespace App\Livewire\Tables;

use App\Http\Exceptions\BookingException;
use App\Models\Booking;
use App\Models\Restaurant;
use App\Models\RestaurantTable;
use App\Models\TimeSlot;
use App\Models\User;
use App\Http\Services\BookingService;
use Carbon\Carbon;
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
        if (auth()->user()->role !== 'admin') {
            abort(403);
        }
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

    public function render()
    {
        $tables = RestaurantTable::where('restaurant_id', $this->restaurant->id)
            ->orderBy('table_number')
            ->get();

        $timeSlots = TimeSlot::where('restaurant_id', $this->restaurant->id)
            ->where('is_active', true)
            ->orderBy('start_time')
            ->get();

        $now = now();
        $isToday = $this->bookingDate === $now->toDateString();

        $pastSlots = collect();
        if ($isToday) {
            $pastSlots = $timeSlots->filter(function ($slot) use ($now) {
                return $slot->start_time <= $now->format('H:i:s');
            })->pluck('id');
        }

        $bookings = Booking::where('restaurant_id', $this->restaurant->id)
            ->whereDate('booking_date', $this->bookingDate)
            ->where('status', 'approved')
            ->get()
            ->keyBy(fn($b) => $b->table_id . '-' . $b->slot_id);

        return view('livewire.tables.table-list', [
            'tables'    => $tables,
            'timeSlots' => $timeSlots,
            'bookings'  => $bookings,
            'pastSlots' => $pastSlots,
            'isToday'   => $isToday,
        ]);
    }
}
