@extends('layouts.app')

@section('content')
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h2 class="text-2xl font-bold mb-6">Оформление подписки</h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <!-- Информация о плане -->
                        <div class="bg-gray-50 p-6 rounded-lg">
                            <h3 class="text-xl font-semibold mb-4">Выбранный план</h3>
                            <div class="space-y-4">
                                <div>
                                    <span class="text-gray-600">Название:</span>
                                    <span class="font-semibold">{{ $plan->name }}</span>
                                </div>
                                <div>
                                    <span class="text-gray-600">Описание:</span>
                                    <p class="mt-1">{{ $plan->description }}</p>
                                </div>
                                <div>
                                    <span class="text-gray-600">Стоимость:</span>
                                    <span class="font-semibold">₸{{ number_format($plan->price, 2) }}</span>
                                    <span class="text-gray-500">/{{ $plan->billing_cycle }}</span>
                                </div>
                                <div>
                                    <span class="text-gray-600">Период:</span>
                                    <span
                                        class="font-semibold">{{ $plan->billing_cycle === 'monthly' ? 'Ежемесячно' : 'Ежегодно' }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Форма оформления -->
                        <div>
                            <form action="{{ route('subscriptions.store') }}" method="POST">
                                @csrf
                                <input type="hidden" name="plan_id" value="{{ $plan->id }}">

                                <div class="space-y-6">
                                    <div>
                                        <label for="payment_method" class="block text-sm font-medium text-gray-700">Способ
                                            оплаты</label>
                                        <select name="payment_method" id="payment_method"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                            <option value="card">Банковская карта</option>
                                            <option value="bank_transfer">Банковский перевод</option>
                                        </select>
                                    </div>

                                    <div>
                                        <label for="coupon" class="block text-sm font-medium text-gray-700">Промокод (если
                                            есть)</label>
                                        <input type="text" name="coupon" id="coupon"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    </div>

                                    <div class="flex items-center">
                                        <input type="checkbox" name="auto_renew" id="auto_renew"
                                            class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                        <label for="auto_renew" class="ml-2 block text-sm text-gray-900">
                                            Автоматически продлевать подписку
                                        </label>
                                    </div>

                                    <div>
                                        <button type="submit"
                                            class="w-full bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition duration-200">
                                            Оформить подписку
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection