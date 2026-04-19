{{--
    View: workslogs/edit.blade.php — Formulário de edição de registro de ponto (gestor only).

    Permite ao gestor editar os 4 horários de um registro.
    Recalcula horas automaticamente ao salvar.

    Layout: layouts/app.blade.php
--}}
@extends('layouts.app')
@section('title', 'Editar Registro')

@section('content')
<div class="max-w-lg mx-auto space-y-6">

    <div>
        <a href="{{ route('worklogs.show', $workLog) }}" class="inline-flex items-center gap-1 text-sm text-indigo-600 hover:text-indigo-800 font-medium transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Voltar ao registro
        </a>
        <h1 class="text-2xl font-bold text-gray-900 mt-2">Editar Registro</h1>
        <p class="text-gray-500 text-sm mt-1">
            {{ $workLog->employee->user->name }} — {{ $workLog->work_date->format('d/m/Y') }}
        </p>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <form method="POST" action="{{ route('worklogs.update', $workLog) }}" class="space-y-5" x-data="{ loading: false }" @submit="loading = true">
            @csrf @method('PUT')

            @php
                $fields = [
                    ['name' => 'clock_in',  'label' => 'Entrada',       'icon' => 'M11 16l-4-4m0 0l4-4m-4 4h14'],
                    ['name' => 'lunch_out', 'label' => 'Saída Almoço',  'icon' => 'M17 16l4-4m0 0l-4-4m4 4H7'],
                    ['name' => 'lunch_in',  'label' => 'Retorno',       'icon' => 'M11 16l-4-4m0 0l4-4m-4 4h14'],
                    ['name' => 'clock_out', 'label' => 'Saída Final',   'icon' => 'M17 16l4-4m0 0l-4-4m4 4H7'],
                ];
            @endphp

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                @foreach($fields as $field)
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">{{ $field['label'] }}</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $field['icon'] }}"/>
                            </svg>
                        </div>
                        <input type="datetime-local" name="{{ $field['name'] }}"
                               value="{{ old($field['name'], $workLog->{$field['name']}?->format('Y-m-d\TH:i')) }}"
                               class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-gray-300 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition @error($field['name']) border-red-400 @enderror">
                    </div>
                    @error($field['name'])<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                @endforeach
            </div>

            <div class="bg-amber-50 border border-amber-200 rounded-xl p-3">
                <p class="text-amber-700 text-xs">
                    <strong>Atenção:</strong> Ao salvar, o total de horas será recalculado automaticamente se todos os 4 horários estiverem preenchidos.
                </p>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-gray-100">
                <a href="{{ route('worklogs.show', $workLog) }}" class="px-5 py-2.5 text-sm text-gray-600 hover:text-gray-800 font-medium transition">Cancelar</a>
                <button type="submit" :disabled="loading"
                    class="px-6 py-2.5 bg-gradient-to-r from-indigo-600 to-violet-600 text-white text-sm font-semibold rounded-xl hover:from-indigo-700 hover:to-violet-700 transition shadow-lg shadow-indigo-500/25 disabled:opacity-70 inline-flex items-center gap-2">
                    <svg x-show="loading" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                    Salvar Alterações
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
