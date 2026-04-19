{{--
    View: auth/login.blade.php — Tela de login do HoraSmart.

    Features:
     - Ícones em cada campo de input
     - Toggle mostrar/ocultar senha (Alpine.js)
     - Loading state com spinner no botão
     - Animação de entrada staggered
     - Checkbox "Lembrar-me" estilizado

    Tecnologias: Blade, Tailwind CSS, Alpine.js
    Layout: layouts/guest.blade.php
--}}
<x-guest-layout>
    @section('title', 'Entrar')

    <div class="animate-slide-up">
        <h2 class="text-2xl font-bold text-gray-900 mb-1">Bem-vindo de volta</h2>
        <p class="text-gray-500 text-sm mb-8">Faça login para acessar o sistema</p>

        @if(session('status'))
            <div class="mb-4 text-sm font-medium text-emerald-600 bg-emerald-50 rounded-xl p-3">
                {{ session('status') }}
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}" class="space-y-5" x-data="{ loading: false, showPass: false }" @submit="loading = true">
            @csrf

            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-1.5">E-mail</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"/>
                        </svg>
                    </div>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username"
                           placeholder="seu@email.com"
                           class="w-full pl-11 pr-4 py-2.5 rounded-xl border border-gray-300 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition @error('email') border-red-400 ring-1 ring-red-400 @enderror">
                </div>
                @error('email')<p class="text-red-500 text-xs mt-1.5">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-gray-700 mb-1.5">Senha</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                    </div>
                    <input id="password" :type="showPass ? 'text' : 'password'" name="password" required autocomplete="current-password"
                           placeholder="Sua senha"
                           class="w-full pl-11 pr-11 py-2.5 rounded-xl border border-gray-300 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition @error('password') border-red-400 ring-1 ring-red-400 @enderror">
                    <button type="button" @click="showPass = !showPass" class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-gray-400 hover:text-gray-600 transition">
                        <svg x-show="!showPass" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        <svg x-show="showPass" style="display:none" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                    </button>
                </div>
                @error('password')<p class="text-red-500 text-xs mt-1.5">{{ $message }}</p>@enderror
            </div>

            <div class="flex items-center justify-between">
                <label class="flex items-center gap-2 cursor-pointer select-none">
                    <input type="checkbox" name="remember" class="w-4 h-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 transition">
                    <span class="text-sm text-gray-600">Lembrar-me</span>
                </label>
            </div>

            <button type="submit" :disabled="loading"
                    class="w-full py-2.5 bg-gradient-to-r from-indigo-600 to-violet-600 text-white text-sm font-semibold rounded-xl hover:from-indigo-700 hover:to-violet-700 focus:ring-4 focus:ring-indigo-200 transition shadow-lg shadow-indigo-500/25 disabled:opacity-70 flex items-center justify-center gap-2">
                <svg x-show="loading" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                <span x-text="loading ? 'Entrando...' : 'Entrar'">Entrar</span>
            </button>
        </form>

        <p class="mt-6 text-center text-sm text-gray-500">
            Não tem conta?
            <a href="{{ route('register') }}" class="text-indigo-600 hover:text-indigo-800 font-semibold transition">Criar conta</a>
        </p>

        {{-- Demo credentials hint --}}
        <div class="mt-6 p-3 bg-slate-100 rounded-xl border border-slate-200">
            <p class="text-xs text-slate-500 font-medium mb-1.5">🔑 Credenciais de demonstração:</p>
            <div class="text-xs text-slate-600 space-y-0.5">
                <p><span class="font-semibold">Gestor:</span> gestor@smart.com / password</p>
                <p><span class="font-semibold">Funcionário:</span> carlos@smart.com / password</p>
            </div>
        </div>
    </div>
</x-guest-layout>
