@extends('layouts.app')

@section('content')
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-2xl font-bold">Мои подписки</h2>
                        <a href="{{ route('plans.index') }}"
                            class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition duration-200">
                            Новый план
                        </a>
                    </div>

                    @if($subscriptions->isEmpty())
                        <div class="text-center py-12">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                </path>
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">Нет активных подписок</h3>
                            <p class="mt-1 text-sm text-gray-500">Начните с выбора подходящего плана подписки.</p>
                            <div class="mt-6">
                                <a href="{{ route('plans.index') }}"
                                    class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                    Просмотреть планы
                                </a>
                            </div>
                        </div>
                    @else
                        <div class="space-y-6">
                            @foreach($subscriptions as $subscription)
                                <div class="bg-white border rounded-lg shadow-sm">
                                    <div class="p-6">
                                        <div class="flex items-center justify-between">
                                            <div>
                                                <h3 class="text-lg font-medium text-gray-900">{{ $subscription->plan->name }}</h3>
                                                <p class="mt-1 text-sm text-gray-500">{{ $subscription->plan->description }}</p>
                                            </div>
                                            <span class="px-3 py-1 rounded-full text-sm font-medium
                                                        @if($subscription->status === 'active') bg-green-100 text-green-800
                                                        @elseif($subscription->status === 'cancelled') bg-red-100 text-red-800
                                                        @else bg-yellow-100 text-yellow-800 @endif">
                                                {{ ucfirst($subscription->status) }}
                                            </span>
                                        </div>

                                        <div class="mt-4 grid grid-cols-2 gap-4">
                                            <div>
                                                <p class="text-sm text-gray-500">Стоимость</p>
                                                <p class="mt-1 text-lg font-medium text-gray-900">
                                                    ₸{{ number_format($subscription->plan->price, 2) }}</p>
                                                <p class="text-sm text-gray-500">
                                                    {{ $subscription->plan->billing_cycle === 'monthly' ? 'Ежемесячно' : 'Ежегодно' }}
                                                </p>
                                            </div>
                                            <div>
                                                <p class="text-sm text-gray-500">Следующий платеж</p>
                                                <p class="mt-1 text-lg font-medium text-gray-900">
                                                    {{ $subscription->ends_at->format('d.m.Y') }}</p>
                                                <p class="text-sm text-gray-500">Автопродление:
                                                    {{ $subscription->auto_renew ? 'Включено' : 'Выключено' }}</p>
                                            </div>
                                        </div>

                                        <div class="mt-6 flex space-x-4">
                                            <a href="{{ route('subscriptions.show', $subscription) }}"
                                                class="text-blue-600 hover:text-blue-900">
                                                Подробнее
                                            </a>
                                            @if($subscription->status === 'active')
                                                <form action="{{ route('subscriptions.cancel', $subscription) }}" method="POST"
                                                    class="inline">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="text-red-600 hover:text-red-900"
                                                        onclick="return confirm('Вы уверены, что хотите отменить подписку?')">
                                                        Отменить
                                                    </button>
                                                </form>
                                            @elseif($subscription->status === 'cancelled')
                                                <form action="{{ route('subscriptions.resume', $subscription) }}" method="POST"
                                                    class="inline">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="text-green-600 hover:text-green-900">
                                                        Возобновить
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection