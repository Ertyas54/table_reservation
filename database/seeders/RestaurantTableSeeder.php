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
                ['T1', 'Основной зал'],
                ['T2', 'Основной зал'],
                ['T3', 'Основной зал'],
                ['V1', 'VIP'],
                ['V2', 'VIP'],
                ['B1', 'Бар'],
            ],
            'Турандот' => [
                ['T1', 'Основной зал'],
                ['T2', 'Основной зал'],
                ['V1', 'VIP'],
                ['P1', 'Терраса'],
                ['P2', 'Терраса'],
                ['P3', 'Терраса'],
            ],
            'Причал' => [
                ['T1', 'Основной зал'],
                ['T2', 'Основной зал'],
                ['T3', 'Основной зал'],
                ['T4', 'Основной зал'],
                ['P1', 'Терраса'],
                ['P2', 'Терраса'],
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
