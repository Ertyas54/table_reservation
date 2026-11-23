<?php

namespace Database\Seeders;

use App\Models\Restaurant;
use App\Models\TimeSlot;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class TimeSlotSeeder extends Seeder
{
    public function run(): void
    {
        $restaurants = Restaurant::all();

        foreach ($restaurants as $restaurant) {
            $opening = Carbon::parse($restaurant->opening_time);
            $closing = Carbon::parse($restaurant->closing_time);

            if ($closing->lessThanOrEqualTo($opening)) {
                $closing = Carbon::parse('23:59');
            }

            $cursor = $opening->copy();

            while ($cursor->copy()->addHour()->lessThanOrEqualTo($closing)) {
                $slotStart = $cursor->format('H:i:s');
                $slotEnd = $cursor->copy()->addHour()->format('H:i:s');

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
                }

                $cursor->addHour();
            }
        }
    }
}
