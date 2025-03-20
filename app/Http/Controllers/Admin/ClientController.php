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
use App\Models\Domain;
use App\Models\User;
use App\Services\TenantService;

class ClientController extends Controller {
    protected $tenantService;

    public function __construct(TenantService $tenantService) {
        $this->tenantService = $tenantService;
    }

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
            'database_name' => 'required|string|max:255|unique:clients,database_name',
            'database_user' => 'required|string|max:255|unique:clients,database_user',
            'database_password' => 'required|string|min:8',
            'admin_email' => 'required|email',
            'admin_password' => 'required|string|min:8',
        ]);

        try {
            DB::beginTransaction();

            $client = Client::create($validated);

            // Создаем тенант
            if (!$this->tenantService->createTenant($client)) {
                throw new \Exception('Failed to create tenant');
            }

            DB::commit();

            return redirect()->route('admin.clients.index')
                ->with('success', 'Клиент успешно создан');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating client: ' . $e->getMessage());
            return back()->with('error', 'Ошибка при создании клиента: ' . $e->getMessage());
        }
    }

    public function show(Client $client) {
        // Проверяем существование базы данных
        $client->database_exists = $this->tenantService->checkDatabaseExists($client->database_name);

        // Получаем статистику
        if ($client->database_exists) {
            try {
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

                DB::statement("ALTER USER '{$client->database_user}'@'%' IDENTIFIED BY '{$validated['database_password']}'");
                DB::statement("FLUSH PRIVILEGES");
            }

            // Обновляем пароль администратора, если указан
            if (!empty($validated['admin_password'])) {
                if ($this->tenantService->checkDatabaseExists($client->database_name)) {
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

            // Отключаем внешние ключи для удаления записи из tenants
            DB::statement('SET FOREIGN_KEY_CHECKS=0');

            // Удаляем запись из таблицы tenants
            DB::table('tenants')->where('id', $client->database_name)->delete();

            // Включаем обратно внешние ключи
            DB::statement('SET FOREIGN_KEY_CHECKS=1');

            // Мягкое удаление клиента
            $client->delete();

            DB::commit();

            return redirect()->route('admin.clients.index')
                ->with('success', 'Клиент перемещен в корзину');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Ошибка при удалении клиента: ' . $e->getMessage());

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

            if (!$this->tenantService->resetTenantDatabase($client)) {
                throw new \Exception('Failed to reset tenant database');
            }

            DB::commit();

            return back()->with('success', 'База данных клиента успешно сброшена');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Ошибка при сбросе базы данных: ' . $e->getMessage());
        }
    }

    public function trash() {
        $clients = Client::onlyTrashed()
            ->orderBy('deleted_at', 'desc')
            ->paginate(10);

        return view('admin.clients.trash', compact('clients'));
    }

    public function restore($id) {
        try {
            DB::beginTransaction();

            $client = Client::onlyTrashed()->findOrFail($id);

            if (!$this->tenantService->restoreTenant($client)) {
                throw new \Exception('Failed to restore tenant');
            }

            // Восстанавливаем клиента
            $client->restore();

            DB::commit();

            return redirect()->route('admin.clients.show', $client)
                ->with('success', 'Клиент успешно восстановлен');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Ошибка при восстановлении клиента: ' . $e->getMessage());

            return back()->with('error', 'Ошибка при восстановлении клиента: ' . $e->getMessage());
        }
    }

    public function forceDelete($id) {
        try {
            $client = Client::withTrashed()->findOrFail($id);
            Log::info('Начинаем полное удаление клиента', ['client_id' => $id]);

            if (!$this->tenantService->deleteTenant($client)) {
                throw new \Exception('Failed to delete tenant');
            }

            // Удаляем клиента
            $client->forceDelete();
            Log::info('Клиент успешно удален');

            return redirect()->route('admin.clients.index')->with('success', 'Клиент успешно удален');
        } catch (\Exception $e) {
            Log::error('Error deleting client: ' . $e->getMessage());
            return back()->with('error', 'Ошибка при удалении клиента: ' . $e->getMessage());
        }
    }
}