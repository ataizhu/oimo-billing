@extends('layouts.app')

@section('content')
    <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <div class="px-4 py-6 sm:px-0">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-semibold text-gray-900">Создать новый план</h2>
                <a href="{{ route('plans.index') }}" class="text-blue-600 hover:text-blue-900">
                    Вернуться к списку
                </a>
            </div>

            <div class="bg-white shadow sm:rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <form action="{{ route('plans.store') }}" method="POST">
                        @csrf

                        <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
                            <div class="sm:col-span-4">
                                <label for="name" class="block text-sm font-medium text-gray-700">Название</label>
                                <div class="mt-1">
                                    <input type="text" name="name" id="name" value="{{ old('name') }}"
                                        class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md">
                                </div>
                                @error('name')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="sm:col-span-6">
                                <label for="description" class="block text-sm font-medium text-gray-700">Описание</label>
                                <div class="mt-1">
                                    <textarea name="description" id="description" rows="3"
                                        class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md">{{ old('description') }}</textarea>
                                </div>
                                @error('description')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="sm:col-span-2">
                                <label for="price" class="block text-sm font-medium text-gray-700">Цена</label>
                                <div class="mt-1 relative rounded-md shadow-sm">
                                    <input type="number" name="price" id="price" value="{{ old('price') }}" step="0.01"
                                        class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md">
                                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                        <span class="text-gray-500 sm:text-sm">₽</span>
                                    </div>
                                </div>
                                @error('price')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="sm:col-span-2">
                                <label for="billing_period" class="block text-sm font-medium text-gray-700">Период
                                    оплаты</label>
                                <div class="mt-1">
                                    <select name="billing_period" id="billing_period"
                                        class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md">
                                        <option value="monthly" {{ old('billing_period') == 'monthly' ? 'selected' : '' }}>
                                            Ежемесячно</option>
                                        <option value="yearly" {{ old('billing_period') == 'yearly' ? 'selected' : '' }}>
                                            Ежегодно</option>
                                    </select>
                                </div>
                                @error('billing_period')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="sm:col-span-6">
                                <label for="features" class="block text-sm font-medium text-gray-700">Возможности</label>
                                <div class="mt-1">
                                    <textarea name="features" id="features" rows="4"
                                        class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md"
                                        placeholder="Введите возможности, по одной в строке">{{ old('features') }}</textarea>
                                    <p class="mt-2 text-sm text-gray-500">Введите каждую возможность с новой строки</p>
                                </div>
                                @error('features')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="sm:col-span-6">
                                <div class="flex items-start">
                                    <div class="flex items-center h-5">
                                        <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}
                                            class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300 rounded">
                                    </div>
                                    <div class="ml-3 text-sm">
                                        <label for="is_active" class="font-medium text-gray-700">Активный план</label>
                                        <p class="text-gray-500">План будет доступен для выбора пользователями</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-5">
                            <button type="submit"
                                class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                Создать план
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection