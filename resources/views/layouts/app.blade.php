<!DOCTYPE html>
<html lang="pt-BR" class="h-full bg-gray-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'PontoSmart') }} — @yield('title', 'Painel')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="manifest" href="/manifest.json">
</head>
<body class="h-full font-sans antialiased">

<div class="min-h-full" x-data="{ mobileOpen: false }">

    <nav class="bg-indigo-700 shadow-md">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">

                <div class="flex items-center gap-8">
                    <a href="{{ route('dashboard') }}" class="text-white font-bold text-lg tracking-tight">
                        PontoSmart
                    </a>
                    <div class="hidden md:flex items-center gap-1">
                        <a href="{{ route('dashboard') }}"
                           class="text-indigo-100 hover:bg-indigo-600 hover:text-white px-3 py-2 rounded-md text-sm font-medium transition">
                            Dashboard
                        </a>
                        @if(auth()->user()->isGestor())
                            <a href="{{ route('employees.index') }}"
                               class="text-indigo-100 hover:bg-indigo-600 hover:text-white px-3 py-2 rounded-md text-sm font-medium transition">
                                Funcionários
                            </a>
                        @endif
                        <a href="{{ route('worklogs.index') }}"
                           class="text-indigo-100 hover:bg-indigo-600 hover:text-white px-3 py-2 rounded-md text-sm font-medium transition">
                            Registros
                        </a>
                        <a href="{{ route('adjustments.index') }}"
                           class="text-indigo-100 hover:bg-indigo-600 hover:text-white px-3 py-2 rounded-md text-sm font-medium transition">
                            Ajustes
                        </a>
                    </div>
                </div>

                <div class="hidden md:flex items-center gap-4">
                    <span class="text-indigo-200 text-sm">{{ auth()->user()->name }}</span>
                    <span class="text-xs px-2 py-1 rounded-full font-medium
                        {{ auth()->user()->isGestor() ? 'bg-yellow-400 text-yellow-900' : 'bg-indigo-500 text-white' }}">
                        {{ auth()->user()->isGestor() ? 'Gestor' : 'Funcionário' }}
                    </span>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit"
                            class="text-indigo-200 hover:text-white text-sm underline underline-offset-2 transition">
                            Sair
                        </button>
                    </form>
                </div>

                <button @click="mobileOpen = !mobileOpen"
                    class="md:hidden text-indigo-200 hover:text-white p-2 rounded">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path x-show="!mobileOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M4 6h16M4 12h16M4 18h16"/>
                        <path x-show="mobileOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>

        <div x-show="mobileOpen" x-transition class="md:hidden px-4 pb-4 space-y-1">
            <a href="{{ route('dashboard') }}" class="block text-indigo-100 hover:bg-indigo-600 px-3 py-2 rounded text-sm">Dashboard</a>
            @if(auth()->user()->isGestor())
                <a href="{{ route('employees.index') }}" class="block text-indigo-100 hover:bg-indigo-600 px-3 py-2 rounded text-sm">Funcionários</a>
            @endif
            <a href="{{ route('worklogs.index') }}" class="block text-indigo-100 hover:bg-indigo-600 px-3 py-2 rounded text-sm">Registros</a>
            <a href="{{ route('adjustments.index') }}" class="block text-indigo-100 hover:bg-indigo-600 px-3 py-2 rounded text-sm">Ajustes</a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="block text-indigo-200 hover:text-white px-3 py-2 text-sm w-full text-left">Sair</button>
            </form>
        </div>
    </nav>

    @if(session('success'))
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
            <div class="bg-green-50 border border-green-200 text-green-800 rounded-lg px-4 py-3 flex items-center justify-between"
                 x-data="{ show: true }" x-show="show">
                <span class="text-sm">{{ session('success') }}</span>
                <button @click="show = false" class="text-green-600 hover:text-green-800 ml-4">✕</button>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
            <div class="bg-red-50 border border-red-200 text-red-800 rounded-lg px-4 py-3 flex items-center justify-between"
                 x-data="{ show: true }" x-show="show">
                <span class="text-sm">{{ session('error') }}</span>
                <button @click="show = false" class="text-red-600 hover:text-red-800 ml-4">✕</button>
            </div>
        </div>
    @endif

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        @yield('content')
    </main>

</div>

</body>
</html>