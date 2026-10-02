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
            $start = Carbon::parse($restaurant->opening_time);
            $close = Carbon::parse($restaurant->closing_time);

            if ($close->lessThanOrEqualTo($start)) {
                $close = Carbon::parse('23:59');
            }

            while ($start->copy()->addHours(2)->lte($close)) {
                TimeSlot::create([
                    'restaurant_id' => $restaurant->id,
                    'start_time'    => $start->format('H:i:s'),
                    'end_time'      => $start->copy()->addHours(2)->format('H:i:s'),
                    'is_active'     => true,
                ]);

                $start->addHours(2);
            }
        }
    }
}
