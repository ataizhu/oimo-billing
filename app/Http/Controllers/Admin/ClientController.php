<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;

class ClientController extends Controller {
    public function index() {
        $clients = Client::query()
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return View::make('admin.clients.index', compact('clients'));
    }

    public function create() {
        return view('admin.clients.create');
    }

    public function store(Request $request) {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'domain' => 'required|string|max:255|unique:clients,domain',
            'database_name' => ['required', 'string', 'max:64', 'unique:clients,database_name', 'regex:/^[a-zA-Z][a-zA-Z0-9_]*$/'],
            'database_user' => ['required', 'string', 'max:64', 'unique:clients,database_user', 'regex:/^[a-zA-Z][a-zA-Z0-9_]*$/'],
            'database_password' => 'required|string|min:8',
            'admin_email' => 'required|email|unique:clients,admin_email',
            'admin_password' => 'required|string|min:8',
            'is_active' => 'boolean'
        ], [
            'database_name.regex' => 'Имя базы данных может содержать только буквы, цифры и знак подчеркивания, и должно начинаться с буквы',
            'database_user.regex' => 'Имя пользователя базы данных может содержать только буквы, цифры и знак подчеркивания, и должно начинаться с буквы'
        ]);

        try {
            DB::beginTransaction();

            // Создаем клиента
            $client = Client::create([
                'name' => $validated['name'],
                'domain' => $validated['domain'],
                'database_name' => $validated['database_name'],
                'database_user' => $validated['database_user'],
                'database_password' => Hash::make($validated['database_password']),
                'admin_email' => $validated['admin_email'],
                'admin_password' => Hash::make($validated['admin_password']),
                'is_active' => $request->boolean('is_active', true)
            ]);

            try {
                // Создаем базу данных для клиента
                DB::statement("CREATE DATABASE IF NOT EXISTS `{$client->database_name}`");

                // Создаем пользователя базы данных
                DB::statement("CREATE USER IF NOT EXISTS `{$client->database_user}`@`localhost` IDENTIFIED BY ?", [$validated['database_password']]);
                DB::statement("GRANT ALL PRIVILEGES ON `{$client->database_name}`.* TO `{$client->database_user}`@`localhost`");
                DB::statement("FLUSH PRIVILEGES");

                // Подключаемся к базе данных клиента
                config(['database.connections.client.database' => $client->database_name]);
                config(['database.connections.client.username' => $client->database_user]);
                config(['database.connections.client.password' => $validated['database_password']]);

                // Очищаем старое соединение
                DB::purge('client');
                DB::disconnect('client');

                // Устанавливаем новое соединение
                DB::reconnect('client');

                // Проверяем соединение
                try {
                    DB::connection('client')->getPdo();
                } catch (\Exception $e) {
                    throw new \Exception('Не удалось подключиться к базе данных клиента: ' . $e->getMessage());
                }

                // Запускаем миграции для базы данных клиента с таймаутом
                $migrateOutput = null;
                $migrateError = null;

                try {
                    $migrateOutput = Artisan::call('migrate', [
                        '--database' => 'client',
                        '--path' => 'database/migrations/tenant',
                        '--force' => true
                    ]);
                } catch (\Exception $e) {
                    $migrateError = $e->getMessage();
                }

                if ($migrateOutput !== 0 || $migrateError) {
                    throw new \Exception('Ошибка при выполнении миграций: ' . ($migrateError ?: Artisan::output()));
                }

                // Создаем администратора клиента
                try {
                    DB::connection('client')->table('users')->insert([
                        'name' => 'Admin',
                        'email' => $validated['admin_email'],
                        'password' => Hash::make($validated['admin_password']),
                        'created_at' => now(),
                        'updated_at' => now(),
                        'is_admin' => true
                    ]);
                } catch (\Exception $e) {
                    throw new \Exception('Ошибка при создании администратора: ' . $e->getMessage());
                }

                DB::commit();

                // Очищаем соединение после успешного создания
                DB::purge('client');
                DB::disconnect('client');

                return redirect("/admin/clients/{$client->id}")
                    ->with('success', 'Клиент успешно создан');

            } catch (\Exception $e) {
                // Если произошла ошибка при создании БД или пользователя,
                // пытаемся откатить изменения
                try {
                    // Очищаем соединение перед удалением
                    DB::purge('client');
                    DB::disconnect('client');

                    DB::statement("DROP DATABASE IF EXISTS `{$client->database_name}`");
                    DB::statement("DROP USER IF EXISTS `{$client->database_user}`@`localhost`");
                    DB::statement("FLUSH PRIVILEGES");
                } catch (\Exception $cleanupError) {
                    Log::error('Ошибка при очистке после неудачного создания клиента: ' . $cleanupError->getMessage());
                }
                throw $e;
            }
        } catch (\Exception $e) {
            DB::rollBack();

            // Очищаем все соединения при ошибке
            DB::purge('client');
            DB::disconnect('client');

            return back()
                ->withInput()
                ->withErrors(['error' => 'Ошибка при создании клиента: ' . $e->getMessage()]);
        }
    }

    public function show(Client $client) {
        // Проверяем существование базы данных
        $client->database_exists = $this->checkDatabaseExists($client->database_name);

        // Получаем статистику
        if ($client->database_exists) {
            try {
                config(['database.connections.client.database' => $client->database_name]);
                config(['database.connections.client.username' => $client->database_user]);
                config(['database.connections.client.password' => $client->database_password]);

                $client->users_count = DB::connection('client')->table('users')->count();
                $client->active_subscriptions_count = DB::connection('client')->table('subscriptions')
                    ->where('status', 'active')
                    ->count();
                $client->total_revenue = DB::connection('client')->table('payments')
                    ->where('status', 'completed')
                    ->sum('amount');
            } catch (\Exception $e) {
                // Игнорируем ошибки при получении статистики
            }
        }

        return view('admin.clients.show', compact('client'));
    }

    public function edit(Client $client) {
        return view('admin.clients.edit', compact('client'));
    }

    public function update(Request $request, Client $client) {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'domain' => ['required', 'string', 'max:255', Rule::unique('clients')->ignore($client->id)],
            'admin_email' => ['required', 'email', Rule::unique('clients')->ignore($client->id)],
            'database_password' => 'nullable|string|min:8',
            'admin_password' => 'nullable|string|min:8',
            'is_active' => 'boolean'
        ]);

        try {
            DB::beginTransaction();

            // Обновляем данные клиента
            $client->update([
                'name' => $validated['name'],
                'domain' => $validated['domain'],
                'admin_email' => $validated['admin_email'],
                'is_active' => $request->boolean('is_active', true)
            ]);

            // Обновляем пароль базы данных, если указан
            if (!empty($validated['database_password'])) {
                $client->database_password = Hash::make($validated['database_password']);
                $client->save();

                DB::statement("ALTER USER '{$client->database_user}'@'localhost' IDENTIFIED BY '{$validated['database_password']}'");
                DB::statement("FLUSH PRIVILEGES");
            }

            // Обновляем пароль администратора, если указан
            if (!empty($validated['admin_password'])) {
                if ($this->checkDatabaseExists($client->database_name)) {
                    config(['database.connections.client.database' => $client->database_name]);
                    config(['database.connections.client.username' => $client->database_user]);
                    config(['database.connections.client.password' => $client->database_password]);

                    DB::connection('client')->table('users')
                        ->where('email', $client->admin_email)
                        ->update(['password' => Hash::make($validated['admin_password'])]);
                }
            }

            DB::commit();

            return redirect()->route('admin.clients.show', $client)
                ->with('success', 'Клиент успешно обновлен');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Ошибка при обновлении клиента: ' . $e->getMessage());
        }
    }

    public function destroy(Client $client) {
        try {
            DB::beginTransaction();

            // Удаляем базу данных клиента
            if ($this->checkDatabaseExists($client->database_name)) {
                DB::statement("DROP DATABASE IF EXISTS {$client->database_name}");
            }

            // Удаляем пользователя базы данных
            DB::statement("DROP USER IF EXISTS '{$client->database_user}'@'localhost'");
            DB::statement("FLUSH PRIVILEGES");

            // Удаляем запись о клиенте
            $client->delete();

            DB::commit();

            return redirect()->route('admin.clients.index')
                ->with('success', 'Клиент успешно удален');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Ошибка при удалении клиента: ' . $e->getMessage());
        }
    }

    public function toggleStatus(Client $client) {
        $client->update(['is_active' => !$client->is_active]);

        return back()->with('success', 'Статус клиента успешно изменен');
    }

    public function resetDatabase(Client $client) {
        try {
            DB::beginTransaction();

            // Удаляем существующую базу данных
            if ($this->checkDatabaseExists($client->database_name)) {
                DB::statement("DROP DATABASE IF EXISTS `{$client->database_name}`");
            }

            // Создаем новую базу данных
            DB::statement("CREATE DATABASE `{$client->database_name}`");

            // Назначаем права
            DB::statement("GRANT ALL PRIVILEGES ON `{$client->database_name}`.* TO `{$client->database_user}`@`localhost`");
            DB::statement("FLUSH PRIVILEGES");

            // Подключаемся к базе данных клиента
            config(['database.connections.client.database' => $client->database_name]);
            config(['database.connections.client.username' => $client->database_user]);
            config(['database.connections.client.password' => $client->database_password]);

            // Очищаем старое соединение
            DB::purge('client');
            DB::disconnect('client');

            // Устанавливаем новое соединение
            DB::reconnect('client');

            // Проверяем соединение
            try {
                DB::connection('client')->getPdo();
            } catch (\Exception $e) {
                throw new \Exception('Не удалось подключиться к базе данных клиента: ' . $e->getMessage());
            }

            // Запускаем миграции
            $migrateOutput = Artisan::call('migrate', [
                '--database' => 'client',
                '--path' => 'database/migrations/tenant',
                '--force' => true
            ]);

            if ($migrateOutput !== 0) {
                throw new \Exception('Ошибка при выполнении миграций: ' . Artisan::output());
            }

            // Создаем администратора
            DB::connection('client')->table('users')->insert([
                'name' => 'Admin',
                'email' => $client->admin_email,
                'password' => $client->admin_password,
                'created_at' => now(),
                'updated_at' => now(),
                'is_admin' => true
            ]);

            DB::commit();

            // Очищаем соединение после успешного создания
            DB::purge('client');
            DB::disconnect('client');

            return back()->with('success', 'База данных клиента успешно сброшена');
        } catch (\Exception $e) {
            DB::rollBack();

            // Очищаем соединение при ошибке
            DB::purge('client');
            DB::disconnect('client');

            return back()->with('error', 'Ошибка при сбросе базы данных: ' . $e->getMessage());
        }
    }

    private function checkDatabaseExists($database) {
        try {
            DB::select("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = ?", [$database]);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}