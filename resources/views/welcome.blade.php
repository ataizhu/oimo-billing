@extends('layouts.app')

@section('content')
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h1 class="text-2xl font-bold mb-4">Добро пожаловать в систему биллинга</h1>
                    <p class="mb-4">Здесь вы можете управлять подписками и платежами.</p>

                    @guest
                        <div class="mt-6">
                            <a href="{{ route('login') }}"
                                class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">
                                Войти в систему
                            </a>
                        </div>
                    @endguest
                </div>
            </div>
        </div>
    </div>
@endsection