@extends('layouts.app')

@section('content')
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h2 class="text-2xl font-bold mb-6">Планы подписки</h2>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        @foreach($plans as $plan)
                            <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                                <div class="px-6 py-8">
                                    <h3 class="text-2xl font-bold text-gray-900">{{ $plan->name }}</h3>
                                    <p class="mt-4 text-gray-500">{{ $plan->description }}</p>
                                    <div class="mt-8">
                                        <span
                                            class="text-4xl font-bold text-gray-900">₸{{ number_format($plan->price, 2) }}</span>
                                        <span class="text-gray-500">/{{ $plan->billing_cycle }}</span>
                                    </div>
                                    <ul class="mt-8 space-y-4">
                                        @foreach($plan->features as $feature)
                                            <li class="flex items-center">
                                                <svg class="h-5 w-5 text-green-500" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M5 13l4 4L19 7"></path>
                                                </svg>
                                                <span class="ml-3 text-gray-700">{{ $feature }}</span>
                                            </li>
                                        @endforeach
                                    </ul>
                                    <div class="mt-8">
                                        <a href="{{ route('subscriptions.create', ['plan' => $plan->id]) }}"
                                            class="block w-full bg-blue-600 text-white text-center px-6 py-3 rounded-lg hover:bg-blue-700 transition duration-200">
                                            Выбрать план
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection