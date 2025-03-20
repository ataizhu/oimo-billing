@extends('layouts.app')

@section('content')
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-2xl font-bold">Создание клиента</h2>
                        <a href="{{ route('admin.clients.index') }}"
                            class="bg-gray-600 text-white px-4 py-2 rounded-md hover:bg-gray-700">
                            Назад к списку
                        </a>
                    </div>

                    <form action="{{ route('admin.clients.store') }}" method="POST">
                        @csrf
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700">Название</label>
                                <input type="text" name="name" id="name" value="{{ old('name') }}"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                @error('name')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="domain" class="block text-sm font-medium text-gray-700">Домен</label>
                                <input type="text" name="domain" id="domain" value="{{ old('domain') }}"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                @error('domain')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="database_name" class="block text-sm font-medium text-gray-700">База
                                    данных</label>
                                <input type="text" name="database_name" id="database_name"
                                    value="{{ old('database_name') }}"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                @error('database_name')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="database_user" class="block text-sm font-medium text-gray-700">Пользователь
                                    БД</label>
                                <input type="text" name="database_user" id="database_user"
                                    value="{{ old('database_user') }}"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                @error('database_user')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="database_password" class="block text-sm font-medium text-gray-700">Пароль
                                    БД</label>
                                <input type="password" name="database_password" id="database_password"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                @error('database_password')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="admin_email" class="block text-sm font-medium text-gray-700">Email
                                    администратора</label>
                                <input type="email" name="admin_email" id="admin_email" value="{{ old('admin_email') }}"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                @error('admin_email')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="admin_password" class="block text-sm font-medium text-gray-700">Пароль
                                    администратора</label>
                                <input type="password" name="admin_password" id="admin_password"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                @error('admin_password')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="is_active" class="block text-sm font-medium text-gray-700">Статус</label>
                                <select name="is_active" id="is_active"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="1" {{ old('is_active', true) ? 'selected' : '' }}>Активен</option>
                                    <option value="0" {{ old('is_active', true) ? '' : 'selected' }}>Неактивен</option>
                                </select>
                            </div>
                        </div>

                        <div class="mt-6">
                            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">
                                Создать клиента
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection