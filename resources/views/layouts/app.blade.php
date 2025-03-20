<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="font-sans antialiased">
    <div class="min-h-screen bg-gray-100">
        <nav class="bg-white border-b border-gray-100">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex">
                        <div class="flex-shrink-0 flex items-center">
                            <a href="{{ route('home') }}" class="text-xl font-bold text-gray-800">
                                {{ config('app.name', 'Laravel') }}
                            </a>
                        </div>

                        @auth
                            <div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex">
                                <a href="{{ route('plans.index') }}"
                                    class="text-gray-700 hover:text-gray-900 px-3 py-2">Тарифы</a>
                                <a href="{{ route('subscriptions.index') }}"
                                    class="text-gray-700 hover:text-gray-900 px-3 py-2">Подписки</a>
                                <a href="{{ route('invoices.index') }}"
                                    class="text-gray-700 hover:text-gray-900 px-3 py-2">Счета</a>
                                <a href="{{ route('payments.index') }}"
                                    class="text-gray-700 hover:text-gray-900 px-3 py-2">Платежи</a>
                            </div>
                        @endauth
                    </div>

                    <div class="flex items-center">
                        @auth
                            <div class="hidden sm:flex sm:items-center sm:ml-6">
                                <div class="relative" x-data="{ open: false }">
                                    <div class="flex items-center space-x-4">
                                        @if(Auth::user()->is_admin)
                                            <a href="{{ route('admin.clients.index') }}"
                                                class="text-gray-700 hover:text-gray-900">Клиенты</a>
                                        @endif
                                        <form method="POST" action="{{ route('logout') }}">
                                            @csrf
                                            <button type="submit" class="text-gray-700 hover:text-gray-900">
                                                Выйти
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @endauth
                    </div>
                </div>
            </div>
        </nav>

        <!-- Page Content -->
        <main>
            @yield('content')
        </main>
    </div>
</body>

</html>