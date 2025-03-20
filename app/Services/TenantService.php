<?php

namespace App\Services;

use App\Models\Client;
use App\Models\Domain;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;

class TenantService {
    public function createTenant(Client $client) {
        try {
            // Проверяем, существует ли уже тенант
            $existingTenant = DB::table('tenants')
                ->where('id', $client->database_name)
                ->first();

            if ($existingTenant) {
                Log::warning("Tenant {$client->database_name} already exists");
                return false;
            }

            // Создаем запись в таблице tenants
            DB::table('tenants')->insert([
                'id' => $client->database_name,
                'data' => json_encode([
                    'name' => $client->name,
                    'admin_email' => $client->admin_email,
                    'is_active' => $client->is_active,
                    'tenancy_db_name' => $client->database_name,
                    'tenancy_db_username' => $client->database_user,
                    'tenancy_db_password' => $client->database_password
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Создаем базу данных
            DB::statement("CREATE DATABASE IF NOT EXISTS `{$client->database_name}`");

            // Создаем пользователя MySQL
            DB::statement("CREATE USER IF NOT EXISTS '{$client->database_user}'@'%' IDENTIFIED BY '{$client->database_password}'");
            DB::statement("GRANT ALL PRIVILEGES ON `{$client->database_name}`.* TO '{$client->database_user}'@'%'");
            DB::statement("FLUSH PRIVILEGES");

            // Создаем домен
            $domain = new Domain();
            $domain->domain = $client->domain;
            $domain->tenant_id = $client->database_name;
            $domain->save();

            // Применяем миграции для базы данных клиента
            $this->runMigrations($client);

            // Создаем администратора
            $this->createAdminUser($client);

            return true;
        } catch (\Exception $e) {
            Log::error("Error creating tenant: " . $e->getMessage());
            return false;
        }
    }

    public function deleteTenant(Client $client) {
        try {
            Log::info("Starting tenant deletion process", [
                'tenant_id' => $client->database_name,
                'database' => $client->database_name,
                'user' => $client->database_user
            ]);

            // Удаляем запись из таблицы domains
            DB::table('domains')->where('tenant_id', $client->database_name)->delete();
            Log::info("Domain record deleted");

            // Удаляем запись из таблицы tenants
            DB::table('tenants')->where('id', $client->database_name)->delete();
            Log::info("Tenant record deleted");

            // Удаляем запись из таблицы users в основной базе данных
            DB::table('users')->where('email', $client->admin_email)->delete();
            Log::info("User record deleted from main database");

            // Удаляем базу данных
            if ($this->checkDatabaseExists($client->database_name)) {
                DB::statement("DROP DATABASE IF EXISTS `{$client->database_name}`");
                Log::info("Database deleted");
            }

            // Удаляем пользователя MySQL
            DB::statement("DROP USER IF EXISTS '{$client->database_user}'@'%'");
            DB::statement("FLUSH PRIVILEGES");
            Log::info("MySQL user deleted");

            Log::info("Tenant {$client->database_name} deleted successfully");
            return true;
        } catch (\Exception $e) {
            Log::error("Error deleting tenant: " . $e->getMessage());
            return false;
        }
    }

    public function restoreTenant(Client $client) {
        try {
            // Проверяем существование базы данных
            if (!$this->checkDatabaseExists($client->database_name)) {
                throw new \Exception('База данных клиента не найдена');
            }

            // Восстанавливаем запись в таблице tenants
            DB::table('tenants')->insert([
                'id' => $client->database_name,
                'data' => json_encode([
                    'name' => $client->name,
                    'admin_email' => $client->admin_email,
                    'is_active' => $client->is_active,
                    'tenancy_db_name' => $client->database_name,
                    'tenancy_db_username' => $client->database_user,
                    'tenancy_db_password' => $client->database_password
                ]),
                'created_at' => now(),
                'updated_at' => now()
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error("Error restoring tenant: " . $e->getMessage());
            return false;
        }
    }

    public function resetTenantDatabase(Client $client) {
        try {
            // Удаляем существующую базу данных
            if ($this->checkDatabaseExists($client->database_name)) {
                DB::statement("DROP DATABASE IF EXISTS `{$client->database_name}`");
            }

            // Создаем новую базу данных
            DB::statement("CREATE DATABASE `{$client->database_name}`");

            // Назначаем права
            DB::statement("GRANT ALL PRIVILEGES ON `{$client->database_name}`.* TO `{$client->database_user}`@`%`");
            DB::statement("FLUSH PRIVILEGES");

            // Применяем миграции
            $this->runMigrations($client);

            // Создаем администратора
            $this->createAdminUser($client);

            return true;
        } catch (\Exception $e) {
            Log::error("Error resetting tenant database: " . $e->getMessage());
            return false;
        }
    }

    private function runMigrations(Client $client) {
        try {
            // Подключаемся к базе данных клиента
            config(['database.connections.client' => [
                'driver' => 'mysql',
                'host' => config('database.connections.mysql.host'),
                'port' => config('database.connections.mysql.port'),
                'database' => $client->database_name,
                'username' => $client->database_user,
                'password' => $client->database_password,
                'charset' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'prefix' => '',
                'strict' => true,
                'engine' => null,
            ]]);

            // Очищаем старое соединение
            DB::purge('client');
            DB::disconnect('client');

            // Устанавливаем новое соединение
            DB::reconnect('client');

            // Запускаем миграции
            $migrateOutput = Artisan::call('migrate', [
                '--database' => 'client',
                '--path' => 'database/migrations/tenant',
                '--force' => true
            ]);

            if ($migrateOutput !== 0) {
                throw new \Exception('Ошибка при выполнении миграций: ' . Artisan::output());
            }

            return true;
        } catch (\Exception $e) {
            Log::error("Error running migrations: " . $e->getMessage());
            return false;
        }
    }

    private function createAdminUser(Client $client) {
        try {
            DB::connection('client')->table('users')->insert([
                'name' => 'Admin',
                'email' => $client->admin_email,
                'password' => Hash::make($client->admin_password),
                'is_admin' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error("Error creating admin user: " . $e->getMessage());
            return false;
        }
    }

    public function checkDatabaseExists($database) {
        try {
            $result = DB::select("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = ?", [$database]);
            return !empty($result);
        } catch (\Exception $e) {
            return false;
        }
    }
}