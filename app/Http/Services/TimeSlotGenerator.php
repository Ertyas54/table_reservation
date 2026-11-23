<?php

namespace App\Http\Services;

use App\Models\Restaurant;
use App\Models\TimeSlot;
use Carbon\Carbon;

class TimeSlotGenerator
{
    public function generateForRestaurant(Restaurant $restaurant): int
    {
        $opening = Carbon::parse($restaurant->opening_time);
        $closing = Carbon::parse($restaurant->closing_time);

        if ($closing->lessThanOrEqualTo($opening)) {
            $closing->addDay();
        }

        $created = 0;
        $cursor = $opening->copy();

        while ($cursor->lessThan($closing)) {
            $slotStart = $cursor->format('H:i:s');
            $slotEnd = $cursor->copy()->addHour()->format('H:i:s');

            if ($cursor->copy()->addHour()->greaterThan($closing)) {
                break;
            }

            $exists = TimeSlot::where('restaurant_id', $restaurant->id)
                ->where('start_time', $slotStart)
                ->where('end_time', $slotEnd)
                ->exists();

            if (!$exists) {
                TimeSlot::create([
                    'restaurant_id' => $restaurant->id,
                    'start_time'    => $slotStart,
                    'end_time'      => $slotEnd,
                    'is_active'     => true,
                ]);
                $created++;
            }

            $cursor->addHour();
        }

        return $created;
    }
}
