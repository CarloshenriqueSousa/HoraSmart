{{--
    Layout: guest.blade.php — Layout para páginas de autenticação (login/registro).

    Design:
     - Tela dividida: branding à esquerda (escondido mobile) + formulário à direita
     - Painel esquerdo com gradiente animado + padrão geométrico + features
     - Painel direito com formulário em card glassmorphism
     - Animações CSS e Alpine.js para features rotativas

    Tecnologias: Blade Component, Tailwind CSS, CSS Animations, Alpine.js
    Dependências: Vite (CSS/JS)
--}}
<!DOCTYPE html>
<html lang="pt-BR" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#4f46e5">
    <title>{{ config('app.name', 'HoraSmart') }} — @yield('title', 'Acesso')</title>
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full bg-slate-50 antialiased">

<div class="min-h-full flex">

    {{-- Branding Panel (desktop) --}}
    <div class="hidden lg:flex lg:w-1/2 relative overflow-hidden bg-gradient-to-br from-indigo-700 via-indigo-600 to-violet-700">
        {{-- Pattern geométrico (CSS puro) --}}
        <div class="absolute inset-0 opacity-10">
            <div class="absolute inset-0"
                 style="background-image: radial-gradient(circle at 25px 25px, rgba(255,255,255,0.3) 2px, transparent 0); background-size: 50px 50px;">
            </div>
        </div>

        {{-- Glow circles animados --}}
        <div class="absolute -top-24 -left-24 w-96 h-96 bg-violet-500/30 rounded-full blur-3xl animate-pulse" style="animation-duration: 4s;"></div>
        <div class="absolute -bottom-24 -right-24 w-96 h-96 bg-indigo-400/30 rounded-full blur-3xl animate-pulse" style="animation-duration: 6s;"></div>
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-64 h-64 bg-white/5 rounded-full blur-2xl animate-pulse" style="animation-duration: 5s;"></div>

        {{-- Conteúdo do branding --}}
        <div class="relative z-10 flex flex-col justify-center px-12 xl:px-16 w-full">
            {{-- Logo --}}
            <div class="mb-12 animate-fade-in">
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-12 h-12 bg-white/20 rounded-2xl flex items-center justify-center backdrop-blur-sm">
                        <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <h1 class="text-3xl font-bold text-white tracking-tight">HoraSmart</h1>
                </div>
                <p class="text-indigo-100 text-lg leading-relaxed max-w-md">
                    Sistema inteligente de controle de jornada e gestão de RH.
                    Simples, rápido e seguro.
                </p>
            </div>

            {{-- Features rotativas --}}
            <div class="space-y-4 animate-slide-up" x-data="{ current: 0 }" x-init="setInterval(() => current = (current + 1) % 4, 3500)">
                @php
                    $features = [
                        ['icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z',       'title' => 'Ponto Digital',       'desc' => '4 batidas diárias com registro via AJAX, sem reload.'],
                        ['icon' => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z', 'title' => 'Dashboard Inteligente', 'desc' => 'KPIs em tempo real para gestores e funcionários.'],
                        ['icon' => 'M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z', 'title' => 'Ajustes com Aprovação',  'desc' => 'Solicite correções com fluxo de aprovação.'],
                        ['icon' => 'M12 18h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z', 'title' => 'PWA Mobile',           'desc' => 'Use no celular sem instalar nada.'],
                    ];
                @endphp

                @foreach($features as $i => $feat)
                <div class="flex items-start gap-4 p-4 rounded-2xl transition-all duration-500"
                     :class="current === {{ $i }} ? 'bg-white/15 shadow-lg scale-[1.02]' : 'bg-white/5 opacity-70'">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0 transition-all"
                         :class="current === {{ $i }} ? 'bg-white/25' : 'bg-white/10'">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $feat['icon'] }}"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-white font-semibold text-sm">{{ $feat['title'] }}</h3>
                        <p class="text-indigo-200 text-xs mt-0.5 leading-relaxed">{{ $feat['desc'] }}</p>
                    </div>
                </div>
                @endforeach
            </div>

            {{-- Footer --}}
            <div class="mt-12 text-indigo-300/70 text-xs">
                © {{ date('Y') }} HoraSmart · Laravel {{ app()->version() }}
            </div>
        </div>
    </div>

    {{-- Formulário --}}
    <div class="flex-1 flex items-center justify-center px-6 py-12 lg:px-8">
        <div class="w-full max-w-md">
            {{-- Logo mobile --}}
            <div class="lg:hidden text-center mb-8 animate-fade-in">
                <div class="flex items-center justify-center gap-2 mb-2">
                    <div class="w-10 h-10 bg-indigo-600 rounded-xl flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <span class="text-2xl font-bold text-gray-900">HoraSmart</span>
                </div>
            </div>

            {{ $slot }}
        </div>
    </div>

</div>

</body>
</html>
