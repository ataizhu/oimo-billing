<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\Client;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder {
    /**
     * Seed the application's database.
     */
    public function run(): void {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        User::create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'is_admin' => true,
        ]);

        // Создаем тестовых клиентов
        $clients = [
            [
                'name' => 'ООО "Первая Компания"',
                'domain' => 'client1.oimo-billing.test',
                'database_name' => 'client1_db',
                'database_user' => 'client1_user',
                'database_password' => Str::random(16),
                'is_active' => true,
            ],
            [
                'name' => 'ИП Иванов',
                'domain' => 'client2.oimo-billing.test',
                'database_name' => 'client2_db',
                'database_user' => 'client2_user',
                'database_password' => Str::random(16),
                'is_active' => true,
            ],
            [
                'name' => 'ООО "Тестовая Компания"',
                'domain' => 'client3.oimo-billing.test',
                'database_name' => 'client3_db',
                'database_user' => 'client3_user',
                'database_password' => Str::random(16),
                'is_active' => false,
            ],
        ];

        foreach ($clients as $client) {
            Client::create($client);
        }
    }
}
