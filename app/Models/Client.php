<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Tenant;
use Stancl\Tenancy\Database\Models\Domain;
use Stancl\Tenancy\Facades\Tenancy;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class Client extends Model {
    protected $fillable = [
        'name',
        'domain',
        'database_name',
        'database_user',
        'database_password',
        'admin_email',
        'admin_password',
        'is_active'
    ];

    protected $hidden = [
        'database_password',
        'admin_password'
    ];

    protected $casts = [
        'is_active' => 'boolean'
    ];

    protected static function booted() {
        static::created(function ($client) {
            try {
                Log::info('Starting tenant creation process', [
                    'client_name' => $client->name,
                    'database_name' => $client->database_name,
                    'domain' => $client->domain
                ]);

                // Создаем базу данных для тенанта
                DB::statement("CREATE DATABASE IF NOT EXISTS {$client->database_name}");
                DB::statement("CREATE USER IF NOT EXISTS '{$client->database_user}'@'%' IDENTIFIED BY '{$client->database_password}'");
                DB::statement("GRANT ALL PRIVILEGES ON {$client->database_name}.* TO '{$client->database_user}'@'%'");
                DB::statement("FLUSH PRIVILEGES");

                $tenant = $client->createTenant();
                Log::info('Tenant created', ['tenant' => $tenant]);

                Log::info("Creating domain {$client->domain} for tenant {$tenant->id}");
                Domain::create([
                    'domain' => $client->domain,
                    'tenant_id' => $tenant->id
                ]);
                Log::info('Domain created');

                Log::info('Running migrations for tenant');
                tenancy()->initialize($tenant);
                config(['database.connections.tenant.database' => $client->database_name]);
                config(['database.connections.tenant.username' => $client->database_user]);
                config(['database.connections.tenant.password' => $client->database_password]);
                DB::purge('tenant');
                Artisan::call('migrate', [
                    '--force' => true,
                    '--path' => 'database/migrations/tenant',
                    '--database' => 'tenant'
                ]);
                Log::info('Migrations completed');

                Log::info('Creating admin user for tenant');
                User::create([
                    'name' => 'Admin',
                    'email' => $client->admin_email,
                    'password' => Hash::make($client->admin_password),
                    'is_admin' => true
                ]);
                Log::info('Admin user created');

            } catch (\Exception $e) {
                Log::error('Failed to create tenant', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                throw $e;
            }
        });
    }

    protected function createTenantDatabase(Tenant $tenant) {
        // Получаем данные из базы напрямую
        $tenantData = DB::table('tenants')->where('id', $tenant->id)->first();
        $data = json_decode($tenantData->data, true);

        Log::info('Creating tenant database:', ['database' => $data['tenancy_db_name']]);

        try {
            // Создаем базу данных
            DB::statement("CREATE DATABASE IF NOT EXISTS {$data['tenancy_db_name']}");

            // Создаем пользователя
            DB::statement("CREATE USER IF NOT EXISTS '{$data['tenancy_db_username']}'@'%' IDENTIFIED BY '{$data['tenancy_db_password']}'");

            // Даем права пользователю
            DB::statement("GRANT ALL PRIVILEGES ON {$data['tenancy_db_name']}.* TO '{$data['tenancy_db_username']}'@'%'");

            // Применяем права
            DB::statement('FLUSH PRIVILEGES');

            Log::info('Tenant database created successfully');

            // Инициализируем тенанта
            tenancy()->initialize($tenant);

            // Запускаем миграции
            Log::info('Running migrations for tenant');
            Artisan::call('tenants:migrate', [
                '--tenants' => [$tenant->id],
            ]);
            Log::info('Migrations completed successfully');

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to create tenant database:', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    protected function createTenant() {
        $data = [
            'name' => $this->name,
            'admin_email' => $this->admin_email,
            'is_active' => $this->is_active,
            'tenancy_db_name' => $this->database_name,
            'tenancy_db_username' => $this->database_user,
            'tenancy_db_password' => $this->database_password
        ];

        Log::info('Creating tenant with data:', $data);

        try {
            // Создаем запись тенанта
            DB::table('tenants')->insert([
                'id' => $this->database_name,
                'data' => json_encode($data),
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // Получаем созданного тенанта
            $tenant = Tenant::find($this->database_name);

            // Создаем базу данных для тенанта
            $this->createTenantDatabase($tenant);

            Log::info('Tenant created successfully:', ['tenant_id' => $tenant->id]);
            return $tenant;
        } catch (\Exception $e) {
            Log::error('Failed to create tenant:', ['error' => $e->getMessage()]);
            throw $e;
        }
    }
}