@extends('layouts.app')

@section('content')
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-2xl font-bold">Информация о клиенте</h2>
                        <div class="space-x-4">
                            <a href="{{ route('admin.clients.edit', $client) }}"
                                class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">
                                Редактировать
                            </a>
                            <a href="{{ route('admin.clients.index') }}"
                                class="bg-gray-600 text-white px-4 py-2 rounded-md hover:bg-gray-700">
                                Назад к списку
                            </a>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Основная информация</h3>
                            <dl class="grid grid-cols-1 gap-4">
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Название</dt>
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
                                    <dd class="mt-1 text-sm text-gray-900">{{ $client->created_at->format('d.m.Y H:i') }}
                                    </dd>
                                </div>
                            </dl>
                        </div>

                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">База данных</h3>
                            <dl class="grid grid-cols-1 gap-4">
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">База данных</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $client->database_name }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Пользователь</dt>
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

                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Администратор</h3>
                            <dl class="grid grid-cols-1 gap-4">
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Email</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $client->admin_email }}</dd>
                                </div>
                            </dl>
                        </div>

                        @if($client->database_exists)
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Статистика</h3>
                                <dl class="grid grid-cols-1 gap-4">
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Пользователей</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ $client->users_count ?? 0 }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Активных подписок</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ $client->active_subscriptions_count ?? 0 }}
                                        </dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Общая выручка</dt>
                                        <dd class="mt-1 text-sm text-gray-900">
                                            {{ number_format($client->total_revenue ?? 0, 2) }} ₸
                                        </dd>
                                    </div>
                                </dl>
                            </div>
                        @endif
                    </div>

                    <div class="mt-6 space-x-4">
                        <form action="{{ route('admin.clients.toggle-status', $client) }}" method="POST" class="inline">
                            @csrf
                            @method('POST')
                            <button type="submit" class="bg-yellow-600 text-white px-4 py-2 rounded-md hover:bg-yellow-700">
                                {{ $client->is_active ? 'Деактивировать' : 'Активировать' }}
                            </button>
                        </form>

                        <form action="{{ route('admin.clients.reset-database', $client) }}" method="POST" class="inline">
                            @csrf
                            @method('POST')
                            <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded-md hover:bg-red-700"
                                onclick="return confirm('Вы уверены? Это действие удалит все данные клиента.')">
                                Сбросить базу данных
                            </button>
                        </form>

                        <form action="{{ route('admin.clients.destroy', $client) }}" method="POST" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded-md hover:bg-red-700"
                                onclick="return confirm('Вы уверены? Это действие удалит клиента и его базу данных.')">
                                Удалить клиента
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection