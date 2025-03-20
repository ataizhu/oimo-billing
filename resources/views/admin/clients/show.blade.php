@extends('layouts.app')

@section('content')
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-2xl font-bold">Информация о клиенте</h2>
                        <div class="space-x-4">
                            <a href="{{ route('admin.clients.edit', $client) }}"
                                class="text-blue-600 hover:text-blue-900">Редактировать</a>
                            <a href="{{ route('admin.clients.index') }}" class="text-gray-600 hover:text-gray-900">← Назад к
                                списку</a>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <!-- Основная информация -->
                        <div class="space-y-6">
                            <div class="bg-gray-50 p-6 rounded-lg">
                                <h3 class="text-lg font-semibold mb-4">Основная информация</h3>
                                <dl class="space-y-4">
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">ID</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ $client->id }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Название компании</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ $client->name }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Домен</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ $client->domain }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Статус</dt>
                                        <dd class="mt-1">
                                            <span
                                                class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $client->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                                {{ $client->is_active ? 'Активен' : 'Неактивен' }}
                                            </span>
                                        </dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Создан</dt>
                                        <dd class="mt-1 text-sm text-gray-900">
                                            {{ $client->created_at->format('d.m.Y H:i') }}</dd>
                                    </div>
                                </dl>
                            </div>

                            <!-- Информация о базе данных -->
                            <div class="bg-gray-50 p-6 rounded-lg">
                                <h3 class="text-lg font-semibold mb-4">База данных</h3>
                                <dl class="space-y-4">
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Имя базы данных</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ $client->database_name }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Пользователь БД</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ $client->database_user }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Статус БД</dt>
                                        <dd class="mt-1">
                                            <span
                                                class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $client->database_exists ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                                {{ $client->database_exists ? 'Существует' : 'Не существует' }}
                                            </span>
                                        </dd>
                                    </div>
                                </dl>
                            </div>
                        </div>

                        <!-- Статистика -->
                        <div class="space-y-6">
                            <div class="bg-gray-50 p-6 rounded-lg">
                                <h3 class="text-lg font-semibold mb-4">Статистика</h3>
                                <dl class="space-y-4">
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Количество пользователей</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ $client->users_count ?? 0 }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Активные подписки</dt>
                                        <dd class="mt-1 text-sm text-gray-900">
                                            {{ $client->active_subscriptions_count ?? 0 }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Общий доход</dt>
                                        <dd class="mt-1 text-sm text-gray-900">
                                            ₸{{ number_format($client->total_revenue ?? 0, 2) }}</dd>
                                    </div>
                                </dl>
                            </div>

                            <!-- Действия -->
                            <div class="bg-white border rounded-lg p-6">
                                <h3 class="text-lg font-semibold mb-4">Действия</h3>
                                <div class="space-y-4">
                                    <form action="{{ route('admin.clients.toggle-status', $client) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit"
                                            class="w-full {{ $client->is_active ? 'bg-red-600 hover:bg-red-700' : 'bg-green-600 hover:bg-green-700' }} text-white px-4 py-2 rounded-lg transition duration-200">
                                            {{ $client->is_active ? 'Деактивировать клиента' : 'Активировать клиента' }}
                                        </button>
                                    </form>

                                    <form action="{{ route('admin.clients.reset-database', $client) }}" method="POST">
                                        @csrf
                                        @method('POST')
                                        <button type="submit"
                                            class="w-full bg-yellow-600 text-white px-4 py-2 rounded-lg hover:bg-yellow-700 transition duration-200"
                                            onclick="return confirm('Вы уверены? Это действие сбросит базу данных клиента.')">
                                            Сбросить базу данных
                                        </button>
                                    </form>

                                    <form action="{{ route('admin.clients.destroy', $client) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            class="w-full bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition duration-200"
                                            onclick="return confirm('Вы уверены? Это действие удалит базу данных клиента.')">
                                            Удалить клиента
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection