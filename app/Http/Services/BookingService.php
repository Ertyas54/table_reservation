<?php

namespace App\Services;

use App\Exceptions\BookingException;
use App\Models\Booking;
use App\Models\RestaurantTable;
use App\Models\TimeSlot;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class BookingService
{
    public function create(User $user, RestaurantTable $table, TimeSlot $slot, string $date): Booking
    {
        if (!$table->is_active) {
            throw new BookingException('Столик недоступен для брони');
        }

        if (!$slot->is_active || $slot->restaurant_id !== $table->restaurant_id) {
            throw new BookingException('Слот недоступен');
        }

        if ($date < now()->toDateString()) {
            throw new BookingException('Нельзя бронировать на прошлую дату');
        }

        return DB::transaction(function () use ($user, $table, $slot, $date) {
            $exists = Booking::where('table_id', $table->id)
                ->where('slot_id', $slot->id)
                ->where('booking_date', $date)
                ->where('status', 'confirmed')
                ->lockForUpdate()
                ->exists();

            if ($exists) {
                throw new BookingException('Этот слот уже занят');
            }

            return Booking::create([
                'user_id'       => $user->id,
                'restaurant_id' => $table->restaurant_id,
                'table_id'      => $table->id,
                'slot_id'       => $slot->id,
                'booking_date'  => $date,
                'status'        => 'confirmed',
            ]);
        });
    }

    public function cancel(int $tableId, int $slotId, string $bookingDate, User $user): void
    {
        $booking = Booking::where('table_id', $tableId)
            ->where('slot_id', $slotId)
            ->where('booking_date', $bookingDate)
            ->where('user_id', auth()->id())
            ->where('status', 'confirmed')
            ->first();

        if (!$booking) {
            throw new BookingException('error', 'Бронь не найдена');
        }

        if ($booking->user_id !== $user->id) {
            throw new BookingException('Это чужая бронь');
        }

        $booking->update(['status' => 'cancelled']);
    }
}
