{{--
    View: employees/index.blade.php — Lista todos os funcionários (gestor).

    Features:
     - Busca instantânea por nome/email/cargo (Alpine.js, client-side)
     - Contador "Mostrando X de Y"
     - Avatares com 2 iniciais e cor por hash
     - Ações: Ver, Editar, Remover
     - Botão de exportar CSV
     - Paginação Laravel
     - Empty state com ilustração

    Tecnologias: Blade, Tailwind CSS, Alpine.js
    Layout: layouts/app.blade.php
--}}
@extends('layouts.app')
@section('title', 'Funcionários')

@section('content')
<div class="space-y-6" x-data="{ search: '', get filtered() { return this.search.length === 0; } }">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Funcionários</h1>
            <p class="text-gray-500 text-sm mt-0.5">{{ $employees->total() }} funcionário(s) cadastrado(s)</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('employees.export.csv') }}"
               class="inline-flex items-center gap-2 px-4 py-2.5 text-sm border border-gray-300 rounded-xl text-gray-700 hover:bg-gray-50 transition font-medium">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                CSV
            </a>
            <a href="{{ route('employees.create') }}"
               class="inline-flex items-center gap-2 px-5 py-2.5 bg-gradient-to-r from-indigo-600 to-violet-600 text-white text-sm font-semibold rounded-xl hover:from-indigo-700 hover:to-violet-700 transition shadow-lg shadow-indigo-500/25">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                Novo Funcionário
            </a>
        </div>
    </div>

    {{-- Busca --}}
    <div class="relative max-w-md">
        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
        </div>
        <input type="text" x-model="search" placeholder="Buscar por nome, email ou cargo..."
               class="w-full pl-11 pr-4 py-2.5 rounded-xl border border-gray-300 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition">
    </div>

    {{-- Tabela --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        @if($employees->isEmpty())
            <div class="px-6 py-16 text-center">
                <svg class="w-16 h-16 text-gray-200 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                <p class="text-gray-400 text-sm mb-3">Nenhum funcionário cadastrado.</p>
                <a href="{{ route('employees.create') }}" class="text-indigo-600 hover:text-indigo-800 text-sm font-semibold">+ Cadastrar primeiro funcionário</a>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100 text-sm">
                    <thead class="bg-gray-50/80 text-xs font-medium text-gray-500 uppercase">
                        <tr>
                            <th class="px-6 py-3 text-left">Funcionário</th>
                            <th class="px-6 py-3 text-left">CPF</th>
                            <th class="px-6 py-3 text-left">Cargo</th>
                            <th class="px-6 py-3 text-left">Admissão</th>
                            <th class="px-6 py-3 text-right">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach($employees as $emp)
                        <tr class="hover:bg-gray-50/50 transition"
                            x-show="!search || '{{ strtolower($emp->user->name . ' ' . $emp->user->email . ' ' . $emp->position) }}'.includes(search.toLowerCase())"
                            x-transition>
                            <td class="px-6 py-3.5">
                                <div class="flex items-center gap-3">
                                    @php
                                        $colors = ['bg-indigo-100 text-indigo-600', 'bg-violet-100 text-violet-600', 'bg-emerald-100 text-emerald-600', 'bg-amber-100 text-amber-600', 'bg-rose-100 text-rose-600'];
                                        $colorClass = $colors[$emp->id % count($colors)];
                                    @endphp
                                    <div class="w-10 h-10 rounded-xl {{ $colorClass }} flex items-center justify-center text-xs font-bold shrink-0">
                                        {{ strtoupper(substr($emp->user->name, 0, 2)) }}
                                    </div>
                                    <div>
                                        <a href="{{ route('employees.show', $emp) }}" class="font-semibold text-gray-800 hover:text-indigo-600 transition">{{ $emp->user->name }}</a>
                                        <p class="text-xs text-gray-400">{{ $emp->user->email }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-3.5 text-gray-600 font-mono text-xs">{{ $emp->cpf }}</td>
                            <td class="px-6 py-3.5">
                                <span class="inline-flex px-2.5 py-1 bg-slate-100 text-slate-700 rounded-lg text-xs font-medium">{{ $emp->position }}</span>
                            </td>
                            <td class="px-6 py-3.5 text-gray-600 text-xs">{{ $emp->hired_at->format('d/m/Y') }}</td>
                            <td class="px-6 py-3.5">
                                <div class="flex items-center justify-end gap-1">
                                    <a href="{{ route('employees.show', $emp) }}" class="p-2 text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition" title="Ver perfil">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    </a>
                                    <a href="{{ route('employees.edit', $emp) }}" class="p-2 text-gray-400 hover:text-amber-600 hover:bg-amber-50 rounded-lg transition" title="Editar">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </a>
                                    <form method="POST" action="{{ route('employees.destroy', $emp) }}" class="inline"
                                          onsubmit="return confirm('Remover {{ $emp->user->name }}? Esta ação é irreversível.')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="p-2 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition" title="Remover">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="px-6 py-4 border-t border-gray-100">
                {{ $employees->links() }}
            </div>
        @endif
    </div>
</div>
@endsection