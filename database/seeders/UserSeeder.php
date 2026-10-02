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
            'password_hash' => bcrypt('password'),
            'role'          => 'admin',
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
                'password_hash' => bcrypt('password'),
                'role'          => 'client',
            ]);
        }
    }
}
