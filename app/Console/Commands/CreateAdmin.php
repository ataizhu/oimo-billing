<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class CreateAdmin extends Command {
    protected $signature = 'admin:create {email} {password}';
    protected $description = 'Создание администратора системы';

    public function handle() {
        $email = $this->argument('email');
        $password = $this->argument('password');

        if (User::where('email', $email)->exists()) {
            $this->error('Пользователь с таким email уже существует');
            return 1;
        }

        User::create([
            'name' => 'Admin',
            'email' => $email,
            'password' => Hash::make($password),
            'is_admin' => true
        ]);

        $this->info('Администратор успешно создан');
        return 0;
    }
}