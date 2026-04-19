{{--
    Layout: app.blade.php — Layout principal autenticado do HoraSmart.

    Estrutura:
     - Navbar fixa com glassmorphism + gradiente indigo→violet
     - Links de navegação dinâmicos por role (gestor vê "Funcionários")
     - Dropdown de perfil com Alpine.js
     - Menu mobile responsivo com Alpine.js
     - Flash messages (success/error) com auto-dismiss
     - Slot para conteúdo via @yield('content')
     - PWA: registra service worker + meta tags mobile
     - Favicon SVG customizado

    Tecnologias: Blade Template Engine, Alpine.js (menu, dropdown), Tailwind CSS, Vite, PWA
    Dependências: resources/css/app.css, resources/js/app.js
--}}
<!DOCTYPE html>
<html lang="pt-BR" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="HoraSmart — Sistema de RH e Controle de Jornada de Trabalho">
    <meta name="theme-color" content="#4f46e5">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <title>{{ config('app.name', 'HoraSmart') }} — @yield('title', 'Painel')</title>
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    <link rel="manifest" href="/manifest.json">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full bg-slate-50 antialiased">

<div class="min-h-full flex flex-col" x-data="{ mobileOpen: false, profileOpen: false }">

    {{-- Navbar --}}
    <nav class="bg-gradient-to-r from-indigo-700 via-indigo-600 to-violet-600 shadow-lg shadow-indigo-500/20 sticky top-0 z-50 backdrop-blur-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">

                {{-- Logo + Links --}}
                <div class="flex items-center gap-8">
                    <a href="{{ route('dashboard') }}" class="flex items-center gap-2.5 group">
                        <div class="w-9 h-9 bg-white/20 rounded-xl flex items-center justify-center backdrop-blur-sm group-hover:bg-white/30 transition-all group-hover:scale-105">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <span class="text-white font-bold text-lg tracking-tight">HoraSmart</span>
                    </a>

                    <div class="hidden md:flex items-center gap-1">
                        @php
                            $navItems = [
                                ['route' => 'dashboard', 'is' => 'dashboard', 'label' => 'Dashboard', 'icon' => 'M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z'],
                            ];
                            if(auth()->user()->isGestor()) {
                                $navItems[] = ['route' => 'employees.index', 'is' => 'employees.*', 'label' => 'Funcionários', 'icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z'];
                            }
                            $navItems[] = ['route' => 'worklogs.index', 'is' => 'worklogs.*', 'label' => 'Registros', 'icon' => 'M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2'];
                            $navItems[] = ['route' => 'adjustments.index', 'is' => 'adjustments.*', 'label' => 'Ajustes', 'icon' => 'M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z'];
                        @endphp

                        @foreach($navItems as $item)
                        <a href="{{ route($item['route']) }}"
                           class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm font-medium transition-all
                                  {{ request()->routeIs($item['is']) ? 'bg-white/20 text-white shadow-sm' : 'text-indigo-100 hover:bg-white/10 hover:text-white' }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $item['icon'] }}"/>
                            </svg>
                            {{ $item['label'] }}
                        </a>
                        @endforeach
                    </div>
                </div>

                {{-- User Dropdown --}}
                <div class="hidden md:flex items-center gap-3">
                    <div class="relative" @click.outside="profileOpen = false">
                        <button @click="profileOpen = !profileOpen"
                                class="flex items-center gap-3 px-3 py-1.5 rounded-xl hover:bg-white/10 transition-all">
                            <div class="text-right">
                                <p class="text-white text-sm font-medium leading-tight">{{ auth()->user()->name }}</p>
                                <p class="text-indigo-200 text-xs">{{ auth()->user()->isGestor() ? 'Gestor de RH' : 'Funcionário' }}</p>
                            </div>
                            <div class="w-9 h-9 rounded-full bg-white/20 flex items-center justify-center text-sm font-bold text-white backdrop-blur-sm ring-2 ring-white/20">
                                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                            </div>
                            <svg class="w-4 h-4 text-indigo-200 transition-transform" :class="profileOpen && 'rotate-180'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>

                        {{-- Dropdown menu --}}
                        <div x-show="profileOpen" x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
                             x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                             x-transition:leave="transition ease-in duration-150"
                             x-transition:leave-start="opacity-100 scale-100"
                             x-transition:leave-end="opacity-0 scale-95"
                             class="absolute right-0 mt-2 w-56 bg-white rounded-xl shadow-xl border border-gray-100 py-1.5 z-50"
                             style="display: none;">
                            <div class="px-4 py-2.5 border-b border-gray-100">
                                <p class="text-sm font-semibold text-gray-900">{{ auth()->user()->name }}</p>
                                <p class="text-xs text-gray-500">{{ auth()->user()->email }}</p>
                            </div>
                            <a href="{{ route('profile.edit') }}" class="flex items-center gap-2.5 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition">
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                Meu Perfil
                            </a>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="flex items-center gap-2.5 w-full px-4 py-2.5 text-sm text-red-600 hover:bg-red-50 transition">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                                    Sair do Sistema
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                {{-- Mobile toggle --}}
                <button @click="mobileOpen = !mobileOpen" class="md:hidden text-indigo-200 hover:text-white p-2 rounded-lg hover:bg-white/10 transition">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path x-show="!mobileOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        <path x-show="mobileOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>

        {{-- Mobile menu --}}
        <div x-show="mobileOpen" x-transition class="md:hidden border-t border-white/10">
            <div class="px-4 py-3 space-y-1">
                <a href="{{ route('dashboard') }}" class="block text-indigo-100 hover:bg-white/10 px-3 py-2 rounded-lg text-sm transition">Dashboard</a>
                @if(auth()->user()->isGestor())
                    <a href="{{ route('employees.index') }}" class="block text-indigo-100 hover:bg-white/10 px-3 py-2 rounded-lg text-sm transition">Funcionários</a>
                @endif
                <a href="{{ route('worklogs.index') }}" class="block text-indigo-100 hover:bg-white/10 px-3 py-2 rounded-lg text-sm transition">Registros</a>
                <a href="{{ route('adjustments.index') }}" class="block text-indigo-100 hover:bg-white/10 px-3 py-2 rounded-lg text-sm transition">Ajustes</a>
                <div class="border-t border-white/10 pt-2 mt-2">
                    <div class="px-3 py-2 text-white text-sm font-medium">{{ auth()->user()->name }}</div>
                    <a href="{{ route('profile.edit') }}" class="block text-indigo-200 hover:text-white px-3 py-2 text-sm transition">Meu Perfil</a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="block text-indigo-200 hover:text-white px-3 py-2 text-sm w-full text-left transition">Sair</button>
                    </form>
                </div>
            </div>
        </div>
    </nav>

    {{-- Flash Messages --}}
    @if(session('success'))
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4 animate-slide-up">
            <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl px-4 py-3 flex items-center justify-between shadow-sm"
                 x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)" x-transition>
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span class="text-sm font-medium">{{ session('success') }}</span>
                </div>
                <button @click="show = false" class="text-emerald-600 hover:text-emerald-800 ml-4 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4 animate-slide-up">
            <div class="bg-red-50 border border-red-200 text-red-800 rounded-xl px-4 py-3 flex items-center justify-between shadow-sm"
                 x-data="{ show: true }" x-show="show" x-transition>
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span class="text-sm font-medium">{{ session('error') }}</span>
                </div>
                <button @click="show = false" class="text-red-600 hover:text-red-800 ml-4 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
        </div>
    @endif

    <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8 animate-fade-in">
        @yield('content')
    </main>

    {{-- Footer --}}
    <footer class="border-t border-gray-200 mt-auto bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-5 flex flex-col sm:flex-row items-center justify-between gap-2 text-xs text-gray-400">
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span>HoraSmart © {{ date('Y') }} — Sistema de Controle de Jornada</span>
            </div>
            <span class="text-gray-300">Laravel {{ app()->version() }} • PHP {{ phpversion() }}</span>
        </div>
    </footer>

</div>

{{-- Registrar Service Worker (PWA) --}}
<script>
    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.register('/sw.js').catch(() => {});
    }
</script>

</body>
</html>