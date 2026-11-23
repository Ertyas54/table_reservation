<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            RestaurantSeeder::class,
            RestaurantTableSeeder::class,
            TimeSlotSeeder::class,
        ]);

        $pushkin = \App\Models\Restaurant::where('name', 'Пушкин')->first();
        if ($pushkin) {
            $owner   = \App\Models\User::where('email', 'owner@example.com')->value('id');
            $manager = \App\Models\User::where('email', 'manager@example.com')->value('id');
            if ($owner)   $pushkin->users()->attach($owner,   ['role' => 'owner']);
            if ($manager) $pushkin->users()->attach($manager, ['role' => 'manager']);
        }
    }
}
