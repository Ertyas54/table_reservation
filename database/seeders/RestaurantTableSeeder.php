<?php

namespace Database\Seeders;

use App\Models\Restaurant;
use App\Models\RestaurantTable;
use Illuminate\Database\Seeder;

class RestaurantTableSeeder extends Seeder
{
    public function run(): void
    {
        $tablesByRestaurant = [
            'Пушкин' => [
                ['T1', 'main'],
                ['T2', 'main'],
                ['T3', 'main'],
                ['V1', 'vip'],
                ['V2', 'vip'],
                ['B1', 'bar'],
            ],
            'Турандот' => [
                ['T1', 'main'],
                ['T2', 'main'],
                ['V1', 'vip'],
                ['P1', 'terrace'],
                ['P2', 'terrace'],
                ['P3', 'terrace'],
            ],
            'Причал' => [
                ['T1', 'main'],
                ['T2', 'main'],
                ['T3', 'main'],
                ['T4', 'main'],
                ['P1', 'terrace'],
                ['P2', 'terrace'],
            ],
        ];

        foreach ($tablesByRestaurant as $restaurantName => $tables) {
            $restaurant = Restaurant::where('name', $restaurantName)->first();
            if (!$restaurant) {
                continue;
            }

            foreach ($tables as [$number, $location]) {
                RestaurantTable::create([
                    'restaurant_id' => $restaurant->id,
                    'table_number'  => $number,
                    'location'      => $location,
                    'is_active'     => true,
                ]);
            }
        }
    }
}
