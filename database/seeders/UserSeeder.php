<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'first_name'    => 'Иван',
            'last_name'     => 'Админов',
            'email'         => 'admin@example.com',
            'phone'         => '+79000000001',
            'password' => bcrypt('password'),
            'role'          => 'admin',
        ]);

        User::create([
            'first_name' => 'Олег', 'last_name' => 'Владельцев',
            'email' => 'owner@example.com', 'phone' => '+79000000010',
            'password' => bcrypt('password'), 'role' => 'owner',
        ]);

        User::create([
            'first_name' => 'Михаил', 'last_name' => 'Менеджеров',
            'email' => 'manager@example.com', 'phone' => '+79000000011',
            'password' => bcrypt('password'), 'role' => 'manager',
        ]);

        $clients = [
            ['Пётр',  'Петров',   'petr@example.com',  '+79000000002'],
            ['Анна',  'Сидорова', 'anna@example.com',  '+79000000003'],
            ['Мария', 'Иванова',  'maria@example.com', '+79000000004'],
            ['Олег',  'Кузнецов', 'oleg@example.com',  '+79000000005'],
        ];

        foreach ($clients as [$first, $last, $email, $phone]) {
            User::create([
                'first_name'    => $first,
                'last_name'     => $last,
                'email'         => $email,
                'phone'         => $phone,
                'password' => bcrypt('password'),
                'role'          => 'client',
            ]);
        }
    }
}
