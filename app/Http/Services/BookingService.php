<?php

namespace App\Http\Services;

use App\Http\Exceptions\BookingException;
use App\Models\Booking;
use App\Models\Restaurant;
use App\Models\RestaurantTable;
use App\Models\TimeSlot;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BookingService
{
    public function createRequest(User $user, Restaurant $restaurant, string $date, array $items, ?string $comment): string {
        if (empty($items)) {
            throw new BookingException('Нужно выбрать хотя бы один слот');
        }

        if ($date < now()->toDateString()) {
            throw new BookingException('Нельзя бронировать на прошлую дату');
        }

        $tableIds = array_unique(array_column($items, 'table_id'));
        $slotIds  = array_unique(array_column($items, 'slot_id'));

        $tables = RestaurantTable::where('restaurant_id', $restaurant->id)
            ->whereIn('id', $tableIds)
            ->get();

        if ($tables->count() !== count($tableIds)) {
            throw new BookingException('Некоторые столики недоступны');
        }

        if ($tables->pluck('location')->unique()->count() > 1) {
            throw new BookingException('Все столики должны быть в одной зоне');
        }

        if ($tables->contains(fn($t) => !$t->is_active)) {
            throw new BookingException('Некоторые столики отключены');
        }

        $slots = TimeSlot::where('restaurant_id', $restaurant->id)
            ->whereIn('id', $slotIds)
            ->get();

        if ($slots->count() !== count($slotIds)) {
            throw new BookingException('Некоторые слоты недоступны');
        }

        if ($slots->contains(fn($s) => !$s->is_active)) {
            throw new BookingException('Некоторые слоты отключены');
        }

        $now = now();
        if ($date === $now->toDateString()) {
            foreach ($slots as $slot) {
                if ($slot->start_time <= $now->format('H:i:s')) {
                    throw new BookingException('Некоторые слоты уже прошли');
                }
            }
        }

        foreach ($items as $item) {
            $conflict = Booking::where('table_id', $item['table_id'])
                ->where('slot_id', $item['slot_id'])
                ->whereDate('booking_date', $date)
                ->whereIn('status', ['approved', 'pending'])
                ->exists();

            if ($conflict) {
                throw new BookingException('Некоторые слоты уже забронированы');
            }
        }

        $groupId = (string) Str::uuid();

        DB::transaction(function () use ($items, $user, $restaurant, $date, $comment, $groupId) {
            foreach ($items as $item) {
                Booking::create([
                    'group_id'      => $groupId,
                    'user_id'       => $user->id,
                    'restaurant_id' => $restaurant->id,
                    'table_id'      => $item['table_id'],
                    'slot_id'       => $item['slot_id'],
                    'booking_date'  => $date,
                    'status'        => 'pending',
                    'comment'       => $comment,
                ]);
            }
        });

        return $groupId;
    }

    public function approve(string $groupId, User $admin): void
    {
        DB::transaction(function () use ($groupId, $admin) {
            $bookings = Booking::forGroup($groupId)
                ->where('status', 'pending')
                ->lockForUpdate()
                ->get();

            if ($bookings->isEmpty()) {
                throw new BookingException('Заявка не найдена или уже обработана');
            }

            foreach ($bookings as $booking) {
                $conflict = Booking::where('table_id', $booking->table_id)
                    ->where('slot_id', $booking->slot_id)
                    ->whereDate('booking_date', $booking->booking_date)
                    ->where('status', 'approved')
                    ->where('id', '!=', $booking->id)
                    ->exists();

                if ($conflict) {
                    throw new BookingException('Некоторые слоты уже заняты');
                }
            }

            Booking::forGroup($groupId)->update([
                'status'      => 'approved',
                'approved_at' => now(),
                'approved_by' => $admin->id,
            ]);
        });
    }

    public function reject(string $groupId, User $admin, string $reason): void
    {
        $updated = Booking::forGroup($groupId)
            ->where('status', 'pending')
            ->update([
                'status'        => 'rejected',
                'admin_comment' => $reason,
                'approved_by'   => $admin->id,
            ]);

        if ($updated === 0) {
            throw new BookingException('Заявка не найдена или уже обработана');
        }
    }

    public function cancel(string $groupId, User $user): void
    {
        $bookings = Booking::forGroup($groupId)->get();

        if ($bookings->isEmpty()) {
            throw new BookingException('Заявка не найдена');
        }

        $first = $bookings->first();
        if ($first->user_id !== $user->id && $user->role !== 'admin') {
            throw new BookingException('Это чужая заявка');
        }

        if (!in_array($first->status, ['pending', 'approved'])) {
            throw new BookingException('Нельзя отменить эту заявку');
        }

        Booking::forGroup($groupId)->update(['status' => 'cancelled']);
    }

    public function hasPendingRequest(User $user, Restaurant $restaurant, string $date): bool
    {
        return Booking::where('user_id', $user->id)
            ->where('restaurant_id', $restaurant->id)
            ->whereDate('booking_date', $date)
            ->where('status', 'pending')
            ->exists();
    }
}
