@extends('layouts.app')

@section('content')
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-2xl font-bold">Детали подписки</h2>
                        <a href="{{ route('subscriptions.index') }}" class="text-blue-600 hover:text-blue-900">
                            ← Назад к списку
                        </a>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <!-- Основная информация -->
                        <div class="space-y-6">
                            <div class="bg-gray-50 p-6 rounded-lg">
                                <h3 class="text-lg font-semibold mb-4">Информация о подписке</h3>
                                <dl class="space-y-4">
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Статус</dt>
                                        <dd class="mt-1">
                                            <span class="px-3 py-1 rounded-full text-sm font-medium
                                                    @if($subscription->status === 'active') bg-green-100 text-green-800
                                                    @elseif($subscription->status === 'cancelled') bg-red-100 text-red-800
                                                    @else bg-yellow-100 text-yellow-800 @endif">
                                                {{ ucfirst($subscription->status) }}
                                            </span>
                                        </dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">План</dt>
                                        <dd class="mt-1 text-lg font-medium text-gray-900">{{ $subscription->plan->name }}
                                        </dd>
                                        <dd class="mt-1 text-sm text-gray-500">{{ $subscription->plan->description }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Стоимость</dt>
                                        <dd class="mt-1 text-lg font-medium text-gray-900">
                                            ₸{{ number_format($subscription->plan->price, 2) }}</dd>
                                        <dd class="mt-1 text-sm text-gray-500">
                                            {{ $subscription->plan->billing_cycle === 'monthly' ? 'Ежемесячно' : 'Ежегодно' }}
                                        </dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Дата начала</dt>
                                        <dd class="mt-1 text-gray-900">{{ $subscription->starts_at->format('d.m.Y') }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Дата окончания</dt>
                                        <dd class="mt-1 text-gray-900">{{ $subscription->ends_at->format('d.m.Y') }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Автопродление</dt>
                                        <dd class="mt-1 text-gray-900">
                                            {{ $subscription->auto_renew ? 'Включено' : 'Выключено' }}
                                        </dd>
                                    </div>
                                </dl>
                            </div>

                            <!-- Действия -->
                            <div class="bg-white border rounded-lg p-6">
                                <h3 class="text-lg font-semibold mb-4">Действия</h3>
                                <div class="space-y-4">
                                    @if($subscription->status === 'active')
                                        <form action="{{ route('subscriptions.cancel', $subscription) }}" method="POST">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit"
                                                class="w-full bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition duration-200"
                                                onclick="return confirm('Вы уверены, что хотите отменить подписку?')">
                                                Отменить подписку
                                            </button>
                                        </form>
                                    @elseif($subscription->status === 'cancelled')
                                        <form action="{{ route('subscriptions.resume', $subscription) }}" method="POST">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit"
                                                class="w-full bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition duration-200">
                                                Возобновить подписку
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- История платежей -->
                        <div>
                            <div class="bg-white border rounded-lg p-6">
                                <h3 class="text-lg font-semibold mb-4">История платежей</h3>
                                @if($subscription->invoices->isEmpty())
                                    <p class="text-gray-500">Пока нет платежей</p>
                                @else
                                    <div class="space-y-4">
                                        @foreach($subscription->invoices as $invoice)
                                            <div class="flex items-center justify-between py-3 border-b">
                                                <div>
                                                    <p class="font-medium text-gray-900">Счет #{{ $invoice->number }}</p>
                                                    <p class="text-sm text-gray-500">{{ $invoice->created_at->format('d.m.Y') }}</p>
                                                </div>
                                                <div class="text-right">
                                                    <p class="font-medium text-gray-900">₸{{ number_format($invoice->amount, 2) }}
                                                    </p>
                                                    <p
                                                        class="text-sm {{ $invoice->status === 'paid' ? 'text-green-600' : 'text-red-600' }}">
                                                        {{ $invoice->status === 'paid' ? 'Оплачен' : 'Не оплачен' }}
                                                    </p>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection