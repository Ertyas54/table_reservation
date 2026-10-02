<?php

namespace Database\Seeders;

use App\Models\Restaurant;
use Illuminate\Database\Seeder;

class RestaurantSeeder extends Seeder
{
    public function run(): void
    {
        $restaurants = [
            [
                'name'         => 'Пушкин',
                'description'  => 'Русская кухня в центре Москвы',
                'city'         => 'Москва',
                'address'      => 'Тверской бульвар, 26А',
                'phone'        => '+74950000001',
                'email'        => 'pushkin@example.com',
                'opening_time' => '10:00',
                'closing_time' => '23:00',
                'is_active'    => true,
            ],
            [
                'name'         => 'Турандот',
                'description'  => 'Китайская и европейская кухня',
                'city'         => 'Москва',
                'address'      => 'Тверской бульвар, 26',
                'phone'        => '+74950000002',
                'email'        => 'turandot@example.com',
                'opening_time' => '12:00',
                'closing_time' => '23:59',
                'is_active'    => true,
            ],
            [
                'name'         => 'Причал',
                'description'  => 'Морской ресторан на набережной',
                'city'         => 'Санкт-Петербург',
                'address'      => 'Адмиралтейская набережная, 4',
                'phone'        => '+78120000003',
                'email'        => 'prichal@example.com',
                'opening_time' => '11:00',
                'closing_time' => '22:00',
                'is_active'    => true,
            ],
        ];

        foreach ($restaurants as $data) {
            Restaurant::create($data);
        }
    }
}
